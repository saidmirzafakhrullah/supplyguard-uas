@extends('layouts.app')

@section('title', 'API Logs - SupplyGuard')
@section('page-title', 'Admin Management - API Logs')

@section('content')

<div class="card sg-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold mb-1">API Logs Monitoring</h4>
            <p class="text-muted mb-0">
                Halaman admin untuk memantau riwayat penggunaan API eksternal
                pada sistem SupplyGuard.
            </p>
        </div>

        <span class="badge bg-primary">API Monitoring</span>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Total Logs</small>
            <h3 class="fw-bold">{{ $summary['total_logs'] }}</h3>
            <span class="badge bg-primary">Requests</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Success Logs</small>
            <h3 class="fw-bold text-success">{{ $summary['success_logs'] }}</h3>
            <span class="badge bg-success">Success</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Failed Logs</small>
            <h3 class="fw-bold text-danger">{{ $summary['failed_logs'] }}</h3>
            <span class="badge bg-danger">Failed</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">External APIs</small>
            <h3 class="fw-bold">{{ $summary['external_apis'] }}</h3>
            <span class="badge bg-primary">Multi API</span>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-lg-5">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-1">API Status Distribution</h5>
            <small class="text-muted">Perbandingan API berhasil dan gagal.</small>

            <div class="mt-3" style="height: 300px;">
                <canvas id="apiStatusChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-1">API Response Time</h5>
            <small class="text-muted">Waktu respon API dalam millisecond.</small>

            <div class="mt-3" style="height: 300px;">
                <canvas id="responseTimeChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="card sg-card p-4 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold mb-1">API Logs Dataset</h5>
            <small class="text-muted">
                Riwayat pemanggilan API eksternal pada fitur utama sistem.
            </small>
        </div>

        <div style="width: 280px;">
            <input
                type="text"
                id="apiLogSearch"
                class="form-control"
                placeholder="Search API logs..."
            >
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>No</th>
                    <th>API Name</th>
                    <th>Endpoint</th>
                    <th>Method</th>
                    <th>Feature</th>
                    <th>Status</th>
                    <th>Code</th>
                    <th>Response</th>
                    <th>Requested At</th>
                </tr>
            </thead>

            <tbody>
                @foreach($apiLogs as $index => $log)
                    <tr
                        class="api-log-row"
                        data-search="{{ strtolower($log['api_name'] . ' ' . $log['endpoint'] . ' ' . $log['feature'] . ' ' . $log['status']) }}"
                    >
                        <td>{{ $index + 1 }}</td>

                        <td>
                            <div class="fw-bold">{{ $log['api_name'] }}</div>
                            <small class="text-muted">{{ $log['description'] }}</small>
                        </td>

                        <td>
                            <code>{{ $log['endpoint'] }}</code>
                        </td>

                        <td>
                            <span class="badge bg-primary">{{ $log['method'] }}</span>
                        </td>

                        <td>{{ $log['feature'] }}</td>

                        <td>
                            @if($log['status'] === 'Success')
                                <span class="badge bg-success">Success</span>
                            @else
                                <span class="badge bg-danger">Failed</span>
                            @endif
                        </td>

                        <td>
                            @if($log['status_code'] === 200)
                                <span class="badge bg-success">{{ $log['status_code'] }}</span>
                            @else
                                <span class="badge bg-danger">{{ $log['status_code'] }}</span>
                            @endif
                        </td>

                        <td>
                            @if($log['response_time'] > 0)
                                {{ $log['response_time'] }} ms
                            @else
                                <span class="text-danger">Limit / Timeout</span>
                            @endif
                        </td>

                        <td>{{ $log['requested_at'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">API Logs Explanation</h5>

    <p class="text-muted mb-2">
        Fitur API Logs digunakan admin untuk memantau integrasi API eksternal.
        Sistem SupplyGuard menggunakan API untuk data negara, cuaca, kurs,
        berita, ekonomi, pelabuhan, dan peta.
    </p>

    <div class="alert alert-info mb-0">
        Halaman ini membuktikan konsep Multi-API Integration dan membantu admin
        melihat status request API yang berhasil atau gagal.
    </div>
</div>

@endsection

@push('scripts')
<script>
    const apiLogs = @json($apiLogs);

    const successLogs = {{ $summary['success_logs'] }};
    const failedLogs = {{ $summary['failed_logs'] }};

    new Chart(document.getElementById('apiStatusChart'), {
        type: 'doughnut',
        data: {
            labels: ['Success', 'Failed'],
            datasets: [{
                data: [successLogs, failedLogs]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    new Chart(document.getElementById('responseTimeChart'), {
        type: 'bar',
        data: {
            labels: apiLogs.map(function (log) {
                return log.api_name;
            }),
            datasets: [{
                label: 'Response Time (ms)',
                data: apiLogs.map(function (log) {
                    return log.response_time;
                }),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    document.getElementById('apiLogSearch').addEventListener('keyup', function () {
        const keyword = this.value.toLowerCase();
        const rows = document.querySelectorAll('.api-log-row');

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