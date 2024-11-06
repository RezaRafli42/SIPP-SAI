<?php

namespace App\Http\Controllers;

use App\Models\Items as Items;
use App\Models\PurchaseOrderItems;
use App\Models\PurchaseOrderServices;
use App\Models\Receipts as Receipts;
use App\Models\Suppliers as Suppliers;
use App\Models\ReceiptItems as ReceiptItems;
use App\Models\ReceiptServices as ReceiptServices;
use App\Models\OfficeWarehouse as OfficeWarehouse;
use App\Models\PurchaseRequests as PurchaseRequests;
use App\Models\PurchaseRequestItems as PurchaseRequestItems;
use App\Models\PurchaseRequestServices as PurchaseRequestServices;
use App\Models\Logs as Logs;
use Illuminate\Support\Facades\Log;
use App\Models\Ships as Ships;
use App\Models\PurchaseOrders as PurchaseOrders;
// use Illuminate\Container\Attributes\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\File;

class ReceiptsController extends Controller
{
  public function indexReceipts()
  {
    $receipts = Receipts::with(['receiptItems', 'receiptServices'])
      ->leftJoin('receipt_items', 'receipt_items.receipt_id', '=', 'receipts.id')
      ->leftJoin('receipt_services', 'receipt_services.receipt_id', '=', 'receipts.id')
      ->select(
        'receipts.*',
        DB::raw('COALESCE(COUNT(DISTINCT receipt_items.id), 0) + COALESCE(COUNT(DISTINCT receipt_services.id), 0) as item_count')
      )
      ->groupBy('receipts.id') // Group by receipt id to get the correct count per receipt
      ->orderBy('receipt_number', 'desc')
      ->get();

    // Mengambil semua kode receipt untuk menentukan kode baru
    $allCodes = Receipts::orderBy('receipt_number')->pluck('receipt_number');
    $codePattern = 'RCPT';

    // Menentukan kode baru yang tidak digunakan
    for ($i = 1; $i <= count($allCodes) + 1; $i++) {
      $newCode = $codePattern . str_pad($i, 3, '0', STR_PAD_LEFT);
      if (!$allCodes->contains($newCode)) {
        break;
      }
    }

    return view('pages.receipts', [
      'receiptData' => $receipts,
      'newCode' => $newCode,
    ]);
  }

  public function findReceipts(Request $request)
  {
    $name = $request->search;

    // Check if the search query is in date format (DD/MM/YYYY)
    if (preg_match('/\d{2}\/\d{2}\/\d{4}/', $name)) {
      // Convert from DD/MM/YYYY to YYYY-MM-DD
      $dateParts = explode('/', $name);
      if (count($dateParts) === 3) {
        $name = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0]; // Convert to YYYY-MM-DD
      }
    }

    // Subquery to calculate the total number of receipt items and services for each receipt
    $itemCountSubquery = DB::table('receipt_items')
      ->select('receipt_id', DB::raw('COUNT(*) as count'))
      ->groupBy('receipt_id')
      ->unionAll(
        DB::table('receipt_services')
          ->select('receipt_id', DB::raw('COUNT(*) as count'))
          ->groupBy('receipt_id')
      );

    // Summing up item and service counts by receipt_id
    $itemCountSubquery = DB::query()
      ->fromSub($itemCountSubquery, 'counts')
      ->select('receipt_id', DB::raw('SUM(count) as item_count'))
      ->groupBy('receipt_id');

    // Main query with joins and filtering
    $results = Receipts::leftJoinSub($itemCountSubquery, 'item_counts', function ($join) {
      $join->on('receipts.id', '=', 'item_counts.receipt_id');
    })
      ->leftJoin('receipt_items', 'receipts.id', '=', 'receipt_items.receipt_id')
      ->leftJoin('purchase_order_items', 'receipt_items.purchase_order_item_id', '=', 'purchase_order_items.id')
      ->leftJoin('purchase_orders', 'purchase_order_items.purchase_order_id', '=', 'purchase_orders.id')
      ->leftJoin('suppliers', 'purchase_orders.supplier_id', '=', 'suppliers.id')
      ->leftJoin('purchase_request_items', 'purchase_order_items.purchase_request_item_id', '=', 'purchase_request_items.id')
      ->leftJoin('items', 'purchase_request_items.item_id', '=', 'items.id')

