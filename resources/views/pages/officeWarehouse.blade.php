@extends('layouts.layout')
<title>Office Warehouse</title>
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
                        <p class="card-title mb-2">Office Warehouse</p>
                        <div class="input-group mb-3">
                            <input id="searchOfficeWarehouse" type="text" class="form-control" placeholder="Search...">
                            <div class="input-group-append">
                                <button class="btn btn-sm btn-primary" type="button">Search</button>
                            </div>
                        </div>
                        @if ((Auth::user() && Auth::user()->role === 'Super Admin') || Auth::user()->role === 'Purchasing Logistic Admin')
                            <div class="input-group mb-3">
                                <button type="button" class="btn btn-success btn-icon-text" data-toggle="modal"
                                    data-target="#addOfficeWarehouseModal">
                                    <i class="mdi mdi-database-plus pr-2"></i>Add Office Warehouse Data
                                </button>
                            </div>
                        @endif
                        <div class="row">
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table class="display expandable-table" style="width:100%">
                                        <thead>
                                            <tr class="text-center">
                                                <th>No</th>
                                                <th>Photo</th>
                                                <th>PMS Code</th>
                                                <th>Item Name</th>
                                                <th>Total Quantity</th>
                                                @if ((Auth::user() && Auth::user()->role === 'Super Admin') || Auth::user()->role === 'Director')
                                                    <th>Delete</th>
                                                @endif
                                                <th>Detail Quantity</th>
                                                <th>Condition</th>
                                                <th>Location</th>
                                                @if ((Auth::user() && Auth::user()->role === 'Super Admin') || Auth::user()->role === 'Purchasing Logistic Admin')
                                                    <th class="col-1">Action</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $n = 1;
                                            @endphp
                                            @foreach ($groupedOfficeWarehouses as $itemId => $items)
                                                @foreach ($items as $index => $item)
                                                    <tr class="text-center">
                                                        @if ($index == 0)
                                                            <th rowspan="{{ count($items) }}">
                                                                {{ $n++ }}</th>
                                                            <td rowspan="{{ count($items) }}">
                                                                @if ($item->item_photo)
                                                                    <img src="images/uploads/item-photos/{{ $item->item_photo }}"
                                                                        class="item-image rounded" alt="Unavailable">
                                                                @else
                                                                    <span class="text-muted">No Photo</span>
                                                                @endif
                                                            </td>
                                                            <td rowspan="{{ count($items) }}">
                                                                {{ $item->item_pms }}</td>
                                                            <td rowspan="{{ count($items) }}">
                                                                {{ $item->item_name }}</td>
                                                            <td rowspan="{{ count($items) }}">
                                                                {{ $item->total_quantity }}
                                                            </td>
                                                            @if ((Auth::user() && Auth::user()->role === 'Super Admin') || Auth::user()->role === 'Director')
                                                                <td rowspan="{{ count($items) }}">
                                                                    <a href="/deleteOfficeWarehouse/{{ $item->id }}"
                                                                        class="btn btn-outline-danger btn-delete">
                                                                        <i class="mdi mdi-delete"></i>
                                                                    </a>
                                                                </td>
                                                            @endif
                                                        @endif
                                                        <td>{{ $item->quantity }}</td>
                                                        <td>{{ $item->condition }}</td>
                                                        <td>{{ $item->location }}</td>
                                                        @if ((Auth::user() && Auth::user()->role === 'Super Admin') || Auth::user()->role === 'Purchasing Logistic Admin')
                                                            <td>
                                                                <a href="#" class="btn btn-outline-primary"
                                                                    data-toggle="modal"
                                                                    data-target="#updateOfficeWarehouseModal"
                                                                    data-officeWarehouse="{{ json_encode($item) }}">
                                                                    <i class="mdi mdi-lead-pencil"></i>
                                                                </a>
                                                            </td>
                                                        @endif
                                                    </tr>
                                                @endforeach
                                            @endforeach
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
        {{-- Add Modal --}}
        <div class="modal fade" id="addOfficeWarehouseModal" tabindex="-1" role="dialog"
            aria-labelledby="addOfficeWarehouseModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addOfficeWarehouseModalLabel">Add Item to Office Warehouse</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="addOfficeWarehouseForm" method="POST" action="{{ url('addOfficeWarehouse') }}"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="newItemName"><span class="text-danger">* </span>Item Name</label>
                                <div class="itemName-dropdown">
                                    <input required type="text" id="newItemName" name="item_name" class="form-control"
                                        autocomplete="off" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                        placeholder="Select item name">
                                    <input type="text" id="newItemID" name="item_id" hidden>
                                    <div id="itemName-list" class="dropdown-menu" aria-labelledby="item_name">
                                        @foreach ($barang as $items)
                                            <a class="dropdown-item" data-item="{{ json_encode($items) }}"
                                                href="#">{{ $items->item_pms }}-{{ $items->item_name }}</a>
                                        @endforeach
                                    </div>
                                    <div id="item-validation-message" class="text-danger"></div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-success">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Update Modal -->
        <div class="modal fade" id="updateOfficeWarehouseModal" tabindex="-1" role="dialog"
            aria-labelledby="updateOfficeWarehouseModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateOfficeWarehouseModalLabel">Update Item in Office Warehouse</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="updateOfficeWarehouseForm" method="POST" action="{{ url('updateOfficeWarehouse') }}"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" name="id" id="id">
                            <div class="form-group">
                                <label for="itemName"><span class="text-danger">* </span>Item Name</label>
                                <input readonly class="form-control" type="text" name="item_name" id="itemName">
                            </div>
                            <div class="form-group">
                                <label for="quantity"><i class="text-danger">* </i>Quantity</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" required
                                    placeholder="Enter quantity">
                            </div>
                            <div class="form-group">
                                <label for="condition"><i class="text-danger">* </i>Condition</label>
                                <select disabled class="form-control" id="condition" name="condition" required>
                                    <option selected disabled value="">-- Select item condition --</option>
                                    <option value="Baru">Baru</option>
                                    <option value="Bekas Bisa Pakai">Bekas Bisa Pakai</option>
                                    <option value="Bekas Tidak Bisa Pakai">Bekas Tidak Bisa Pakai</option>
                                    <option value="Rekondisi">Rekondisi</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="location"><i class="text-danger">* </i>Location</label>
                                <input type="text" class="form-control" id="location" name="location" required
                                    placeholder="Enter item location">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-success">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endsection
    @section('script')
        {{-- Search --}}
        <script>
            $(document).ready(function() {
                $('#searchOfficeWarehouse').on('keyup', function() {
                    let query = $(this).val();

                    $.ajax({
                        url: "{{ 'findOfficeWarehouse' }}",
                        type: "GET",
                        data: {
                            query: query
                        },
                        success: function(response) {
                            let rows = '';
                            let n = 1;

                            if (Object.keys(response).length > 0) {
                                $.each(response, function(itemId, itemsGroup) {
                                    $.each(itemsGroup, function(index, item) {
                                        if (index === 0) {
                                            rows += `
                                    <tr class="text-center">
                                        <th rowspan="${itemsGroup.length}">${n++}</th>
                                        <td rowspan="${itemsGroup.length}">
                                            ${item.item_photo ? `<img src="images/uploads/item-photos/${item.item_photo}" class="item-image rounded" alt="Unavailable">` : '<span class="text-muted">No Photo</span>'}
                                        </td>
                                        <td rowspan="${itemsGroup.length}">${item.item_pms}</td>
                                        <td rowspan="${itemsGroup.length}">${item.item_name}</td>
                                        <td rowspan="${itemsGroup.length}">${item.total_quantity}</td>
                                        @if ((Auth::user() && Auth::user()->role === 'Super Admin') || Auth::user()->role === 'Director')
                                        <td rowspan="${itemsGroup.length}">
                                            <a href="/deleteOfficeWarehouse/${item.id}" class="btn btn-outline-danger btn-delete">
                                                <i class="mdi mdi-delete"></i>
                                            </a>
                                        </td>
                                        @endif
                                `;
                                        }
                                        rows += `
                                <td>${item.quantity}</td>
                                <td>${item.condition}</td>
                                <td>${item.location}</td>
                                @if ((Auth::user() && Auth::user()->role === 'Super Admin') || Auth::user()->role === 'Purchasing Logistic Admin')
                                <td>
                                    <a href="#" class="btn btn-outline-primary"
                                       data-toggle="modal"
                                       data-target="#updateOfficeWarehouseModal"
                                       data-officeWarehouse='${JSON.stringify(item)}'>
                                       <i class="mdi mdi-lead-pencil"></i>
                                    </a>
                                </td>
                                @endif
                            </tr>
                            `;
                                    });
                                });
                            } else {
                                rows =
                                    `<tr><td colspan="10" class="text-center">No records found</td></tr>`;
                            }

                            $('tbody').html(rows);
                        }
                    });
                });
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
                        $(dropdownInput).val(item.item_pms + ' - ' + item.item_name);
                        var itemIDInput = $(dropdownInput).siblings('input[name="item_id"]');
                        itemIDInput.val(item.id);
                        $(dropdownMenu).removeClass('show');
                        $('#item-validation-message').text('');

                        var item_id = itemIDInput.val();

                        if (item_id) {
                            checkItemExistence(item_id, $(dropdownInput).closest('.modal-body'));
                        }
                    });
                }

                function checkItemExistence(item_id, modalBody) {
                    $.ajax({
                        type: "get",
                        url: "{{ url('checkOfficeWarehouse') }}",
                        data: {
                            id: item_id,
                        },
                        success: function(response) {
                            if (response.exists) {
                                $(modalBody).find('#item-validation-message').text(
                                    'Item already exist');
                                $(modalBody).find('input[name="item_name"]').val(
                                    '');
                            } else {
                                $(modalBody).find('#item-validation-message').text('');
                            }
                        },
                    });
                }

                $(document).ready(function() {
                    // Initialize dropdowns for both add and update modals
                    setupDropdown('#newItemName', '#addOfficeWarehouseModal #itemName-list');

                    // Hide dropdown when clicking outside
                    $(document).on('click', function(event) {
                        if (!$(event.target).closest('.dropdown').length) {
                            $('.dropdown-menu').removeClass('show-dropdown');
                        }
                    });
                });
            });
        </script>

        {{-- UpdateOfficeWarehouse Modal --}}
        <script>
            $('#updateOfficeWarehouseModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget); // Button that triggered the modal
                var officeWarehouse = button.data(
                    'officewarehouse'); // Extract info from data-* attributes

                // Update the modal's content.
                var modal = $(this);
                modal.find('.modal-body #id').val(officeWarehouse.id);
                modal.find('.modal-body #itemName').val(officeWarehouse.item_pms + ' - ' + officeWarehouse
                    .item_name);
                modal.find('.modal-body #itemID').val(officeWarehouse.item_id);
                modal.find('.modal-body #quantity').val(officeWarehouse.quantity);
                modal.find('.modal-body #location').val(officeWarehouse.location);
                modal.find('.modal-body #condition').val(officeWarehouse.condition);
            });
        </script>
    @endsection
</div>
