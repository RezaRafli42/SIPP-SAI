<?php

namespace App\Http\Controllers;

use App\Models\ShipWarehouses as ShipWarehouses;
use App\Models\ShipWarehouseConditions as ShipWarehouseConditions;
use App\Models\ShipWarehouseUsages as ShipWarehouseUsages;
use App\Models\ShipWarehouseSendOffice as ShipWarehouseSendOffice;
use App\Models\OfficeWarehouse as OfficeWarehouse;
use App\Models\WarehouseHistory as WarehouseHistory;
use App\Models\Ships as Ships;
use App\Models\Items as Items;
use App\Models\InventoryTransfers as InventoryTransfers;
use App\Models\InventoryTransferItems as InventoryTransferItems;
use App\Models\Logs as Logs;
use App\Models\PurchaseRequests as PurchaseRequests;
use App\Models\PurchaseRequestItems as PurchaseRequestItems;
use App\Models\PurchaseOrders as PurchaseOrders;
use App\Models\PurchaseOrderItems as PurchaseOrderItems;
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
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DeliveryOrderExport;

class InventoryTransfersController extends Controller
{
  public function indexInventoryTransfers()
  {
    $ships = Ships::get();
    $shipType = $ships->unique('ship_type')->pluck('ship_type');

    return view('pages.inventoryTransfers', [
      'ships' => $ships,
      'shipType' => $shipType,
    ]);
  }

  public function loadDataInventoryTransfers($shipID, Request $request)
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

    // Build the query to fetch inventory transfers and associated item information
    $query = InventoryTransfers::join('inventory_transfer_items', 'inventory_transfers.id', '=', 'inventory_transfer_items.inventory_transfer_id')
      ->join('purchase_request_items', 'inventory_transfer_items.purchase_request_item_id', '=', 'purchase_request_items.id')
      ->join('items', 'purchase_request_items.item_id', '=', 'items.id')
      ->where('inventory_transfers.ship_id', $shipID)
      ->select('inventory_transfers.id as id', 'inventory_transfers.*')
      ->distinct();  // Select necessary columns and use distinct to avoid duplicates

    // Add search conditions if a keyword is provided
    if ($search) {
      $query->where(function ($q) use ($search) {
        $q->where('inventory_transfers.delivery_order_number', 'LIKE', "%$search%")
          ->orWhere('items.item_name', 'LIKE', "%$search%")
          ->orWhere('inventory_transfers.status', 'LIKE', "%$search%")
          ->orWhere('inventory_transfers.send_date', 'LIKE', "%$search%"); // Search by send_date (formatted date)
      });
    }

    // Add custom ordering for the status field and send_date
    $query->orderByRaw("
            FIELD(inventory_transfers.status, 'Dikirim Kantor', 'Diterima Kapal'),
            inventory_transfers.send_date DESC
        ");

    // Execute the query and get the results
    $inventoryTransfers = $query->get();

    // Render the HTML content for the response
    $html = view('pills.pillsInventoryTransfers', compact('inventoryTransfers'))->render();
    return response()->json(['html' => $html]);
  }

