<?php

namespace App\Http\Controllers;

use App\Models\ShipWarehouseUsages as ShipWarehouseUsages;
use App\Models\OfficeWarehouse as OfficeWarehouse;
use App\Models\InventoryTransfers as InventoryTransfers;
use App\Models\Logs as Logs;
use App\Models\PurchaseRequests as PurchaseRequests;
use App\Models\PurchaseOrders as PurchaseOrders;
use App\Models\PurchaseOrderItems as PurchaseOrderItems;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use DateTime;

class DashboardController extends Controller
{
  public function indexDashboard(Request $request)
  {
    $currentMonth = Carbon::now()->month;
    $currentYear = Carbon::now()->year;

    // DATA LOG
    $search = $request->input('searchLog');
    $logs = Logs::with('users') // Eager load the user relationship
      ->orderBy('time', 'desc')
      ->when($search, function ($query, $search) {
        return $query->where('action', 'like', '%' . $search . '%');
      })
      ->limit(150)
      ->get();
    // END DATA LOG

    // DATA TOTAL PR
    $totalPR = PurchaseRequests::whereMonth('request_date', $currentMonth)
      ->whereYear('request_date', $currentYear)
      ->count();
    // END DATA TOTAL PR

    // DATA TOTAL PO
    $totalPO = PurchaseOrders::whereMonth('purchase_date', $currentMonth)
      ->whereYear('purchase_date', $currentYear)
      ->count();
    // END DATA TOTAL PO

    // DATA TOTAL IT
    $totalIT = InventoryTransfers::count();
    // END DATA TOTAL IT

    // DATA TOTAL PR DONE
    $totalPRDone = PurchaseRequests::where('status', 'Selesai')->count();
    $allPRs = PurchaseRequests::count();
    // END DATA TOTAL PR DONE

    // DATA OFFICE WAREHOUSE
    $officeWarehouse = OfficeWarehouse::join('items', 'office_warehouse.item_id', '=', 'items.id')
      ->select(
        'items.*',
        'office_warehouse.item_id',
        DB::raw('SUM(office_warehouse.quantity) as total_quantity')
      )
      ->where('office_warehouse.condition', '!=', 'Bekas Tidak Bisa Pakai')
      ->orderBy('items.item_pms', 'asc')
      ->groupBy('office_warehouse.item_id', 'items.id')
      ->get();
    // END DATA OFFICE WAREHOUSE

    // DATA USAGE ITEM
    $usageItem = ShipWarehouseUsages::join('ship_warehouse_conditions', 'ship_warehouse_usages.ship_warehouse_condition_id', '=', 'ship_warehouse_conditions.id')
      ->join('ship_warehouses', 'ship_warehouse_conditions.ship_warehouse_id', '=', 'ship_warehouses.id')
      ->join('items', 'ship_warehouses.item_id', '=', 'items.id')
      ->select(
        'items.item_name',
        DB::raw('SUM(ship_warehouse_usages.quantity_used) as total_used')
      )
      ->groupBy('items.item_name')
      ->orderBy('total_used', 'desc')
      ->limit(5)
      ->get();
    // END DATA USAGE ITEM

    // DATA PR PER MONTH
    $monthlyRequests = DB::table('purchase_requests')
      ->select(
        DB::raw('YEAR(request_date) as year'),
        DB::raw('MONTH(request_date) as month'),
        DB::raw('COUNT(*) as total_requests')
      )
      ->where('request_date', '>=', DB::raw('DATE_SUB(CURDATE(), INTERVAL 4 MONTH)'))
      ->groupBy('year', 'month')
      ->orderBy('year', 'asc')
      ->orderBy('month', 'asc')
      ->get();

    // Prepare data for chart
    $months = [];
    $totalPRs = [];
    foreach ($monthlyRequests as $request) {
      // Format the month and year (e.g., "Jan 2024")
      $months[] = DateTime::createFromFormat('!m', $request->month)->format('M') . ' ' . $request->year;
      $totalPRs[] = $request->total_requests;
    }

    // END DATA PR PER MONTH

    // DATA SHIP WITH MOST PR
    $topShips = PurchaseRequests::join('ships', 'purchase_requests.ship_id', '=', 'ships.id')
      ->select(
        'ships.ship_name',
        DB::raw('COUNT(purchase_requests.id) as total_pr'),
        DB::raw('SUM(CASE WHEN purchase_requests.status = "Selesai" THEN 1 ELSE 0 END) as selesai_pr')
      )
      ->groupBy('ships.ship_name')
      ->orderBy('total_pr', 'desc')
      ->limit(4)
      ->get();
    // END DATA SHIP WITH MOST PR

    // DATA PURCHASE ORDER ITEMS FOR TOTAL SPEND THIS MONTH
    $purchaseOrders = PurchaseOrderItems::join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
      ->whereMonth('purchase_orders.purchase_date', $currentMonth)
      ->whereYear('purchase_orders.purchase_date', $currentYear)
      ->whereIn('purchase_order_items.status', ['Menunggu Diproses', 'Selesai'])
      ->select('purchase_order_items.price', 'purchase_order_items.quantity', 'purchase_orders.currency', 'purchase_orders.purchase_date')
      ->get();
    // END DATA PURCHASE ORDER ITEMS FOR TOTAL SPEND THIS MONTH

    // DATA TOP SPEND SUPPLIER
    $topSuppliers = PurchaseOrders::join('suppliers', 'purchase_orders.supplier_id', '=', 'suppliers.id')
      ->join('purchase_order_items', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
      ->select(
        'suppliers.id as supplier_id',
        'suppliers.supplier_name',
        'suppliers.supplier_city',
        'suppliers.supplier_country',
        'suppliers.supplier_address',
        'purchase_orders.currency',
        'purchase_orders.purchase_date',
        'purchase_order_items.quantity',  // Include quantity
        'purchase_order_items.price' // Include unit price for calculating in JS
      )
      ->where('purchase_orders.status', '!=', 'Diajukan')
      ->whereMonth('purchase_orders.purchase_date', $currentMonth)
      ->whereYear('purchase_orders.purchase_date', $currentYear)
      ->get();

    // dd($topSuppliers);
    // END DATA TOP SPEND SUPPLIER

    // DATA TOP SPEND SUPPLIER WITH ITEMS
    $suppliersWithItems = $topSuppliers->map(function ($supplier) {
      $items = PurchaseOrderItems::join('purchase_orders', 'purchase_order_items.purchase_order_id', '=', 'purchase_orders.id')
        ->join('purchase_request_items', 'purchase_order_items.purchase_request_item_id', '=', 'purchase_request_items.id')
        ->join('items', 'purchase_request_items.item_id', '=', 'items.id')
        ->where('purchase_orders.supplier_id', $supplier->supplier_id)
        ->select(
          'items.item_name',
          DB::raw('SUM(purchase_order_items.quantity) as total_quantity')
        )
        ->groupBy('items.item_name')
        ->orderBy('total_quantity', 'desc')
        ->limit(5) // Mengambil 5 item terbanyak dari supplier ini
        ->get();
      // Menambahkan data item ke supplier
      $supplier->items = $items;
      return $supplier;
      // dd($supplier);
    });
    // END DATA TOP SUPPLIER WITH ITEMS

    return view('dashboard', [
      'totalPR' => $totalPR,
      'totalPO' => $totalPO,
      'purchaseOrders' => $purchaseOrders,
      'topShips' => $topShips,
      'topSuppliers' => $topSuppliers,
      'suppliersWithItems' => $suppliersWithItems,
      'logs' => $logs,
      'officeWarehouse' => $officeWarehouse,
      'usageItem' => $usageItem,
      'monthlyRequests' => $monthlyRequests,
      'months' => $months,
      'totalPRs' => $totalPRs,
      'totalIT' => $totalIT,
      'totalPRDone' => $totalPRDone,
      'allPRs' => $allPRs,
    ]);
  }

  public function searchLogs(Request $request)
  {
    $search = $request->input('searchLog');

    // Get Log dengan fitur pencarian
    $logs = Logs::orderBy('time', 'asc')
      ->when($search, function ($query, $search) {
        return $query->where('action', 'like', '%' . $search . '%');
      })
      ->get();

    $logEntries = [];
    $actionKeywords = ['created', 'updated', 'deleted', 'used', 'received', 'rejected', 'sent', 'imported', 'accepted', 'data', 'added', 'approve'];

    foreach ($logs as $log) {
      $logEntry = $log->log;
      $words = explode(" ", $logEntry);
      $user = [];
      $action = [];

      foreach ($words as $word) {
        if (in_array(strtolower($word), $actionKeywords)) {
          $action = array_slice($words, array_search($word, $words));
          break;
        } else {
          $user[] = $word;
        }
      }

      $userName = implode(" ", $user);

      // Cari foto profil berdasarkan nama user
      $userProfile = User::where('name', $userName)->first();
      $userImage = $userProfile ? $userProfile->profile_photo : 'default.jpg'; // Jika tidak ditemukan, gunakan gambar default

      // Format waktu log menggunakan Carbon
      $formattedTime = Carbon::parse($log->time)->format('H:i d/m/Y');

      // Tambahkan hasil yang dipisahkan ke dalam array logEntries
      $logEntries[] = [
        'user' => $userName,
        'image' => $userImage,
        'action' => implode(" ", $action),
        'time' => $formattedTime,
      ];
    }

    // Kembalikan view yang menampilkan hasil pencarian log
    return view('search.searchLogDashboard', compact('logEntries'))->render();
  }
}
