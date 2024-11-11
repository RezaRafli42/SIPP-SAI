@extends('layouts.layout')
<title>Purchase Order</title>
<meta name="csrf-token" content="{{ csrf_token() }}">
@section('main-panel')
    <div class="main-panel">
        <div class="content-wrapper">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    @if (session('swal-success'))
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                Swal.fire({
                                    title: '{{ session('swal-success') }}!',
                                    icon: 'success',
                                    showCancelButton: false,
                                });
                            });
                        </script>
                    @endif
                    @if (session('swal-fail'))
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                Swal.fire({
                                    title: '{{ session('swal-fail') }}!',
                                    icon: 'error',
                                    showCancelButton: false,
                                });
                            });
                        </script>
                    @endif
                    <div class="card-body">
                        <p class="card-title mb-2">Purchase Orders</p>
                        <div class="input-group mb-3">
                            <div class="input-group-append">
                                <select name="shipSelect" id="shipSelect"
                                    class="form-control pr-1 rounded-left text-center ">
                                    <option class="text-center" value="" selected>All</option>
                                    @foreach ($shipName as $ship)
                                        <option class="text-center" value="{{ $ship }}">
                                            {{ $ship }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- Search input -->
                            <input id="searchPurchaseOrders" type="text" class="form-control" placeholder="Search...">
                            <div class="input-group-append">
                                <button class="btn btn-sm btn-primary" type="button">Search</button>
                            </div>
                        </div>
                        @if ((Auth::user() && Auth::user()->role === 'Super Admin') || Auth::user()->role === 'Purchasing Logistic Admin')
                            <div class="input-group mb-3">
                                <button type="button" class="btn btn-success btn-icon-text mr-1" data-toggle="modal"
                                    data-target="#addPurchaseOrdersModal">
                                    <i class="mdi mdi-database-plus pr-2"></i>Add Purchase Order
                                </button>
                                <button type="button" class="btn btn-warning btn-icon-text" data-toggle="modal"
                                    data-target="#addLPJModal">
                                    <i class="mdi mdi-database-plus pr-2"></i>Add LPJ
                                </button>
                            </div>
                        @endif
                        <div class="row">
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table class="display expandable-table text-nowrap table-pills table-striped"
                                        style="width:100%" data-toggle="table" data-sortable="true">
                                        <thead>
                                            <tr class="text-center">
                                                <th data-sortable="true">No</th>
                                                <th data-sortable="true">PO No</th>
                                                <th data-sortable="true">Purchase Date</th>
                                                <th data-sortable="true">PIC</th>
                                                <th data-sortable="true">Item Count</th>
                                                <th data-sortable="true">Status</th>
                                                <th class="col-1">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="purchaseOrderTbody">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- content-wrapper ends -->
        <!-- Add Purchase Order Modal -->
        <div class="modal fade" id="addPurchaseOrdersModal" tabindex="-1" role="dialog"
            aria-labelledby="addPurchaseOrdersModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-custom" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addPurchaseOrdersModalLabel">Add Purchase Order</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="addPurchaseOrdersForm" method="POST" action="{{ url('addPurchaseOrders') }}"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-6 col-sm-4">
                                        <label for="newPurchaseOrderNumber"><span class="text-danger">*</span>No.
                                            PO</label>
                                        <div class="input-group mb-3">
                                            <div class="input-group-append-switch">
                                                <label class="switch">
                                                    <input type="checkbox" id="switchNewPurchaseOrderNumber"
                                                        class="switchBtn">
                                                    <span class="slider round"></span>
                                                </label>
                                            </div>
                                            <input required name="purchase_order_number" type="text" class="form-control"
                                                id="newPurchaseOrderNumber">
                                        </div>
                                        <div id="newPurchaseOrderNumber-validation" class="text-danger"></div>
                                    </div>
                                    <div class="col-6 col-sm-4">
                                        <label for="newPurchaseDate"><span class="text-danger">*</span>Purchase
                                            Date</label>
                                        <input required name="purchase_date" type="date" class="form-control"
                                            id="newPurchaseDate">
                                    </div>
                                    <div class="col-6 col-sm-4">
                                        <label for="newShipId"><span class="text-danger">*</span>Ship Name</label>
                                        <input required readonly name="ship_id" type="text" class="form-control"
                                            id="newShipId">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-6 col-sm-4">
                                        <label for="newPIC"><span class="text-danger">*</span>PIC</label>
                                        <input required name="pic" type="text" class="form-control"
                                            id="newPIC">
                                    </div>
                                    <div class="col-6 col-sm-4">
                                        <label for="newPICContact"><span class="text-danger">*</span>PIC Contact</label>
                                        <input required name="pic_contact" type="text" class="form-control"
                                            id="newPICContact">
                                    </div>
                                    <div class="col-6 col-sm-4">
                                        <label for="newNote">Note</label>
                                        <input name="note" type="text" class="form-control" id="newNote">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-6 col-sm-4">
                                        <label for="newSupplierID"><span class="text-danger">*</span>Supplier</label>
                                        <div class="supplier-dropdown">
                                            <input type="text" class="form-control" autocomplete="off"
                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                                id="supplierSearchInput" placeholder="Search Supplier" required>
                                            <input required type="hidden" id="selectedSupplierID" name="supplier_id">
                                            <div id="supplier-list" class="dropdown-menu"
                                                aria-labelledby="supplierSearchInput">
                                                @foreach ($suppliers as $supplier)
                                                    <a class="dropdown-item" href="#"
                                                        data-supplier-id="{{ $supplier->id }}"
                                                        data-supplier-currency="{{ $supplier->supplier_currency }}">{{ $supplier->supplier_name }}</a>
                                                @endforeach
                                            </div>
                                            <div id="supplierSearchValidation" class="text-danger"></div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-4">
                                        <label for="newCurrency"><span class="text-danger">*</span>Currency</label>
                                        <select class="selectpicker form-control" id="supplierCurrency"
                                            name="supplier_currency" required>
                                            <option selected disabled>
                                                -- Select Supplier First --</option>
                                        </select>
                                    </div>

                                    <div class="col-6 col-sm-4">
                                        <label for="newDeliveryAddress"><span class="text-danger"
                                                id="spanNewDeliveryAddress">*</span>Delivery
                                            Address</label>
                                        <select class="text-center" name="selectDeliveryAddress"
                                            id="selectDeliveryAddress">
                                            <option disabled selected value="">-- Select Address --</option>
                                            <option value="Kantor Pusat">Kantor Pusat</option>
                                            <option value="Gudang 338">Gudang 338</option>
                                        </select>
                                        <input required name="delivery_address" type="text" class="form-control"
                                            id="newDeliveryAddress">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-6 col-sm-4">
                                        <label for="jasaSearchInput"> Select Services</label>
                                        <div class="jasa-dropdown">
                                            <input type="text" class="form-control" autocomplete="off"
                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                                id="jasaSearchInput" placeholder="Select Services">
                                            <div id="jasa-list" class="dropdown-menu" aria-labelledby="jasaSearchInput">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-6 col-sm-4">
                                        <a class="btn btn-success mr-2" href="#" data-toggle="modal"
                                            data-target="#selectPurchaseRequestsModal">
                                            <i class="fa-solid fa-list"></i> Select PR
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div id="temporaryItem">
                                    <table class="display expandable-table" style="width:100%">
                                        <thead>
                                            <tr class="text-center">
                                                <th>No</th>
                                                <th>PMS Code</th>
                                                <th>Item Name</th>
                                                <th style="width: 120px"><span class="text-danger">*</span>Quantity</th>
                                                <th>Unit</th>
                                                <th style="width: 180px"><span class="text-danger">*</span>Condition</th>
                                                <th id="newPriceHeader" style="width: 150px"><span
                                                        class="text-danger">*</span>Price</th>
                                                <th style="width: 90px"><span class="text-danger">*</span>PPN
                                                    (%)
                                                </th>
                                                <th style="width: 90px"><span class="text-danger">*</span>PPh
                                                    (%)
                                                </th>
                                                <th>Option</th>
                                                <th><span class="text-danger">*</span>Utility</th>
                                                <th>PR No.</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Items will be appended here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" id="submitButton" class="btn btn-success">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Add LPJ Modal -->
        <div class="modal fade" id="addLPJModal" tabindex="-1" role="dialog" aria-labelledby="addLPJModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-custom" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addLPJModalLabel">Add LPJ</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="addLPJForm" method="POST" action="{{ url('addLPJ') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-8  col-sm-6">
                                        <label for="newLPJNumber"><span class="text-danger">*</span>No.
                                            LPJ</label>
                                        <input required name="lpj_number" type="text" class="form-control"
                                            id="newLPJNumber">
                                        <div id="newLPJNumber-validation" class="text-danger"></div>
                                    </div>
                                    <div class="col-8  col-sm-6">
                                        <label for="newLPJDate"><span class="text-danger">*</span>LPJ
                                            Date</label>
                                        <input required name="lpj_date" type="date" class="form-control"
                                            id="newLPJDate">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-8  col-sm-6">
                                        <label for="newPIC"><span class="text-danger">*</span>PIC</label>
                                        <input required name="pic" type="text" class="form-control"
                                            id="newPIC">
                                    </div>
                                    <div class="col-8  col-sm-6">
                                        <label for="newNote">Note</label>
                                        <input name="note" type="text" class="form-control" id="newNote">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-8  col-sm-6">
                                        <label for="newPurchaseOrderNumber"><span class="text-danger">*</span>No.
                                            PO</label>
                                        <div class="purchaseOrderNumber-dropdown">
                                            <input type="text" class="form-control" autocomplete="off"
                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                                id="searchPurchaseOrderNumber" name="searchPurchaseOrderNumber">
                                            <input required type="hidden" id="purchase_order_ids"
                                                name="purchase_order_ids">
                                            <div id="purchaseOrder-list" class="dropdown-menu"
                                                aria-labelledby="newPurchaseOrderNumber">
                                                <!-- Item list will be appended here -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group" style="margin-bottom: 0 !important">
                                <div id="temporaryItemDraft" style="margin-bottom: 0">
                                    <table class="display expandable-table" style="width:100%">
                                        <thead>
                                            <tr class="text-center">
                                                <th>No</th>
                                                <th>PMS Code</th>
                                                <th>Item Name</th>
                                                <th>Quantity</th>
                                                <th>Unit</th>
                                                <th>Condition</th>
                                                <th>Price</th>
                                                <th>PPN(%)</th>
                                                <th>PPh(%)</th>
                                                <th>Option</th>
                                                <th>Utility</th>
                                                <th>PR No.</th>
                                                <th>Amount</th>
                                            </tr>
                                        <tfoot>
                                            <tr class="text-center">
                                                <td colspan="12"
                                                    style="text-align: right; font-weight: bold; border: none">
                                                    Sub Total
                                                </td>
                                                <td id="subTotalLPJ" style="text-align: center; border: none">0</td>
                                            </tr>
                                            <tr class="text-center">
                                                <td colspan="12"
                                                    style="text-align: right; font-weight: bold; border: none">
                                                    Total PPN
                                                </td>
                                                <td id="totalPpnLPJ" style="text-align: center; border: none">0</td>
                                            </tr>
                                            <tr class="text-center">
                                                <td colspan="12"
                                                    style="text-align: right; font-weight: bold; border: none">
                                                    Total PPh
                                                </td>
                                                <td id="totalPphLPJ" style="text-align: center; border: none">0</td>
                                            </tr>
                                            <tr class="text-center">
                                                <td colspan="12"
                                                    style="text-align: right; font-weight: bold; border: none">
                                                    Total All
                                                </td>
                                                <td id="totalAllLPJ" style="text-align: center; border: none">0</td>
                                            </tr>
                                        </tfoot>
                                        </thead>
                                        <tbody>
                                            <!-- Items will be appended here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" id="submitButton" class="btn btn-success">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Select Purchase Requests Modal -->
        <div class="modal fade" id="selectPurchaseRequestsModal" tabindex="-1" role="dialog"
            aria-labelledby="selectPurchaseRequestsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="selectPurchaseRequestsModalLabel">Select Purchase Request</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="" method="" action="" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <div class="col-6">
                                    <label for="newPurchaseRequestNumber"><span class="text-danger">*</span>No. PR</label>
                                    <div class="purchaseRequestNumber-dropdown">
                                        <input type="text" class="form-control" autocomplete="off"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                            id="newPurchaseRequestNumber">
                                        <input required type="hidden" id="newPurchaseRequestNumber"
                                            name="purchase_request_id">
                                        <div id="purchaseRequest-list" class="dropdown-menu"
                                            aria-labelledby="newPurchaseRequestNumber">
                                            <!-- Item list will be appended here -->
                                        </div>
                                        <div id="newPurchaseRequestNumber-validation" class="text-danger"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div id="temporaryItem">
                                    <table class="display expandable-table" style="width:100%">
                                        <thead>
                                            <tr class="text-center">
                                                <th>No</th>
                                                <th>PMS Code</th>
                                                <th>Item Name</th>
                                                <th>Quantity</th>
                                                <th>Unit</th>
                                                <th>Option</th>
                                                <th>Utility</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Items will be appended here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Detail LPJ Modal --}}
        <div class="modal fade" id="detailLPJModal" tabindex="-1" role="dialog" aria-labelledby="detailLPJModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-custom" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="detailLPJModalLabel">Detail LPJ</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <div class="row">
                                <div class="col-8 col-sm-6">
                                    <label for="LPJNumber">No. LPJ</label>
                                    <input type="hidden" name="lpj_id" id="lpj_id">
                                    <input readonly name="lpj_number" type="text" class="form-control"
                                        id="LPJNumber">
                                </div>
                                <div class="col-8 col-sm-6">
                                    <label for="LPJDate">LPJ Date</label>
                                    <input readonly name="lpj_date" type="date" class="form-control" id="LPJDate">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-8 col-sm-6">
                                    <label for="PIC">PIC</label>
                                    <input readonly name="pic" type="text" class="form-control" id="PIC">
                                </div>
                                <div class="col-8 col-sm-6">
                                    <label for="note">Note</label>
                                    <input readonly name="note" type="text" class="form-control" id="note">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div id="temporaryItem">
                                <table class="display expandable-table" style="width:100%">
                                    <thead>
                                        <tr class="text-center">
                                            <th>No</th>
                                            <th>PMS Code</th>
                                            <th>Item Name</th>
                                            <th>Quantity</th>
                                            <th>Unit</th>
                                            <th>Condition</th>
                                            <th id="priceHeader">Price</th>
                                            <th>PPN (%)</th>
                                            <th>PPh (%)</th>
                                            <th>Option</th>
                                            <th>Utility</th>
                                            <th>PR No.</th>
                                            <th>Draft No.</th>
                                            <th id="ammountHeader">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemList">
                                        <!-- Items will be appended here -->
                                    </tbody>
                                    <tfoot>
                                        <tr class="text-center">
                                            <td colspan="13" style="text-align: right; font-weight: bold; border: none">
                                                Sub Total
                                            </td>
                                            <td id="subTotal" style="text-align: center; border: none">0</td>
                                        </tr>
                                        <tr class="text-center">
                                            <td colspan="13" style="text-align: right; font-weight: bold; border: none">
                                                Total PPN
                                            </td>
                                            <td id="totalPpn" style="text-align: center; border: none">0</td>
                                        </tr>
                                        <tr class="text-center">
                                            <td colspan="13" style="text-align: right; font-weight: bold; border: none">
                                                Total PPh
                                            </td>
                                            <td id="totalPph" style="text-align: center; border: none">0</td>
                                        </tr>
                                        <tr class="text-center">
                                            <td colspan="13" style="text-align: right; font-weight: bold; border: none">
                                                Total All
                                            </td>
                                            <td id="totalAll" style="text-align: center; border: none">0</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <a class="btn btn-primary" href="#" id="print"><i class="fa-solid fa-print"></i>
                                Print</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Purchase Order Modal -->
        <div class="modal fade" id="detailPurchaseOrderModal" tabindex="-1" role="dialog"
            aria-labelledby="detailPurchaseOrderModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-custom" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="detailPurchaseOrderModalLabel">Detail Purchase Order</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="acceptPurchaseOrdersForm" method="POST" action="{{ url('acceptPurchaseOrders') }}"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-6 col-sm-4">
                                        <label for="purchaseOrderNumber">No. PO</label>
                                        <input type="hidden" name="po_id" id="po_id">
                                        <input readonly name="purchase_order_number" type="text" class="form-control"
                                            id="purchaseOrderNumber">
                                    </div>
                                    <div class="col-6 col-sm-4">
                                        <label for="purchaseDate">Purchase Date</label>
                                        <input readonly name="purchase_date" type="date" class="form-control"
                                            id="purchaseDate">
                                    </div>
                                    <div class="col-6 col-sm-4 non-lpj">
                                        <label for="shipId">Ship Name</label>
                                        <input readonly required name="ship_id" type="text" class="form-control"
                                            id="shipId">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-6 col-sm-4">
                                        <label for="PIC">PIC</label>
                                        <input readonly name="pic" type="text" class="form-control"
                                            id="PIC">
                                    </div>
                                    <div class="col-6 col-sm-4 non-lpj">
                                        <label for="PICContact">PIC Contact</label>
                                        <input readonly name="pic_contact" type="text" class="form-control"
                                            id="PICContact">
                                    </div>
                                    <div class="col-6 col-sm-4">
                                        <label for="note">Note</label>
                                        <input readonly name="note" type="text" class="form-control"
                                            id="note">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-6 col-sm-4 non-lpj">
                                        <label for="supplier">Supplier</label>
                                        <input readonly name="supplier" type="text" class="form-control"
                                            id="supplier">
                                    </div>
                                    <div class="col-6 col-sm-4 non-lpj">
                                        <label for="currency">Currency</label>
                                        <input readonly required name="currency" type="text" class="form-control"
                                            id="currency">
                                    </div>

                                    <div class="col-6 col-sm-4 non-lpj">
                                        <label for="deliveryAddress">Delivery Address</label>
                                        <input readonly required name="delivery_address" type="text"
                                            class="form-control" id="deliveryAddress">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div id="temporaryItem">
                                    <table class="display expandable-table" style="width:100%">
                                        <thead>
                                            <tr class="text-center">
                                                <th>No</th>
                                                <th>PMS Code</th>
                                                <th>Item Name</th>
                                                <th style="width: 6%">Quantity</th>
                                                <th>Unit</th>
                                                <th>Condition</th>
                                                <th id="priceHeader">Price</th>
                                                <th>PPN (%)</th>
                                                <th>PPh (%)</th>
                                                <th>Option</th>
                                                <th>Utility</th>
                                                <th>PR No.</th>
                                                <th>Status</th>
                                                <th id="ammountHeader">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody id="itemList">
                                            <!-- Items will be appended here -->
                                        </tbody>
                                        <tfoot>
                                            <tr class="text-center">
                                                <td colspan="13"
                                                    style="text-align: right; font-weight: bold; border: none">
                                                    Sub Total
                                                </td>
                                                <td id="subTotal" style="text-align: center; border: none">0</td>
                                            </tr>
                                            <tr class="text-center">
                                                <td colspan="13"
                                                    style="text-align: right; font-weight: bold; border: none">
                                                    Total PPN
                                                </td>
                                                <td id="totalPpn" style="text-align: center; border: none">0</td>
                                            </tr>
                                            <tr class="text-center">
                                                <td colspan="13"
                                                    style="text-align: right; font-weight: bold; border: none">
                                                    Total PPh
                                                </td>
                                                <td id="totalPph" style="text-align: center; border: none">0</td>
                                            </tr>
                                            <tr class="text-center">
                                                <td colspan="13"
                                                    style="text-align: right; font-weight: bold; border: none">
                                                    Total All
                                                </td>
                                                <td id="totalAll" style="text-align: center; border: none">0</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="button" id="saveQuantitiesButton" class="btn btn-primary">Save</button>
                                <button type="submit" id="submitButton" class="btn btn-success">Accept</button>
                    </form>
                    <form id="rejectPurchaseOrdersForm" method="POST" action="{{ url('rejectPurchaseOrders') }}">
                        @csrf
                        <input type="hidden" name="po_id" id="po_id">
                        <button type="submit" id="rejectButton" class="btn btn-danger">Reject</button>
                    </form>
                    <a class="btn btn-warning" href="#" id="print"><i class="fa-solid fa-print"></i>
                        Print</a>
                </div>
            </div>
        </div>
    @endsection
    @section('script')
        {{-- Load Search Purchase Orders --}}
        <script>
            $(document).ready(function() {
                var purchaseOrdersData = @json($allOrders);

                function formatDate(dateString) {
                    var date = new Date(dateString);
                    var day = date.getDate();
                    var month = date.getMonth() + 1; // Bulan dimulai dari 0
                    var year = date.getFullYear();
                    // Menambahkan leading zero jika diperlukan
                    return (day < 10 ? '0' + day : day) + '/' + (month < 10 ? '0' + month : month) + '/' + year;
                }

                function renderAllPurchaseOrders() {
                    var tableBody = $('#purchaseOrderTbody');
                    tableBody.empty(); // Clear the table

                    if (purchaseOrdersData.length > 0) {
                        var no = 1;
                        purchaseOrdersData.forEach(function(order) {
                            // Cek tipe data untuk menentukan modal yang akan dibuka
                            var modalTarget = order.type === 'LPJ' ? '#detailLPJModal' :
                                '#detailPurchaseOrderModal';
                            var row = `<tr class="text-center">
                                    <th class="align-middle">${no++}</th>
                                    <td>${order.number || '-'}</td>
                                    <td>${formatDate(order.date)}</td>
                                    <td>${order.pic}</td>
                                    <td>${order.item_count || '-'}</td>
                                    <td>${order.status}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="${modalTarget}" data-purchaseorder='${JSON.stringify(order)}'>
                                                <i class="mdi mdi-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>`;
                            tableBody.append(row);
                        });
                    } else {
                        tableBody.html(
                            '<tr class="text-center"><td colspan="12" class="text-center text-muted">No records found</td></tr>'
                        );
                    }
                }

                // Tampilkan data semua kapal saat halaman dimuat pertama kali
                renderAllPurchaseOrders();
                let debounceTimeout;
                let lastSearch = ''; // Menyimpan nilai pencarian terakhir
                let lastShipName = ''; // Menyimpan nilai dropdown terakhir

                $('input[id="searchPurchaseOrders"]').on('keyup', function() {
                    var search = $(this).val(); // Ambil nilai pencarian saat ini

                    // Jika nilai pencarian tidak berubah, batalkan pencarian
                    if (search === lastSearch) {
                        return;
                    }

                    // Simpan nilai pencarian yang baru
                    lastSearch = search;
                    clearTimeout(debounceTimeout);
                    debounceTimeout = setTimeout(function() {
                        performSearch(search, $('select[id="shipSelect"]').val());
                    });
                });

                $('select[id="shipSelect"]').on('change', function() {
                    var shipName = $(this).val(); // Ambil nilai kapal saat ini

                    // Jika nilai dropdown kapal tidak berubah, batalkan pencarian
                    if (shipName === lastShipName) {
                        return;
                    }

                    // Simpan nilai dropdown kapal yang baru
                    lastShipName = shipName;

                    // Lakukan pencarian segera setelah dropdown berubah
                    performSearch($('input[id="searchPurchaseOrders"]').val(), shipName);
                });

                function performSearch(search, shipName) {
                    var tableBody = $('#purchaseOrderTbody');
                    // Tampilkan semua data jika pencarian kosong dan tidak ada kapal yang dipilih
                    if (search.length === 0 && (shipName === '' || shipName === 'All Ship')) {
                        renderAllPurchaseOrders();
                    } else {
                        // Tampilkan pesan "Searching for data..."
                        tableBody.html(
                            '<tr class="text-center"><td colspan="8" class="text-center text-muted">Searching for data...</td></tr>'
                        );
                        $.ajax({
                            url: "{{ url('findPurchaseOrders') }}",
                            method: 'GET',
                            data: {
                                search: search,
                                shipName: shipName // Kirim nama kapal juga
                            },
                            success: function(response) {
                                tableBody.empty(); // Kosongkan tabel
                                if (response.length > 0) {
                                    var no = 1;
                                    response.forEach(function(purchaseOrder) {
                                        var row = `<tr class="text-center">
                            <th class="align-middle">${no++}</th>
                            <td>${purchaseOrder.purchase_order_number}</td>
                            <td>${formatDate(purchaseOrder.purchase_date)}</td>
                            <td>${purchaseOrder.pic}</td>
                            <td>${purchaseOrder.item_count}</td> <!-- Display item count here -->
                            <td>${purchaseOrder.status}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#detailPurchaseOrderModal" data-purchaseorder='${JSON.stringify(purchaseOrder)}'>
                                        <i class="mdi mdi-eye"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>`;
                                        tableBody.append(row);
                                    });
                                } else {
                                    tableBody.html(
                                        '<tr class="text-center"><td colspan="12" class="text-center text-muted">No records found</td></tr>'
                                    );
                                }
                            },
                            error: function(xhr) {
                                tableBody.html(
                                    `<tr class="text-center"><td colspan="8" class="text-center text-danger">
                        Error: ${xhr.status} - ${xhr.responseText}
                    </td></tr>`
                                );
                            }
                        });
                    }
                }
            });
        </script>

        {{-- Add PO & Select PR Modal --}}
        <script>
            $(document).ready(function() {
                var addedShips = [];
                var addedItems = JSON.parse(localStorage.getItem('addedItems')) || [];
                var jasaSeparatorAdded = false;

                // Fungsi untuk mengupdate nilai input hidden ship_id berdasarkan barang atau jasa yang ada di tabel
                function updateShipId() {
                    var shipNames = []; // Array untuk menampung nama kapal yang ada

                    // Loop melalui semua baris di tabel untuk mendapatkan ship_name yang unik
                    $('#addPurchaseOrdersModal #temporaryItem tbody tr').each(function() {
                        var shipName = $(this).data('ship-name');
                        if (shipName && !shipNames.includes(shipName)) {
                            shipNames.push(shipName); // Tambahkan nama kapal ke array jika belum ada
                        }
                    });

                    // Update input hidden dengan semua ship_id yang terkait, dipisahkan dengan koma
                    var shipNamesString = shipNames.join(', ').replace(/,\s*$/,
                        ''); // Gabungkan dengan koma, hapus koma ekstra jika ada
                    $('#newShipId').val(shipNamesString);
                }

                // Fungsi untuk menghapus item dari tabel dan memperbarui ship_id
                $(document).on('click', '.remove-row', function() {
                    var itemRow = $(this).closest('tr');
                    var id_prd = itemRow.data('id_prd');
                    var shipNameToRemove = itemRow.data('ship-name');
                    var itemCategory = itemRow.data(
                        'category'); // Ambil kategori untuk cek apakah ini 'Service'

                    // Hapus item dari tabel
                    itemRow.remove();

                    // Hapus item dari array addedItems
                    var itemIndex = addedItems.indexOf(id_prd);
                    if (itemIndex > -1) {
                        addedItems.splice(itemIndex, 1);
                        localStorage.setItem('addedItems', JSON.stringify(addedItems));
                    }

                    // Periksa apakah masih ada item dari kapal yang sama
                    var stillHasShipItems = $(
                        '#addPurchaseOrdersModal #temporaryItem tbody tr[data-ship-name="' +
                        shipNameToRemove + '"]').length;

                    if (stillHasShipItems === 0) {
                        // Jika semua item dari kapal tersebut telah dihapus, hapus kapal dari array addedShips
                        var shipIndex = addedShips.indexOf(shipNameToRemove);
                        if (shipIndex > -1) {
                            addedShips.splice(shipIndex, 1);
                        }
                    }

                    // Periksa apakah masih ada jasa (service) di tabel
                    var stillHasServiceItems = $(
                        '#addPurchaseOrdersModal #temporaryItem tbody tr[data-category="Service"]').length;

                    // Jika tidak ada jasa tersisa, hapus separator jasa
                    if (stillHasServiceItems === 0) {
                        $('.jasa-separator').remove(); // Hapus separator jasa jika tidak ada jasa yang tersisa
                        jasaSeparatorAdded =
                            false; // Reset flag agar bisa menambah separator lagi jika ada jasa baru
                    }

                    // Perbarui input hidden ship_id
                    updateShipId();
                });

                $('#addPurchaseOrdersModal').on('show.bs.modal', function(e) {
                    var today = new Date().toISOString().split('T')[0];
                    $('#addPurchaseOrdersModal #newPurchaseDate').val(today);

                    // Handle search input
                    $('#supplierSearchInput').on('input', function() {
                        var keyword = $(this).val().toLowerCase();
                        $('#supplier-list .dropdown-item').each(function() {
                            var text = $(this).text().toLowerCase();
                            if (text.indexOf(keyword) > -1) {
                                $(this).show();
                            } else {
                                $(this).hide();
                            }
                        });
                        $('.dropdown-menu').addClass('show-dropdown');
                    });

                    // Handle supplier selection
                    $('#supplier-list').on('click', '.dropdown-item', function(e) {
                        e.preventDefault();
                        var supplierID = $(this).data('supplier-id');
                        var supplierName = $(this).text();
                        var supplierCurrencies = $(this).data('supplier-currency');
                        $('#newPriceHeader').text('Price');
                        $('#selectedSupplierID').val(supplierID);
                        $('#supplierSearchInput').val(supplierName);
                        $('.dropdown-menu').removeClass('show-dropdown');
                        // Populate the currency select dropdown based on selected supplier
                        var currencyArray = supplierCurrencies.split(',');
                        $('#supplierCurrency').empty();
                        $('#supplierCurrency').val('');
                        $('#supplierCurrency').append(
                            '<option selected disabled value="">-- Select Currency --</option>');
                        currencyArray.forEach(function(currency) {
                            $('#supplierCurrency').append('<option value="' + currency + '">' +
                                currency + '</option>');
                        });
                        $('#supplierCurrency').selectpicker('refresh');
                    });

                    $('#supplierCurrency').on('change', function() {
                        var selectedCurrency = $(this).val();
                        var row = $('#addPurchaseOrdersModal #temporaryItem tbody tr');
                        // Enable/disable the PPN input based on currency
                        if (selectedCurrency === ' IDR' || selectedCurrency === 'IDR') {
                            row.find('input[name="ppn[]"]').attr('readonly', false);
                        } else {
                            row.find('input[name="ppn[]"]').val(0);
                            row.find('input[name="ppn[]"]').attr('readonly', true);
                        }
                        // Update the price column header with the selected currency
                        if (selectedCurrency) {
                            $('#newPriceHeader').text(`Price (${selectedCurrency})`);
                        } else {
                            $('#newPriceHeader').text('Price');
                        }
                    });

                    $('#newSupplierID').on('change', function() {
                        var selectedSupplier = $(this).find('option:selected');
                        var currencies = selectedSupplier.data('currencies');
                        var currencyArray = currencies.split(',');
                        $('#newPriceHeader').text('Price');
                        $('#supplierCurrency').empty();
                        $('#supplierCurrency').val('');
                        $('#supplierCurrency').append(
                            '<option selected disabled value="">-- Select Currency --</option>');
                        currencyArray.forEach(function(currency) {
                            $('#supplierCurrency').append('<option value="' + currency + '">' +
                                currency + '</option>');
                        });
                        $('#supplierCurrency').selectpicker('refresh');
                        var row = $('#addPurchaseOrdersModal #temporaryItem tbody tr');
                        if ($(this).val() === ' IDR' || selectedCurrency === 'IDR') {
                            row.find('input[name="ppn[]"]').attr('readonly', false);
                        } else {
                            row.find('input[name="ppn[]"]').val(0);
                            row.find('input[name="ppn[]"]').attr('readonly', true);
                        }
                    });

                    // Cek duplikasi nomor PO
                    $('#addPurchaseOrdersModal input[name="purchase_order_number"]').on('input', function() {
                        var POno = $(this).val();
                        $.ajax({
                            type: "get",
                            url: "{{ url('check-purchaseOrderNumber') }}",
                            data: "POno=" + POno,
                            success: function(response) {
                                if (response.exists) {
                                    $('#addPurchaseOrdersModal #newPurchaseOrderNumber-validation')
                                        .text('Purchase order number already used.');
                                    $('#addPurchaseOrdersModal button[id=submitButton]')
                                        .attr('disabled', true);
                                } else {
                                    $('#addPurchaseOrdersModal #newPurchaseOrderNumber-validation')
                                        .text('');
                                    $('#addPurchaseOrdersModal button[id=submitButton]')
                                        .attr('disabled', false);
                                }
                            },
                            error: function() {
                                alert('Error');
                            }
                        });
                    });

                    $("#selectDeliveryAddress").on('change', function() {
                        var pilihan = $("#selectDeliveryAddress").val();
                        if (pilihan === 'Kantor Pusat') {
                            $("#addPurchaseOrdersModal #newDeliveryAddress").val(
                                'Ruko Puri Mutiara Blok BG No.2-3, RT.2/RW.5, Sunter Agung, Tanjung Priok, North Jakarta City, Jakarta 14350'
                            );
                        } else if (pilihan === 'Gudang 338') {
                            $("#addPurchaseOrdersModal #newDeliveryAddress").val(
                                'Ruko The Linq No.338 Jl.Trembesi Kemayoran'
                            );
                        }
                    });

                    // Switch untuk PO Number
                    document.getElementById('switchNewPurchaseOrderNumber').addEventListener('change',
                        function() {
                            const poInput = document.getElementById('newPurchaseOrderNumber');
                            const addressInput = document.getElementById('newDeliveryAddress');
                            const spanInput = document.getElementById('spanNewDeliveryAddress');
                            if (this.checked) {
                                poInput.value = 'Draft-';
                                poInput.readOnly = false;
                                addressInput.required = false;
                                spanInput.style.display = 'none';
                                // Listener untuk memastikan "draft-" tidak terhapus
                                poInput.addEventListener('input', preventDraftRemoval);
                                // Memastikan kursor berada setelah teks "draft-"
                                setCaretPosition(poInput, poInput.value.length);
                            } else {
                                poInput.value = '';
                                poInput.readOnly = false;
                                addressInput.required = true;
                                spanInput.style.display = 'inline';
                                poInput.removeEventListener('input', preventDraftRemoval);
                            }
                        });

                    function preventDraftRemoval(event) {
                        const draftText = 'Draft-';
                        if (!event.target.value.startsWith(draftText)) {
                            event.target.value = draftText;
                        }
                    }

                    function setCaretPosition(ctrl, pos) {
                        if (ctrl.setSelectionRange) {
                            ctrl.focus();
                            ctrl.setSelectionRange(pos, pos);
                        }
                    }

                    $.ajax({
                        type: "GET",
                        url: "{{ url('get-services') }}",
                        success: function(response) {
                            var serviceList = $('#addPurchaseOrdersModal #jasa-list');
                            serviceList.empty();
                            // Tambahkan jasa ke dalam dropdown
                            $.each(response.jasa_items, function(index, service) {
                                var newLink = $('<a></a>')
                                    .addClass('dropdown-item')
                                    .attr('href', '#')
                                    .attr('data-service-id', service.id)
                                    .attr('data-service-account-id', service.account_id)
                                    .attr('data-service-code', service.service_code)
                                    .attr('data-service-name', service.service_name)
                                    .text(service.service_code + ' - ' + service
                                        .service_name);
                                serviceList.append(newLink);
                            });
                        },
                        error: function(xhr) {
                            console.log(xhr.responseText);
                            alert('Gagal mengambil data. Silakan coba lagi.');
                        }
                    });

                    $('#jasaSearchInput').on('input', function() {
                        var keyword = $(this).val().toLowerCase();
                        $('#jasa-list .dropdown-item').each(function() {
                            var text = $(this).text().toLowerCase();
                            if (text.indexOf(keyword) > -1) {
                                $(this).show();
                            } else {
                                $(this).hide();
                            }
                        });
                        $('.dropdown-menu').addClass('show-dropdown');
                    });

                    $('#jasa-list').off('click', '.dropdown-item');
                    $('#jasa-list').on('click', '.dropdown-item', function(e) {
                        e.preventDefault();
                        var serviceID = $(this).data('service-id');
                        var serviceCode = $(this).data('service-code');
                        var serviceName = $(this).data('service-name');

                        // Tambahkan service ke tabel sebagai jasa
                        addItemToPurchaseOrder('service-' + serviceID, serviceCode, serviceName, 1, '',
                            '', '', '', 'Service', ''); // 'Service' sebagai kategori
                    });
                });

                // Fungsi untuk update nomor baris tabel (untuk Items, tidak untuk Services)
                function updateTableRowNumbers() {
                    var tableBody = $('#addPurchaseOrdersModal #temporaryItem tbody');
                    var rowNumber = 1;
                    tableBody.find('tr').each(function() {
                        var category = $(this).data('category');
                        if (category !== 'Service' && !$(this).hasClass(
                                'jasa-separator')) { // Nomor urut untuk items saja
                            $(this).find('td:first').text(rowNumber);
                            rowNumber++;
                        }
                    });
                }

                // Function to add item to the purchase order table
                function addItemToPurchaseOrder(itemID, itemCode, itemName, itemQuantity, itemUnit, itemOption,
                    itemUtility, itemPRno, itemCategory, shipName) {
                    // ** Hilangkan batasan penambahan service (duplikasi jasa diperbolehkan) **
                    if (itemCategory !== 'Service' && addedItems.includes(itemID)) {
                        alert('Item sudah ditambahkan!');
                        return;
                    }

                    addedItems.push(itemID);
                    localStorage.setItem('addedItems', JSON.stringify(addedItems));

                    var tableBody = $('#addPurchaseOrdersModal #temporaryItem tbody');

                    if (itemCategory === 'Service' && !jasaSeparatorAdded) {
                        var separatorRow = `
                        <tr class="text-center jasa-separator">
                            <td colspan="12"><strong>Services (Jasa)</strong></td>
                        </tr>`;
                        tableBody.append(separatorRow);
                        jasaSeparatorAdded = true;
                    }

                    var rowNumber = itemCategory === 'Service' ? 'Jasa' : (tableBody.find('tr[data-category="Item"]')
                        .length + 1);
                    var unitInput = itemCategory === 'Service' ? '' : itemUnit;
                    var conditionInput = itemCategory === 'Service' ? '<td></td>' : `
                        <td>
                            <select required name="condition[]" class="form-control text-center">
                                <option value="" selected disabled>Select condition</option>
                                <option value="Baru">Baru</option>
                                <option value="Bekas Bisa Pakai">Bekas Bisa Pakai</option>
                                <option value="Rekondisi">Rekondisi</option>
                            </select>
                        </td>`;
                    var priceInput = itemCategory === 'Service' ?
                        `<input type="number" name="price_service[]" class="form-control text-center prize-input" step="0.01" required>` :
                        `<input type="number" name="price_item[]" class="form-control text-center prize-input" step="0.01" required>`;
                    var ppnInput = itemCategory === 'Service' ?
                        '<input type="number" name="ppn_service[]" class="form-control text-center" value="0" min="0">' :
                        `<input type="number" name="ppn[]" class="form-control text-center" value="0" min="0">`;
                    var pphInput = itemCategory === 'Service' ?
                        '<input type="number" name="pph_service[]" class="form-control text-center" value="0" min="0" step="any">' :
                        ``;
                    var quantityInput = itemCategory === 'Service' ?
                        `1` :
                        `<input type="number" name="quantity[]" class="form-control text-center quantity-input" value="${itemQuantity}" min="1" required>`;
                    var utilityInput = itemCategory === 'Service' ?
                        `<input type="text" name="utility[]" class="form-control text-center" required value="${itemUtility}">` :
                        itemUtility;
                    var hiddenInput = itemCategory === 'Service' ?
                        `<input type="hidden" name="purchase_request_service_id[]" value="${itemID}" required>` :
                        `<input type="hidden" name="purchase_request_item_id[]" value="${itemID}" required>`;

                    var row = `
                    <tr data-id_prd="${itemID}" data-ship-name="${shipName}" data-category="${itemCategory}">
                        <td>${rowNumber}</td>
                        <td>${itemCode}</td>
                        <td>${itemName}</td>
                        <td>${quantityInput}</td>
                        <td>${unitInput}</td>
                        ${conditionInput}
                        <td>${priceInput}</td>
                        <td>${ppnInput}</td>
                        <td>${pphInput}</td>
                        <td>${itemOption}</td>
                        <td>${utilityInput}</td>
                        <td>${itemPRno ? itemPRno : ''}</td>
                        <td><button type="button" class="btn btn-danger remove-row">Remove</button></td>
                        ${hiddenInput}
                    </tr>`;

                    if (itemCategory === 'Service') {
                        tableBody.append(row);
                    } else {
                        if (jasaSeparatorAdded) {
                            tableBody.find('.jasa-separator').before(row);
                        } else {
                            tableBody.append(row);
                        }
                    }

                    updateTableRowNumbers();
                    updateShipId();
                }

                // Event listener ketika membuka modal Select Purchase Request
                $('#selectPurchaseRequestsModal').on('show.bs.modal', function(e) {
                    $('#addPurchaseOrdersModal').addClass('modal-backdrop');
                    $.ajax({
                        type: "get",
                        url: "{{ url('get-purchaseRequests') }}",
                        success: function(response) {
                            var purchaseRequestList = $('#purchaseRequest-list');
                            purchaseRequestList.empty();
                            $.each(response, function(index, purchaseRequest) {
                                var newLink = $('<a></a>')
                                    .addClass('dropdown-item purchaseRequest-dropdown')
                                    .attr('href', '#')
                                    .attr('data-purchase-request-id', purchaseRequest.id)
                                    .text(purchaseRequest.purchase_request_number);
                                purchaseRequestList.append(newLink);
                            });
                        }
                    });
                    $('#selectPurchaseRequestsModal #newPurchaseRequestNumber').on('input', function() {
                        var keyword = $(this).val().toLowerCase();
                        $('#selectPurchaseRequestsModal .dropdown-item').each(function() {
                            var text = $(this).text().toLowerCase();
                            if (text.indexOf(keyword) > -1) {
                                $(this).show();
                            } else {
                                $(this).hide();
                            }
                        });
                        $('.dropdown-menu').addClass('show-dropdown');
                    });
                });

                $('#selectPurchaseRequestsModal').on('hidden.bs.modal', function() {
                    $('#addPurchaseOrdersModal').removeClass('modal-backdrop');
                    if ($('.modal.show').length) {
                        $('body').addClass('modal-open');
                    }
                });

                // Event listener ketika menutup modal Add Purchase Order
                $('#addPurchaseOrdersModal').on('hidden.bs.modal', function() {
                    $('#temporaryItem tbody').empty();
                    $('#newPurchaseRequestNumber-validation').text('');
                    addedItems = [];
                    localStorage.removeItem('addedItems');
                    addedShips = [];
                    localStorage.removeItem('addedShips');
                    $('#newShipId').val('');
                    $('#supplierCurrency').selectpicker('refresh');
                });

                // Event listener ketika menambahkan item dari modal Select Purchase Requests
                $(document).on('click', '.add-item', function() {
                    var itemID = $(this).data('item-id');
                    var itemCode = $(this).data('item-code');
                    var itemName = $(this).data('item-name');
                    var itemQuantity = $(this).data('item-quantity');
                    var itemUnit = $(this).data('item-unit');
                    var itemOption = $(this).data('item-option');
                    var itemUtility = $(this).data('item-utility');
                    var itemPRno = $(this).data('item-prno');
                    var itemCategory = $(this).data('item-category');
                    var shipName = $(this).data('ship-name');

                    addItemToPurchaseOrder(itemID, itemCode, itemName, itemQuantity, itemUnit, itemOption,
                        itemUtility, itemPRno, itemCategory, shipName);

                    $(this).replaceWith('<span class="text-success">Added</span>');
                });

                // Fungsi untuk mengambil item purchase request
                function loadPurchaseRequestItems(purchaseRequestID) {
                    $.ajax({
                        url: '/get-itemPurchaseRequests/' + purchaseRequestID,
                        type: 'GET',
                        success: function(response) {
                            var tableBody = $('#selectPurchaseRequestsModal #temporaryItem tbody');
                            tableBody.empty(); // Kosongkan tabel sebelum render ulang

                            // Render items terlebih dahulu
                            if (response.items && response.items.length > 0) {
                                response.items.forEach(function(item, index) {
                                    var rowClass = '';
                                    if (item.total_quantity < item.minimum_quantity) {
                                        rowClass =
                                            'bg-light-red'; // Warna background jika total_quantity kurang dari minimum_quantity
                                    }

                                    var itemData = item.items;
                                    if (itemData) {
                                        var addButton = `
                                            <button type="button" class="btn btn-success add-item" 
                                                data-item-id="${item.id}" 
                                                data-item-code="${itemData.item_pms}" 
                                                data-item-name="${itemData.item_name}" 
                                                data-item-quantity="${item.quantity}" 
                                                data-item-unit="${itemData.item_unit}"
                                                data-item-utility="${item.utility}"
                                                data-item-option="${item.option}"
                                                data-item-prno="${item.purchase_request.purchase_request_number}"
                                                data-item-category="${itemData.item_category}"
                                                data-ship-name="${item.purchase_request.ships.ship_name}">
                                                Add
                                            </button>`;

                                        // Jika item sudah ditambahkan, tampilkan "Added"
                                        if (addedItems.includes(item.id)) {
                                            addButton = '<span class="text-success">Added</span>';
                                        } else if (item.status !== 'Menunggu Diproses') {
                                            addButton =
                                                ''; // Jika status bukan "Menunggu Diproses", sembunyikan tombol Add
                                        }

                                        // Tampilkan item dalam tabel
                                        var row = `
                                            <tr class="text-center ${rowClass}">
                                                <th>${index + 1}</th>
                                                <td>${itemData.item_pms}</td>
                                                <td>${itemData.item_name}</td>
                                                <td>${item.quantity}</td>
                                                <td>${itemData.item_unit}</td>
                                                <td>${item.option}</td>
                                                <td>${item.utility}</td>
                                                <td>${item.status}</td>
                                                <td>${addButton}</td>
                                            </tr>`;
                                        tableBody.append(row);
                                    } else {
                                        console.error("Item data not found for item with ID: " +
                                            item.id);
                                    }
                                });
                            } else {
                                tableBody.append(
                                    '<tr class="text-center"><td colspan="13">No items available.</td></tr>'
                                );
                            }

                            // Tambahkan separator sebelum jasa
                            if (response.services && response.services.length > 0) {
                                var separatorRow = `
                                    <tr class="text-center jasa-separator">
                                        <td colspan="12"><strong>Services (Jasa)</strong></td>
                                    </tr>`;
                                tableBody.append(separatorRow);

                                // Render services setelah separator
                                response.services.forEach(function(service, index) {
                                    var serviceData = service.services;
                                    if (serviceData) {
                                        var addButton = `
                                            <button type="button" class="btn btn-success add-item" 
                                                data-item-id="${service.id}" 
                                                data-item-code="${serviceData.service_code}" 
                                                data-item-name="${serviceData.service_name}" 
                                                data-item-quantity="-" 
                                                data-item-unit="-" 
                                                data-item-utility="${service.utility}" 
                                                data-item-option=""
                                                data-item-prno="${service.purchase_request.purchase_request_number}"
                                                data-item-category="Service"
                                                data-ship-name="${service.purchase_request.ships.ship_name}">
                                                Add
                                            </button>`;

                                        // Jika service sudah ditambahkan, tampilkan "Added"
                                        if (addedItems.includes(service.id)) {
                                            addButton = '<span class="text-success">Added</span>';
                                        } else if (service.status !== 'Menunggu Diproses') {
                                            addButton =
                                                ''; // Jika status bukan "Menunggu Diproses", sembunyikan tombol Add
                                        }

                                        // Tampilkan service dalam tabel
                                        var row = `
                                            <tr class="text-center">
                                                <th>Jasa</th>
                                                <td>${serviceData.service_code}</td>
                                                <td>${serviceData.service_name}</td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td>${service.utility}</td>
                                                <td>${service.status}</td>
                                                <td>${addButton}</td>
                                            </tr>`;
                                        tableBody.append(row);
                                    } else {
                                        console.error(
                                            "Service data not found for service with ID: " +
                                            service.id);
                                    }
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error(error);
                            $('#newPurchaseRequestNumber-validation').text(
                                'Failed to load items and services for the selected purchase request.');
                        }
                    });
                }

                $('#purchaseRequest-list').on('click', '.dropdown-item', function(e) {
                    e.preventDefault();
                    var purchaseRequestID = $(this).data('purchase-request-id');
                    $('#newPurchaseRequestNumber').val($(this).text());
                    $('#newPurchaseRequestNumber').data('purchase-request-id', purchaseRequestID);
                    $('#newPurchaseRequestNumber-validation').text('');
                    loadPurchaseRequestItems(purchaseRequestID);
                });

                // Event listener ketika menutup modal Select Purchase Request
                $('#selectPurchaseRequestsModal').on('hidden.bs.modal', function() {
                    $('#addPurchaseOrdersModal').removeClass('modal-backdrop');
                    if ($('.modal.show').length) {
                        $('body').addClass('modal-open');
                    }
                });

                $(window).on('beforeunload', function() {
                    addedItems = []; // Reset added items when the page is reloaded or closed
                    localStorage.removeItem('addedItems'); // Clear addedItems from localStorage on page unload
                    addedShips = [];
                    localStorage.removeItem('addedShips');
                });
            });

            $('#selectPurchaseRequestsModal').on('hidden.bs.modal', function(e) {
                $('#addPurchaseOrdersModal').removeClass('modal-backdrop');
                if ($('.modal.show').length) {
                    $('body').addClass('modal-open');
                }
            });
        </script>

        {{-- Clear Table when Close Modal --}}
        <script>
            $('#addPurchaseOrdersModal, #detailPurchaseOrderModal').on(
                'hidden.bs.modal',
                function(e) {
                    var tableBody = $(
                        '#temporaryItem tbody');
                    tableBody.empty();
                    $('#supplierCurrency').empty();
                    $('#supplierCurrency').val('');
                });
            $('#selectPurchaseRequestsModal').on('hidden.bs.modal', function(e) {
                var tableBody = $('#selectPurchaseRequestsModal #temporaryItem tbody');
                tableBody.empty(); // Menghapus semua baris di tabel
                $('#newPurchaseRequestNumber').val(''); // Mengosongkan input nomor PR
            });
        </script>

        {{-- Detail Purchase Order Modal --}}
        <script>
            $(document).ready(function() {
                // Menghitung perubahan quantity
                function recalculateTotals() {
                    let subTotal = 0;
                    let totalPpn = 0;
                    let totalPph = 0;

                    $('#detailPurchaseOrderModal #temporaryItem tbody tr').each(function() {
                        const $row = $(this);
                        const quantity = parseFloat($row.find('.quantity-input').val()) || 0;
                        const price = parseFloat($row.find('.price-cell').data('price')) || 0;
                        const ppn = parseFloat($row.find('.ppn-cell').data('ppn')) || 0;
                        const pph = parseFloat($row.find('.pph-cell').data('pph')) || 0;

                        // Hitung ulang amount untuk setiap item
                        const amount = quantity * price;
                        const ppnAmount = amount * (ppn / 100);
                        const pphAmount = amount * (pph / 100);
                        const amountWithTaxes = amount + ppnAmount - pphAmount;

                        // Update jumlah untuk item ini di tabel
                        $row.find('.amount-cell').text(amountWithTaxes.toLocaleString('id-ID'));

                        // Tambahkan amount item ini ke subtotal dan total PPN serta PPh
                        subTotal += amount;
                        totalPpn += ppnAmount;
                        totalPph += pphAmount;
                    });

                    // Update total keseluruhan di footer
                    $('#detailPurchaseOrderModal #subTotal').text(subTotal.toLocaleString('id-ID'));
                    $('#detailPurchaseOrderModal #totalPpn').text(totalPpn.toLocaleString('id-ID'));
                    $('#detailPurchaseOrderModal #totalPph').text(totalPph.toLocaleString('id-ID'));
                    $('#detailPurchaseOrderModal #totalAll').text((subTotal + totalPpn - totalPph).toLocaleString(
                        'id-ID'));
                }
                $(document).on('input', '.quantity-input', function() {
                    recalculateTotals();
                });
                $('#detailPurchaseOrderModal').on('show.bs.modal', function(e) {
                    var button = $(e.relatedTarget);
                    var itemData = button.data('purchaseorder');
                    if (itemData) {
                        $('#detailPurchaseOrderModal #po_id').val(itemData.id);
                        $('#detailPurchaseOrderModal #purchaseDate').val(itemData.date);
                        $('#detailPurchaseOrderModal #purchaseOrderNumber').val(itemData.number);
                        $('#detailPurchaseOrderModal #supplier').val(itemData.supplier_name);
                        $('#detailPurchaseOrderModal #currency').val(itemData.currency);
                        $('#detailPurchaseOrderModal #shipId').val(itemData.ship_id);
                        $('#detailPurchaseOrderModal #PIC').val(itemData.pic);
                        $('#detailPurchaseOrderModal #PICContact').val(itemData.pic_contact);
                        $('#detailPurchaseOrderModal #deliveryAddress').val(itemData.delivery_address);
                        $('#detailPurchaseOrderModal #note').val(itemData.note);
                        $('#detailPurchaseOrderModal').find('a[id="print"]').attr('href',
                            '/print-purchaseOrders/' + itemData.id);
                        if (itemData.status === "Diajukan") {
                            $('#detailPurchaseOrderModal #submitButton').show();
                            $('#detailPurchaseOrderModal #rejectButton').show();
                            $('#detailPurchaseOrderModal #print').hide();
                        } else {
                            $('#detailPurchaseOrderModal #submitButton').hide();
                            $('#detailPurchaseOrderModal #rejectButton').hide();
                            $('#detailPurchaseOrderModal #print').show();
                        }
                        $('#temporaryItem tbody').empty();
                        $('#temporaryItem tfoot').find('.idr-total-row').remove(); // Remove previous IDR totals

                        // Perform AJAX request to fetch items and services
                        $.ajax({
                            url: '/get-purchaseOrderItems/' + itemData
                                .id, // Server returns both items and services
                            method: 'GET',
                            success: function(response) {
                                var subTotal = 0;
                                var totalPpn = 0;
                                var totalPph = 0;
                                var totalInIDR = 0;
                                var promises = [];

                                // 1. Display Items (Barang)
                                if (response.items && response.items.length > 0) {
                                    response.items.forEach(function(item, index) {
                                        var rowClass = ''
                                        if (item.total_quantity < item.minimum_quantity) {
                                            rowClass = 'bg-light-red';
                                        }
                                        var amount = parseFloat(item.price) * parseFloat(
                                            item.quantity);
                                        var ppnAmount = amount * (parseFloat(item.ppn) /
                                            100);
                                        subTotal += amount;
                                        totalPpn += ppnAmount;
                                        var amountWithPpn = (amount + ppnAmount)
                                            .toLocaleString('id-ID');

                                        var row = `
                                            <tr class="text-center ${rowClass}" data-poi-id="${item.poi_id}">
                                                <td>${index + 1}</td>
                                                <td>${item.item_pms}</td>
                                                <td>${item.item_name}</td>
                                                <td>
                                                    <input type="number" class="form-control text-center quantity-input" value="${item.quantity}" 
                                                        ${item.status === 'Selesai' ? 'readonly' : ''}/>
                                                </td>
                                                <td>${item.item_unit}</td>
                                                <td>${item.condition}</td>
                                                <td class="price-cell" data-price="${item.price}">${parseFloat(item.price).toLocaleString('id-ID')}</td>
                                                <td class="ppn-cell" data-ppn="${item.ppn}">${item.ppn}</td>
                                                <td></td>
                                                <td>${item.item_option}</td>
                                                <td>${item.utility}</td>
                                                <td>
                                                    <a href="#" class="pr-link" data-pr-number="${item.purchase_request_number}" data-ship-id="${item.ship_id}">
                                                        ${item.purchase_request_number}
                                                    </a>
                                                </td>
                                                <td>${item.status}</td>
                                                <td class="amount-cell">${amountWithPpn.toLocaleString('id-ID')}</td>
                                            </tr>
                                        `;
                                        $('#temporaryItem tbody').append(row);

                                        // Handle currency conversion if not in IDR
                                        if (response.PO.currency !== 'IDR') {
                                            let conversionPromise = $.ajax({
                                                url: `https://api.frankfurter.app/${response.PO.purchase_date}?from=${response.PO.currency}&to=IDR`,
                                                method: 'GET'
                                            }).done(function(rateResponse) {
                                                var conversionRate = rateResponse
                                                    .rates.IDR;
                                                var convertedAmount = amount *
                                                    conversionRate;
                                                totalInIDR += convertedAmount;
                                            });
                                            promises.push(conversionPromise);
                                        } else {
                                            totalInIDR += amount; // Already in IDR
                                        }
                                    });
                                } else {}

                                // 2. Add Separator for Services
                                if (response.services && response.services.length > 0) {
                                    $('#temporaryItem tbody').append(`
                                    <tr class="text-center jasa-separator">
                                        <td colspan="14"><strong>Services (Jasa)</strong></td>
                                    </tr>
                                `);

                                    // 3. Display Services (Jasa)
                                    response.services.forEach(function(service, index) {
                                        var cekPPN = service.ppn ? service.ppn : "0";
                                        var cekPPH = service.pph ? service.pph : "0";
                                        var price = parseFloat(service.price);
                                        var quantity = 1;
                                        var amount = price * quantity;

                                        // Hitung PPN dan PPh jasa
                                        var ppnAmount = amount * (parseFloat(cekPPN) / 100);
                                        var pphAmount = amount * (parseFloat(cekPPH) / 100);

                                        // Tambahkan jasa ke Sub Total dan PPN serta PPh ke total masing-masing
                                        subTotal += amount;
                                        totalPpn += ppnAmount;
                                        totalPph += pphAmount;

                                        var amountWithTaxes = amount + ppnAmount -
                                            pphAmount;

                                        // Handle service fields yang mungkin null (misalnya dari service-list)
                                        var serviceCode = service.service_code ? service
                                            .service_code : "";
                                        var serviceName = service.service_name ? service
                                            .service_name : "";
                                        var purchaseRequestNumber = service
                                            .purchase_request_number ? service
                                            .purchase_request_number : "";
                                        var ppn = service.ppn ? service.ppn : "0";
                                        var pph = service.pph ? service.pph : "0";
                                        var utility = service.utility ? service.utility :
                                            "";
                                        var servicePRLink = purchaseRequestNumber !== "" ?
                                            `<a href="#" class="pr-link" data-pr-number="${purchaseRequestNumber}" data-ship-id="${service.ship_id}">
                                        ${purchaseRequestNumber}</a>` : purchaseRequestNumber;

                                        // Pastikan service.price dalam bentuk angka sebelum diformat
                                        var formattedPrice = parseFloat(service.price)
                                            .toLocaleString('id-ID');
                                        var formattedPpnAmount = ppnAmount.toLocaleString(
                                            'id-ID');
                                        var formattedPph = parseFloat(pph).toString();

                                        // Menampilkan hasil pada tabel
                                        var row = `
                                        <tr class="text-center">
                                            <td>Jasa</td>
                                            <td>${serviceCode}</td>
                                            <td>${serviceName}</td>
                                            <td>1</td>
                                            <td></td>
                                            <td></td>
                                            <td>${formattedPrice}</td>
                                            <td>${ppn}</td>
                                            <td>${formattedPph}</td>
                                            <td></td>
                                            <td>${utility}</td>
                                            <td>${servicePRLink}</td>
                                            <td>${service.status}</td>
                                            <td>${amountWithTaxes.toLocaleString(
                                            'id-ID')}</td>
                                        </tr>
                                            `;
                                        $('#temporaryItem tbody').append(row);

                                        // Tambahkan harga jasa ke total (anggap jasa dalam IDR)
                                        totalInIDR += amount;
                                    });
                                }

                                // Wait for all currency conversion requests to complete
                                $.when.apply($, promises).done(function() {
                                    // Update the totals in the modal
                                    $('#detailPurchaseOrderModal #subTotal').text(subTotal
                                        .toLocaleString('id-ID'));
                                    $('#detailPurchaseOrderModal #totalPpn').text(totalPpn
                                        .toLocaleString(
                                            'id-ID'));
                                    $('#detailPurchaseOrderModal #totalPph').text(totalPph
                                        .toLocaleString('id-ID'));
                                    $('#detailPurchaseOrderModal #totalAll').text((
                                            subTotal + totalPpn - totalPph)
                                        .toLocaleString('id-ID'));

                                    // Display total in IDR if necessary
                                    if (itemData.currency !== 'IDR' && totalInIDR > 0) {
                                        var totalInIDRRow = `
                                            <tr class="text-center idr-total-row">
                                                <td colspan="13" style="text-align: right; font-weight: bold;">Total in IDR</td>
                                                <td>${totalInIDR.toLocaleString('id-ID')}</td>
                                            </tr>
                                        `;
                                        $('#temporaryItem tfoot').append(totalInIDRRow);
                                    }
                                });
                            },
                            error: function() {
                                console.log('Error loading data');
                            }
                        });

                    } else {
                        console.log('No item data found');
                    }
                    $(document).off('click', '.pr-link');
                    $(document).on('click', '.pr-link', function(e) {
                        var prNumber = $(this).data('pr-number');
                        $.ajax({
                            url: '/get-ship/',
                            data: {
                                prNumber: prNumber
                            },
                            method: 'GET',
                            success: function(response) {
                                localStorage.setItem('activePillId', response.ship_id);
                            },
                            error: function() {
                                console.log('Error loading data');
                            }
                        });
                        localStorage.setItem('prNumber', prNumber);
                        window.open(`/purchaseRequests`, '_blank');
                        if (e.ctrlKey) {
                            e.preventDefault(); // Prevent the default anchor tag behavior
                            window.open(`/purchaseOrders`, '_blank');
                        }
                    });

                    $('#detailPurchaseOrderModal #saveQuantitiesButton').off('click').on('click', function() {
                        let updatedQuantities = [];

                        $('#detailPurchaseOrderModal #temporaryItem tbody tr').each(function() {
                            const $row = $(this);
                            const poiId = $row.data('poi-id');
                            const quantity = parseFloat($row.find('.quantity-input').val()) ||
                                0;

                            if (poiId) {
                                updatedQuantities.push({
                                    poi_id: poiId,
                                    quantity: quantity
                                });
                            }
                        });

                        // Munculkan konfirmasi sebelum mengirim data
                        Swal.fire({
                            title: 'Are you sure?',
                            text: 'Do you really want to update the quantities?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#46a146',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Yes, update it!',
                            cancelButtonText: 'Cancel'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Jika pengguna mengklik "Yes", jalankan AJAX untuk update
                                $.ajax({
                                    url: '/updatePurchaseOrderQuantities',
                                    method: 'POST',
                                    data: {
                                        _token: $('meta[name="csrf-token"]').attr(
                                            'content'),
                                        quantities: updatedQuantities
                                    },
                                    success: function(response) {
                                        if (response.success) {
                                            $('#detailPurchaseOrderModal').modal(
                                                'hide');
                                            Swal.fire({
                                                title: 'PO updated successfully!',
                                                icon: 'success',
                                                showCancelButton: false,
                                                confirmButtonColor: '#46a146',
                                                confirmButtonText: 'OK'
                                            });
                                        } else {
                                            Swal.fire({
                                                title: 'Failed to update quantities!',
                                                text: 'Quantities cannot be less than received quantity.',
                                                icon: 'error',
                                                showCancelButton: false,
                                                confirmButtonColor: '#46a146',
                                                confirmButtonText: 'OK'
                                            });
                                        }
                                    },
                                });
                            }
                        });
                    });
                    // Mencegah tombol enter untuk accept
                    $('#detailPurchaseOrderModal').on('keydown', 'input', function(event) {
                        if (event.key === 'Enter') {
                            event.preventDefault();
                            return false;
                        }
                    });
                });
            });
        </script>

        {{-- Detail LPJ Modal --}}
        <script>
            $(document).ready(function() {
                $('#detailLPJModal').on('show.bs.modal', function(e) {
                    var button = $(e.relatedTarget);
                    var itemData = button.data('purchaseorder');
                    // console.log(itemData);
                    if (itemData) {
                        $('#detailLPJModal #lpj_id').val(itemData.id);
                        $('#detailLPJModal #LPJDate').val(itemData.date);
                        $('#detailLPJModal #LPJNumber').val(itemData.number);
                        $('#detailLPJModal #PIC').val(itemData.pic);
                        $('#detailLPJModal #note').val(itemData.note);
                        $('#detailLPJModal').find('a[id="print"]').attr('href',
                            '/print-purchaseOrders/' + itemData.id);
                        $('#detailLPJModal #temporaryItem tbody').empty();
                        $('#detailLPJModal #temporaryItem tfoot').find('.idr-total-row')
                            .remove();
                        // Perform AJAX request to fetch items and services
                        $.ajax({
                            url: '/get-purchaseOrderItemforLPJ/' + itemData
                                .id, // Server returns both items and services
                            method: 'GET',
                            success: function(response) {
                                var subTotal = 0;
                                var totalPpn = 0;
                                var totalPph = 0;
                                var totalInIDR = 0; // Total sudah dalam IDR karena LPJ selalu IDR

                                // 1. Display Items (Barang)
                                if (response.items && response.items.length > 0) {
                                    response.items.forEach(function(item, index) {
                                        var rowClass = '';
                                        if (item.total_quantity < item.minimum_quantity) {
                                            rowClass = 'bg-light-red';
                                        }
                                        var amount = parseFloat(item.price) * parseFloat(
                                            item.quantity);
                                        var ppnAmount = amount * (parseFloat(item.ppn) /
                                            100);
                                        subTotal += amount;
                                        totalPpn += ppnAmount;
                                        var amountWithPpn = amount + ppnAmount;

                                        // Update totalInIDR langsung karena IDR
                                        totalInIDR += amount;
                                        var row = `
                                            <tr class="text-center ${rowClass}">
                                                <td>${index + 1}</td>
                                                <td>${item.item_pms}</td>
                                                <td>${item.item_name}</td>
                                                <td>${item.quantity}</td>
                                                <td>${item.item_unit}</td>
                                                <td>${item.condition}</td>
                                                <td>${parseFloat(item.price).toLocaleString('id-ID')}</td>
                                                <td>${item.ppn}</td>
                                                <td></td>
                                                <td>${item.item_option}</td>
                                                <td>${item.utility}</td>
                                                <td>
                                                    <a href="#" class="pr-link" data-pr-number="${item.purchase_request_number}" data-ship-id=${item.ship_id}>
                                                        ${item.purchase_request_number}
                                                    </a>
                                                </td>
                                                <td>${item.purchase_order_number}</td>
                                                <td>${amountWithPpn.toLocaleString('id-ID')}</td>
                                            </tr>
                                        `;
                                        $('#detailLPJModal #temporaryItem tbody').append(
                                            row);
                                    });
                                }

                                // 2. Add Separator for Services
                                if (response.services && response.services.length > 0) {
                                    $('#detailLPJModal #temporaryItem tbody').append(`
                                        <tr class="text-center jasa-separator">
                                            <td colspan="14"><strong>Services (Jasa)</strong></td>
                                        </tr>
                                    `);

                                    // 3. Display Services (Jasa)
                                    response.services.forEach(function(service, index) {
                                        var cekPPN = service.ppn ? service.ppn : "0";
                                        var cekPPH = service.pph ? service.pph : "0";
                                        var price = parseFloat(service.price);
                                        var quantity = 1;
                                        var amount = price * quantity;

                                        // Hitung PPN dan PPh jasa
                                        var ppnAmount = amount * (parseFloat(cekPPN) / 100);
                                        var pphAmount = amount * (parseFloat(cekPPH) / 100);

                                        // Tambahkan jasa ke Sub Total dan PPN serta PPh ke total masing-masing
                                        subTotal += amount;
                                        totalPpn += ppnAmount;
                                        totalPph += pphAmount;

                                        var amountWithTaxes = amount + ppnAmount -
                                            pphAmount;
                                        totalInIDR += amount; // Update total langsung

                                        // Menampilkan hasil pada tabel
                                        var row = `
                                                <tr class="text-center">
                                                    <td>Jasa</td>
                                                    <td>${service.service_code || ''}</td>
                                                    <td>${service.service_name || ''}</td>
                                                    <td>1</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td>${price.toLocaleString('id-ID')}</td>
                                                    <td>${cekPPN}</td>
                                                    <td>${cekPPH}</td>
                                                    <td></td>
                                                    <td>${service.utility || ''}</td>
                                                    <td>${service.purchase_request_number || ''}</td>
                                                    <td>${service.purchase_order_number}</td>
                                                    <td>${amountWithTaxes.toLocaleString('id-ID')}</td>
                                                </tr>
                                            `;
                                        $('#detailLPJModal #temporaryItem tbody').append(
                                            row);
                                    });
                                }

                                // Update the totals in the modal
                                $('#detailLPJModal #subTotal').text(subTotal.toLocaleString(
                                    'id-ID'));
                                $('#detailLPJModal #totalPpn').text(totalPpn.toLocaleString(
                                    'id-ID'));
                                $('#detailLPJModal #totalPph').text(totalPph.toLocaleString(
                                    'id-ID'));
                                $('#detailLPJModal #totalAll').text((subTotal + totalPpn - totalPph)
                                    .toLocaleString('id-ID'));
                            },
                            error: function() {
                                console.log('Error loading data');
                            }
                        });
                    } else {
                        console.log('No item data found');
                    }
                    $(document).off('click', '.pr-link');
                    $(document).on('click', '.pr-link', function(e) {
                        var prNumber = $(this).data('pr-number');
                        $.ajax({
                            url: '/get-ship/',
                            data: {
                                prNumber: prNumber
                            },
                            method: 'GET',
                            success: function(response) {
                                localStorage.setItem('activePillId', response.ship_id);
                            },
                            error: function() {
                                console.log('Error loading data');
                            }
                        });
                        localStorage.setItem('prNumber', prNumber);
                        window.open(`/purchaseRequests`, '_blank');
                        if (e.ctrlKey) {
                            e.preventDefault(); // Prevent the default anchor tag behavior
                            window.open(`/purchaseOrders`, '_blank');
                        }
                    });
                });
            });
        </script>

        {{-- Search from another page --}}
        <script>
            $(document).ready(function() {
                // Check if there's a PO Number stored in local storage
                var storedPoNumber = localStorage.getItem('poNumber');

                if (storedPoNumber) {
                    // Prefill the search input with the stored PO Number
                    $('#searchPurchaseOrders').val(storedPoNumber);

                    // Trigger the search function (e.g., simulate a keyup event to search)
                    $('#searchPurchaseOrders').trigger('keyup');

                    // Clear the stored PO Number after using it
                    localStorage.removeItem('poNumber');
                }
            });
        </script>
        {{-- Table Sort --}}
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-table/dist/bootstrap-table.min.js"></script>

        {{-- Add LPJ Modal --}}
        <script>
            $(document).ready(function() {
                var addedItems = [];
                var rowNumber = 1;
                var jasaSeparatorAdded = false;
                var isLPJNumberValid = false;
                $('#addLPJModal #submitButton').prop('disabled', true);

                // Event handler when modal is shown
                $('#addLPJModal').on('show.bs.modal', function() {
                    $('#addLPJModal input[name="lpj_number"]').on('input', function() {
                        var LPJno = $(this).val();
                        $.ajax({
                            type: "get",
                            url: "{{ url('check-LPJNumber') }}",
                            data: "LPJno=" + LPJno,
                            success: function(response) {
                                if (response.exists) {
                                    $('#addLPJModal #newLPJNumber-validation')
                                        .text('LPJ number already used.');
                                    $('#addLPJModal button[id=submitButton]')
                                        .attr('disabled', true);
                                    isLPJNumberValid = false;
                                } else {
                                    $('#addLPJModal #newLPJNumber-validation')
                                        .text('');
                                    isLPJNumberValid = true;
                                }
                                toggleSubmitButton();
                            },
                            error: function() {
                                alert('Error');
                            }
                        });
                    });

                    var today = new Date().toISOString().split('T')[0];
                    $('#addLPJModal #newLPJDate').val(today);

                    // Fetch purchase orders and populate dropdown
                    $.ajax({
                        type: "get",
                        url: "{{ url('get-purchaseOrders') }}",
                        success: function(response) {
                            var purchaseOrderList = $('#purchaseOrder-list');
                            purchaseOrderList.empty();
                            $.each(response, function(index, purchaseOrder) {
                                var newItem = $('<div></div>')
                                    .addClass('dropdown-item')
                                    .css({
                                        'display': 'flex',
                                        'justify-content': 'space-between'
                                    });
                                var newText = $('<span></span>').addClass(
                                    'purchaseOrder-dropdown').text(purchaseOrder
                                    .purchase_order_number);
                                var addButton = $('<button></button>').addClass(
                                        'btn btn-sm btn-success add-purchase-order')
                                    .attr('data-purchase-order-id', purchaseOrder.id)
                                    .attr('data-added', addedItems.includes(purchaseOrder
                                        .id) ? 'true' : 'false')
                                    .text(addedItems.includes(purchaseOrder.id) ? 'Remove' :
                                        'Add');

                                if (addedItems.includes(purchaseOrder.id)) {
                                    addButton.removeClass('btn-success').addClass(
                                        'btn-danger');
                                }

                                newItem.append(newText).append(addButton);
                                purchaseOrderList.append(newItem);
                            });
                        },
                        error: function(xhr, status, error) {
                            console.error("Error fetching purchase orders:", error);
                        }
                    });
                    $('#addLPJModal #searchPurchaseOrderNumber').on('input', function() {
                        var keyword = $(this).val().toLowerCase();
                        $('#purchaseOrder-list .dropdown-item').each(function() {
                            var text = $(this).text().toLowerCase();
                            if (text.indexOf(keyword) > -1) {
                                $(this).show();
                            } else {
                                $(this).hide();
                            }
                        });
                        $('.dropdown-menu').addClass('show-dropdown');
                    });
                });

                // Handle Add/Remove button clicks
                $('#purchaseOrder-list').on('click', '.add-purchase-order', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var purchaseOrderID = $(this).data('purchase-order-id');
                    var isAdded = $(this).attr('data-added') === 'true';

                    if (!isAdded) {
                        loadPurchaseOrderItems(purchaseOrderID); // Load and display both items and services
                        $(this).text('Remove')
                            .removeClass('btn-success')
                            .addClass('btn-danger')
                            .attr('data-added', 'true');
                        addedItems.push(purchaseOrderID);
                        localStorage.setItem('addedItemsLPJ', JSON.stringify(addedItems));
                        document.getElementById('purchase_order_ids').value = addedItems.join(',');
                    } else {
                        $('#addLPJModal #temporaryItemDraft tbody tr[data-po-id="' + purchaseOrderID + '"]')
                            .remove();
                        $(this).text('Add')
                            .removeClass('btn-danger')
                            .addClass('btn-success')
                            .attr('data-added', 'false');
                        addedItems = addedItems.filter(item => item !== purchaseOrderID);
                        localStorage.setItem('addedItemsLPJ', JSON.stringify(addedItems));
                        document.getElementById('purchase_order_ids').value = addedItems.join(',');

                        // Cek apakah masih ada jasa setelah penghapusan
                        var stillHasServices = $(
                            '#addLPJModal #temporaryItemDraft tbody tr[data-category="Service"]').length;
                        if (stillHasServices === 0) {
                            $('.jasa-separator').remove();
                            jasaSeparatorAdded = false;
                        }
                        updateTableRowNumbers();
                        recalculateTotals();
                    }
                    toggleSubmitButton()
                });

                // Reset modal when hidden
                $('#addLPJModal').on('hidden.bs.modal', function() {
                    var tableBody = $('#addLPJModal #temporaryItemDraft tbody');
                    tableBody.empty(); // Empty the table
                    jasaSeparatorAdded = false;
                    $('#addLPJModal #submitButton').prop('disabled', true);

                    // Reset totals
                    $('#addLPJModal #subTotalLPJ').text('0');
                    $('#addLPJModal #totalPpnLPJ').text('0');
                    $('#addLPJModal #totalAllLPJ').text('0');

                    addedItems = []; // Clear addedItems
                    localStorage.removeItem('addedItemsLPJ'); // Clear localStorage

                    // Reset the "Add" button state to be btn-success
                    $('#purchaseOrder-list .add-purchase-order')
                        .text('Add')
                        .removeClass('btn-danger')
                        .addClass('btn-success')
                        .attr('data-added', 'false');
                });

                // Load purchase order items into the table (both items and services)
                function loadPurchaseOrderItems(purchaseOrderID) {
                    $.ajax({
                        url: '/get-itemPurchaseOrders/' + purchaseOrderID,
                        type: 'GET',
                        success: function(response) {
                            var tableBody = $('#addLPJModal #temporaryItemDraft tbody');
                            // First, add all items
                            response.items.forEach(function(item, index) {
                                var newRow = $('<tr></tr>').addClass('text-center').attr(
                                    'data-po-id', purchaseOrderID).attr('data-category', 'Item');

                                var baseAmount = parseFloat(item.price) * parseFloat(item.quantity);
                                var ppnAmount = baseAmount * (parseFloat(item.ppn) / 100);
                                var itemAmount = baseAmount + ppnAmount;

                                newRow.append($('<td></td>').text('')) // Tempat untuk nomor
                                    .append($('<td></td>').text(item.purchase_request_items.items
                                        .item_pms))
                                    .append($('<td></td>').text(item.purchase_request_items.items
                                        .item_name))
                                    .append($('<td></td>').text(item.quantity))
                                    .append($('<td></td>').text(item.purchase_request_items.items
                                        .item_unit))
                                    .append($('<td></td>').text(item.condition))
                                    .append($('<td></td>').text(parseInt(item.price).toLocaleString(
                                        'id-ID'))) // Format harga
                                    .append($('<td></td>').text(item.ppn))
                                    .append($('<td></td>').text(''))
                                    .append($('<td></td>').text(item.purchase_request_items.option))
                                    .append($('<td></td>').text(item.purchase_request_items
                                        .utility))
                                    .append($('<td></td>').text(item.purchase_request_items
                                        .purchase_request.purchase_request_number))
                                    .append($('<td></td>').text(parseInt(itemAmount).toLocaleString(
                                        'id-ID'))); // Format amount

                                // Jika ada separator jasa, tambahkan items di atasnya
                                if (jasaSeparatorAdded) {
                                    tableBody.find('.jasa-separator').before(newRow);
                                } else {
                                    tableBody.append(newRow);
                                }
                            });

                            // Only add separator if there are services
                            if (response.services.length > 0 && !jasaSeparatorAdded) {

                                var separatorRow = $('<tr></tr>').addClass('text-center jasa-separator')
                                    .append($('<td></td>').attr('colspan', 13).css('font-weight', 'bold')
                                        .text('Services (Jasa)'));
                                tableBody.append(separatorRow);
                                jasaSeparatorAdded = true; // Tandai bahwa separator sudah ditambahkan
                            }

                            // Now, add all services after separator
                            response.services.forEach(function(service, index) {
                                var newRow = $('<tr></tr>').addClass('text-center').attr(
                                    'data-po-id', purchaseOrderID).attr('data-category',
                                    'Service');

                                // Periksa apakah service berasal dari PR atau manual
                                var serviceCode = service.purchase_request_services ?
                                    service.purchase_request_services.services.service_code :
                                    service.services.service_code;
                                var serviceName = service.purchase_request_services ?
                                    service.purchase_request_services.services.service_name :
                                    service.services.service_name;
                                var serviceUtility = service.purchase_request_services ? service
                                    .purchase_request_services.utility : service.utility;
                                var purchaseRequestNumber = service.purchase_request_services ?
                                    service.purchase_request_services.purchase_request
                                    .purchase_request_number : '';
                                var baseAmount = service.price * 1; // Harga pokok
                                var ppnAmount = baseAmount * (service.ppn / 100); // PPN
                                var pphAmount = baseAmount * (service.pph / 100); // PPh
                                var serviceAmount = baseAmount + ppnAmount - pphAmount;
                                var formattedPph = parseFloat(service.pph).toString();

                                newRow.append($('<td></td>').text('Jasa'))
                                    .append($('<td></td>').text(serviceCode))
                                    .append($('<td></td>').text(serviceName))
                                    .append($('<td></td>').text(
                                        '1')) // Jasa tidak punya kuantitas selain "1"
                                    .append($('<td></td>').text('')) // Kosongkan kolom unit
                                    .append($('<td></td>').text('')) // Kosongkan kolom condition
                                    .append($('<td></td>').text(parseInt(service.price)
                                        .toLocaleString('id-ID'))) // Format harga
                                    .append($('<td></td>').text(service.ppn))
                                    .append($('<td></td>').text(formattedPph))
                                    .append($('<td></td>').text('')) // Kosongkan kolom option
                                    .append($('<td></td>').text(serviceUtility))
                                    .append($('<td></td>').text(purchaseRequestNumber))
                                    .append($('<td></td>').text(parseInt(serviceAmount)
                                        .toLocaleString('id-ID'))); // Format amount

                                tableBody.append(newRow);
                            });
                            updateTableRowNumbers(); // Perbarui penomoran setelah penambahan
                            recalculateTotals();
                            toggleSubmitButton()
                        },
                        error: function(xhr, status, error) {
                            console.error(error);
                        }
                    });
                }

                // Fungsi untuk memperbarui nomor baris (penomoran ulang) setelah penghapusan
                function updateTableRowNumbers() {
                    var tableBody = $('#addLPJModal #temporaryItemDraft tbody');
                    var newRowNumber = 1;

                    // Loop melalui semua baris, nomor ulang untuk items saja (bukan jasa atau separator)
                    tableBody.find('tr').each(function() {
                        if ($(this).data('category') === 'Item') {
                            $(this).find('td:first').text(newRowNumber++); // Nomori hanya items
                        } else if ($(this).data('category') === 'Service') {
                            $(this).find('td:first').text('Jasa'); // Ganti nomor untuk jasa
                        }
                    });
                }

                // Recalculate totals
                function recalculateTotals() {
                    var totalAmount = 0;
                    var totalPpn = 0;
                    var totalPph = 0;

                    $('#addLPJModal #temporaryItemDraft tbody tr').each(function() {
                        var price = parseFloat($(this).find('td:eq(6)').text().replace(/\./g, '')) || 0;
                        var quantity = parseFloat($(this).find('td:eq(3)').text()) || 0;
                        var ppn = parseFloat($(this).find('td:eq(7)').text()) || 0;
                        var pph = parseFloat($(this).find('td:eq(8)').text()) || 0;

                        var amount = price * quantity;
                        var ppnAmount = amount * (ppn / 100);
                        var pphAmount = amount * (pph / 100);

                        var totalRowAmount = amount + ppnAmount - pphAmount;

                        // Update kolom amount per baris dengan format rupiah
                        $(this).find('td:eq(12)').text(totalRowAmount.toLocaleString('id-ID'));

                        totalAmount += amount;
                        totalPpn += ppnAmount;
                        totalPph += pphAmount;
                    });

                    // Update sub total, PPN, PPh, dan total keseluruhan dengan format rupiah
                    $('#addLPJModal #subTotalLPJ').text(totalAmount.toLocaleString('id-ID'));
                    $('#addLPJModal #totalPpnLPJ').text(totalPpn.toLocaleString('id-ID'));
                    $('#addLPJModal #totalPphLPJ').text(totalPph.toLocaleString('id-ID'));
                    $('#addLPJModal #totalAllLPJ').text((totalAmount + totalPpn - totalPph).toLocaleString('id-ID'));
                }

                function toggleSubmitButton() {
                    var hasData = $('#addLPJModal #temporaryItemDraft tbody tr').length > 0;
                    $('#addLPJModal #submitButton').prop('disabled', !(isLPJNumberValid && hasData));
                }
            });
        </script>
    @endsection
</div>
