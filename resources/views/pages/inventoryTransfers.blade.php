@extends('layouts.layout')
<title>Inventory Transfer</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
@section('main-panel')
    <div class="main-panel">
        <div class="content-wrapper" style="padding: 1rem .8rem;">
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
                    @if (session('swal-fail-title') && session('swal-fail-text'))
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                Swal.fire({
                                    title: '{{ session('swal-fail-title') }}!',
                                    text: '{{ session('swal-fail-text') }}.',
                                    icon: 'error',
                                    showCancelButton: false,
                                });
                            });
                        </script>
                    @endif
                    <div class="row">
                        <div class="col-2" style="overflow-y: scroll; height: 83vh;">
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
                                            href="#v-pills-{{ $shipItem->id }}" role="tab" aria-controls="v-pills-home"
                                            aria-selected="true"
                                            data-ship="{{ json_encode($shipItem) }}">{{ $shipItem->ship_name }}</a>
                                    @endforeach
                                @endforeach
                            </div>
                        </div>
                        <div class="col-10" style="max-height: 83vh;">
                            <div class="card-body">
                                <p class="card-title mb-2">Inventory Transfers</p>
                                <p class="vessel-title">{{ $shipItem->ship_name }}</p>
                                <div class="input-group mb-3">
                                    <input id="searchInventoryTransfers" type="text" class="form-control"
                                        placeholder="Search...">
                                    <div class="input-group-append">
                                        <button class="btn btn-sm btn-primary" type="button">Search</button>
                                    </div>
                                </div>
                                @if ((Auth::user() && Auth::user()->role === 'Super Admin') || Auth::user()->role === 'Purchasing Logistic Admin')
                                    <div class="input-group mb-3">
                                        <button type="button" class="btn btn-success btn-icon-text mr-1"
                                            data-toggle="modal" data-target="#addInventoryTransferModal">
                                            <i class="mdi mdi-database-plus pr-2"></i>Add Inventory Transfer
                                        </button>
                                    </div>
                                @endif
                                <div class="row">
                                    <div class="col-12">
                                        <div class="table-responsive" style="height: 425px !important">
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
        </div>
        <!-- content-wrapper ends -->
        <!-- Add Inventory Transfer Modal -->
        <div class="modal fade" id="addInventoryTransferModal" tabindex="-1"
            aria-labelledby="addInventoryTransferModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addInventoryTransferModalLabel">Add Inventory Transfer</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ url('addInventoryTransfers') }}" method="POST" id="addInventoryTransfersForm"
                        enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <div class="modal-body">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-8 col-sm-6">
                                        <label for="">Origin Warehouse</label>
                                        <input readonly required type="text" class="form-control" value="Kantor Pusat">
                                    </div>
                                    <div class="col-8 col-sm-6">
                                        <label for="newShipID">Destination Warehouse</label>
                                        <input readonly required name="ship_id" type="text" class="form-control"
                                            id="newShipID">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-8 col-sm-6">
                                        <label for="newDeliveryOrderNumber"><span class="text-danger">*</span>Delivery
                                            Order
                                            Number</label>
                                        <input required name="delivery_order_number" type="text" class="form-control"
                                            id="newDeliveryOrderNumber">
                                        <div id="newDeliveryOrderNumber-validation-message" class="text-danger"></div>
                                    </div>
                                    <div class="col-8 col-sm-6">
                                        <label for="newShippingMethod"><span class="text-danger">*</span>Shipping
                                            Method</label>
                                        <input required name="shipping_method" type="text" class="form-control"
                                            id="newShippingMethod">
                                    </div>
                                </div>
                            </div>
                            <div class="section-border">
                                <h5>Sender Information</h5>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-8 col-sm-6">
                                            <label for="newSenderUP"><span class="text-danger">*</span>Sender Name</label>
                                            <input required type="text" class="form-control" id="newSenderUP"
                                                name="sender_up">
                                        </div>
                                        <div class="col-8 col-sm-6">
                                            <label for="newSenderContact"><span class="text-danger">*</span>Sender
                                                Contact</label></label>
                                            <input required name="sender_contact" type="text" class="form-control"
                                                id="newSenderContact">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-8 col-sm-6">
                                            <label for="newSenderTitle"><span class="text-danger">*</span>Sender
                                                Title</label>
                                            <input required type="text" class="form-control" id="newSenderTitle"
                                                name="sender_title">
                                        </div>
                                        <div class="col-8 col-sm-6">
                                            <label for="newSenderPhotos">Upload Photos</label>
                                            <input name="sender_photos[]" type="file" class="form-control"
                                                id="newSenderPhotos" aria-describedby="newSenderPhotos" accept="image/*"
                                                multiple>
                                            <small class="text-muted">You can select up to 3 images.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="section-border">
                                <h5>Recipient Information</h5>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-8 col-sm-6">
                                            <label for="newRecipientName"><span class="text-danger">*</span>Recipient
                                                Name</label>
                                            <input required type="text" class="form-control" id="newRecipientName"
                                                name="recipient_name">
                                        </div>
                                        <div class="col-8 col-sm-6">
                                            <label for="newRecipientProjectPosition"><span
                                                    class="text-danger">*</span>Project
                                                Position</label>
                                            <input required type="text" class="form-control"
                                                id="newRecipientProjectPosition" name="recipient_project_position">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-8 col-sm-6">
                                            <label for="newRecipientTitle"><span class="text-danger">*</span>Recipient
                                                Title</label>
                                            <input required type="text" class="form-control" id="newRecipientTitle"
                                                name="recipient_title">
                                        </div>
                                        <div class="col-8 col-sm-6">
                                            <label for="newRecipient_up">UP</label>
                                            <input type="text" class="form-control" id="newRecipient_up"
                                                name="recipient_up">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-8 col-sm-6">
                                        <label for="newSendDate">Send Date</label>
                                        <input required type="date" class="form-control" id="newSendDate"
                                            name="send_date">
                                    </div>
                                    <div class="col-8 col-sm-6">
                                        <label for="newReceivedDate">Received Date</label>
                                        <input readonly required name="received_date" type="date" class="form-control"
                                            id="newReceivedDate">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-8 col-sm-6">
                                        <a class="btn btn-success" href="#" data-toggle="modal"
                                            data-target="#selectPurchaseRequestsModal">
                                            <i class="fa-solid fa-list"></i> Select PR
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div id="temporaryItem">
                                    <table class="display expandable-table text-nowrap" style="min-width: 100%">
                                        <thead>
                                            <tr class="text-center">
                                                <th scope="col">No</th>
                                                <th scope="col">PMS Code</th>
                                                <th scope="col">Item Name</th>
                                                <th scope="col">Quantity</th>
                                                <th scope="col">Unit</th>
                                                <th scope="col" style="width: 200px"><span
                                                        class="text-danger">*</span>Condition</th>
                                                <th scope="col">PR No.</th>
                                                <th scope="col" style="width: 90px"><span
                                                        class="text-danger">*</span>Koli
                                                </th>
                                                <th scope="col">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Items will be appended here -->
                                        </tbody>
                                    </table>
                                    <div id="emptyTableWarning" class="text-danger text-center mt-3"
                                        style="display: none;">
                                        Please at least add one item.
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
                                <div class="row">
                                    <div class="col-8 col-sm-6">
                                        <label for="newPurchaseRequestNumber"><span class="text-danger">*</span>No.
                                            PR</label>
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
                                    <div class="col-8 col-sm-6">
                                        <label for="shipID">Ship Name</label>
                                        <input readonly required name="ship_id" type="text" class="form-control"
                                            id="shipID">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div id="temporaryItem">
                                    <table class="display expandable-table" style="width:100%">
                                        <thead>
                                            <tr class="text-center">
                                                <th scope="col">No</th>
                                                <th scope="col">PMS Code</th>
                                                <th scope="col">Item Name</th>
                                                <th scope="col">Quantity</th>
                                                <th scope="col">Unit</th>
                                                <th scope="col">Option</th>
                                                <th scope="col">Utility</th>
                                                <th scope="col">Status</th>
                                                <th scope="col">Action</th>
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

        <!-- Detail Inventory Transfer Modal -->
        <div class="modal fade" id="detailInventoryTransferModal" tabindex="-1"
            aria-labelledby="detailInventoryTransferModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="detailInventoryTransferModalLabel">Detail Inventory Transfer</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="updateInventoryTransfers" method="POST" id="" name=""
                        enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <div class="modal-body">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-8 col-sm-6">
                                        <label for="">Origin Warehouse</label>
                                        <input readonly required type="text" class="form-control"
                                            value="Kantor Pusat">
                                    </div>
                                    <div class="col-8 col-sm-6">
                                        <label for="shipID">Destination Warehouse</label>
                                        <input readonly required name="ship_id" type="text" class="form-control"
                                            id="shipID">
                                    </div>
                                </div>
                            </div>
                            <input name="id" type="hidden" class="form-control" id="inventoryTransferID">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-8 col-sm-6">
                                        <label for="deliveryOrderNumber">Delivery Order
                                            Number</label>
                                        <input readonly required name="delivery_order_number" type="text"
                                            class="form-control" id="deliveryOrderNumber">
                                        <div id="deliveryOrderNumber-validation-message" class="text-danger"></div>
                                    </div>
                                    <div class="col-8 col-sm-6">
                                        <label for="shippingMethod">Shipping
                                            Method</label>
                                        <input readonly required name="shipping_method" type="text"
                                            class="form-control" id="shippingMethod">
                                    </div>
                                </div>
                            </div>
                            <div class="section-border">
                                <h5>Sender Information</h5>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-8 col-sm-6">
                                            <label for="senderUP">Sender Name</label>
                                            <input readonly required type="text" class="form-control" id="senderUP"
                                                name="sender_up">
                                        </div>
                                        <div class="col-8 col-sm-6">
                                            <label for="senderContact">Sender Contact</label></label>
                                            <input readonly required name="sencder_contact" type="text"
                                                class="form-control" id="senderContact">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-8 col-sm-6">
                                            <label for="senderTitle">Sender Title</label>
                                            <input readonly required type="text" class="form-control" id="senderTitle"
                                                name="sender_title">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-8 col-sm-6">
                                            <button type="button" class="btn btn-success" data-toggle="modal"
                                                data-target="#photosModal" id="viewSenderPhotos">View Sender
                                                Photos</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="section-border">
                                <h5>Recipient Information</h5>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-8 col-sm-6">
                                            <label for="recipientName">Recipient Name</label>
                                            <input readonly required type="text" class="form-control"
                                                id="recipientName" name="recipient_name">
                                        </div>
                                        <div class="col-8 col-sm-6">
                                            <label for="recipientProjectPosition">Project Position</label>
                                            <input readonly required type="text" class="form-control"
                                                id="recipientProjectPosition" name="recipient_project_position">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-8 col-sm-6">
                                            <label for="recipientTitle">Recipient Title</label>
                                            <input readonly required type="text" class="form-control"
                                                id="recipientTitle" name="recipient_title">
                                        </div>
                                        <div class="col-8 col-sm-6">
                                            <label for="recipient_up">UP</label>
                                            <input readonly type="text" class="form-control" id="recipientUp"
                                                name="recipient_up">
                                        </div>
                                    </div>
                                </div>
                                @if ((Auth::user() && Auth::user()->role === 'Super Admin') || Auth::user()->role === 'Kapal')
                                    <div class="form-group upload-section">
                                        <div class="row">
                                            <div class="col-8 col-sm-6">
                                                <label for="file"><span class="text-danger">*</span>Delivery
                                                    Receipt</label>
                                                <input required name="file" type="file" class="form-control"
                                                    id="file" aria-describedby="file"
                                                    accept="image/*,.pdf,.doc,.docx,.xls,.xlsx" multiple>
                                                <small class="text-muted">Upload your delivery receipt.</small>
                                            </div>
                                            <div class="col-8 col-sm-6">
                                                <label for="recipientPhotos">Upload Photos</label>
                                                <input name="recipient_photos[]" type="file" class="form-control"
                                                    id="recipientPhotos" aria-describedby="recipientPhotos"
                                                    accept="image/*" multiple>
                                                <small class="text-muted">You can select up to 3 images.</small>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <div class="form-group view-section">
                                    <div class="row">
                                        @if (
                                            (Auth::user() && Auth::user()->role === 'Super Admin') ||
                                                Auth::user()->role === 'Purchasing Logistic Admin' ||
                                                Auth::user()->role === 'Kapal')
                                            <div class="col-8 col-sm-6">
                                                <a class="btn btn-primary" href="#" id="print-delivery-receipt">
                                                    Print Delivery Receipt</a>
                                            </div>
                                        @endif
                                        <div class="col-8 col-sm-6">
                                            <button type="button" class="btn btn-success" data-toggle="modal"
                                                data-target="#photosModal" id="viewRecipientPhotos">View Recipient
                                                Photos</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-8 col-sm-6">
                                        <label for="sendDate">Send Date</label>
                                        <input readonly required type="date" class="form-control" id="sendDate"
                                            name="send_date">
                                    </div>
                                    <div class="col-8 col-sm-6">
                                        <label for="receivedDate">Received Date</label>
                                        <input required name="received_date" type="date" class="form-control"
                                            id="receivedDate">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <!-- Accordion for Item Details -->
                                <div class="accordion" id="accordionItems">
                                    <!-- Dynamic accordion items will be appended here -->
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            @if (
                                (Auth::user() && Auth::user()->role === 'Super Admin') ||
                                    Auth::user()->role === 'Purchasing Logistic Admin' ||
                                    Auth::user()->role === 'Kapal')
                                <a class="btn btn-primary" href="#" id="print-delivery-order"><i
                                        class="fa-solid fa-print"></i>
                                    Print</a>
                            @endif
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            @if ((Auth::user() && Auth::user()->role === 'Super Admin') || Auth::user()->role === 'Kapal')
                                <button type="submit" id="submitButton"
                                    class="btn btn-success upload-section">Save</button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Photos Modal --}}
        <div class="modal fade" id="photosModal" tabindex="-1" role="dialog" aria-labelledby="photosModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="photosModalLabel">Photos</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body text-center" id="modalBodyContent">
                        <!-- Images will be dynamically inserted here -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous">
    </script>
    {{-- Load and Search Data --}}
    <script>
        $(document).ready(function() {
            $('.nav-pills .nav-link').on('click', function() {
                var shipID = $(this).attr('id');
                loadData(shipID);
            });

            $('input[id="searchInventoryTransfers"]').keyup(function() {
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
                url: '/data-inventoryTransfers/' + shipID,
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

    {{-- Add Inventory Transfer Modal --}}
    <script>
        $(document).ready(function() {
            function generateShipInitials(shipName) {
                var words = shipName.split(' '); // Pisahkan nama kapal berdasarkan spasi
                var initials = '';
                for (var i = 0; i < words.length - 1; i++) {
                    initials += words[i].charAt(0)
                        .toUpperCase(); // Ambil huruf pertama dari setiap kata kecuali kata terakhir
                }
                initials += words[words.length - 1].charAt(0)
                    .toUpperCase(); // Ambil huruf pertama dari kata terakhir
                return initials;
            }

            $('#addInventoryTransferModal').on('show.bs.modal', function(e) {
                var activePill = $('#v-pills-tab .nav-link.active');
                var shipData = activePill.data('ship');
                $('#addInventoryTransferModal #newShipID').val(shipData.ship_name);

                var today = new Date().toISOString().split('T')[0];
                $('#addInventoryTransferModal #newSendDate').val(today);
                // Cek duplikasi nomor Delivery Order
                $('#addInventoryTransferModal input[name="delivery_order_number"]').on('input',
                    function() {
                        var DOno = $(this).val();
                        $.ajax({
                            type: "get",
                            url: "{{ url('check-deliveryOrderNumber') }}",
                            data: "DOno=" + DOno,
                            success: function(response) {
                                if (response.exists) {
                                    $('#addInventoryTransferModal #newDeliveryOrderNumber-validation-message')
                                        .text(
                                            'Delivery order number already used.');
                                    $('#addInventoryTransferModal button[id=submitButton]')
                                        .attr('disabled', true);
                                } else {
                                    $('#addInventoryTransferModal #newDeliveryOrderNumber-validation-message')
                                        .text('');
                                    $('#addInventoryTransferModal button[id=submitButton]')
                                        .attr('disabled', false);
                                }
                            },
                            error: function() {
                                alert('Error');
                            }
                        });
                    });
                // Handle file input maximal 3
                document.getElementById('newSenderPhotos').addEventListener('change',
                    function() {
                        var fileInput = this;
                        var files = fileInput.files;
                        if (files.length > 3) {
                            alert('You can only upload a maximum of 3 files.');
                            fileInput.value =
                                ''; // Clear the input if the user exceeds the limit
                        }
                    });
            });
            $('#addInventoryTransferModal').on('hidden.bs.modal', function() {});
        });
    </script>

    {{-- Select Purchase Request Modal --}}
    <script>
        $(document).ready(function() {
            var addedItems = JSON.parse(localStorage.getItem('addedItems')) || [];

            // Function to open the modal
            $(document).ready(function() {
                // Event handler for when the "Select Purchase Requests" modal is shown
                $('#selectPurchaseRequestsModal').on('show.bs.modal', function(e) {
                    $('#addInventoryTransferModal').addClass('modal-backdrop');
                    var activePill = $('#v-pills-tab .nav-link.active');
                    var shipData = activePill.data('ship');
                    $('#shipID').val(shipData.ship_name);

                    $.ajax({
                        type: "get",
                        url: "{{ url('get-purchaseRequestDone') }}",
                        data: "ship=" + $('#newShipID').val(),
                        success: function(response) {
                            var purchaseRequestList = $('#purchaseRequest-list');
                            purchaseRequestList.empty();
                            $.each(response, function(index, purchaseRequest) {
                                var newLink = $('<a></a>')
                                    .addClass(
                                        'dropdown-item it-purchaseRequest-dropdown'
                                    )
                                    .attr('href', '#')
                                    .attr('data-purchase-request-id',
                                        purchaseRequest.id)
                                    .text(purchaseRequest
                                        .purchase_request_number);
                                purchaseRequestList.append(newLink);
                            });
                        },
                    });
                });

                // Event handler for when the "Add Purchase Orders" modal is shown
                $('#addInventoryTransferModal').on('show.bs.modal', function() {
                    // Attach the validation event handler for the form
                    $('#addInventoryTransfersForm').off('submit').on('submit', function(e) {
                        var tableBody = $(
                            '#addInventoryTransferModal #temporaryItem tbody');
                        if (tableBody.children('tr').length === 0) {
                            e.preventDefault(); // Prevent form submission
                            $('#emptyTableWarning').show(); // Show the warning message
                            return false;
                        } else {
                            $('#emptyTableWarning')
                                .hide(); // Hide the warning message if there are items
                        }
                    });

                    // Check if the table is empty when the modal is first opened
                    var tableBody = $('#addInventoryTransferModal #temporaryItem tbody');
                    if (tableBody.children('tr').length === 0) {
                        $('#emptyTableWarning').show(); // Show the warning message
                    } else {
                        $('#emptyTableWarning').hide(); // Hide the warning message
                    }
                });

                // Clear warning and reset form when the modal is hidden
                $('#addInventoryTransferModal').on('hidden.bs.modal', function() {
                    $('#emptyTableWarning').hide(); // Hide the warning message
                    $('#addInventoryTransfersForm')[0].reset(); // Reset the form fields
                    $('#temporaryItem tbody').empty(); // Clear the table
                });
            });

            $('#newPurchaseRequestNumber').on('input', function() {
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

            $('#purchaseRequest-list').on('click', '.dropdown-item', function(e) {
                e.preventDefault();
                var purchaseRequestID = $(this).data('purchase-request-id');
                $('#newPurchaseRequestNumber').val($(this).text());
                $('#newPurchaseRequestNumber').data('purchase-request-id', purchaseRequestID);
                $('#newPurchaseRequestNumber-validation').text('');
                loadPurchaseRequestItems(purchaseRequestID);
            });

            function loadPurchaseRequestItems(purchaseRequestID) {
                $.ajax({
                    url: '/get-itemPurchaseRequests/' + purchaseRequestID,
                    type: 'GET',
                    success: function(response) {
                        var tableBody = $('#selectPurchaseRequestsModal #temporaryItem tbody');
                        tableBody.empty();

                        if (response.items && response.items.length > 0) {
                            response.items.forEach(function(item, index) {
                                // Mengakses data dari tabel items melalui relasi
                                var itemData = item
                                    .items; // Mengakses data item dari tabel items
                                if (itemData) { // Pastikan itemData ada sebelum mengakses propertinya
                                    var addButton = `
                            <button type="button" class="btn btn-primary add-item" 
                                data-item-id="${item.id}" 
                                data-item-code="${itemData.item_pms}" 
                                data-item-name="${itemData.item_name}" 
                                data-item-quantity="${item.quantity}" 
                                data-item-unit="${itemData.item_unit}"
                                data-item-prno="${item.purchase_request.purchase_request_number}">
                                Add
                            </button>`;

                                    // Jika item sudah ditambahkan, ubah tombol "Add" menjadi teks "Added"
                                    if (addedItems.includes(item.id)) {
                                        addButton = '<span class="text-success">Added</span>';
                                    } else if (item.status !== 'Diproses' && item.status !==
                                        'Menunggu Diproses') {
                                        addButton = '';
                                    }

                                    var row = `
                            <tr class="text-center">
                                <th scope="row">${index + 1}</th>
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
                                '<tr class="text-center"><td colspan="10">No items available.</td></tr>'
                            );
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                        $('#newPurchaseRequestNumber-validation').text(
                            'Failed to load items for the selected purchase request.');
                    }
                });
            }

            $(document).ready(function() {
                // Menggunakan event delegation untuk menangani event pada elemen yang di-generate secara dinamis
                $(document).on('click', '.add-item', function() {
                    var itemID = $(this).data('item-id');
                    var itemCode = $(this).data('item-code');
                    var itemName = $(this).data('item-name');
                    var itemQuantity = $(this).data('item-quantity');
                    var itemUnit = $(this).data('item-unit');
                    var itemPRno = $(this).data('item-prno');
                    // Tambahkan item ke Purchase Order
                    addItemToInventoryTransfer(itemID, itemCode, itemName, itemQuantity, itemUnit,
                        itemPRno);
                    // Ubah tombol menjadi 'Added' setelah item ditambahkan
                    $(this).replaceWith('<span class="text-success">Added</span>');
                });
            });

            function addItemToInventoryTransfer(itemID, itemCode, itemName, itemQuantity, itemUnit, itemPRno) {
                // Cek apakah itemID sudah ada di addedItems
                if (!addedItems.includes(itemID)) {
                    // Tambahkan item ke dalam array addedItems
                    addedItems.push(itemID);
                    localStorage.setItem('addedItems', JSON.stringify(
                        addedItems)); // Simpan addedItems ke localStorage

                    // Tambahkan item ke modal Add Purchase Orders
                    var tableBody = $('#addInventoryTransferModal #temporaryItem tbody');
                    var rowCount = tableBody.find('tr').length;

                    var row =
                        `<tr data-id_prd="${itemID}">
                    <td>${rowCount + 1}</td>
                    <td>${itemCode}</td>
                    <td>${itemName}</td>
                    <td>${itemQuantity}</td>
                    <td>${itemUnit}</td>
                    <td>
                        <select required name="condition[]" class="form-control text-center">
                            <option value="" selected disabled>-- Select condition --</option>
                            <option value="Baru">Baru</option>
                            <option value="Bekas Bisa Pakai">Bekas Bisa Pakai</option>
                            <option value="Bekas Tidak Bisa Pakai">Bekas Tidak Bisa Pakai</option>
                            <option value="Rekondisi">Rekondisi</option>
                        </select>
                    </td>
                    <td>${itemPRno}</td>
                    <td><input type="number" name="koli[]" class="form-control text-center" required value="1" min="1"></td>
                    <td><button type="button" class="btn btn-danger remove-row">Remove</button></td>
                    <input type="hidden" hidden name="purchase_request_item_id[]" class="form-control" value="${itemID}" required>
                </tr>`;

                    tableBody.append(row);

                    // Tambahkan fungsi remove untuk baris yang baru ditambahkan
                    $('.remove-row').click(function() {
                        var id_prd = $(this).closest('tr').data('id_prd');

                        // Menghapus item dari array addedItems menggunakan splice
                        var index = addedItems.indexOf(id_prd);
                        if (index > -1) {
                            addedItems.splice(index, 1);
                        }

                        // Update localStorage
                        localStorage.setItem('addedItems', JSON.stringify(addedItems));

                        // Menghapus baris dari tabel
                        $(this).closest('tr').remove();

                        console.log('Item removed. Current addedItems:', addedItems);
                    });
                } else {
                    alert('Item already added!');
                }
            }


            // Reset the added items array when the modal is closed
            $('#addInventoryTransferModal').on('hidden.bs.modal', function() {
                $('#temporaryItem tbody').empty();
                $('#newDeliveryOrderNumber-validation-message').text('');
                addedItems = [];
                localStorage.removeItem('addedItems');
            });

            $(window).on('beforeunload', function() {
                addedItems = []; // Reset added items when the page is reloaded or closed
                localStorage.removeItem('addedItems'); // Clear addedItems from localStorage on page unload
            });
        });

        $('#selectPurchaseRequestsModal').on('hidden.bs.modal', function(e) {
            $('#addInventoryTransferModal').removeClass('modal-backdrop');
            if ($('.modal.show').length) {
                $('body').addClass('modal-open');
            }
        });
    </script>

    {{-- Detail Inventory Transfer Modal --}}
    <script>
        $(document).ready(function() {
            $('#detailInventoryTransferModal').on('show.bs.modal', function(e) {
                var activePill = $('#v-pills-tab .nav-link.active');
                var shipData = activePill.data('ship');
                $('#detailInventoryTransferModal #shipID').val(shipData.ship_name);

                var button = $(e.relatedTarget); // Button that triggered the modal
                var itemData = button.data('inventorytransfer'); // Extract info from data-* attributes

                if (itemData) {
                    $('#detailInventoryTransferModal #inventoryTransferID').val(itemData.id);
                    $('#detailInventoryTransferModal #deliveryOrderNumber').val(itemData
                        .delivery_order_number);
                    $('#detailInventoryTransferModal #shippingMethod').val(itemData.shipping_method);
                    $('#detailInventoryTransferModal #senderUp').val(itemData.sender_up);
                    $('#detailInventoryTransferModal #senderContact').val(itemData.sender_contact);
                    $('#detailInventoryTransferModal #senderTitle').val(itemData.sender_title);
                    $('#detailInventoryTransferModal #recipientName').val(itemData.recipient_name);
                    $('#detailInventoryTransferModal #recipientProjectPosition').val(itemData
                        .recipient_project_position);
                    $('#detailInventoryTransferModal #recipientTitle').val(itemData.recipient_title);
                    $('#detailInventoryTransferModal #recipientUp').val(itemData.recipient_up);
                    $('#detailInventoryTransferModal #sendDate').val(itemData.send_date);
                    $('#detailInventoryTransferModal #receivedDate').val(itemData.received_date);
                    $('#detailInventoryTransferModal').find('a[id="print-delivery-receipt"]').attr('href',
                        '/print-deliveryReceipts/' + itemData.id);
                    $('#detailInventoryTransferModal').find('a[id="print-delivery-order"]').attr('href',
                        '/print-deliveryOrders/' + itemData.id);
                    console.log(itemData.status);
                    if (itemData.status === "Dikirim Kantor") {
                        var today = new Date().toISOString().split('T')[0];
                        $('#detailInventoryTransferModal #receivedDate').val(today);
                        $('#detailInventoryTransferModal #receivedDate').removeAttr(
                            'readonly'); // Menghapus readonly jika status "Dikirim Kantor"
                    } else {
                        $('#detailInventoryTransferModal #receivedDate').val(itemData.received_date);
                        $('#detailInventoryTransferModal #receivedDate').attr('readonly',
                            true); // Set readonly jika bukan "Dikirim Kantor"
                    }

                    // Update photos modal for sender photos
                    var senderPhotosButton = $('#viewSenderPhotos');
                    senderPhotosButton.data('photos', itemData.sender_photos);
                    senderPhotosButton.data('type', 'sender');

                    // Update photos modal for recipient photos
                    var recipientPhotosButton = $('#viewRecipientPhotos');
                    recipientPhotosButton.data('photos', itemData.recipient_photos);
                    recipientPhotosButton.data('type', 'recipient');

                    // Event listener for viewing sender photos
                    senderPhotosButton.off('click').on('click', function() {
                        showPhotosModal($(this).data('photos'), 'sender');
                    });

                    // Event listener for viewing recipient photos
                    recipientPhotosButton.off('click').on('click', function() {
                        showPhotosModal($(this).data('photos'), 'recipient');
                    });

                    // Check the status and hide the upload section if necessary
                    if (itemData.status === 'Diterima Kapal') {
                        $('.upload-section').hide();
                    } else {
                        $('.upload-section').show();
                    }

                    if (itemData.status === 'Dikirim Kantor') {
                        $('.view-section').hide();
                    } else {
                        $('.view-section').show();
                    }

                    // Clear the previous items in the accordion
                    $('#detailInventoryTransferModal #accordionItems').empty();

                    // Perform AJAX request to fetch items
                    $.ajax({
                        url: '/get-inventoryTransferItems/' + itemData.id,
                        method: 'GET',
                        success: function(response) {
                            // Check if items are available
                            if (response.items && response.items.length > 0) {
                                // Group items by Koli
                                var groupedItems = response.items.reduce(function(acc, item) {
                                    if (!acc[item.koli]) {
                                        acc[item.koli] = [];
                                    }
                                    acc[item.koli].push(item);
                                    return acc;
                                }, {});

                                // Iterate over each Koli group and create an accordion section
                                for (var koli in groupedItems) {
                                    var items = groupedItems[koli];

                                    // Sanitize the `koli` value to remove spaces and other special characters
                                    var sanitizedKoli = koli.replace(/\s+/g,
                                        '-'); // Replaces spaces with hyphens

                                    var accordionHtml = `
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="heading${sanitizedKoli}">
                                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse${sanitizedKoli}" aria-expanded="true" aria-controls="collapse${sanitizedKoli}">
                                                    Koli ${koli}
                                                </button>
                                            </h2>
                                            <div id="collapse${sanitizedKoli}" class="accordion-collapse collapse" aria-labelledby="heading${sanitizedKoli}">
                                                <div class="accordion-body">
                                                    <table class="display expandable-table text-nowrap" style="min-width: 100%">
                                                        <thead>
                                                            <tr class="text-center">
                                                                <th scope="col">No</th>
                                                                <th scope="col">PMS Code</th>
                                                                <th scope="col">Item Name</th>
                                                                <th scope="col">Quantity</th>
                                                                <th scope="col">Unit</th>
                                                                <th scope="col">Condition</th>
                                                                <th scope="col">PR No.</th>
                                                                <th scope="col">Koli</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>`;
                                    // Append rows for each item in the Koli group
                                    items.forEach(function(item, index) {
                                        console.log(item);
                                        var rowClass = '';
                                        // If total_quantity is less than a certain threshold, add a class for red background
                                        if (item.total_quantity < item
                                            .minimum_quantity) {
                                            rowClass =
                                                'bg-light-red'; // Change this to 'bg-danger' if using Bootstrap's built-in class
                                        }

                                        accordionHtml += `
                                        <tr class="${rowClass} text-center">
                                            <th scope="row">${index + 1}</th>
                                            <td>${item.item_pms}</td>
                                            <td>${item.item_name}</td>
                                            <td>${item.quantity}</td>
                                            <td>${item.item_unit}</td>
                                            <td>${item.condition}</td>
                                            <td>
                                                <a href="#" class="pr-link" data-pr-number="${item.purchase_request_number}" data-ship-id=${item.ship_id}>
                                                    ${item.purchase_request_number}
                                                </a>
                                            </td>
                                            <td>${item.koli}</td>
                                        </tr>`;
                                    });

                                    accordionHtml += `
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>`;

                                    // Append the accordion section to the modal content
                                    $('#detailInventoryTransferModal #accordionItems').append(
                                        accordionHtml);
                                }
                            } else {
                                $('#detailInventoryTransferModal #accordionItems').append(
                                    '<div class="card"><div class="card-header">No items available.</div></div>'
                                );
                            }
                        },
                        error: function() {
                            console.log('Error loading data');
                        }
                    });
                } else {
                    console.log('No item data found');
                }
                // Ctrl + Click event for Purchase Request Number in the purchase order modal
                $(document).on('click', '.pr-link', function(e) {
                    var prNumber = $(this).data('pr-number');
                    var shipId = $(this).data('ship-id'); // Get the ship_id
                    localStorage.setItem('prNumber', prNumber);
                    localStorage.setItem('shipId', shipId);
                    window.open('/purchaseRequests', '_blank');
                    // Check if Ctrl key is pressed
                    if (e.ctrlKey) {
                        e.preventDefault(); // Prevent the default anchor tag behavior
                        // Open the purchaseRequests page in a new tab
                        window.open('/purchaseRequests', '_blank');
                    }
                });
            });
        });

        // Function to display photos in the modal
        function showPhotosModal(photos, type) {
            var modalBody = $('#photosModal #modalBodyContent');
            var basePath = type === 'sender' ? 'images/uploads/inventoryTransfers-photos/sender/' :
                'images/uploads/inventoryTransfers-photos/recipient/';

            // Decode JSON string to an array if necessary
            if (typeof photos === 'string') {
                try {
                    photos = JSON.parse(photos); // Decode JSON string to array
                } catch (e) {
                    console.error('Error parsing JSON:', e);
                    photos = []; // Fallback to empty array on error
                }
            } else if (!Array.isArray(photos)) {
                photos = []; // Handle non-array types
            }
            modalBody.empty(); // Clear the modal content before populating
            if (photos && photos.length > 0) {
                photos.forEach(function(filename) {
                    var imagePath = basePath + filename;
                    var imgElement = `
                        <div class="mb-3">
                            <img src="${imagePath}" class="img-fluid fixed-size-image">
                        </div>
                        `;
                    modalBody.append(imgElement);
                });
            } else {
                modalBody.append('<p class="text-muted">No photos available.</p>');
            }
        }
    </script>

    {{-- Add backdrop when Photos Modal show --}}
    <script>
        $('#photosModal').on('show.bs.modal', function(e) {
            $('#detailInventoryTransferModal').addClass('modal-backdrop');
        });
        $('#photosModal').on('hidden.bs.modal', function(e) {
            $('#detailInventoryTransferModal').removeClass('modal-backdrop');
            if ($('.modal.show').length) {
                $('body').addClass('modal-open');
            }
        });
    </script>
@endsection
</div>
