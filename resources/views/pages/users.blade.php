@extends('layouts.layout')
<title>Users</title>
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
                        <p class="card-title mb-2">Users</p>
                        <div class="row">
                            <div class="col-12">
                                <div class="table-responsive" style="height: calc(100vh - 160px);">
                                    <table class="display expandable-table table-striped" style="width:100%;"
                                        data-toggle="table" data-sortable="true">
                                        <thead>
                                            <tr class="text-center">
                                                <th data-sortable="true">No</th>
                                                <th>Photo</th>
                                                <th data-sortable="true">Name</th>
                                                <th data-sortable="true">Email</th>
                                                <th data-sortable="true">Role</th>
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
    @endsection
    @section('script')
        {{-- Load Search User --}}
        <script>
            $(document).ready(function() {
                // Menyimpan data kapal yang sudah dilempar dari server ke variabel JavaScript
                var userData = @json($users);

                // Fungsi untuk menampilkan semua data kapal
                function renderAllUsers() {
                    var tableBody = $('tbody');
                    tableBody.empty(); // Kosongkan tabel

                    if (userData.length > 0) {
                        var no = 1;
                        userData.forEach(function(user) {
                            var row = `<tr class="text-center">
            <th class="align-middle" scope="row">${no++}</th>
            <td><img class="user-image rounded" style="width:100px; height:100px" src="images/uploads/user-photos/${user.profile_photo}" alt="image" /></td>
            <td>${user.name}</td>
            <td>${user.email}</td>
            <td>${user.role}</td>
        </tr>`;
                            tableBody.append(row);
                        });
                    } else {
                        tableBody.html(
                            '<tr class="text-center"><td colspan="6" class="text-center text-muted">No Users found</td></tr>'
                        );
                    }
                }
                // Tampilkan data semua kapal saat halaman dimuat pertama kali
                renderAllUsers();
                $('#searchUser').on('keyup', function() {
                    var search = $(this).val();
                    var tableBody = $('tbody');
                    // Jika input kosong, tampilkan kembali semua data kapal
                    if (search.length === 0) {
                        renderAllUsers();
                    } else {
                        // Tampilkan pesan "Searching for data" jika ada input pencarian
                        tableBody.html(
                            '<tr class="text-center"><td colspan="6" class="text-center text-muted">Searching for data...</td></tr>'
                        );
                    }
                });
            });
        </script>

        {{-- Table Sort --}}
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-table/dist/bootstrap-table.min.js"></script>
    @endsection
</div>
