@extends('layouts.layout')
<title>Services</title>
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
                        <p class="card-title mb-2">Services</p>
                        <div class="input-group mb-3">
                            <input id="searchService" type="text" class="form-control" placeholder="Search...">
                            <div class="input-group-append">
                                <button class="btn btn-sm btn-primary" type="button">Search</button>
                            </div>
                        </div>
                        @if ((Auth::user() && Auth::user()->role === 'Super Admin') || Auth::user()->role === 'Fleet Admin')
                            <div class="input-group mb-3">
                                <button type="button" class="btn btn-success btn-icon-text" data-toggle="modal"
                                    data-target="#addServiceModal">
                                    <i class="mdi mdi-database-plus pr-2"></i>Add Service
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
                                                <th data-sortable="true">Account</th>
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
        <div class="modal fade" id="updateServiceModal" tabindex="-1" role="dialog"
            aria-labelledby="updateServiceModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-custom-small" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateServiceModalLabel">Update Service</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="updateServiceForm" method="POST" action="{{ url('updateService') }}"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" class="form-control" id="id" name="id" required>
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <label for="serviceName"><i class="text-danger">* </i>Service Name</label>
                                    <input type="text" class="form-control" id="serviceName" name="service_name"
                                        required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="accountID"><span class="text-danger">*</span>Service Account</label>
                                    <div class="serviceAccount-dropdown">
                                        <input type="text" class="form-control" autocomplete="off" data-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false" id="serviceAccount">
                                        <input required type="hidden" id="accountID" name="account_id">
                                        <div id="serviceAccount-list" class="dropdown-menu"
                                            aria-labelledby="serviceAccount">
                                            <!-- Service account will be appended here -->
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="serviceCode"><i class="text-danger">* </i>Service Code</label>
                                    <input type="text" class="form-control" id="serviceCode" name="service_code"
                                        required>
                                    <div id="validation" class="text-danger"></div>
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
        <div class="modal fade" id="addServiceModal" tabindex="-1" role="dialog"
            aria-labelledby="addServiceModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-custom-small" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addServiceModalLabel">Add Service</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="addServiceForm" method="POST" action="{{ url('addService') }}"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <label for="newServiceName"><i class="text-danger">* </i>Service Name</label>
                                    <input type="text" class="form-control" id="newServiceName" name="service_name"
                                        required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="accountID"><span class="text-danger">*</span>Service Account</label>
                                    <div class="serviceAccount-dropdown">
                                        <input type="text" class="form-control" autocomplete="off"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                            id="serviceAccount">
                                        <input required type="hidden" id="accountID" name="account_id">
                                        <div id="serviceAccount-list" class="dropdown-menu"
                                            aria-labelledby="serviceAccount">
                                            <!-- Service account will be appended here -->
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="newServiceCode"><i class="text-danger">* </i>Service Code</label>
                                    <input type="text" class="form-control" id="newServiceCode" name="service_code"
                                        required>
                                    <div id="validation" class="text-danger"></div>
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
                $('#updateServiceModal').on('show.bs.modal', function(event) {
                    var button = $(event.relatedTarget); // Button that triggered the modal
                    var service = button.data('service'); // Extract info from data-* attributes

                    // Update the modal's content.
                    var modal = $(this);
                    modal.find('.modal-body #id').val(service.id);
                    modal.find('.modal-body #serviceName').val(service.service_name);
                    modal.find('.modal-body #serviceAccount').val(service.expense_accounts.account_name);
                    modal.find('.modal-body #serviceCode').val(service.service_code);

                    var accounts = @json($accounts);

                    function populateAccountDropdown(accounts) {
                        var serviceAccountList = $('#updateServiceModal #serviceAccount-list');
                        serviceAccountList.empty(); // Kosongkan dropdown sebelum menambahkan service

                        $.each(accounts, function(index, account) {
                            var newLink = $('<a></a>')
                                .addClass(
                                    'dropdown-item') // Menggunakan class dropdown-item sesuai Bootstrap
                                .attr('href', '#')
                                .attr('data-account-id', account
                                    .id) // Simpan account ID di data attribute
                                .text(account.account_name); // Tampilkan nama akun

                            // Append ke dalam dropdown list
                            serviceAccountList.append(newLink);

                        });
                    }
                    populateAccountDropdown(accounts);
                    $('#updateServiceModal #serviceAccount').on('input', function() {
                        var keyword = $(this).val().toLowerCase();
                        $('#updateServiceModal .dropdown-item').each(
                            function() { // Menggunakan class dropdown-item
                                var text = $(this).text().toLowerCase();
                                if (text.indexOf(keyword) > -1) {
                                    $(this).show();
                                } else {
                                    $(this).hide();
                                }
                            });
                        $('#updateServiceModal .dropdown-menu').addClass(
                            'show'); // Menggunakan class show dari Bootstrap
                    });

                    $('#updateServiceModal #serviceAccount-list').on('click', '.dropdown-item', function(e) {
                        e.preventDefault();
                        var accountName = $(this).text(); // Ambil nama akun yang dipilih
                        var accountId = $(this).data(
                            'account-id'); // Ambil ID akun dari data-account-id
                        $('#updateServiceModal #serviceAccount').val(
                            accountName); // Set nilai input text dengan nama akun
                        $('#updateServiceModal #accountID').val(
                            accountId); // Set nilai hidden input dengan account ID
                    });
                });
            });
        </script>

        {{-- Load Search Service --}}
        <script>
            $(document).ready(function() {
                var servicesData = @json($services);

                function renderAllServices() {
                    var tableBody = $('tbody');
                    tableBody.empty(); // Kosongkan tabel

                    if (servicesData.length > 0) {
                        var no = 1;
                        servicesData.forEach(function(service) {
                            var row = `<tr class="text-center" style="height: 50px;">
            <th class="align-middle" scope="row">${no++}</th>
            <td>${service.service_code}</td>
            <td>${service.service_name}</td>
            <td>${service.expense_accounts.account_name}</td>
            @if (
                (Auth::user() && Auth::user()->role === 'Super Admin') ||
                    Auth::user()->role === 'Director' ||
                    Auth::user()->role === 'Fleet Admin')
            <td>
                <div class="btn-group" role="group">
                    @if ((Auth::user() && Auth::user()->role === 'Super Admin') || Auth::user()->role === 'Fleet Admin')
                    <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#updateServiceModal" data-service='${JSON.stringify(service)}'>
                        <i class="mdi mdi-lead-pencil"></i>
                    </button>
                    @endif
                    @if ((Auth::user() && Auth::user()->role === 'Super Admin') || Auth::user()->role === 'Director')
                    <a href="/deleteService/${service.id}" class="btn btn-outline-danger btn-delete">
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
                            // '<tr class="text-center"><td colspan="8" class="text-center text-muted">No records found</td></tr>'
                        );
                    }

                }

                // Tampilkan data semua kapal saat halaman dimuat pertama kali
                renderAllServices();

                $('#searchService').on('keyup', function() {
                    var search = $(this).val();
                    var tableBody = $('tbody');

                    // Jika input kosong, tampilkan kembali semua data kapal
                    if (search.length === 0) {
                        renderAllServices();
                    } else {
                        // Tampilkan pesan "Searching for data" jika ada input pencarian
                        tableBody.html(
                            '<tr class="text-center"><td colspan="12" class="text-center text-muted">Searching for data...</td></tr>'
                        );

                        $.ajax({
                            url: "{{ url('findService') }}", // URL ke route yang menangani pencarian
                            method: 'GET',
                            data: {
                                search: search
                            },
                            success: function(response) {
                                tableBody.empty(); // Kosongkan tabel
                                if (response.length > 0) {
                                    var no = 1;
                                    response.forEach(function(service) {
                                        var row = `<tr class="text-center" style="height: 50px;">
            <th class="align-middle" scope="row">${no++}</th>
            <td>${service.service_code}</td>
            <td>${service.service_name}</td>
            <td>${service.expense_accounts.account_name}</td>
            @if (
                (Auth::user() && Auth::user()->role === 'Super Admin') ||
                    Auth::user()->role === 'Director' ||
                    Auth::user()->role === 'Fleet Admin')
            <td>
                <div class="btn-group" role="group">
                    @if ((Auth::user() && Auth::user()->role === 'Super Admin') || Auth::user()->role === 'Fleet Admin')
                    <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#updateServiceModal" data-service='${JSON.stringify(service)}'>
                        <i class="mdi mdi-lead-pencil"></i>
                    </button>
                    @endif
                    @if ((Auth::user() && Auth::user()->role === 'Super Admin') || Auth::user()->role === 'Director')
                    <a href="/deleteService/${service.id}" class="btn btn-outline-danger btn-delete">
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
                                    '<tr class="text-center"><td colspan="6" class="text-center text-danger">Error while searching for services</td></tr>'
                                );
                            }
                        });
                    }
                });
            });
        </script>

        {{-- Check Service Code --}}
        <script>
            $(document).ready(function() {
                $('#addServiceModal input[name="service_code"], #updateServiceModal input[name="service_code"]').on(
                    'keyup',
                    function() {
                        var serviceCode = $(this).val();
                        $.ajax({
                            type: "get",
                            url: "{{ url('check-serviceCode') }}",
                            data: {
                                serviceCode: serviceCode
                            },
                            success: function(response) {
                                if (response.exists) {
                                    $('#addServiceModal #validation, #updateServiceModal #validation')
                                        .text('Service code already used.');
                                    $('#addServiceModal button[type=submit], #updateServiceModal button[type=submit]')
                                        .attr('disabled', true);
                                } else {
                                    $('#addServiceModal #validation, #updateServiceModal #validation')
                                        .text('');
                                    $('#addServiceModal button[type=submit], #updateServiceModal button[type=submit]')
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
                $('#addServiceModal').on('show.bs.modal', function(event) {
                    var accounts = @json($accounts);

                    function populateAccountDropdown(accounts) {
                        var serviceAccountList = $('#addServiceModal #serviceAccount-list');
                        serviceAccountList.empty(); // Kosongkan dropdown sebelum menambahkan service

                        $.each(accounts, function(index, account) {
                            var newLink = $('<a></a>')
                                .addClass(
                                    'dropdown-item') // Menggunakan class dropdown-item sesuai Bootstrap
                                .attr('href', '#')
                                .attr('data-account-id', account
                                    .id) // Simpan account ID di data attribute
                                .text(account.account_name); // Tampilkan nama akun

                            // Append ke dalam dropdown list
                            serviceAccountList.append(newLink);

                        });
                    }
                    populateAccountDropdown(accounts);

                    $('#addServiceModal #serviceAccount').on('input', function() {
                        var keyword = $(this).val().toLowerCase();
                        $('#addServiceModal .dropdown-item').each(
                            function() { // Menggunakan class dropdown-item
                                var text = $(this).text().toLowerCase();
                                if (text.indexOf(keyword) > -1) {
                                    $(this).show();
                                } else {
                                    $(this).hide();
                                }
                            });
                        $('#addServiceModal .dropdown-menu').addClass(
                            'show'); // Menggunakan class show dari Bootstrap
                    });

                    $('#addServiceModal #serviceAccount-list').on('click', '.dropdown-item', function(e) {
                        e.preventDefault();
                        var accountName = $(this).text(); // Ambil nama akun yang dipilih
                        var accountId = $(this).data(
                            'account-id'); // Ambil ID akun dari data-account-id
                        $('#addServiceModal #serviceAccount').val(
                            accountName); // Set nilai input text dengan nama akun
                        $('#addServiceModal #accountID').val(
                            accountId); // Set nilai hidden input dengan account ID
                    });
                });
            });
        </script>

        {{-- Hidden Modal --}}
        <script>
            $(document).ready(function() {
                $('#addServiceModal, #updateServiceModal').on('hidden.bs.modal', function(event) {
                    $('#addServiceModal #validation, #updateServiceModal #validation').text('');
                    $('#addServiceModal button[type=submit], #updateServiceModal button[type=submit]')
                        .attr('disabled', false);
                });
            });
        </script>

        {{-- Table Sort --}}
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-table/dist/bootstrap-table.min.js"></script>
    @endsection
</div>
