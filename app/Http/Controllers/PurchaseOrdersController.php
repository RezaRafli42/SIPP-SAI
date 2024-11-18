<?php

namespace App\Http\Controllers;

use App\Models\ShipWarehouses as ShipWarehouses;
use App\Models\ShipWarehouseConditions as ShipWarehouseConditions;
use App\Models\ShipWarehouseUsages as ShipWarehouseUsages;
use App\Models\ShipWarehouseSendOffice as ShipWarehouseSendOffice;
use App\Models\OfficeWarehouse as OfficeWarehouse;
use App\Models\Ships as Ships;
use App\Models\Items as Items;
use App\Models\Logs as Logs;
use App\Models\LPJ as LPJ;
use App\Models\Services as Services;
use App\Models\ReceiptItems as ReceiptItems;
use App\Models\PurchaseRequests as PurchaseRequests;
use App\Models\PurchaseRequestItems as PurchaseRequestItems;
use App\Models\PurchaseRequestServices as PurchaseRequestServices;
use App\Models\PurchaseOrders as PurchaseOrders;
use App\Models\PurchaseOrderItems as PurchaseOrderItems;
use App\Models\PurchaseOrderServices as PurchaseOrderServices;
use App\Models\Suppliers;
use App\Models\ExpenseAccountDetails as ExpenseAccountDetails;
use App\Models\ExpenseAccounts as ExpenseAccounts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\PDF;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Calculation\Web\Service;

