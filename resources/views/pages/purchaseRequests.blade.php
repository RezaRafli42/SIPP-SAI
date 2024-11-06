@extends('layouts.layout')
<title>Purchase Request</title>
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
                                <p class="card-title mb-2">Purchase Requests</p>
                                {{-- <p class="vessel-title">{{ $shipItem->ship_name }}</p> --}}
                                <div class="input-group mb-3">
                                    <input id="searchPurchaseRequests" type="text" class="form-control"
                                        placeholder="Search...">
                                    <div class="input-group-append">
                                        <button class="btn btn-sm btn-primary" type="button">Search</button>
                                    </div>
                                </div>
                                <div class="input-group mb-3">
                                    @if (
                                        (Auth::user() && Auth::user()->role === 'Super Admin') ||
                                            Auth::user()->role === 'Fleet Admin' ||
                                            Auth::user()->role === 'Kapal')
                                        <button type="button" class="btn btn-success btn-icon-text mr-1"
                                            data-toggle="modal" data-target="#addPurchaseRequestsModal">
                                            <i class="mdi mdi-database-plus pr-2"></i>Add Purchase Request
                                        </button>
                                        <button type="button" class="btn btn-primary btn-icon-text mr-1"
                                            data-toggle="modal" data-target="#addAutomaticPurchaseRequestsModal">
                                            <i class="mdi mdi-database-plus pr-2"></i>Automatic Purchase Request
                                        </button>
                                    @endif
                                    @if ((Auth::user() && Auth::user()->role === 'Super Admin') || Auth::user()->role === 'Purchasing Logistic Admin')
                                        <button type="button" class="btn btn-warning btn-icon-text mr-1"
                                            data-toggle="modal" data-target="#exportPurchaseRequestModal" data-ship-id="">
                                            <i class="mdi mdi-import pr-2"></i>Export (.xlsx)
                                        </button>
                                    @endif
                                </div>
                                <div class="row">
                                    <div class="col-12 p-0">
                                        <div class="table-responsive">
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
        <!-- Export Modal -->
        <div class="modal fade" id="exportPurchaseRequestModal" tabindex="-1" role="dialog"
            aria-labelledby="exportPurchaseRequestModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exportPurchaseRequestModalLabel">Export</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body text-center">
                        <form action="{{ route('exportPurchaseRequests') }}" method="GET">
                            <div class="form-group">
                                <label for="shipID">Ship Name</label>
                                <input readonly class="form-control text-center" type="text" name="ship_id"
                                    id="shipID">
                            </div>
                            <div class="form-group">
                                <label for="month">Month:</label>
                                <select name="month" id="month" class="form-control text-center" required>
                                    <option value="1">January</option>
                                    <option value="2">February</option>
                                    <option value="3">March</option>
                                    <option value="4">April</option>
                                    <option value="5">May</option>
                                    <option value="6">June</option>
                                    <option value="7">July</option>
                                    <option value="8">August</option>
                                    <option value="9">September</option>
                                    <option value="10">October</option>
                                    <option value="11">November</option>
                                    <option value="12">December</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="year">Year:</label>
                                <input readonly class="form-control text-center" type="text" name="year"
                                    id="year">
                            </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Export</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Automatic Purchase Request Modal -->
        <div class="modal fade" id="addAutomaticPurchaseRequestsModal" tabindex="-1" role="dialog"
            aria-labelledby="addAutomaticPurchaseRequestsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addAutomaticPurchaseRequestsModalLabel">Add Purchase Request</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="addAutomaticPurchaseRequestsForm" method="POST" action="{{ url('addPurchaseRequests') }}"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-8 col-sm-6">
                                        <label for="newAutomaticPurchaseRequestNumber"><span
                                                class="text-danger">*</span>No.
                                            PR</label>
                                        <div class="input-group mb-3">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-primary btn-icon-text generateBtn"
                                                    id="generateNewAutomaticPurchaseRequestNumber">
                                                    <i class="mdi mdi-refresh pr-2"> Generate</i>
                                                </button>
                                            </div>
                                            <input readonly required name="purchase_request_number" type="text"
                                                class="form-control" id="newAutomaticPurchaseRequestNumber">
                                            <div id="newAutomaticPurchaseRequestNumber-validation" class="text-danger">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-8 col-sm-6">
                                        <label for="newShipID"><span class="text-danger">*</span>Ship Name</label>
                                        <input readonly required name="ship_id" type="text" class="form-control"
                                            id="newShipID">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-8 col-sm-6">
                                        <label for="newDocument">Document</label>
                                        <input name="document[]" type="file" class="form-control" id="newDocument"
                                            aria-describedby="newDocument" accept="image/*" multiple>
                                        <small class="text-muted">You can select up to 3 images.</small>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div id="temporaryItem">
                                    <table class="display expandable-table text-nowrap" style="min-width: 100%">
                                        <thead>
                                            <tr class="text-center">
                                                <th>No</th>
                                                <th>PMS Code</th>
                                                <th>Item Name</th>
                                                <th style="width: 120px"><span class="text-danger">*</span>Quantity</th>
                                                <th>Unit</th>
                                                <th style="width: 200px"><span class="text-danger">*</span>Option</th>
                                                {{-- <th style="width: 220px"><span
                                    class="text-danger">*</span>Condition</th> --}}
                                                <th><span class="text-danger">*</span>Utility</th>
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

        <!-- Add Purchase Request Modal -->
        <div class="modal fade" id="addPurchaseRequestsModal" tabindex="-1" role="dialog"
            aria-labelledby="addPurchaseRequestsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addPurchaseRequestsModalLabel">Add Purchase Request</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="addPurchaseRequestsForm" method="POST" action="{{ url('addPurchaseRequests') }}"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-8 col-sm-6">
                                        <label for="newPurchaseRequestNumber"><span class="text-danger">*</span>No.
                                            PR</label>
                                        <div class="input-group mb-3">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-primary btn-icon-text generateBtn"
                                                    id="generateNewPurchaseRequestNumber">
                                                    <i class="mdi mdi-refresh pr-2"> Generate</i>
                                                </button>
                                            </div>
                                            <input readonly required name="purchase_request_number" type="text"
                                                class="form-control" id="newPurchaseRequestNumber">
                                            <div id="newPurchaseRequestNumber-validation" class="text-danger"></div>
                                        </div>
                                    </div>
                                    <div class="col-8 col-sm-6">
                                        <label for="newShipID"><span class="text-danger">*</span>Ship Name</label>
                                        <input readonly required name="ship_id" type="text" class="form-control"
                                            id="newShipID">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-8 col-sm-6">
                                        <label for="newItemName"><span class="text-danger">*</span>Item Name</label>
                                        <div class="itemName-dropdown">
                                            <input type="text" class="form-control" autocomplete="off"
                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                                id="newItemName">
                                            <input required type="hidden" id="newItemID" name="item_id">
                                            <div id="item-list" class="dropdown-menu" aria-labelledby="newItemName">
                                                <!-- Item list will be appended here -->
                                            </div>
                                            <div id="newItemName-validation" class="text-danger"></div>
                                        </div>
                                    </div>
                                    <div class="col-8 col-sm-6">
                                        <label for="newServiceName">Service Name</label>
                                        <div class="serviceName-dropdown">
                                            <input type="text" class="form-control" autocomplete="off"
                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                                id="newServiceName">
                                            {{-- <input required type="hidden" id="newItemID" name="item_id"> --}}
                                            <div id="service-list" class="dropdown-menu"
                                                aria-labelledby="newServiceName">
                                                <!-- Item list will be appended here -->
                                            </div>
                                            <div id="newServiceName-validation" class="text-danger"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-8 col-sm-6">
                                        <label for="newDocument">Document</label>
                                        <input name="document[]" type="file" class="form-control" id="newDocument"
                                            aria-describedby="newDocument" accept="image/*" multiple>
                                        <small class="text-muted">You can select up to 3 images.</small>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div id="temporaryItem">
                                    <table class="display expandable-table text-nowrap" style="min-width: 100%">
                                        <thead>
                                            <tr class="text-center">
                                                <th>No</th>
                                                <th>PMS Code</th>
                                                <th>Item Name</th>
                                                <th style="width: 120px"><span class="text-danger">*</span>Quantity
                                                </th>
                                                <th>Unit</th>
                                                <th style="width: 200px"><span class="text-danger">*</span>Option
                                                </th>
                                                {{-- <th style="width: 220px"><span
                                                class="text-danger">*</span>Condition</th> --}}
                                                <th><span class="text-danger">*</span>Utility</th>
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
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" id="submitButton" class="btn btn-success">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Document Modal -->
        <div class="modal fade" id="documentModal" tabindex="-1" role="dialog" aria-labelledby="documentModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="documentModalLabel">Documents</h5>
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

        <!-- Detail Purchase Request Modal -->
        <div class="modal fade" id="detailPurchaseRequestModal" tabindex="-1" role="dialog"
            aria-labelledby="detailPurchaseRequestModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="detailPurchaseRequestModalLabel">Detail Purchase Request</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="acceptPurchaseRequestsForm" method="POST" action="{{ url('acceptPurchaseRequests') }}"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-8 col-sm-6">
                                        <label for="purchaseRequestNumber">No. PR</label>
                                        <input type="hidden" name="pr_id" id="pr_id">
                                        <input readonly name="purchase_request_number" type="text"
                                            class="form-control" id="purchaseRequestNumber">
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
                                    <table class="display expandable-table" style="width:100%;">
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
                            @if ((Auth::user() && Auth::user()->role === 'Super Admin') || Auth::user()->role === 'Port Engineer')
                                <button type="submit" id="submitButton" class="btn btn-success">Accept</button>
                            @endif
                    </form>
                    <form id="rejectPurchaseRequestsForm" method="POST" action="{{ url('rejectPurchaseRequests') }}">
                        @csrf
                        <input type="hidden" name="pr_id" id="pr_id">
                        @if ((Auth::user() && Auth::user()->role === 'Super Admin') || Auth::user()->role === 'Port Engineer')
                            <button type="submit" id="rejectButton" class="btn btn-danger">Reject</button>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    {{-- Load and Search Data --}}
    <script>
        $(document).ready(function() {
            // var shipID = localStorage.getItem('shipId');
            var prNumber = localStorage.getItem('prNumber');
            var activePillId = localStorage.getItem('activePillId'); // Retrieve the active tab ID

            // If shipID exists, use it to load the correct tab and data
            if (activePillId) {
                $('#' + activePillId).tab('show'); // Show the corresponding tab
                loadData(activePillId); // Load the data for the ship

                // If there's a PR number, prefill the search input
                if (prNumber) {
                    $('input[id="searchPurchaseRequests"]').val(prNumber);
                    loadData(activePillId, prNumber); // Load data based on the PR number
                }

                // Update the active tab ID based on the used shipID
                localStorage.setItem('activePillId', activePillId);

                // Optionally, clear the storage after using the values (if not needed elsewhere)
                localStorage.removeItem('shipId');
                localStorage.removeItem('prNumber');
            } else if (activePillId) {
                // If there's no specific shipID but an activePillId, load the corresponding tab and data
                $('#' + activePillId).tab('show');
                loadData(activePillId);
            }

            // Handle tab clicks
            $('.nav-pills .nav-link').on('click', function() {
                var shipID = $(this).attr('id'); // Get the ID of the clicked tab (ship ID)

                // Store the clicked ship as the active ship in local storage
                localStorage.setItem('activePillId', shipID);
                localStorage.setItem('shipId', shipID); // Optionally, also store it as shipId

                // Load data for the clicked ship
                loadData(shipID);
            });

            // Handle search input manually
            $('input[id="searchPurchaseRequests"]').keyup(function() {
                var activePillId = localStorage.getItem('activePillId');
                var searchKeyword = $(this).val();

                // Ensure that we're searching within the currently active ship
                loadData(activePillId, searchKeyword);
            });

            // Function to load data
            function loadData(shipID, searchKeyword = '') {
                $(".data").html('<center><p class="text-muted">Searching for data...</p></center>');

                $.ajax({
                    url: '/data-purchaseRequests/' + shipID,
                    type: 'GET',
                    data: {
                        search: searchKeyword // Pass the search term if it's provided
                    },
                    success: function(response) {
                        $(".data").html(response.html);

                        // Get and set the name of the selected ship
                        var selectedShipName = $('#' + shipID).text().trim();
                        $('.vessel-title').text(selectedShipName);
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            }
        });
    </script>

    {{-- Add New Purchase Request Modal --}}
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

            $('#addPurchaseRequestsModal').on('show.bs.modal', function(e) {
                var activePill = $('#v-pills-tab .nav-link.active');
                var shipData = activePill.data('ship');
                $('#addPurchaseRequestsModal #newShipID').val(shipData.ship_name);
                // Generate PR Number
                $('#addPurchaseRequestsModal #generateNewPurchaseRequestNumber')
                    .on('click', function() {
                        var shipInitials = generateShipInitials($(
                            '#addPurchaseRequestsModal #newShipID').val());
                        $('#addPurchaseRequestsModal #newPurchaseRequestNumber').val('');
                        $.ajax({
                            url: '{{ url('generate-purchaseRequestNumber') }}',
                            type: 'GET',
                            data: {
                                ship: $('#addPurchaseRequestsModal #newShipID').val()
                            },
                            success: function(response) {
                                console.log(response.purchase_request_number +
                                    shipInitials);
                                $('#addPurchaseRequestsModal #newPurchaseRequestNumber')
                                    .val(response.purchase_request_number +
                                        shipInitials);
                            },
                            error: function(xhr, status, error) {
                                console.error(error);
                            }
                        });
                    });
                // Cek duplikasi nomor PR
                $('input[name="purchase_request_number"]').on('input', function() {
                    var PRno = $(this).val();
                    $.ajax({
                        type: "get",
                        url: "{{ url('check-purchaseRequestNumber') }}",
                        data: "PRno=" + PRno,
                        success: function(response) {
                            if (response.exists) {
                                $('#newPurchaseRequestNumber-validation').text(
                                    'Purchase request number already used.');
                                $('#addPurchaseRequestsModal button[id=submitButton]')
                                    .attr('disabled', true);
                            } else {
                                $('#newPurchaseRequestNumber-validation').text('');
                                $('#addPurchaseRequestsModal button[id=submitButton]')
                                    .attr('disabled', false);
                            }
                        },
                        error: function() {
                            alert('Error');
                        }
                    });
                });
            });
            // Handle file input maximal 3
            document.getElementById('newDocument').addEventListener('change', function() {
                var fileInput = this;
                var files = fileInput.files;
                if (files.length > 3) {
                    alert('You can only upload a maximum of 3 files.');
                    fileInput.value = ''; // Clear the input if the user exceeds the limit
                }
            });

            // Validasi sebelum submit form di modal "Add Purchase Requests"
            $('#addPurchaseRequestsForm').on('submit', function(e) {
                var tableBody = $('#addPurchaseRequestsModal #temporaryItem tbody');
                if (tableBody.children('tr').length === 0) {
                    e.preventDefault(); // Mencegah pengiriman form
                    $('#emptyTableWarning').show(); // Tampilkan pesan peringatan
                    return false;
                } else {
                    $('#emptyTableWarning').hide(); // Sembunyikan pesan peringatan jika ada item
                }
            });
            // Validasi sebelum submit form di modal "Add Automatic Purchase Requests"
            $('#addAutomaticPurchaseRequestsForm').on('submit', function(e) {
                var tableBody = $('#addAutomaticPurchaseRequestsModal #temporaryItem tbody');
                if (tableBody.children('tr').length === 0) {
                    e.preventDefault(); // Mencegah pengiriman form
                    $('#emptyTableWarning').show(); // Tampilkan pesan peringatan
                    return false;
                } else {
                    $('#emptyTableWarning').hide(); // Sembunyikan pesan peringatan jika ada item
                }
            });

            // Reset form dan sembunyikan pesan peringatan ketika modal ditutup
            $('#addPurchaseRequestsModal, #addAutomaticPurchaseRequestsModal').on('hidden.bs.modal',
                function() {
                    $('#emptyTableWarning').hide(); // Sembunyikan pesan peringatan
                    $('#temporaryItem tbody').empty(); // Kosongkan tabel
                });

            // Modal Add Purchase Request On Close
            $('#addPurchaseRequestsModal').on('hidden.bs.modal', function(e) {
                $('#addPurchaseRequestsModal #newPurchaseRequestNumber').val('');
                $('#addPurchaseRequestsModal #newShipID').val('');
            });
            // Modal Add Purchase Request On Close
            $('#addAutomaticPurchaseRequestsModal').on('hidden.bs.modal', function(e) {
                $('#addAutomaticPurchaseRequestsModal #newPurchaseRequestNumber').val('');
                $('#addAutomaticPurchaseRequestsModal #newShipID').val('');
            });
        });
    </script>

    {{-- Add Modal Item and Service --}}
    <script>
        $(document).ready(function() {
            var itemStorage = JSON.parse(localStorage.getItem('item')) || [];
            var jasaStorage = JSON.parse(localStorage.getItem('jasa')) || [];
            var jasaSeparatorAdded = false; // Untuk melacak apakah separator sudah ditambahkan

            // Fungsi untuk me-render ulang tabel item dan jasa
            function renderTable() {
                $('#temporaryItem tbody').empty(); // Bersihkan tabel item sebelum di-render ulang
                jasaSeparatorAdded = false; // Reset status separator

                // Render item terlebih dahulu
                itemStorage.forEach(function(item, index) {
                    var rowHTML = `
                        <tr class="text-center">
                            <th>${index + 1}</th>
                            <td>${item.code}</td>
                            <td>${item.name}</td>
                            <td><input type="number" name="quantity[]" class="form-control text-center quantity-input" value="0" min="1" required></td>
                            <td>${item.unit}</td>
                            <td>
                                <select required name="option[]" class="form-control text-center">
                                    <option selected disabled value="">-- Select Option --</option>
                                    <option value="Regular">Regular</option>
                                    <option value="Critical">Critical</option>
                                    <option value="Urgent">Urgent</option>
                                </select>
                            </td>
                            <td><input type="text" name="utility_items[]" class="form-control text-center" required></td>
                            <td><button type="button" class="btn btn-danger remove-row" data-index="${index}" data-type="item">Remove</button></td>
                            <input type="hidden" name="item_id[]" value="${item.id}">
                        </tr>
                        `;
                    $('#temporaryItem tbody').append(rowHTML);
                });

                // Jika ada jasa, tambahkan pemisah untuk jasa
                if (jasaStorage.length > 0 && !jasaSeparatorAdded) {
                    var separator = `
                    <tr class="text-center jasa-separator">
                        <td colspan="8"><strong>Services (Jasa)</strong></td>
                    </tr>
                    `;
                    $('#temporaryItem tbody').append(separator);
                    jasaSeparatorAdded = true; // Tandai bahwa separator sudah ditambahkan
                }

                // Render jasa setelah pemisah
                jasaStorage.forEach(function(jasa, index) {
                    var jasaRowHTML = `
                        <tr class="text-center">
                            <th>Jasa</th>
                            <td>${jasa.code}</td>
                            <td>${jasa.name}</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td><input type="text" name="utility_services[]" class="form-control text-center" required></td>
                            <td><button type="button" class="btn btn-danger remove-row" data-index="${index}" data-type="jasa">Remove</button></td>
                            <input type="hidden" name="service_id[]" value="${jasa.id}">
                        </tr>
                        `;
                    $('#temporaryItem tbody').append(jasaRowHTML);
                });
            }

            // Fungsi untuk menambah item ke dalam storage dan render ulang tabel
            function addItemToStorage(itemID, itemCode, itemName, itemUnit, isJasa = false) {
                if (isJasa) {
                    jasaStorage.push({
                        id: itemID,
                        code: itemCode,
                        name: itemName,
                        unit: itemUnit
                    });
                    localStorage.setItem('jasa', JSON.stringify(jasaStorage));
                } else {
                    var itemExist = itemStorage.some(function(item) {
                        return item.id === itemID;
                    });
                    if (!itemExist) {
                        itemStorage.push({
                            id: itemID,
                            code: itemCode,
                            name: itemName,
                            unit: itemUnit
                        });
                        localStorage.setItem('item', JSON.stringify(itemStorage));
                    } else {
                        Swal.fire({
                            title: 'Item already Added!',
                            icon: 'warning',
                        })
                        return;
                    }
                }
                renderTable(); // Render ulang tabel setelah item atau jasa ditambahkan
            }

            // Event handler untuk menambah item dari dropdown
            $('#item-list').on('click', '.dropdown-item', function(e) {
                e.preventDefault();
                var itemID = $(this).data('item-id');
                var itemCode = $(this).data('item-code');
                var itemName = $(this).data('item-name');
                var itemUnit = $(this).data('item-unit');
                addItemToStorage(itemID, itemCode, itemName, itemUnit, false);
            });

            // Event handler untuk menambah jasa dari dropdown
            $('#service-list').on('click', '.dropdown-item', function(e) {
                e.preventDefault();
                var itemID = $(this).data('item-id');
                var itemCode = $(this).data('item-code');
                var itemName = $(this).data('item-name');
                var itemUnit = $(this).data('item-unit');
                addItemToStorage(itemID, itemCode, itemName, itemUnit, true); // True untuk jasa
            });

            // Event handler untuk menghapus item atau jasa dari tabel
            $(document).on('click', '.remove-row', function() {
                var index = $(this).data('index');
                var type = $(this).data('type');

                if (type === 'item') {
                    itemStorage.splice(index, 1); // Hapus item berdasarkan index
                    localStorage.setItem('item', JSON.stringify(
                        itemStorage)); // Simpan ulang ke localStorage
                } else if (type === 'jasa') {
                    jasaStorage.splice(index, 1); // Hapus jasa berdasarkan index
                    localStorage.setItem('jasa', JSON.stringify(
                        jasaStorage)); // Simpan ulang ke localStorage
                }

                renderTable(); // Render ulang tabel setelah penghapusan
            });

            // AJAX untuk mendapatkan daftar item berdasarkan ship
            $('#addPurchaseRequestsModal').on('show.bs.modal', function(e) {
                var shipName = $('#addPurchaseRequestsModal #newShipID').val();
                // Ambil daftar items
                $.ajax({
                    type: "GET",
                    url: "{{ url('get-itemName') }}",
                    data: {
                        ship: shipName
                    }, // Kirim data nama kapal
                    success: function(response) {
                        var itemList = $('#item-list');
                        itemList.empty(); // Kosongkan list sebelumnya

                        // Tambahkan item ke dalam dropdown
                        $.each(response.ship_items, function(index, item) {
                            var newLink = $('<a></a>')
                                .addClass('dropdown-item')
                                .attr('href', '#')
                                .attr('data-item-id', item.id)
                                .attr('data-item-code', item.item_pms)
                                .attr('data-item-unit', item.item_unit)
                                .attr('data-item-name', item.item_name)
                                .text(item.item_pms + ' - ' + item.item_name);
                            itemList.append(newLink);
                        });
                    },
                    error: function(xhr) {
                        console.log(xhr.responseText);
                        alert('Gagal mengambil data item. Silakan coba lagi.');
                    }
                });

                // Handle search input item
                $('#newItemName').on('input', function() {
                    var keyword = $(this).val().toLowerCase();
                    $('#item-list .dropdown-item').each(function() {
                        var text = $(this).text().toLowerCase();
                        if (text.indexOf(keyword) > -1) {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    });
                    $('.dropdown-menu').addClass('show-dropdown');
                });

                // Ambil daftar jasa (service)
                $.ajax({
                    type: "GET",
                    url: "{{ url('get-serviceName') }}", // Endpoint baru untuk jasa
                    data: {
                        ship: shipName
                    }, // Kirim data nama kapal
                    success: function(response) {
                        var serviceList = $('#service-list');
                        serviceList.empty(); // Kosongkan list sebelumnya

                        // Tambahkan jasa ke dalam dropdown
                        $.each(response.service, function(index, service) {
                            var newLink = $('<a></a>')
                                .addClass('dropdown-item')
                                .attr('href', '#')
                                .attr('data-item-id', service.id)
                                .attr('data-item-code', service.service_code)
                                .attr('data-item-unit', service.service_unit)
                                .attr('data-item-name', service.service_name)
                                .text(service.service_code + ' - ' + service
                                    .service_name);
                            serviceList.append(newLink);
                        });
                    },
                    error: function(xhr) {
                        console.log(xhr.responseText);
                        alert('Gagal mengambil data jasa. Silakan coba lagi.');
                    }
                });

                // Handle search input services
                $('#newServiceName').on('input', function() {
                    var keyword = $(this).val().toLowerCase();
                    $('#service-list .dropdown-item').each(function() {
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

            // Bersihkan semuanya ketika modal ditutup
            $('#addPurchaseRequestsModal').on('hidden.bs.modal', function() {
                itemStorage = [];
                jasaStorage = [];
                localStorage.removeItem('item');
                localStorage.removeItem('jasa');
                $('#temporaryItem tbody').empty(); // Bersihkan tabel item dan jasa
            });

            // Reset storage ketika halaman di-reload
            $(window).on('beforeunload', function() {
                localStorage.removeItem('item');
                localStorage.removeItem('jasa');
            });

            // Inisialisasi: Render ulang tabel saat halaman pertama kali dimuat
            renderTable();
        });
    </script>

    {{-- Detail Purchase Request Modal --}}
    <script>
        $(document).ready(function() {
            $('#detailPurchaseRequestModal').on('show.bs.modal', function(e) {
                var activePill = $('#v-pills-tab .nav-link.active');
                var shipData = activePill.data('ship');
                $('#detailPurchaseRequestModal #shipID').val(shipData.ship_name);

                var button = $(e.relatedTarget); // Button that triggered the modal
                var itemData = button.data('purchaserequest'); // Extract info from data-* attributes

                if (itemData) {
                    $('#detailPurchaseRequestModal #pr_id').val(itemData.id);
                    $('#detailPurchaseRequestModal #purchaseRequestNumber').val(itemData
                        .purchase_request_number);

                    // Check the status and hide the buttons if "Diajukan"
                    if (itemData.status === "Diajukan") {
                        $('#detailPurchaseRequestModal #submitButton').show();
                        $('#detailPurchaseRequestModal #rejectButton').show();
                    } else {
                        $('#detailPurchaseRequestModal #submitButton').hide();
                        $('#detailPurchaseRequestModal #rejectButton').hide();
                    }

                    // Clear the previous items in the table
                    $('#temporaryItem tbody').empty();

                    // Perform AJAX request to fetch items and services
                    $.ajax({
                        url: '/get-purchaseRequestItems/' + itemData.id,
                        method: 'GET',
                        success: function(response) {
                            // Append items to the table
                            if (response.items && response.items.length > 0) {
                                response.items.forEach(function(item, index) {
                                    var rowClass = '';
                                    if (item.total_quantity < item.minimum_quantity) {
                                        rowClass = 'bg-light-red';
                                        console.log('total quantity : ' +
                                            item.total_quantity);
                                        console.log('minimum quantity : ' +
                                            item.minimum_quantity);
                                    }
                                    var row = `
                    <tr class="text-center ${rowClass}">
                        <th>${index + 1}</th>
                        <td>${item.item_pms}</td>
                        <td>${item.item_name}</td>
                        <td>${item.quantity_needed}</td>
                        <td>${item.item_unit}</td>
                        <td>${item.option}</td>
                        <td>${item.utility}</td>
                        <td>${item.status}</td>
                    </tr>`;
                                    $('#temporaryItem tbody').append(row);
                                });
                            } else {
                                // $('#temporaryItem tbody').append(
                                //     '<tr class="text-center"><td colspan="8">No items available.</td></tr>'
                                // );
                            }

                            // Append jasa separator if there are services to display
                            if (response.services && response.services.length > 0) {
                                var separator = `
            <tr class="text-center jasa-separator">
                <td colspan="8"><strong>Services (Jasa)</strong></td>
            </tr>`;
                                $('#temporaryItem tbody').append(separator);

                                // Append jasa rows to the table
                                response.services.forEach(function(service, index) {
                                    var jasaRow = `
                    <tr class="text-center">
                        <th>Jasa</th>
                        <td>${service.service_code}</td>
                        <td>${service.service_name}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>${service.utility}</td>
                        <td>${service.status}</td>
                    </tr>`;
                                    $('#temporaryItem tbody').append(jasaRow);
                                });
                            }
                        },
                        error: function() {
                            console.log('Error loading data');
                        }
                    });

                } else {
                    console.log('No item data found');
                }
            });
        });
    </script>

    {{-- Clear Table when Close Modal --}}
    <script>
        $('#addPurchaseRequestsModal, #addAutomaticPurchaseRequestsModal, #detailPurchaseRequestModal').on(
            'hidden.bs.modal',
            function(e) {
                var tableBody = $(
                    '#temporaryItem tbody');
                tableBody.empty();
            });
    </script>

    {{-- Export Purchase Requests Modal --}}
    <script>
        $(document).ready(function() {
            $('#exportPurchaseRequestModal').on('show.bs.modal', function(e) {
                var activePill = $('#v-pills-tab .nav-link.active');
                var shipData = activePill.data('ship');
                $('#exportPurchaseRequestModal #shipID').val(
                    shipData.ship_name); // Set the ship name in the modal's input

                var today = new Date();
                var currentMonth = (today.getMonth() + 1).toString(); // getMonth() returns 0-11, so add 1
                var currentYear = today.getFullYear()
                    .toString(); // getFullYear() returns the full year (e.g., 2024)
                // Set the current month and year in the select elements
                $('#exportPurchaseRequestModal #month').val(currentMonth)
                    .change(); // Select the current month
                $('#exportPurchaseRequestModal #year').val(currentYear).change(); // Select the current year
            });
        });
    </script>

    {{-- Automatic Purchase Requests Modal --}}
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

            $('#addAutomaticPurchaseRequestsModal').on('show.bs.modal', function(e) {
                var activePill = $('#v-pills-tab .nav-link.active');
                var shipData = activePill.data('ship');
                $('#addAutomaticPurchaseRequestsModal #newShipID').val(shipData.ship_name);

                $('#addAutomaticPurchaseRequestsModal #generateNewAutomaticPurchaseRequestNumber')
                    .on('click', function() {
                        var shipInitials = generateShipInitials($(
                            '#addAutomaticPurchaseRequestsModal #newShipID').val());
                        $('#addAutomaticPurchaseRequestsModal #newAutomaticPurchaseRequestNumber').val(
                            '');
                        $.ajax({
                            url: '{{ url('generate-purchaseRequestNumber') }}',
                            type: 'GET',
                            data: {
                                ship: $('#addAutomaticPurchaseRequestsModal #newShipID').val()
                            },
                            success: function(response) {
                                $('#addAutomaticPurchaseRequestsModal #newAutomaticPurchaseRequestNumber')
                                    .val(response.purchase_request_number +
                                        shipInitials);
                            },
                            error: function(xhr, status, error) {
                                console.error(error);
                            }
                        });
                    });

                // Handle file input maximum of 3 files
                $('#addAutomaticPurchaseRequestsModal #newDocument').on('change', function() {
                    var fileInput = $(this);
                    var files = fileInput[0].files;
                    if (files.length > 3) {
                        alert('You can only upload a maximum of 3 files.');
                        fileInput.val(''); // Clear the input if the user exceeds the limit
                    }
                });

                // Switch PR Number
                $('#addAutomaticPurchaseRequestsModal #switchNewAutomaticPurchaseRequestNumber')
                    .off('change')
                    .on('change', function() {
                        if ($(this).is(':checked')) {
                            var shipInitials = generateShipInitials($(
                                '#addAutomaticPurchaseRequestsModal #newShipID').val());
                            $.ajax({
                                url: '{{ url('generate-purchaseRequestNumber') }}',
                                type: 'GET',
                                data: {
                                    ship: $('#addAutomaticPurchaseRequestsModal #newShipID')
                                        .val()
                                },
                                success: function(response) {
                                    console.log(response.purchase_request_number +
                                        shipInitials);
                                    $('#addAutomaticPurchaseRequestsModal #newPurchaseRequestNumber')
                                        .val(response.purchase_request_number +
                                            shipInitials).attr('readonly', true);
                                },
                                error: function(xhr, status, error) {
                                    console.error(error);
                                }
                            });
                        } else {
                            $('#addAutomaticPurchaseRequestsModal #newPurchaseRequestNumber').val('')
                                .removeAttr('readonly');
                        }
                    });
                // Cek duplikasi nomor PR
                $('#addAutomaticPurchaseRequestsModal input[name="purchase_request_number"]').on('input',
                    function() {
                        var PRno = $(this).val();
                        $.ajax({
                            type: "get",
                            url: "{{ url('check-purchaseRequestNumber') }}",
                            data: "PRno=" + PRno,
                            success: function(response) {
                                if (response.exists) {
                                    $('#addAutomaticPurchaseRequestsModal #newPurchaseRequestNumber-validation')
                                        .text(
                                            'Purchase request number already used.');
                                    $('#addAutomaticPurchaseRequestsModal button[id=submitButton]')
                                        .attr('disabled', true);
                                } else {
                                    $('#addAutomaticPurchaseRequestsModal #newPurchaseRequestNumber-validation')
                                        .text('');
                                    $('#addAutomaticPurchaseRequestsModal button[id=submitButton]')
                                        .attr('disabled', false);
                                }
                            },
                            error: function() {
                                alert('Error');
                            }
                        });
                    });

                $.ajax({
                    url: '/get-automaticPurchaseRequests', // URL untuk mengirim permintaan AJAX
                    type: 'GET',
                    data: {
                        ship_id: shipData.id // Kirim ID kapal untuk mendapatkan item yang relevan
                    },
                    success: function(response) {
                        var tableBody = $(
                            '#addAutomaticPurchaseRequestsModal #temporaryItem tbody');
                        tableBody.empty(); // Kosongkan tabel sebelum mengisi ulang

                        if (response.items.length > 0) {
                            response.items.forEach(function(item, index) {
                                var row = `
                <tr class="text-center">
                    <td>${index + 1}</td> <!-- Use the loop's index here -->
                    <td>${item.item_pms}</td>
                    <td>${item.item_name}</td>
                    <td><input type="number" name="quantity[]" class="form-control text-center quantity-input p-0" value="${item.quantity_needed}" min="1" required></td>
                    <td>${item.item_unit}</td>
                    <td>
                        <select required name="option[]" class="form-control text-center">
                            <option selected disabled value="">-- Select Option --</option>
                            <option value="Regular">Regular</option>
                            <option value="Critical">Critical</option>
                            <option value="Urgent">Urgent</option>
                        </select>
                    </td>
                    <td><textarea type="text" name="utility_items[]" class="form-control text-center" required></textarea></td>
                </tr>
                <input type="hidden" name="item_id[]" value="${item.id}">
                <input type="hidden" name="item_pms[]" value="${item.item_pms}">
                <input type="hidden" name="item_name[]" value="${item.item_name}">
                <input type="hidden" name="item_unit[]" value="${item.item_unit}">
                    `;
                                tableBody.append(row);
                            });
                        } else {
                            tableBody.append(
                                '<tr class="text-center"><td colspan="8">No items available.</td></tr>'
                            );
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            });
        });
    </script>
@endsection
</div>