      // Joins for services and their related suppliers
      ->leftJoin('receipt_services', 'receipts.id', '=', 'receipt_services.receipt_id')
      ->leftJoin('purchase_order_services', 'receipt_services.purchase_order_service_id', '=', 'purchase_order_services.id')
      ->leftJoin('purchase_orders as service_purchase_orders', 'purchase_order_services.purchase_order_id', '=', 'service_purchase_orders.id')
      ->leftJoin('suppliers as service_suppliers', 'service_purchase_orders.supplier_id', '=', 'service_suppliers.id')

      // Join purchase_request_services terlebih dahulu
      ->leftJoin('purchase_request_services', 'purchase_order_services.purchase_request_service_id', '=', 'purchase_request_services.id')

      // Baru join ke services setelah purchase_request_services
      ->leftJoin('services', 'purchase_request_services.service_id', '=', 'services.id')

      // Filter based on search terms
      ->where(function ($query) use ($name) {
        $query->where('receipts.receipt_number', 'LIKE', '%' . $name . '%')
          ->orWhere('receipts.received_by', 'LIKE', '%' . $name . '%')
          ->orWhere('items.item_pms', 'LIKE', '%' . $name . '%')
          ->orWhere('items.item_name', 'LIKE', '%' . $name . '%')
          ->orWhere('services.service_code', 'LIKE', '%' . $name . '%')
          ->orWhere('services.service_name', 'LIKE', '%' . $name . '%')
          ->orWhere('suppliers.supplier_name', 'LIKE', '%' . $name . '%')
          ->orWhere('service_suppliers.supplier_name', 'LIKE', '%' . $name . '%') // Search for service suppliers
          ->orWhere('purchase_order_items.condition', 'LIKE', '%' . $name . '%')
          ->orWhere('purchase_order_items.quantity', 'LIKE', '%' . $name . '%')
          ->orWhere('receipt_items.received_quantity', 'LIKE', '%' . $name . '%')
          ->orWhere('items.item_unit', 'LIKE', '%' . $name . '%')
          ->orWhere('receipt_items.serial_number', 'LIKE', '%' . $name . '%')
          ->orWhere('purchase_orders.purchase_order_number', 'LIKE', '%' . $name . '%') // Search for items PO number
          ->orWhere('service_purchase_orders.purchase_order_number', 'LIKE', '%' . $name . '%') // Search for services PO number
          ->orWhere('purchase_order_services.utility', 'LIKE', '%' . $name . '%') // Search for service name in purchase order services
          ->orWhere('purchase_request_services.utility', 'LIKE', '%' . $name . '%') // Search for service name in purchase request services
          ->orWhere('receipts.received_date', 'LIKE', '%' . $name . '%');
      })

      // Select necessary fields with aggregation
      ->select(
        'receipts.*',
        DB::raw('IFNULL(item_counts.item_count, 0) as item_count'),
        DB::raw('MAX(items.item_name) as item_name'),
        DB::raw('MAX(items.item_pms) as pms_code'),
        DB::raw('MAX(purchase_order_items.quantity) as quantity'),
        DB::raw('MAX(receipt_items.received_quantity) as received_quantity'),
        DB::raw('MAX(items.item_unit) as unit'),
        DB::raw('MAX(purchase_order_items.condition) as item_condition'),
        DB::raw('MAX(receipt_items.serial_number) as serial_number'),
        DB::raw('MAX(purchase_orders.purchase_order_number) as po_number'), // Items PO number
        DB::raw('MAX(service_purchase_orders.purchase_order_number) as service_po_number'), // Service PO number
        DB::raw('MAX(suppliers.supplier_name) as supplier_name'),
        DB::raw('MAX(IFNULL(service_suppliers.supplier_name, "")) as service_supplier_name'), // Use MAX aggregate function
        DB::raw('MAX(IFNULL(purchase_order_services.utility, "")) as service_name'), // Service name from purchase order services
        DB::raw('MAX(IFNULL(purchase_request_services.utility, "")) as request_service_name') // Service name from purchase request services
      )
      ->groupBy('receipts.id')
      ->orderBy('receipts.received_date', 'desc')
      ->get();

