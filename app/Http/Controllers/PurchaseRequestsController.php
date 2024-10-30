<?php

namespace App\Http\Controllers;

use App\Models\ShipWarehouses as ShipWarehouses;
use App\Models\ShipWarehouseConditions as ShipWarehouseConditions;
use App\Models\ShipWarehouseUsages as ShipWarehouseUsages;
use App\Models\ShipWarehouseSendOffice as ShipWarehouseSendOffice;
use App\Models\OfficeWarehouse as OfficeWarehouse;
use App\Models\Ships as Ships;
use App\Models\Services as Services;
use App\Models\Items as Items;
use App\Models\Logs as Logs;
use App\Models\PurchaseRequests as PurchaseRequests;
use App\Models\PurchaseRequestItems as PurchaseRequestItems;
use App\Models\PurchaseRequestServices as PurchaseRequestServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\File;
use App\Exports\PurchaseRequestExport;
use Maatwebsite\Excel\Facades\Excel;
use DateTime;


class PurchaseRequestsController extends Controller
{
  public function indexPurchaseRequests()
  {
    $ships = Ships::get();
    $items = Items::get();
    $shipType = $ships->unique('ship_type')->pluck('ship_type');

    return view('pages.purchaseRequests', [
      'ships' => $ships,
      'items' => $items,
      'shipType' => $shipType,
    ]);
  }

  public function generatePurchaseRequestNumber(Request $request)
  {
    $ship = $request->input('ship');
    // Dapatkan ID kapal berdasarkan nama kapal
    $shipRecord = Ships::where('ship_name', $ship)->first();
    if (!$shipRecord) {
      return response()->json(['error' => 'Ship not found'], 404);
    }
    $shipID = $shipRecord->id;
    // Ambil semua nomor PR untuk kapal tertentu
    $allNumbers = PurchaseRequests::where('ship_id', $shipID)
      ->orderBy('purchase_request_number')
      ->pluck('purchase_request_number')
      ->map(function ($number) {
        // Extract the numeric part of the purchase request number
        preg_match('/(\d{4})/', $number, $matches);
        return $matches[1] ?? null;
      })
      ->filter();
    // Pola dasar untuk nomor PR
    $codePattern = 'PR/SAI/';
    $year = date('Y'); // Mendapatkan tahun saat ini
    $month = date('m'); // Mendapatkan bulan saat ini
    // Loop untuk menemukan nomor yang unik
    for ($i = 1; $i <= count($allNumbers) + 1; $i++) {
      $newCodeNumber = str_pad($i, 4, '0', STR_PAD_LEFT); // Menambahkan nol di depan hingga 4 digit
      $newCode = $newCodeNumber . '/' . $codePattern . $month . '/' . $year . '/';
      if (!$allNumbers->contains($newCodeNumber)) {
        break;
      }
    }
    // Mengembalikan nomor PR yang baru sebagai respon JSON
    return response()->json(['purchase_request_number' => $newCode]);
  }

  public function loadDataPurchaseRequests($shipID, Request $request)
  {
    // Check if the ship exists
    $ship = Ships::find($shipID);
    if (!$ship) {
      return response()->json(['error' => 'Ship not found'], 404);
    }

    // Get the search keyword from the request
    $search = $request->input('search');

    // Check if the search query is in date format (DD/MM/YYYY)
    if ($search && preg_match('/\d{2}\/\d{2}\/\d{4}/', $search)) {
      // Convert from DD/MM/YYYY to YYYY-MM-DD
      $dateParts = explode('/', $search);
      if (count($dateParts) === 3) {
        $search = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0]; // Convert to YYYY-MM-DD
      }
    }

    // Subquery to calculate the total number of items for each purchase request
    $itemCountSubquery = DB::table('purchase_request_items')
      ->select('purchase_request_id', DB::raw('COUNT(*) as item_count'))
      ->groupBy('purchase_request_id');

    // Subquery to calculate the total number of services for each purchase request
    $serviceCountSubquery = DB::table('purchase_request_services')
      ->select('purchase_request_id', DB::raw('COUNT(*) as service_count'))
      ->groupBy('purchase_request_id');

