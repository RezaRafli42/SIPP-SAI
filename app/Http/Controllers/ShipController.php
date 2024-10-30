<?php

namespace App\Http\Controllers;

use App\Models\Ships as Ships;
use App\Models\Logs as Logs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\File;

class ShipController extends Controller
{
  public function indexShip()
  {
    $ships = Ships::orderby('ship_type', 'desc')->get();
    return view('pages.ships', [
      'ships' => $ships,
    ]);
  }

  public function findShip(Request $request)
  {
    $search = $request->input('search');

    // Lakukan pencarian berdasarkan nama kapal atau kriteria lain
    $ships = Ships::where('ship_name', 'LIKE', "%{$search}%")
      ->orWhere('ship_type', 'LIKE', "%{$search}%")
      ->orWhere('ship_position', 'LIKE', "%{$search}%")
      ->get();

    return response()->json($ships);  // Kembalikan hasil dalam format JSON
  }

  public function addShip(Request $request)
  {
    // Menyimpan data kapal
    $ship = new Ships;
    $ship->ship_name = $request->ship_name;
    $ship->ship_type = $request->ship_type;
    $ship->ship_position = $request->ship_position;
    if ($request->hasFile('ship_photo')) {
      $file = $request->file('ship_photo');
      $extension = $file->getClientOriginalExtension();
      $filename = $request->ship_name . '.' . $extension;
      $path = public_path('images/uploads/ship-photos/' . $filename);
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
        $file->move(public_path('images/uploads/ship-photos'), $filename);
      }
      $ship->ship_photo = $filename;
    }
    $ship->save();

    Logs::create([
      'user_id' => Auth::user()->id,
      'action' => 'added ship data ' . $ship->ship_name . '.',
    ]);

    return redirect()->back()->with('swal-success', 'Ship added successfully');
  }

  public function deleteShip($id)
  {
    $ship = Ships::find($id);
    if ($ship) {
      $imagePath = public_path('images/uploads/ship-photos/' . $ship->ship_photo);
      if (File::exists($imagePath)) {
        File::delete($imagePath);
      }
      $ship->delete();

      Logs::create([
        'user_id' => Auth::user()->id,
        'action' => 'deleted ship data ' . $ship->ship_name . '.',
      ]);

      return redirect()->back()->with('swal-success', 'Ship deleted successfully');
    } else {
      return redirect()->back()->with('swal-fail', 'Ship not found');
    }
  }

  public function updateShip(Request $request)
  {
    $ship = Ships::find($request->id);
    $ship->ship_name = $request->ship_name;
    $ship->ship_type = $request->ship_type;
    $ship->ship_position = $request->ship_position;
    if ($request->hasFile('ship_photo')) {
      $file = $request->file('ship_photo');
      $extension = $file->getClientOriginalExtension();
      $filename = $request->ship_name . '.' . $extension;
      $path = public_path('images/uploads/ship-photos/' . $filename);
      // Hapus foto lama jika ada
      $old = ('images/uploads/ship-photos/' . $ship->ship_photo);
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
        $file->move(public_path('images/uploads/ship-photos'), $filename);
      }
      $ship->ship_photo = $filename;
    }
    $ship->save();

    Logs::create([
      'user_id' => Auth::user()->id,
      'action' => 'updated ship data ' . $ship->ship_name . '.',
    ]);

    return redirect()->back()->with('swal-success', 'Ship updated successfully');
  }
}
