@extends('layouts.app')

@section('title', 'Log API - SupplyGuard')
@section('page-title', 'Manajemen Admin - Log API')

@section('content')

{{-- BAGIAN JUDUL --}}
<div class="card sg-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold mb-1">Pemantauan Log API</h4>
            <p class="text-muted mb-0">
                Halaman admin untuk memantau riwayat penggunaan API eksternal
                pada sistem SupplyGuard.
            </p>
        </div>

        <span class="badge bg-primary">Pemantauan API</span>
    </div>
</div>

{{-- RINGKASAN --}}
<div class="row g-4">
    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Total Log</small>
            <h3 class="fw-bold">{{ $summary['total_logs'] }}</h3>
            <span class="badge bg-primary">Permintaan</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Log Berhasil</small>
            <h3 class="fw-bold text-success">{{ $summary['success_logs'] }}</h3>
            <span class="badge bg-success">Berhasil</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Log Gagal</small>
            <h3 class="fw-bold text-danger">{{ $summary['failed_logs'] }}</h3>
            <span class="badge bg-danger">Gagal</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">API Eksternal</small>
            <h3 class="fw-bold">{{ $summary['external_apis'] }}</h3>
            <span class="badge bg-primary">Multi-API</span>
        </div>
    </div>
</div>

{{-- GRAFIK --}}
<div class="row g-4 mt-1">
    <div class="col-lg-5">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-1">Distribusi Status API</h5>
            <small class="text-muted">
                Perbandingan permintaan API yang berhasil dan gagal.
            </small>

            <div class="mt-3" style="height: 300px;">
                <canvas id="apiStatusChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-1">Waktu Respons API</h5>
            <small class="text-muted">
                Waktu respons API dalam milidetik.
            </small>

            <div class="mt-3" style="height: 300px;">
                <canvas id="responseTimeChart"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- DATASET LOG API --}}
<div class="card sg-card p-4 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold mb-1">Dataset Log API</h5>
            <small class="text-muted">
                Riwayat pemanggilan API eksternal pada fitur utama sistem.
            </small>
        </div>

        <div style="width: 280px;">
            <input
                type="text"
                id="apiLogSearch"
                class="form-control"
                placeholder="Cari log API..."
            >
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama API</th>
                    <th>Endpoint</th>
                    <th>Metode</th>
                    <th>Fitur</th>
                    <th>Status</th>
                    <th>Kode</th>
                    <th>Respons</th>
                    <th>Waktu Permintaan</th>
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
                            <div class="fw-bold">
                                {{ $log['api_name'] }}
                            </div>

                            <small class="text-muted">
                                {{ $log['description'] }}
                            </small>
                        </td>

                        <td>
                            <code>{{ $log['endpoint'] }}</code>
                        </td>

                        <td>
                            <span class="badge bg-primary">
                                {{ $log['method'] }}
                            </span>
                        </td>

                        <td>
                            {{
                                [
                                    'Countries' => 'Negara',
                                    'Country Data' => 'Data Negara',
                                    'Country Dashboard' => 'Dasbor Negara',
                                    'Weather' => 'Cuaca',
                                    'Weather Monitoring' => 'Pemantauan Cuaca',
                                    'Currency' => 'Mata Uang',
                                    'Currency Impact' => 'Dampak Mata Uang',
                                    'News' => 'Berita',
                                    'News Intelligence' => 'Intelijen Berita',
                                    'Ports' => 'Pelabuhan',
                                    'Port Location' => 'Lokasi Pelabuhan',
                                    'Economy' => 'Ekonomi',
                                    'Economic Data' => 'Data Ekonomi',
                                    'Map' => 'Peta',
                                    'Risk Scoring' => 'Penilaian Risiko'
                                ][$log['feature']] ?? $log['feature']
                            }}
                        </td>

                        <td>
                            @if($log['status'] === 'Success')
                                <span class="badge bg-success">
                                    Berhasil
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    Gagal
                                </span>
                            @endif
                        </td>

                        <td>
                            @if($log['status_code'] === 200)
                                <span class="badge bg-success">
                                    {{ $log['status_code'] }}
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    {{ $log['status_code'] }}
                                </span>
                            @endif
                        </td>

                        <td>
                            @if($log['response_time'] > 0)
                                {{ $log['response_time'] }} ms
                            @else
                                <span class="text-danger">
                                    Batas / Waktu Habis
                                </span>
                            @endif
                        </td>

                        <td>{{ $log['requested_at'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- PENJELASAN --}}
<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">Penjelasan Log API</h5>

    <p class="text-muted mb-2">
        Fitur Log API digunakan admin untuk memantau integrasi API eksternal.
        Sistem SupplyGuard menggunakan API untuk data negara, cuaca, kurs,
        berita, ekonomi, pelabuhan, dan peta.
    </p>

    <div class="alert alert-info mb-0">
        Halaman ini membuktikan konsep integrasi Multi-API dan membantu admin
        melihat status permintaan API yang berhasil atau gagal.
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
            labels: [
                'Berhasil',
                'Gagal'
            ],
            datasets: [{
                data: [
                    successLogs,
                    failedLogs
                ]
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
                label: 'Waktu Respons (ms)',
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