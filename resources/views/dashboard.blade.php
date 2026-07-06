@extends('layouts.app')

@section('title', 'Dashboard - SupplyGuard')
@section('page-title', 'Dashboard Overview')

@section('content')
<div class="row g-4">
    <div class="col-md-3">
        <div class="card sg-card p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted">Global Countries</small>
                    <h3 class="fw-bold mb-0">{{ $summary['countries'] }}</h3>
                    <small class="text-muted">All countries dataset</small>
                </div>
                <div class="sg-stat-icon">
                    <i class="bi bi-globe2"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted">Global Ports</small>
                    <h3 class="fw-bold mb-0">{{ $summary['ports'] }}</h3>
                    <small class="text-muted">Port monitoring</small>
                </div>
                <div class="sg-stat-icon">
                    <i class="bi bi-geo-alt"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted">News Intelligence</small>
                    <h3 class="fw-bold mb-0">{{ $summary['news'] }}</h3>
                    <small class="text-muted">Cached articles</small>
                </div>
                <div class="sg-stat-icon">
                    <i class="bi bi-newspaper"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted">Average Risk</small>
                    <h3 class="fw-bold mb-0">{{ $summary['average_risk'] }}%</h3>
                    <small class="text-muted">Supply chain risk</small>
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
            <small class="text-muted">Low Risk Countries</small>
            <div class="d-flex justify-content-between align-items-center mt-2">
                <h2 class="fw-bold text-success">{{ $summary['low_risk'] }}</h2>
                <span class="badge-soft risk-low">Safe Route</span>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card sg-card p-4">
            <small class="text-muted">Medium Risk Countries</small>
            <div class="d-flex justify-content-between align-items-center mt-2">
                <h2 class="fw-bold text-warning">{{ $summary['medium_risk'] }}</h2>
                <span class="badge-soft risk-medium">Watch Carefully</span>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card sg-card p-4">
            <small class="text-muted">High Risk Countries</small>
            <div class="d-flex justify-content-between align-items-center mt-2">
                <h2 class="fw-bold text-danger">{{ $summary['high_risk'] }}</h2>
                <span class="badge-soft risk-high">Delay Possible</span>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-lg-8">
        <div class="card sg-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold mb-0">Risk Score by Country</h5>
                    <small class="text-muted">SG-Risk Weighted Scoring Algorithm</small>
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
            <h5 class="fw-bold mb-0">Risk Distribution</h5>
            <small class="text-muted">Low, medium, and high risk summary</small>

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
                    <h5 class="fw-bold mb-0">Global Port Monitoring Map</h5>
                    <small class="text-muted">Interactive port location dashboard using Leaflet.js</small>
                </div>
                <span class="badge bg-success">Leaflet.js</span>
            </div>

            <div id="worldMap"></div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-3">Latest Supply Chain News</h5>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                    <tr>
                        <th>News</th>
                        <th>Country</th>
                        <th>Risk</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($latestNews as $item)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $item['title'] }}</div>
                                <small class="text-muted">Sentiment: {{ $item['sentiment'] }}</small>
                            </td>
                            <td>{{ $item['country'] }}</td>
                            <td>
                                @if($item['risk'] == 'High')
                                    <span class="badge-soft risk-high">High</span>
                                @elseif($item['risk'] == 'Medium')
                                    <span class="badge-soft risk-medium">Medium</span>
                                @else
                                    <span class="badge-soft risk-low">Low</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-12">
        <div class="card sg-card p-4">
            <h5 class="fw-bold">SupplyGuard Decision Support Summary</h5>
            <p class="text-muted mb-0">
                Sistem ini dirancang untuk membantu perusahaan memantau risiko impor berdasarkan data semua negara,
                cuaca, inflasi, kurs mata uang, berita global, dan ketersediaan pelabuhan. Nilai risiko dihitung
                menggunakan algoritma pembobotan SG-Risk Weighted Scoring Algorithm.
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
                label: 'Risk Score',
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
            labels: ['Low Risk', 'Medium Risk', 'High Risk'],
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

    const map = L.map('worldMap').setView([5, 110], 2);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 7,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    const ports = [
        {
            name: 'Tanjung Priok Port',
            country: 'Indonesia',
            lat: -6.104,
            lng: 106.880
        },
        {
            name: 'Shanghai Port',
            country: 'China',
            lat: 31.230,
            lng: 121.473
        },
        {
            name: 'Hamburg Port',
            country: 'Germany',
            lat: 53.546,
            lng: 9.966
        },
        {
            name: 'Singapore Port',
            country: 'Singapore',
            lat: 1.264,
            lng: 103.840
        }
    ];

    ports.forEach(port => {
        L.marker([port.lat, port.lng])
            .addTo(map)
            .bindPopup(`
                <b>${port.name}</b><br>
                Country: ${port.country}<br>
                Status: Active
            `);
    });
</script>
@endpush