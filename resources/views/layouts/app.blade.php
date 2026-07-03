<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'SupplyGuard Intelligence Platform')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet">

    <style>
        body {
            background: #f4f7fb;
            font-family: Arial, Helvetica, sans-serif;
        }

        .sg-sidebar {
            width: 270px;
            min-height: 100vh;
            background: linear-gradient(180deg, #08111f, #102844);
            color: white;
            position: fixed;
            left: 0;
            top: 0;
            overflow-y: auto;
            z-index: 1000;
        }

        .sg-brand {
            padding: 22px;
            border-bottom: 1px solid rgba(255,255,255,0.12);
        }

        .sg-brand h4 {
            font-weight: 800;
            margin: 0;
        }

        .sg-brand small {
            color: #9fb7d4;
        }

        .sg-menu {
            padding: 14px;
        }

        .sg-menu-title {
            font-size: 11px;
            color: #8fa7c4;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 18px 10px 8px;
        }

        .sg-menu a {
            display: flex;
            align-items: center;
            gap: 11px;
            color: #d9e6f7;
            text-decoration: none;
            padding: 11px 13px;
            border-radius: 12px;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .sg-menu a:hover,
        .sg-menu a.active {
            background: rgba(255,255,255,0.13);
            color: white;
        }

        .sg-content {
            margin-left: 270px;
            min-height: 100vh;
        }

        .sg-navbar {
            background: white;
            padding: 16px 26px;
            border-bottom: 1px solid #e5e9f0;
            position: sticky;
            top: 0;
            z-index: 900;
        }

        .sg-main {
            padding: 26px;
        }

        .sg-card {
            background: white;
            border: 0;
            border-radius: 18px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        }

        .sg-stat-icon {
            width: 45px;
            height: 45px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eef5ff;
            color: #0d6efd;
            font-size: 22px;
        }

        .risk-low {
            background: #e8f7ee;
            color: #198754;
        }

        .risk-medium {
            background: #fff6df;
            color: #b7791f;
        }

        .risk-high {
            background: #fdeaea;
            color: #dc3545;
        }

        .badge-soft {
            padding: 7px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
        }

        #worldMap {
            height: 350px;
            border-radius: 18px;
            overflow: hidden;
        }

        @media (max-width: 900px) {
            .sg-sidebar {
                position: relative;
                width: 100%;
                min-height: auto;
            }

            .sg-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>

@include('layouts.sidebar')

<div class="sg-content">
    <div class="sg-navbar d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0 fw-bold">@yield('page-title', 'Dashboard')</h5>
            <small class="text-muted">Global Supply Chain Risk Intelligence Platform</small>
        </div>

        <div class="d-flex align-items-center gap-3">
            <div class="text-end">
                <div class="fw-bold">{{ Auth::user()->name ?? 'User' }}</div>
                <small class="text-muted">{{ Auth::user()->email ?? '-' }}</small>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </button>
            </form>
        </div>
    </div>

    <main class="sg-main">
        @yield('content')
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

@stack('scripts')

</body>
</html>