    // Main query to fetch purchase requests, associated items, and services
    $query = PurchaseRequests::where('purchase_requests.ship_id', $shipID)
      ->leftJoinSub($itemCountSubquery, 'item_counts', function ($join) {
        $join->on('purchase_requests.id', '=', 'item_counts.purchase_request_id');
      })
      ->leftJoinSub($serviceCountSubquery, 'service_counts', function ($join) {
        $join->on('purchase_requests.id', '=', 'service_counts.purchase_request_id');
      })
      ->leftJoin('purchase_request_items', 'purchase_requests.id', '=', 'purchase_request_items.purchase_request_id')  // LEFT JOIN untuk items
      ->leftJoin('items', 'purchase_request_items.item_id', '=', 'items.id')  // LEFT JOIN untuk items details
      ->leftJoin('purchase_request_services', 'purchase_requests.id', '=', 'purchase_request_services.purchase_request_id')  // LEFT JOIN untuk services
      ->leftJoin('services', 'purchase_request_services.service_id', '=', 'services.id');  // LEFT JOIN untuk service details

    // Add search conditions if a keyword is provided
    if ($search) {
      $query->where(function ($q) use ($search) {
        $q->where('purchase_requests.purchase_request_number', 'LIKE', "%$search%")
          ->orWhere('items.item_name', 'LIKE', "%$search%")
          ->orWhere('services.service_name', 'LIKE', "%$search%")
          ->orWhere('purchase_request_services.utility', 'LIKE', "%$search%")
          ->orWhere('purchase_request_items.utility', 'LIKE', "%$search%")
          ->orWhere('purchase_requests.status', 'LIKE', "%$search%")
          ->orWhere('purchase_requests.request_date', 'LIKE', "%$search%"); // Search by request date
      });
    }

    // Add custom ordering for the status field and request_date
    $query->orderByRaw("
                FIELD(purchase_requests.status, 'Diajukan', 'Menunggu Diproses', 'Sebagian Diproses', 'Terproses', 'Dikirim Kantor', 'Selesai', 'Ditolak'),
                purchase_requests.request_date ASC
            ");

    // Execute the query and get the results
    $purchaseRequests = $query->select(
      'purchase_requests.*',
      DB::raw('IFNULL(item_counts.item_count, 0) + IFNULL(service_counts.service_count, 0) as item_count') // Sum of items and services
    )
      ->groupBy('purchase_requests.id') // Group by purchase request ID
      ->distinct()
      ->get();

    // Render the HTML content for the response
    $html = view('pills.pillsPurchaseRequests', compact('purchaseRequests'))->render();
    return response()->json(['html' => $html]);
  }



  public function getItemName(Request $request)
  {
    $shipName = $request->ship;

    // Cari ID kapal berdasarkan nama kapal
    $ship = Ships::where('ship_name', $shipName)->first();

    if ($ship) {
      $item = ShipWarehouses::where('ship_id', $ship->id)
        ->leftJoin('items', 'ship_warehouses.item_id', '=', 'items.id')
        ->select('ship_warehouses.*', 'items.*')
        ->get();
      return response()->json([
        'ship_items' => $item,
      ]);
    } else {
      // Jika kapal tidak ditemukan, kembalikan respons kosong atau pesan kesalahan
      return response()->json(['message' => 'Ship not found.'], 404);
    }
  }

  public function getServiceName(Request $request)
  {
    $service = Services::get();
    return response()->json([
      'service' => $service
    ]);
  }

  public function checkPurchaseRequestNumber(Request $request)
  {
    $PRno = $request->PRno;
    $exists = PurchaseRequests::where('purchase_request_number', $PRno)->exists();
    return response()->json(['exists' => $exists]);
  }

