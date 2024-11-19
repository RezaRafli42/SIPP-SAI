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

    // Ambil data dengan relasi yang dibutuhkan
    $data = ExpenseAccountDetails::with([
      'purchaseOrderItems.purchaseOrders',
      'purchaseOrderServices.purchaseOrders',
      'purchaseOrderItems.purchaseRequestItems.items',
      'purchaseOrderServices.purchaseRequestServices.services',
      'purchaseOrderServices.services',
    ])
      ->where('account_id', $accountId)
      ->get();

    // Map data untuk menghasilkan struktur yang sesuai
    $mappedData = $data->map(function ($detail) {
      if ($detail->purchase_order_item_id) {
        $purchaseOrder = $detail->purchaseOrderItems->purchaseOrders ?? null;
        $item = $detail->purchaseOrderItems->purchaseRequestItems->items ?? null;

        return [
          'purchase_order_number' => $purchaseOrder->purchase_order_number ?? '-',
          'currency' => $purchaseOrder->currency ?? 'IDR',
          'pms_code' => $item->item_pms ?? '-',
          'item_name' => $item->item_name ?? 'Unknown Item',
          'quantity' => $detail->purchaseOrderItems->quantity ?? 0,
          'price' => number_format((float)$detail->purchaseOrderItems->price ?? 0, 2, '.', ''),
          'transaction_date' => $purchaseOrder->purchase_date ?? '1970-01-01',
          'amount' => number_format((float)$detail->amount ?? 0, 2, '.', ''),
        ];
      } elseif ($detail->purchase_order_service_id) {
        $purchaseOrderService = $detail->purchaseOrderServices ?? null;
        $service = $purchaseOrderService->purchaseRequestServices->services
          ?? $purchaseOrderService->services;

        return [
          'purchase_order_number' => $purchaseOrderService->purchaseOrders->purchase_order_number ?? '-',
          'currency' => $purchaseOrderService->purchaseOrders->currency ?? 'IDR',
          'pms_code' => $service->service_code ?? '-',
          'item_name' => $service->service_name ?? 'Unknown Service',
          'quantity' => 1,
          'price' => number_format((float)$purchaseOrderService->price ?? 0, 2, '.', ''),
          'transaction_date' => $purchaseOrderService->purchaseOrders->purchase_date ?? '1970-01-01',
          'amount' => number_format((float)$detail->amount ?? 0, 2, '.', ''),
        ];
      }

      return null;
    })->filter();

    // Urutkan data berdasarkan transaction_date secara ASC
    $sortedData = $mappedData->sortByDesc('transaction_date');

    // Return data dalam bentuk JSON
    return response()->json(['data' => $sortedData->values()]);
  }
}
