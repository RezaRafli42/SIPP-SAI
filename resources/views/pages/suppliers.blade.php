@extends('layouts.layout')
<title>Suppliers</title>
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
                        <p class="card-title mb-2">Suppliers</p>
                        <div class="input-group mb-3">
                            <input id="searchSupplier" type="text" class="form-control" placeholder="Search...">
                            <div class="input-group-append">
                                <button class="btn btn-sm btn-primary" type="button">Search</button>
                            </div>
                        </div>
                        @if ((Auth::user() && Auth::user()->role === 'Super Admin') || Auth::user()->role === 'Purchasing Logistic Admin')
                            <div class="input-group mb-3">
                                <button type="button" class="btn btn-success btn-icon-text" data-toggle="modal"
                                    data-target="#addSupplierModal">
                                    <i class="mdi mdi-database-plus pr-2"></i>Add Supplier
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
                                                <th data-sortable="true">Code</th>
                                                <th data-sortable="true">Name</th>
                                                <th data-sortable="true">City</th>
                                                <th>Contact</th>
                                                <th data-sortable="true">Category</th>
                                                @if (
                                                    (Auth::user() && Auth::user()->role === 'Super Admin') ||
                                                        Auth::user()->role === 'Director' ||
                                                        Auth::user()->role === 'Purchasing Logistic Admin')
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
        {{-- Add Modal --}}
        <div class="modal fade" id="addSupplierModal" tabindex="-1" role="dialog" aria-labelledby="addSupplierModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addSupplierModalLabel">Add Supplier</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="addSupplierForm" method="POST" action="{{ url('addSupplier') }}"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="newSupplierCode"><i class="text-danger">* </i>Supplier Code</label>
                                    <input type="text" class="form-control" id="newSupplierCode" name="supplier_code"
                                        required readonly value="{{ $supplier_code }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="newSupplierName"><i class="text-danger">* </i>Supplier Name</label>
                                    <input type="text" class="form-control" id="newSupplierName" name="supplier_name"
                                        required>
                                </div>

                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="newSupplierCity">Supplier City</label>
                                    <input type="text" class="form-control" id="newSupplierCity" name="supplier_city">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="newSupplierCountry">Supplier Country</label>
                                    <input type="text" class="form-control" id="newSupplierCountry"
                                        name="supplier_country">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="newSupplierEmail">Supplier Email</label>
                                    <input type="text" class="form-control" id="newSupplierEmail" name="supplier_email">
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="newSupplierContact">Supplier Contact</label>
                                    <input type="text" class="form-control" id="newSupplierContact"
                                        name="supplier_contact">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="newSupplierCurrency"><i class="text-danger">* </i>Supplier
                                        Currency</label>
                                    <br>
                                    <select class="form-control selectpicker" id="newSupplierCurrency"
                                        name="supplier_currency[]" multiple data-max-options="2" required>
                                        <option value="IDR">IDR</option>
                                        <option value="USD">USD</option>
                                        <option value="JPY">JPY</option>
                                        <option value="EUR">EUR</option>
                                        <option value="AUD">AUD</option>
                                        <option value="CNY">CNY</option>
                                        <option value="KRW">KRW</option>
                                        <option value="AUD">AUD</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="newSupplierCategory"><i class="text-danger">* </i>Supplier
                                        Category</label>
                                    <select class="form-control" id="newSupplierCategory" name="supplier_category"
                                        required>
                                        <option disabled selected value="">-- Select supplier category --</option>
                                        <option value="Umum">Umum</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-12">
                                    <label for="newSupplierAddress">Supplier Address</label>
                                    <textarea type="text" class="form-control" id="newSupplierAddress" name="supplier_address"></textarea>
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

        {{-- Update Modal --}}
        <div class="modal fade" id="updateSupplierModal" tabindex="-1" role="dialog"
            aria-labelledby="updateSupplierModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateSupplierModalLabel">Update Supplier</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="updateSupplierForm" method="POST" action="{{ url('updateSupplier') }}"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" name="id" id="id">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="supplierCode"><i class="text-danger">* </i>Supplier Code</label>
                                    <input type="text" class="form-control" id="supplierCode" name="supplier_code"
                                        required readonly>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="supplierName"><i class="text-danger">* </i>Supplier Name</label>
                                    <input type="text" class="form-control" id="supplierName" name="supplier_name"
                                        required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="supplierCity">Supplier City</label>
                                    <input type="text" class="form-control" id="supplierCity" name="supplier_city">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="supplierCountry">Supplier Country</label>
                                    <input type="text" class="form-control" id="supplierCountry"
                                        name="supplier_country">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="supplierEmail">Supplier Email</label>
                                    <input type="text" class="form-control" id="supplierEmail" name="supplier_email">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="supplierContact">Supplier Contact</label>
                                    <input type="text" class="form-control" id="supplierContact"
                                        name="supplier_contact">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="supplierCurrency"><i class="text-danger">* </i>Supplier Currency</label>
                                    <br><select class="form-control selectpicker" id="supplierCurrency"
                                        name="supplier_currency[]" multiple data-max-options="2" required>
                                        <option value="IDR">IDR</option>
                                        <option value="USD">USD</option>
                                        <option value="JPY">JPY</option>
                                        <option value="EUR">EUR</option>
                                        <option value="AUD">AUD</option>
                                        <option value="CNY">CNY</option>
                                        <option value="KRW">KRW</option>
                                        <option value="AUD">AUD</option>
                                        <!-- Add more currency options as needed -->
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="supplierCategory"><i class="text-danger">* </i>Supplier Category</label>
                                    <select class="form-control" id="supplierCategory" name="supplier_category" required>
                                        <option disabled selected value="">-- Select supplier category --</option>
                                        <option value="Umum">Umum</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-row">
                                <label for="supplierAddress">Supplier Address</label>
                                <textarea type="text" class="form-control" id="supplierAddress" name="supplier_address"></textarea>
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
                $('#updateSupplierModal').on('show.bs.modal', function(event) {
                    var button = $(event.relatedTarget); // Button that triggered the modal
                    var supplier = button.data('supplier'); // Extract info from data-* attributes

                    // Update the modal's content.
                    var modal = $(this);
                    modal.find('.modal-body #id').val(supplier.id);
                    modal.find('.modal-body #supplierCode').val(supplier.supplier_code);
                    modal.find('.modal-body #supplierName').val(supplier.supplier_name);
                    modal.find('.modal-body #supplierCity').val(supplier.supplier_city);
                    modal.find('.modal-body #supplierCountry').val(supplier.supplier_country);
                    modal.find('.modal-body #supplierAddress').val(supplier.supplier_address);
                    modal.find('.modal-body #supplierContact').val(supplier.supplier_contact);
                    modal.find('.modal-body #supplierEmail').val(supplier.supplier_email);
                    modal.find('.modal-body #supplierCategory').val(supplier.supplier_category);
                    modal.find('.modal-body #supplierCurrency').selectpicker('val', supplier.supplier_currency
                        .split(', '));
                });
            });
        </script>

        {{-- Load Search Supplier --}}
        <script>
            $(document).ready(function() {
                var suppliersData = @json($suppliers);

                function renderAllSuppliers() {
                    var tableBody = $('tbody');
                    tableBody.empty(); // Kosongkan tabel

                    if (suppliersData.length > 0) {
                        var no = 1;
                        suppliersData.forEach(function(supplier) {
                            var row = `<tr class="text-center">
            <th class="align-middle" scope="row">${no++}</th>
            <td>${supplier.supplier_code}</td>
            <td>${supplier.supplier_name}</td>
            <td>${supplier.supplier_city}</td>
            <td>${supplier.supplier_contact}</td>
            <td>${supplier.supplier_category}</td>
            @if (
                (Auth::user() && Auth::user()->role === 'Super Admin') ||
                    Auth::user()->role === 'Director' ||
                    Auth::user()->role === 'Purchasing Logistic Admin')
            <td>
                <div class="btn-group" role="group">
                    @if ((Auth::user() && Auth::user()->role === 'Super Admin') || Auth::user()->role === 'Purchasing Logistic Admin')
                    <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#updateSupplierModal" data-supplier='${JSON.stringify(supplier)}'>
                        <i class="mdi mdi-lead-pencil"></i>
                    </button>
                    @endif
                    @if ((Auth::user() && Auth::user()->role === 'Super Admin') || Auth::user()->role === 'Director')
                    <a href="/deleteSupplier/${supplier.id}" class="btn btn-outline-danger btn-delete">
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
                            '<tr class="text-center"><td colspan="6" class="text-center text-muted">No records found</td></tr>'
                        );
                    }

                }

                // Tampilkan data semua kapal saat halaman dimuat pertama kali
                renderAllSuppliers();

                $('#searchSupplier').on('keyup', function() {
                    var search = $(this).val();
                    var tableBody = $('tbody');

                    // Jika input kosong, tampilkan kembali semua data kapal
                    if (search.length === 0) {
                        renderAllSuppliers();
                    } else {
                        // Tampilkan pesan "Searching for data" jika ada input pencarian
                        tableBody.html(
                            '<tr class="text-center"><td colspan="6" class="text-center text-muted">Searching for data...</td></tr>'
                        );

                        $.ajax({
                            url: "{{ url('findSupplier') }}", // URL ke route yang menangani pencarian
                            method: 'GET',
                            data: {
                                search: search
                            },
                            success: function(response) {
                                tableBody.empty(); // Kosongkan tabel
                                if (response.length > 0) {
                                    var no = 1;
                                    response.forEach(function(supplier) {
                                        var row = `<tr class="text-center">
            <th class="align-middle" scope="row">${no++}</th>
            <td>${supplier.supplier_code}</td>
            <td>${supplier.supplier_name}</td>
            <td>${supplier.supplier_city}</td>
            <td>${supplier.supplier_contact}</td>
            <td>${supplier.supplier_category}</td>
            @if (
                (Auth::user() && Auth::user()->role === 'Super Admin') ||
                    Auth::user()->role === 'Director' ||
                    Auth::user()->role === 'Purchasing Logistic Admin')
            <td>
                <div class="btn-group" role="group">
                    @if ((Auth::user() && Auth::user()->role === 'Super Admin') || Auth::user()->role === 'Purchasing Logistic Admin')
                    <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#updateSupplierModal" data-supplier='${JSON.stringify(supplier)}'>
                        <i class="mdi mdi-lead-pencil"></i>
                    </button>
                    @endif
                    @if ((Auth::user() && Auth::user()->role === 'Super Admin') || Auth::user()->role === 'Director')
                    <a href="/deleteSupplier/${supplier.id}" class="btn btn-outline-danger btn-delete">
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
                                        '<tr class="text-center"><td colspan="6" class="text-center text-muted">No records found</td></tr>'
                                    );
                                }
                            },
                            error: function() {
                                tableBody.html(
                                    '<tr class="text-center"><td colspan="6" class="text-center text-danger">Error while searching for suppliers</td></tr>'
                                );
                            }
                        });
                    }
                });
            });
        </script>

        {{-- Check Supplier Code --}}
        <script>
            $(document).ready(function() {
                $('#addSupplierModal input[name="supplier_code"], #updateSupplierModal input[name="supplier_code"]').on(
                    'keyup',
                    function() {
                        var supplierCode = $(this).val();
                        $.ajax({
                            type: "get",
                            url: "{{ url('check-supplierCode') }}",
                            data: {
                                supplierCode: supplierCode
                            },
                            success: function(response) {
                                if (response.exists) {
                                    $('#addSupplierModal #validation, #updateSupplierModal #validation')
                                        .text('Supplier Code already used.');
                                    $('#addSupplierModal button[type=submit], #updateSupplierModal button[type=submit]')
                                        .attr('disabled', true);
                                } else {
                                    $('#addSupplierModal #validation, #updateSupplierModal #validation')
                                        .text('');
                                    $('#addSupplierModal button[type=submit], #updateSupplierModal button[type=submit]')
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

        {{-- Table Sort --}}
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-table/dist/bootstrap-table.min.js"></script>
    @endsection
</div>