  public function addPurchaseRequests(Request $request)
  {
    // dd($request->all());
    // Mulai transaksi
    DB::beginTransaction();

    try {
      // Validasi jika nomor PR kosong
      if (empty($request->input('purchase_request_number'))) {
        return redirect()->back()->with('swal-fail', 'Please fill out the PR number fields');
      }

      // Ambil kapal berdasarkan ship_id yang diberikan
      $ship = Ships::where('ship_name', $request->input('ship_id'))->first();

      if (!$ship) {
        return redirect()->back()->with('swal-fail', 'Invalid ship name provided');
      }

      $fileNames = []; // Menyimpan nama file yang diunggah

      // Proses upload dokumen jika ada
      if ($request->hasFile('document')) {
        foreach ($request->file('document') as $index => $file) {
          $extension = $file->getClientOriginalExtension();
          $filename = uniqid() . '.' . $extension;  // Generate unique filename
          $path = public_path('images/uploads/purchaseRequests-photos/' . $filename);

          $maxFileSize = 75000; // Maksimal ukuran file 75KB
          $fileSize = $file->getSize();

          $manager = new \Intervention\Image\ImageManager(['driver' => 'imagick']);

          if ($fileSize > $maxFileSize && in_array($extension, ['jpg', 'jpeg', 'png'])) {
            // Kompres dan simpan gambar
            $quality = 75; // Awali dengan kualitas 75%
            do {
              $image = $manager->make($file);
              $image->resize(800, null, function ($constraint) {
                $constraint->aspectRatio();
              })->save($path, $quality); // Simpan dengan kualitas sekarang

              $currentFileSize = filesize($path); // Ambil ukuran file setelah disimpan

              if ($currentFileSize > $maxFileSize) {
                $quality -= 5; // Turunkan kualitas sebesar 5% dan ulangi
              }
            } while ($currentFileSize > $maxFileSize && $quality > 5); // Hentikan jika ukuran sudah sesuai atau kualitas terlalu rendah
          } else {
            // Simpan dokumen tanpa kompresi
            $file->move(public_path('images/uploads/purchaseRequests-photos/'), $filename);
          }

          $fileNames[] = $filename; // Tambahkan nama file ke array
        }
      }

      // Buat dan simpan data purchase request
      $purchaseRequest = new PurchaseRequests();
      $purchaseRequest->purchase_request_number = $request->input('purchase_request_number');
      $purchaseRequest->ship_id = $ship->id;
      $purchaseRequest->document = json_encode($fileNames); // Simpan nama file sebagai JSON
      $purchaseRequest->request_date = now();  // Set request date ke waktu sekarang
      $purchaseRequest->status = 'Diajukan';  // Set status default
      $purchaseRequest->save();

      // Cek apakah ada items atau services yang diinput
      $hasItems = $request->has('item_id');
      $hasServices = $request->has('service_id');

      if (!$hasItems && !$hasServices) {
        return redirect()->back()->with('swal-fail', 'Please select at least one item or service.');
      }

      // Simpan items ke purchase_request_items jika ada
      if ($hasItems && is_array($request->input('item_id'))) {
        foreach ($request->input('item_id') as $index => $itemId) {
          $item = Items::find($itemId);  // Asumsikan Anda punya model Items

          $purchaseRequestItem = new PurchaseRequestItems();
          $purchaseRequestItem->purchase_request_id = $purchaseRequest->id;
          $purchaseRequestItem->item_id = $itemId;
          $purchaseRequestItem->quantity = $request->input('quantity')[$index];
          $purchaseRequestItem->option = $request->input('option')[$index];
          $purchaseRequestItem->utility = $request->input('utility_items')[$index];
          $purchaseRequestItem->status = 'Diajukan';  // Set status default untuk items
          $purchaseRequestItem->save();
        }
      }

      // Simpan services ke purchase_request_services jika ada
      if ($hasServices) {
        foreach ($request->input('service_id') as $index => $serviceId) {
          $service = Services::find($serviceId);  // Asumsikan Anda punya model Services

          $purchaseRequestService = new PurchaseRequestServices();
          $purchaseRequestService->purchase_request_id = $purchaseRequest->id;
          $purchaseRequestService->service_id = $serviceId;
          $purchaseRequestService->utility = $request->input('utility_services')[$index];
          $purchaseRequestService->status = 'Diajukan';
          $purchaseRequestService->save();
        }
      }

      // Log penambahan PR
      Logs::create([
        'user_id' => Auth::user()->id,
        'action' => 'created Purchase Request ' . $request->input('purchase_request_number') . ' in the ' . $ship->ship_name . ' Warehouse.',
      ]);

      // Commit transaksi jika semuanya berhasil
      DB::commit();

      // Redirect back or to another page with success message
      return redirect()->back()->with('swal-success', 'Purchase Request added successfully');
    } catch (\Exception $e) {
      // Jika terjadi error, lakukan rollback semua perubahan
      DB::rollBack();

      // Kembalikan dengan error message
      return redirect()->back()->with('swal-fail', 'Failed to add Purchase Request: ' . $e->getMessage());
    }
  }

