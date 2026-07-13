@extends('layouts.app')

@section('title', 'Visualisasi Data - SupplyGuard')
@section('page-title', 'Dasbor Visualisasi Data')

@section('content')

{{-- BAGIAN JUDUL --}}
<div class="card sg-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold mb-1">Dasbor Visualisasi Data</h4>
            <p class="text-muted mb-0">
                Visualisasi data risiko rantai pasok global berdasarkan semua negara,
                cuaca, kurs, berita, pelabuhan, dan inflasi.
            </p>
        </div>

        <span class="badge bg-primary">Analitik Chart.js</span>
    </div>
</div>

{{-- KARTU RINGKASAN --}}
<div class="row g-4">
    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Total Negara</small>
            <h3 class="fw-bold">{{ $summary['total_countries'] }}</h3>
            <span class="badge bg-primary">Seluruh Negara</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Rata-rata Risiko</small>
            <h3 class="fw-bold">{{ $summary['average_risk'] }}%</h3>
            <span class="badge-soft risk-medium">Rata-rata Global</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Risiko Rendah</small>
            <h3 class="fw-bold text-success">{{ $summary['low_risk'] }}</h3>
            <span class="badge-soft risk-low">Aman</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Risiko Sedang</small>
            <h3 class="fw-bold text-warning">{{ $summary['medium_risk'] }}</h3>
            <span class="badge-soft risk-medium">Pantau</span>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-md-6">
        <div class="card sg-card p-4">
            <small class="text-muted">Risiko Tinggi</small>
            <h3 class="fw-bold text-danger">{{ $summary['high_risk'] }}</h3>
            <span class="badge-soft risk-high">Perlu Perhatian</span>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card sg-card p-4">
            <small class="text-muted">Risiko Kritis</small>
            <h3 class="fw-bold text-dark">{{ $summary['critical_risk'] }}</h3>
            <span class="badge bg-dark text-white">Kritis</span>
        </div>
    </div>
</div>

{{-- BARIS GRAFIK 1 --}}
<div class="row g-4 mt-1">
    <div class="col-lg-7">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-1">10 Negara dengan Risiko Tertinggi</h5>
            <small class="text-muted">
                Negara dengan nilai risiko rantai pasok tertinggi.
            </small>

            <div class="mt-3" style="height: 330px;">
                <canvas id="topRiskChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-1">Distribusi Risiko</h5>
            <small class="text-muted">
                Distribusi kategori risiko global.
            </small>

            <div class="mt-3" style="height: 330px;">
                <canvas id="riskDistributionChart"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- BARIS GRAFIK 2 --}}
<div class="row g-4 mt-1">
    <div class="col-lg-6">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-1">Rata-rata Risiko Berdasarkan Wilayah</h5>
            <small class="text-muted">
                Rata-rata risiko berdasarkan wilayah.
            </small>

            <div class="mt-3" style="height: 320px;">
                <canvas id="regionRiskChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-1">Perbandingan Indikator Risiko</h5>
            <small class="text-muted">
                Rata-rata indikator risiko dari semua negara.
            </small>

            <div class="mt-3" style="height: 320px;">
                <canvas id="indicatorChart"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- BARIS TABEL --}}
<div class="row g-4 mt-1">
    <div class="col-lg-6">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-3">Negara dengan Risiko Tertinggi</h5>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Negara</th>
                            <th>Wilayah</th>
                            <th>Risiko</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($topRiskCountries as $index => $country)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $country['name'] }}</td>
                                <td>{{ $country['region'] }}</td>
                                <td class="fw-bold">{{ $country['risk_score'] }}</td>
                                <td>
                                    <span class="badge-soft {{ $country['badge'] }}">
                                        {{
                                            [
                                                'Low' => 'Rendah',
                                                'Medium' => 'Sedang',
                                                'High' => 'Tinggi',
                                                'Critical' => 'Kritis'
                                            ][$country['category']] ?? $country['category']
                                        }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-3">Negara dengan Risiko Terendah</h5>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Negara</th>
                            <th>Wilayah</th>
                            <th>Risiko</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($lowRiskCountries as $index => $country)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $country['name'] }}</td>
                                <td>{{ $country['region'] }}</td>
                                <td class="fw-bold">{{ $country['risk_score'] }}</td>
                                <td>
                                    <span class="badge-soft {{ $country['badge'] }}">
                                        {{
                                            [
                                                'Low' => 'Rendah',
                                                'Medium' => 'Sedang',
                                                'High' => 'Tinggi',
                                                'Critical' => 'Kritis'
                                            ][$country['category']] ?? $country['category']
                                        }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- PRATINJAU SELURUH NEGARA --}}