class PurchaseOrdersController extends Controller
{
  public function indexPurchaseOrders(Request $request)
  {
    $suppliers = Suppliers::orderby('supplier_code', 'asc')->get();
    $shipName = Ships::pluck('ship_name');
    $search = $request->query('search');

    // Query untuk Purchase Orders yang belum ditarik ke LPJ (lpj_id = 0)
    $purchaseOrders = PurchaseOrders::leftJoin('suppliers', 'purchase_orders.supplier_id', '=', 'suppliers.id')
      ->leftJoin('purchase_order_items', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
      ->leftJoin('purchase_order_services', 'purchase_orders.id', '=', 'purchase_order_services.purchase_order_id')
      ->select(
        'purchase_orders.id as id',
        'purchase_orders.purchase_order_number as number',
        'purchase_orders.purchase_date as date', // Gunakan purchase_date sebagai tanggal utama
        'purchase_orders.pic',
        'purchase_orders.pic_contact',
        'purchase_orders.note',
        'purchase_orders.status',
        'purchase_orders.ship_id',
        'purchase_orders.currency',
        'purchase_orders.delivery_address',
        'suppliers.supplier_name',
        'suppliers.supplier_code',
        DB::raw('COUNT(DISTINCT purchase_order_items.id) + COUNT(DISTINCT purchase_order_services.id) as item_count'),
        DB::raw('"PO" as type') // Menandai sebagai PO
      )
      ->where(function ($query) {
        $query->where('purchase_orders.lpj_id', '=', 0)
          ->orWhereNull('purchase_orders.lpj_id');
      })

      ->when($search, function ($query, $search) {
        return $query->where('purchase_orders.purchase_order_number', 'like', "%{$search}%");
      })
      ->groupBy('purchase_orders.id', 'suppliers.supplier_name', 'suppliers.supplier_code');

    // Query untuk LPJ dengan daftar PO terkait
    $lpjs = LPJ::select(
      'lpj.id as id',
      'lpj.lpj_number as number',
      'lpj.lpj_date as date', // Gunakan lpj_date sebagai tanggal utama
      'lpj.pic',
      DB::raw('NULL as pic_contact'),
      'lpj.note',
      'lpj.status',
      DB::raw('NULL as ship_id'),
      DB::raw('NULL as currency'),
      DB::raw('NULL as delivery_address'),
      DB::raw('NULL as supplier_name'),
      DB::raw('NULL as supplier_code'),
      DB::raw('COUNT(DISTINCT purchase_order_items.id) + COUNT(DISTINCT purchase_order_services.id) as item_count'),
      DB::raw('"LPJ" as type') // Menandai sebagai LPJ
    )
      ->leftJoin('purchase_orders as po', 'lpj.id', '=', 'po.lpj_id')
      ->leftJoin('purchase_order_items', 'po.id', '=', 'purchase_order_items.purchase_order_id') // Join untuk POI terkait
      ->leftJoin('purchase_order_services', 'po.id', '=', 'purchase_order_services.purchase_order_id') // Join untuk POS terkait
      ->groupBy('lpj.id');

    $allOrders = $purchaseOrders->union($lpjs)
      ->orderByRaw("
            FIELD(status, 'Diajukan', 'Menunggu Diproses', 'Sebagian Diproses', 'Sebagian Selesai', 'Selesai', 'Ditolak'),
            date DESC
        ")
      ->get();

    // Mengambil informasi pengguna
    $auth = Auth::user();
    if ($auth) {
      $user = [
        'name' => $auth->name,
        'role' => $auth->role,
      ];
    }

    // Mengembalikan data ke view
    return view('pages.purchaseOrders', [
      'suppliers' => $suppliers,
      'allOrders' => $allOrders,
      'user' => $user,
      'shipName' => $shipName,
      'search' => $search,
    ]);
  }

  public function getPurchaseOrderItems($id)
  {
    $PO = PurchaseOrders::find($id);

    // Dapatkan ship_id dari Purchase Order terkait (dari PR)
    $ship_id = PurchaseRequests::join('purchase_request_items', 'purchase_requests.id', '=', 'purchase_request_items.purchase_request_id')
      ->join('purchase_order_items', 'purchase_request_items.id', '=', 'purchase_order_items.purchase_request_item_id')
      ->where('purchase_order_items.purchase_order_id', $id)
      ->value('purchase_requests.ship_id');

    // Query untuk items (barang)
    $items = PurchaseOrderItems::where('purchase_order_items.purchase_order_id', $id)
      ->leftJoin('purchase_request_items', 'purchase_order_items.purchase_request_item_id', '=', 'purchase_request_items.id')  // Join ke purchase_request_items
      ->leftJoin('items', 'purchase_request_items.item_id', '=', 'items.id')  // Join ke items melalui item_id di purchase_request_items
      ->leftJoin('purchase_requests', 'purchase_request_items.purchase_request_id', '=', 'purchase_requests.id')  // Join ke purchase_requests
      ->leftJoin('ship_warehouses', function ($join) use ($ship_id) {
        $join->on('ship_warehouses.item_id', '=', 'items.id')
          ->where('ship_warehouses.ship_id', '=', $ship_id);
      })
      ->leftJoin('ship_warehouse_conditions', function ($join) {
        $join->on('ship_warehouse_conditions.ship_warehouse_id', '=', 'ship_warehouses.id')
          ->whereIn('ship_warehouse_conditions.condition', ['Baru', 'Bekas Bisa Pakai', 'Rekondisi']);
      })
      ->select(
        'purchase_order_items.id as poi_id',
        'purchase_order_items.quantity',
        'purchase_order_items.condition',
        'purchase_order_items.price',
        'purchase_order_items.ppn',
        'purchase_order_items.status',
        'items.item_pms',  // Mengambil item code dari tabel items
        'items.item_name',  // Mengambil item name dari tabel items
        'items.item_unit',  // Mengambil item unit dari tabel items
        DB::raw('IFNULL(purchase_request_items.option, "undefined") as item_option'),  // Mengubah alias dari option ke item_option
        DB::raw('IFNULL(purchase_request_items.utility, "undefined") as utility'),
        DB::raw('IFNULL(purchase_requests.purchase_request_number, "undefined") as purchase_request_number'),
        'ship_warehouses.minimum_quantity',  // Mengambil minimum quantity
        DB::raw('IFNULL(SUM(ship_warehouse_conditions.quantity), 0) as total_quantity')  // Mengambil total available quantity
      )
      ->groupBy(
        'purchase_order_items.id',
        'purchase_order_items.quantity',
        'purchase_order_items.condition',
        'purchase_order_items.price',
        'purchase_order_items.ppn',
        'purchase_order_items.status',
        'items.item_pms',
        'items.item_name',
        'items.item_unit',
        'purchase_request_items.option',
        'purchase_request_items.utility',
        'purchase_requests.purchase_request_number',
        'ship_warehouses.minimum_quantity',
        'purchase_request_items.id'
      )
      ->get();

    // Query untuk services (jasa)
    $services = PurchaseOrderServices::where('purchase_order_services.purchase_order_id', $id)
      ->leftJoin('purchase_request_services', 'purchase_order_services.purchase_request_service_id', '=', 'purchase_request_services.id')  // Join ke purchase_request_services (jika ada)
      ->leftJoin('services as pr_services', 'purchase_request_services.service_id', '=', 'pr_services.id')  // Join ke services dari purchase_request_services
      ->leftJoin('services as manual_services', 'purchase_order_services.service_id', '=', 'manual_services.id')  // Join ke services dari purchase_order_services (manual entry)
      ->leftJoin('purchase_requests', 'purchase_request_services.purchase_request_id', '=', 'purchase_requests.id')  // Join ke purchase_requests
      ->select(
        'purchase_order_services.price',
        'purchase_order_services.ppn',
        'purchase_order_services.pph',
        'purchase_order_services.status',
        DB::raw('COALESCE(pr_services.service_code, manual_services.service_code) as service_code'),
        DB::raw('COALESCE(pr_services.service_name, manual_services.service_name) as service_name'),
        'purchase_order_services.utility',
        DB::raw('IFNULL(purchase_requests.purchase_request_number, "") as purchase_request_number')
      )
      ->groupBy(
        'purchase_order_services.price',
        'purchase_order_services.ppn',
        'purchase_order_services.pph',
        'purchase_order_services.status',
        'pr_services.service_code',
        'manual_services.service_code',
        'pr_services.service_name',
        'manual_services.service_name',
        'purchase_order_services.utility',
        'purchase_requests.purchase_request_number'
      )
      ->get();

    // Return items dan services ke frontend
    return response()->json([
      'items' => $items,
      'services' => $services,  // Mengembalikan juga data jasa (services)
      'PO' => $PO,
    ]);
  }

  public function getPurchaseOrderItemforLPJ($lpj_id)
  {
    // Ambil semua Purchase Orders yang terkait dengan LPJ tertentu (berdasarkan lpj_id)
    $PO = PurchaseOrders::where('lpj_id', $lpj_id)->pluck('id'); // Mendapatkan ID dari semua Purchase Orders terkait

    // Dapatkan ship_id dari Purchase Order terkait (dari PR)
    $ship_id = PurchaseRequests::join('purchase_request_items', 'purchase_requests.id', '=', 'purchase_request_items.purchase_request_id')
      ->join('purchase_order_items', 'purchase_request_items.id', '=', 'purchase_order_items.purchase_request_item_id')
      ->whereIn('purchase_order_items.purchase_order_id', $PO)
      ->value('purchase_requests.ship_id');

    // Query untuk items (barang)
    $items = PurchaseOrderItems::whereIn('purchase_order_items.purchase_order_id', $PO) // Menggunakan whereIn untuk semua PO terkait
      ->leftJoin('purchase_orders', 'purchase_order_items.purchase_order_id', '=', 'purchase_orders.id')
      ->leftJoin('purchase_request_items', 'purchase_order_items.purchase_request_item_id', '=', 'purchase_request_items.id')
      ->leftJoin('items', 'purchase_request_items.item_id', '=', 'items.id')
      ->leftJoin('purchase_requests', 'purchase_request_items.purchase_request_id', '=', 'purchase_requests.id')
      ->leftJoin('ship_warehouses', function ($join) use ($ship_id) {
        $join->on('ship_warehouses.item_id', '=', 'items.id')
          ->where('ship_warehouses.ship_id', '=', $ship_id);
      })
      ->leftJoin('ship_warehouse_conditions', function ($join) {
        $join->on('ship_warehouse_conditions.ship_warehouse_id', '=', 'ship_warehouses.id')
          ->whereIn('ship_warehouse_conditions.condition', ['Baru', 'Bekas Bisa Pakai', 'Rekondisi']);
      })
      ->leftJoin('suppliers', 'purchase_orders.supplier_id', '=', 'suppliers.id')
      ->select(
        'purchase_orders.purchase_order_number',
        'purchase_order_items.quantity',
        'purchase_order_items.condition',
        'purchase_order_items.price',
        'purchase_order_items.ppn',
        'purchase_order_items.status',
        'items.item_pms',
        'items.item_name',
        'items.item_unit',
        DB::raw('IFNULL(purchase_request_items.option, "undefined") as item_option'),
        DB::raw('IFNULL(purchase_request_items.utility, "undefined") as utility'),
        DB::raw('IFNULL(purchase_requests.purchase_request_number, "undefined") as purchase_request_number'),
        'ship_warehouses.minimum_quantity',
        DB::raw('IFNULL(SUM(ship_warehouse_conditions.quantity), 0) as total_quantity'),
        'suppliers.supplier_name'
      )
      ->groupBy(
        'purchase_orders.purchase_order_number',
        'purchase_order_items.quantity',
        'purchase_order_items.condition',
        'purchase_order_items.price',
        'purchase_order_items.ppn',
        'purchase_order_items.status',
        'items.item_pms',
        'items.item_name',
        'items.item_unit',
        'purchase_request_items.option',
        'purchase_request_items.utility',
        'purchase_requests.purchase_request_number',
        'ship_warehouses.minimum_quantity',
        'purchase_request_items.id',
        'suppliers.supplier_name'
      )
      ->get();

    // Query untuk services (jasa)
    $services = PurchaseOrderServices::whereIn('purchase_order_services.purchase_order_id', $PO) // Menggunakan whereIn untuk semua PO terkait
      ->leftJoin('purchase_orders', 'purchase_order_services.purchase_order_id', '=', 'purchase_orders.id')
      ->leftJoin('purchase_request_services', 'purchase_order_services.purchase_request_service_id', '=', 'purchase_request_services.id')
      ->leftJoin('services as pr_services', 'purchase_request_services.service_id', '=', 'pr_services.id')
      ->leftJoin('services as manual_services', 'purchase_order_services.service_id', '=', 'manual_services.id')
      ->leftJoin('purchase_requests', 'purchase_request_services.purchase_request_id', '=', 'purchase_requests.id')
      ->leftJoin('suppliers', 'purchase_orders.supplier_id', '=', 'suppliers.id')
      ->select(
        'purchase_orders.purchase_order_number',
        'purchase_order_services.price',
        'purchase_order_services.ppn',
        'purchase_order_services.pph',
        'purchase_order_services.status',
        DB::raw('COALESCE(pr_services.service_code, manual_services.service_code) as service_code'),
        DB::raw('COALESCE(pr_services.service_name, manual_services.service_name) as service_name'),
        'purchase_order_services.utility',
        DB::raw('IFNULL(purchase_requests.purchase_request_number, "") as purchase_request_number'),
        'suppliers.supplier_name'
      )
      ->groupBy(
        'purchase_orders.purchase_order_number',
        'purchase_order_services.price',
        'purchase_order_services.ppn',
        'purchase_order_services.pph',
        'purchase_order_services.status',
        'pr_services.service_code',
        'manual_services.service_code',
        'pr_services.service_name',
        'manual_services.service_name',
        'purchase_order_services.utility',
        'purchase_requests.purchase_request_number',
        'suppliers.supplier_name'
      )
      ->get();

    // Return items dan services ke frontend
    return response()->json([
      'items' => $items,
      'services' => $services,
      'PO' => $PO,
    ]);
  }

  public function findPurchaseOrders(Request $request)
  {
    $name = $request->search;
    $shipName = $request->shipName;

    // Check if the search query is in date format (DD/MM/YYYY)
    if ($name && preg_match('/\d{2}\/\d{2}\/\d{4}/', $name)) {
      // Convert from DD/MM/YYYY to YYYY-MM-DD
      $dateParts = explode('/', $name);
      if (count($dateParts) === 3) {
        $name = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0]; // Convert to YYYY-MM-DD
      }
    }

    // Subquery untuk menghitung jumlah items dan services per purchase order
    $subquery = DB::table('purchase_orders')
      ->leftJoin('purchase_order_items', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
      ->leftJoin('purchase_order_services', 'purchase_orders.id', '=', 'purchase_order_services.purchase_order_id')
      ->select('purchase_orders.id', DB::raw('COUNT(DISTINCT purchase_order_items.id) + COUNT(DISTINCT purchase_order_services.id) as item_count'))
      ->groupBy('purchase_orders.id');

    // Query utama untuk mencari purchase orders
    $query = PurchaseOrders::leftJoin('suppliers', 'purchase_orders.supplier_id', '=', 'suppliers.id') // LEFT JOIN untuk suppliers
      ->leftJoinSub($subquery, 'item_counts', function ($join) {
        $join->on('purchase_orders.id', '=', 'item_counts.id');
      })
      ->leftJoin('purchase_order_items', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
      ->leftJoin('purchase_request_items', 'purchase_order_items.purchase_request_item_id', '=', 'purchase_request_items.id')
      ->leftJoin('items', 'purchase_request_items.item_id', '=', 'items.id')
      ->leftJoin('purchase_order_services', 'purchase_orders.id', '=', 'purchase_order_services.purchase_order_id')
      ->leftJoin('purchase_request_services', 'purchase_order_services.purchase_request_service_id', '=', 'purchase_request_services.id')
      ->leftJoin('services', 'purchase_request_services.service_id', '=', 'services.id')
      ->where(function ($query) use ($name) {
        $query->where('purchase_orders.purchase_order_number', 'LIKE', '%' . $name . '%')
          ->orWhere('purchase_orders.ship_id', 'LIKE', '%' . $name . '%')
          ->orWhere('purchase_orders.pic', 'LIKE', '%' . $name . '%')
          ->orWhere('purchase_orders.pic_contact', 'LIKE', '%' . $name . '%')
          ->orWhere('purchase_orders.note', 'LIKE', '%' . $name . '%')
          ->orWhere('suppliers.supplier_name', 'LIKE', '%' . $name . '%') // Pencarian supplier_name
          ->orWhere('purchase_orders.currency', 'LIKE', '%' . $name . '%')
          ->orWhere('purchase_orders.delivery_address', 'LIKE', '%' . $name . '%')
          ->orWhere('purchase_orders.status', 'LIKE', '%' . $name . '%')
          ->orWhere('items.item_name', 'LIKE', '%' . $name . '%')
          ->orWhere('items.item_pms', 'LIKE', '%' . $name . '%')
          ->orWhere('services.service_name', 'LIKE', '%' . $name . '%') // Pencarian berdasarkan nama service
          ->orWhere('services.service_code', 'LIKE', '%' . $name . '%')
          ->orWhere('purchase_orders.purchase_date', 'LIKE', '%' . $name . '%')
          ->orWhere('purchase_request_items.utility', 'LIKE', '%' . $name . '%')
          ->orWhere('purchase_order_services.utility', 'LIKE', '%' . $name . '%');
      });

    // Filter berdasarkan shipName jika ada
    if (!empty($shipName) && $shipName !== 'All Ship') { // Filter berdasarkan shipName
      $query->where('purchase_orders.ship_id', 'LIKE', '%' . $shipName . '%');
    }

    // Ambil data dengan item count yang benar
    $results = $query->select(
      'purchase_orders.id as id_po',
      'purchase_orders.purchase_order_number',
      'purchase_orders.purchase_date',
      'purchase_orders.status',
      'purchase_orders.pic',
      'purchase_orders.pic_contact',
      'purchase_orders.delivery_address',
      'purchase_orders.currency',
      'purchase_orders.note',
      'suppliers.supplier_name',
      'purchase_orders.ship_id', // Menampilkan string ship_id (nama kapal)
      'item_counts.item_count' // Menggunakan hasil dari subquery
    )
      ->groupBy(
        'purchase_orders.id',
        'purchase_orders.purchase_order_number',
        'purchase_orders.purchase_date',
        'purchase_orders.status',
        'purchase_orders.pic',
        'suppliers.supplier_name',
        'purchase_orders.ship_id', // Group by ship_id
        'item_counts.item_count'
      )
      ->orderByRaw("
          FIELD(purchase_orders.status, 'Diajukan', 'Menunggu Diproses', 'Sebagian Diproses', 'Sebagian Selesai', 'Selesai', 'Ditolak'),
          purchase_orders.purchase_date ASC
      ")
      ->get();

    return response()->json($results);
  }

  public function checkPurchaseOrderNumber(Request $request)
  {
    $POno = $request->POno;
    $exists = PurchaseOrders::where('purchase_order_number', $POno)->exists();
    return response()->json(['exists' => $exists]);
  }

  public function checkLPJNumber(Request $request)
  {
    $LPJno = $request->LPJno;
    $exists = LPJ::where('lpj_number', $LPJno)->exists();
    return response()->json(['exists' => $exists]);
  }

  public function getPurchaseRequests()
  {
    // Query untuk mencari barang-barang dengan id_kapal yang sesuai
    $purchaseRequest = PurchaseRequests::where(function ($query) {
      $query->where('status', 'Menunggu Diproses')
        ->orWhere('status', 'Sebagian Diproses');
    })
      ->get();
    return response()->json($purchaseRequest);
  }

  public function getPurchaseOrders()
  {
    // Query untuk mencari barang-barang dengan id_kapal yang sesuai
    $purchaseOrder = PurchaseOrders::where(function ($query) {
      $query->where('purchase_order_number', 'LIKE', 'Draft-%')
        ->where('status', 'Menunggu LPJ');
    })
      ->get();
    return response()->json($purchaseOrder);
  }

  public function getItemPurchaseRequests($purchaseRequestID)
  {
    // Mengambil data purchase request items bersama dengan item terkait
    $purchaseRequestItems = PurchaseRequestItems::where('purchase_request_id', $purchaseRequestID)
      ->with(['items', 'purchaseRequest.ships']) // Eager load ship through purchaseRequest
      ->get();

    // Mengambil data purchase request services bersama dengan service terkait
    $purchaseRequestServices = PurchaseRequestServices::where('purchase_request_id', $purchaseRequestID)
      ->with(['services', 'purchaseRequest.ships']) // Eager load ship through purchaseRequest
      ->get();

    // Untuk menghitung total quantity dan mengambil minimum_quantity di ship_warehouse_conditions
    foreach ($purchaseRequestItems as $item) {
      // Dapatkan item_id dan ship_id dari setiap purchase request item
      $itemId = $item->item_id;
      $shipId = $item->purchaseRequest->ship_id;  // Mendapatkan ship_id dari relasi purchaseRequest

      // Query untuk mengambil total quantity dan minimum_quantity
      $warehouseData = ShipWarehouses::where('ship_warehouses.ship_id', $shipId)
        ->where('ship_warehouses.item_id', $itemId)
        ->join('ship_warehouse_conditions', 'ship_warehouses.id', '=', 'ship_warehouse_conditions.ship_warehouse_id')
        ->whereIn('ship_warehouse_conditions.condition', ['Baru', 'Bekas Bisa Pakai', 'Rekondisi'])
        ->select(
          DB::raw('IFNULL(SUM(ship_warehouse_conditions.quantity), 0) as total_quantity'),  // Menghitung total quantity
          'ship_warehouses.minimum_quantity'  // Mengambil kolom minimum_quantity
        )
        ->groupBy('ship_warehouses.minimum_quantity')  // Group by untuk mencegah duplikasi
        ->first();

      // Simpan hasil total_quantity dan minimum_quantity di properti item
      $item->total_quantity = $warehouseData ? $warehouseData->total_quantity : 0;
      $item->minimum_quantity = $warehouseData ? $warehouseData->minimum_quantity : 0;
    }

    // Return hasil sebagai respons
    return response()->json([
      'items' => $purchaseRequestItems,         // Data dari purchase_request_items
      'services' => $purchaseRequestServices    // Data dari purchase_request_services
    ]);
  }

  public function getItemPurchaseOrders($purchaseOrderID)
  {
    // Ambil items dari purchase order
    $purchaseOrderItems = PurchaseOrderItems::with([
      'purchaseOrders',
      'purchaseRequestItems.items',
      'purchaseRequestItems.purchaseRequest'
    ])
      ->where('purchase_order_id', $purchaseOrderID)
      ->get();

    // Ambil services dari purchase order
    $purchaseOrderServices = PurchaseOrderServices::with([
      'purchaseOrders',
      'purchaseRequestServices.services',
      'purchaseRequestServices.purchaseRequest',
      'services'
    ])
      ->where('purchase_order_id', $purchaseOrderID)
      ->get();

    // Kembalikan data dalam bentuk JSON dengan items dan services
    return response()->json([
      'items' => $purchaseOrderItems,
      'services' => $purchaseOrderServices
    ]);
  }

  public function addPurchaseOrders(Request $request)
  {
    // Validasi nomor PO jika berstatus Draft
    if ($request->input('purchase_order_number') === 'Draft-' || (Str::startsWith($request->input('purchase_order_number'), 'Draft-') && strlen($request->input('purchase_order_number')) <= 6)) {
      return redirect()->back()->with('swal-fail', 'Please fill out the PO Draft number fields');
    }

    // Validasi mata uang jika berstatus Draft
    if (Str::startsWith($request->input('purchase_order_number'), 'Draft-') && $request->input('supplier_currency') !== 'IDR') {
      return redirect()->back()->with('swal-fail', 'PO Draft must have IDR as currency');
    }

    try {
      // Mulai transaksi database
      DB::beginTransaction();

      // Buat Purchase Order baru
      $purchaseOrder = new PurchaseOrders();
      $purchaseOrder->purchase_order_number = $request->purchase_order_number;
      $purchaseOrder->ship_id = $request->ship_id; // Simpan ship_id sebagai string jika multiple ship
      $purchaseOrder->supplier_id = $request->supplier_id;
      $purchaseOrder->currency = $request->supplier_currency;
      $purchaseOrder->pic = $request->pic;
      $purchaseOrder->pic_contact = $request->pic_contact;
      $purchaseOrder->delivery_address = $request->delivery_address;
      $purchaseOrder->note = $request->note;
      $purchaseOrder->purchase_date = $request->purchase_date;

      // Tentukan status Purchase Order
      if (Str::startsWith($request->purchase_order_number, 'Draft-')) {
        $purchaseOrder->status = 'Menunggu LPJ';
      } else {
        $purchaseOrder->status = 'Diajukan';
      }

      $purchaseOrder->save();

      // **Simpan items (barang) ke purchase_order_items**
      if (!empty($request->purchase_request_item_id)) {
        foreach ($request->purchase_request_item_id as $index => $purchase_request_item_id) {
          $purchaseOrderItem = new PurchaseOrderItems();
          $purchaseOrderItem->purchase_order_id = $purchaseOrder->id;

          // Jika purchase_request_item_id adalah 0 (atau null), berarti item ini tidak dari PR
          $purchaseOrderItem->purchase_request_item_id = $purchase_request_item_id == 0 ? null : $purchase_request_item_id;

          // Isi field lainnya untuk items
          $purchaseOrderItem->quantity = $request->input('quantity')[$index] ?? 0;
          $purchaseOrderItem->condition = $request->input('condition')[$index] ?? '';  // Kondisi item (Baru/Bekas/Rekondisi)
          $purchaseOrderItem->price = $request->input('price_item')[$index] ?? 0;  // Harga item
          $purchaseOrderItem->ppn = $request->input('ppn')[$index] ?? 0;  // PPN item

          // Set status item purchase order
          $purchaseOrderItem->status = Str::startsWith($purchaseOrder->purchase_order_number, 'Draft-') ? 'Menunggu LPJ' : 'Diajukan';
          $purchaseOrderItem->save();

          // Hanya record ke expense_account_details jika PO adalah Draft
          if (Str::startsWith($purchaseOrder->purchase_order_number, 'Draft-')) {
            $purchaseRequestItem = PurchaseRequestItems::find($purchaseOrderItem->purchase_request_item_id);
            if ($purchaseRequestItem) {
              $item = Items::find($purchaseRequestItem->item_id);
              if ($item) {
                $accountId = $item->account_id;

                // Hitung jumlah amount (quantity * price)
                $amount = $purchaseOrderItem->quantity * $purchaseOrderItem->price;

                // Simpan ke expense_account_details
                $expenseDetail = ExpenseAccountDetails::where('account_id', $accountId)
                  ->where('purchase_order_item_id', $purchaseOrderItem->id)
                  ->first();

                if ($expenseDetail) {
                  // Jika sudah ada, tambahkan amount
                  $expenseDetail->amount += $amount;
                  $expenseDetail->save();
                } else {
                  // Jika belum ada, buat entri baru
                  ExpenseAccountDetails::create([
                    'account_id' => $accountId,
                    'purchase_order_item_id' => $purchaseOrderItem->id,
                    'purchase_order_service_id' => null,
                    'amount' => $amount,
                  ]);
                }
              }
            }
          }
        }
      }

      // **Simpan services (jasa) ke purchase_order_services**
      if (!empty($request->purchase_request_service_id)) {
        foreach ($request->purchase_request_service_id as $index => $purchase_request_service_id) {
          $purchaseOrderService = new PurchaseOrderServices();
          $purchaseOrderService->purchase_order_id = $purchaseOrder->id;
          $purchaseOrderService->status = Str::startsWith($purchaseOrder->purchase_order_number, 'Draft-') ? 'Menunggu LPJ' : 'Diajukan';

          if (Str::startsWith($purchase_request_service_id, 'service-')) {
            // Service dari Service List
            $actualServiceId = substr($purchase_request_service_id, strlen('service-'));
            $purchaseOrderService->purchase_request_service_id = null;
            $purchaseOrderService->service_id = $actualServiceId;
          } else {
            // Service dari PRS
            $purchaseOrderService->purchase_request_service_id = $purchase_request_service_id == 0 ? null : $purchase_request_service_id;
            $purchaseOrderService->service_id = null;
          }

          // Simpan informasi lain terkait service
          $purchaseOrderService->price = $request->input('price_service')[$index] ?? 0;
          $purchaseOrderService->ppn = $request->input('ppn_service')[$index] ?? 0;
          $pphService = str_replace(',', '.', $request->input('pph_service')[$index] ?? 0);
          $purchaseOrderService->pph = $pphService;
          $purchaseOrderService->utility = $request->input('utility')[$index] ?? '';

          // Simpan purchase order service
          $purchaseOrderService->save();

          // Hanya record ke expense_account_details jika PO adalah Draft
          if (Str::startsWith($purchaseOrder->purchase_order_number, 'Draft-')) {
            if ($purchaseOrderService->purchase_request_service_id) {
              // Jika menarik dari PRS, cari Service terkait dari PRS
              $purchaseRequestService = PurchaseRequestServices::find($purchaseOrderService->purchase_request_service_id);
              if ($purchaseRequestService) {
                $service = Services::find($purchaseRequestService->service_id);
              } else {
                throw new \Exception("Purchase Request Service with ID {$purchaseOrderService->purchase_request_service_id} not found.");
              }
            } else {
              // Jika langsung dari Service List
              $service = Services::find($purchaseOrderService->service_id);
            }

            if ($service) {
              $accountId = $service->account_id;

              // Hitung jumlah amount (hanya price karena service tidak punya quantity)
              $amount = $purchaseOrderService->price;

              // Simpan ke expense_account_details
              $expenseDetail = ExpenseAccountDetails::where('account_id', $accountId)
                ->where('purchase_order_service_id', $purchaseOrderService->id)
                ->first();

              if ($expenseDetail) {
                // Jika sudah ada, tambahkan amount
                $expenseDetail->amount += $amount;
                $expenseDetail->save();
              } else {
                // Jika belum ada, buat entri baru
                ExpenseAccountDetails::create([
                  'account_id' => $accountId,
                  'purchase_order_item_id' => null,
                  'purchase_order_service_id' => $purchaseOrderService->id,
                  'amount' => $amount,
                ]);
              }
            }
          }
        }
      }

      // Commit transaksi
      DB::commit();

      // Redirect ke halaman sebelumnya dengan pesan sukses
      return redirect()->back()->with('swal-success', 'Purchase Order berhasil disimpan.');
    } catch (\Exception $e) {
      // Rollback transaksi jika terjadi error
      DB::rollback();
      return redirect()->back()->with('swal-fail', 'Failed to add Purchase Order: ' . $e->getMessage());
    }
  }

  public function addLPJ(Request $request)
  {
    // dd('Request data:', $request->all());
    try {
      $request->validate([
        'lpj_number' => 'required|string',
        'lpj_date' => 'required|date',
        'pic' => 'required',
        'note' => 'nullable|string',
        'purchase_order_ids' => 'required|string',
      ]);
    } catch (\Exception $e) {
      dd('Exception caught:', $e->getMessage());
    }
    $purchaseOrderIds = explode(',', $request->input('purchase_order_ids'));
    $waitingItems = PurchaseOrderItems::whereIn('purchase_order_id', $purchaseOrderIds)
      ->where('status', '!=', 'Selesai')
      ->count();
    $waitingServices = PurchaseOrderServices::whereIn('purchase_order_id', $purchaseOrderIds)
      ->where('status', '!=', 'Selesai')
      ->count();
    if ($waitingItems > 0 || $waitingServices > 0) {
      return redirect()->back()->with('swal-fail', 'Receive the items and services first before creating the LPJ');
    }
    $lpj = new LPJ();
    $lpj->lpj_number = $request->lpj_number;
    $lpj->lpj_date = $request->lpj_date;
    $lpj->pic = $request->pic;
    $lpj->note = $request->note;
    $lpj->status = 'Selesai';
    $lpj->save();
    $updatedPOCount = PurchaseOrders::whereIn('id', $purchaseOrderIds)
      ->update(['lpj_id' => $lpj->id, 'status' => 'Diambil oleh LPJ']);
    $logEntry = Logs::create([
      'user_id' => Auth::user()->id,
      'action' => 'Created LPJ with ID: ' . $lpj->id . ' and merged draft Purchase Orders.',
    ]);
    return redirect()->back()->with('swal-success', 'LPJ added successfully.');
  }

  public function acceptPurchaseOrders(Request $request)
  {
    try {
      DB::beginTransaction();

      // Mengubah status Purchase Order menjadi "Menunggu Diproses"
      $purchaseOrders = PurchaseOrders::findOrFail($request->po_id);
      $purchaseOrders->status = "Menunggu Diproses";
      $purchaseOrders->save();

      // Periksa apakah currency adalah selain IDR
      $currency = $purchaseOrders->currency;
      $exchangeRate = 1; // Default jika IDR

      if ($currency !== 'IDR') {
        // Ambil nilai kurs konversi (gunakan API atau database lokal untuk kurs)
        $purchaseDate = $purchaseOrders->purchase_date;
        $exchangeRate = $this->getExchangeRate($currency, 'IDR', $purchaseDate);

        if (!$exchangeRate) {
          throw new \Exception("Exchange rate for {$currency} to IDR on {$purchaseDate} not found.");
        }
      }

      // Mengubah status setiap item di Purchase Order menjadi "Menunggu Diproses"
      $purchaseOrderItems = PurchaseOrderItems::where('purchase_order_id', $request->po_id)->get();
      foreach ($purchaseOrderItems as $item) {
        $item->status = "Menunggu Diproses";
        $item->save();

        // Mengubah status Purchase Request Item yang terkait menjadi "Diproses" jika ada
        if ($item->purchase_request_item_id) {
          $purchaseRequestItem = PurchaseRequestItems::findOrFail($item->purchase_request_item_id);
          $purchaseRequestItem->status = "Diproses";
          $purchaseRequestItem->save();
        }

        // **Tambahkan ke Expense Account Details**
        $purchaseRequestItem = PurchaseRequestItems::find($item->purchase_request_item_id);
        if ($purchaseRequestItem) {
          $relatedItem = Items::find($purchaseRequestItem->item_id);
          if ($relatedItem) {
            $accountId = $relatedItem->account_id;

            // Hitung jumlah amount dan konversi ke IDR jika perlu
            $amount = $item->quantity * $item->price;
            $amountInIDR = $amount * $exchangeRate;

            // Simpan ke expense_account_details
            $expenseDetail = ExpenseAccountDetails::where('account_id', $accountId)
              ->where('purchase_order_item_id', $item->id)
              ->first();

            if ($expenseDetail) {
              // Jika sudah ada, tambahkan amount
              $expenseDetail->amount += $amountInIDR;
              $expenseDetail->save();
            } else {
              // Jika belum ada, buat entri baru
              ExpenseAccountDetails::create([
                'account_id' => $accountId,
                'purchase_order_item_id' => $item->id,
                'purchase_order_service_id' => null,
                'amount' => $amountInIDR,
              ]);
            }
          }
        }
      }

      // Mengubah status setiap service di Purchase Order menjadi "Menunggu Diproses"
      $purchaseOrderServices = PurchaseOrderServices::where('purchase_order_id', $request->po_id)->get();
      foreach ($purchaseOrderServices as $service) {
        $service->status = "Menunggu Diproses";
        $service->save();

        // Mengubah status Purchase Request Service yang terkait menjadi "Diproses" jika ada
        if ($service->purchase_request_service_id) {
          $purchaseRequestService = PurchaseRequestServices::findOrFail($service->purchase_request_service_id);
          $purchaseRequestService->status = "Diproses";
          $purchaseRequestService->save();
        }

        // **Tambahkan ke Expense Account Details**
        if ($service->purchase_request_service_id) {
          $purchaseRequestService = PurchaseRequestServices::find($service->purchase_request_service_id);
          if ($purchaseRequestService) {
            $relatedService = Services::find($purchaseRequestService->service_id);
          } else {
            throw new \Exception("Purchase Request Service with ID {$service->purchase_request_service_id} not found.");
          }
        } else {
          $relatedService = Services::find($service->service_id);
        }

        if ($relatedService) {
          $accountId = $relatedService->account_id;

          // Hitung jumlah amount dan konversi ke IDR jika perlu
          $amount = $service->price;
          $amountInIDR = $amount * $exchangeRate;

          // Simpan ke expense_account_details
          $expenseDetail = ExpenseAccountDetails::where('account_id', $accountId)
            ->where('purchase_order_service_id', $service->id)
            ->first();

          if ($expenseDetail) {
            // Jika sudah ada, tambahkan amount
            $expenseDetail->amount += $amountInIDR;
            $expenseDetail->save();
          } else {
            // Jika belum ada, buat entri baru
            ExpenseAccountDetails::create([
              'account_id' => $accountId,
              'purchase_order_item_id' => null,
              'purchase_order_service_id' => $service->id,
              'amount' => $amountInIDR,
            ]);
          }
        }
      }

      // Lanjutkan logika untuk memperbarui status Purchase Request...

      DB::commit();

      return redirect()->back()->with('swal-success', 'Purchase Order accepted successfully');
    } catch (\Exception $e) {
      DB::rollback();
      return redirect()->back()->with('swal-fail', 'Failed to accept Purchase Order: ' . $e->getMessage());
    }
  }

  private function getExchangeRate($fromCurrency, $toCurrency, $date)
  {
    // Contoh API untuk mendapatkan kurs
    $apiUrl = "https://api.frankfurter.app/{$date}?from={$fromCurrency}&to={$toCurrency}";

    try {
      $response = Http::get($apiUrl);
      if ($response->successful() && isset($response['rates'][$toCurrency])) {
        return $response['rates'][$toCurrency];
      }
      return null;
    } catch (\Exception $e) {
      return null;
    }
  }

  public function rejectPurchaseOrders(Request $request)
  {
    try {
      DB::beginTransaction();

      // Mengubah status Purchase Order menjadi "Ditolak"
      $purchaseOrder = PurchaseOrders::findOrFail($request->po_id);
      $purchaseOrder->status = "Ditolak";
      $purchaseOrder->save();

      // Mengubah status setiap item di Purchase Order menjadi "Ditolak"
      $purchaseOrderItems = PurchaseOrderItems::where('purchase_order_id', $request->po_id)->get();
      foreach ($purchaseOrderItems as $item) {
        $item->status = "Ditolak";
        $item->save();
      }

      // Mengubah status setiap service di Purchase Order menjadi "Ditolak"
      $purchaseOrderServices = PurchaseOrderServices::where('purchase_order_id', $request->po_id)->get();
      foreach ($purchaseOrderServices as $service) {
        $service->status = "Ditolak";
        $service->save();
      }

      // Jika tidak ada item atau service, lempar pengecualian
      if ($purchaseOrderItems->isEmpty() && $purchaseOrderServices->isEmpty()) {
        throw new \Exception('Tidak ada item atau service untuk Purchase Order ini.');
      }

      // Menambahkan log
      Logs::create([
        'user_id' => Auth::user()->id,
        'action' => 'rejected Purchase Order ' . $purchaseOrder->purchase_order_number . '.',
      ]);

      DB::commit();

      return redirect()->back()->with('swal-success', 'Purchase Order rejected successfully');
    } catch (\Exception $e) {
      DB::rollback();
      return redirect()->back()->with('swal-fail', 'Failed to reject Purchase Order: ' . $e->getMessage());
    }
  }

  public function printPurchaseOrders($id)
  {
    $data = PurchaseOrderItems::where('purchase_order_id', $id)
      ->leftJoin('purchase_orders', 'purchase_order_items.purchase_order_id', '=', 'purchase_orders.id')
      ->leftJoin('purchase_request_items', 'purchase_order_items.purchase_request_item_id', '=', 'purchase_request_items.id')
      ->leftJoin('purchase_requests', 'purchase_request_items.purchase_request_id', '=', 'purchase_requests.id')
      ->leftJoin('items', 'purchase_request_items.item_id', '=', 'items.id')
      ->leftJoin('suppliers', 'purchase_orders.supplier_id', '=', 'suppliers.id')
      ->leftJoin('ships', 'purchase_orders.ship_id', '=', 'ships.id')
      ->select('purchase_orders.*', 'purchase_order_items.*', 'purchase_order_items.condition as condition_poi', 'purchase_request_items.*', 'purchase_requests.*', 'items.*', 'ships.*', 'suppliers.*')
      ->get();

    // Tidak perlu menghitung di sini, biarkan Blade template yang menghitung

    $dataSatuan = PurchaseOrderItems::where('purchase_order_id', $id)
      ->leftJoin('purchase_orders', 'purchase_order_items.purchase_order_id', '=', 'purchase_orders.id')
      ->leftJoin('purchase_request_items', 'purchase_order_items.purchase_request_item_id', '=', 'purchase_request_items.id')
      ->leftJoin('purchase_requests', 'purchase_request_items.purchase_request_id', '=', 'purchase_requests.id')
      ->leftJoin('items', 'purchase_request_items.item_id', '=', 'items.id')
      ->leftJoin('suppliers', 'purchase_orders.supplier_id', '=', 'suppliers.id')
      ->leftJoin('ships', 'purchase_orders.ship_id', '=', 'ships.id')
      ->select('purchase_orders.*', 'purchase_order_items.*', 'purchase_request_items.*', 'purchase_requests.*', 'items.*', 'ships.*', 'suppliers.*')
      ->first();

    $currency = $data->first()->currency;
    $waktu = 'Jakarta, ' . Carbon::now()->format('d F Y');
    $pdf = PDF::loadview('print.printPurchaseOrders', ['data' => $data, 'dataSatuan' => $dataSatuan, 'waktu' => $waktu, 'currency' => $currency]);
    return $pdf->download('Purchase Order.pdf');
  }

  public function statisticPurchaseOrders()
  {
    $pendingCount = PurchaseOrders::where('status', 'Diajukan')->count();
    $progressCount = PurchaseOrders::whereIn('status', ['Menunggu Diproses', 'Sebagian Diproses', 'Terproses'])
      ->count();
    $finishedCount = PurchaseOrders::where('status', 'Selesai')->count();
    $rejectedCount = PurchaseOrders::where('status', 'Ditolak')->count();
    return response()->json([
      'pending' => $pendingCount,
      'progress' => $progressCount,
      'finished' => $finishedCount,
      'rejected' => $rejectedCount,
    ]);
  }

  public function getServices()
  {
    $jasa = Services::all();
    return response()->json([
      'jasa_items' => $jasa
    ]);
  }

  public function getShip(Request $request)
  {
    // Cari Purchase Request berdasarkan PR Number
    $purchaseRequest = PurchaseRequests::where('purchase_request_number', $request->prNumber)->first();

    if ($purchaseRequest) {
      // Ambil Ship ID terkait dengan PR
      $shipId = $purchaseRequest->ship_id;
      // Return response JSON dengan Ship ID
      return response()->json([
        'ship_id' => $shipId,
      ]);
    } else {
      // Jika tidak ditemukan, return error
      return response()->json([
        'error' => 'PR Number not found.'
      ], 404);
    }
  }

  public function updatePurchaseOrderQuantities(Request $request)
  {
    $request->validate([
      'quantities' => 'required|array',
      'quantities.*.poi_id' => 'required|integer|exists:purchase_order_items,id',
      'quantities.*.quantity' => 'required|integer|min:1',
    ]);

    try {
      DB::beginTransaction();

      foreach ($request->quantities as $itemData) {
        $item = PurchaseOrderItems::findOrFail($itemData['poi_id']);
        $po = PurchaseOrders::findOrFail($item->purchase_order_id); // Ambil data PO terkait

        $totalReceivedQuantity = ReceiptItems::where('purchase_order_item_id', $item->id)
          ->sum('received_quantity');

        // Jika quantity kurang dari total yang sudah diterima
        if ($itemData['quantity'] < $totalReceivedQuantity) {
          return response()->json(['success' => false, 'message' => 'Quantity cannot be less than received quantity'], 400);
        }

        // Update quantity dan status
        $item->quantity = $itemData['quantity'];
        $item->status = ($totalReceivedQuantity >= $item->quantity) ? 'Selesai' : 'Belum Selesai';
        $item->save();

        // **Update Expense Account Details**
        $expenseDetail = ExpenseAccountDetails::where('purchase_order_item_id', $item->id)->first();

        if ($expenseDetail) {
          $newAmount = $item->quantity * $item->price;

          // Jika currency PO bukan IDR, konversikan amount ke IDR
          if ($po->currency !== 'IDR') {
            $exchangeRate = $this->getExchangeRateFromAPI($po->currency, 'IDR', $po->purchase_date);
            $newAmount *= $exchangeRate; // Konversi ke IDR
          }

          // Perbarui jumlah di expense_account_details
          $expenseDetail->amount = $newAmount;
          $expenseDetail->save();
        }
      }

      // Update status Purchase Order jika semua item selesai
      $PO = PurchaseOrders::findOrFail($item->purchase_order_id);
      $allItemsCompleted = PurchaseOrderItems::where('purchase_order_id', $PO->id)
        ->where('status', '!=', 'Selesai')
        ->doesntExist();
      $hasServices = PurchaseOrderServices::where('purchase_order_id', $PO->id)->exists();
      $allServicesCompleted = $hasServices ? PurchaseOrderServices::where('purchase_order_id', $PO->id)
        ->where('status', '!=', 'Selesai')
        ->doesntExist() : true;

      if ($allItemsCompleted && $allServicesCompleted) {
        $PO->status = 'Selesai';
      } else {
        $PO->status = 'Sebagian Selesai';
      }
      $PO->save();

      DB::commit();

      // Kembalikan pesan sukses jika berhasil
      return response()->json(['success' => true]);
    } catch (\Exception $e) {
      DB::rollback();
      return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
  }

  private function getExchangeRateFromAPI($fromCurrency, $toCurrency, $date)
  {
    try {
      $url = "https://api.frankfurter.app/{$date}?from={$fromCurrency}&to={$toCurrency}";
      $response = file_get_contents($url);
      $data = json_decode($response, true);

      if (isset($data['rates'][$toCurrency])) {
        return $data['rates'][$toCurrency];
      }

      throw new \Exception("Exchange rate for {$fromCurrency} to {$toCurrency} on {$date} not found.");
    } catch (\Exception $e) {
      throw new \Exception("Failed to fetch exchange rate: " . $e->getMessage());
    }
  }
}