  public function getPurchaseRequestItems($id)
  {
    $ship_id = PurchaseRequests::find($id)->ship_id;

    // Query untuk items (sudah ada)
    $items = PurchaseRequestItems::join('items', 'purchase_request_items.item_id', '=', 'items.id')
      ->leftJoin('ship_warehouses', function ($join) use ($ship_id) {
        $join->on('ship_warehouses.item_id', '=', 'items.id')
          ->where('ship_warehouses.ship_id', '=', $ship_id);
      })
      ->leftJoin('ship_warehouse_conditions', function ($join) {
        $join->on('ship_warehouse_conditions.ship_warehouse_id', '=', 'ship_warehouses.id')
          ->whereIn('ship_warehouse_conditions.condition', ['Baru', 'Bekas Bisa Pakai', 'Rekondisi']);
      })
      ->where('purchase_request_items.purchase_request_id', $id)
      ->select(
        'items.id',
        'items.item_pms',
        'items.item_name',
        'purchase_request_items.quantity as quantity_needed',
        'items.item_unit',
        'purchase_request_items.option',
        'purchase_request_items.utility',
        'purchase_request_items.status',
        'ship_warehouses.minimum_quantity',
        DB::raw('IFNULL(SUM(ship_warehouse_conditions.quantity), 0) as total_quantity')  // Total available quantity
      )
      ->groupBy(
        'items.id',
        'items.item_pms',
        'items.item_name',
        'purchase_request_items.quantity',
        'items.item_unit',
        'purchase_request_items.option',
        'purchase_request_items.utility',
        'purchase_request_items.status',
        'ship_warehouses.minimum_quantity',
        'purchase_request_items.id'
      )
      ->get();

    // Query untuk services (jasa)
    $services = PurchaseRequestServices::join('services', 'purchase_request_services.service_id', '=', 'services.id')
      ->where('purchase_request_services.purchase_request_id', $id)
      ->select(
        'services.id',
        'services.service_code',
        'services.service_name',
        'purchase_request_services.utility',
        'purchase_request_services.status',
      )
      ->get();

    // Return the results as JSON, combining items and services
    return response()->json([
      'items' => $items,
      'services' => $services
    ]);
  }

  public function acceptPurchaseRequests(Request $request)
  {
    // Temukan Purchase Request yang diminta
    $purchaseRequest = PurchaseRequests::findOrFail($request->pr_id);

    // Ubah status dari Purchase Request ke "Menunggu Diproses"
    $purchaseRequest->status = "Menunggu Diproses";
    $purchaseRequest->save();

    // 1. Ubah status Purchase Request Items
    $purchaseRequestItems = PurchaseRequestItems::where('purchase_request_id', $request->pr_id)->get();
    foreach ($purchaseRequestItems as $item) {
      $item->status = "Menunggu Diproses";
      $item->save();
    }

    // 2. Ubah status Purchase Request Services (Jasa)
    $purchaseRequestServices = PurchaseRequestServices::where('purchase_request_id', $request->pr_id)->get();
    foreach ($purchaseRequestServices as $service) {
      $service->status = "Menunggu Diproses";
      $service->save();
    }

    // 3. Log aksi perubahan
    $ship = Ships::findOrFail($purchaseRequest->ship_id);
    Logs::create([
      'user_id' => Auth::user()->id,
      'action' => 'accepted Purchase Request ' . $purchaseRequest->purchase_request_number . ' in the ' . $ship->ship_name . ' Warehouse.',
    ]);

    // 4. Redirect dan berikan pesan sukses
    return redirect()->back()->with('swal-success', 'Purchase Request accepted successfully');
  }

