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

class ExpenseAccountsController extends Controller
{
  public function indexExpenseAccounts()
  {
    $expenseAccounts = ExpenseAccounts::orderby('account_code', 'asc')->get();
    return view('pages.expenseAccounts', [
      'account' => $expenseAccounts,
    ]);
  }

  public function findExpenseAccount(Request $request)
  {
    $search = $request->input('search');

    // Lakukan pencarian berdasarkan item atau nama akun pengeluaran
    $accounts = ExpenseAccounts::where('account_type', 'LIKE', "%{$search}%")
      ->orWhere('account_code', 'LIKE', "%{$search}%")
      ->orWhere('account_name', 'LIKE', "%{$search}%")
      ->orderby('account_code', 'asc')
      ->get();

    return response()->json($accounts);  // Kembalikan hasil dalam format JSON
  }

  public function addExpenseAccount(Request $request)
  {
    // Menyimpan data kapal
    $account = new ExpenseAccounts();
    $account->account_name = $request->account_name;
    $account->account_code = $request->account_code;
    $account->account_type = $request->account_type;
    $account->save();

    Logs::create([
      'user_id' => Auth::user()->id,
      'action' => 'added account data ' . $account->account_name . '.',
    ]);

    return redirect()->back()->with('swal-success', 'Account added successfully');
  }

  public function deleteExpenseAccount($id)
  {
    $account = ExpenseAccounts::find($id);
    if ($account) {
      $account->delete();

      Logs::create([
        'user_id' => Auth::user()->id,
        'action' => 'deleted account data ' . $account->account_name . '.',
      ]);

      return redirect()->back()->with('swal-success', 'Account deleted successfully');
    } else {
      return redirect()->back()->with('swal-fail', 'Account not found');
    }
  }

  public function updateExpenseAccount(Request $request)
  {
    $account = ExpenseAccounts::find($request->id);
    $account->account_name = $request->account_name;
    $account->account_code = $request->account_code;
    $account->account_type = $request->account_type;
    $account->save();

    Logs::create([
      'user_id' => Auth::user()->id,
      'action' => 'updated account data ' . $account->account_name . '.',
    ]);

    return redirect()->back()->with('swal-success', 'Account updated successfully');
  }

  public function checkAccountCode(Request $request)
  {
    $accountCode = $request->accountCode;
    $exists = ExpenseAccounts::where('account_code', $accountCode)->exists();
    return response()->json(['exists' => $exists]);
  }
}