  public function getInventoryTransferItems($id)
  {
    // Cari ship_id berdasarkan purchase_order_id
    $ship_id = InventoryTransfers::find($id)->ship_id;

    // Ambil hanya item yang sesuai dengan purchase_order_id yang diberikan
    $items = InventoryTransferItems::where('inventory_transfer_items.inventory_transfer_id', $id)
      ->join('purchase_request_items', 'inventory_transfer_items.purchase_request_item_id', '=', 'purchase_request_items.id')
      ->join('purchase_requests', 'purchase_request_items.purchase_request_id', '=', 'purchase_requests.id')
      ->join('items', 'purchase_request_items.item_id', '=', 'items.id')
      ->leftJoin('ship_warehouses', function ($join) use ($ship_id) {
        $join->on('items.id', '=', 'ship_warehouses.item_id')
          ->where('ship_warehouses.ship_id', '=', $ship_id);
      })
      ->leftJoin('ship_warehouse_conditions', function ($join) {
        $join->on('ship_warehouses.id', '=', 'ship_warehouse_conditions.ship_warehouse_id')
          ->whereIn('ship_warehouse_conditions.condition', ['Baru', 'Bekas Bisa Pakai', 'Rekondisi']);
      })
      ->select(
        'items.item_pms',
        'items.item_name',
        'purchase_request_items.quantity',
        'items.item_unit',
        'inventory_transfer_items.condition',
        'purchase_requests.purchase_request_number',
        'purchase_requests.ship_id',
        'inventory_transfer_items.koli',
        'ship_warehouses.minimum_quantity',
        DB::raw('SUM(ship_warehouse_conditions.quantity) as total_quantity')
      )
      ->groupBy(
        'items.item_pms',
        'items.item_name',
        'purchase_request_items.quantity',
        'items.item_unit',
        'inventory_transfer_items.condition',
        'purchase_requests.purchase_request_number',
        'purchase_requests.ship_id',
        'inventory_transfer_items.koli',
        'ship_warehouses.minimum_quantity'
      )
      ->orderBy('inventory_transfer_items.koli')
      ->get();

    // Return the results as JSON
    return response()->json(['items' => $items]);
  }

  public function checkDeliveryOrderNumber(Request $request)
  {
    $DOno = $request->DOno;
    $exists = InventoryTransfers::where('delivery_order_number', $DOno)->exists();
    return response()->json(['exists' => $exists]);
  }

