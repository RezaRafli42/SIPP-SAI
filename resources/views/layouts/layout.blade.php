<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=1024, height=10, initial-scale=0.1">
    <title>Purchase Plan SAI</title>
    {{-- CSS Table Sort --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-table/dist/bootstrap-table.min.css">
    <!-- plugins:css -->
    <link rel="stylesheet" href="vendors/feather/feather.css">
    <link rel="stylesheet" href="vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->

    <link rel="stylesheet" href="vendors/ti-icons/css/themify-icons.css">
    {{-- <link rel="stylesheet" type="text/css" href="js/select.dataTables.min.css"> --}}
    <link rel="stylesheet" href="../../vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="../../vendors/select2/select2.min.css">
    <link rel="stylesheet" href="../../vendors/select2-bootstrap-theme/select2-bootstrap.min.css">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <link rel="stylesheet" href="css/vertical-layout-light/style.css">
    <!-- endinject -->
    <link rel="shortcut icon" href="images/logo-sai.png" />
    {{-- CSS Pribadi --}}
    <link rel="stylesheet" href="css/pages/pages.css">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.18/css/bootstrap-select.min.css">
</head>

<body class="sidebar-icon-only">
    <div class="container-scroller">
        <!-- partial:partials/_navbar.html -->
        <nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
            <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
                <a class="navbar-brand brand-logo mr-5" href="dashboard"><img src="images/logo-sai.png" class="mr-2"
                        alt="logo" /></a>
                <a class="navbar-brand brand-logo-mini" href="dashboard"><img src="images/logo-sai.png"
                        alt="logo" /></a>
            </div>
            <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
                <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
                    <span class="icon-menu"></span>
                </button>
                <ul class="navbar-nav navbar-nav-right">
                    <li class="nav-item nav-profile dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" id="profileDropdown">
                            <img src="{{ Auth::user()->profile_photo ? asset('images/uploads/user-photos/' . Auth::user()->profile_photo) : asset('images/default-profile.png') }}"
                                alt="profile" />
                        </a>
                        <div class="dropdown-menu dropdown-menu-right navbar-dropdown"
                            aria-labelledby="profileDropdown">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                    <i class="ti-power-off text-primary"></i>
                                    Logout
                                </a>
                            </form>
                            <a class="dropdown-item" href="{{ route('updatePassword') }}">
                                <i class="ti-settings text-primary"></i>
                                Update Password
                            </a>
                        </div>
                    </li>
                </ul>
                <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button"
                    data-toggle="offcanvas">
                    <span class="icon-menu"></span>
                </button>
            </div>
        </nav>
        <!-- partial -->
        <div class="container-fluid page-body-wrapper">
            <!-- partial:partials/_settings-panel.html -->
            <div class="theme-setting-wrapper">
                <div id="settings-trigger"><i class="ti-settings"></i></div>
                <div id="theme-settings" class="settings-panel">
                    <i class="settings-close ti-close"></i>
                    <p class="settings-heading">SIDEBAR SKINS</p>
                    <div class="sidebar-bg-options selected" id="sidebar-light-theme">
                        <div class="img-ss rounded-circle bg-light border mr-3"></div>Light
                    </div>
                    <div class="sidebar-bg-options" id="sidebar-dark-theme">
                        <div class="img-ss rounded-circle bg-dark border mr-3"></div>Dark
                    </div>
                    <p class="settings-heading mt-2">HEADER SKINS</p>
                    <div class="color-tiles mx-0 px-4">
                        <div class="tiles success"></div>
                        <div class="tiles warning"></div>
                        <div class="tiles danger"></div>
                        <div class="tiles info"></div>
                        <div class="tiles dark"></div>
                        <div class="tiles default"></div>
                    </div>
                </div>
            </div>
            <div id="right-sidebar" class="settings-panel">
                <i class="settings-close ti-close"></i>
                <ul class="nav nav-tabs border-top" id="setting-panel" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="todo-tab" data-toggle="tab" href="#todo-section"
                            role="tab" aria-controls="todo-section" aria-expanded="true">TO DO LIST</a>
                    </li>
                    {{-- <li class="nav-item">
                        <a class="nav-link" id="chats-tab" data-toggle="tab" href="#chats-section" role="tab"
                            aria-controls="chats-section">CHATS</a>
                    </li> --}}
                </ul>
            </div>
            <!-- partial -->
            <!-- partial:partials/_sidebar.html -->
            <nav class="sidebar sidebar-offcanvas" id="sidebar">
                <ul class="nav">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard">
                            <i class="icon-grid menu-icon"></i>
                            <span class="menu-title">Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="collapse" href="#master-data" aria-expanded="false"
                            aria-controls="master-data">
                            <i class="mdi mdi-database menu-icon"></i>
                            <span class="menu-title">Master Data</span>
                            <i class="menu-arrow"></i>
                        </a>
                        <div class="collapse" id="master-data">
                            <ul class="nav flex-column sub-menu">
                                <li class="nav-item"> <a class="nav-link" href="users">Users</a></li>
                                <li class="nav-item"> <a class="nav-link" href="ships">Ships</a></li>
                                <li class="nav-item"> <a class="nav-link" href="items">Items</a></li>
                                <li class="nav-item"> <a class="nav-link" href="services">Services</a></li>
                                <li class="nav-item"> <a class="nav-link" href="expenseAccounts">Expense Accounts</a>
                                </li>
                                <li class="nav-item"> <a class="nav-link" href="suppliers">Suppliers</a></li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="collapse" href="#warehouse" aria-expanded="false"
                            aria-controls="warehouse">
                            <i class="icon-columns menu-icon"></i>
                            <span class="menu-title">Warehouse</span>
                            <i class="menu-arrow"></i>
                        </a>
                        <div class="collapse" id="warehouse">
                            <ul class="nav flex-column sub-menu">
                                <li class="nav-item"><a class="nav-link" href="officeWarehouse">Office Warehouse</a>
                                </li>
                                <li class="nav-item"><a class="nav-link" href="shipWarehouses">Ship Warehouses</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="purchaseRequests">
                            <i class="mdi mdi-cart-plus menu-icon"></i>
                            <span class="menu-title">Purchase Request</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="purchaseOrders">
                            <i class="mdi mdi-currency-usd menu-icon"></i>
                            <span class="menu-title">Purchase Order</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="receipts">
                            <i class="mdi mdi-note-outline menu-icon"></i>
                            <span class="menu-title">Receipt</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="inventoryTransfers">
                            <i class="mdi mdi-truck menu-icon"></i>
                            <span class="menu-title">Inventory Transfers</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="expenseAccountSpends">
                            <i class="mdi mdi-currency-usd menu-icon"></i>
                            <span class="menu-title">Account Spends</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <!-- partial -->
            @yield('main-panel')
            <!-- main-panel ends -->
            {{-- <footer class="footer">
                <div class="d-sm-flex justify-content-center justify-content-sm-between">
                    <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">Copyright ©2024.
                        PT.Samudera Atlantis International. All rights reserved.</span>
                    <span class="float-none float-sm-right d-block mt-1 mt-sm-0 text-center">Developed by M.Reza Rafli
                        Zamzam Alfarizi.</span>
                </div>
            </footer> --}}
        </div>
        <!-- page-body-wrapper ends -->
    </div>
    <!-- container-scroller -->

    <!-- plugins:js -->
    <script src="vendors/js/vendor.bundle.base.js"></script>
    <!-- endinject -->
    <!-- Plugin js for this page -->
    <script src="vendors/chart.js/Chart.min.js"></script>

    {{-- <script src="js/dataTables.select.min.js"></script> --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="js/off-canvas.js"></script>
    <script src="js/hoverable-collapse.js"></script>
    <script src="js/template.js"></script>
    <script src="js/settings.js"></script>
    <script src="js/todolist.js"></script>
    <!-- endinject -->
    <!-- Custom js for this page-->
    <script src="js/dashboard.js"></script>
    <script src="js/Chart.roundedBarCharts.js"></script>
    <!-- End custom js for this page-->
    @yield('script')
    {{-- JS Pribadi --}}
    {{-- Alert Delete Data --}}
    <script>
        $(document).ready(function() {
            // Pastikan tombol delete di binding dengan event klik
            $('body').on('click', '.btn-delete', function(event) {
                event.preventDefault(); // Mencegah default behavior dari anchor <a>
                var deleteUrl = $(this).attr('href'); // Mengambil URL untuk penghapusan
                // Tampilkan SweetAlert untuk konfirmasi
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#46a146',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Jika pengguna mengkonfirmasi dengan "Yes", redirect ke URL penghapusan
                        window.location.href = deleteUrl;
                    }
                });
            });
        });
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.18/js/bootstrap-select.min.js"></script>
    <script src="js/general.js"></script>
    <script>
        document.getElementById('sidebar').addEventListener('mouseenter', function() {
            $(".sidebar-offcanvas").addClass("activated");
            $("body").removeClass("sidebar-icon-only");
        });

        document.getElementById('sidebar').addEventListener('mouseleave', function() {
            $(".sidebar-offcanvas").removeClass("active");
            $("body").addClass("sidebar-icon-only");

            // Close all submenus when sidebar is collapsed
            $('.sidebar .collapse').collapse('hide');
        });
    </script>
</body>

</html>
