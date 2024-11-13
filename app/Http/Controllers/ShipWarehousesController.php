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
use App\Models\Logs as Logs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ShipWarehousesController extends Controller
{
  public function addShipWarehouses(Request $request)
  {
    // Find the ship by name
    $ship = Ships::where('ship_name', $request->ship_id)->first();

    if (!$ship) {
      return redirect()->back()->with('swal-fail', 'Ship not found');
    }

    // Find the item by id
    $item = Items::find($request->item_id);

    if (!$item) {
      return redirect()->back()->with('swal-fail', 'Item not found');
    }

    $shipsID = Ships::where('ship_name', $request->ship_id)->first();
    $shipWarehouse = ShipWarehouses::create([
      'ship_id' => $shipsID->id,
      'item_id' => $request->item_id,
      'minimum_quantity' => $request->minimum_quantity,
      'department' => $request->department,
      'position_date' => $request->position_date,
      'equipment_category' => $request->equipment_category,
      'tool_category' => $request->tool_category,
      'type' => $request->type,
      'certification' => $request->certification,
      'last_maintenance_date' => $request->last_maintenance_date,
      'last_inspection_date' => $request->last_inspection_date,
      'description' => $request->description,
    ]);

    $conditions = ['Baru', 'Bekas Bisa Pakai', 'Bekas Tidak Bisa Pakai', 'Rekondisi'];
    foreach ($conditions as $condition) {
      ShipWarehouseConditions::create([
        'ship_warehouse_id' => $shipWarehouse->id,
        'condition' => $condition,
        'quantity' => 0,
        'location' => '', // atau Anda dapat memasukkan lokasi lain jika berbeda
      ]);
    }

    Logs::create([
      'user_id' => Auth::user()->id,
      'action' => 'added data ' . $item->item_name . ' (' . $item->item_pms . ') to the ' . $ship->ship_name . ' Warehouse.',
    ]);

    return redirect()->back()->with('swal-success', 'Item added to Ship Warehouse successfully');
  }

  public function updateShipWarehouses(Request $request)
  {
    // Find the ship warehouse record
    $shipWarehouse = ShipWarehouses::find($request->id);
    // Find the ship by name
    $ship = Ships::where('ship_name', $request->ship_id)->first();
    if (!$ship) {
      return redirect()->back()->with('swal-fail', 'Ship not found');
    }
    // Find the item by id
    $item = Items::find($request->item_id);
    if (!$item) {
      return redirect()->back()->with('swal-fail', 'Item not found');
    }

    // Update the ship warehouse data
    $shipWarehouse->update([
      'minimum_quantity' => $request->minimum_quantity,
      'department' => $request->department,
      'position_date' => $request->position_date,
      'equipment_category' => $request->equipment_category,
      'tool_category' => $request->tool_category,
      'type' => $request->type,
      'certification' => $request->certification,
      'last_maintenance_date' => $request->last_maintenance_date,
      'last_inspection_date' => $request->last_inspection_date,
      'description' => $request->description,
    ]);

    Logs::create([
      'user_id' => Auth::user()->id,
      'action' => 'updated data ' . $item->item_name . ' (' . $item->item_pms . ')  in the ' . $ship->ship_name . ' Warehouse.',
    ]);

    return redirect()->back()->with('swal-success', 'Data updated successfully');
  }

  public function updateShipWarehouseCondition(Request $request)
  {
    // Find the ship warehouse condition by ID
    $shipWarehouseCondition = ShipWarehouseConditions::find($request->condition_id);

    // Retrieve related ship and item details
    $shipWarehouse = ShipWarehouses::find($shipWarehouseCondition->ship_warehouse_id);
    $ship = Ships::find($shipWarehouse->ship_id);
    $item = Items::find($shipWarehouse->item_id);

    // Update ship warehouse condition details
    $shipWarehouseCondition->update([
      'quantity' => $request->quantity,
      'location' => $request->location,
    ]);

    // Log the update
    Logs::create([
      'user_id' => Auth::user()->id,
      'action' => 'updated data ' . $item->item_name . ' (' . $item->item_pms . ') with condition ' . $shipWarehouseCondition->condition . ' in the ' . $ship->ship_name . ' Warehouse.',
    ]);

    // Redirect back with success message
    return redirect()->back()->with('swal-success', 'Ship warehouse data updated successfully');
  }

  public function deleteShipWarehouses($id)
  {
    // Find the ship warehouse by ID
    $shipWarehouse = ShipWarehouses::findOrFail($id);

    // Retrieve related ship and item details
    $ship = Ships::find($shipWarehouse->ship_id);
    $item = Items::find($shipWarehouse->item_id);

    // Retrieve all conditions related to the ship warehouse
    $conditions = ShipWarehouseConditions::where('ship_warehouse_id', $id)->get();

    // Loop through each condition to delete dependent records in all related tables
    foreach ($conditions as $condition) {
      // Delete records in ship_warehouse_usages
      ShipWarehouseUsages::where('ship_warehouse_condition_id', $condition->id)->delete();
      // Delete records in ship_warehouse_send_office
      ShipWarehouseSendOffice::where('ship_warehouse_condition_id', $condition->id)->delete();
    }

    // Delete the associated conditions
    ShipWarehouseConditions::where('ship_warehouse_id', $id)->delete();

    // Delete the ship warehouse
    $shipWarehouse->delete();

    // Log the deletion
    Logs::create([
      'user_id' => Auth::user()->id,
      'action' => 'deleted data ' . $item->item_name . ' (' . $item->item_pms . ') from the ' . $ship->ship_name . 'Warehouse.',
    ]);

    // Redirect back with success message
    return redirect()->back()->with('swal-success', 'Ship warehouse deleted successfully');
  }

  public function addShipWarehouseUsages(Request $request)
  {
    // Ambil data condition berdasarkan id
    $condition = ShipWarehouseConditions::find($request->condition_id);

    // Retrieve related ship and item details
    $shipWarehouse = ShipWarehouses::find($condition->ship_warehouse_id);
    $ship = Ships::find($shipWarehouse->ship_id);
    $item = Items::find($shipWarehouse->item_id);

    // Proses unggah foto jika ada
    if ($request->hasFile('photo')) {
      $file = $request->file('photo');
      $extension = $file->getClientOriginalExtension();
      $filename = uniqid() . '.' . $extension;
      $path = public_path('images/uploads/shipWarehouseUsage-photos/' . $filename);
      // Set maximum file size to 150KB (150,000 bytes)
      $maxFileSize = 150000; // 150KB
      // Get file size
      $fileSize = $file->getSize();
      // Create new ImageManager instance with imagick driver
      $manager = new ImageManager(['driver' => 'imagick']);
      if ($fileSize > $maxFileSize) {
        // Compress and save the image
        $image = $manager->make($file);
        $image->resize(800, null, function ($constraint) {
          $constraint->aspectRatio();
        })->save($path, 75); // Save with 75% quality
      } else {
        // Save the image without compression
        $file->move(public_path('images/uploads/shipWarehouseUsage-photos/'), $filename);
      }
      $photoPath = $filename;
    } else {
      $photoPath = null;
    }

    // Buat transaksi baru
    ShipWarehouseUsages::create([
      'ship_warehouse_condition_id' => $condition->id,
      'quantity_used' => $request->quantity_used,
      'used_item_condition' => $request->used_item_condition,
      'usage_date' => $request->usage_date,
      'description' => $request->description,
      'pic' => $request->pic,
      'photo' => $photoPath,
      'status' => '', // assuming you want to set a default status
    ]);

    // Kurangi jumlah item di kondisi yang sesuai
    $condition->quantity -= $request->quantity_used;
    $condition->save();

    // Tambah barang di gudang berdasarkan kondisi barang lama yang diganti
    $newCondition = ShipWarehouseConditions::where('ship_warehouse_id', $condition->ship_warehouse_id)
      ->where('condition', $request->used_item_condition)
      ->first();

    if ($newCondition) {
      $newCondition->quantity += $request->quantity_used;
      $newCondition->save();
    } else {
      // Jika kondisi tidak ada, buat baru
      ShipWarehouseConditions::create([
        'ship_warehouse_id' => $condition->ship_warehouse_id,
        'condition' => $request->used_item_condition,
        'quantity' => $request->quantity_used,
      ]);
    }

    // Create log entry
    Logs::create([
      'user_id' => Auth::user()->id,
      'action' => 'used ' . $item->item_name . ' (' . $item->item_pms . ') with condition ' . $newCondition->condition . ' from the ' . $ship->ship_name . ' Warehouse.',
    ]);

    return redirect()->back()->with('swal-success', 'Usage recorded successfully');
  }

  public function addAdjustmentShipWarehouseUsages(Request $request)
  {
    // Cari ship_warehouse berdasarkan item_id
    $shipWarehouse = ShipWarehouses::where('item_id', $request->item_id)->first();

    if (!$shipWarehouse) {
      return redirect()->back()->with('swal-fail', 'Ship warehouse not found');
    }

    // Cari kondisi berdasarkan ship_warehouse_id dan condition dari request
    $condition = ShipWarehouseConditions::where('ship_warehouse_id', $shipWarehouse->id)
      ->where('condition', $request->condition)
      ->first();

    if (!$condition) {
      return redirect()->back()->with('swal-fail', 'Condition not found');
    }

    // Retrieve related ship and item details
    $ship = Ships::find($shipWarehouse->ship_id);
    $item = Items::find($shipWarehouse->item_id);

    // Buat transaksi baru, tanpa mengurangi quantity di sini
    ShipWarehouseUsages::create([
      'ship_warehouse_condition_id' => $condition->id, // Kondisi berdasarkan item_id dan condition
      'quantity_used' => $request->quantity_used,
      'used_item_condition' => $request->used_item_condition,
      'usage_date' => $request->usage_date,
      'description' => $request->description,
      'pic' => $request->pic,
      'note' => 'Adjustment',
      'status' => 'Diajukan',
    ]);

    // Jangan kurangi quantity di sini. Tunggu sampai konfirmasi.

    // Create log entry
    Logs::create([
      'user_id' => Auth::user()->id,
      'action' => 'submitted an adjustment for ' . $item->item_name . ' (' . $item->item_pms . ') with condition ' . $condition->condition . ' from the ' . $ship->ship_name . ' Warehouse.',
    ]);

    return redirect()->back()->with('swal-success', 'Usage adjustment submitted successfully');
  }

  public function confirmAdjustmentShipWarehouseUsage($id)
  {
    // Find the usage record
    $dataUsage = ShipWarehouseUsages::find($id);

    if (!$dataUsage) {
      return redirect()->back()->with('swal-fail', 'Data usage not found');
    }

    if ($dataUsage->status == 'Disetujui') {
      return redirect()->back()->with('swal-fail', 'Item already approved');
    }

    // Retrieve related ship and item details
    $ship = Ships::find($dataUsage->shipWarehouseConditions->shipWarehouses->ship_id);
    $item = Items::find($dataUsage->shipWarehouseConditions->shipWarehouses->item_id);

    // Update the usage record to confirmed
    $dataUsage->status = 'Disetujui';
    $dataUsage->save();

    // Kurangi jumlah item di kondisi yang sesuai saat disetujui
    $condition = $dataUsage->shipWarehouseConditions;
    $condition->quantity -= $dataUsage->quantity_used;
    $condition->save();

    // Tambahkan quantity item di kondisi barang baru
    $newCondition = ShipWarehouseConditions::where('ship_warehouse_id', $condition->ship_warehouse_id)
      ->where('condition', $dataUsage->used_item_condition)
      ->first();

    if ($newCondition) {
      $newCondition->quantity += $dataUsage->quantity_used;
      $newCondition->save();
    } else {
      // Jika kondisi tidak ada, buat baru
      ShipWarehouseConditions::create([
        'ship_warehouse_id' => $condition->ship_warehouse_id,
        'condition' => $dataUsage->used_item_condition,
        'quantity' => $dataUsage->quantity_used,
      ]);
    }

    // Create log entry
    Logs::create([
      'user_id' => Auth::user()->id,
      'action' => 'approved usage adjustment for ' . $item->item_name . ' (' . $item->item_pms . ') with condition ' . $dataUsage->used_item_condition . ' in the ' . $ship->ship_name . ' Warehouse.',
    ]);

    return redirect()->back()->with('swal-success', 'Items approved successfully');
  }

  public function addShipWarehouseSendOffice(Request $request)
  {
    // Ambil data condition berdasarkan id
    $condition = ShipWarehouseConditions::find($request->condition_id);

    if (!$condition) {
      return redirect()->back()->with('swal-fail', 'Condition not found');
    }

    // Ambil data dari ship_warehouse berdasarkan ship_warehouse_id dari condition
    $shipWarehouse = ShipWarehouses::find($condition->ship_warehouse_id);

    if (!$shipWarehouse) {
      return redirect()->back()->with('swal-fail', 'Ship Warehouse not found');
    }

    // Retrieve related ship and item details
    $ship = Ships::find($shipWarehouse->ship_id);
    $item = Items::find($shipWarehouse->item_id);

    // Proses unggah foto jika ada
    if ($request->hasFile('photo')) {
      $file = $request->file('photo');
      $extension = $file->getClientOriginalExtension();
      $filename = uniqid() . '.' . $extension;
      $path = public_path('images/uploads/shipWarehouseSendOffice-photos/' . $filename);
      // Set maximum file size to 150KB (150,000 bytes)
      $maxFileSize = 150000; // 150KB
      // Get file size
      $fileSize = $file->getSize();
      // Create new ImageManager instance with imagick driver
      $manager = new ImageManager(['driver' => 'imagick']);
      if ($fileSize > $maxFileSize) {
        // Compress and save the image
        $image = $manager->make($file);
        $image->resize(800, null, function ($constraint) {
          $constraint->aspectRatio();
        })->save($path, 75); // Save with 75% quality
      } else {
        // Save the image without compression
        $file->move(public_path('images/uploads/shipWarehouseSendOffice-photos/'), $filename);
      }
      $photoPath = $filename;
    } else {
      $photoPath = null;
    }

    // Buat transaksi baru
    $shipWarehouseSendOffice = ShipWarehouseSendOffice::create([
      'ship_warehouse_condition_id' => $condition->id,
      'quantity_send' => $request->quantity_send,
      'send_date' => $request->send_date,
      'pic' => $request->pic,
      'description' => $request->description,
      'photo' => $photoPath,
      'status' => 'Send by Ship', // assuming you want to set a default status
    ]);

    // Kurangi jumlah item di kondisi yang sesuai
    $condition->quantity -= $request->quantity_send;
    $condition->save();

    // Create log entry
    Logs::create([
      'user_id' => Auth::user()->id,
      'action' => 'sent ' . $item->item_name . ' (' . $item->item_pms . ') with condition ' . $condition->condition . ' from the ' . $ship->ship_name . ' Warehouse to the Office Warehouse.',
    ]);

    return redirect()->back()->with('swal-success', 'Send item to Office successfully');
  }

  public function addAdjustmentShipWarehouseSendOffice(Request $request)
  {
    // Ambil data condition berdasarkan id
    $condition = ShipWarehouseConditions::find($request->condition_id);

    if (!$condition) {
      return redirect()->back()->with('swal-fail', 'Condition not found');
    }

    // Ambil data dari ship_warehouse berdasarkan ship_warehouse_id dari condition
    $shipWarehouse = ShipWarehouses::find($condition->ship_warehouse_id);

    if (!$shipWarehouse) {
      return redirect()->back()->with('swal-fail', 'Ship Warehouse not found');
    }

    // Retrieve related ship and item details
    $ship = Ships::find($shipWarehouse->ship_id);
    $item = Items::find($shipWarehouse->item_id);

    // Buat transaksi baru tanpa mengurangi quantity
    $shipWarehouseSendOffice = ShipWarehouseSendOffice::create([
      'ship_warehouse_condition_id' => $condition->id,
      'quantity_send' => $request->quantity_send,
      'send_date' => $request->send_date,
      'pic' => $request->pic,
      'description' => $request->description,
      'note' => 'Adjustment',
      'status' => 'Diajukan', // status default
    ]);

    // Buat log tanpa pengurangan quantity
    Logs::create([
      'user_id' => Auth::user()->id,
      'action' => 'submitted adjustment for ' . $item->item_name . ' (' . $item->item_pms . ') with condition ' . $condition->condition . ' from the ' . $ship->ship_name . ' Warehouse.',
    ]);

    return redirect()->back()->with('swal-success', 'Adjustment submitted successfully');
  }


  public function confirmAdjustmentShipWarehouseSendOffice($id)
  {
    // Temukan data pengiriman berdasarkan ID
    $dataSendOffice = ShipWarehouseSendOffice::find($id);

    if (!$dataSendOffice) {
      return redirect()->back()->with('swal-fail', 'Data not found');
    }

    if ($dataSendOffice->status == 'Send by Ship') {
      return redirect()->back()->with('swal-fail', 'Item already approved');
    }

    // Ambil detail terkait kapal dan barang
    $ship = Ships::find($dataSendOffice->shipWarehouseConditions->shipWarehouses->ship_id);
    $item = Items::find($dataSendOffice->shipWarehouseConditions->shipWarehouses->item_id);
    $condition = $dataSendOffice->shipWarehouseConditions;

    // Kurangi quantity barang di kondisi yang sesuai saat dikonfirmasi
    $condition->quantity -= $dataSendOffice->quantity_send;
    $condition->save();

    // Update status menjadi "Send by Ship"
    $dataSendOffice->status = 'Send by Ship';
    $dataSendOffice->save();

    // Buat log setelah kuantitas berkurang
    Logs::create([
      'user_id' => Auth::user()->id,
      'action' => 'approved adjustment for ' . $item->item_name . ' (' . $item->item_pms . ') with condition ' . $condition->condition . ' from the ' . $ship->ship_name . ' Warehouse.',
    ]);

    return redirect()->back()->with('swal-success', 'Items approved successfully');
  }


  public function confirmShipWarehouseSendOffice($id)
  {
    // Find the send office record
    $sendOffice = ShipWarehouseSendOffice::find($id);

    if (!$sendOffice) {
      return redirect()->back()->with('swal-fail', 'Send Office record not found');
    }

    if ($sendOffice->confirmed) {
      return redirect()->back()->with('swal-fail', 'Item already received');
    }

    // Find the corresponding office warehouse record
    $item_id = $sendOffice->shipWarehouseConditions->shipWarehouses->item_id;
    $condition = $sendOffice->shipWarehouseConditions->condition;

    // Retrieve related ship and item details
    $ship = Ships::find($sendOffice->shipWarehouseConditions->shipWarehouses->ship_id);
    $item = Items::find($item_id);

    $officeWarehouse = OfficeWarehouse::where('item_id', $item_id)
      ->where('condition', $condition)
      ->first();

    if ($officeWarehouse) {
      $quantityBefore = $officeWarehouse->quantity;
      $officeWarehouse->quantity += $sendOffice->quantity_send;
      $officeWarehouse->save();
    } else {
      $quantityBefore = 0;
      $conditions = ['Baru', 'Bekas Bisa Pakai', 'Bekas Tidak Bisa Pakai', 'Rekondisi'];
      foreach ($conditions as $cond) {
        OfficeWarehouse::create([
          'item_id' => $item_id,
          'quantity' => $cond == $condition ? $sendOffice->quantity_send : 0,
          'location' => '', // Or use the actual location if available
          'condition' => $cond,
        ]);
      }
    }

    WarehouseHistory::create([
      'warehouse_type' => 'office',
      'ship_id' => null,
      'item_id' => $item_id,
      'condition' => $condition,
      'transaction_type' => 'In',
      'source_or_destination' => $ship->ship_name,
      'quantity_before' => $quantityBefore,
      'quantity_after' => $officeWarehouse->quantity,
      'transaction_date' => now(),
    ]);

    // Update the send office record to confirmed
    $sendOffice->status = 'Received by Office'; // Update status to "Received by Office"
    $sendOffice->save();

    // Create log entry
    Logs::create([
      'user_id' => Auth::user()->id,
      'action' => 'received ' . $item->item_name . ' (' . $item->item_pms . ') with condition ' . $officeWarehouse->condition . ' from the ' . $ship->ship_name . ' Warehouse.',
    ]);

    return redirect()->back()->with('swal-success', 'Items received successfully');
  }

  public function deleteShipWarehouseUsages($id)
  {
    $transaction = ShipWarehouseUsages::find($id);
    if (!$transaction) {
      return redirect()->back()->with('swal-fail', 'Transaction not found');
    }

    $condition = ShipWarehouseConditions::find($transaction->ship_warehouse_condition_id);
    if ($condition) {
      $condition->quantity += $transaction->quantity_used;
      $condition->save();
    }

    $shipWarehouse = ShipWarehouses::find($condition->ship_warehouse_id);
    $item = Items::find($shipWarehouse->item_id);
    $ship = Ships::find($shipWarehouse->ship_id);

    $newCondition = ShipWarehouseConditions::where('ship_warehouse_id', $condition->ship_warehouse_id)
      ->where('condition', $transaction->used_item_condition)
      ->first();

    if ($newCondition) {
      $newCondition->quantity -= $transaction->quantity_used;
      $newCondition->save();
    }

    // Hapus foto jika ada
    if ($transaction->photo) {
      $photoPath = public_path('images/uploads/shipWarehouseUsage-photos/' . $transaction->photo);
      if (file_exists($photoPath)) {
        unlink($photoPath);
      }
    }

    // Hapus transaksi
    $transaction->delete();

    // Buat log entry
    Logs::create([
      'user_id' => Auth::user()->id,
      'action' => 'deleted usage record for ' . $item->item_name . ' (' . $item->item_pms . ') from the ' . $ship->ship_name . ' Warehouse.',
    ]);

    return redirect()->back()->with('swal-success', 'Transaction deleted successfully');
  }

  public function deleteShipWarehouseSendOffice($id)
  {
    // Temukan transaksi
    $transaction = ShipWarehouseSendOffice::find($id);
    // dd($transaction->quantity_send);
    if (!$transaction) {
      return redirect()->back()->with('swal-fail', 'Transaction not found');
    }

    // Temukan kondisi terkait
    $condition = ShipWarehouseConditions::find($transaction->ship_warehouse_condition_id);
    if ($condition) {
      // Tambahkan kuantitas kembali ke kondisi yang terkait dengan transaksi
      $condition->quantity += $transaction->quantity_send;
      $condition->save(); // Simpan kondisi dengan kuantitas yang telah diperbarui
    }

    $shipWarehouse = ShipWarehouses::find($condition->ship_warehouse_id);
    $item = Items::find($shipWarehouse->item_id);
    $ship = Ships::find($shipWarehouse->ship_id);

    // Hapus foto jika ada
    if ($transaction->photo) {
      $photoPath = public_path('images/uploads/shipWarehouseUsage-photos/' . $transaction->photo);
      if (file_exists($photoPath)) {
        unlink($photoPath);
      }
    }

    // Hapus transaksi
    $transaction->delete();

    // Buat log entry
    Logs::create([
      'user_id' => Auth::user()->id,
      'action' => 'deleted sent record for ' . $item->item_name . ' (' . $item->item_pms . ') from the ' . $ship->ship_name . ' Warehouse.',
    ]);

    // Redirect dengan pesan sukses
    return redirect()->back()->with('swal-success', 'Transaction deleted successfully');
  }

  public function shipWarehouseUsage($shipId, Request $request)
  {
    // Check if the ship exists
    $ship = Ships::find($shipId);
    if (!$ship) {
      return response()->json(['error' => 'Ship not found'], 404);
    }
    // Get the search keyword from the request
    $search = $request->input('search');
    // Build the query to fetch ship warehouse transactions and associated items
    $query = ShipWarehouseUsages::whereHas('shipWarehouseConditions.shipWarehouses', function ($query) use ($shipId, $search) {
      $query->where('ship_id', $shipId);
      // Add search conditions if a keyword is provided
      if ($search) {
        $query->whereHas('items', function ($q) use ($search) {
          $q->where('item_name', 'LIKE', "%$search%")
            ->orWhere('quantity_used', 'LIKE', "%$search%")
            ->orWhere('pic', 'LIKE', "%$search%")
            ->orWhere(function ($q) use ($search) {
              // Pisahkan pencarian jika format bukan timestamp
              // Cek apakah format pencarian adalah tanggal valid (lebih ketat)
              $isDate = preg_match("/\d{4}-\d{2}-\d{2}/", $search) || strtotime($search) !== false;
              if ($isDate) {
                // Cocokkan berdasarkan tanggal (gunakan format YYYY-MM-DD)
                $q->whereDate('created_at', '=', date('Y-m-d', strtotime($search)));
              } else {
                // Format khusus jika bukan tanggal valid
                $q->whereRaw("DATE_FORMAT(created_at, '%d-%m-%Y') LIKE ?", ["%$search%"]);
              }
            });
        });
      }
    })->with(['shipWarehouseConditions.shipWarehouses.items']);
    // Execute the query and get the results
    $shipWarehouseUsages = $query
      ->orderByRaw("CASE 
        WHEN note = 'Adjustment' AND status = 'Diajukan' THEN 1 
        ELSE 2 
    END")
      ->orderBy('created_at', 'desc')
      ->get();
    // dd($shipWarehouseUsages);

    $auth = Auth::user();
    if ($auth) {
      $user = [
        'name' => $auth->name,
        'role' => $auth->role,
      ];
    }
    // Render the HTML content for the response
    $html = view('pages.shipWarehouseUsage', [
      'shipWarehouseUsages' => $shipWarehouseUsages,
      'user' => $user, // Make sure to pass the user data here
    ])->render();
    return response()->json([
      'html' => $html,
      'user' => $user,
    ]);
  }

  public function shipWarehouseSendOffice($shipId, Request $request)
  {
    // Check if the ship exists
    $ship = Ships::find($shipId);
    if (!$ship) {
      return response()->json(['error' => 'Ship not found'], 404);
    }

    // Get the search keyword from the request
    $search = $request->input('search');

    // Build the query to fetch ship warehouse transactions and associated items
    $query = ShipWarehouseSendOffice::whereHas('shipWarehouseConditions.shipWarehouses', function ($query) use ($shipId, $search) {
      $query->where('ship_id', $shipId);
      // Add search conditions if a keyword is provided
      if ($search) {
        $query->whereHas('items', function ($q) use ($search) {
          $q->where('item_name', 'LIKE', "%$search%")
            ->orWhere('quantity_send', 'LIKE', "%$search%")
            ->orWhere('pic', 'LIKE', "%$search%")
            ->orWhere('description', 'LIKE', "%$search%")
            ->orWhere(function ($q) use ($search) {
              // Pisahkan pencarian jika format bukan timestamp
              // Cek apakah format pencarian adalah tanggal valid
              if (strtotime($search)) {
                $q->whereDate('created_at', '=', date('Y-m-d', strtotime($search))); // Cocokkan berdasarkan tanggal
              } else {
                $q->whereRaw("DATE_FORMAT(created_at, '%d-%m-%Y') LIKE ?", ["%$search%"]); // Format khusus
              }
            });
        });
      }
    })
      ->with(['shipWarehouseConditions.shipWarehouses.items'])
      ->orderByRaw("
          CASE 
              WHEN status = 'Diajukan' THEN 1 
              WHEN status = 'Send by Ship' THEN 2 
              WHEN status = 'Received by Office' THEN 3 
              ELSE 4 
          END
      ")
      ->orderBy('created_at', 'desc');


    // Execute the query and get the results
    $shipWarehouseSendOffice = $query->get();

    $auth = Auth::user();
    if ($auth) {
      $user = [
        'name' => $auth->name,
        'role' => $auth->role,
      ];
    }
    // Render the HTML content for the response
    $html = view('pages.shipWarehouseSendOffice', ['shipWarehouseSendOffice' => $shipWarehouseSendOffice, 'user' => $user])->render();

    return response()->json(['html' => $html, 'user' => $user]);
  }

  public function indexShipWarehouses()
  {
    $ships = Ships::get();
    $items = Items::get();
    $shipType = $ships->unique('ship_type')->pluck('ship_type');

    $auth = Auth::user();
    if ($auth) {
      $user = [
        'name' => $auth->name,
        'role' => $auth->role,
      ];
    }
    return view('pages.shipWarehouses', [
      'ships' => $ships,
      'items' => $items,
      'shipType' => $shipType,
      'user' => $user,
    ]);
  }

  public function loadDataShipWarehouses($shipID, Request $request)
  {
    // Check if the ship exists
    $ship = Ships::find($shipID);
    if (!$ship) {
      return response()->json(['error' => 'Ship not found'], 404);
    }

    // Get the search keyword from the request
    $search = $request->input('search');

    // Build the query to fetch ship warehouses and associated items
    $query = ShipWarehouses::join('items', 'ship_warehouses.item_id', '=', 'items.id')
      ->select('items.*', 'ship_warehouses.*')
      ->where('ship_warehouses.ship_id', $shipID);

    // Add search conditions if a keyword is provided
    if ($search) {
      $query->where(function ($q) use ($search) {
        $q->where('items.item_name', 'LIKE', "%$search%")
          ->orWhere('items.item_pms', 'LIKE', "%$search%");
      });
    }

    // Execute the query and get the results
    $shipWarehouses = $query->orderBy('items.item_pms', 'asc')->get();

    // Fetch the conditions for each warehouse item
    foreach ($shipWarehouses as $warehouse) {
      $warehouse->conditions = ShipWarehouseConditions::where('ship_warehouse_id', $warehouse->id)->get();
    }

    // Get authenticated user information
    $auth = Auth::user();
    $user = null;
    if ($auth) {
      $user = [
        'name' => $auth->name,
        'role' => $auth->role,
      ];
    }

    // Render the HTML content for the response
    $html = view('pills.pillsShipWarehouses', compact('shipWarehouses', 'user'))->render();

    return response()->json([
      'html' => $html,
      'user' => $user,
      'shipWarehouses' => $shipWarehouses,
    ]);
  }

  public function checkShipWarehouses(Request $request)
  {
    $item_id = $request->id;
    $shipID = $request->shipID;
    $barangExists = ShipWarehouses::where('item_id', $item_id)
      ->where('ship_id', $shipID)
      ->exists();
    return response()->json(['exists' => $barangExists]);
  }

  public function importShipWarehouses(Request $request)
  {
    // Validate the uploaded file
    $request->validate([
      'import_file' => 'required|mimes:xlsx',
      'ship_name' => 'required|string', // Ensure ship_name is provided
    ]);

    // Find the ship based on the name provided in the request
    $ship = Ships::where('ship_name', $request->ship_name)->first();

    if (!$ship) {
      return redirect()->back()->with('swal-fail', "Ship {$request->ship_name} not found");
    }

    // Load the Excel file
    $path = $request->file('import_file')->getRealPath();
    $spreadsheet = IOFactory::load($path);
    $sheet = $spreadsheet->getActiveSheet();
    $data = $sheet->toArray();

    // Skip the first row if it contains headers
    $header = array_shift($data);

    $currentItemDetails = null; // To hold details for the current item

    foreach ($data as $row) {
      // Remove any empty rows or rows with insufficient data
      $row = array_map('trim', $row); // Trim whitespace from all elements
      if (empty($row) || count($row) < 15 || (empty($row[0]) && !$currentItemDetails)) {
        continue; // Skip if the row is empty or has fewer than 15 columns, and there's no current item details
      }

      // Map Excel data to your fields based on the column order in your screenshot
      if (!empty($row[0])) {
        // New item information row
        $currentItemDetails = [
          'itemCode' => $row[0], // Item Code
          'minimumQuantity' => $row[1], // Minimum Quantity
          'department' => $row[2], // Department
          'positionDate' => $row[3], // Position Date
          'equipmentCategory' => $row[4], // Equipment Category
          'toolCategory' => $row[5], // Tool Category
          'pmsNumber' => $row[6], // PMS Number
          'type' => $row[7], // Type
          'certification' => $row[8], // Certification
          'lastMaintenanceDate' => $row[9], // Last Maintenance Date
          'lastInspectionDate' => $row[10], // Last Inspection Date
          'description' => $row[11], // Description
        ];

        // Find the item by code
        $item = Items::where('item_pms', $currentItemDetails['itemCode'])->first();

        if (!$item) {
          return redirect()->back()->with('swal-fail', "Item with code {$currentItemDetails['itemCode']} not found");
        }

        // Check if the item already exists in the ship's warehouse
        $shipWarehouse = ShipWarehouses::updateOrCreate(
          ['ship_id' => $ship->id, 'item_id' => $item->id],
          [
            'minimum_quantity' => $currentItemDetails['minimumQuantity'],
            'department' => $currentItemDetails['department'],
            'position_date' => $currentItemDetails['positionDate'],
            'equipment_category' => $currentItemDetails['equipmentCategory'],
            'tool_category' => $currentItemDetails['toolCategory'],
            'type' => $currentItemDetails['type'],
            'certification' => $currentItemDetails['certification'],
            'last_maintenance_date' => $currentItemDetails['lastMaintenanceDate'],
            'last_inspection_date' => $currentItemDetails['lastInspectionDate'],
            'description' => $currentItemDetails['description'],
          ]
        );

        // Automatically create the four conditions if they do not exist
        $conditions = ['Baru', 'Bekas Bisa Pakai', 'Bekas Tidak Bisa Pakai', 'Rekondisi'];
        foreach ($conditions as $condition) {
          ShipWarehouseConditions::firstOrCreate(
            ['ship_warehouse_id' => $shipWarehouse->id, 'condition' => $condition],
            ['quantity' => 0, 'location' => '']
          );
        }
      }

      // Process quantity and location for each condition
      $condition = $row[12]; // Condition
      $location = $row[13]; // Location
      $quantity = isset($row[14]) && is_numeric($row[14]) ? (int)$row[14] : 0; // Quantity with validation

      // Ensure item details are available
      if (!$currentItemDetails) {
        continue; // Skip if no current item details are available
      }

      // Update the condition with quantity and location from Excel
      $conditionRecord = ShipWarehouseConditions::where('ship_warehouse_id', $shipWarehouse->id)
        ->where('condition', $condition)
        ->first();

      if ($conditionRecord) {
        $conditionRecord->quantity = $quantity;
        $conditionRecord->location = $location;
        $conditionRecord->save();
      }

      // Log the action
      Logs::create([
        'user_id' => Auth::user()->id,
        'action' => "imported data to the {$ship->ship_name} Warehouse.",
      ]);
    }

    return redirect()->back()->with('swal-success', 'Items imported successfully');
  }

  public function getItemsInShip(Request $request)
  {
    $shipID = $request->shipID;

    // Query untuk mencari barang-barang dengan id_kapal yang sesuai
    $query = ShipWarehouses::join('items', 'ship_warehouses.item_id', '=', 'items.id')
      ->select('items.item_name', 'items.item_pms', 'items.id')
      ->where('ship_warehouses.ship_id', $shipID);
    // Eksekusi query dan ambil hasilnya
    $adjustmentItemName = $query->orderBy('items.item_pms', 'asc')->get();
    // Cek apakah ada data yang ditemukan
    if ($adjustmentItemName->isEmpty()) {
      return response()->json(['message' => 'No items found for this ship'], 404);
    }
    // Kirim hasilnya sebagai respons JSON
    return response()->json($adjustmentItemName);
  }

  public function getItemQuantity(Request $request)
  {
    $itemID = $request->input('item_id');
    $condition = $request->input('condition');
    $shipID = $request->input('shipID');

    // Query untuk mendapatkan quantity berdasarkan item ID, kondisi, dan ship ID
    $warehouseCondition = ShipWarehouses::where('ship_warehouses.ship_id', $shipID)
      ->where('ship_warehouses.item_id', $itemID)
      ->join('ship_warehouse_conditions', 'ship_warehouse_conditions.ship_warehouse_id', '=', 'ship_warehouses.id')
      ->where('ship_warehouse_conditions.condition', $condition)
      ->select('ship_warehouse_conditions.quantity', 'ship_warehouse_conditions.id')
      ->first();

    if ($warehouseCondition) {
      return response()->json(['quantity' => $warehouseCondition->quantity, 'id' => $warehouseCondition->id]);
    } else {
      return response()->json(['quantity' => 0]);
    }
  }
}
