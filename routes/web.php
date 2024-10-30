<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'indexDashboard'])->name('indexDashboard');
    Route::get('/updatePassword', function () {
        return view('profile.show');
    })->name('updatePassword');
});
Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'indexDashboard'])->name('dashboard');

Route::get('/dashboard/search', [App\Http\Controllers\DashboardController::class, 'searchLogs'])->name('dashboard.search');

// Users
Route::get('/users', [App\Http\Controllers\UserController::class, 'indexUser'])->name('indexUser');
// Ships
Route::get('/ships', [App\Http\Controllers\ShipController::class, 'indexShip'])->name('indexShip');
Route::get('/findShip', [App\Http\Controllers\ShipController::class, 'findShip'])->name('findShip');
Route::post('/addShip', [App\Http\Controllers\ShipController::class, 'addShip'])->name('addShip');
Route::get('/deleteShip/{id}', [App\Http\Controllers\ShipController::class, 'deleteShip'])->name('deleteShip');
Route::post('/updateShip', [App\Http\Controllers\ShipController::class, 'updateShip'])->name('updateShip');
// Items
Route::get('/items', [App\Http\Controllers\ItemController::class, 'indexItem'])->name('indexItem');
Route::get('/findItem', [App\Http\Controllers\ItemController::class, 'findItem'])->name('findItem');
Route::post('/addItem', [App\Http\Controllers\ItemController::class, 'addItem'])->name('addItem');
Route::post('/updateItem', [App\Http\Controllers\ItemController::class, 'updateItem'])->name('updateItem');
Route::get('/deleteItem/{id}', [App\Http\Controllers\ItemController::class, 'deleteItem'])->name('deleteItem');
Route::get('/check-itemPMS', [App\Http\Controllers\ItemController::class, 'checkItemPMS'])->name('checkItemPMS');
// Services
Route::get('/services', [App\Http\Controllers\ServicesController::class, 'indexServices'])->name('indexServices');
Route::get('/findService', [App\Http\Controllers\ServicesController::class, 'findService'])->name('findService');
Route::post('/addService', [App\Http\Controllers\ServicesController::class, 'addService'])->name('addService');
Route::post('/updateService', [App\Http\Controllers\ServicesController::class, 'updateService'])->name('updateService');
Route::get('/deleteService/{id}', [App\Http\Controllers\ServicesController::class, 'deleteService'])->name('deleteService');
Route::get('/check-serviceCode', [App\Http\Controllers\ServicesController::class, 'checkServiceCode'])->name('checkServiceCode');
// Accounts
Route::get('/expenseAccounts', [App\Http\Controllers\ExpenseAccountsController::class, 'indexExpenseAccounts'])->name('indexExpenseAccounts');
Route::get('/findExpenseAccount', [App\Http\Controllers\ExpenseAccountsController::class, 'findExpenseAccount'])->name('findExpenseAccount');
Route::post('/addExpenseAccount', [App\Http\Controllers\ExpenseAccountsController::class, 'addExpenseAccount'])->name('addExpenseAccount');
Route::post('/updateExpenseAccount', [App\Http\Controllers\ExpenseAccountsController::class, 'updateExpenseAccount'])->name('updateExpenseAccount');
Route::get('/deleteExpenseAccount/{id}', [App\Http\Controllers\ExpenseAccountsController::class, 'deleteExpenseAccount'])->name('deleteExpenseAccount');
Route::get('/check-accountCode', [App\Http\Controllers\ExpenseAccountsController::class, 'checkAccountCode'])->name('checkAccountCode');
// Suppliers
Route::get('/suppliers', [App\Http\Controllers\SupplierController::class, 'indexSupplier'])->name('indexSupplier');
Route::post('/addSupplier', [App\Http\Controllers\SupplierController::class, 'addSupplier'])->name('addSupplier');
Route::post('/updateSupplier', [App\Http\Controllers\SupplierController::class, 'updateSupplier'])->name('updateSupplier');
Route::get('/findSupplier', [App\Http\Controllers\SupplierController::class, 'findSupplier'])->name('findSupplier');
Route::get('/deleteSupplier/{id}', [App\Http\Controllers\SupplierController::class, 'deleteSupplier'])->name('deleteSupplier');
// Office Warehouse
Route::get('/officeWarehouse', [App\Http\Controllers\OfficeWarehouseController::class, 'indexOfficeWarehouse'])->name('indexOfficeWarehouse');
Route::get('/findOfficeWarehouse', [App\Http\Controllers\OfficeWarehouseController::class, 'findOfficeWarehouse'])->name('findOfficeWarehouse');
Route::post('/addOfficeWarehouse', [App\Http\Controllers\OfficeWarehouseController::class, 'addOfficeWarehouse'])->name('addOfficeWarehouse');
Route::get('/checkOfficeWarehouse', [App\Http\Controllers\OfficeWarehouseController::class, 'checkOfficeWarehouse'])->name('checkOfficeWarehouse');
Route::post('/updateOfficeWarehouse', [App\Http\Controllers\OfficeWarehouseController::class, 'updateOfficeWarehouse'])->name('updateOfficeWarehouse');
Route::get('/deleteOfficeWarehouse/{id}', [App\Http\Controllers\OfficeWarehouseController::class, 'deleteOfficeWarehouse'])->name('deleteOfficeWarehouse');
// Ship Warehouses
Route::get('/shipWarehouses', [App\Http\Controllers\ShipWarehousesController::class, 'indexShipWarehouses'])->name('indexShipWarehouses');
Route::get('/data-shipWarehouses/{id}', [App\Http\Controllers\ShipWarehousesController::class, 'loadDataShipWarehouses'])->name('loadDataShipWarehouses');
Route::get('/checkShipWarehouses', [App\Http\Controllers\ShipWarehousesController::class, 'checkShipWarehouses'])->name('checkShipWarehouses');
Route::post('/addShipWarehouses', [App\Http\Controllers\ShipWarehousesController::class, 'addShipWarehouses'])->name('addShipWarehouses');
Route::post('/updateShipWarehouses', [App\Http\Controllers\ShipWarehousesController::class, 'updateShipWarehouses'])->name('updateShipWarehouses');
Route::get('/deleteShipWarehouses/{id}', [App\Http\Controllers\ShipWarehousesController::class, 'deleteShipWarehouses'])->name('deleteShipWarehouses');
Route::post('/addShipWarehouseUsages', [App\Http\Controllers\ShipWarehousesController::class, 'addShipWarehouseUsages'])->name('addShipWarehouseUsages');
Route::post('/addAdjustmentShipWarehouseUsages', [App\Http\Controllers\ShipWarehousesController::class, 'addAdjustmentShipWarehouseUsages'])->name('addAdjustmentShipWarehouseUsages');
Route::post('/updateShipWarehouseConditions', [App\Http\Controllers\ShipWarehousesController::class, 'updateShipWarehouseCondition'])->name('updateShipWarehouseCondition');
Route::post('/addShipWarehouseSendOffice', [App\Http\Controllers\ShipWarehousesController::class, 'addShipWarehouseSendOffice'])->name('addShipWarehouseSendOffice');
Route::post('/addAdjustmentShipWarehouseSendOffice', [App\Http\Controllers\ShipWarehousesController::class, 'addAdjustmentShipWarehouseSendOffice'])->name('addAdjustmentShipWarehouseSendOffice');
Route::get('/confirmAdjustmentShipWarehouseSendOffice/{shipId}', [App\Http\Controllers\ShipWarehousesController::class, 'confirmAdjustmentShipWarehouseSendOffice'])->name('confirmAdjustmentShipWarehouseSendOffice');
Route::get('/shipWarehouseUsage/{shipId}', [App\Http\Controllers\ShipWarehousesController::class, 'shipWarehouseUsage'])->name('shipWarehouseUsage');
Route::get('/confirmAdjustmentShipWarehouseUsage/{shipId}', [App\Http\Controllers\ShipWarehousesController::class, 'confirmAdjustmentShipWarehouseUsage'])->name('confirmAdjustmentShipWarehouseUsage');
Route::get('/deleteShipWarehouseUsage/{id}', [App\Http\Controllers\ShipWarehousesController::class, 'deleteShipWarehouseUsages'])->name('deleteShipWarehouseUsages');
Route::get('/shipWarehouseSendOffice/{shipId}', [App\Http\Controllers\ShipWarehousesController::class, 'shipWarehouseSendOffice'])->name('shipWarehouseSendOffice');
Route::get('/confirmShipWarehouseSendOffice/{id}', [App\Http\Controllers\ShipWarehousesController::class, 'confirmShipWarehouseSendOffice'])->name('confirmShipWarehouseSendOffice');
Route::get('/deleteShipWarehouseSendOffice/{id}', [App\Http\Controllers\ShipWarehousesController::class, 'deleteShipWarehouseSendOffice'])->name('deleteShipWarehouseSendOffice');
Route::post('/importShipWarehouses', [App\Http\Controllers\ShipWarehousesController::class, 'importShipWarehouses'])->name('importShipWarehouses');
Route::get('/get-itemsInShip', [App\Http\Controllers\ShipWarehousesController::class, 'getItemsInShip'])->name('getItemsInShip');
Route::get('/get-itemQuantity', [App\Http\Controllers\ShipWarehousesController::class, 'getItemQuantity'])->name('getItemQuantity');
// Purchase Requests
Route::get('/purchaseRequests', [App\Http\Controllers\PurchaseRequestsController::class, 'indexPurchaseRequests'])->name('indexPurchaseRequests');
Route::get('/data-purchaseRequests/{id}', [App\Http\Controllers\PurchaseRequestsController::class, 'loadDataPurchaseRequests'])->name('loadDataPurchaseRequests');
Route::get('generate-purchaseRequestNumber', [App\Http\Controllers\PurchaseRequestsController::class, 'generatePurchaseRequestNumber'])->name('generatePurchaseRequestNumber');;
Route::get('/check-purchaseRequestNumber', [App\Http\Controllers\PurchaseRequestsController::class, 'checkPurchaseRequestNumber'])->name('checkPurchaseRequestNumber');
Route::get('/get-itemName', [App\Http\Controllers\PurchaseRequestsController::class, 'getItemName'])->name('getItemName');
Route::get('/get-serviceName', [App\Http\Controllers\PurchaseRequestsController::class, 'getServiceName'])->name('getServiceName');
Route::post('/addPurchaseRequests', [App\Http\Controllers\PurchaseRequestsController::class, 'addPurchaseRequests'])->name('addPurchaseRequests');
Route::get('/get-purchaseRequestItems/{id}', [App\Http\Controllers\PurchaseRequestsController::class, 'getPurchaseRequestItems'])->name('getPurchaseRequestItems');
Route::post('/acceptPurchaseRequests', [App\Http\Controllers\PurchaseRequestsController::class, 'acceptPurchaseRequests'])->name('acceptPurchaseRequests');
Route::post('/rejectPurchaseRequests', [App\Http\Controllers\PurchaseRequestsController::class, 'rejectPurchaseRequests'])->name('rejectPurchaseRequests');
Route::get('/export-purchaseRequests', [App\Http\Controllers\PurchaseRequestsController::class, 'exportPurchaseRequests'])->name('exportPurchaseRequests');
Route::get('/get-automaticPurchaseRequests', [App\Http\Controllers\PurchaseRequestsController::class, 'getAutomaticPurchaseRequests'])->name('getAutomaticPurchaseRequests');
// Purchase Orders
Route::get('/purchaseOrders', [App\Http\Controllers\PurchaseOrdersController::class, 'indexPurchaseOrders'])->name('indexPurchaseOrders');
Route::get('/check-purchaseOrderNumber', [App\Http\Controllers\PurchaseOrdersController::class, 'checkPurchaseOrderNumber'])->name('checkPurchaseOrderNumber');
Route::get('/get-purchaseRequests', [App\Http\Controllers\PurchaseOrdersController::class, 'getPurchaseRequests'])->name('getPurchaseRequests');
Route::get('/get-purchaseOrders', [App\Http\Controllers\PurchaseOrdersController::class, 'getPurchaseOrders'])->name('getPurchaseOrders');
Route::get('/get-itemPurchaseRequests/{id}', [App\Http\Controllers\PurchaseOrdersController::class, 'getItemPurchaseRequests'])->name('getItemPurchaseRequests');
Route::get('/get-itemPurchaseOrders/{id}', [App\Http\Controllers\PurchaseOrdersController::class, 'getItemPurchaseOrders'])->name('getItemPurchaseOrders');
Route::post('/addPurchaseOrders', [App\Http\Controllers\PurchaseOrdersController::class, 'addPurchaseOrders'])->name('addPurchaseOrders');
Route::post('/addLPJ', [App\Http\Controllers\PurchaseOrdersController::class, 'addLPJ'])->name('addLPJ');
Route::get('/get-purchaseOrderItems/{id}', [App\Http\Controllers\PurchaseOrdersController::class, 'getPurchaseOrderItems'])->name('getPurchaseOrderItems');
Route::post('/acceptPurchaseOrders', [App\Http\Controllers\PurchaseOrdersController::class, 'acceptPurchaseOrders'])->name('acceptPurchaseOrders');
Route::post('/rejectPurchaseOrders', [App\Http\Controllers\PurchaseOrdersController::class, 'rejectPurchaseOrders'])->name('rejectPurchaseOrders');
Route::get('/print-purchaseOrders/{id}', [App\Http\Controllers\PurchaseOrdersController::class, 'printPurchaseOrders'])->name('printPurchaseOrders');
Route::get('/findPurchaseOrders', [App\Http\Controllers\PurchaseOrdersController::class, 'findPurchaseOrders'])->name('findPurchaseOrders');
Route::get('/get-services', [App\Http\Controllers\PurchaseOrdersController::class, 'getServices'])->name('getServices');
Route::get('/get-ship', [App\Http\Controllers\PurchaseOrdersController::class, 'getShip'])->name('getShip');
// Receipts
Route::get('/receipts', [App\Http\Controllers\ReceiptsController::class, 'indexReceipts'])->name('indexReceipts');
Route::get('/get-supplier', [App\Http\Controllers\ReceiptsController::class, 'getSupplier'])->name('getSupplier');
Route::get('/get-itemSuppliers/{id}', [App\Http\Controllers\ReceiptsController::class, 'getItemSuppliers'])->name('getItemSuppliers');
Route::post('/addReceipts', [App\Http\Controllers\ReceiptsController::class, 'addReceipts'])->name('addReceipts');
Route::get('/get-receiptItems/{id}', [App\Http\Controllers\ReceiptsController::class, 'getReceiptItems'])->name('getReceiptItems');
Route::get('/deleteReceipt/{id}', [App\Http\Controllers\ReceiptsController::class, 'deleteReceipts'])->name('deleteReceipts');
Route::get('/findReceipts', [App\Http\Controllers\ReceiptsController::class, 'findReceipts'])->name('findReceipts');
// Inventory Transfers
Route::get('/inventoryTransfers', [App\Http\Controllers\InventoryTransfersController::class, 'indexInventoryTransfers'])->name('indexInventoryTransfers');
Route::get('/data-inventoryTransfers/{id}', [App\Http\Controllers\InventoryTransfersController::class, 'loadDataInventoryTransfers'])->name('loadDataInventoryTransfers');
Route::post('/addInventoryTransfers', [App\Http\Controllers\InventoryTransfersController::class, 'addInventoryTransfers'])->name('addInventoryTransfers');
Route::get('/check-deliveryOrderNumber', [App\Http\Controllers\InventoryTransfersController::class, 'checkDeliveryOrderNumber'])->name('checkDeliveryOrderNumber');
Route::get('/get-purchaseRequestDone', [App\Http\Controllers\InventoryTransfersController::class, 'getPurchaseRequestDone'])->name('getPurchaseRequestDone');
Route::get('/get-inventoryTransferItems/{id}', [App\Http\Controllers\InventoryTransfersController::class, 'getInventoryTransferItems'])->name('getInventoryTransferItems');
Route::get('/print-deliveryOrders/{id}', [App\Http\Controllers\InventoryTransfersController::class, 'printDeliveryOrders'])->name('printDeliveryOrders');
Route::post('/updateInventoryTransfers', [App\Http\Controllers\InventoryTransfersController::class, 'updateInventoryTransfers'])->name('updateInventoryTransfers');
Route::get('/print-deliveryReceipts/{id}', [App\Http\Controllers\InventoryTransfersController::class, 'printDeliveryReceipts'])->name('printDeliveryReceipts');
