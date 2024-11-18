@extends('layouts.layout')
<title>Items</title>
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
                        <p class="card-title mb-2">Items</p>
                        <div class="input-group mb-3">
                            <input id="searchItem" type="text" class="form-control" placeholder="Search...">
                            <div class="input-group-append">
                                <button class="btn btn-sm btn-primary" type="button">Search</button>
                            </div>
                        </div>
                        @if ((Auth::user() && Auth::user()->role === 'Super Admin') || Auth::user()->role === 'Fleet Admin')
                            <div class="input-group mb-3">
                                <button type="button" class="btn btn-success btn-icon-text" data-toggle="modal"
                                    data-target="#addItemModal">
                                    <i class="mdi mdi-database-plus pr-2"></i>Add Item
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
                                                <th data-sortable="true">No</th>
                                                <th>Photo</th>
                                                <th data-sortable="true">PMS</th>
                                                <th data-sortable="true">Account</th>
                                                <th data-sortable="true">Name</th>
                                                <th data-sortable="true">Unit</th>
                                                <th data-sortable="true">Category</th>
                                                @if (
                                                    (Auth::user() && Auth::user()->role === 'Super Admin') ||
                                                        Auth::user()->role === 'Director' ||
                                                        Auth::user()->role === 'Fleet Admin')
                                                    <th class="col-1">Action</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
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
        {{-- Update Modal --}}
        <div class="modal fade" id="updateItemModal" tabindex="-1" role="dialog" aria-labelledby="updateItemModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-custom-small" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateItemModalLabel">Update Item</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="updateItemForm" method="POST" action="{{ url('updateItem') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" name="id" id="id">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-8 col-sm-6">
                                        <label for="itemPMS"><i class="text-danger">* </i>Item PMS</label>
                                        <input type="text" class="form-control" id="itemPMS" name="item_pms" required>
                                        <div id="validation" class="text-danger"></div>
                                    </div>
                                    <div class="col-8 col-sm-6">
                                        <label for="accountID"><span class="text-danger">*</span>Item Account</label>
                                        <div class="itemAccount-dropdown">
                                            <input type="text" class="form-control" autocomplete="off"
                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                                id="itemAccount">
                                            <input required type="hidden" id="accountID" name="account_id">
                                            <div id="itemAccount-list" class="dropdown-menu" aria-labelledby="itemAccount">
                                                <!-- Item account will be appended here -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-8 col-sm-6">
                                        <label for="itemName"><i class="text-danger">* </i>Item Name</label>
                                        <input type="text" class="form-control" id="itemName" name="item_name" required>
                                    </div>
                                    <div class="col-8 col-sm-6">
                                        <label for="itemUnit"><i class="text-danger">* </i>Item Unit</label>
                                        <select class="form-control" id="itemUnit" name="item_unit" required>
                                            <option value="" selected disabled>-- Select item unit --</option>
                                            <option value="Set">Set</option>
                                            <option value="Meter">Meter</option>
                                            <option value="Roll">Roll</option>
                                            <option value="Unit">Unit</option>
                                            <option value="Pcs">Pcs</option>
                                            <option value="Ls">Ls</option>
                                            <option value="Dll">Dll</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="row">
                                    <div class="col-8 col-sm-6">
                                        <label for="itemCategory"><i class="text-danger">* </i>Item Category</label>
                                        <select class="form-control" id="itemCategory" name="item_category" required>
                                            <option selected disabled value="">-- Select item category --</option>
                                            <option value="Stock">Stock</option>
                                            <option value="Consumable">Consumable</option>
                                            <option value="Jasa">Jasa</option>
                                        </select>
                                    </div>
                                    <div class="col-8 col-sm-6">
                                        <label for="itemPhoto"><i class="text-danger" id="span-itemPhoto">*
                                            </i>Photo</label>
                                        <input type="file" class="form-control" id="itemPhoto" name="item_photo"
                                            accept=".jpg, .jpeg, .png, .svg">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-success">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Add Modal --}}
        <div class="modal fade" id="addItemModal" tabindex="-1" role="dialog" aria-labelledby="addItemModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-custom-small" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addItemModalLabel">Add Item</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="addItemForm" method="POST" action="{{ url('addItem') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-8 col-sm-6">
                                        <label for="newItemPMS"><i class="text-danger">* </i>Item PMS</label>
                                        <input type="text" class="form-control" id="newItemPMS" name="item_pms"
                                            required>
                                        <div id="validation" class="text-danger"></div>
                                    </div>
                                    <div class="col-8 col-sm-6">
                                        <label for="accountID"><span class="text-danger">*</span>Item Account</label>
                                        <div class="itemAccount-dropdown">
                                            <input type="text" class="form-control" autocomplete="off"
                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                                id="itemAccount">
                                            <input required type="hidden" id="accountID" name="account_id">
                                            <div id="itemAccount-list" class="dropdown-menu"
                                                aria-labelledby="itemAccount">
                                                <!-- Item account will be appended here -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-8 col-sm-6">
                                        <label for="newItemName"><i class="text-danger">* </i>Item Name</label>
                                        <input type="text" class="form-control" id="newItemName" name="item_name"
                                            required>
                                    </div>
                                    <div class="col-8 col-sm-6">
                                        <label for="newItemUnit"><i class="text-danger">* </i>Item Unit</label>
                                        <select class="form-control" id="newItemUnit" name="item_unit" required>
                                            <option value="" selected disabled>-- Select item unit --</option>
                                            <option value="Set">Set</option>
                                            <option value="Meter">Meter</option>
                                            <option value="Roll">Roll</option>
                                            <option value="Unit">Unit</option>
                                            <option value="Pcs">Pcs</option>
                                            <option value="Ls">Ls</option>
                                            <option value="Dll">Dll</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-8 col-sm-6">
                                        <label for="newItemCategory"><i class="text-danger">* </i>Item Category</label>
                                        <select class="form-control" id="newItemCategory" name="item_category" required>
                                            <option selected disabled value="">-- Select item category --</option>
                                            <option value="Stock">Stock</option>
                                            <option value="Consumable">Consumable</option>
                                            <option value="Jasa">Jasa</option>
                                        </select>
                                    </div>
                                    <div class="col-8 col-sm-6">
                                        <label for="newItemPhoto"><i class="text-danger" id="span-itemPhoto">*
                                            </i>Photo</label>
                                        <input type="file" class="form-control" id="newItemPhoto" name="item_photo"
                                            required accept=".jpg, .jpeg, .png, .svg">
                                    </div>
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
    @endsection
    @section('script')
        {{-- Update Modal --}}
        <script>
            $(document).ready(function() {
                $('#updateItemModal').on('show.bs.modal', function(event) {
                    var button = $(event.relatedTarget); // Button that triggered the modal
                    var item = button.data('item'); // Extract info from data-* attributes

                    // Update the modal's content.
                    var modal = $(this);
                    modal.find('.modal-body #id').val(item.id);
                    modal.find('.modal-body #itemPMS').val(item.item_pms);
                    modal.find('.modal-body #itemAccount').val(item.expense_accounts.account_name);
                    modal.find('.modal-body #accountID').val(item.expense_accounts.id);
                    modal.find('.modal-body #itemName').val(item.item_name);
                    modal.find('.modal-body #itemUnit').val(item.item_unit);
                    modal.find('.modal-body #itemCategory').val(item.item_category);

                    var accounts = @json($account);

                    function populateAccountDropdown(accounts) {
                        var itemAccountList = $('#updateItemModal #itemAccount-list');
                        itemAccountList.empty(); // Kosongkan dropdown sebelum menambahkan item

                        $.each(accounts, function(index, account) {
                            var newLink = $('<a></a>')
                                .addClass('dropdown-item account-dropdown')
                                .attr('href', '#')
                                .attr('data-account-id', account
                                    .id) // Simpan account ID di data attribute
                                .text(account.account_name); // Tampilkan nama akun

                            // Append ke dalam dropdown list
                            itemAccountList.append(newLink);
                        });
                    }
                    populateAccountDropdown(accounts);
                    $('#updateItemModal #itemAccount').on('input', function() {
                        var keyword = $(this).val().toLowerCase();
                        $('.dropdown-item').each(function() {
                            var text = $(this).text().toLowerCase();
                            if (text.indexOf(keyword) > -1) {
                                $(this).show();
                            } else {
                                $(this).hide();
                            }
                        });
                        $('#updateItemModal .dropdown-menu').addClass('show-dropdown');
                    });
                    $('#updateItemModal #itemAccount-list').on('click', '.dropdown-item', function(e) {
                        e.preventDefault();
                        var accountName = $(this).text(); // Ambil nama akun yang dipilih
                        var accountId = $(this).data(
                            'account-id'); // Ambil ID akun dari data-account-id
                        $('#updateItemModal #itemAccount').val(
                            accountName); // Set nilai input text dengan nama akun
                        $('#updateItemModal #accountID').val(accountId);
                    });
                });
            });
        </script>

        {{-- Load Search Item --}}
        <script>
            $(document).ready(function() {
                var itemsData = @json($items);

                function renderAllItems() {
                    var tableBody = $('tbody');
                    tableBody.empty(); // Kosongkan tabel

                    if (itemsData.length > 0) {
                        var no = 1;
                        itemsData.forEach(function(item) {
                            var row = `<tr class="text-center" style="height: 50px;">
            <th class="align-middle" scope="row">${no++}</th>
            <td><img class="item-image rounded" style="width:100px; height:100px" src="images/uploads/item-photos/${item.item_photo}" alt="image" /></td>
            <td>${item.item_pms}</td>
            <td>${item.expense_accounts.account_name}</td>
            <td>${item.item_name}</td>
            <td>${item.item_unit}</td>
            <td>${item.item_category}</td>
            @if (
                (Auth::user() && Auth::user()->role === 'Super Admin') ||
                    Auth::user()->role === 'Director' ||
                    Auth::user()->role === 'Fleet Admin')
            <td>
                <div class="btn-group" role="group">
                    @if ((Auth::user() && Auth::user()->role === 'Super Admin') || Auth::user()->role === 'Fleet Admin')
                    <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#updateItemModal" data-item='${JSON.stringify(item)}'>
                        <i class="mdi mdi-lead-pencil"></i>
                    </button>
                    @endif
                    @if ((Auth::user() && Auth::user()->role === 'Super Admin') || Auth::user()->role === 'Director')
                    <a href="/deleteItem/${item.id}" class="btn btn-outline-danger btn-delete">
                        <i class="mdi mdi-delete"></i>
                    </a>
                    @endif
                </div>
            </td>
            @endif
        </tr>`;
                            tableBody.append(row);
                        });
                    } else {
                        tableBody.html(
                            '<tr class="text-center"><td colspan="8" class="text-center text-muted">No records found</td></tr>'
                        );
                    }

                }

                // Tampilkan data semua kapal saat halaman dimuat pertama kali
                renderAllItems();

                $('#searchItem').on('keyup', function() {
                    var search = $(this).val();
                    var tableBody = $('tbody');

                    // Jika input kosong, tampilkan kembali semua data kapal
                    if (search.length === 0) {
                        renderAllItems();
                    } else {
                        // Tampilkan pesan "Searching for data" jika ada input pencarian
                        tableBody.html(
                            '<tr class="text-center"><td colspan="8" class="text-center text-muted">Searching for data...</td></tr>'
                        );

                        $.ajax({
                            url: "{{ url('findItem') }}", // URL ke route yang menangani pencarian
                            method: 'GET',
                            data: {
                                search: search
                            },
                            success: function(response) {
                                tableBody.empty(); // Kosongkan tabel
                                if (response.length > 0) {
                                    var no = 1;
                                    response.forEach(function(item) {
                                        var row = `<tr class="text-center" style="height: 50px;">
            <th class="align-middle" scope="row">${no++}</th>
            <td><img class="item-image rounded" style="width:100px; height:100px" src="images/uploads/item-photos/${item.item_photo}" alt="image" /></td>
            <td>${item.item_pms}</td>
            <td>${item.expense_accounts.account_name}</td>
            <td>${item.item_name}</td>
            <td>${item.item_unit}</td>
            <td>${item.item_category}</td>
            @if (
                (Auth::user() && Auth::user()->role === 'Super Admin') ||
                    Auth::user()->role === 'Director' ||
                    Auth::user()->role === 'Fleet Admin')
            <td>
                <div class="btn-group" role="group">
                    @if ((Auth::user() && Auth::user()->role === 'Super Admin') || Auth::user()->role === 'Fleet Admin')
                    <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#updateItemModal" data-item='${JSON.stringify(item)}'>
                        <i class="mdi mdi-lead-pencil"></i>
                    </button>
                    @endif
                    @if ((Auth::user() && Auth::user()->role === 'Super Admin') || Auth::user()->role === 'Director')
                    <a href="/deleteItem/${item.id}" class="btn btn-outline-danger btn-delete">
                        <i class="mdi mdi-delete"></i>
                    </a>
                    @endif
                </div>
            </td>
            @endif
        </tr>`;
                                        tableBody.append(row);
                                    });
                                } else {
                                    tableBody.html(
                                        '<tr class="text-center"><td colspan="8" class="text-center text-muted">No records found</td></tr>'
                                    );
                                }
                            },
                            error: function() {
                                tableBody.html(
                                    '<tr class="text-center"><td colspan="6" class="text-center text-danger">Error while searching for items</td></tr>'
                                );
                            }
                        });
                    }
                });
            });
        </script>

        {{-- Check Item PMS --}}
        <script>
            $(document).ready(function() {
                $('#addItemModal input[name="item_pms"], #updateItemModal input[name="item_pms"]').on('keyup',
                    function() {
                        var itemPMS = $(this).val();
                        $.ajax({
                            type: "get",
                            url: "{{ url('check-itemPMS') }}",
                            data: {
                                itemPMS: itemPMS
                            },
                            success: function(response) {
                                if (response.exists) {
                                    $('#addItemModal #validation, #updateItemModal #validation')
                                        .text('Item PMS already used.');
                                    $('#addItemModal button[type=submit], #updateItemModal button[type=submit]')
                                        .attr('disabled', true);
                                } else {
                                    $('#addItemModal #validation, #updateItemModal #validation')
                                        .text('');
                                    $('#addItemModal button[type=submit], #updateItemModal button[type=submit]')
                                        .attr('disabled', false);
                                }
                            },
                            error: function() {
                                alert('Error');
                            }
                        });
                    });
            });
        </script>

        {{-- Add Modal --}}
        <script>
            $(document).ready(function() {
                $('#addItemModal').on('show.bs.modal', function(event) {
                    var accounts = @json($account);

                    function populateAccountDropdown(accounts) {
                        var itemAccountList = $('#addItemModal #itemAccount-list');
                        itemAccountList.empty(); // Kosongkan dropdown sebelum menambahkan item

                        $.each(accounts, function(index, account) {
                            var newLink = $('<a></a>')
                                .addClass('dropdown-item account-dropdown')
                                .attr('href', '#')
                                .attr('data-account-id', account
                                    .id) // Simpan account ID di data attribute
                                .text(account.account_name); // Tampilkan nama akun

                            // Append ke dalam dropdown list
                            itemAccountList.append(newLink);
                        });
                    }
                    populateAccountDropdown(accounts);
                    $('#addItemModal #itemAccount').on('input', function() {
                        var keyword = $(this).val().toLowerCase();
                        $('#addItemModal .dropdown-item').each(function() {
                            var text = $(this).text().toLowerCase();
                            if (text.indexOf(keyword) > -1) {
                                $(this).show();
                            } else {
                                $(this).hide();
                            }
                        });
                        $('#addItemModal .dropdown-menu').addClass('show-dropdown');
                    });
                    $('#addItemModal #itemAccount-list').on('click', '.dropdown-item', function(e) {
                        e.preventDefault();
                        var accountName = $(this).text(); // Ambil nama akun yang dipilih
                        var accountId = $(this).data(
                            'account-id'); // Ambil ID akun dari data-account-id
                        $('#addItemModal #itemAccount').val(
                            accountName); // Set nilai input text dengan nama akun
                        $('#addItemModal #accountID').val(accountId);
                    });
                    $('#addItemModal #newItemCategory').on('change', function() {
                        if ($(this).val() === 'Jasa') {
                            $('#addItemModal #newItemPhoto').removeAttr(
                                'required');
                            $('#addItemModal #span-itemPhoto').attr('hidden',
                                true);
                        } else {
                            $('#addItemModal #newItemPhoto').attr('required',
                                true);
                            $('#addItemModal #span-itemPhoto').attr('hidden',
                                false);
                        }
                    });
                });
            });
        </script>

        {{-- Hidden Modal --}}
        <script>
            $(document).ready(function() {
                $('#addItemModal, #updateItemModal').on('hidden.bs.modal', function(event) {
                    $('#addItemModal #validation, #updateItemModal #validation').text('');
                    $('#addItemModal button[type=submit], #updateItemModal button[type=submit]')
                        .attr('disabled', false);
                });
            });
        </script>

        {{-- Table Sort --}}
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-table/dist/bootstrap-table.min.js"></script>
    @endsection
</div>
