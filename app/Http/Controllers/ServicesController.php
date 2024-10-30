<?php

namespace App\Http\Controllers;

use App\Models\Items as Items;
use App\Models\Logs as Logs;
use App\Models\ExpenseAccounts as ExpenseAccounts;
use App\Models\Services as Services;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\Calculation\Web\Service;

class ServicesController extends Controller
{
  public function indexServices()
  {
    $services = Services::with('expenseAccounts')->orderby('service_code', 'asc')->get();
    $accounts = ExpenseAccounts::orderBy('account_code')->get();
    return view('pages.services', [
      'services' => $services,
      'accounts' => $accounts,
    ]);
  }

  public function findService(Request $request)
  {
    $search = $request->input('search');
    $services = Services::with('expenseAccounts')
      ->where('service_name', 'LIKE', "%{$search}%")
      ->orWhere('service_code', 'LIKE', "%{$search}%")
      ->orWhereHas('expenseAccounts', function ($query) use ($search) {
        $query->where('account_name', 'LIKE', "%{$search}%");
      })
      ->orderby('service_code', 'asc')
      ->get();

    return response()->json($services);  // Kembalikan hasil dalam format JSON
  }

  public function addService(Request $request)
  {
    $service = new Services;
    $service->account_id = $request->account_id;
    $service->service_code = $request->service_code;
    $service->service_name = $request->service_name;
    $service->save();

    Logs::create([
      'user_id' => Auth::user()->id,
      'action' => 'added service data ' . $service->service_name . '.',
    ]);
    return redirect()->back()->with('swal-success', 'Service added successfully');
  }

  public function deleteService($id)
  {
    $service = Services::find($id);
    if ($service) {
      $service->delete();

      Logs::create([
        'user_id' => Auth::user()->id,
        'action' => 'deleted service data ' . $service->service_name . '.',
      ]);

      return redirect()->back()->with('swal-success', 'Service deleted successfully');
    } else {
      return redirect()->back()->with('swal-fail', 'Service not found');
    }
  }

  public function updateService(Request $request)
  {
    $service = Services::find($request->id);
    $service->account_id = $request->account_id;
    $service->service_code = $request->service_code;
    $service->service_name = $request->service_name;
    $service->save();

    Logs::create([
      'user_id' => Auth::user()->id,
      'action' => 'updated service data ' . $service->service_name . '.',
    ]);

    return redirect()->back()->with('swal-success', 'Service updated successfully');
  }

  public function checkServiceCode(Request $request)
  {
    $serviceCode = $request->serviceCode;
    $exists = Services::where('service_code', $serviceCode)->exists();
    return response()->json(['exists' => $exists]);
  }
}
