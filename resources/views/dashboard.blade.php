@extends('layouts.app')

@section('title', 'Dasbor - SupplyGuard')
@section('page-title', 'Ringkasan Dasbor')

@section('content')
<div class="alert alert-info border-0 shadow-sm d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <i class="bi bi-database-check me-2"></i>{{ $apiStatus }}
        <div class="small mt-1">Pembaruan data terakhir: <strong>{{ $latestDataAt }}</strong></div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <span class="badge bg-success">API berhasil: {{ $apiSummary['success_logs'] }}</span>
        <span class="badge bg-danger">API gagal: {{ $apiSummary['failed_logs'] }}</span>
        <span class="badge bg-primary">{{ $riskScopeCount }} negara dinilai</span>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-3">
        <div class="card sg-card p-4 position-relative">
            <a href="{{ route('countries.index') }}" class="stretched-link" aria-label="Buka Negara Global"></a>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted">Negara Global</small>
                    <h3 class="fw-bold mb-0">{{ $summary['countries'] }}</h3>
                    <small class="text-muted">Dataset seluruh negara</small>
                </div>
                <div class="sg-stat-icon">
                    <i class="bi bi-globe2"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4 position-relative">
            <a href="{{ route('ports.index') }}" class="stretched-link" aria-label="Buka Lokasi Pelabuhan"></a>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted">Pelabuhan Global</small>
                    <h3 class="fw-bold mb-0">{{ $summary['ports'] }}</h3>
                    <small class="text-muted">Pemantauan pelabuhan</small>
                </div>
                <div class="sg-stat-icon">
                    <i class="bi bi-geo-alt"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4 position-relative">
            <a href="{{ route('news.index') }}" class="stretched-link" aria-label="Buka Intelijen Berita"></a>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted">Intelijen Berita</small>
                    <h3 class="fw-bold mb-0">{{ $summary['news'] }}</h3>
                    <small class="text-muted">Artikel tersimpan</small>
                </div>
                <div class="sg-stat-icon">
                    <i class="bi bi-newspaper"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4 position-relative">
            <a href="{{ route('risk.index') }}" class="stretched-link" aria-label="Buka Penilaian Risiko"></a>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted">Rata-rata Risiko</small>
                    <h3 class="fw-bold mb-0">{{ $summary['average_risk'] }}%</h3>
                    <small class="text-muted">Risiko rantai pasok</small>
                </div>
                <div class="sg-stat-icon">
                    <i class="bi bi-activity"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-md-4">
        <div class="card sg-card p-4">
            <small class="text-muted">Negara Berisiko Rendah</small>
            <div class="d-flex justify-content-between align-items-center mt-2">
                <h2 class="fw-bold text-success">{{ $summary['low_risk'] }}</h2>
                <span class="badge-soft risk-low">Jalur Aman</span>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card sg-card p-4">
            <small class="text-muted">Negara Berisiko Sedang</small>
            <div class="d-flex justify-content-between align-items-center mt-2">
                <h2 class="fw-bold text-warning">{{ $summary['medium_risk'] }}</h2>
                <span class="badge-soft risk-medium">Pantau dengan Cermat</span>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card sg-card p-4">
            <small class="text-muted">Negara Berisiko Tinggi</small>
            <div class="d-flex justify-content-between align-items-center mt-2">
                <h2 class="fw-bold text-danger">{{ $summary['high_risk'] }}</h2>
                <span class="badge-soft risk-high">Berpotensi Terlambat</span>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-lg-8">
        <div class="card sg-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold mb-0">Skor Risiko per Negara</h5>
                    <small class="text-muted">10 negara dengan skor tertinggi dari snapshot terbaru</small>
                </div>
                <span class="badge bg-primary">Chart.js</span>
            </div>

            <div style="height: 280px;">
                <canvas id="riskChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-0">Distribusi Risiko</h5>
            <small class="text-muted">Ringkasan risiko rendah, sedang, dan tinggi</small>

            <div style="height: 280px;">
                <canvas id="riskPieChart" class="mt-3"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-lg-7">
        <div class="card sg-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold mb-0">Peta Pemantauan Pelabuhan Global</h5>
                    <small class="text-muted">
                        Satu pelabuhan representatif per negara dari database
                    </small>
                </div>
                <span class="badge bg-success">Leaflet.js</span>
            </div>

            <div id="worldMap"></div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-3">Berita Rantai Pasok Terbaru</h5>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                    <tr>
                        <th>Berita</th>
                        <th>Negara</th>
                        <th>Risiko</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse($latestNews as $item)
                        <tr>
                            <td>
                                @if(!empty($item['url']))
                                    <a href="{{ $item['url'] }}" target="_blank" rel="noopener noreferrer" class="fw-semibold text-decoration-none">
                                        {{ $item['title'] }}
                                    </a>
                                @else
                                    <div class="fw-semibold">{{ $item['title'] }}</div>
                                @endif

                                <small class="text-muted">
                                    Sentimen:
                                    {{
                                        [
                                            'Positive' => 'Positif',
                                            'Neutral' => 'Netral',
                                            'Negative' => 'Negatif'
                                        ][$item['sentiment']] ?? $item['sentiment']
                                    }}
                                </small>
                            </td>

                            <td>{{ $item['country'] }}</td>

                            <td>
                                @if($item['risk'] == 'High')
                                    <span class="badge-soft risk-high">Tinggi</span>
                                @elseif($item['risk'] == 'Medium')
                                    <span class="badge-soft risk-medium">Sedang</span>
                                @else
                                    <span class="badge-soft risk-low">Rendah</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted py-4">Belum ada berita tersimpan.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-12">
        <div class="card sg-card p-4">
            <h5 class="fw-bold">Ringkasan Pendukung Keputusan SupplyGuard</h5>

            <p class="text-muted mb-0">
                Sistem ini dirancang untuk membantu perusahaan memantau risiko impor berdasarkan data semua negara,
                cuaca, inflasi, kurs mata uang, berita global, dan ketersediaan pelabuhan. Nilai risiko dihitung
                menggunakan Algoritma Pembobotan SG-Risk.
            </p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const riskLabels = @json($riskLabels);
    const riskData = @json($riskData);

    new Chart(document.getElementById('riskChart'), {
        type: 'bar',
        data: {
            labels: riskLabels,
            datasets: [{
                label: 'Skor Risiko',
                data: riskData,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });

    new Chart(document.getElementById('riskPieChart'), {
        type: 'doughnut',
        data: {
            labels: ['Risiko Rendah', 'Risiko Sedang', 'Risiko Tinggi'],
            datasets: [{
                data: [
                    {{ $summary['low_risk'] }},
                    {{ $summary['medium_risk'] }},
                    {{ $summary['high_risk'] }}
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    const map = L.map('worldMap').setView([15, 20], 2);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 7,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    const ports = @json($mapPorts);

    const riskColors = {
        Low: '#198754',
        Medium: '#ffc107',
        High: '#dc3545',
        Critical: '#212529'
    };

    ports.forEach(port => {
        L.circleMarker([port.lat, port.lng], {
            radius: 6,
            color: riskColors[port.risk] || '#0d6efd',
            fillColor: riskColors[port.risk] || '#0d6efd',
            fillOpacity: 0.85,
            weight: 1
        })
            .addTo(map)
            .bindPopup(`
                <b>${port.name}</b><br>
                Negara: ${port.country}<br>
                Status: ${port.status}<br>
                Risiko: ${port.risk}
            `);
    });

    if (ports.length === 0) {
        L.popup()
            .setLatLng([15, 20])
            .setContent('Belum ada data pelabuhan dengan koordinat valid.')
            .openOn(map);
    }
</script>
@endpush
