@extends('layouts.layout')
<title>AccountSpends</title>
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
                        <p class="card-title mb-2">Account Spends <span class="text-danger bold small">(*Excl. PPN &
                                PPh)</span>
                        </p>
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
                                                <th data-sortable="true">Account Type</th>
                                                <th data-sortable="true">Account Code</th>
                                                <th data-sortable="true">Account Name</th>
                                                <th data-sortable="true">Total Spend</th>
                                                @if (
                                                    (Auth::user() && Auth::user()->role === 'Super Admin') ||
                                                        Auth::user()->role === 'Director' ||
                                                        Auth::user()->role === 'Fleet Admin')
                                                    <th class="col-1">Detail</th>
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
        {{-- Detail Modal --}}
        <div class="modal fade" id="detailExpenseAccountSpendsModal" tabindex="-1" role="dialog"
            aria-labelledby="detailExpenseAccountSpendsLabel" aria-hidden="true">
            <div class="modal-dialog modal-custom-medium" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="detailExpenseAccountSpendsLabel">Detail Spends</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="display expandable-table table-striped" style="width:100%;" data-toggle="table"
                                data-sortable="true">
                                <thead>
                                    <tr class="text-center">
                                        <th data-sortable="true">No</th>
                                        <th data-sortable="true">PO No</th>
                                        <th data-sortable="true">PMS Code</th>
                                        <th data-sortable="true">Item Name</th>
                                        <th data-sortable="true">Quantity</th>
                                        <th data-sortable="true">Price</th>
                                        <th data-sortable="true">Total</th>
                                        <th data-sortable="true">Transaction Date</th>
                                    </tr>
                                </thead>
                                <tbody>
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
    @endsection
    @section('script')
        {{-- Detail Expense Account Detail Modal --}}
        <script>
            $(document).ready(function() {
                $('#detailExpenseAccountSpendsModal').on('show.bs.modal', function(event) {
                    var button = $(event.relatedTarget);
                    var account = button.data('account');
                    var tableBody = $('#detailExpenseAccountSpendsModal tbody');
                    tableBody.empty();
                    $.ajax({
                        type: "get",
                        url: "{{ url('get-detailAccountSpends') }}", // URL endpoint
                        data: {
                            accountID: account.account_id // Kirim account ID
                        },
                        success: function(response) {
                            if (response.data.length > 0) {
                                var no = 1;
                                response.data.forEach(function(item, index) {
                                    var row = `<tr class="text-center">
                                        <td>${index + 1}</td>
                                        <td>${item.purchase_order_number}</td>
                                        <td>${item.pms_code}</td>
                                        <td>${item.item_name}</td>
                                        <td>${item.quantity}</td>
                                        <td>${new Intl.NumberFormat('id-ID', { style: 'currency', currency: item.currency }).format(item.price)}</td>
                                        <td>${new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(item.amount)}</td>
                                        <td>${new Date(item.transaction_date).toLocaleDateString('id-ID', {
                                            day: '2-digit',
                                            month: '2-digit',
                                            year: 'numeric'
                                        })}</td>
                                    </tr>`;
                                    tableBody.append(row);
                                });
                            } else {
                                // Jika tidak ada data, tampilkan pesan
                                tableBody.html(
                                    '<tr><td colspan="8" class="text-center">No matching records found</td></tr>'
                                );
                            }
                        },
                        error: function() {
                            // Tampilkan pesan error jika AJAX gagal
                            alert('Error fetching data. Please try again.');
                        }
                    });
                });
            });
        </script>

        {{-- Load Search Expense Account Details --}}
        <script>
            $(document).ready(function() {
                var data = @json($data);

                function renderAlldata(filteredData = null) {
                    var tableBody = $('.content-wrapper tbody');
                    tableBody.empty();
                    var renderData = filteredData || data;
                    if (renderData.length > 0) {
                        var no = 1;
                        renderData.forEach(function(account) {
                            var row = `<tr class="text-center" style="height: 50px;">
                                <th class="align-middle" scope="row">${no++}</th>
                                <td>${account.expense_accounts.account_type}</td>
                                <td>${account.expense_accounts.account_code}</td>
                                <td>${account.expense_accounts.account_name}</td>
                                <td>${new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(account.total_spend)}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#detailExpenseAccountSpendsModal" data-account='${JSON.stringify(
                                            account
                                        )}'>
                                            <i class="mdi mdi-eye"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>`;
                            tableBody.append(row);
                        });
                    }
                }

                // Render data saat halaman dimuat pertama kali
                renderAlldata();

                // Fungsi untuk pencarian data
                $('#searchExpenseAccount').on('keyup', function() {
                    var search = $(this).val().toLowerCase(); // Ambil nilai input pencarian
                    if (search.length === 0) {
                        renderAlldata(); // Render ulang semua data jika input kosong
                    } else {
                        // Filter data berdasarkan nama akun, tipe, atau kode akun
                        var filteredData = data.filter(function(account) {
                            return (
                                account.expense_accounts?.account_name.toLowerCase().includes(
                                    search) || // Search by account name
                                account.expense_accounts?.account_type.toLowerCase().includes(
                                    search) || // Search by account type
                                account.expense_accounts?.account_code.toLowerCase().includes(
                                    search) // Search by account code
                            );
                        });
                        renderAlldata(filteredData); // Render data yang telah difilter
                    }
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