  public function addInventoryTransfers(Request $request)
  {
    // Mulai transaksi database
    DB::beginTransaction();

    try {
      $ship = Ships::where('ship_name', $request->ship_id)->first();

      $fileNames = []; // Array to hold all filenames
      // Handle file uploads, if any
      if ($request->hasFile('sender_photos')) {
        foreach ($request->file('sender_photos') as $index => $file) {
          $extension = $file->getClientOriginalExtension();
          $filename = uniqid() . '.' . $extension;  // Generate a unique filename
          $path = public_path('images/uploads/inventoryTransfers-photos/sender/' . $filename);

          $maxFileSize = 75000; // 75KB
          $fileSize = $file->getSize();

          $manager = new \Intervention\Image\ImageManager(['driver' => 'imagick']);

          if ($fileSize > $maxFileSize && in_array($extension, ['jpg', 'jpeg', 'png'])) {
            // Compress and save the image
            $quality = 75; // Start with 75% quality
            do {
              $image = $manager->make($file);
              $image->resize(800, null, function ($constraint) {
                $constraint->aspectRatio();
              })->save($path, $quality); // Save with current quality level

              $currentFileSize = filesize($path); // Get the file size after saving

              if ($currentFileSize > $maxFileSize) {
                $quality -= 5; // Reduce quality by 5% and try again
              }
            } while ($currentFileSize > $maxFileSize && $quality > 5); // Stop if file size is acceptable or quality is too low
          } else {
            // Save the document without compression
            $file->move(public_path('images/uploads/inventoryTransfers-photos/sender/'), $filename);
          }

          $fileNames[] = $filename; // Add the filename to the array
        }
      }

      // Buat Inv baru
      $inventoryTransfer = new InventoryTransfers();
      $inventoryTransfer->ship_id = $ship->id;
      $inventoryTransfer->delivery_order_number = $request->delivery_order_number;
      $inventoryTransfer->shipping_method = $request->shipping_method;
      $inventoryTransfer->sender_up = $request->sender_up;
      $inventoryTransfer->sender_title = $request->sender_title;
      $inventoryTransfer->sender_contact = $request->sender_contact;
      $inventoryTransfer->sender_photos = json_encode($fileNames);
      $inventoryTransfer->recipient_name = $request->recipient_name;
      $inventoryTransfer->recipient_project_position = $request->recipient_project_position;
      $inventoryTransfer->recipient_title = $request->recipient_title;
      $inventoryTransfer->recipient_up = $request->recipient_up;
      $inventoryTransfer->send_date = $request->send_date;

      $inventoryTransfer->save();

      $purchaseRequestId = null;

      foreach ($request->purchase_request_item_id as $index => $purchase_request_item_id) {
        $inventoryTransferItem = new InventoryTransferItems();
        $inventoryTransferItem->inventory_transfer_id = $inventoryTransfer->id;
        $inventoryTransferItem->purchase_request_item_id = $purchase_request_item_id;
        $inventoryTransferItem->condition = $request->condition[$index];
        $inventoryTransferItem->koli = $request->koli[$index];
        $inventoryTransferItem->save();

        $purchaseRequestItem = PurchaseRequestItems::find($purchase_request_item_id);
        $officeWarehouse = OfficeWarehouse::where('item_id', $purchaseRequestItem->item_id)
          ->where('condition', $request->condition[$index])
          ->first();

        if ($officeWarehouse) {
          $quantityBefore = $officeWarehouse->quantity;
          $newQuantity = $officeWarehouse->quantity - $purchaseRequestItem->quantity;

          // Cek apakah quantity menjadi kurang dari 0
          if ($newQuantity < 0) {
            // Batalkan seluruh transaksi dan berikan pesan error
            DB::rollBack();
            return redirect()->back()->with([
              'swal-fail-title' => 'Quantity Insufficient',
              'swal-fail-text' => 'The quantity in the Office Warehouse for item ' . $purchaseRequestItem->items->item_name . ' with condition ' . $request->condition[$index] . ' is insufficient'
            ]);
          }

          // Jika valid, kurangi quantity
          $officeWarehouse->quantity = $newQuantity;
          $officeWarehouse->save();

          WarehouseHistory::create([
            'warehouse_type' => 'office', // Karena ini dari gudang kantor
            'ship_id' => null, // Tidak terkait dengan kapal
            'item_id' => $purchaseRequestItem->item_id,
            'condition' => $request->condition[$index],
            'transaction_type' => 'Out', // Barang keluar dari gudang
            'source_or_destination' => $inventoryTransfer->delivery_order_number, // Nomor DO sebagai sumber
            'quantity_before' => $quantityBefore,
            'quantity_after' => $newQuantity,
            'transaction_date' => now(), // Tanggal saat transaksi
          ]);

          // Ubah status purchase_request_item menjadi "Dikirim Kantor"
          $purchaseRequestItem->status = 'Dikirim Kantor';
          $purchaseRequestItem->save();

          // Simpan purchase_request_id untuk pemeriksaan status nanti
          $purchaseRequestId = $purchaseRequestItem->purchase_request_id;
        }
      }

      if ($purchaseRequestId) {
        $purchaseRequest = PurchaseRequests::find($purchaseRequestId);

        // Cek apakah semua item sudah dikirim
        $allPurchaseRequestItems = $purchaseRequest->items;
        $allShipped = true;

        foreach ($allPurchaseRequestItems as $item) {
          if ($item->status !== 'Dikirim Kantor') {
            $allShipped = false;
            break;
          }
        }

        // Update status Purchase Request jika semua item sudah dikirim
        if ($allShipped) {
          $purchaseRequest->status = 'Dikirim Kantor';
          $purchaseRequest->save();
        }
      }

      Logs::create([
        'user_id' => Auth::user()->id,
        'action' => 'created Delivery Order ' . $request->input('delivery_order_number') . ' to the ' . $request->ship_id . ' Warehouse.',
      ]);

      // Commit transaksi jika semua berhasil
      DB::commit();

      return redirect()->back()->with('swal-success', 'Delivery Order added successfully');
    } catch (\Exception $e) {
      // Rollback transaksi jika terjadi kesalahan
      DB::rollBack();

      // Redirect dengan pesan error
      return redirect()->back()->with('swal-fail', 'Failed to add Delivery Order! ' . $e->getMessage());
    }
  }

