<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Admin Console | My Portfolio</title>

    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="{{ url('app.css') }}">

    <script src="{{ url('app.js') }}"></script>

    <!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/2.3.7/css/dataTables.dataTables.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<!-- jQuery (required for DataTables) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/2.3.7/js/dataTables.min.js"></script>

    <style>
        html {
            height: 100%;
        }

        body.console-body {
            margin: 0;
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            background-color: #f0f6ff;
        }

        .console-main {
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
            min-height: 0;
        }

        .console-main > .console-inner {
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
            min-height: 0;
        }

        .console-content {
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
            min-height: 0;
        }

        .login-center .card {
            margin-top: 0;
        }

        .adminbar.topbar {
            flex-shrink: 0;
        }

        .topbar {
            background: #1f2937;
            color: #fff;
        }

        .topbar a {
            color: #d1d5db;
            text-decoration: none;
            margin-left: 15px;
            font-weight: 500;
        }

        .topbar a:hover {
            color: #fff;
        }

        .brand {
            font-weight: 600;
            font-size: 20px;
        }

        .container {
            max-width: 1100px;
            margin: auto;
        }

        .card {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .alert {
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-error {
            background: #fee2e2;
            color: #b91c1c;
        }

        
.adminbar .admin-nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
}

.dashboard-section .card{
    margin: 0;
}

.table-responsive {
    overflow-x: auto !important;
    width: 100%;
}

@media (max-width:1024px){
.card-body{
    padding: 0;
}

}

.table-responsive table{
    border: 1px solid #dee2e6;
}

.dataTables_wrapper .dataTables_paginate .paginate_button:hover{
    background: transparent !important;
    border: none !important;
}

        .adminfooter {
            flex-shrink: 0;
            width: 100%;
        }

.dataTables_filter{
    margin-bottom:20px!important;
}

@media (max-width:767px){
    .adminbar .admin-nav{
        flex-direction: column;
        text-align: center;
    }

    .adminbar .admin-nav .brand{
        margin-bottom: 10px;
    }

    .adminbar .admin-nav > div:last-child {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 0.35rem 0.75rem;
    }

    .adminbar .admin-nav > div:last-child a {
        margin-left: 0;
    }
}

    </style>
</head>

<body class="console-body">

    <!-- Top Navigation -->
    <div class="adminbar topbar w3-padding">
        <div class="container admin-nav w3-flex w3-justify-between w3-align-center">

            <div class="brand">Admin Console</div>

            <div>
                @if (Auth::check())
                    <span style="margin-right:10px;">
                         {{ auth()->user()->first }} {{ auth()->user()->last }}
                    </span>

                    <a href="/console/dashboard">Dashboard</a>
                    <a href="/">View Site</a>
                    <a href="/console/logout" class="w3-text-red"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                @else
                    <a href="/">Return to Website</a>
                @endif
            </div>

        </div>
    </div>

    <main class="console-main">
        <div class="container console-inner">

            <!-- Flash Message -->
            @if (session()->has('message'))
                <div class="alert alert-error">
                    {{ session()->get('message') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger py-2 px-3 mb-3 d-flex align-items-center gap-2 small" role="alert">
                    <i class="fa-solid fa-circle-exclamation flex-shrink-0" aria-hidden="true"></i>
                    <span class="mb-0">{{ $errors->first() }}</span>
                </div>
            @endif

            <!-- Page Content -->
            <div class="console-content">
                @yield('content')
            </div>

        </div>
    </main>

    <footer class="adminfooter text-center py-3" style="background: #1f2937; color: #d1d5db; font-size: 14px;">
        <span class="d-inline-block px-3">© Copyright 2026 Ministry of Ayush. All Rights Reserved</span>
    </footer>

    <script>
    $(document).ready(function () {
        $('.datatable').each(function () {
            new DataTable(this, {
                pageLength: 10,
                responsive: true
            });
        });
    });
</script>

</body>
</html>