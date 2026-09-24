<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Admin Console | MOA</title>

    <link rel="icon" href="{{ asset('assets/images/favicon.png') }}" type="image/png">
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}" type="image/png">

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
            max-width: 1400px;
            width: 100%;
            margin: auto;
            padding-left: 16px;
            padding-right: 16px;
        }

        .console-actions {
            white-space: nowrap;
            min-width: 150px;
        }

        .console-actions .btn {
            display: inline-block;
            margin: 0 4px 4px 0;
            vertical-align: middle;
        }

        .console-thumb {
            width: 56px;
            height: 56px;
            object-fit: cover;
            border-radius: 4px;
            margin: 2px;
        }

        .console-audio {
            max-width: 140px;
            height: 32px;
        }

        table.datatable th,
        table.datatable td {
            vertical-align: middle !important;
            padding: 10px 12px !important;
        }

        table.datatable td:last-child,
        table.datatable th:last-child {
            white-space: nowrap;
        }

        .console-bulk-table .bulk-check-col {
            width: 44px;
            text-align: center;
            vertical-align: middle;
        }

        .console-bulk-table input[type="checkbox"][data-select-all],
        .console-bulk-table input[type="checkbox"][data-row-selector] {
            width: 18px;
            height: 18px;
            margin: 0;
            cursor: pointer;
            accent-color: #198754;
        }

        .table-dark .bulk-check-col input[type="checkbox"] {
            filter: brightness(1.15);
        }

        .console-form label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .console-form input[type="text"],
        .console-form input[type="url"],
        .console-form input[type="email"],
        .console-form input[type="number"],
        .console-form input[type="password"],
        .console-form select,
        .console-form textarea,
        .console-form input[type="file"] {
            display: block;
            width: 100%;
            max-width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 15px;
            line-height: 1.45;
            background: #fff;
            box-sizing: border-box;
        }

        .console-form textarea {
            min-height: 90px;
            resize: vertical;
        }

        .console-form textarea#description,
        .console-form textarea[rows="8"] {
            min-height: 180px;
        }

        .console-form input[type="number"] {
            max-width: 160px;
        }

        .console-form input[type="color"] {
            width: 48px;
            height: 38px;
            padding: 2px;
            display: inline-block;
            vertical-align: middle;
            max-width: 48px;
        }

        .console-form .form-hint,
        .console-form .w3-small {
            color: #6b7280;
            margin-top: 6px;
        }

        .console-form .console-preview img {
            max-width: 240px;
            height: auto;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
        }

        .console-page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .console-page-header .console-page-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-left: auto;
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

        .console-upload-overlay {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1050;
            padding: 0.75rem 1rem 1rem;
            background: linear-gradient(to top, rgba(15, 23, 42, 0.92), rgba(15, 23, 42, 0.75) 40%, transparent);
            pointer-events: none;
        }

        .console-upload-overlay.is-active {
            pointer-events: auto;
        }

        .console-upload-panel {
            max-width: 1100px;
            margin: 0 auto;
            background: #fff;
            border-radius: 10px;
            padding: 1rem 1.25rem;
            box-shadow: 0 8px 28px rgba(0, 0, 0, 0.18);
            border: 1px solid #e5e7eb;
        }

        .console-upload-progress-track {
            height: 1.35rem;
            border-radius: 0.5rem;
        }

        .console-upload-progress-track .progress-bar {
            font-size: 0.75rem;
            font-weight: 600;
        }

        #console-upload-detail {
            word-break: break-word;
        }

    </style>
</head>

<body class="console-body"
      data-php-upload-max-bytes="{{ \App\Support\UploadLimits::effectiveMaxBytes() }}"
      data-php-upload-max-label="{{ \App\Support\UploadLimits::effectiveMaxLabel() }}"
      data-php-image-batch-size="{{ \App\Support\UploadLimits::imageUploadBatchSize() }}"
      data-php-image-batch-max-mb="150">

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
                    <a href="{{ route('console.account.password') }}">Password</a>
                    <a href="/console/footer">Footer</a>
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
                <div class="alert alert-success py-2 px-3 mb-3 small" role="status">
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

    <script src="{{ asset('assets/js/console-upload-progress.js') }}?v=20260923bulkupload2"></script>
    <script src="{{ asset('assets/js/console-bulk-selection.js') }}?v=20260922daterange"></script>
    <script>
    $(document).ready(function () {
        $('.datatable').each(function () {
            if ($.fn.DataTable && $.fn.DataTable.isDataTable(this)) {
                return;
            }
            var freezeOrder = $(this).hasClass('datatable-frozen-order');
            new DataTable(this, {
                pageLength: freezeOrder ? 50 : 10,
                scrollX: true,
                autoWidth: false,
                ordering: !freezeOrder,
                order: [],
                columnDefs: [
                    { orderable: false, targets: -1 }
                ]
            });
        });
    });
</script>
    @stack('scripts')

</body>
</html>