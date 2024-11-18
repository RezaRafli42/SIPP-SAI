<?php

namespace App\Http\Controllers;

use App\Models\Items as Items;
use App\Models\Logs as Logs;
use App\Models\ExpenseAccounts as ExpenseAccounts;
use App\Models\ExpenseAccountDetails as ExpenseAccountDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\File;

class ExpenseAccountDetailsController extends Controller
{
  public function indexExpenseAccountDetails()
  {
    $data = ExpenseAccountDetails::with('expenseAccounts')
      ->selectRaw('account_id, SUM(amount) as total_spend')
      ->groupBy('account_id')
      ->get();

    return view('pages.expenseAccountDetails', [
      'data' => $data,
    ]);
  }

  public function getDetailAccountSpends(Request $request)
  {
    $accountId = $request->accountID;

    $data = ExpenseAccountDetails::with([
      'purchaseOrderItems.purchaseOrders',
      'purchaseOrderServices.purchaseOrders',
      'purchaseOrderItems.purchaseRequestItems.items',
      'purchaseOrderServices.purchaseRequestServices.services',
      'purchaseOrderServices.services', // Relasi langsung ke services
    ])
      ->where('account_id', $accountId)
      ->get()
      ->map(function ($detail) {
        if ($detail->purchase_order_item_id) {
          return [
            'purchase_order_number' => $detail->purchaseOrderItems->purchaseOrders->purchase_order_number ?? '-',
            'currency' => $detail->purchaseOrderItems->purchaseOrders->currency ?? 'IDR', // Default currency jika null
            'pms_code' => $detail->purchaseOrderItems->purchaseRequestItems->items->item_pms ?? '-',
            'item_name' => $detail->purchaseOrderItems->purchaseRequestItems->items->item_name ?? 'Unknown Item',
            'quantity' => $detail->purchaseOrderItems->quantity ?? 0,
            'price' => $detail->purchaseOrderItems->price ?? 0,
            'transaction_date' => $detail->purchaseOrderItems->purchaseOrders->purchase_date ?? '1970-01-01', // Default tanggal
            'amount' => $detail->amount ?? 0, // Default amount jika null
          ];
        } elseif ($detail->purchase_order_service_id) {
          $purchaseOrderService = $detail->purchaseOrderServices;
          $service = $purchaseOrderService->purchaseRequestServices->services
            ?? $purchaseOrderService->services; // Relasi fallback jika salah satu null

          return [
            'purchase_order_number' => $purchaseOrderService->purchaseOrders->purchase_order_number ?? '-',
            'currency' => $purchaseOrderService->purchaseOrders->currency ?? 'IDR', // Default currency jika null
            'pms_code' => $service->service_code ?? '-',
            'item_name' => $service->service_name ?? 'Unknown Service',
            'quantity' => 1, // Default quantity untuk service
            'price' => $purchaseOrderService->price ?? 0,
            'transaction_date' => $purchaseOrderService->purchaseOrders->purchase_date ?? '1970-01-01', // Default tanggal
            'amount' => $detail->amount ?? 0, // Default amount jika null
          ];
        }

        return null; // Jika tidak memenuhi kondisi di atas
      })
      ->filter(); // Hapus null values dari hasil map

    return response()->json(['data' => $data->values()]);
  }
}
