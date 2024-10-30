<?php

namespace App\Http\Controllers;

use App\Models\Items as Items;
use App\Models\Logs as Logs;
use App\Models\ExpenseAccounts as ExpenseAccounts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\File;

class ItemController extends Controller
{
  public function indexItem()
  {
    $items = Items::with('expenseAccounts')->orderby('item_name', 'asc')->get();
    $account = ExpenseAccounts::orderby('account_code', 'asc')->get();
    return view('pages.items', [
      'items' => $items,
      'account' => $account,
    ]);
  }

  public function findItem(Request $request)
  {
    $search = $request->input('search');

    // Lakukan pencarian berdasarkan item atau nama akun pengeluaran
    $items = Items::with('expenseAccounts') // Ganti dengan singular jika nama relasi adalah 'expenseAccount'
      ->where('item_pms', 'LIKE', "%{$search}%")
      ->orWhere('item_name', 'LIKE', "%{$search}%")
      ->orWhere('item_unit', 'LIKE', "%{$search}%")
      ->orWhere('item_category', 'LIKE', "%{$search}%")
      ->orWhereHas('expenseAccounts', function ($query) use ($search) {
        $query->where('account_name', 'LIKE', "%{$search}%");
      })
      ->orderby('item_name', 'asc')
      ->get();

    return response()->json($items);  // Kembalikan hasil dalam format JSON
  }

  public function addItem(Request $request)
  {
    // Menyimpan data kapal
    $item = new Items;
    $item->account_id = $request->account_id;
    $item->item_pms = $request->item_pms;
    $item->item_name = $request->item_name;
    $item->item_unit = $request->item_unit;
    $item->item_category = $request->item_category;
    if ($request->hasFile('item_photo')) {
      $file = $request->file('item_photo');
      $extension = $file->getClientOriginalExtension();
      $filename = $request->item_pms . '.' . $extension;
      $path = public_path('images/uploads/item-photos/' . $filename);
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
        $file->move(public_path('images/uploads/item-photos'), $filename);
      }
      $item->item_photo = $filename;
    }
    $item->save();

    Logs::create([
      'user_id' => Auth::user()->id,
      'action' => 'added item data ' . $item->item_name . '.',
    ]);

    return redirect()->back()->with('swal-success', 'Item added successfully');
  }

  public function deleteItem($id)
  {
    $item = Items::find($id);
    if ($item) {
      $imagePath = public_path('images/uploads/item-photos/' . $item->item_photo);
      if (File::exists($imagePath)) {
        File::delete($imagePath);
      }
      $item->delete();

      Logs::create([
        'user_id' => Auth::user()->id,
        'action' => 'deleted item data ' . $item->item_name . '.',
      ]);

      return redirect()->back()->with('swal-success', 'Item deleted successfully');
    } else {
      return redirect()->back()->with('swal-fail', 'Item not found');
    }
  }

  public function updateItem(Request $request)
  {
    $item = Items::find($request->id);
    $item->item_pms = $request->item_pms;
    $item->account_id = $request->account_id;
    $item->item_name = $request->item_name;
    $item->item_unit = $request->item_unit;
    $item->item_category = $request->item_category;
    if ($request->hasFile('item_photo')) {
      $file = $request->file('item_photo');
      $extension = $file->getClientOriginalExtension();
      $filename = $request->item_name . '.' . $extension;
      $path = public_path('images/uploads/item-photos/' . $filename);
      // Hapus foto lama jika ada
      $old = ('images/uploads/item-photos/' . $item->item_photo);
      if (File::exists($old)) {
        File::delete($old);
      }
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
        $file->move(public_path('images/uploads/item-photos'), $filename);
      }
      $item->item_photo = $filename;
    }
    $item->save();

    Logs::create([
      'user_id' => Auth::user()->id,
      'action' => 'updated item data ' . $item->item_name . '.',
    ]);

    return redirect()->back()->with('swal-success', 'Item updated successfully');
  }

  public function checkItemPMS(Request $request)
  {
    $itemPMS = $request->itemPMS;
    $exists = Items::where('item_pms', $itemPMS)->exists();
    return response()->json(['exists' => $exists]);
  }
}
