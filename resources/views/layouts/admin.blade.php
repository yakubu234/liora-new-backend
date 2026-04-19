<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') | Liora City</title>
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <style>
        table.dataTable.dtr-inline.collapsed > tbody > tr[role="row"] > td.dtr-control::before,
        table.dataTable.dtr-inline.collapsed > tbody > tr[role="row"] > th.dtr-control::before {
            top: 50%;
            transform: translateY(-50%);
            background-color: #1f6feb;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0 !important;
            margin-left: 0.15rem;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button .page-link {
            border-radius: 0.35rem;
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 0.75rem;
        }

        .dataTables_wrapper table.dataTable td,
        .dataTables_wrapper table.dataTable th {
            vertical-align: middle;
        }

        .dt-action-group {
            display: flex;
            flex-wrap: wrap;
            gap: 0.35rem;
            align-items: center;
        }

        .dt-action-group form {
            margin: 0;
        }
    </style>
    @stack('styles')
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                    <i class="fas fa-bars"></i>
                </a>
            </li>
        </ul>

        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="far fa-user mr-1"></i>
                    {{ auth()->user()->name }}
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <span class="dropdown-item dropdown-header">{{ auth()->user()->email }}</span>
                    <div class="dropdown-divider"></div>
                    <form action="{{ route('logout') }}" method="POST" class="px-3 py-2">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-block">Sign out</button>
                    </form>
                </div>
            </li>
        </ul>
    </nav>

    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="{{ route('dashboard') }}" class="brand-link">
            <img src="{{ asset('vendor/adminlte/dist/img/AdminLTELogo.png') }}" alt="Liora City" class="brand-image img-circle elevation-3" style="opacity: .8">
            <span class="brand-text font-weight-light">Liora City</span>
        </a>

        <div class="sidebar">
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <img src="{{ asset('vendor/adminlte/dist/img/user2-160x160.jpg') }}" class="img-circle elevation-2" alt="User Image">
                </div>
                <div class="info">
                    <a href="{{ route('dashboard') }}" class="d-block">{{ auth()->user()->name }}</a>
                </div>
            </div>

            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('profile.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user-circle"></i>
                            <p>
                                Profile
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('profile.user') }}" class="nav-link {{ request()->routeIs('profile.user') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>User Profile</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item {{ request()->routeIs('messages.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('messages.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-envelope"></i>
                            <p>
                                Messages
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('messages.index') }}" class="nav-link {{ request()->routeIs('messages.index') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>View All</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item {{ request()->routeIs('bookings.history', 'bookings.balance', 'bookings.create') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('bookings.history', 'bookings.balance', 'bookings.create') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-calendar-check"></i>
                            <p>
                                Bookings
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('bookings.history') }}" class="nav-link {{ request()->routeIs('bookings.history') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Booking History</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('bookings.balance') }}" class="nav-link {{ request()->routeIs('bookings.balance') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Yet to Balance</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('bookings.create') }}" class="nav-link {{ request()->routeIs('bookings.create') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Book Now</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item {{ request()->routeIs('services.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('services.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-concierge-bell"></i>
                            <p>
                                Service
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('services.index') }}" class="nav-link {{ request()->routeIs('services.index') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>List All</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('services.create') }}" class="nav-link {{ request()->routeIs('services.create') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Add New</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item {{ request()->routeIs('settings.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-cogs"></i>
                            <p>
                                App Settings
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('settings.agreement') }}" class="nav-link {{ request()->routeIs('settings.agreement') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Agreement</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('settings.receipt-signature') }}" class="nav-link {{ request()->routeIs('settings.receipt-signature') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Receipt Signature</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('settings.event-types') }}" class="nav-link {{ request()->routeIs('settings.event-types') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Type of Event</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('settings.users') }}" class="nav-link {{ request()->routeIs('settings.users*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>View Users</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item {{ request()->routeIs('website.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('website.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-globe"></i>
                            <p>
                                Website page
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('website.contact') }}" class="nav-link {{ request()->routeIs('website.contact') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Contact us</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('website.gallery') }}" class="nav-link {{ request()->routeIs('website.gallery') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Gallery</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('website.smtp') }}" class="nav-link {{ request()->routeIs('website.smtp') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Add SMTP Details</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('bookings.search') }}" class="nav-link {{ request()->routeIs('bookings.search') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-search"></i>
                            <p>Booking search</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.index') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-chart-bar"></i>
                            <p>Report</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <form action="{{ route('logout') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="nav-link btn btn-link text-left w-100">
                                <i class="nav-icon fas fa-sign-out-alt"></i>
                                <p>Logout</p>
                            </button>
                        </form>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>@yield('page_title', 'Dashboard')</h1>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                @yield('content')
            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>Liora City Event Center Admin Panel.</strong>
        <div class="float-right d-none d-sm-inline-block">
            AdminLTE starter
        </div>
    </footer>
</div>

<script src="{{ asset('vendor/adminlte/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('vendor/adminlte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('vendor/adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('vendor/adminlte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
<script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>
<script>
    $(function () {
        $('.js-data-table').each(function () {
            const $table = $(this);

            if ($.fn.DataTable.isDataTable(this)) {
                return;
            }

            $table.DataTable({
                paging: true,
                pageLength: 10,
                lengthChange: true,
                searching: true,
                ordering: true,
                info: true,
                autoWidth: false,
                responsive: {
                    details: {
                        type: 'inline',
                        target: 'tr'
                    }
                },
                columnDefs: [
                    {
                        targets: 'dt-actions',
                        orderable: false,
                        searchable: false,
                        responsivePriority: 1
                    },
                    {
                        targets: 'dt-priority-1',
                        responsivePriority: 1
                    },
                    {
                        targets: 'dt-priority-2',
                        responsivePriority: 2
                    },
                    {
                        targets: 'dt-priority-3',
                        responsivePriority: 3
                    }
                ]
            });
        });
    });
</script>
@stack('scripts')
</body>
</html>