  public function getPurchaseRequestDone(Request $request)
  {
    $shipName = $request->ship;

    // Cari ID kapal berdasarkan nama kapal
    $ship = Ships::where('ship_name', $shipName)->first();

    if ($ship) {
      // Query untuk mencari barang-barang dengan id_kapal yang sesuai
      $purchaseRequest = PurchaseRequests::where('ship_id', $ship->id)
        ->where(function ($query) {
          $query->where('status', 'Terproses')
            ->orWhere('status', 'Sebagian Diproses')
            ->orWhere('status', 'Menunggu Diproses');
        })
        ->get();

      return response()->json($purchaseRequest);
    } else {
      // Jika kapal tidak ditemukan, kembalikan respons kosong atau pesan kesalahan
      return response()->json(['message' => 'Ship not found.'], 404);
    }
  }

  public function updateInventoryTransfers(Request $request)
  {
    // Mulai transaksi database
    DB::beginTransaction();

    try {
      $item = $request->id;
      $inventoryTransfer = InventoryTransfers::find($item);

      if (!$inventoryTransfer) {
        throw new \Exception('Inventory Transfer not found');
      }

      $fileNames = json_decode($inventoryTransfer->recipient_photos, true) ?? []; // Existing photos

      // Handle recipient photos uploads, if any
      if ($request->hasFile('recipient_photos')) {
        foreach ($request->file('recipient_photos') as $index => $file) {
          $extension = $file->getClientOriginalExtension();
          $filename = uniqid() . '.' . $extension; // Generate a unique filename
          $path = public_path('images/uploads/inventoryTransfers-photos/recipient/' . $filename);

          $maxFileSize = 75000; // 75KB
          $fileSize = $file->getSize();

          $manager = new \Intervention\Image\ImageManager(['driver' => 'imagick']);

          if ($fileSize > $maxFileSize && in_array($extension, ['jpg', 'jpeg', 'png'])) {
            // Compress and save the image
            $quality = 75;
            do {
              $image = $manager->make($file);
              $image->resize(800, null, function ($constraint) {
                $constraint->aspectRatio();
              })->save($path, $quality); // Save with current quality level

              $currentFileSize = filesize($path);

              if ($currentFileSize > $maxFileSize) {
                $quality -= 5;
              }
            } while ($currentFileSize > $maxFileSize && $quality > 5);
          } else {
            $file->move(public_path('images/uploads/inventoryTransfers-photos/recipient/'), $filename);
          }

          $fileNames[] = $filename;
        }
      }

      // Handle delivery receipt file upload
      if ($request->hasFile('file')) {
        $deliveryReceiptFile = $request->file('file');
        $deliveryReceiptFileName = uniqid() . '.' . $deliveryReceiptFile->getClientOriginalExtension();
        $deliveryReceiptFile->move(public_path('files/uploads/inventoryTransfers-files/'), $deliveryReceiptFileName);

        // Delete old file if exists
        if ($inventoryTransfer->file) {
          $oldFilePath = public_path('files/uploads/inventoryTransfers-files/' . $inventoryTransfer->file);
          if (file_exists($oldFilePath)) {
            unlink($oldFilePath);
          }
        }

        $inventoryTransfer->file = $deliveryReceiptFileName;
      }

      // Update InventoryTransfer record
      $inventoryTransfer->recipient_photos = json_encode($fileNames);
      $inventoryTransfer->received_date = $request->received_date;
      $inventoryTransfer->status = 'Diterima Kapal'; // Set status to "Diterima Kapal"
      $inventoryTransfer->save();

      // Update related PurchaseRequestItems and ShipWarehouseConditions
      $purchaseRequestId = null;
      $inventoryTransferItems = InventoryTransferItems::where('inventory_transfer_id', $inventoryTransfer->id)->get();

      foreach ($inventoryTransferItems as $inventoryTransferItem) {
        $purchaseRequestItem = PurchaseRequestItems::find($inventoryTransferItem->purchase_request_item_id);
        if ($purchaseRequestItem) {
          // Update status to "Diterima Kapal"
          $purchaseRequestItem->status = 'Diterima Kapal';
          $purchaseRequestItem->save();

          // Update quantity in ShipWarehouseConditions
          $shipWarehouse = ShipWarehouses::where('item_id', $purchaseRequestItem->item_id)->first();

          if ($shipWarehouse) {
            $shipWarehouseCondition = ShipWarehouseConditions::where('ship_warehouse_id', $shipWarehouse->id)
              ->where('condition', $inventoryTransferItem->condition)
              ->first();

            if ($shipWarehouseCondition) {
              $quantityBefore = $shipWarehouseCondition->quantity; // Save quantity before update
              $shipWarehouseCondition->quantity += $purchaseRequestItem->quantity;
              $shipWarehouseCondition->save();

              // Add to Warehouse History
              WarehouseHistory::create([
                'warehouse_type' => 'ship',
                'ship_id' => $shipWarehouse->ship_id,
                'item_id' => $shipWarehouse->item_id,
                'condition' => $inventoryTransferItem->condition,
                'transaction_type' => 'In', // Item added to ship warehouse
                'source_or_destination' => $inventoryTransfer->delivery_order_number,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $shipWarehouseCondition->quantity,
                'transaction_date' => now(),
              ]);
            }
          }

          // Save purchase_request_id for status check
          $purchaseRequestId = $purchaseRequestItem->purchase_request_id;
        }
      }

      // Update PurchaseRequest status to "Selesai" if all items are received
      if ($purchaseRequestId) {
        $purchaseRequest = PurchaseRequests::find($purchaseRequestId);

        if ($purchaseRequest) {
          $allItemsReceived = $purchaseRequest->items->every(function ($item) {
            return $item->status === 'Diterima Kapal';
          });

          if ($allItemsReceived) {
            $purchaseRequest->status = 'Selesai';
            $purchaseRequest->save();
          }
        }
      }

      Logs::create([
        'user_id' => Auth::user()->id,
        'action' => 'accepted Delivery Order ' . $request->input('delivery_order_number') . '.',
      ]);

      // Commit transaksi
      DB::commit();

      return redirect()->back()->with('swal-success', 'Delivery Order accepted successfully');
    } catch (\Exception $e) {
      // Rollback transaksi jika terjadi kesalahan
      DB::rollBack();

      // Redirect dengan pesan error
      return redirect()->back()->with('swal-fail', 'Failed to accept Delivery Order! ' . $e->getMessage());
    }
  }

  public function printDeliveryReceipts($id)
  {
    // Cari InventoryTransfer berdasarkan ID
    $inventoryTransfer = InventoryTransfers::find($id);

    if (!$inventoryTransfer) {
      return redirect()->back()->with('swal-fail', 'Delivery Receipt not found');
    }

    // Dapatkan nama file dari database
    $fileName = $inventoryTransfer->file;

    if (!$fileName) {
      return redirect()->back()->with('swal-fail', 'No file associated with this Delivery Receipt');
    }

    // Path ke file di folder files/uploads/inventoryTransfers-files/
    $filePath = public_path('files/uploads/inventoryTransfers-files/' . $fileName);

    // Cek apakah file ada
    if (!file_exists($filePath)) {
      return redirect()->back()->with('swal-fail', 'File not found');
    }

    // Unduh file
    return response()->download($filePath, $fileName);
  }

  public function printDeliveryOrders(Request $request, $id)
  {
    return Excel::download(new DeliveryOrderExport($request->id), 'Delivery Receipt.xlsx');
  }
}
