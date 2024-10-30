<?php

namespace App\Http\Controllers;

use App\Models\Suppliers as Suppliers;
use App\Models\Logs as Logs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\File;

class SupplierController extends Controller
{
  public function indexSupplier()
  {
    $suppliers = Suppliers::orderby('supplier_code', 'asc')->get();
    $allCodes = Suppliers::orderBy('supplier_code')->pluck('supplier_code');
    $codePattern = 'PMK';
    // Loop through possible codes until we find an unused one
    for ($i = 1; $i <= count($allCodes) + 1; $i++) {
      $newCode = $codePattern . str_pad($i, 3, '0', STR_PAD_LEFT);
      if (!$allCodes->contains($newCode)) {
        break;
      }
    }

    $auth = Auth::user();
    if ($auth) {
      $user = [
        'name' => $auth->name,
        'role' => $auth->role,
      ];
    }
    return view('pages.suppliers', [
      'suppliers' => $suppliers,
      'supplier_code' => $newCode,
      'user' => $user,
    ]);
  }

  public function addSupplier(Request $request)
  {
    // Menyimpan data kapal
    $supplier = new Suppliers;
    $supplier->supplier_code = $request->supplier_code;
    $supplier->supplier_name = $request->supplier_name;
    $supplier->supplier_city = $request->supplier_city;
    $supplier->supplier_country = $request->supplier_country;
    $supplier->supplier_address = $request->supplier_address;
    $supplier->supplier_contact = $request->supplier_contact;
    $supplier->supplier_email = $request->supplier_email;
    $supplier->supplier_category = $request->supplier_category;
    $supplier->supplier_currency = implode(', ', $request->supplier_currency);
    $supplier->save();
    Logs::create([
      'user_id' => Auth::user()->id,
      'action' => 'added supplier data ' . $supplier->supplier_name . '.',
    ]);
    return redirect()->route('indexSupplier')->with('success', 'Supplier added successfully');
  }

  public function updateSupplier(Request $request)
  {
    $supplier = Suppliers::find($request->id);
    $supplier->supplier_code = $request->supplier_code;
    $supplier->supplier_name = $request->supplier_name;
    $supplier->supplier_city = $request->supplier_city;
    $supplier->supplier_country = $request->supplier_country;
    $supplier->supplier_address = $request->supplier_address;
    $supplier->supplier_contact = $request->supplier_contact;
    $supplier->supplier_email = $request->supplier_email;
    $supplier->supplier_category = $request->supplier_category;
    $supplier->supplier_currency = implode(', ', $request->supplier_currency);
    $supplier->save();
    Logs::create([
      'user_id' => Auth::user()->id,
      'action' => 'updated supplier data ' . $supplier->supplier_name . '.',
    ]);
    return redirect()->back()->with('swal-success', 'Supplier updated successfully');
  }

  public function deleteSupplier($id)
  {
    $supplier = Suppliers::find($id);
    if ($supplier) {
      $supplier->delete();
      Logs::create([
        'user_id' => Auth::user()->id,
        'action' => 'deleted supplier data ' . $supplier->supplier_name . '.',
      ]);
      return redirect()->back()->with('swal-success', 'Supplier deleted successfully');
    } else {
      return redirect()->back()->with('swal-fail', 'Supplier not found');
    }
  }

  public function findSupplier(Request $request)
  {
    $search = $request->input('search');

    // Lakukan pencarian berdasarkan nama kapal atau kriteria lain
    $suppliers = Suppliers::where('supplier_code', 'LIKE', "%{$search}%")
      ->orWhere('supplier_name', 'LIKE', "%{$search}%")
      ->orWhere('supplier_city', 'LIKE', "%{$search}%")
      ->orWhere('supplier_country', 'LIKE', "%{$search}%")
      ->orWhere('supplier_address', 'LIKE', "%{$search}%")
      ->orWhere('supplier_contact', 'LIKE', "%{$search}%")
      ->orWhere('supplier_email', 'LIKE', "%{$search}%")
      ->orWhere('supplier_category', 'LIKE', "%{$search}%")
      ->orWhere('supplier_currency', 'LIKE', "%{$search}%")
      ->get();

    return response()->json($suppliers);  // Kembalikan hasil dalam format JSON
  }
}
