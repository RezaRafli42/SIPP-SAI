<?php

namespace App\Http\Controllers;

use App\Models\Ships as Ships;
use App\Models\OfficeWarehouse as OfficeWarehouse;
use App\Models\Items as Items;
use App\Models\Logs as Logs;
use App\Models\WarehouseHistory as WarehouseHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\File;

class OfficeWarehouseController extends Controller
{
  public function indexOfficeWarehouse()
  {
    $items = Items::orderBy('item_pms')->get();
    $officeWarehouses = OfficeWarehouse::join('items', 'office_warehouse.item_id', '=', 'items.id')
      ->select(
        'items.*',
        'office_warehouse.*',
      )
      ->orderBy('items.item_pms', 'asc')
      ->get();
    $history = WarehouseHistory::where('warehouse_type', 'office')->orderBy('transaction_date', 'ASC')->with('items')->get();

    // Tentukan urutan kondisi
    $conditionOrder = ['Baru' => 1, 'Bekas Bisa Pakai' => 2, 'Bekas Tidak Bisa Pakai' => 3, 'Rekondisi' => 4];

    // Urutkan berdasarkan kondisi dan item_pms
    $officeWarehouses = $officeWarehouses->sortBy(function ($item) use ($conditionOrder) {
      return $conditionOrder[$item->condition];
    });

    // Mengelompokkan dan menghitung total quantity
    $groupedOfficeWarehouses = $officeWarehouses->groupBy('item_id');
    foreach ($groupedOfficeWarehouses as $itemId => $itemsGroup) {
      $totalQuantity = $itemsGroup->sum('quantity');
      foreach ($itemsGroup as $item) {
        $item->total_quantity = $totalQuantity;
      }
    }
    return view('pages.officeWarehouse', [
      'groupedOfficeWarehouses' => $groupedOfficeWarehouses,
      'barang' => $items,
      'history' => $history,
    ]);
  }

  public function findOfficeWarehouse(Request $request)
  {
    $query = $request->get('query');

    // Fetch matching results from the office warehouse and items table
    $officeWarehouses = OfficeWarehouse::join('items', 'office_warehouse.item_id', '=', 'items.id')
      ->where('items.item_name', 'LIKE', "%{$query}%")
      ->orWhere('items.item_pms', 'LIKE', "%{$query}%")
      ->orWhere('office_warehouse.location', 'LIKE', "%{$query}%")
      ->select('items.*', 'office_warehouse.*')
      ->orderBy('items.item_pms', 'asc')
      ->get();
    // Tentukan urutan kondisi
    $conditionOrder = ['Baru' => 1, 'Bekas Bisa Pakai' => 2, 'Bekas Tidak Bisa Pakai' => 3, 'Rekondisi' => 4];
    // Sort based on condition and item code
    $officeWarehouses = $officeWarehouses->sortBy(function ($item) use ($conditionOrder) {
      return $conditionOrder[$item->condition];
    });
    // Group and calculate total quantity
    $groupedOfficeWarehouses = $officeWarehouses->groupBy('item_id');
    foreach ($groupedOfficeWarehouses as $itemId => $itemsGroup) {
      $totalQuantity = $itemsGroup->sum('quantity');
      foreach ($itemsGroup as $item) {
        $item->total_quantity = $totalQuantity;
      }
    }
    // Return as JSON response
    return response()->json($groupedOfficeWarehouses);
  }

  public function addOfficeWarehouse(Request $request)
  {
    // Retrieve the item details
    $item = Items::find($request->item_id);

    if (!$item) {
      return redirect()->back()->with('swal-fail', 'Item not found');
    }

    $conditions = ['Baru', 'Bekas Bisa Pakai', 'Bekas Tidak Bisa Pakai', 'Rekondisi'];
    foreach ($conditions as $condition) {
      // Create OfficeWarehouse entry
      OfficeWarehouse::create([
        'item_id' => $request->item_id,
        'quantity' => 0,
        'condition' => $condition,
      ]);
    }

    Logs::create([
      'user_id' => Auth::user()->id,
      'action' => 'added item ' . $item->item_name . ' in the Office Warehouse.',
    ]);

    return redirect()->back()->with('swal-success', 'Item added to Office Warehouse successfully');
  }

  public function checkOfficeWarehouse(Request $request)
  {
    $item_id = $request->id;
    $barangExists = OfficeWarehouse::where('item_id', $item_id)
      ->exists();
    return response()->json(['exists' => $barangExists]);
  }

  public function updateOfficeWarehouse(Request $request)
  {
    $officeWarehouse = OfficeWarehouse::find($request->id);
    $officeWarehouse->quantity = $request->quantity;
    $officeWarehouse->location = $request->location;
    $officeWarehouse->save();

    // Fetch the associated item details
    $item = Items::find($officeWarehouse->item_id);

    if (!$item) {
      return redirect()->back()->withErrors(['error' => 'Item not found']);
    }
    Logs::create([
      'user_id' => Auth::user()->id,
      'action' => 'updated item ' . $item->item_name . ' in the Office Warehouse.',
    ]);

    return redirect()->back()->with('swal-success', 'Data updated successfully');
  }

  public function deleteOfficeWarehouse($id)
  {
    // Find the office warehouse record by ID
    $officeWarehouse = OfficeWarehouse::find($id);

    // Check if the record was found
    if ($officeWarehouse) {
      // Get the item_id from the found record
      $item_id = $officeWarehouse->item_id;

      // Retrieve the item details for logging
      $item = Items::find($item_id);

      // Check if the item was found
      if ($item) {
        // Delete all office warehouse records with the same item_id
        OfficeWarehouse::where('item_id', $item_id)->delete();

        Logs::create([
          'user_id' => Auth::user()->id,
          'action' => 'deleted item ' . $item->item_name . ' in the Office Warehouse.',
        ]);

        return redirect()->back()->with('swal-success', 'Data deleted successfully');
      } else {
        return redirect()->back()->with('swal-fail', 'Item not found');
      }
    } else {
      return redirect()->back()->with('swal-fail', 'Data not found');
    }
  }
}
