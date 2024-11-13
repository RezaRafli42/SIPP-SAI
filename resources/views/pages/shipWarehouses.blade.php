@extends('layouts.layout')
<title>Ship Warehouses</title>
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
                    <div class="row">
                        <div class="col-2 pr-0">
                            <div class="ship-list">
                                <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist"
                                    aria-orientation="vertical">
                                    @php
                                        // Dapatkan nama kapal dari user yang login
                                        $loggedInUserName = Auth::user()->name;
                                        $loggedInUserRole = Auth::user()->role;
                                        // Jika user adalah "Kantor Pusat", tampilkan semua menu
                                        if ($loggedInUserRole === 'Kapal') {
                                            // Dapatkan tipe kapal dari nama kapal yang login
                                            $loggedInShipType =
                                                $ships->firstWhere('ship_name', $loggedInUserName)->ship_type ?? null;
                                            // Filter shipType dan ships sesuai dengan user yang login
                                            $filteredShipTypes = collect([$loggedInShipType]);
                                            $filteredShips = $ships->where('ship_name', $loggedInUserName);
                                        } else {
                                            $filteredShipTypes = $shipType; // Tampilkan semua shipType
                                            $filteredShips = $ships; // Tampilkan semua ships
                                        }
                                    @endphp
                                    @foreach ($filteredShipTypes as $type)
                                        <a
                                            class="ship-type font-weight-bolder rounded text-white mb-1 mt-1 text-decoration-none">{{ $type }}</a>
                                        @foreach ($filteredShips->where('ship_type', $type) as $shipItem)
                                            <a class="nav-link" id="{{ $shipItem->id }}" data-toggle="pill"
                                                href="#v-pills-{{ $shipItem->id }}" role="tab"
                                                aria-controls="v-pills-home" aria-selected="true"
                                                data-ship="{{ json_encode($shipItem) }}">{{ $shipItem->ship_name }}</a>
                                        @endforeach
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="col-10 pl-0">
                            <div class="card-body">
                                <p class="card-title mb-2 mb-2">Ship Warehouses</p>
                                {{-- <p class="vessel-title">{{ $shipItem->ship_name }}</p> --}}
                                <div class="input-group mb-3">
                                    <input id="searchShipWarehouses" type="text" class="form-control"
                                        placeholder="Search...">
                                    <div class="input-group-append">
                                        <button class="btn btn-sm btn-primary" type="button">Search</button>
                                    </div>
                                </div>
                                <div class="input-group mb-3">
                                    @if (
                                        (Auth::user() && Auth::user()->role === 'Super Admin') ||
                                            Auth::user()->role === 'Fleet Admin' ||
                                            Auth::user()->role === 'Purchasing Logistic Admin' ||
                                            Auth::user()->role === 'Kapal')
                                        <button type="button" class="btn btn-success btn-icon-text mr-1"
                                            data-toggle="modal" data-target="#addShipWarehousesModal">
                                            <i class="mdi mdi-database-plus pr-2"></i>Add Ship Warehouse Data
                                        </button>
                                    @endif
                                    <button type="button" class="btn btn-primary btn-icon-text mr-1" data-toggle="modal"
                                        data-target="#listShipWarehouseUsageModal" data-ship-id="{{ $shipItem->id }}">
                                        <i class="mdi mdi-format-list-bulleted pr-2"></i>List Usage
                                    </button>
                                    <button type="button" class="btn btn-primary btn-icon-text mr-1" data-toggle="modal"
                                        data-target="#listShipWarehouseSendOfficeModal" data-ship-id="{{ $shipItem->id }}">
                                        <i class="mdi mdi-format-list-bulleted pr-2"></i>List Send to Office
                                    </button>
                                    @if ((Auth::user() && Auth::user()->role === 'Super Admin') || Auth::user()->role === 'Kapal')
                                        <button type="button" class="btn btn-danger btn-icon-text mr-1" data-toggle="modal"
                                            data-target="#adjustmentShipWarehouseModal" data-ship-id="{{ $shipItem->id }}">
                                            <i class="mdi mdi-format-list-bulleted pr-2"></i>Adjusment
                                        </button>
                                    @endif
                                    <button type="button" class="btn btn-primary btn-icon-text mr-1" data-toggle="modal"
                                        data-target="#shipWarehouseHistoryModal">
                                        <i class="mdi mdi-database pr-2"></i>Warehouse Item History
                                    </button>
                                    @if (
                                        (Auth::user() && Auth::user()->role === 'Super Admin') ||
                                            Auth::user()->role === 'Fleet Admin' ||
                                            Auth::user()->role === 'Purchasing Logistic Admin' ||
                                            Auth::user()->role === 'Kapal')
                                        <button type="button" class="btn btn-warning btn-icon-text mr-1"
                                            data-toggle="modal" data-target="#importShipWarehouseModal" data-ship-id="">
                                            <i class="mdi mdi-import pr-2"></i>Import (.xlsx)
                                        </button>
                                    @endif
                                </div>
                                <div class="row">
                                    <div class="col-12 p-0">
                                        <div class="data">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- content-wrapper ends -->
        {{-- Add Modal --}}
        <div class="modal fade" id="addShipWarehousesModal" tabindex="-1" role="dialog"
            aria-labelledby="addShipWarehousesModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addShipWarehousesModalLabel">Add Item to Ship Warehouse</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="addShipWarehousesForm" method="POST" action="{{ url('addShipWarehouses') }}"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-6 col-sm-4">
                                        <label for="newShipID"><span class="text-danger">*</span>Ship Name</label>
                                        <input readonly required name="ship_id" type="text" class="form-control"
                                            id="newShipID">
                                    </div>
                                    <div class="col-6 col-sm-4">
                                        <label for="newItemName"><span class="text-danger">*</span>Item Name</label>
                                        <div class="itemName-dropdown">
                                            <input required type="text" id="newItemName" name="item_name"
                                                class="form-control" autocomplete="off" data-toggle="dropdown"
                                                aria-haspopup="true" aria-expanded="false"
                                                placeholder="Select item name">
                                            <input type="text" id="newItemID" name="item_id" hidden>
                                            <div id="itemName-list-col3" class="dropdown-menu"
                                                aria-labelledby="item_name">
                                                @foreach ($items as $item)
                                                    <a class="dropdown-item" href="#"
                                                        data-item="{{ json_encode($item) }}">{{ $item->item_pms }}-{{ $item->item_name }}</a>
                                                @endforeach
                                            </div>
                                            <div id="item-validation-message" class="text-danger"></div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-4">
                                        <label for="newMinimumQuantity"><span class="text-danger">*</span>Minimum
                                            Quantity</label>
                                        <input required name="minimum_quantity" type="number" class="form-control"
                                            id="newMinimumQuantity" placeholder="Enter item minimum quantity">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-6 col-sm-4">
                                        <label for="newDepartment"><span class="text-danger">*</span>Department</label>
                                        <input required name="department" type="text" class="form-control"
                                            id="newDepartment" placeholder="Enter item department">
                                    </div>
                                    <div class="col-6 col-sm-4">
                                        <label for="newPositionDate">Position Date</label>
                                        <input name="position_date" type="text" class="form-control"
                                            id="newPositionDate" placeholder="Enter position date">
                                    </div>
                                    <div class="col-6 col-sm-4">
                                        <label for="newEquipmentCategory"><span class="text-danger">*</span>Equipment
                                            Category</label>
                                        <input required name="equipment_category" type="text" class="form-control"
                                            id="newEquipmentCategory" placeholder="Enter equipment category">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-8 col-sm-6">
                                        <label for="newToolCategory"><span class="text-danger">*</span>Tool
                                            Category</label>
                                        <input required name="tool_category" type="text" class="form-control"
                                            id="newToolCategory" placeholder="Enter tool category">
                                    </div>
                                    <div class="col-8 col-sm-6">
                                        <label for="newType">Type</label>
                                        <input name="type" type="text" class="form-control" id="newType"
                                            placeholder="Enter item type">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-6 col-sm-4">
                                        <label for="newCertification">Certification</label>
                                        <input name="certification" type="text" class="form-control"
                                            id="newCertification" placeholder="Enter item certification">
                                    </div>
                                    <div class="col-6 col-sm-4">
                                        <label for="newLastMaintenanceDate">Last Maintenance Date</label>
                                        <input name="last_maintenance_date" type="date" class="form-control"
                                            id="newLastMaintenanceDate">
                                    </div>
                                    <div class="col-6 col-sm-4">
                                        <label for="newLastInspectionDate">Last Inspection Date</label>
                                        <input name="last_inspection_date" type="date" class="form-control"
                                            id="newLastInspectionDate">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-12 col-sm-12">
                                        <label for="newDescription">Description</label>
                                        <textarea name="description" type="text" class="form-control" id="newDescription"
                                            placeholder="Enter item description"></textarea>
                                    </div>
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

        {{-- Update Modal --}}
        <div class="modal fade" id="updateShipWarehousesModal" tabindex="-1" role="dialog"
            aria-labelledby="updateShipWarehousesModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateShipWarehousesModalLabel">Update Item in Ship Warehouse</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="updateShipWarehousesForm" method="POST" action="{{ url('updateShipWarehouses') }}"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" name="id" id="id">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-6 col-sm-4">
                                        <label for="shipID"><span class="text-danger">*</span>Ship Name</label>
                                        <input readonly required name="ship_id" type="text" class="form-control"
                                            id="shipID">
                                    </div>
                                    <div class="col-6 col-sm-4">
                                        <label for="itemName"><span class="text-danger">*</span>Item Name</label>
                                        <div class="itemName-dropdown">
                                            <input disabled required type="text" id="itemName" name="item_name"
                                                class="form-control" autocomplete="off" data-toggle="dropdown"
                                                aria-haspopup="true" aria-expanded="false"
                                                placeholder="Select item name">
                                            <input type="text" id="itemID" name="item_id" hidden>
                                            <div id="itemName-list" class="dropdown-menu" aria-labelledby="item_name">
                                                @foreach ($items as $item)
                                                    <a class="dropdown-item" href="#"
                                                        data-item="{{ json_encode($item) }}">{{ $item->item_pms }}-{{ $item->item_name }}</a>
                                                @endforeach
                                            </div>
                                            <div id="item-validation-message" class="text-danger"></div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-4">
                                        <label for="minimumQuantity"><span class="text-danger">*</span>Minimum
                                            Quantity</label>
                                        <input required name="minimum_quantity" type="number" class="form-control"
                                            id="minimumQuantity" placeholder="Enter item minimum quantity">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-6 col-sm-4">
                                        <label for="department"><span class="text-danger">*</span>Department</label>
                                        <input required name="department" type="text" class="form-control"
                                            id="department" placeholder="Enter item department">
                                    </div>
                                    <div class="col-6 col-sm-4">
                                        <label for="positionDate">Position Date</label>
                                        <input name="position_date" type="text" class="form-control"
                                            id="positionDate" placeholder="Enter position date">
                                    </div>
                                    <div class="col-6 col-sm-4">
                                        <label for="equipmentCategory"><span class="text-danger">*</span>Equipment
                                            Category</label>
                                        <input required name="equipment_category" type="text" class="form-control"
                                            id="equipmentCategory" placeholder="Enter equipment category">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-8 col-sm-6">
                                        <label for="toolCategory"><span class="text-danger">*</span>Tool Category</label>
                                        <input required name="tool_category" type="text" class="form-control"
                                            id="toolCategory" placeholder="Enter tool category">
                                    </div>
                                    <div class="col-8 col-sm-6">
                                        <label for="type">Type</label>
                                        <input name="type" type="text" class="form-control" id="type"
                                            placeholder="Enter item type">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-6 col-sm-4">
                                        <label for="certification">Certification</label>
                                        <input name="certification" type="text" class="form-control"
                                            id="certification" placeholder="Enter item certification">
                                    </div>
                                    <div class="col-6 col-sm-4">
                                        <label for="lastMaintenanceDate">Last Maintenance Date</label>
                                        <input name="last_maintenance_date" type="date" class="form-control"
                                            id="lastMaintenanceDate">
                                    </div>
                                    <div class="col-6 col-sm-4">
                                        <label for="lastInspectionDate">Last Inspection Date</label>
                                        <input name="last_inspection_date" type="date" class="form-control"
                                            id="lastInspectionDate">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-12 col-sm-12">
                                        <label for="description">Description</label>
                                        <textarea name="description" type="text" class="form-control" id="description"
                                            placeholder="Enter item description"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="submit" id="submitButton" class="btn btn-success">Save</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Add Usage Modal --}}
        <div class="modal fade" id="addShipWarehouseUsageModal" tabindex="-1" role="dialog"
            aria-labelledby="addShipWarehouseUsageModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addShipWarehouseUsageModalLabel">Add Ship Warehouse Usage</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="addShipWarehouseUsageModalForm" method="POST"
                        action="{{ url('addShipWarehouseUsages') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" name="condition_id" id="newConditionId">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-8 col-sm-6">
                                        <label for="newItemName"><span class="text-danger">*</span>Item Name</label>
                                        <input required disabled type="text" name="item_name" id="newItemName"
                                            class="form-control">
                                    </div>
                                    <div class="col-8 col-sm-6">
                                        <label for="newCondition"><span class="text-danger">*</span>Condition</label>
                                        <input disabled type="text" name="condition" id="newCondition"
                                            class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-6 col-sm-4">
                                        <label for="newQuantity"><span class="text-danger">*</span>Quantity</label>
                                        <input required disabled type="text" name="quantity" id="newQuantity"
                                            class="form-control">
                                    </div>
                                    <div class="col-6 col-sm-4">
                                        <label for="newQuantityUsed"><span class="text-danger">*</span>Quantity
                                            Used</label>
                                        <input required type="number" name="quantity_used" id="newQuantityUsed"
                                            class="form-control" min="1">
                                    </div>
                                    <div class="col-6 col-sm-4">
                                        <label for="newQuantityLeft"><span class="text-danger">*</span>Quantity
                                            Left</label>
                                        <input disabled required type="number" name="quantity_left" id="newQuantityLeft"
                                            class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-8 col-sm-6">
                                        <label for="newUsedItemCondition"><span class="text-danger">*</span>Used Item
                                            Condition</label>
                                        <select required name="used_item_condition" id="newUsedItemCondition"
                                            class="form-control">
                                            <option selected disabled value="">-- Select Condition --</option>
                                            <option value="Bekas Bisa Pakai">Bekas Bisa Pakai</option>
                                            <option value="Bekas Tidak Bisa Pakai">Bekas Tidak Bisa Pakai</option>
                                            <option value="Rekondisi">Rekondisi</option>
                                        </select>
                                    </div>
                                    <div class="col-8 col-sm-6">
                                        <label for="newUsageDate"><span class="text-danger">*</span>Usage Date</label>
                                        <input required type="date" name="usage_date" id="newUsageDate"
                                            class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-8 col-sm-6">
                                        <label for="newPic"><span class="text-danger">*</span>PIC</label>
                                        <input required type="text" name="pic" id="newPic"
                                            class="form-control">
                                    </div>
                                    <div class="col-8 col-sm-6">
                                        <label for="photo">Photo</label>
                                        <input type="file" name="photo" id="photo" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="newDescription">Description</label>
                                <textarea type="text" name="description" id="newDescription" class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" id="submitTransactionButton" class="btn btn-success">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Adjustment Modal --}}
        <div class="modal fade" id="adjustmentShipWarehouseModal" tabindex="-1" role="dialog"
            aria-labelledby="adjustmentShipWarehouseModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="adjustmentShipWarehouseModalLabel">Adjustment</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form">
                            <ul class="tab-group">
                                <li class="tab active"><a href="#usage">Usage</a></li>
                                <li class="tab"><a href="#sendOffice">Send Office</a></li>
                                {{-- <li class="tab"><a href="#adjustmentRecord">Adjustment Record</a></li> --}}
                            </ul>
                            <div class="tab-content">
                                <div id="usage">
                                    <form id="addShipWarehouseUsageModalForm" method="POST"
                                        action="{{ url('addAdjustmentShipWarehouseUsages') }}"
                                        enctype="multipart/form-data">
                                        @csrf
                                        <div class="modal-body">
                                            <input type="hidden" name="condition_id" id="newConditionID">
                                            <input type="hidden" name="ship_id" id="newShipID">
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-8 col-sm-6">
                                                        <label for="newItemName"><span class="text-danger">*</span>Item
                                                            Name</label>
                                                        <div class="adjustmentItemName-dropdown">
                                                            <input required type="text" id="newItemName"
                                                                name="item_name" class="form-control" autocomplete="off"
                                                                data-toggle="dropdown" aria-haspopup="true"
                                                                aria-expanded="false" placeholder="Select item name">
                                                            <input type="text" id="newItemID" name="item_id" hidden>
                                                            <div id="adjustmentItemName-list" class="dropdown-menu"
                                                                aria-labelledby="newItemName">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-8 col-sm-6">
                                                        <label for="newCondition"><span
                                                                class="text-danger">*</span>Condition</label>
                                                        <select name="condition" id="newCondition" class="form-control">
                                                            <option value="" selected disabled>-- Select Condition --
                                                            </option>
                                                            <option value="Baru">Baru</option>
                                                            <option value="Bekas Bisa Pakai">Bekas Bisa Pakai</option>
                                                            <option value="Rekondisi">Rekondisi</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-6 col-sm-4">
                                                        <label for="newQuantity"><span
                                                                class="text-danger">*</span>Quantity</label>
                                                        <input required readonly type="text" name="quantity"
                                                            id="newQuantity" class="form-control">
                                                    </div>
                                                    <div class="col-6 col-sm-4">
                                                        <label for="newQuantityUsed"><span
                                                                class="text-danger">*</span>Quantity
                                                            Used</label>
                                                        <input required type="number" name="quantity_used"
                                                            id="newQuantityUsed" class="form-control" min="1">
                                                    </div>
                                                    <div class="col-6 col-sm-4">
                                                        <label for="newQuantityLeft"><span
                                                                class="text-danger">*</span>Quantity
                                                            Left</label>
                                                        <input disabled required type="number" name="quantity_left"
                                                            id="newQuantityLeft" class="form-control">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-8 col-sm-6">
                                                        <label for="newUsedItemCondition"><span
                                                                class="text-danger">*</span>Used Item
                                                            Condition</label>
                                                        <select required name="used_item_condition"
                                                            id="newUsedItemCondition" class="form-control">
                                                            <option selected disabled value="">-- Select
                                                                Condition --</option>
                                                            <option value="Bekas Bisa Pakai">Bekas Bisa Pakai</option>
                                                            <option value="Bekas Tidak Bisa Pakai">Bekas Tidak Bisa
                                                                Pakai</option>
                                                            <option value="Rekondisi">Rekondisi</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-8 col-sm-6">
                                                        <label for="newUsageDate"><span class="text-danger">*</span>Usage
                                                            Date</label>
                                                        <input required type="date" name="usage_date"
                                                            id="newUsageDate" class="form-control">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-8 col-sm-6">
                                                        <label for="newPic"><span
                                                                class="text-danger">*</span>PIC</label>
                                                        <input required type="text" name="pic" id="newPic"
                                                            class="form-control">
                                                    </div>
                                                    <div class="col-8 col-sm-6">
                                                        <label for="newDescription"><span
                                                                class="text-danger">*</span>Description</label>
                                                        <select required name="description" id="newDescription"
                                                            class="form-control">
                                                            <option value="" disabled selected>-- Select Reason --
                                                            </option>
                                                            <option value="Human Error">Human Error</option>
                                                            <option value="System Error">System Error</option>
                                                            <option value="Data Tidak Lengkap">Data Tidak Lengkap</option>
                                                            <option value="Kesalahan Komunikasi">Kesalahan Komunikasi
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-dismiss="modal">Close</button>
                                            <button type="submit" id="submitTransactionButton"
                                                class="btn btn-success">Save</button>
                                        </div>
                                    </form>
                                </div>
                                <div id="sendOffice">
                                    <form id="addShipWarehouseSendOfficeModalForm" method="POST"
                                        action="{{ url('addAdjustmentShipWarehouseSendOffice') }}"
                                        enctype="multipart/form-data">
                                        @csrf
                                        <div class="modal-body">
                                            <input type="hidden" name="condition_id" id="newConditionId">
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-8 col-sm-6">
                                                        <label for="newItemName"><span class="text-danger">*</span>Item
                                                            Name</label>
                                                        <div class="adjustmentItemName-dropdown">
                                                            <input required type="text" id="newItemName"
                                                                name="item_name" class="form-control" autocomplete="off"
                                                                data-toggle="dropdown" aria-haspopup="true"
                                                                aria-expanded="false" placeholder="Select item name">
                                                            <input type="text" id="newItemID" name="item_id" hidden>
                                                            <div id="adjustmentItemName-list" class="dropdown-menu"
                                                                aria-labelledby="newItemName">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-8 col-sm-6">
                                                        <label for="newCondition"><span
                                                                class="text-danger">*</span>Condition</label>
                                                        <input readonly type="text" name="condition" id="newCondition"
                                                            class="form-control">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-6 col-sm-4">
                                                        <label for="newQuantity"><span
                                                                class="text-danger">*</span>Quantity</label>
                                                        <input required disabled type="text" name="quantity"
                                                            id="newQuantity" class="form-control">
                                                    </div>
                                                    <div class="col-6 col-sm-4">
                                                        <label for="newQuantitySend"><span
                                                                class="text-danger">*</span>Quantity
                                                            Send</label>
                                                        <input required type="number" name="quantity_send"
                                                            id="newQuantitySend" class="form-control" min="1">
                                                    </div>
                                                    <div class="col-6 col-sm-4">
                                                        <label for="newQuantityLeft"><span
                                                                class="text-danger">*</span>Quantity
                                                            Left</label>
                                                        <input disabled required type="number" name="quantity_left"
                                                            id="newQuantityLeft" class="form-control">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-6 col-sm-4">
                                                        <label for="newSendDate"><span class="text-danger">*</span>Send
                                                            Date</label>
                                                        <input required type="date" name="send_date" id="newSendDate"
                                                            class="form-control">
                                                    </div>
                                                    <div class="col-6 col-sm-4">
                                                        <label for="newPic"><span
                                                                class="text-danger">*</span>PIC</label>
                                                        <input required type="text" name="pic" id="newPic"
                                                            class="form-control">
                                                    </div>
                                                    <div class="col-6 col-sm-4">
                                                        <label for="newDescription"><span
                                                                class="text-danger">*</span>Reason</label>
                                                        <select required name="description" id="newDescription"
                                                            class="form-control">
                                                            <option value="" disabled selected>-- Select Reason --
                                                            </option>
                                                            <option value="Human Error">Human Error</option>
                                                            <option value="System Error">System Error</option>
                                                            <option value="Data Tidak Lengkap">Data Tidak Lengkap</option>
                                                            <option value="Kesalahan Komunikasi">Kesalahan Komunikasi
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-dismiss="modal">Close</button>
                                            <button type="submit" id="submitTransactionButton"
                                                class="btn btn-success">Save</button>
                                        </div>
                                    </form>
                                </div>
                            </div><!-- tab-content -->
                        </div> <!-- /form -->
                    </div>

                </div>
            </div>
        </div>

        {{-- Update per Condition --}}
        <div class="modal fade" id="updateShipWarehouseConditionsModal" tabindex="-1" role="dialog"
            aria-labelledby="updateShipWarehouseConditionsModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateShipWarehouseConditionsModalLabel">Update Condition</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="updateShipWarehouseConditionsForm" method="POST"
                        action="{{ url('updateShipWarehouseConditions') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" name="condition_id" id="condition_id">
                            <div class="form-group">
                                <label for="quantity"><span class="text-danger">*</span>Quantity</label>
                                <input required type="number" name="quantity" id="quantity" class="form-control"
                                    @if ($user['role'] === 'Kapal') readonly @endif>
                            </div>
                            <div class="form-group">
                                <label for="condition">Condition</label>
                                <input disabled type="text" name="condition" id="condition" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="location"><span class="text-danger">*</span>Location</label>
                                <input required type="text" name="location" id="location" class="form-control">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" id="submitConditionButton" class="btn btn-success">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Add Send Office Modal --}}
        <div class="modal fade" id="addShipWarehouseSendOfficeModal" tabindex="-1" role="dialog"
            aria-labelledby="addShipWarehouseSendOfficeModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addShipWarehouseSendOfficeModalLabel">Send Item to Office</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="addShipWarehouseSendOfficeModalForm" method="POST"
                        action="{{ url('addShipWarehouseSendOffice') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" name="condition_id" id="newConditionId">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-8 col-sm-6">
                                        <label for="newItemName"><span class="text-danger">*</span>Item Name</label>
                                        <input required disabled type="text" name="item_name" id="newItemName"
                                            class="form-control">
                                    </div>
                                    <div class="col-8 col-sm-6">
                                        <label for="newCondition"><span class="text-danger">*</span>Condition</label>
                                        <input readonly type="text" name="condition" id="newCondition"
                                            class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-6 col-sm-4">
                                        <label for="newQuantity"><span class="text-danger">*</span>Quantity</label>
                                        <input required disabled type="text" name="quantity" id="newQuantity"
                                            class="form-control">
                                    </div>
                                    <div class="col-6 col-sm-4">
                                        <label for="newQuantitySend"><span class="text-danger">*</span>Quantity
                                            Send</label>
                                        <input required type="number" name="quantity_send" id="newQuantitySend"
                                            class="form-control" min="1">
                                    </div>
                                    <div class="col-6 col-sm-4">
                                        <label for="newQuantityLeft"><span class="text-danger">*</span>Quantity
                                            Left</label>
                                        <input disabled required type="number" name="quantity_left" id="newQuantityLeft"
                                            class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-6 col-sm-4">
                                        <label for="newSendDate"><span class="text-danger">*</span>Send Date</label>
                                        <input required type="date" name="send_date" id="newSendDate"
                                            class="form-control">
                                    </div>
                                    <div class="col-6 col-sm-4">
                                        <label for="newPic"><span class="text-danger">*</span>PIC</label>
                                        <input required type="text" name="pic" id="newPic"
                                            class="form-control">
                                    </div>
                                    <div class="col-6 col-sm-4">
                                        <label for="photo">Photo</label>
                                        <input type="file" name="photo" id="photo" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="newDescription">Description</label>
                                <textarea type="text" name="description" id="newDescription" class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" id="submitTransactionButton" class="btn btn-success">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Detail Usage Modal --}}
        <div class="modal fade" id="detailShipWarehouseUsageModal" tabindex="-1" role="dialog"
            aria-labelledby="detailShipWarehouseUsageModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="detailShipWarehouseUsageModalLabel">Detail Usage</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body text-center">
                        <div id="image-content">
                        </div>
                        <div class="form-group">
                            <input disabled name="description" type="text" class="form-control" id="description">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Usage List Modal -->
        <div class="modal fade" id="listShipWarehouseUsageModal" tabindex="-1" role="dialog"
            aria-labelledby="listShipWarehouseUsageModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="listShipWarehouseUsageModalLabel">Ship Warehouse Usage</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="input-group mb-3">
                            <input id="searchShipUsage" type="text" class="form-control" placeholder="Search...">
                            <div class="input-group-append">
                                <button class="btn btn-sm btn-primary" type="button">Search</button>
                            </div>
                        </div>
                        <div id="shipWarehouseUsageList">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Send Office List Modal -->
        <div class="modal fade" id="listShipWarehouseSendOfficeModal" tabindex="-1" role="dialog"
            aria-labelledby="listShipWarehouseSendOfficeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="listShipWarehouseSendOfficeModalLabel">Ship Warehouse Send to Office
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="input-group mb-3">
                            <input id="searchShipSendOffice" type="text" class="form-control"
                                placeholder="Search...">
                            <div class="input-group-append">
                                <button class="btn btn-sm btn-primary" type="button">Search</button>
                            </div>
                        </div>
                        <div id="shipWarehouseSendOfficeList">

                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Detail Send Office Modal --}}
        <div class="modal fade" id="detailShipWarehouseSendOfficeModal" tabindex="-1" role="dialog"
            aria-labelledby="detailShipWarehouseSendOfficeModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="detailShipWarehouseSendOfficeModalLabel">Detail Send to Office</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body text-center">
                        <div id="image-content">

                        </div>
                        <div class="form-group">
                            <input disabled name="description" type="text" class="form-control" id="description">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Import Modal -->
        <div class="modal fade" id="importShipWarehouseModal" tabindex="-1" role="dialog"
            aria-labelledby="importShipWarehouseModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addShipModalLabel" style="margin: auto 10px auto 1;">
                            Import Data</h5>
                        <div class="btn-group modal-title" role="group">
                            <a href="{{ asset('import/shipWarehouse/import format.xlsx') }}"
                                class="btn btn-outline-success" download>
                                Download Format
                            </a>
                        </div>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form method="POST" action="{{ route('importShipWarehouses') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="shipID"><span class="text-danger">*</span>Ship Name</label>
                                <input readonly required name="ship_name" type="text" class="form-control"
                                    id="shipID">
                            </div>
                            <div class="form-group">
                                <label for="importFile"><span class="text-danger">*</span>Import File (.csv)</label>
                                <input required name="import_file" type="file" class="form-control" id="importFile"
                                    accept=".xlsx">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-success">Import</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- History Modal --}}
        <div class="modal fade" id="shipWarehouseHistoryModal" tabindex="-1" role="dialog"
            aria-labelledby="shipWarehouseHistoryModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-custom-medium" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="shipWarehouseHistoryModalLabel">Ship Warehouse History</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="" method="POST" action="{{ url('') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div style="max-height: 510px; overflow-y: auto">
                                <table class="table table-bordered text-center" data-toggle="table"
                                    data-sortable="true">
                                    <thead
                                        style="position: sticky; top: 0; background-color: white; z-index: 10; border-bottom: solid 1px black;">
                                        <tr>
                                            <th style="font-size: 1rem" data-sortable="true">No</th>
                                            <th style="font-size: 1rem" data-sortable="true">Category</th>
                                            <th style="font-size: 1rem" data-sortable="true">Source/Destination</th>
                                            <th style="font-size: 1rem" data-sortable="true">Item PMS</th>
                                            <th style="font-size: 1rem" data-sortable="true">Item Name</th>
                                            <th style="font-size: 1rem" data-sortable="true">Condition</th>
                                            <th style="font-size: 1rem" data-sortable="true">Quantity Before</th>
                                            <th style="font-size: 1rem" data-sortable="true">Quantity After</th>
                                            <th style="font-size: 1rem" data-sortable="true">Transaction Date</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tableBodyShipWarehouseHistory">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endsection
    @section('script')
        {{-- Load and Search Data --}}
        <script>
            $(document).ready(function() {
                $('.nav-pills .nav-link').on('click', function() {
                    var shipID = $(this).attr('id');
                    loadData(shipID);
                });

                $('input[id="searchShipWarehouses"]').keyup(function() {
                    var shipID = localStorage.getItem('activePillId');
                    var searchKeyword = $(this).val();
                    loadData(shipID, searchKeyword);
                });

                $('.nav-pills .nav-link').on('click', function() {
                    var pillID = $(this).attr('id');
                    localStorage.setItem('activePillId', pillID);
                });

                var activePillId = localStorage.getItem('activePillId');
                if (activePillId) {
                    $('#' + activePillId).tab('show');
                    var shipID = activePillId;
                    loadData(shipID);
                }
            });

            function loadData(shipID, searchKeyword = '') {
                $(".data").html('<center><p class="text-muted">Searching for data</p></center>');
                $.ajax({
                    url: '/data-shipWarehouses/' + shipID,
                    type: 'GET',
                    data: {
                        search: searchKeyword
                    },
                    success: function(response) {
                        $(".data").html(response.html);
                        // Dapatkan nama kapal yang dipilih
                        var selectedShipName = $('#' + shipID).text().trim();

                        // Ubah teks elemen dengan class vessel-title
                        $('.vessel-title').text(selectedShipName);
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            }
        </script>

        {{-- Add Modal --}}
        <script>
            $('#addShipWarehousesModal').on('show.bs.modal', function(e) {
                var activePill = $('#v-pills-tab .nav-link.active');
                var shipData = activePill.data('ship');
                $('#newShipID').val(shipData.ship_name);
            });
        </script>

        {{-- Item name dropdown --}}
        <script>
            $(document).ready(function() {
                function setupDropdown(dropdownInput, dropdownMenu) {
                    $(dropdownInput).on('input', function() {
                        var keyword = $(this).val().toLowerCase();
                        $(dropdownMenu).find('.dropdown-item').each(function() {
                            var text = $(this).text().toLowerCase();
                            if (text.indexOf(keyword) > -1) {
                                $(this).show();
                            } else {
                                $(this).hide();
                            }
                        });
                        $(dropdownMenu).addClass('show-dropdown');
                    });
                    $(dropdownMenu).on('click', '.dropdown-item', function() {
                        var item = $(this).data('item');
                        $('#newItemName').val(item.item_pms + ' - ' + item.item_name);
                        $('#newItemID').val(item.id);
                        $('#newUnit').val(item.item_unit);
                        $(dropdownMenu).removeClass('show-dropdown');
                        $('#item-validation-message').text('');
                        var item_id = $('#newItemID').val();
                        var shipID = localStorage.getItem('activePillId');
                        if (item_id && shipID) {
                            checkItemExistence(item_id, shipID, $(dropdownInput).closest('.modal-body'));
                        }
                    });
                }

                function checkItemExistence(item_id, shipID, modalBody) {
                    $.ajax({
                        type: "get",
                        url: "{{ url('checkShipWarehouses') }}",
                        data: {
                            id: item_id,
                            shipID: shipID,
                        },
                        success: function(response) {
                            if (response.exists) {
                                $(modalBody).find('#item-validation-message').text('Item already exist');
                                $(modalBody).find('input[name="item_name"]').val('');
                            } else {
                                $(modalBody).find('#item-validation-message').text('');
                            }
                        },
                    });
                }
                setupDropdown('#newItemName', '#addShipWarehousesModal #itemName-list-col3');
                setupDropdown('#itemName', '#updateShipWarehousesModal #itemName-list-col3');

                // Hide dropdown when clicking outside
                $(document).on('click', function(event) {
                    if (!$(event.target).closest('.dropdown').length) {
                        $('.dropdown-menu').removeClass('show-dropdown');
                    }
                });
            });
        </script>

        {{-- Update Modal --}}
        <script>
            $(document).ready(function() {
                $('#updateShipWarehousesModal').on('show.bs.modal', function(e) {
                    var activePill = $('#v-pills-tab .nav-link.active');
                    var shipData = activePill.data('ship');
                    $('#updateShipWarehousesModal #shipID').val(shipData.ship_name);
                    var button = $(e.relatedTarget); // Button that triggered the modal
                    var itemData = button.data('shipwarehouses'); // Extract info from data-* attributes
                    if (itemData) {
                        // $('#updateShipWarehousesModal #shipID').val(itemData.ship_id);
                        $('#updateShipWarehousesModal #id').val(itemData.id);
                        $('#updateShipWarehousesModal #itemName').val(itemData.item_name);
                        $('#updateShipWarehousesModal #itemID').val(itemData.item_id);
                        $('#updateShipWarehousesModal #department').val(itemData.department);
                        $('#updateShipWarehousesModal #positionDate').val(itemData.position_date);
                        $('#updateShipWarehousesModal #equipmentCategory').val(itemData.equipment_category);
                        $('#updateShipWarehousesModal #toolCategory').val(itemData.tool_category);
                        $('#updateShipWarehousesModal #type').val(itemData.type);
                        $('#updateShipWarehousesModal #certification').val(itemData.certification);
                        $('#updateShipWarehousesModal #location').val(itemData.location);
                        $('#updateShipWarehousesModal #lastMaintenanceDate').val(itemData
                            .last_maintenance_date);
                        $('#updateShipWarehousesModal #lastInspectionDate').val(itemData.last_inspection_date);
                        $('#updateShipWarehousesModal #description').val(itemData.description);
                        $('#updateShipWarehousesModal #quantity').val(itemData.quantity);
                        $('#updateShipWarehousesModal #minimumQuantity').val(itemData.minimum_quantity);
                        $('#updateShipWarehousesModal #unit').val(itemData.unit);
                        $('#updateShipWarehousesModal #condition').val(itemData.condition);
                    } else {
                        console.log('No item data found');
                    }
                });
            });
        </script>

        {{-- Add Ship Warehouse Usage --}}
        <script>
            $(document).ready(function() {
                $('#addShipWarehouseUsageModal').on('show.bs.modal', function(e) {
                    var data = $(e.relatedTarget).data('ship-warehouses');
                    if (data) {
                        var condition = data.condition;
                        var warehouse = data.warehouse;
                        // Populate the form with the condition data
                        $('#addShipWarehouseUsageModal #newConditionId').val(condition.id);
                        $('#addShipWarehouseUsageModal #newItemName').val(warehouse.item_name);
                        $('#addShipWarehouseUsageModal #newCondition').val(condition.condition);
                        $('#addShipWarehouseUsageModal #newQuantity').val(condition.quantity);
                        $('#addShipWarehouseUsageModal #newQuantityUsed').val(
                            0); // Initial value for Quantity Used
                        $('#addShipWarehouseUsageModal #newQuantityLeft').val(condition
                            .quantity); // Initial value for Quantity Left
                        // Set default value for usage date to today
                        var today = new Date().toISOString().split('T')[0];
                        $('#addShipWarehouseUsageModal #newUsageDate').val(today);
                    } else {
                        console.log('No data found for ship warehouses');
                    }

                    // Cut Off bulanan
                    var usageDateInput = document.getElementById('newUsageDate');
                    var today = new Date();
                    var year = today.getFullYear();
                    var month = today.getMonth() + 1; // JavaScript months are 0-11
                    var lastDayOfMonth = new Date(year, month, 0)
                        .getDate(); // Get last day of the current month
                    if (month < 10) {
                        month = '0' + month; // Ensure month is in "MM" format
                    }

                    var minDate = year + '-' + month + '-01'; // First day of the current month
                    var maxDate = year + '-' + month + '-' +
                        lastDayOfMonth; // Last day of the current month

                    usageDateInput.min = minDate;
                    usageDateInput.max = maxDate;
                });

                // Update the quantity left field based on the quantity used input
                $('#addShipWarehouseUsageModal #newQuantityUsed').on('input', function() {
                    var quantityUsed = $(this).val();
                    var quantity = $('#addShipWarehouseUsageModal #newQuantity').val();
                    var quantityLeft = quantity - quantityUsed;

                    if (quantityUsed < 0 || quantityLeft < 0) {
                        notEnoughQuantity()
                        $(this).val(0);
                        $('#addShipWarehouseUsageModal #newQuantityLeft').val(quantity);
                    } else {
                        $('#addShipWarehouseUsageModal #newQuantityLeft').val(quantityLeft);
                    }
                });

                function notEnoughQuantity() {
                    Swal.fire({
                        title: 'Not enough quantity!',
                        icon: 'info',
                        showCancelButton: false,
                        confirmButtonColor: '#46a146',
                        confirmButtonText: 'Got it'
                    }).then((result) => {});
                }
            });
        </script>

        {{-- Update Condition Modal --}}
        <script>
            // Update Ship Warehouse Condition Modal
            $('#updateShipWarehouseConditionsModal').on('show.bs.modal', function(e) {
                var button = $(e.relatedTarget);
                var conditionData = button.data('condition');
                // Populate the form with the condition data
                $('#updateShipWarehouseConditionsModal #condition_id').val(conditionData.id);
                $('#updateShipWarehouseConditionsModal #quantity').val(conditionData.quantity);
                $('#updateShipWarehouseConditionsModal #condition').val(conditionData.condition);
                $('#updateShipWarehouseConditionsModal #location').val(conditionData.location);
            });
        </script>

        {{-- Ship Warehouse Send Office Modal --}}
        <script>
            $(document).ready(function() {
                $('#addShipWarehouseSendOfficeModal').on('show.bs.modal', function(e) {
                    var data = $(e.relatedTarget).data('ship-warehouses');
                    if (data) {
                        var condition = data.condition;
                        var warehouse = data.warehouse;
                        // Populate the form with the condition data
                        $('#addShipWarehouseSendOfficeModal #newConditionId').val(condition.id);
                        $('#addShipWarehouseSendOfficeModal #newItemName').val(warehouse.item_name);
                        $('#addShipWarehouseSendOfficeModal #newCondition').val(condition.condition);
                        $('#addShipWarehouseSendOfficeModal #newQuantity').val(condition.quantity);
                        $('#addShipWarehouseSendOfficeModal #newQuantitySend').val(
                            0); // Initial value for Quantity Used
                        $('#addShipWarehouseSendOfficeModal #newQuantityLeft').val(condition
                            .quantity);
                        var today = new Date().toISOString().split('T')[0];
                        $('#addShipWarehouseSendOfficeModal #newSendDate').val(today);
                    } else {
                        console.log('No data found for ship warehouses');
                    }

                    // Cut Off bulanan
                    var sendDateInput = $('#addShipWarehouseSendOfficeModal #newSendDate');
                    var today = new Date();
                    var year = today.getFullYear();
                    var month = today.getMonth() + 1; // JavaScript months are 0-11
                    var lastDayOfMonth = new Date(year, month, 0)
                        .getDate(); // Get last day of the current month

                    if (month < 10) {
                        month = '0' + month; // Ensure month is in "MM" format
                    }

                    var minDate = year + '-' + month + '-01'; // First day of the current month
                    var maxDate = year + '-' + month + '-' + lastDayOfMonth; // Last day of the current month

                    // Use jQuery .attr() to set the min and max attributes
                    sendDateInput.attr('min', minDate);
                    sendDateInput.attr('max', maxDate);

                });

                // Update the quantity left field based on the quantity used input
                $('#addShipWarehouseSendOfficeModal #newQuantitySend').on('input', function() {
                    var quantityUsed = $(this).val();
                    var quantity = $('#addShipWarehouseSendOfficeModal #newQuantity').val();
                    var quantityLeft = quantity - quantityUsed;

                    if (quantityUsed < 0 || quantityLeft < 0) {
                        notEnoughQuantity();
                        $(this).val(0);
                        $('#addShipWarehouseSendOfficeModal #newQuantityLeft').val(quantity);
                    } else {
                        $('#addShipWarehouseSendOfficeModal #newQuantityLeft').val(quantityLeft);
                    }
                });

                function notEnoughQuantity() {
                    Swal.fire({
                        title: 'Not enough quantity!',
                        icon: 'info',
                        showCancelButton: false,
                        confirmButtonColor: '#46a146',
                        confirmButtonText: 'Got it'
                    }).then((result) => {});
                }
            });
        </script>

        {{-- Details Ship Warehouse Usage Modal --}}
        <script>
            $(document).ready(function() {
                $('#detailShipWarehouseUsageModal').on('show.bs.modal', function(e) {
                    $('#listShipWarehouseUsageModal').addClass('modal-backdrop');
                    var button = $(e.relatedTarget);
                    var photo = button.data('photo');
                    var description = button.data('description');

                    var modalBody = $('#image-content');
                    modalBody.empty(); // Clear previous images
                    // Handle the case where images are null, undefined, or an empty array
                    if (photo) {
                        var imagePath = 'images/uploads/shipWarehouseUsage-photos/' + photo;
                        console.log(imagePath);
                        var imgElement = `
                                <div class="mb-3">
                                    <img src="${imagePath}" class="img-fluid fixed-size-image">
                                </div>
                                `;
                        modalBody.append(imgElement);
                    } else {
                        modalBody.append('<p>No images available.</p>');
                    }
                    // Set the text of the textarea element
                    $('#detailShipWarehouseUsageModal input[name="description"]').val(description).val(
                        description);
                });
                $('#detailShipWarehouseUsageModal').on('hidden.bs.modal', function(e) {
                    $('#listShipWarehouseUsageModal').removeClass('modal-backdrop');
                    if ($('.modal.show').length) {
                        $('body').addClass('modal-open');
                    }
                });
            });
        </script>

        {{-- Ship Warehouse Usage Modal --}}
        <script>
            $(document).ready(function() {
                $('#listShipWarehouseUsageModal').on('show.bs.modal', function(e) {
                    var activePill = $('#v-pills-tab .nav-link.active');
                    var shipData = activePill.data('ship'); // Periksa apakah shipId benar
                    var shipId = shipData.id;
                    loadShipWarehouseUsageData(shipId); // Panggil fungsi untuk memuat data

                    // Fungsi pencarian di dalam event show.bs.modal
                    $('#listShipWarehouseUsageModal #searchShipUsage').off('input').on('input', function() {
                        var search = $(this).val();
                        loadShipWarehouseUsageData(shipId, search); // Memuat data berdasarkan pencarian
                    });
                });

                function loadShipWarehouseUsageData(shipId, search = '') {
                    // AJAX request untuk mendapatkan data penggunaan
                    $.ajax({
                        url: '/shipWarehouseUsage/' + shipId,
                        method: 'GET',
                        data: {
                            search: search
                        },
                        success: function(data) {
                            $('#shipWarehouseUsageList').html(data.html);
                        },
                        error: function(xhr, status, error) {
                            console.error("Error:", error);
                        }
                    });
                }
            });
        </script>

        {{-- Ship Warehouse Send Office List Modal --}}
        <script>
            $(document).ready(function() {
                $('#listShipWarehouseSendOfficeModal').on('show.bs.modal', function(e) {
                    var activePill = $('#v-pills-tab .nav-link.active');
                    var shipData = activePill.data('ship'); // Periksa apakah shipId benar
                    var shipId = shipData.id; // Extract info from data-* attributes
                    loadShipWarehouseSendOfficeData(shipId);

                    // Handle the search functionality
                    $('#searchShipSendOffice').on('input', function() {
                        var search = $(this).val();
                        loadShipWarehouseSendOfficeData(shipId, search);
                    });
                });

                function loadShipWarehouseSendOfficeData(shipId, search = '') {
                    // AJAX request to get the usage data
                    $.ajax({
                        url: '/shipWarehouseSendOffice/' + shipId,
                        method: 'GET',
                        data: {
                            search: search
                        },
                        success: function(data) {
                            $('#shipWarehouseSendOfficeList').html(data.html);
                        },
                        error: function(xhr, status, error) {
                            console.error(error);
                        }
                    });
                }
            });
        </script>

        {{-- Details Ship Warehouse Send Office Modal --}}
        <script>
            $(document).ready(function() {
                $('#detailShipWarehouseSendOfficeModal').on('show.bs.modal', function(e) {
                    $('#listShipWarehouseSendOfficeModal').addClass('modal-backdrop');
                    var button = $(e.relatedTarget);
                    var photo = button.data('photo');
                    var description = button.data('description');
                    // Set the src attribute of the img element
                    var modalBody = $('#detailShipWarehouseSendOfficeModal #image-content');
                    modalBody.empty(); // Clear previous images
                    // Handle the case where images are null, undefined, or an empty array
                    if (photo) {
                        var imagePath = 'images/uploads/shipWarehouseSendOffice-photos/' + photo;
                        // console.log(imagePath);
                        var imgElement = `
                <div class="mb-3">
                    <img src="${imagePath}" class="img-fluid fixed-size-image">
                </div>
            `;
                        modalBody.append(imgElement);
                    } else {
                        modalBody.append('<p>No images available.</p>');
                    }
                    // Set the text of the textarea element
                    $('#detailShipWarehouseSendOfficeModal input[name="description"]').val(description);
                    console.log('foto = ' + photo);
                    console.log('desk = ' + description);
                });
                $('#detailShipWarehouseSendOfficeModal').on('hidden.bs.modal', function(e) {
                    $('#listShipWarehouseSendOfficeModal').removeClass('modal-backdrop');
                });
            });
        </script>

        {{-- Import Ship Warehouse Modal --}}
        <script>
            $(document).ready(function() {
                $('#importShipWarehouseModal').on('show.bs.modal', function(e) {
                    var activePill = $('#v-pills-tab .nav-link.active');
                    var shipData = activePill.data('ship');
                    $('#importShipWarehouseModal #shipID').val(shipData
                        .ship_name); // Use the correct variable name here
                });
            });
        </script>

        {{-- Adjustment Modal --}}
        <script>
            $(document).ready(function() {
                $('#adjustmentShipWarehouseModal').on('show.bs.modal', function(e) {
                    var today = new Date().toISOString().split('T')[0];
                    $('#adjustmentShipWarehouseModal #newUsageDate').val(today);
                    $('#adjustmentShipWarehouseModal #newSendDate').val(today);
                    var shipID = localStorage.getItem('activePillId');
                    $('#adjustmentShipWarehouseModal #usage #newShipID').val(shipID);
                    $('#adjustmentShipWarehouseModal #sendOffice #newShipID').val(shipID);
                    $.ajax({
                        type: "get",
                        url: "{{ url('get-itemsInShip') }}",
                        data: {
                            shipID: shipID
                        },
                        success: function(response) {
                            var adjustmentItemNameListUsage = $('#usage #adjustmentItemName-list');
                            var adjustmentItemNameListSendOffice = $(
                                '#sendOffice #adjustmentItemName-list');

                            adjustmentItemNameListUsage.empty();
                            adjustmentItemNameListSendOffice.empty();

                            $.each(response, function(index, adjustmentItemName) {
                                var newLinkUsage = $('<a></a>')
                                    .addClass('dropdown-item adjustmentItemName-dropdown')
                                    .attr('href', '#')
                                    .attr('data-item-id', adjustmentItemName.id)
                                    .attr('data-item-name', adjustmentItemName.item_name)
                                    .attr('data-item-quantity', adjustmentItemName.quantity)
                                    .attr('data-item-code', adjustmentItemName.item_pms)
                                    .text(adjustmentItemName.item_pms + '-' +
                                        adjustmentItemName.item_name);

                                var newLinkSendOffice = newLinkUsage
                                    .clone(); // Buat salinan dari elemen

                                adjustmentItemNameListUsage.append(newLinkUsage);
                                adjustmentItemNameListSendOffice.append(newLinkSendOffice);
                            });
                        }
                    });
                    // START ADJUSTMENT USAGE
                    // Event handler ketika item name dipilih
                    $('#adjustmentShipWarehouseModal #usage #adjustmentItemName-list').on('click',
                        '.dropdown-item',
                        function(e) {
                            e.preventDefault();
                            var itemID = $(this).data('item-id');
                            var itemName = $(this).data('item-name');
                            var itemQuantity = $(this).data('item-quantity');
                            var itemCode = $(this).data('item-code');

                            // Set nilai item name dan item ID
                            $('#adjustmentShipWarehouseModal #usage #newItemName').val(itemCode + '-' +
                                itemName);
                            $('#adjustmentShipWarehouseModal #usage #newItemID').val(itemID);

                            // Reset dropdown condition dan hapus quantity
                            $('#adjustmentShipWarehouseModal #usage #newCondition').val(
                                ''); // Reset condition
                            $('#adjustmentShipWarehouseModal #usage #newQuantity').val(
                                ''); // Reset quantity

                            // Focus pada condition setelah memilih item
                            $('#adjustmentShipWarehouseModal #usage #newCondition').focus();
                        });

                    // Event handler ketika condition dipilih
                    $('#adjustmentShipWarehouseModal #usage #newCondition').on('change', function() {
                        var selectedCondition = $(this).val();
                        var itemID = $('#adjustmentShipWarehouseModal #usage #newItemID')
                            .val(); // Ambil item ID dari input yang dipilih
                        if (itemID && selectedCondition) {
                            // Lakukan pencarian AJAX untuk mendapatkan quantity berdasarkan item ID dan kondisi
                            $.ajax({
                                type: 'GET',
                                url: "{{ url('get-itemQuantity') }}", // Ganti dengan URL endpoint yang benar
                                data: {
                                    item_id: itemID,
                                    condition: selectedCondition,
                                    shipID: shipID
                                },
                                success: function(response) {
                                    // Set nilai quantity sesuai response
                                    $('#adjustmentShipWarehouseModal #usage #newQuantity')
                                        .val(
                                            response.quantity);
                                },
                                error: function() {
                                    console.error("Error fetching quantity");
                                }
                            });
                        }
                    });

                    // Event handler ketika item name diubah secara manual
                    $('#adjustmentShipWarehouseModal #usage #newItemName').on('input', function() {
                        // Reset condition dan quantity saat item name berubah
                        $('#adjustmentShipWarehouseModal #usage #newCondition').val(
                            ''); // Reset condition
                        $('#adjustmentShipWarehouseModal #usage #newQuantity').val(
                            ''); // Reset quantity
                    });

                    // Fungsi pencarian dropdown item
                    $('#adjustmentShipWarehouseModal #usage #newItemName').on('input', function() {
                        var keyword = $(this).val().toLowerCase();
                        $('.dropdown-item').each(function() {
                            var text = $(this).text().toLowerCase();
                            if (text.indexOf(keyword) > -1) {
                                $(this).show();
                            } else {
                                $(this).hide();
                            }
                        });
                        $('.dropdown-menu').addClass('show-dropdown');
                    });
                    // Update the quantity left field based on the quantity used input
                    $('#adjustmentShipWarehouseModal #usage #newQuantityUsed').on('input', function() {
                        var quantityUsed = $(this).val();
                        var quantity = $('#adjustmentShipWarehouseModal #usage #newQuantity').val();
                        var quantityLeft = quantity - quantityUsed;

                        if (quantityUsed < 0 || quantityLeft < 0) {
                            notEnoughQuantity()
                            $(this).val(0);
                            $('#adjustmentShipWarehouseModal #usage #newQuantityLeft').val(quantity);
                        } else {
                            $('#adjustmentShipWarehouseModal #usage #newQuantityLeft').val(
                                quantityLeft);
                        }
                    });
                    // END ADJUSTMENT USAGE

                    // START ADJUSTMENT SEND OFFICE
                    // Event handler ketika item name dipilih
                    $('#adjustmentShipWarehouseModal #sendOffice #adjustmentItemName-list').on('click',
                        '.dropdown-item',
                        function(e) {
                            e.preventDefault();
                            var itemID = $(this).data('item-id');
                            var itemName = $(this).data('item-name');
                            var itemQuantity = $(this).data('item-quantity');
                            var itemCode = $(this).data('item-code');

                            // Set nilai item name dan item ID
                            $('#adjustmentShipWarehouseModal #sendOffice #newItemName').val(itemCode + '-' +
                                itemName);
                            $('#adjustmentShipWarehouseModal #sendOffice #newItemID').val(itemID);

                            // Reset dropdown condition dan hapus quantity
                            $('#adjustmentShipWarehouseModal #sendOffice #newCondition').val(
                                'Bekas Tidak Bisa Pakai'); // Reset condition
                            $('#adjustmentShipWarehouseModal #sendOffice #newQuantity').val(
                                ''); // Reset quantity
                            // Focus pada condition setelah memilih item
                            $('#adjustmentShipWarehouseModal #sendOffice #newCondition').focus();
                            var itemID = $('#adjustmentShipWarehouseModal #sendOffice #newItemID')
                                .val(); // Ambil item ID dari input yang dipilih
                            if (itemID) {
                                // Lakukan pencarian AJAX untuk mendapatkan quantity berdasarkan item ID dan kondisi
                                $.ajax({
                                    type: 'GET',
                                    url: "{{ url('get-itemQuantity') }}", // Ganti dengan URL endpoint yang benar
                                    data: {
                                        item_id: itemID,
                                        condition: 'Bekas Tidak Bisa Pakai',
                                        shipID: shipID
                                    },
                                    success: function(response) {
                                        // Set nilai quantity sesuai response
                                        $('#adjustmentShipWarehouseModal #sendOffice #newQuantity')
                                            .val(response.quantity);
                                        $('#adjustmentShipWarehouseModal #sendOffice #newConditionId')
                                            .val(response.id);
                                    },
                                    error: function() {
                                        console.error("Error fetching quantity");
                                    }
                                });
                            }
                        });

                    // Event handler ketika item name diubah secara manual
                    $('#adjustmentShipWarehouseModal #sendOffice #newItemName').on('input', function() {
                        // Reset condition dan quantity saat item name berubah
                        $('#adjustmentShipWarehouseModal #sendOffice #newCondition').val(
                            ''); // Reset condition
                        $('#adjustmentShipWarehouseModal #sendOffice #newQuantity').val(
                            ''); // Reset quantity
                    });

                    // Fungsi pencarian dropdown item
                    $('#adjustmentShipWarehouseModal #sendOffice #newItemName').on('input', function() {
                        var keyword = $(this).val().toLowerCase();
                        $('.dropdown-item').each(function() {
                            var text = $(this).text().toLowerCase();
                            if (text.indexOf(keyword) > -1) {
                                $(this).show();
                            } else {
                                $(this).hide();
                            }
                        });
                        $('.dropdown-menu').addClass('show-dropdown');
                    });

                    // Update the quantity left field based on the quantity used input
                    $('#adjustmentShipWarehouseModal #sendOffice #newQuantitySend').on('input', function() {
                        var quantitySend = $(this).val();
                        var quantity = $('#adjustmentShipWarehouseModal #sendOffice #newQuantity')
                            .val();
                        var quantityLeft = quantity - quantitySend;

                        if (quantitySend < 0 || quantityLeft < 0) {
                            notEnoughQuantity()
                            $(this).val(0);
                            $('#adjustmentShipWarehouseModal #sendOffice #newQuantityLeft').val(
                                quantity);
                        } else {
                            $('#adjustmentShipWarehouseModal #sendOffice #newQuantityLeft').val(
                                quantityLeft);
                        }
                    });
                    // END ADJUSTMENT SEND OFFICE

                    function notEnoughQuantity() {
                        Swal.fire({
                            title: 'Not enough quantity!',
                            icon: 'info',
                            showCancelButton: false,
                            confirmButtonColor: '#46a146',
                            confirmButtonText: 'Got it'
                        }).then((result) => {});
                    }


                    $('.tab a').on('click', function(e) {
                        e.preventDefault();
                        $(this).parent().addClass('active');
                        $(this).parent().siblings().removeClass('active');
                        target = $(this).attr('href');
                        $('.tab-content > div').not(target).hide();
                        $(target).fadeIn(600);
                    });
                });
            });
        </script>

        {{-- History Modal --}}
        <script>
            $(document).ready(function() {
                $('#shipWarehouseHistoryModal').on('show.bs.modal', function(e) {
                    var activePill = $('#v-pills-tab .nav-link.active');
                    var shipData = activePill.data('ship'); // Periksa apakah shipId benar
                    var shipId = shipData.id;
                    loadShipWarehouseHistoryData(shipId); // Panggil fungsi untuk memuat data
                });

                function loadShipWarehouseHistoryData(shipId) {
                    // AJAX request untuk mendapatkan data penggunaan
                    $.ajax({
                        url: '/shipWarehouseHistory/' + shipId,
                        method: 'GET',
                        success: function(data) {
                            populateShipWarehouseHistoryTable(data);
                        },
                        error: function(xhr, status, error) {
                            console.error("Error:", error);
                        }
                    });
                }

                function populateShipWarehouseHistoryTable(data) {
                    var tableBody = $('#tableBodyShipWarehouseHistory');
                    tableBody.empty(); // Kosongkan tabel sebelum mengisi data baru

                    // Akses array dari properti `history`
                    var records = data.history;

                    // Iterasi melalui data dalam `history`
                    records.forEach(function(record, index) {
                        var transactionTypeColor = record.transaction_type === 'In' ? 'green' : 'red';
                        var quantityChange = record.transaction_type === 'In' ? '+' : '-';
                        var quantityDifference = Math.abs(record.quantity_after - record.quantity_before);

                        var row = `
                            <tr>
                                <td>${index + 1}</td>
                                <td style="color: ${transactionTypeColor}; font-weight: bold; font-size:1rem;">
                                    ${record.transaction_type}
                                </td>
                                <td>${record.source_or_destination}</td>
                                <td>${record.items.item_pms}</td>
                                <td>${record.items.item_name}</td>
                                <td>${record.condition}</td>
                                <td>
                                    ${record.quantity_before}
                                    <span style="color: ${transactionTypeColor};">
                                        (${quantityChange}${quantityDifference})
                                    </span>
                                </td>
                                <td>${record.quantity_after}</td>
                                <td>${new Date(record.transaction_date).toLocaleDateString()}</td>
                            </tr>
                        `;
                        tableBody.append(row); // Tambahkan baris ke tabel
                    });
                }
            });
        </script>
    @endsection
</div>