<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">Pratinjau Visualisasi Seluruh Negara</h5>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Negara</th>
                    <th>Wilayah</th>
                    <th>Cuaca</th>
                    <th>Mata Uang</th>
                    <th>Berita</th>
                    <th>Pelabuhan</th>
                    <th>Inflasi</th>
                    <th>Total Risiko</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                @foreach(array_slice($countries, 0, 25) as $index => $country)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $country['name'] }}</td>
                        <td>{{ $country['region'] }}</td>
                        <td>{{ $country['weather_risk'] }}</td>
                        <td>{{ $country['currency_risk'] }}</td>
                        <td>{{ $country['news_risk'] }}</td>
                        <td>{{ $country['port_risk'] }}</td>
                        <td>{{ $country['inflation_risk'] }}</td>
                        <td class="fw-bold">{{ $country['risk_score'] }}</td>
                        <td>
                            <span class="badge-soft {{ $country['badge'] }}">
                                {{
                                    [
                                        'Low' => 'Rendah',
                                        'Medium' => 'Sedang',
                                        'High' => 'Tinggi',
                                        'Critical' => 'Kritis'
                                    ][$country['category']] ?? $country['category']
                                }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <small class="text-muted">
        Tabel ini menampilkan 25 negara pertama sebagai pratinjau.
        Semua negara tetap diproses di controller.
    </small>
</div>

{{-- PENJELASAN --}}
<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">Penjelasan Visualisasi</h5>

    <p class="text-muted mb-2">
        Halaman ini digunakan untuk melihat pola risiko global secara visual.
        Data divisualisasikan menggunakan Chart.js agar perbandingan antarnegara
        dan wilayah lebih mudah dianalisis.
    </p>

    <div class="alert alert-info mb-0">
        Indikator visualisasi meliputi risiko cuaca, risiko mata uang,
        risiko berita, risiko pelabuhan, risiko inflasi, dan total skor risiko.
    </div>
</div>

@endsection

@push('scripts')
<script>
    const countries = @json($countries);
    const regionSummary = @json($regionSummary);
    const topRiskCountries = @json($topRiskCountries);
    const riskSummary = @json($summary);

    function average(items, key) {
        if (!items.length) {
            return 0;
        }

        const total = items.reduce(function (sum, item) {
            return sum + Number(item[key] || 0);
        }, 0);

        return Number((total / items.length).toFixed(2));
    }

    const averageWeather = average(countries, 'weather_risk');
    const averageCurrency = average(countries, 'currency_risk');
    const averageNews = average(countries, 'news_risk');
    const averagePort = average(countries, 'port_risk');
    const averageInflation = average(countries, 'inflation_risk');

    new Chart(document.getElementById('topRiskChart'), {
        type: 'bar',
        data: {
            labels: topRiskCountries.map(function (country) {
                return country.name;
            }),
            datasets: [{
                label: 'Skor Risiko',
                data: topRiskCountries.map(function (country) {
                    return country.risk_score;
                }),
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });

    new Chart(document.getElementById('riskDistributionChart'), {
        type: 'doughnut',
        data: {
            labels: [
                'Risiko Rendah',
                'Risiko Sedang',
                'Risiko Tinggi',
                'Risiko Kritis'
            ],
            datasets: [{
                data: [
                    riskSummary.low_risk,
                    riskSummary.medium_risk,
                    riskSummary.high_risk,
                    riskSummary.critical_risk
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    new Chart(document.getElementById('regionRiskChart'), {
        type: 'bar',
        data: {
            labels: regionSummary.map(function (item) {
                return item.region;
            }),
            datasets: [{
                label: 'Rata-rata Risiko',
                data: regionSummary.map(function (item) {
                    return item.average_risk;
                }),
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

    new Chart(document.getElementById('indicatorChart'), {
        type: 'radar',
        data: {
            labels: [
                'Cuaca',
                'Mata Uang',
                'Berita',
                'Pelabuhan',
                'Inflasi'
            ],
            datasets: [{
                label: 'Rata-rata Indikator Risiko',
                data: [
                    averageWeather,
                    averageCurrency,
                    averageNews,
                    averagePort,
                    averageInflation
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                r: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
</script>
@endpush