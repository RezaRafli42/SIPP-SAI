@extends('layouts.layout')
<title>Ships</title>
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
                        <p class="card-title mb-2">Ships</p>
                        <div class="input-group mb-3">
                            <input id="searchShip" type="text" class="form-control" placeholder="Search...">
                            <div class="input-group-append">
                                <button class="btn btn-sm btn-primary" type="button">Search</button>
                            </div>
                        </div>
                        @if (Auth::user() && Auth::user()->role === 'Super Admin')
                            <div class="input-group mb-3">
                                <button type="button" class="btn btn-success btn-icon-text" data-toggle="modal"
                                    data-target="#addShipModal">
                                    <i class="mdi mdi-database-plus pr-2"></i>Add Ship
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
                                                <th data-sortable="true">Name</th>
                                                <th data-sortable="true">Position</th>
                                                <th data-sortable="true">Type</th>
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
        <div class="modal fade" id="updateShipModal" tabindex="-1" role="dialog" aria-labelledby="updateShipModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateShipModalLabel">Update Ship</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="updateShipForm" method="POST" action="{{ url('updateShip') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" name="id" id="id">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-8 col-sm-6">
                                        <label for="shipName"><i class="text-danger">* </i>Ship Name</label>
                                        <input type="text" class="form-control" id="shipName" name="ship_name" required>
                                    </div>
                                    <div class="col-8 col-sm-6">
                                        <label for="shipType"><i class="text-danger">* </i>Ship Type</label>
                                        <select class="form-control" id="shipType" name="ship_type" required>
                                            <option disabled selected value="">-- Select Type --</option>
                                            <option value="TSHD">TSHD</option>
                                            <option value="CSD">CSD</option>
                                            <option value="GD">GD</option>
                                            <option value="AE">AE</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="shipPosition">Ship Position</label>
                                <input type="text" class="form-control" id="shipPosition" name="ship_position">
                            </div>
                            <div class="form-group">
                                <label for="shipPhoto"><i class="text-danger">* </i>Photo</label>
                                <input type="file" class="form-control" id="shipPhoto" name="ship_photo">
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
        <div class="modal fade" id="addShipModal" tabindex="-1" role="dialog" aria-labelledby="addShipModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addShipModalLabel">Add Ship</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="addShipForm" method="POST" action="{{ url('addShip') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-8 col-sm-6">
                                        <label for="newShipName"><i class="text-danger">* </i>Ship Name</label>
                                        <input type="text" class="form-control" id="newShipName" name="ship_name"
                                            required>
                                    </div>
                                    <div class="col-8 col-sm-6">
                                        <label for="newShipType"><i class="text-danger">* </i>Ship Type</label>
                                        <select class="form-control" id="newShipType" name="ship_type" required>
                                            <option disabled selected value="">-- Select Type --</option>
                                            <option value="TSHD">TSHD</option>
                                            <option value="CSD">CSD</option>
                                            <option value="GD">GD</option>
                                            <option value="AE">AE</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="newShipPosition">Ship Position</label>
                                <input type="text" class="form-control" id="newShipPosition" name="ship_position">
                            </div>
                            <div class="form-group">
                                <label for="newShipPhoto"><i class="text-danger">* </i>Photo</label>
                                <input type="file" class="form-control" id="newShipPhoto" name="ship_photo" required>
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
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        {{-- Update Modal --}}
        <script>
            $(document).ready(function() {
                $('#updateShipModal').on('show.bs.modal', function(event) {
                    var button = $(event.relatedTarget); // Button that triggered the modal
                    var ship = button.data('ship'); // Extract info from data-* attributes

                    // Update the modal's content.
                    var modal = $(this);
                    modal.find('.modal-body #id').val(ship.id);
                    modal.find('.modal-body #shipName').val(ship.ship_name);
                    modal.find('.modal-body #shipType').val(ship.ship_type);
                    modal.find('.modal-body #shipPosition').val(ship.ship_position);
                });
            });
        </script>

        {{-- Load Search Ship --}}
        <script>
            $(document).ready(function() {
                // Menyimpan data kapal yang sudah dilempar dari server ke variabel JavaScript
                var shipsData = @json($ships);

                // Fungsi untuk menampilkan semua data kapal
                function renderAllShips() {
                    var tableBody = $('tbody');
                    tableBody.empty(); // Kosongkan tabel

                    if (shipsData.length > 0) {
                        var no = 1;
                        shipsData.forEach(function(ship) {
                            var row = `<tr class="text-center">
            <th class="align-middle" scope="row">${no++}</th>
            <td><img class="ship-image rounded" style="width:100px; height:100px" src="images/uploads/ship-photos/${ship.ship_photo}" alt="image" /></td>
            <td>${ship.ship_name}</td>
            <td>${ship.ship_position}</td>
            <td>${ship.ship_type}</td>
            @if (
                (Auth::user() && Auth::user()->role === 'Super Admin') ||
                    Auth::user()->role === 'Director' ||
                    Auth::user()->role === 'Fleet Admin')
            <td>
                <div class="btn-group" role="group">
                    @if ((Auth::user() && Auth::user()->role === 'Super Admin') || Auth::user()->role === 'Fleet Admin')
                    <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#updateShipModal" data-ship='${JSON.stringify(ship)}'>
                        <i class="mdi mdi-lead-pencil"></i>
                    </button>
                    @endif
                    @if ((Auth::user() && Auth::user()->role === 'Super Admin') || Auth::user()->role === 'Director')
                    <a href="/deleteShip/${ship.id}" class="btn btn-outline-danger btn-delete">
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
                renderAllShips();

                $('#searchShip').on('keyup', function() {
                    var search = $(this).val();
                    var tableBody = $('tbody');

                    // Jika input kosong, tampilkan kembali semua data kapal
                    if (search.length === 0) {
                        renderAllShips();
                    } else {
                        // Tampilkan pesan "Searching for data" jika ada input pencarian
                        tableBody.html(
                            '<tr class="text-center"><td colspan="6" class="text-center text-muted">Searching for data...</td></tr>'
                        );

                        $.ajax({
                            url: "{{ url('findShip') }}", // URL ke route yang menangani pencarian
                            method: 'GET',
                            data: {
                                search: search
                            },
                            success: function(response) {
                                tableBody.empty(); // Kosongkan tabel
                                if (response.length > 0) {
                                    var no = 1;
                                    response.forEach(function(ship) {
                                        var row = `<tr class="text-center">
                                        <th class="align-middle" scope="row">${no++}</th>
                                        <td><img class="ship-image rounded" style="width:100px; height:100px" src="images/uploads/ship-photos/${ship.ship_photo}" alt="image" /></td>
                                        <td>${ship.ship_name}</td>
                                        <td>${ship.ship_position}</td>
                                        <td>${ship.ship_type}</td>
                                        @if (
                                            (Auth::user() && Auth::user()->role === 'Super Admin') ||
                                                Auth::user()->role === 'Director' ||
                                                Auth::user()->role === 'Fleet Admin')
            <td>
                <div class="btn-group" role="group">
                    @if ((Auth::user() && Auth::user()->role === 'Super Admin') || Auth::user()->role === 'Fleet Admin')
                    <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#updateShipModal" data-ship='${JSON.stringify(ship)}'>
                        <i class="mdi mdi-lead-pencil"></i>
                    </button>
                    @endif
                    @if ((Auth::user() && Auth::user()->role === 'Super Admin') || Auth::user()->role === 'Director')
                    <a href="/deleteShip/${ship.id}" class="btn btn-outline-danger btn-delete">
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
                                    '<tr class="text-center"><td colspan="6" class="text-center text-danger">Error while searching for ships</td></tr>'
                                );
                            }
                        });
                    }
                });
            });
        </script>

        {{-- Table Sort --}}
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-table/dist/bootstrap-table.min.js"></script>
    @endsection
</div>
