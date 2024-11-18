@extends('layouts.layout')
<title>ExpenseAccounts</title>
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
                        <p class="card-title mb-2">Expense Accounts</p>
                        <div class="input-group mb-3">
                            <input id="searchExpenseAccount" type="text" class="form-control" placeholder="Search...">
                            <div class="input-group-append">
                                <button class="btn btn-sm btn-primary" type="button">Search</button>
                            </div>
                        </div>
                        @if ((Auth::user() && Auth::user()->role === 'Super Admin') || Auth::user()->role === 'Fleet Admin')
                            <div class="input-group mb-3">
                                <button type="button" class="btn btn-success btn-icon-text" data-toggle="modal"
                                    data-target="#addExpenseAccountModal">
                                    <i class="mdi mdi-database-plus pr-2"></i>Add Expense Account
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
                                                <th data-sortable="true">Name</th>
                                                <th data-sortable="true">Type</th>
                                                <th data-sortable="true">Code</th>
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
        <div class="modal fade" id="updateExpenseAccountModal" tabindex="-1" role="dialog"
            aria-labelledby="updateExpenseAccountModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-custom-small" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateExpenseAccountModalLabel">Update Expense Account</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="updateExpenseAccountForm" method="POST" action="{{ url('updateExpenseAccount') }}"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" class="form-control" id="id" name="id" required>
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <label for="accountName"><i class="text-danger">* </i>Account
                                        Name</label>
                                    <input type="text" class="form-control" id="accountName" name="account_name"
                                        required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="accountType"><i class="text-danger">* </i>Account
                                        Type</label>
                                    <select class="form-control" id="accountType" name="account_type" required>
                                        <option value="" selected disabled>-- Select account type --</option>
                                        <option value="COGS">COGS</option>
                                        <option value="EXPS">EXPS</option>
                                        <option value="OEXP">OEXP</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="accountCode"><i class="text-danger">* </i>Account
                                        Code</label>
                                    <input type="text" class="form-control" id="accountCode" name="account_code"
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
        <div class="modal fade" id="addExpenseAccountModal" tabindex="-1" role="dialog"
            aria-labelledby="addExpenseAccountModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-custom-small" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addExpenseAccountModalLabel">Add Expense Account</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="addExpenseAccountForm" method="POST" action="{{ url('addExpenseAccount') }}"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <label for="newAccountName"><i class="text-danger">* </i>Account
                                        Name</label>
                                    <input type="text" class="form-control" id="newAccountName" name="account_name"
                                        required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="newAccountType"><i class="text-danger">* </i>Account
                                        Type</label>
                                    <select class="form-control" id="newAccountType" name="account_type" required>
                                        <option value="" selected disabled>-- Select account type --</option>
                                        <option value="COGS">COGS</option>
                                        <option value="EXPS">EXPS</option>
                                        <option value="OEXP">OEXP</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="newAccountCode"><i class="text-danger">* </i>Account
                                        Code</label>
                                    <input type="text" class="form-control" id="newAccountCode" name="account_code"
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
                $('#updateExpenseAccountModal').on('show.bs.modal', function(event) {
                    var button = $(event.relatedTarget); // Button that triggered the modal
                    var account = button.data('account'); // Extract info from data-* attributes

                    // Update the modal's content.
                    var modal = $(this);
                    modal.find('.modal-body #id').val(account.id);
                    modal.find('.modal-body #accountName').val(account.account_name);
                    modal.find('.modal-body #accountType').val(account.account_type);
                    modal.find('.modal-body #accountCode').val(account.account_code);
                });
            });
        </script>

        {{-- Load Search Expense Account --}}
        <script>
            $(document).ready(function() {
                var accountsData = @json($account);

                function renderAllExpenseAccounts() {
                    var tableBody = $('tbody');
                    tableBody.empty(); // Kosongkan tabel

                    if (accountsData.length > 0) {
                        var no = 1;
                        accountsData.forEach(function(account) {
                            var row = `<tr class="text-center" style="height: 50px;">
            <th class="align-middle" scope="row">${no++}</th>
            <td>${account.account_name}</td>
            <td>${account.account_type}</td>
            <td>${account.account_code}</td>
            @if (
                (Auth::user() && Auth::user()->role === 'Super Admin') ||
                    Auth::user()->role === 'Director' ||
                    Auth::user()->role === 'Fleet Admin')
            <td>
                <div class="btn-group" role="group">
                    @if ((Auth::user() && Auth::user()->role === 'Super Admin') || Auth::user()->role === 'Fleet Admin')
                    <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#updateExpenseAccountModal" data-account='${JSON.stringify(account)}'>
                        <i class="mdi mdi-lead-pencil"></i>
                    </button>
                    @endif
                    @if ((Auth::user() && Auth::user()->role === 'Super Admin') || Auth::user()->role === 'Director')
                    <a href="/deleteExpenseAccount/${account.id}" class="btn btn-outline-danger btn-delete">
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
                renderAllExpenseAccounts();

                $('#searchExpenseAccount').on('keyup', function() {
                    var search = $(this).val();
                    var tableBody = $('tbody');

                    // Jika input kosong, tampilkan kembali semua data kapal
                    if (search.length === 0) {
                        renderAllExpenseAccounts();
                    } else {
                        // Tampilkan pesan "Searching for data" jika ada input pencarian
                        tableBody.html(
                            '<tr class="text-center"><td colspan="8" class="text-center text-muted">Searching for data...</td></tr>'
                        );

                        $.ajax({
                            url: "{{ url('findExpenseAccount') }}", // URL ke route yang menangani pencarian
                            method: 'GET',
                            data: {
                                search: search
                            },
                            success: function(response) {
                                tableBody.empty(); // Kosongkan tabel
                                if (response.length > 0) {
                                    var no = 1;
                                    response.forEach(function(account) {
                                        var row = `<tr class="text-center" style="height: 50px;">
            <th class="align-middle" scope="row">${no++}</th>
            <td>${account.account_name}</td>
            <td>${account.account_type}</td>
            <td>${account.account_code}</td>
            @if (
                (Auth::user() && Auth::user()->role === 'Super Admin') ||
                    Auth::user()->role === 'Director' ||
                    Auth::user()->role === 'Fleet Admin')
            <td>
                <div class="btn-group" role="group">
                    @if ((Auth::user() && Auth::user()->role === 'Super Admin') || Auth::user()->role === 'Fleet Admin')
                    <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#updateExpenseAccountModal" data-account='${JSON.stringify(account)}'>
                        <i class="mdi mdi-lead-pencil"></i>
                    </button>
                    @endif
                    @if ((Auth::user() && Auth::user()->role === 'Super Admin') || Auth::user()->role === 'Director')
                    <a href="/deleteExpenseAccount/${account.id}" class="btn btn-outline-danger btn-delete">
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

        {{-- Check Expense Account PMS --}}
        <script>
            $(document).ready(function() {
                $('#addExpenseAccountModal input[name="account_code"], #updateExpenseAccountModal input[name="account_code"]')
                    .on('keyup',
                        function() {
                            var accountCode = $(this).val();
                            $.ajax({
                                type: "get",
                                url: "{{ url('check-accountCode') }}",
                                data: {
                                    accountCode: accountCode
                                },
                                success: function(response) {
                                    if (response.exists) {
                                        $('#addExpenseAccountModal #validation, #updateExpenseAccountModal #validation')
                                            .text('Account code already used.');
                                        $('#addExpenseAccountModal button[type=submit], #updateExpenseAccountModal button[type=submit]')
                                            .attr('disabled', true);
                                    } else {
                                        $('#addExpenseAccountModal #validation, #updateExpenseAccountModal #validation')
                                            .text('');
                                        $('#addExpenseAccountModal button[type=submit], #updateExpenseAccountModal button[type=submit]')
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

        {{-- Hidden Modal --}}
        <script>
            $(document).ready(function() {
                $('#addExpenseAccountModal, #updateExpenseAccountModal').on('hidden.bs.modal', function(event) {
                    $('#addExpenseAccountModal #validation, #updateExpenseAccountModal #validation').text('');
                    $('#addExpenseAccountModal button[type=submit], #updateExpenseAccountModal button[type=submit]')
                        .attr('disabled', false);
                });
            });
        </script>

        {{-- Table Sort --}}
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-table/dist/bootstrap-table.min.js"></script>
    @endsection
</div>
