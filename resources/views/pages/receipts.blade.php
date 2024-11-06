@extends('layouts.layout')
<title>Receipt</title>
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
                    <div class="card-body">
                        <p class="card-title mb-2">Receipts Data</p>
                        <div class="input-group mb-3">
                            <input id="searchReceipt" type="text" class="form-control" placeholder="Search...">
                            <div class="input-group-append">
                                <button class="btn btn-sm btn-primary" type="button">Search</button>
                            </div>
                        </div>
                        @if ((Auth::user() && Auth::user()->role === 'Super Admin') || Auth::user()->role === 'Purchasing Logistic Admin')
                            <div class="input-group mb-3">
                                <button type="button" class="btn btn-success btn-icon-text" data-toggle="modal"
                                    data-target="#addReceiptModal">
                                    <i class="mdi mdi-database-plus pr-2"></i>Add Receipt
                                </button>
                            </div>
                        @endif
                        <div class="row">
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table class="display expandable-table table-striped" style="width:100%;"
                                        data-toggle="table" data-sortable="true">
                                        <thead>
                                            <tr class="text-center">
                                                <th data-sortable="true">No#</th>
                                                <th data-sortable="true">Receipt No.</th>
                                                <th data-sortable="true">Received Date</th>
                                                <th data-sortable="true">Received By</th>
                                                <th data-sortable="true">Item Count</th>
                                                <th class="col-1">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="dataReceipts">
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
        <!-- Add Receipt Items Modal -->
        <div class="modal fade" id="addReceiptModal" tabindex="-1" role="dialog" aria-labelledby="addReceiptModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-custom" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addReceiptModalLabel">Add Receipt</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="addReceiptsForm" method="POST" action="{{ url('addReceipts') }}"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-8 col-sm-6">
                                        <label for="newReceiptNumber"><span class="text-danger">*</span>No.
                                            Receipt</label>
                                        <input readonly required name="receipt_number" type="text" class="form-control"
                                            id="newReceiptNumber" value="{{ $newCode }}">
                                        <div id="newReceiptNumber-validation" class="text-danger"></div>
                                    </div>
                                    <div class="col-8 col-sm-6">
                                        <label for="newReceivedBy"><span class="text-danger">*</span>Received By</label>
                                        <input required name="received_by" type="text" class="form-control"
                                            id="newReceivedBy" placeholder="Enter recipient's name">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-8 col-sm-6">
                                        <label for="newSupplierID"><span class="text-danger">*</span>Supplier</label>
                                        <input readonly required name="supplier_id_display" type="text"
                                            class="form-control" id="newSupplierID">
                                    </div>
                                    <div class="col-8 col-sm-6">
                                        <label for="newReceivedDate"><span class="text-danger">*</span>Received
                                            Date</label>
                                        <input required name="received_date" type="date" class="form-control"
                                            id="newReceivedDate">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-8 col-sm-6">
                                        <a class="btn btn-success" href="#" data-toggle="modal"
                                            data-target="#selectSupplierModal">
                                            <i class="fa-solid fa-list"></i> Select Items
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
                                                <th style="width: 100px">Quantity</th>
                                                <th style="width: 100px">Received Quantity</th>
                                                <th>Unit</th>
                                                <th>Condition</th>
                                                <th>PO No.</th>
                                                <th>Serial Number</th>
                                                <th>Action</th>
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
                        <div id="purchaseOrderItemsContainer"></div> <!-- Hidden inputs for purchase_order_item_ids -->
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" id="submitButton" class="btn btn-success">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Select Items Modal -->
        <div class="modal fade" id="selectSupplierModal" tabindex="-1" role="dialog"
            aria-labelledby="selectSupplierModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="selectSupplierModalLabel">Select Items</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <div class="row">
                                <div class="col-8 col-sm-6">
                                    <label for="newSupplierID"><span class="text-danger">*</span>Supplier Name</label>
                                    <div class="supplier-dropdown">
                                        <input type="text" class="form-control" autocomplete="off"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                            id="newSupplierID">
                                        <input required type="hidden" id="newSupplierID" name="supplier_id">
                                        <div id="supplier-list" class="dropdown-menu" aria-labelledby="newSupplierID">
                                            <!-- Item list will be appended here -->
                                        </div>
                                    </div>
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
                                            <th>PO No.</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Items will be appended here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Receipt Modal -->
        <div class="modal fade" id="detailReceiptModal" tabindex="-1" role="dialog"
            aria-labelledby="detailReceiptModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-custom" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="detailReceiptModalLabel">Detail Receipt</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="" method="POST" action="{{ url('') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-8 col-sm-6">
                                        <label for="receiptNumber">No. Receipt</label>
                                        <input type="hidden" name="receipt_id" id="receiptID">
                                        <input readonly name="receipt_number" type="text" class="form-control"
                                            id="receiptNumber">
                                    </div>
                                    <div class="col-8 col-sm-6">
                                        <label for="receivedBy">Received By</label>
                                        <input readonly required name="received_by" type="text" class="form-control"
                                            id="receivedBy">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-8 col-sm-6">
                                        <label for="supplier">Supplier</label>
                                        <input readonly name="supplier" type="text" class="form-control"
                                            id="supplier">
                                    </div>
                                    <div class="col-8 col-sm-6">
                                        <label for="receivedDate">Received Date</label>
                                        <input readonly required name="received_date" type="date" class="form-control"
                                            id="receivedDate">
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
                                                <th scope="col">Received Quantity</th>
                                                <th scope="col">Unit</th>
                                                <th scope="col">Condition</th>
                                                <th scope="col">PO No.</th>
                                                <th scope="col">Serial Number</th>
                                                <th scope="col">Supplier</th>
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
                    </form>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    @endsection
    @section('script')
        {{-- Load Search Receipt --}}
        <script>
            $(document).ready(function() {
                function formatDate(dateString) {
                    var date = new Date(dateString);
                    var day = date.getDate();
                    var month = date.getMonth() + 1; // Bulan dimulai dari 0
                    var year = date.getFullYear();
                    // Menambahkan leading zero jika diperlukan
                    return (day < 10 ? '0' + day : day) + '/' + (month < 10 ? '0' + month : month) + '/' + year;
                }
                // Menyimpan data kapal yang sudah dilempar dari server ke variabel JavaScript
                var receiptData = @json($receiptData);
                // Fungsi untuk menampilkan semua data kapal
                function renderAllReceipt() {
                    var tableBody = $('#dataReceipts');
                    tableBody.empty(); // Kosongkan tabel

                    if (receiptData.length > 0) {
                        var no = 1;
                        receiptData.forEach(function(receipt) {
                            var row = `<tr class="text-center">
            <th class="align-middle">${no++}</th>
            <td>${receipt.receipt_number}</td>
            <td>${formatDate(receipt.received_date)}</td>
            <td>${receipt.received_by}</td>
            <td>${receipt.item_count}</td>
            <td>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#detailReceiptModal" data-receipt='${JSON.stringify(receipt)}'>
                        <i class="mdi mdi-eye"></i>
                    </button>
                    @if ((Auth::user() && Auth::user()->role === 'Super Admin') || Auth::user()->role === 'Director')
                    <a href="/deleteReceipt/${receipt.id}" class="btn btn-outline-danger btn-delete">
                        <i class="mdi mdi-delete"></i>
                    </a>
                    @endif
                </div>
            </td>
          </tr>`;
                            tableBody.append(row);
                        });
                    }
                }

                // Tampilkan data semua kapal saat halaman dimuat pertama kali
                renderAllReceipt();

                $('#searchReceipt').on('keyup', function() {
                    var search = $(this).val();
                    var tableBody = $('#dataReceipts');

                    // Jika input kosong, tampilkan kembali semua data kapal
                    if (search.length === 0) {
                        renderAllReceipt();
                    } else {
                        // Tampilkan pesan "Searching for data" jika ada input pencarian
                        tableBody.html(
                            '<tr class="text-center"><td colspan="12" class="text-center text-muted">Searching for data...</td></tr>'
                        );

                        $.ajax({
                            url: "{{ url('findReceipts') }}",
                            method: 'GET',
                            data: {
                                search: search
                            },
                            success: function(response) {
                                tableBody.empty(); // Kosongkan tabel
                                if (response.length > 0) {
                                    var no = 1;
                                    response.forEach(function(receipt) {
                                        var row = `<tr class="text-center">
                                        <th class="align-middle">${no++}</th>
                                        <td>${receipt.receipt_number}</td>
                                        <td>${formatDate(receipt.received_date)}</td>
                                        <td>${receipt.received_by}</td>
                                        <td>${receipt.item_count}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#detailReceiptModal" data-receipt='${JSON.stringify(receipt)}'>
                                                    <i class="mdi mdi-eye"></i>
                                                </button>
                                                @if ((Auth::user() && Auth::user()->role === 'Super Admin') || Auth::user()->role === 'Director')
                                                <a href="/deleteReceipt/${receipt.id}" class="btn btn-outline-danger btn-delete">
                                                    <i class="mdi mdi-delete"></i>
                                                </a>
                                                @endif
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

                            error: function() {
                                tableBody.html(
                                    '<tr class="text-center"><td colspan="12" class="text-center text-danger">Error while searching for receipts</td></tr>'
                                );
                            }
                        });
                    }
                });
            });
        </script>

        {{-- Add Receipt Modal --}}
        <script>
            $(document).ready(function() {
                $('#addReceiptModal').on('show.bs.modal', function(e) {
                    var today = new Date().toISOString().split('T')[0];
                    $('#addReceiptModal #newReceivedDate').val(today);
                });
            });
        </script>

        {{-- Select Suppliers Modal --}}
        <script>
            $(document).ready(function() {
                var addedReceiptItems = JSON.parse(localStorage.getItem('addedReceiptItems')) || [];
                var addedReceiptServices = JSON.parse(localStorage.getItem('addedReceiptServices')) || [];
                var serviceSeparatorAdded = false;
                var selectedSuppliers = [];
                $('#selectSupplierModal').on('show.bs.modal', function(e) {
                    $('#addReceiptModal').addClass('modal-backdrop');
                    $.ajax({
                        type: "get",
                        url: "{{ url('get-supplier') }}",
                        success: function(response) {
                            var supplierList = $('#supplier-list');
                            supplierList.empty();
                            $.each(response, function(index, supplier) {
                                var newLink = $('<a></a>')
                                    .addClass('dropdown-item')
                                    .attr('href', '#')
                                    .attr('data-supplier-id', supplier.id)
                                    .text(supplier.supplier_name);
                                supplierList.append(newLink);
                            });
                        },
                    });
                });
                $('#selectSupplierModal #newSupplierID').on('input', function() {
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
                $('#supplier-list').on('click', '.dropdown-item', function(e) {
                    e.preventDefault();
                    var supplierID = $(this).data('supplier-id');
                    var supplierName = $(this).text();
                    $('#selectSupplierModal #newSupplierID').val(supplierName);
                    loadSupplierItems(supplierID, supplierName);
                });

                function loadSupplierItems(supplierID, supplierName) {
                    $.ajax({
                        url: '/get-itemSuppliers/' + supplierID,
                        type: 'GET',
                        success: function(response) {
                            console.log(response);
                            var tableBody = $('#selectSupplierModal #temporaryItem tbody');
                            tableBody.empty();

                            // Menampilkan items
                            if (response.items && response.items.length > 0) {
                                response.items.forEach(function(item, index) {
                                    var addButton = `
                                        <button type="button" class="btn btn-success add-receipt" 
                                            data-item-id="${item.id}" 
                                            data-item-code="${item.item_pms}" 
                                            data-item-name="${item.item_name}" 
                                            data-item-quantity="${item.status === 'Belum Selesai' ? item.remaining_quantity : item.quantity}" 
                                            data-item-unit="${item.item_unit}"
                                            data-item-condition="${item.condition}"
                                            data-item-supplier-id="${supplierID}"
                                            data-item-supplier-name="${supplierName}"
                                            data-item-purchase-order-number="${item.purchase_order_number}">
                                            Add
                                        </button>`;
                                    // Cek apakah item sudah ditambahkan ke localStorage
                                    if (addedReceiptItems.some(addedItem => addedItem.id === item
                                            .id)) {
                                        addButton = '<span class="text-success">Added</span>';
                                    }

                                    var row = `
                                    <tr class="text-center">
                                        <th>${index + 1}</th>
                                        <td>${item.item_pms}</td>
                                        <td>${item.item_name}</td>
                                        <td>${item.status === 'Belum Selesai' ? item.remaining_quantity : item.quantity}</td>
                                        <td>${item.item_unit}</td>
                                        <td>${item.condition}</td>
                                        <td>${item.purchase_order_number}</td>
                                        <td>${addButton}</td>
                                    </tr>`;
                                    tableBody.append(row);
                                });
                            }

                            // Menampilkan services
                            if (response.services && response.services.length > 0) {
                                var serviceSeparator = `
                                <tr class="text-center">
                                    <td colspan="8"><strong>Services (Jasa)</strong></td>
                                </tr>`;
                                tableBody.append(serviceSeparator);

                                response.services.forEach(function(service, index) {
                                    var serviceButton = `
                                        <button type="button" class="btn btn-success add-receipt-service" 
                                            data-service-id="${service.id}" 
                                            data-service-code="${service.service_code}" 
                                            data-service-name="${service.service_name}" 
                                            data-service-supplier-id="${supplierID}"
                                            data-service-supplier-name="${supplierName}"
                                            data-service-utility="${service.utility}"
                                            data-service-purchase-order-number="${service.purchase_order_number}">
                                            Add
                                        </button>`;

                                    // Cek apakah service sudah ditambahkan ke localStorage
                                    if (addedReceiptServices.some(addedService => addedService
                                            .id === service.id)) {
                                        serviceButton = '<span class="text-success">Added</span>';
                                    }

                                    var serviceRow = `
                                    <tr class="text-center">
                                        <th>Jasa</th>
                                        <td>${service.service_code}</td>
                                        <td>${service.service_name}</td>
                                        <td>1</td>
                                        <td></td>
                                        <td></td>
                                        <td>${service.purchase_order_number}</td>
                                        <td>${serviceButton}</td>
                                    </tr>`;
                                    tableBody.append(serviceRow);
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error(error);
                            $('#newPurchaseRequestNumber-validation').text(
                                'Failed to load items and services for the selected supplier.');
                        }
                    });
                }

                function renderTable() {
                    $('#addReceiptModal #temporaryItem tbody').empty(); // Bersihkan tabel item sebelum di-render ulang
                    serviceSeparatorAdded = false; // Reset status separator sebelum mulai render
                    addedReceiptItems.forEach(function(item, index) {
                        var rowHTML = `
                            <tr class="text-center">
                                <th>${index + 1}</th>
                                <td>${item.code}</td>
                                <td>${item.name}</td>
                                <td><input readonly type="number" name="quantity[]" class="form-control text-center" value="${item.status === 'Belum Selesai' ? item.remaining_quantity : item.quantity}" min="1" required></td>
                                <td><input type="number" name="received_quantity[]" class="form-control text-center" value="${item.status === 'Belum Selesai' ? item.remaining_quantity : item.quantity}" min="1" required></td>
                                <td>${item.unit}</td>
                                <td>${item.condition}</td>
                                <td>${item.purchaseOrderNumber}</td>
                                <td><input type="text" name="serial_number[]" class="form-control text-center" required></td>
                                <td><button type="button" class="btn btn-danger remove-row" data-index="${index}" data-type="item">Remove</button></td>
                                <input type="hidden" name="item_id[]" value="${item.id}">
                            </tr>
                        `;
                        $('#addReceiptModal #temporaryItem tbody').append(rowHTML);
                    });

                    // Jika ada jasa, tambahkan pemisah untuk jasa
                    if (addedReceiptServices.length > 0 && !serviceSeparatorAdded) {
                        var separator = `
                        <tr class="text-center jasa-separator">
                            <td colspan="10"><strong>Services (Jasa)</strong></td>
                        </tr>`;
                        $('#addReceiptModal #temporaryItem tbody').append(separator);
                        serviceSeparatorAdded = true;
                    } else {}

                    // Render jasa setelah pemisah
                    addedReceiptServices.forEach(function(jasa, index) {
                        var jasaRowHTML = `
                        <tr class="text-center">
                            <th>Jasa</th>
                            <td>${jasa.code}</td>
                            <td>${jasa.name}</td>
                            <td>1</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>${jasa.purchaseOrderNumber}</td>
                            <td>${jasa.utility}</td>
                            <td><button type="button" class="btn btn-danger remove-row" data-index="${index}" data-type="service">Remove</button></td>
                            <input type="hidden" name="service_id[]" value="${jasa.id}">
                        </tr>
                    `;
                        $('#addReceiptModal #temporaryItem tbody').append(jasaRowHTML);
                    });
                }

                function addItemToStorage(itemID, itemCode, itemName, itemQuantity, itemUnit, itemCondition,
                    purchaseOrderNumber, utility = null, isService = false, supplierName
                ) {
                    if (isService) {
                        addedReceiptServices.push({
                            id: itemID,
                            code: itemCode,
                            name: itemName,
                            purchaseOrderNumber: purchaseOrderNumber,
                            utility: utility,
                            supplierName: supplierName // Simpan nama supplier di service
                        });
                        localStorage.setItem('addedReceiptServices', JSON.stringify(addedReceiptServices));
                    } else {
                        var itemExist = addedReceiptItems.some(function(item) {
                            return item.id === itemID;
                        });
                        if (!itemExist) {
                            addedReceiptItems.push({
                                id: itemID,
                                code: itemCode,
                                name: itemName,
                                quantity: itemQuantity,
                                unit: itemUnit,
                                condition: itemCondition,
                                purchaseOrderNumber: purchaseOrderNumber,
                                supplierName: supplierName // Simpan nama supplier di item
                            });
                            localStorage.setItem('addedReceiptItems', JSON.stringify(addedReceiptItems));
                        } else {
                            Swal.fire({
                                title: 'Item already Added!',
                                icon: 'warning',
                            });
                            return;
                        }
                    }

                    // Tambahkan supplier ke selectedSuppliers jika belum ada
                    if (!selectedSuppliers.includes(supplierName)) {
                        selectedSuppliers.push(supplierName);
                    }

                    updateSupplierInput(); // Perbarui input supplier
                    renderTable(); // Render ulang tabel setelah item atau jasa ditambahkan
                }


                function updateSupplierInput() {
                    $('#addReceiptModal #newSupplierID').val(selectedSuppliers.join(', '));
                }

                $(document).on('click', '.add-receipt', function() {
                    var itemID = $(this).data('item-id');
                    var itemCode = $(this).data('item-code');
                    var itemName = $(this).data('item-name');
                    var itemQuantity = $(this).data('item-quantity');
                    var itemUnit = $(this).data('item-unit');
                    var itemCondition = $(this).data('item-condition');
                    var itemPurchaseOrderNumber = $(this).data('item-purchase-order-number');
                    var supplierName = $(this).data('item-supplier-name'); // Dapatkan nama supplier

                    // Tambahkan item ke localStorage dengan supplierName
                    addItemToStorage(itemID, itemCode, itemName, itemQuantity, itemUnit, itemCondition,
                        itemPurchaseOrderNumber, null, false, supplierName);

                    // Ubah tombol menjadi 'Added' setelah item ditambahkan
                    $(this).replaceWith('<span class="text-success">Added</span>');
                });

                $(document).on('click', '.add-receipt-service', function() {
                    var serviceID = $(this).data('service-id');
                    var serviceCode = $(this).data('service-code');
                    var serviceName = $(this).data('service-name');
                    var servicePurchaseOrderNumber = $(this).data('service-purchase-order-number');
                    var serviceUtility = $(this).data('service-utility');
                    var supplierName = $(this).data('service-supplier-name'); // Dapatkan nama supplier

                    // Tambahkan service ke localStorage dengan supplierName
                    addItemToStorage(serviceID, serviceCode, serviceName, '', '', '',
                        servicePurchaseOrderNumber, serviceUtility, true, supplierName);

                    // Ubah tombol menjadi 'Added' setelah service ditambahkan
                    $(this).replaceWith('<span class="text-success">Added</span>');
                });
                renderTable();

                $(document).on('click', '.remove-row', function() {
                    var index = $(this).data('index'); // Ambil index baris yang akan dihapus
                    var type = $(this).data('type'); // Ambil tipe data (item atau service)
                    var removedSupplierName = ''; // Pastikan variabel diinisialisasi

                    if (type === 'item') {
                        removedSupplierName = addedReceiptItems[index]
                            .supplierName; // Dapatkan nama supplier dari item
                        addedReceiptItems.splice(index, 1); // Hapus item dari array addedReceiptItems
                        localStorage.setItem('addedReceiptItems', JSON.stringify(
                            addedReceiptItems)); // Update localStorage
                    } else if (type === 'service') {
                        removedSupplierName = addedReceiptServices[index]
                            .supplierName; // Dapatkan nama supplier dari service
                        addedReceiptServices.splice(index, 1); // Hapus service dari array addedReceiptServices
                        localStorage.setItem('addedReceiptServices', JSON.stringify(
                            addedReceiptServices)); // Update localStorage
                    }

                    // Cek apakah masih ada item/service lain dari supplier tersebut
                    var supplierStillHasItems = addedReceiptItems.some(item => item.supplierName ===
                        removedSupplierName);
                    var supplierStillHasServices = addedReceiptServices.some(service => service.supplierName ===
                        removedSupplierName);

                    if (!supplierStillHasItems && !supplierStillHasServices) {
                        // Jika tidak ada item/service lain dari supplier tersebut, hapus dari selectedSuppliers
                        selectedSuppliers = selectedSuppliers.filter(supplier => supplier !==
                            removedSupplierName);
                    }

                    updateSupplierInput(); // Perbarui input supplier
                    renderTable(); // Render ulang tabel untuk merefleksikan perubahan
                });

                $('#selectSupplierModal').on('hidden.bs.modal', function(e) {
                    $('#selectSupplierModal #temporaryItem tbody')
                        .empty(); // Kosongkan tabel sebelum modal ditutup
                    $('#addReceiptModal').removeClass('modal-backdrop');
                    if ($('.modal.show').length) {
                        $('body').addClass('modal-open');
                    }
                });
                $('#addReceiptModal').on('hidden.bs.modal', function() {
                    $('#temporaryItem tbody').empty();
                    addedReceiptItems = [];
                    addedReceiptServices = [];
                    selectedSuppliers = [];
                    localStorage.removeItem('addedReceiptItems');
                    localStorage.removeItem('addedReceiptServices');
                    $('#addReceiptModal #newSupplierID').val('');
                    $('#purchaseOrderItemsContainer').empty();
                });
                $(window).on('beforeunload', function() {
                    addedReceiptItems = [];
                    addedReceiptServices = [];
                    localStorage.removeItem('addedReceiptItems');
                    localStorage.removeItem('addedReceiptServices');
                });
                $('#selectSupplierModal').on('hidden.bs.modal', function(e) {
                    $('#addReceiptModal').removeClass('modal-backdrop');
                    $('#selectSupplierModal #newSupplierID').val('');
                });
            });
        </script>

        {{-- Detail Receipt Modal --}}
        <script>
            $(document).ready(function() {
                // Detail Receipt Modal
                $('#detailReceiptModal').on('show.bs.modal', function(e) {
                    var button = $(e.relatedTarget); // Button that triggered the modal
                    var itemData = button.data('receipt'); // Extract info from data-* attributes

                    if (itemData) {
                        $('#detailReceiptModal #receiptNumber').val(itemData.receipt_number);
                        $('#detailReceiptModal #receivedBy').val(itemData.received_by);
                        $('#detailReceiptModal #receivedDate').val(itemData.received_date);

                        // Clear the previous items in the table
                        $('#temporaryItem tbody').empty();

                        // Perform AJAX request to fetch items and services
                        $.ajax({
                            url: '/get-receiptItems/' + itemData.id,
                            method: 'GET',
                            success: function(response) {
                                console.log(response);
                                if (response.receipt) {
                                    $('#receiptNumber').val(response.receipt.receipt_number);
                                    $('#receivedBy').val(response.receipt.received_by);
                                    let suppliers = new Set();
                                    $('#detailReceiptModal tbody').empty();

                                    // Loop through items and add suppliers
                                    $.each(response.items, function(index, item) {

                                        suppliers.add(item.supplier_name);
                                        var row = `<tr class="text-center">
                                                <td>${index + 1}</td>
                                                <td>${item.item_pms}</td>
                                                <td>${item.item_name}</td>
                                                <td>${item.quantity}</td>
                                                <td>${item.received_quantity}</td>
                                                <td>${item.item_unit}</td>
                                                <td>${item.condition}</td>
                                                <td>
                                                    <a href="#" class="po-link" data-po-number="${item.purchase_order_number}">
                                                        ${item.purchase_order_number}
                                                    </a>
                                                </td>
                                                <td>${item.serial_number}</td>
                                                <td>${item.supplier_name}</td>
                                            </tr>`;
                                        $('#detailReceiptModal tbody').append(row);
                                    });

                                    // Check and add services
                                    if (response.services && response.services.length > 0) {
                                        console.log(response.services);

                                        // Add separator for Services
                                        $('#detailReceiptModal tbody').append(
                                            '<tr class="separator-row"><td colspan="10" class="text-center font-weight-bold">Services</td></tr>'
                                        );

                                        // Loop through services and add suppliers
                                        $.each(response.services, function(index, service) {
                                            console.log(service);
                                            suppliers.add(service
                                                .supplier_name
                                            ); // Add supplier name from services

                                            var serviceRow = `<tr class="text-center service-row">
                                                <th>Jasa</th>
                                                <td>${service.service_code}</td>
                                                <td>${service.service_name}</td>
                                                <td>1</td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td>
                                                    <a href="#" class="po-link" data-po-number="${service.purchase_order_number}">
                                                        ${service.purchase_order_number}
                                                    </a>
                                                </td>
                                                <td>${service.pos_utility}</td>
                                                <td>${service.supplier_name}</td>
                                            </tr>`;
                                            $('#detailReceiptModal tbody').append(
                                                serviceRow);
                                        });
                                    }

                                    // Set the supplier field with all supplier names, joined by a comma
                                    $('#supplier').val(Array.from(suppliers).join(', '));
                                } else {
                                    $('#temporaryItem tbody').append(
                                        '<tr class="text-center"><td colspan="12">No items available.</td></tr>'
                                    );
                                }
                            },
                            error: function() {}
                        });

                    } else {}
                });

                // Ctrl + Click event for PO Number in the receipt modal
                $(document).on('click', '.po-link', function(e) {
                    var poNumber = $(this).data('po-number');
                    localStorage.setItem('poNumber', poNumber);
                    window.open(`/purchaseOrders`, '_blank');
                    // Check if Ctrl key is pressed
                    if (e.ctrlKey) {
                        e.preventDefault(); // Prevent the default anchor tag behavior
                        // Open the new window and store the window object
                        window.open(`/purchaseOrders`, '_blank');
                    }
                });
            });
        </script>

        {{-- Table Sort --}}
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-table/dist/bootstrap-table.min.js"></script>
    @endsection
</div>
