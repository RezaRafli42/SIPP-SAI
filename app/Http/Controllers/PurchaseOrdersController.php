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
use App\Models\Services as Services;
use App\Models\PurchaseRequests as PurchaseRequests;
use App\Models\PurchaseRequestItems as PurchaseRequestItems;
use App\Models\PurchaseRequestServices as PurchaseRequestServices;
use App\Models\PurchaseOrders as PurchaseOrders;
use App\Models\PurchaseOrderItems as PurchaseOrderItems;
use App\Models\PurchaseOrderServices as PurchaseOrderServices;
use App\Models\Suppliers;
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
    // Get the search query from the request
    $search = $request->query('search');
    // Join the purchase_orders with suppliers and count the number of items
    $purchaseOrders = PurchaseOrders::leftJoin('suppliers', 'purchase_orders.supplier_id', '=', 'suppliers.id') // Ubah ke LEFT JOIN
      ->leftJoin('purchase_order_items', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id') // Tetap LEFT JOIN untuk purchase_order_items
      ->leftJoin('purchase_order_services', 'purchase_orders.id', '=', 'purchase_order_services.purchase_order_id')
      ->select(
        'purchase_orders.*',
        'purchase_orders.id as id_po',
        'suppliers.supplier_name',
        'suppliers.supplier_code',
        DB::raw('COUNT(DISTINCT purchase_order_items.id) + COUNT(DISTINCT purchase_order_services.id) as item_count')
      )
      // If there's a search term, filter by purchase order number
      ->when($search, function ($query, $search) {
        return $query->where('purchase_orders.purchase_order_number', 'like', "%{$search}%");
      })
      ->groupBy('purchase_orders.id', 'suppliers.supplier_name', 'suppliers.supplier_code') // Group by purchase order and supplier details
      ->orderByRaw("
              FIELD(purchase_orders.status, 'Diajukan', 'Menunggu Diproses', 'Sebagian Diproses', 'Sebagian Selesai', 'Selesai', 'Ditolak'),
              purchase_orders.purchase_date ASC
          ")
      ->get();
    $auth = Auth::user();
    if ($auth) {
      $user = [
        'name' => $auth->name,
        'role' => $auth->role,
      ];
    }
    return view('pages.purchaseOrders', [
      'suppliers' => $suppliers,
      'purchaseOrders' => $purchaseOrders,
      'user' => $user,
      'shipName' => $shipName,
      'search' => $search, // Pass the search term to the view
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
        'purchase_order_services.status',
        DB::raw('COALESCE(pr_services.service_code, manual_services.service_code) as service_code'),
        DB::raw('COALESCE(pr_services.service_name, manual_services.service_name) as service_name'),
        'purchase_order_services.utility',
        DB::raw('IFNULL(purchase_requests.purchase_request_number, "") as purchase_request_number')
      )
      ->groupBy(
        'purchase_order_services.price',
        'purchase_order_services.ppn',
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
    // Debugging untuk melihat semua input yang diterima
    // dd($request->all());

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

          // Update status Purchase Request Item menjadi "Diproses" jika PO adalah Draft
          if (Str::startsWith($purchaseOrder->purchase_order_number, 'Draft-')) {
            $purchaseRequestItem = PurchaseRequestItems::find($purchase_request_item_id);
            if ($purchaseRequestItem) {
              $purchaseRequestItem->status = 'Diproses';
              $purchaseRequestItem->save();
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

          // Jika purchase_request_service_id dimulai dengan 'service-', ini adalah jasa dari Service List, bukan PR
          if (Str::startsWith($purchase_request_service_id, 'service-')) {
            $actualServiceId = substr($purchase_request_service_id, strlen('service-'));  // Ambil ID setelah 'service-'
            $purchaseOrderService->purchase_request_service_id = null;  // Set purchase_request_service_id ke null
            $purchaseOrderService->service_id = $actualServiceId;  // Isi dengan ID dari tabel services
          } else {
            $purchaseOrderService->purchase_request_service_id = $purchase_request_service_id == 0 ? null : $purchase_request_service_id;
            $purchaseOrderService->service_id = null;  // Set service_id ke null karena ini bukan dari service-list
          }

          // Simpan informasi lain terkait service
          $purchaseOrderService->price = $request->input('price_service')[$index] ?? 0;
          $purchaseOrderService->ppn = $request->input('ppn_service')[$index] ?? 0;
          $purchaseOrderService->utility = $request->input('utility')[$index] ?? '';

          // Set status jasa sesuai dengan status PO
          $purchaseOrderService->save();

          // **Update status Purchase Request Service menjadi "Diproses" jika PO adalah Draft**
          if (Str::startsWith($purchaseOrder->purchase_order_number, 'Draft-')) {
            $purchaseRequestService = PurchaseRequestServices::find($purchase_request_service_id);
            if ($purchaseRequestService) {
              $purchaseRequestService->status = 'Diproses';
              $purchaseRequestService->save();
            }
          }
        }
      }

      // Update status PR hanya jika purchase_order_number dimulai dengan 'Draft-'
      if (Str::startsWith($purchaseOrder->purchase_order_number, 'Draft-')) {
        $purchaseRequestId = $request->purchase_request_item_id[0] ?? $request->purchase_request_service_id[0];
        if ($purchaseRequestId) {
          $purchaseRequest = PurchaseRequestItems::find($purchaseRequestId)?->purchaseRequest
            ?? PurchaseRequestServices::find($purchaseRequestId)?->purchaseRequest;
          if ($purchaseRequest) {
            // Mengambil semua items dan services dari purchase_request yang terkait
            $allPurchaseRequestItems = PurchaseRequestItems::where('purchase_request_id', $purchaseRequest->id)->get();
            $allPurchaseRequestServices = PurchaseRequestServices::where('purchase_request_id', $purchaseRequest->id)->get();

            $allProcessed = true;
            foreach ($allPurchaseRequestItems as $item) {
              if ($item->status !== 'Diproses') {
                $allProcessed = false;
                break;
              }
            }

            foreach ($allPurchaseRequestServices as $service) {
              if ($service->status !== 'Diproses') {
                $allProcessed = false;
                break;
              }
            }

            // Update status Purchase Request berdasarkan status semua items dan services
            $purchaseRequest->status = $allProcessed ? 'Terproses' : 'Sebagian Diproses';
            $purchaseRequest->save();
          }
        }
      }

      // Log tindakan yang dilakukan
      Logs::create([
        'user_id' => Auth::user()->id,
        'action' => 'Created Purchase Order ' . $request->input('purchase_order_number') . '.',
      ]);

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
    // dd($request->all());
    $purchaseOrderIds = explode(',', $request->input('purchase_order_ids'));
    // Validasi data dari request
    $request->validate([
      'purchase_order_number' => 'required',
      'purchase_date' => 'required|date',
      'purchase_order_ids' => 'required',
      'pic' => 'required',
      'note' => 'nullable|string'
    ]);
    // Cek jumlah items dengan status "Menunggu LPJ"
    $waitingItems = PurchaseOrderItems::whereIn('purchase_order_id', $purchaseOrderIds)
      ->where('status', 'Menunggu LPJ')
      ->count();

    // Cek jumlah services dengan status "Menunggu LPJ"
    $waitingServices = PurchaseOrderServices::whereIn('purchase_order_id', $purchaseOrderIds)
      ->where('status', 'Menunggu LPJ')
      ->count();

    if ($waitingItems > 0 || $waitingServices > 0) {
      // Jika ada item atau service dengan status "Menunggu LPJ", batalkan proses dan tampilkan swal-fail
      return redirect()->back()->with('swal-fail', 'Receive the items and services first before creating the LPJ');
    }

    // Jika semua item sudah diproses, lanjutkan pembuatan LPJ
    // Buat LPJ baru (di tabel purchase_orders)
    Log::info('Creating new LPJ...');
    $lpj = new PurchaseOrders();
    $lpj->purchase_order_number = $request->purchase_order_number;
    $lpj->purchase_date = $request->purchase_date;
    $lpj->pic = $request->pic;
    $lpj->note = $request->note;
    $lpj->currency = 'IDR'; // LPJ default-nya selalu menggunakan mata uang IDR
    $lpj->status = 'Selesai'; // Set status LPJ sebagai 'Selesai'
    $lpj->save();
    Log::info('LPJ created with ID: ' . $lpj->id);

    // Ambil semua Draft Purchase Orders yang dipilih
    Log::info('Fetching Draft POs...');
    $draftPOs = PurchaseOrders::whereIn('id', $purchaseOrderIds)->get();
    Log::info('Found ' . $draftPOs->count() . ' Draft POs to merge.');

    // Update setiap item di Draft PO agar purchase_order_id merujuk ke LPJ baru
    foreach ($draftPOs as $draftPO) {
      Log::info('Updating items and services for PO: ' . $draftPO->purchase_order_number);

      // Update semua items yang ada di PurchaseOrderItems, ganti purchase_order_id ke ID LPJ yang baru dibuat
      PurchaseOrderItems::where('purchase_order_id', $draftPO->id)
        ->update(['purchase_order_id' => $lpj->id]);

      Log::info('Updated items to point to LPJ ID: ' . $lpj->id);

      // Update semua services yang ada di PurchaseOrderServices, ganti purchase_order_id ke ID LPJ yang baru dibuat
      PurchaseOrderServices::where('purchase_order_id', $draftPO->id)
        ->update(['purchase_order_id' => $lpj->id]);  // Ganti ID PO jasa ke ID LPJ baru

      Log::info('Updated services to point to LPJ ID: ' . $lpj->id);

      // Hapus Draft PO setelah item-item dan services di-update
      $draftPO->delete();
      Log::info('Deleted draft PO with ID: ' . $draftPO->id);
    }

    // Log tindakan
    Logs::create([
      'user_id' => Auth::user()->id,
      'action' => 'created LPJ ' . $request->purchase_order_number . ' by merging draft Purchase Orders.',
    ]);
    Log::info('Action logged for LPJ.');

    // Redirect ke halaman sebelumnya dengan pesan sukses
    return redirect()->back()->with('swal-success', 'LPJ added successfully');
  }

  public function acceptPurchaseOrders(Request $request)
  {
    // dd($request->all());
    try {
      DB::beginTransaction();

      // Mengubah status Purchase Order menjadi "Menunggu Diproses"
      $purchaseOrders = PurchaseOrders::findOrFail($request->po_id);
      $purchaseOrders->status = "Menunggu Diproses";
      $purchaseOrders->save();

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
      }

      // Jika tidak ada item atau service, lempar pengecualian
      if ($purchaseOrderItems->isEmpty() && $purchaseOrderServices->isEmpty()) {
        throw new \Exception('Tidak ada item atau service untuk Purchase Order ini.');
      }

      // Mengambil Purchase Request ID dari service pertama yang memiliki relasi ke Purchase Request
      $purchaseRequestId = null;
      if (!$purchaseOrderItems->isEmpty() && $purchaseOrderItems->first()->purchaseRequestItems) {
        $purchaseRequestId = optional($purchaseOrderItems->first()->purchaseRequestItems)->purchase_request_id;
      } elseif (!$purchaseOrderServices->isEmpty()) {
        foreach ($purchaseOrderServices as $service) {
          // Hanya ambil purchase_request_id jika ada relasi dengan PurchaseRequestService
          if ($service->purchase_request_service_id) {
            $purchaseRequestService = PurchaseRequestServices::find($service->purchase_request_service_id);
            if ($purchaseRequestService) {
              $purchaseRequestId = $purchaseRequestService->purchase_request_id;
              break; // Keluar dari loop jika sudah menemukan purchase_request_id
            }
          }
        }
      }

      if ($purchaseRequestId) {
        // Mengambil semua items dan services terkait dalam Purchase Request
        $allPurchaseRequestItems = PurchaseRequestItems::where('purchase_request_id', $purchaseRequestId)->get();
        $allPurchaseRequestServices = PurchaseRequestServices::where('purchase_request_id', $purchaseRequestId)->get();

        // Pengecekan apakah semua Purchase Request Items dan Services berstatus "Diproses"
        $allProcessed = true;

        // Pengecekan untuk semua Purchase Request Items
        foreach ($allPurchaseRequestItems as $item) {
          if ($item->status !== 'Diproses') {
            $allProcessed = false;
            break;
          }
        }

        // Pengecekan untuk semua Purchase Request Services
        if ($allProcessed) {
          foreach ($allPurchaseRequestServices as $service) {
            if ($service->status !== 'Diproses') {
              $allProcessed = false;
              break;
            }
          }
        }

        // Update status Purchase Request
        $purchaseRequest = PurchaseRequests::find($purchaseRequestId);
        if ($purchaseRequest) {
          $purchaseRequest->status = $allProcessed ? 'Terproses' : 'Sebagian Diproses';
          $purchaseRequest->save();
        }
      }

      // Menambahkan log
      Logs::create([
        'user_id' => Auth::user()->id,
        'action' => 'accepted Purchase Order ' . $purchaseOrders->purchase_order_number . '.',
      ]);

      DB::commit();

      return redirect()->back()->with('swal-success', 'Purchase Order accepted successfully');
    } catch (\Exception $e) {
      DB::rollback();
      return redirect()->back()->with('swal-fail', 'Failed to accept Purchase Order: ' . $e->getMessage());
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
}
