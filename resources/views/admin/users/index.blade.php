@extends('layouts.app')

@section('title', 'Admin Users - SupplyGuard')
@section('page-title', 'Admin Management - Users')

@section('content')

{{-- HEADER --}}
<div class="card sg-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold mb-1">Users Management</h4>
            <p class="text-muted mb-0">
                Halaman admin untuk memantau data user yang terdaftar pada sistem SupplyGuard.
            </p>
        </div>

        <span class="badge bg-primary">Admin Panel</span>
    </div>
</div>

{{-- SUMMARY --}}
<div class="row g-4">
    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Total Users</small>
            <h3 class="fw-bold">{{ $summary['total_users'] }}</h3>
            <span class="badge bg-primary">Registered</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Admin Users</small>
            <h3 class="fw-bold text-danger">{{ $summary['admin_users'] }}</h3>
            <span class="badge bg-danger">Admin</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Regular Users</small>
            <h3 class="fw-bold text-success">{{ $summary['regular_users'] }}</h3>
            <span class="badge bg-success">User</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Active Users</small>
            <h3 class="fw-bold text-primary">{{ $summary['active_users'] }}</h3>
            <span class="badge bg-primary">Active</span>
        </div>
    </div>
</div>

{{-- CHART + INFO --}}
<div class="row g-4 mt-1">
    <div class="col-lg-5">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-1">User Role Distribution</h5>
            <small class="text-muted">
                Perbandingan jumlah admin dan user biasa.
            </small>

            <div class="mt-3" style="height: 280px;">
                <canvas id="userRoleChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-3">Admin Users Information</h5>

            <div class="alert alert-info">
                Menu ini digunakan untuk melihat user yang sudah terdaftar pada sistem.
                Data user diambil langsung dari tabel <b>users</b> di database Laravel.
            </div>

            <table class="table align-middle mb-0">
                <tbody>
                    <tr>
                        <td>Database Table</td>
                        <td class="fw-bold text-end">users</td>
                    </tr>

                    <tr>
                        <td>Managed By</td>
                        <td class="fw-bold text-end">Admin</td>
                    </tr>

                    <tr>
                        <td>Function</td>
                        <td class="fw-bold text-end">User Monitoring</td>
                    </tr>

                    <tr>
                        <td>Status</td>
                        <td class="text-end">
                            <span class="badge bg-success">Active</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- USERS TABLE --}}
<div class="card sg-card p-4 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold mb-1">Registered Users</h5>
            <small class="text-muted">
                Daftar user yang sudah memiliki akun pada sistem.
            </small>
        </div>

        <div style="width: 260px;">
            <input
                type="text"
                id="userSearch"
                class="form-control"
                placeholder="Search user..."
            >
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Registered At</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody id="userTableBody">
                @forelse($users as $index => $user)
                    <tr
                        class="user-row"
                        data-search="{{ strtolower($user['name'] . ' ' . $user['email'] . ' ' . $user['role']) }}"
                    >
                        <td>{{ $index + 1 }}</td>

                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div
                                    class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                    style="width: 36px; height: 36px;"
                                >
                                    {{ strtoupper(substr($user['name'], 0, 1)) }}
                                </div>

                                <div>
                                    <div class="fw-bold">{{ $user['name'] }}</div>
                                    <small class="text-muted">User ID: {{ $user['id'] }}</small>
                                </div>
                            </div>
                        </td>

                        <td>{{ $user['email'] }}</td>

                        <td>
                            @if($user['role'] === 'Admin')
                                <span class="badge bg-danger">Admin</span>
                            @else
                                <span class="badge bg-success">User</span>
                            @endif
                        </td>

                        <td>
                            <span class="badge bg-primary">
                                {{ $user['status'] }}
                            </span>
                        </td>

                        <td>{{ $user['created_at'] }}</td>

                        <td>
                            <button class="btn btn-sm btn-outline-primary" disabled>
                                Detail
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">
                            Belum ada user yang terdaftar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- EXPLANATION --}}
<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">Users Management Explanation</h5>

    <p class="text-muted mb-2">
        Fitur Users Management digunakan admin untuk melihat data akun pengguna.
        Dalam project ini, data user berasal dari database MySQL pada tabel users.
    </p>

    <div class="alert alert-info mb-0">
        Halaman ini mendukung kebutuhan Admin Dashboard karena admin dapat memantau
        jumlah user, role user, status user, dan data akun yang sudah terdaftar.
    </div>
</div>

@endsection

@push('scripts')
<script>
    const adminUsers = {{ $summary['admin_users'] }};
    const regularUsers = {{ $summary['regular_users'] }};

    new Chart(document.getElementById('userRoleChart'), {
        type: 'doughnut',
        data: {
            labels: ['Admin', 'User'],
            datasets: [{
                data: [adminUsers, regularUsers]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    document.getElementById('userSearch').addEventListener('keyup', function () {
        const keyword = this.value.toLowerCase();
        const rows = document.querySelectorAll('.user-row');

        rows.forEach(function (row) {
            const text = row.getAttribute('data-search');

            if (text.includes(keyword)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
</script>
@endpush