  public function rejectPurchaseRequests(Request $request)
  {
    // Temukan Purchase Request yang diminta
    $purchaseRequest = PurchaseRequests::findOrFail($request->pr_id);

    // Ubah status dari Purchase Request ke "Ditolak"
    $purchaseRequest->status = "Ditolak";
    $purchaseRequest->save();

    // 1. Ubah status Purchase Request Items menjadi "Ditolak"
    $purchaseRequestItems = PurchaseRequestItems::where('purchase_request_id', $request->pr_id)->get();
    foreach ($purchaseRequestItems as $item) {
      $item->status = "Ditolak";
      $item->save();
    }

    // 2. Ubah status Purchase Request Services (Jasa) menjadi "Ditolak"
    $purchaseRequestServices = PurchaseRequestServices::where('purchase_request_id', $request->pr_id)->get();
    foreach ($purchaseRequestServices as $service) {
      $service->status = "Ditolak";
      $service->save();
    }

    // 3. Log aksi penolakan
    $ship = Ships::findOrFail($purchaseRequest->ship_id);
    Logs::create([
      'user_id' => Auth::user()->id,
      'action' => 'rejected Purchase Request ' . $purchaseRequest->purchase_request_number . ' in the ' . $ship->ship_name . ' Warehouse.',
    ]);

    // 4. Redirect dan berikan pesan sukses
    return redirect()->back()->with('swal-success', 'Purchase Request rejected successfully');
  }

  public function getAutomaticPurchaseRequests(Request $request)
  {
    $ship_id = $request->input('ship_id');

    // Mengambil item dari ship_warehouses yang quantity-nya di bawah minimum_quantity
    $items = DB::table('ship_warehouses')
      ->join('items', 'ship_warehouses.item_id', '=', 'items.id')
      ->leftJoin('ship_warehouse_conditions', 'ship_warehouse_conditions.ship_warehouse_id', '=', 'ship_warehouses.id')
      ->whereIn('ship_warehouse_conditions.condition', ['Baru', 'Bekas Bisa Pakai', 'Rekondisi'])
      ->where('ship_warehouses.ship_id', $ship_id)
      ->select(
        'items.id',
        'items.item_pms',
        'items.item_name',
        'items.item_unit',
        'ship_warehouses.minimum_quantity',
        DB::raw('SUM(ship_warehouse_conditions.quantity) as total_quantity'),
        DB::raw('ship_warehouses.minimum_quantity - SUM(ship_warehouse_conditions.quantity) as quantity_needed')
      )
      ->groupBy(
        'items.id',
        'items.item_pms',
        'items.item_name',
        'items.item_unit',
        'ship_warehouses.minimum_quantity'
      )
      ->havingRaw('total_quantity < ship_warehouses.minimum_quantity')
      ->get();

    // Return the results as JSON
    return response()->json(['items' => $items]);
  }

  public function exportPurchaseRequests(Request $request)
  {
    $month = $request->input('month');
    $year = $request->input('year');
    $ship = Ships::where('ship_name', $request->input('ship_id'))->first();
    $shipID = $ship->id;
    return Excel::download(new PurchaseRequestExport($month, $year, $shipID), 'Purchase Requests_' . $ship->ship_name . '_' . $month . '_' . $year . '.xlsx');
  }

  public function statisticPurchaseRequests($shipID)
  {
    $pendingCount = PurchaseRequests::where('ship_id', $shipID)->where('status', 'Diajukan')->count();
    $progressCount = PurchaseRequests::where('ship_id', $shipID)
      ->whereIn('status', ['Menunggu Diproses', 'Sebagian Diproses', 'Terproses'])
      ->count();
    $finishedCount = PurchaseRequests::where('ship_id', $shipID)->where('status', 'Selesai')->count();
    $rejectedCount = PurchaseRequests::where('ship_id', $shipID)->where('status', 'Ditolak')->count();
    return response()->json([
      'pending' => $pendingCount,
      'progress' => $progressCount,
      'finished' => $finishedCount,
      'rejected' => $rejectedCount,
    ]);
  }
}
