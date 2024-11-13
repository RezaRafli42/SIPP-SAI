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
    // Mulai transaksi database
    DB::beginTransaction();

    try {
      // Temukan kapal berdasarkan nama
      $ship = Ships::where('ship_name', $request->ship_id)->first();
      if (!$ship) {
        throw new \Exception('Ship not found');
      }

      // Temukan item berdasarkan ID
      $item = Items::find($request->item_id);
      if (!$item) {
        throw new \Exception('Item not found');
      }

      // Tambahkan data Ship Warehouse
      $shipWarehouse = ShipWarehouses::create([
        'ship_id' => $ship->id,
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

      // Buat kondisi barang baru dan catat Warehouse History
      $conditions = ['Baru', 'Bekas Bisa Pakai', 'Bekas Tidak Bisa Pakai', 'Rekondisi'];
      foreach ($conditions as $condition) {
        $conditionRecord = ShipWarehouseConditions::create([
          'ship_warehouse_id' => $shipWarehouse->id,
          'condition' => $condition,
          'quantity' => 0,
          'location' => '', // atau Anda dapat memasukkan lokasi lain jika berbeda
        ]);

        // Tambahkan ke Warehouse History
        WarehouseHistory::create([
          'warehouse_type' => 'ship',
          'ship_id' => $ship->id,
          'item_id' => $shipWarehouse->item_id,
          'condition' => $condition,
          'transaction_type' => 'In', // Barang ditambahkan
          'source_or_destination' => 'Item Added',
          'quantity_before' => 0, // Awalnya 0
          'quantity_after' => 0, // Tetap 0 karena belum ada stok
          'transaction_date' => now(),
        ]);
      }

      // Catat log aktivitas
      Logs::create([
        'user_id' => Auth::user()->id,
        'action' => 'added data ' . $item->item_name . ' (' . $item->item_pms . ') to the ' . $ship->ship_name . ' Warehouse.',
      ]);

      // Commit transaksi
      DB::commit();

      return redirect()->back()->with('swal-success', 'Item added to Ship Warehouse successfully');
    } catch (\Exception $e) {
      // Rollback jika terjadi kesalahan
      DB::rollBack();

      // Kembalikan pesan error
      return redirect()->back()->with('swal-fail', 'Failed to add item: ' . $e->getMessage());
    }
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
    // Mulai transaksi database
    DB::beginTransaction();

    try {
      // Temukan kondisi gudang kapal berdasarkan ID
      $shipWarehouseCondition = ShipWarehouseConditions::findOrFail($request->condition_id);

      // Ambil data gudang kapal dan item terkait
      $shipWarehouse = ShipWarehouses::find($shipWarehouseCondition->ship_warehouse_id);
      $ship = Ships::find($shipWarehouse->ship_id);
      $item = Items::find($shipWarehouse->item_id);

      // Simpan kuantitas sebelum perubahan
      $quantityBefore = $shipWarehouseCondition->quantity;

      // Perbarui data kondisi gudang
      $shipWarehouseCondition->update([
        'quantity' => $request->quantity,
        'location' => $request->location,
      ]);

      // Tambahkan ke Warehouse History
      WarehouseHistory::create([
        'warehouse_type' => 'ship',
        'ship_id' => $ship->id,
        'item_id' => $shipWarehouse->item_id,
        'condition' => $shipWarehouseCondition->condition,
        'transaction_type' => $request->quantity > $quantityBefore ? 'In' : 'Out',
        'source_or_destination' => 'Item Updated',
        'quantity_before' => $quantityBefore,
        'quantity_after' => $request->quantity,
        'transaction_date' => now(),
      ]);

      // Catat log aktivitas
      Logs::create([
        'user_id' => Auth::user()->id,
        'action' => 'updated data ' . $item->item_name . ' (' . $item->item_pms . ') with condition ' . $shipWarehouseCondition->condition . ' in the ' . $ship->ship_name . ' Warehouse.',
      ]);

      // Commit transaksi
      DB::commit();

      return redirect()->back()->with('swal-success', 'Ship warehouse data updated successfully');
    } catch (\Exception $e) {
      // Rollback jika terjadi kesalahan
      DB::rollBack();

      // Kembalikan pesan error
      return redirect()->back()->with('swal-fail', 'Failed to update ship warehouse condition: ' . $e->getMessage());
    }
  }

  public function deleteShipWarehouses($id)
  {
    // Mulai transaksi database
    DB::beginTransaction();

    try {
      // Temukan ship warehouse berdasarkan ID
      $shipWarehouse = ShipWarehouses::findOrFail($id);

      // Ambil data kapal dan item terkait
      $ship = Ships::find($shipWarehouse->ship_id);
      $item = Items::find($shipWarehouse->item_id);

      // Ambil semua kondisi terkait dengan ship warehouse
      $conditions = ShipWarehouseConditions::where('ship_warehouse_id', $id)->get();

      // Loop melalui setiap kondisi untuk mencatat perubahan di Warehouse History
      foreach ($conditions as $condition) {
        // Catat ke Warehouse History sebelum menghapus kondisi
        WarehouseHistory::create([
          'warehouse_type' => 'ship',
          'ship_id' => $ship->id,
          'item_id' => $shipWarehouse->item_id,
          'condition' => $condition->condition,
          'transaction_type' => 'Out', // Karena barang dihapus
          'source_or_destination' => 'Item Deleted',
          'quantity_before' => $condition->quantity,
          'quantity_after' => 0, // Karena semua data dihapus
          'transaction_date' => now(),
        ]);

        // Hapus data terkait kondisi ini
        ShipWarehouseUsages::where('ship_warehouse_condition_id', $condition->id)->delete();
        ShipWarehouseSendOffice::where('ship_warehouse_condition_id', $condition->id)->delete();
      }

      // Hapus kondisi terkait
      ShipWarehouseConditions::where('ship_warehouse_id', $id)->delete();

      // Hapus ship warehouse
      $shipWarehouse->delete();

      // Log penghapusan
      Logs::create([
        'user_id' => Auth::user()->id,
        'action' => 'deleted data ' . $item->item_name . ' (' . $item->item_pms . ') from the ' . $ship->ship_name . ' Warehouse.',
      ]);

      // Commit transaksi
      DB::commit();

      return redirect()->back()->with('swal-success', 'Ship warehouse deleted successfully');
    } catch (\Exception $e) {
      // Rollback jika terjadi kesalahan
      DB::rollBack();

      // Kembalikan pesan error
      return redirect()->back()->with('swal-fail', 'Failed to delete ship warehouse: ' . $e->getMessage());
    }
  }

  public function addShipWarehouseUsages(Request $request)
  {
    // Mulai transaksi database
    DB::beginTransaction();

    try {
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
      $quantityBefore = $condition->quantity;
      $condition->quantity -= $request->quantity_used;

      if ($condition->quantity < 0) {
        throw new \Exception('Quantity used exceeds available quantity');
      }

      $condition->save();
      WarehouseHistory::create([
        'warehouse_type' => 'ship',
        'ship_id' => $ship->id,
        'item_id' => $shipWarehouse->item_id,
        'condition' => $condition->condition,
        'transaction_type' => 'Out',
        'source_or_destination' => 'Usage',
        'quantity_before' => $quantityBefore,
        'quantity_after' => $condition->quantity,
        'transaction_date' => now(),
      ]);

      // Tambah barang di gudang berdasarkan kondisi barang lama yang diganti
      $newCondition = ShipWarehouseConditions::where('ship_warehouse_id', $condition->ship_warehouse_id)
        ->where('condition', $request->used_item_condition)
        ->first();

      if ($newCondition) {
        $quantityBeforeNew = $newCondition->quantity;
        $newCondition->quantity += $request->quantity_used;
        $newCondition->save();
        WarehouseHistory::create([
          'warehouse_type' => 'ship',
          'ship_id' => $ship->id,
          'item_id' => $shipWarehouse->item_id,
          'condition' => $newCondition->condition,
          'transaction_type' => 'In',
          'source_or_destination' => 'Usage',
          'quantity_before' => $quantityBeforeNew,
          'quantity_after' => $newCondition->quantity,
          'transaction_date' => now(),
        ]);
      } else {
        // Jika kondisi tidak ada, buat baru
        $newCondition = ShipWarehouseConditions::create([
          'ship_warehouse_id' => $condition->ship_warehouse_id,
          'condition' => $request->used_item_condition,
          'quantity' => $request->quantity_used,
        ]);
        WarehouseHistory::create([
          'warehouse_type' => 'ship',
          'ship_id' => $ship->id,
          'item_id' => $shipWarehouse->item_id,
          'condition' => $request->used_item_condition,
          'transaction_type' => 'In',
          'source_or_destination' => 'Usage',
          'quantity_before' => 0,
          'quantity_after' => $request->quantity_used,
          'transaction_date' => now(),
        ]);
      }

      // Create log entry
      Logs::create([
        'user_id' => Auth::user()->id,
        'action' => 'used ' . $item->item_name . ' (' . $item->item_pms . ') with condition ' . $newCondition->condition . ' from the ' . $ship->ship_name . ' Warehouse.',
      ]);

      // Commit transaksi jika semua berhasil
      DB::commit();

      return redirect()->back()->with('swal-success', 'Usage recorded successfully');
    } catch (\Exception $e) {
      // Rollback transaksi jika terjadi kesalahan
      DB::rollBack();

      // Redirect dengan pesan error
      return redirect()->back()->with('swal-fail', 'Failed to record usage: ' . $e->getMessage());
    }
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
    // Mulai transaksi database
    DB::beginTransaction();

    try {
      // Ambil data condition berdasarkan id
      $condition = ShipWarehouseConditions::find($request->condition_id);

      if (!$condition) {
        throw new \Exception('Condition not found');
      }

      // Ambil data dari ship_warehouse berdasarkan ship_warehouse_id dari condition
      $shipWarehouse = ShipWarehouses::find($condition->ship_warehouse_id);

      if (!$shipWarehouse) {
        throw new \Exception('Ship Warehouse not found');
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
        $maxFileSize = 150000; // 150KB
        $fileSize = $file->getSize();
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
      $quantityBefore = $condition->quantity;
      $condition->quantity -= $request->quantity_send;

      if ($condition->quantity < 0) {
        throw new \Exception('Quantity send exceeds available quantity');
      }

      $condition->save();

      // Tambahkan ke Warehouse History (barang keluar dari ship warehouse)
      WarehouseHistory::create([
        'warehouse_type' => 'ship',
        'ship_id' => $ship->id,
        'item_id' => $shipWarehouse->item_id,
        'condition' => $condition->condition,
        'transaction_type' => 'Out',
        'source_or_destination' => 'Send to Office',
        'quantity_before' => $quantityBefore,
        'quantity_after' => $condition->quantity,
        'transaction_date' => now(),
      ]);

      // Create log entry
      Logs::create([
        'user_id' => Auth::user()->id,
        'action' => 'sent ' . $item->item_name . ' (' . $item->item_pms . ') with condition ' . $condition->condition . ' from the ' . $ship->ship_name . ' Warehouse to the Office Warehouse.',
      ]);

      // Commit transaksi jika semua berhasil
      DB::commit();

      return redirect()->back()->with('swal-success', 'Send item to Office successfully');
    } catch (\Exception $e) {
      // Rollback transaksi jika terjadi kesalahan
      DB::rollBack();

      return redirect()->back()->with('swal-fail', 'Failed to send item to Office: ' . $e->getMessage());
    }
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
    // Mulai transaksi database
    DB::beginTransaction();

    try {
      $transaction = ShipWarehouseUsages::find($id);
      if (!$transaction) {
        return redirect()->back()->with('swal-fail', 'Transaction not found');
      }

      $condition = ShipWarehouseConditions::find($transaction->ship_warehouse_condition_id);
      if (!$condition) {
        throw new \Exception('Condition record not found for the transaction');
      }

      $shipWarehouse = ShipWarehouses::find($condition->ship_warehouse_id);
      $item = Items::find($shipWarehouse->item_id);
      $ship = Ships::find($shipWarehouse->ship_id);

      // Tambahkan kuantitas kembali ke kondisi awal (barang yang digunakan dikembalikan)
      $quantityBeforeCondition = $condition->quantity;
      $condition->quantity += $transaction->quantity_used;
      $condition->save();

      // Tambahkan ke Warehouse History (barang bertambah kembali ke kondisi awal)
      WarehouseHistory::create([
        'warehouse_type' => 'ship',
        'ship_id' => $ship->id,
        'item_id' => $shipWarehouse->item_id,
        'condition' => $condition->condition,
        'transaction_type' => 'In',
        'source_or_destination' => 'Usage Deleted',
        'quantity_before' => $quantityBeforeCondition,
        'quantity_after' => $condition->quantity,
        'transaction_date' => now(),
      ]);

      // Kurangi kuantitas dari kondisi barang lama yang sebelumnya ditambahkan
      $newCondition = ShipWarehouseConditions::where('ship_warehouse_id', $condition->ship_warehouse_id)
        ->where('condition', $transaction->used_item_condition)
        ->first();

      if ($newCondition) {
        $quantityBeforeNewCondition = $newCondition->quantity;
        $newCondition->quantity -= $transaction->quantity_used;

        if ($newCondition->quantity < 0) {
          throw new \Exception('Reverted quantity exceeds current quantity in the new condition');
        }

        $newCondition->save();

        // Tambahkan ke Warehouse History (barang dikurangi dari kondisi baru)
        WarehouseHistory::create([
          'warehouse_type' => 'ship',
          'ship_id' => $ship->id,
          'item_id' => $shipWarehouse->item_id,
          'condition' => $newCondition->condition,
          'transaction_type' => 'Out',
          'source_or_destination' => 'Usage Deleted',
          'quantity_before' => $quantityBeforeNewCondition,
          'quantity_after' => $newCondition->quantity,
          'transaction_date' => now(),
        ]);
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

      // Commit transaksi jika semua berhasil
      DB::commit();

      return redirect()->back()->with('swal-success', 'Transaction deleted successfully');
    } catch (\Exception $e) {
      // Rollback transaksi jika terjadi kesalahan
      DB::rollBack();

      return redirect()->back()->with('swal-fail', 'Failed to delete transaction: ' . $e->getMessage());
    }
  }

  public function deleteShipWarehouseSendOffice($id)
  {
    // Mulai transaksi database
    DB::beginTransaction();

    try {
      // Temukan transaksi
      $transaction = ShipWarehouseSendOffice::find($id);
      if (!$transaction) {
        throw new \Exception('Transaction not found');
      }

      // Temukan kondisi terkait
      $condition = ShipWarehouseConditions::find($transaction->ship_warehouse_condition_id);
      if (!$condition) {
        throw new \Exception('Condition record not found for the transaction');
      }

      $shipWarehouse = ShipWarehouses::find($condition->ship_warehouse_id);
      $item = Items::find($shipWarehouse->item_id);
      $ship = Ships::find($shipWarehouse->ship_id);

      // Tambahkan kuantitas kembali ke kondisi yang terkait dengan transaksi
      $quantityBefore = $condition->quantity;
      $condition->quantity += $transaction->quantity_send;
      $condition->save();

      // Tambahkan ke Warehouse History (barang kembali ke Ship Warehouse)
      WarehouseHistory::create([
        'warehouse_type' => 'ship',
        'ship_id' => $ship->id,
        'item_id' => $shipWarehouse->item_id,
        'condition' => $condition->condition,
        'transaction_type' => 'In',
        'source_or_destination' => 'Send Deleted',
        'quantity_before' => $quantityBefore,
        'quantity_after' => $condition->quantity,
        'transaction_date' => now(),
      ]);

      // Hapus foto jika ada
      if ($transaction->photo) {
        $photoPath = public_path('images/uploads/shipWarehouseSendOffice-photos/' . $transaction->photo);
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

      // Commit transaksi jika semua berhasil
      DB::commit();

      // Redirect dengan pesan sukses
      return redirect()->back()->with('swal-success', 'Transaction deleted successfully');
    } catch (\Exception $e) {
      // Rollback transaksi jika terjadi kesalahan
      DB::rollBack();

      return redirect()->back()->with('swal-fail', 'Failed to delete transaction: ' . $e->getMessage());
    }
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

  public function shipWarehouseHistory($shipID)
  {
    $history = WarehouseHistory::where('ship_id', $shipID)->orderBy('transaction_date', 'ASC')->with('items')->get();
    return response()->json(['history' => $history]);
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
    // Mulai transaksi database
    DB::beginTransaction();

    try {
      // Validasi file yang diunggah
      $request->validate([
        'import_file' => 'required|mimes:xlsx',
        'ship_name' => 'required|string',
      ]);

      // Temukan kapal berdasarkan nama
      $ship = Ships::where('ship_name', $request->ship_name)->first();
      if (!$ship) {
        throw new \Exception("Ship {$request->ship_name} not found");
      }

      // Baca file Excel
      $path = $request->file('import_file')->getRealPath();
      $spreadsheet = IOFactory::load($path);
      $sheet = $spreadsheet->getActiveSheet();
      $data = $sheet->toArray();

      // Ambil header dari file dan pastikan format sesuai
      $header = array_shift($data);

      $currentItemDetails = null;

      foreach ($data as $row) {
        $row = array_map('trim', $row); // Trim whitespace

        if (empty($row[0]) && !$currentItemDetails) {
          continue; // Lewati baris kosong
        }

        // Data item baru
        if (!empty($row[0])) {
          $currentItemDetails = [
            'itemCode' => $row[0],
            'minimumQuantity' => $row[1],
            'department' => $row[2],
            'positionDate' => $row[3],
            'equipmentCategory' => $row[4],
            'toolCategory' => $row[5],
            'pmsNumber' => $row[6],
            'type' => $row[7],
            'certification' => $row[8],
            'lastMaintenanceDate' => $row[9],
            'lastInspectionDate' => $row[10],
            'description' => $row[11],
          ];

          $item = Items::where('item_pms', $currentItemDetails['itemCode'])->first();
          if (!$item) {
            throw new \Exception("Item with code {$currentItemDetails['itemCode']} not found");
          }

          // Tambahkan atau perbarui data gudang kapal
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

          // Tambahkan kondisi jika belum ada
          $conditions = ['Baru', 'Bekas Bisa Pakai', 'Bekas Tidak Bisa Pakai', 'Rekondisi'];
          foreach ($conditions as $condition) {
            ShipWarehouseConditions::firstOrCreate(
              ['ship_warehouse_id' => $shipWarehouse->id, 'condition' => $condition],
              ['quantity' => 0, 'location' => '']
            );
          }
        }

        // Proses kuantitas dan lokasi kondisi
        $condition = $row[12]; // Kondisi
        $location = $row[13]; // Lokasi
        $quantity = isset($row[14]) && is_numeric($row[14]) ? (int)$row[14] : 0;

        $conditionRecord = ShipWarehouseConditions::where('ship_warehouse_id', $shipWarehouse->id)
          ->where('condition', $condition)
          ->first();

        if ($conditionRecord) {
          $quantityBefore = $conditionRecord->quantity; // Simpan kuantitas sebelum perubahan
          $conditionRecord->quantity = $quantity;
          $conditionRecord->location = $location;
          $conditionRecord->save();

          // Tambahkan ke Warehouse History
          WarehouseHistory::create([
            'warehouse_type' => 'ship',
            'ship_id' => $ship->id,
            'item_id' => $shipWarehouse->item_id,
            'condition' => $condition,
            'transaction_type' => 'In',
            'source_or_destination' => 'Import',
            'quantity_before' => $quantityBefore,
            'quantity_after' => $quantity,
            'transaction_date' => now(),
          ]);
        }
      }

      // Buat log untuk proses import
      Logs::create([
        'user_id' => Auth::user()->id,
        'action' => "imported data to the {$ship->ship_name} Warehouse.",
      ]);

      // Commit transaksi
      DB::commit();

      return redirect()->back()->with('swal-success', 'Items imported successfully');
    } catch (\Exception $e) {
      // Rollback jika terjadi kesalahan
      DB::rollBack();

      // Tangkap detail error
      $errorDetails = [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
      ];

      // Kembalikan pesan error ke pengguna
      return redirect()->back()->with('swal-fail', 'Failed to import items: ' . $e->getMessage() . ' in ' . $e->getFile() . ' at line ' . $e->getLine());
    }
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