    return response()->json($results);
  }

  public function getSupplier()
  {
    $supplier = Suppliers::all();
    return response()->json($supplier);
  }

  public function getItemSuppliers($supplierID)
  {
    $items = PurchaseOrderItems::join('purchase_orders', 'purchase_order_items.purchase_order_id', '=', 'purchase_orders.id')
      ->join('purchase_request_items', 'purchase_order_items.purchase_request_item_id', '=', 'purchase_request_items.id')
      ->join('items', 'purchase_request_items.item_id', '=', 'items.id')
      ->leftJoin('receipt_items', 'purchase_order_items.id', '=', 'receipt_items.purchase_order_item_id')
      ->where('purchase_orders.supplier_id', $supplierID)
      ->whereIn('purchase_order_items.status', ['Menunggu Diproses', 'Menunggu LPJ', 'Belum Selesai'])
      ->select(
        'purchase_order_items.*',
        'items.item_pms',
        'items.item_name',
        'items.item_unit',
        'purchase_orders.purchase_order_number',
        DB::raw('(purchase_order_items.quantity - COALESCE(SUM(receipt_items.received_quantity), 0)) as remaining_quantity')
      )
      ->groupBy('purchase_order_items.id')
      ->having('remaining_quantity', '>', 0)
      ->get();
    // dd($items);

    $services = PurchaseOrderServices::join('purchase_orders', 'purchase_order_services.purchase_order_id', '=', 'purchase_orders.id')
      ->leftJoin('purchase_request_services', 'purchase_order_services.purchase_request_service_id', '=', 'purchase_request_services.id')
      ->leftJoin('services AS request_services', 'purchase_request_services.service_id', '=', 'request_services.id') // Alias pertama
      ->leftJoin('services AS direct_services', 'purchase_order_services.service_id', '=', 'direct_services.id') // Alias kedua
      ->where('purchase_orders.supplier_id', $supplierID)
      ->whereIn('purchase_order_services.status', ['Menunggu Diproses', 'Menunggu LPJ'])
      ->select(
        'purchase_order_services.*',
        DB::raw('COALESCE(request_services.service_code, direct_services.service_code) AS service_code'),
        DB::raw('COALESCE(request_services.service_name, direct_services.service_name) AS service_name'),
        'purchase_orders.purchase_order_number' // Ambil nomor purchase order
      )
      ->get();
    // dd($services);

    return response()->json([
      'items' => $items,
      'services' => $services
    ]);
  }

  public function addReceipts(Request $request)
  {
    try {
      DB::beginTransaction();

      // Membuat entri baru untuk receipt
      $receipt = Receipts::create([
        'receipt_number' => $request->receipt_number,
        'received_date' => $request->received_date,
        'received_by' => $request->received_by,
      ]);

      $processedPurchaseOrderServiceIds = [];
      $processedPurchaseOrderIds = [];

      // Proses jika `item_id` ada dan berupa array (Menerima Items Saja atau Kombinasi)
      if (is_array($request->item_id) && count($request->item_id) > 0) {
        foreach ($request->item_id as $index => $purchaseOrderItemId) {
          $purchaseOrderItem = PurchaseOrderItems::where('id', $purchaseOrderItemId)->first();

          if (!$purchaseOrderItem) {
            DB::rollback();
            return redirect()->back()->with('swal-fail', 'Purchase Order Item not found');
          }

          $purchaseRequestItem = PurchaseRequestItems::where('id', $purchaseOrderItem->purchase_request_item_id)->first();
          if (!$purchaseRequestItem) {
            DB::rollback();
            return redirect()->back()->with('swal-fail', 'Purchase Request Item not found');
          }

          $itemId = $purchaseRequestItem->item_id;
          $condition = $purchaseOrderItem->condition;

          if (!$condition) {
            DB::rollback();
            return redirect()->back()->with('swal-fail', 'Condition is required');
          }

          $officeWarehouseItem = OfficeWarehouse::where('item_id', $itemId)
            ->where('condition', $condition)
            ->first();

          if ($officeWarehouseItem) {
            $officeWarehouseItem->quantity += $request->received_quantity[$index];
            $officeWarehouseItem->save();
          } else {
            $conditions = ['Baru', 'Bekas Bisa Pakai', 'Bekas Tidak Bisa Pakai', 'Rekondisi'];

            foreach ($conditions as $cond) {
              OfficeWarehouse::create([
                'item_id' => $itemId,
                'quantity' => 0,
                'location' => '',
                'condition' => $cond
              ]);
            }

            $officeWarehouseItem = OfficeWarehouse::where('item_id', $itemId)
              ->where('condition', $condition)
              ->first();

            $officeWarehouseItem->quantity += $request->received_quantity[$index];
            $officeWarehouseItem->save();
          }

          $processedPurchaseOrderIds[] = $purchaseOrderItem->purchase_order_id;

          ReceiptItems::create([
            'receipt_id' => $receipt->id,
            'purchase_order_item_id' => $purchaseOrderItem->id,
            'serial_number' => $request->serial_number[$index],
            'received_quantity' => $request->received_quantity[$index],
          ]);

          $totalReceivedQuantity = ReceiptItems::where('purchase_order_item_id', $purchaseOrderItemId)
            ->sum('received_quantity');

          $purchaseOrderItem->status = $totalReceivedQuantity < $purchaseOrderItem->quantity ? 'Belum Selesai' : 'Selesai';
          $purchaseOrderItem->save();
        }
      }

      // Proses jika `service_id` ada dan berupa array (Menerima Services Saja atau Kombinasi)
      if (is_array($request->service_id) && count($request->service_id) > 0) {
        foreach ($request->service_id as $serviceId) {
          ReceiptServices::create([
            'receipt_id' => $receipt->id,
            'purchase_order_service_id' => $serviceId,
          ]);

          $purchaseOrderService = PurchaseOrderServices::where('id', $serviceId)->first();
          if ($purchaseOrderService) {
            $purchaseOrderService->status = 'Selesai';
            $purchaseOrderService->save();

            // Mendapatkan Purchase Request Service terkait jika ada
            if ($purchaseOrderService->purchase_request_service_id) {
              $purchaseRequestService = PurchaseRequestServices::where('id', $purchaseOrderService->purchase_request_service_id)->first();
              if ($purchaseRequestService) {
                $purchaseRequestService->status = 'Selesai';
                $purchaseRequestService->save();

                $purchaseRequestId = $purchaseRequestService->purchase_request_id;
                // Periksa apakah hanya ada PRS di PR terkait dan semuanya sudah selesai
                $allServicesCompleted = PurchaseRequestServices::where('purchase_request_id', $purchaseRequestId)
                  ->where('status', '!=', 'Selesai')
                  ->doesntExist();

                $hasItems = PurchaseRequestItems::where('purchase_request_id', $purchaseRequestId)->exists();

                if ($allServicesCompleted && !$hasItems) {
                  $purchaseRequest = PurchaseRequests::find($purchaseRequestId);
                  if ($purchaseRequest) {
                    $purchaseRequest->status = 'Selesai';
                    $purchaseRequest->save();
                  }
                }
              }
            }

            $processedPurchaseOrderServiceIds[] = $purchaseOrderService->id;
            $processedPurchaseOrderIds[] = $purchaseOrderService->purchase_order_id;
          }
        }
      }

      // Debugging: Cek PO yang akan diperbarui
      $processedPurchaseOrderIds = array_unique($processedPurchaseOrderIds);

      // Update status setiap Purchase Order berdasarkan POS dan POI terkait
      foreach ($processedPurchaseOrderIds as $purchaseOrderId) {
        $purchaseOrder = PurchaseOrders::find($purchaseOrderId);

        if (!$purchaseOrder) {
          // dd("Purchase Order ID: $purchaseOrderId not found");
          continue;
        }

        // dd("Purchase Order Number: " . $purchaseOrder->purchase_order_number);

        if (str_starts_with($purchaseOrder->purchase_order_number, 'Draft-')) {
          // dd("Skipping Draft PO: " . $purchaseOrder->purchase_order_number);
          continue;
        }

        // Cek apakah semua items dan services pada Purchase Order ini sudah selesai
        $allItemsCompleted = PurchaseOrderItems::where('purchase_order_id', $purchaseOrderId)
          ->where('status', '!=', 'Selesai')
          ->doesntExist();
        $allServicesCompleted = PurchaseOrderServices::where('purchase_order_id', $purchaseOrderId)
          ->where('status', '!=', 'Selesai')
          ->doesntExist();

        if ($allItemsCompleted && $allServicesCompleted) {
          $purchaseOrder->status = 'Selesai';
        } else {
          $purchaseOrder->status = 'Sebagian Selesai';
        }
        $purchaseOrder->save();
      }

      Logs::create([
        'user_id' => Auth::user()->id,
        'action' => 'created Receipt data ' . $request->receipt_number . ' in the Office Warehouse.',
      ]);

      DB::commit();

      return redirect()->back()->with('swal-success', 'Receipt successfully added');
    } catch (\Exception $e) {
      DB::rollback();
      return redirect()->back()->with('swal-fail', 'Failed to add Receipt: ' . $e->getMessage());
    }
  }

  public function deleteReceipts($receiptId)
  {
    try {
      DB::beginTransaction();

      // Retrieve the receipt and related receipt items
      $receipt = Receipts::findOrFail($receiptId);
      $receiptItems = ReceiptItems::where('receipt_id', $receipt->id)->get();

      $processedPurchaseOrderIds = [];

      foreach ($receiptItems as $receiptItem) {
        // Retrieve the related purchase order item
        $purchaseOrderItem = PurchaseOrderItems::where('id', $receiptItem->purchase_order_item_id)->first();

        if (!$purchaseOrderItem) {
          DB::rollback();
          return redirect()->back()->with('swal-fail', 'Purchase Order Item not found');
        }

        // Retrieve the purchase request item associated with the purchase order item
        $purchaseRequestItem = PurchaseRequestItems::where('id', $purchaseOrderItem->purchase_request_item_id)->first();

        if (!$purchaseRequestItem) {
          DB::rollback();
          return redirect()->back()->with('swal-fail', 'Purchase Request Item not found');
        }

        // Get the item_id and condition from the purchase order item
        $itemId = $purchaseRequestItem->item_id;
        $condition = $purchaseOrderItem->condition; // Use the actual condition of the item

        if (!$itemId) {
          DB::rollback();
          return redirect()->back()->with('swal-fail', 'Item ID not valid');
        }

        // Reverse the quantity in the OfficeWarehouse for the specific condition
        $officeWarehouseItem = OfficeWarehouse::where('item_id', $itemId)
          ->where('condition', $condition) // Use the condition from the purchase order item
          ->first();

        if ($officeWarehouseItem) {
          // Check if the quantity subtraction will cause a negative value
          if ($officeWarehouseItem->quantity < $purchaseOrderItem->quantity) {
            DB::rollback();
            return redirect()->back()->with([
              'swal-fail-title' => 'Quantity Insufficient',
              'swal-fail-text' => 'Cannot delete receipt because insufficient stock in Office Warehouse'
            ]);
          }

          // Subtract the quantity added previously
          $officeWarehouseItem->quantity -= $purchaseOrderItem->quantity;
          $officeWarehouseItem->save();
        }

        // Reverse the status of the purchase order item
        $purchaseOrderItem->status = 'Menunggu Diproses'; // Or another previous status
        $purchaseOrderItem->save();

        // Add the purchase order ID to the processed list
        $processedPurchaseOrderIds[] = $purchaseOrderItem->purchase_order_id;

        // Delete the receipt item entry
        $receiptItem->delete();
      }

      // Update the Purchase Order status based on item status
      $processedPurchaseOrderIds = array_unique($processedPurchaseOrderIds);

      foreach ($processedPurchaseOrderIds as $purchaseOrderId) {
        $purchaseOrder = PurchaseOrders::find($purchaseOrderId);

        // Check if all items are completed
        $allItemsCompleted = PurchaseOrderItems::where('purchase_order_id', $purchaseOrderId)
          ->where('status', 'Selesai')
          ->doesntExist();

        if ($allItemsCompleted) {
          // If all items are returned to previous status, set PO status to 'Menunggu Diproses'
          $purchaseOrder->status = 'Menunggu Diproses';
        } else {
          // If only some items are returned, set status to 'Sebagian Selesai'
          $purchaseOrder->status = 'Sebagian Selesai';
        }

        $purchaseOrder->save();
      }

      // Delete the receipt entry
      $receipt->delete();

      // Log the action
      Logs::create([
        'user_id' => Auth::user()->id,
        'action' => 'deleted Receipt data ' . $receipt->receipt_number . ' in the Office Warehouse.',
      ]);

      DB::commit();

      return redirect()->back()->with('swal-success', 'Receipt successfully deleted');
    } catch (\Exception $e) {
      DB::rollback();
      return redirect()->back()->with('swal-fail', 'Failed to delete Receipt: ' . $e->getMessage());
    }
  }

  public function getReceiptItems($id)
  {
    $receipt = Receipts::find($id);

    if (!$receipt) {
      return response()->json(['error' => 'Receipt not found'], 404);
    }

    $items = ReceiptItems::leftJoin('purchase_order_items', 'receipt_items.purchase_order_item_id', '=', 'purchase_order_items.id')
      ->leftJoin('purchase_request_items', 'purchase_order_items.purchase_request_item_id', '=', 'purchase_request_items.id')
      ->leftJoin('items', 'purchase_request_items.item_id', '=', 'items.id')
      ->leftJoin('purchase_orders', 'purchase_order_items.purchase_order_id', '=', 'purchase_orders.id')
      ->leftJoin('suppliers', 'purchase_orders.supplier_id', '=', 'suppliers.id')
      ->where('receipt_items.receipt_id', $id)
      ->select(
        'receipt_items.serial_number',
        'receipt_items.received_quantity',
        'items.item_pms',
        'items.item_name',
        'items.item_unit',
        'purchase_order_items.condition',
        'purchase_order_items.quantity',
        'purchase_orders.purchase_order_number',
        'suppliers.supplier_name'
      )
      ->get();

    try {
      $services = ReceiptServices::leftJoin('purchase_order_services', 'receipt_services.purchase_order_service_id', '=', 'purchase_order_services.id')
        ->leftJoin('purchase_orders', 'purchase_order_services.purchase_order_id', '=', 'purchase_orders.id')
        ->leftJoin('suppliers', 'purchase_orders.supplier_id', '=', 'suppliers.id')

        // Join ke purchase_request_services dan services untuk menangani dua kasus
        ->leftJoin('purchase_request_services', 'purchase_order_services.purchase_request_service_id', '=', 'purchase_request_services.id')
        ->leftJoin('services', function ($join) {
          $join->on('purchase_order_services.service_id', '=', 'services.id')
            ->orOn('purchase_request_services.service_id', '=', 'services.id');
        })
        ->where('receipt_services.receipt_id', $id)
        ->select(
          'services.service_name',
          'services.service_code',
          'purchase_order_services.utility as pos_utility',
          'purchase_order_services.price',
          'purchase_order_services.ppn',
          'purchase_order_services.status as pos_status',
          'purchase_orders.purchase_order_number',
          'suppliers.supplier_name'
        )
        ->get();
    } catch (\Exception $e) {
      Log::error("Error fetching services for receipt ID $id: " . $e->getMessage());
      return response()->json(['error' => 'Failed to fetch services'], 500);
    }

    return response()->json([
      'receipt' => $receipt,
      'items' => $items,
      'services' => $services,
    ]);
  }
}
