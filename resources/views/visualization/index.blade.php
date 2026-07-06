@extends('layouts.app')

@section('title', 'Data Visualization - SupplyGuard')
@section('page-title', 'Data Visualization Dashboard')

@section('content')

{{-- HEADER --}}
<div class="card sg-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold mb-1">Data Visualization Dashboard</h4>
            <p class="text-muted mb-0">
                Visualisasi data risiko rantai pasok global berdasarkan semua negara,
                cuaca, kurs, berita, pelabuhan, dan inflasi.
            </p>
        </div>

        <span class="badge bg-primary">Chart.js Analytics</span>
    </div>
</div>

{{-- SUMMARY CARDS --}}
<div class="row g-4">
    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Total Countries</small>
            <h3 class="fw-bold">{{ $summary['total_countries'] }}</h3>
            <span class="badge bg-primary">All Countries</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Average Risk</small>
            <h3 class="fw-bold">{{ $summary['average_risk'] }}%</h3>
            <span class="badge-soft risk-medium">Global Average</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Low Risk</small>
            <h3 class="fw-bold text-success">{{ $summary['low_risk'] }}</h3>
            <span class="badge-soft risk-low">Safe</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Medium Risk</small>
            <h3 class="fw-bold text-warning">{{ $summary['medium_risk'] }}</h3>
            <span class="badge-soft risk-medium">Monitor</span>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-md-6">
        <div class="card sg-card p-4">
            <small class="text-muted">High Risk</small>
            <h3 class="fw-bold text-danger">{{ $summary['high_risk'] }}</h3>
            <span class="badge-soft risk-high">Attention</span>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card sg-card p-4">
            <small class="text-muted">Critical Risk</small>
            <h3 class="fw-bold text-dark">{{ $summary['critical_risk'] }}</h3>
            <span class="badge bg-dark text-white">Critical</span>
        </div>
    </div>
</div>

{{-- CHART ROW 1 --}}
<div class="row g-4 mt-1">
    <div class="col-lg-7">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-1">Top 10 Highest Risk Countries</h5>
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
            <h5 class="fw-bold mb-1">Risk Distribution</h5>
            <small class="text-muted">
                Distribusi kategori risiko global.
            </small>

            <div class="mt-3" style="height: 330px;">
                <canvas id="riskDistributionChart"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- CHART ROW 2 --}}
<div class="row g-4 mt-1">
    <div class="col-lg-6">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-1">Average Risk by Region</h5>
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
            <h5 class="fw-bold mb-1">Risk Indicator Comparison</h5>
            <small class="text-muted">
                Rata-rata indikator risiko dari semua negara.
            </small>

            <div class="mt-3" style="height: 320px;">
                <canvas id="indicatorChart"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- TABLE ROW --}}
<div class="row g-4 mt-1">
    <div class="col-lg-6">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-3">Highest Risk Countries</h5>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Country</th>
                            <th>Region</th>
                            <th>Risk</th>
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
                                        {{ $country['category'] }}
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
            <h5 class="fw-bold mb-3">Lowest Risk Countries</h5>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Country</th>
                            <th>Region</th>
                            <th>Risk</th>
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
                                        {{ $country['category'] }}
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

{{-- ALL COUNTRIES PREVIEW --}}
<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">All Countries Visualization Preview</h5>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Country</th>
                    <th>Region</th>
                    <th>Weather</th>
                    <th>Currency</th>
                    <th>News</th>
                    <th>Port</th>
                    <th>Inflation</th>
                    <th>Total Risk</th>
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
                                {{ $country['category'] }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <small class="text-muted">
        Tabel ini menampilkan 25 negara pertama sebagai preview.
        Semua negara tetap diproses di controller.
    </small>
</div>

{{-- EXPLANATION --}}
<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">Visualization Explanation</h5>

    <p class="text-muted mb-2">
        Halaman ini digunakan untuk melihat pola risiko global secara visual.
        Data divisualisasikan menggunakan Chart.js agar perbandingan antar negara
        dan region lebih mudah dianalisis.
    </p>

    <div class="alert alert-info mb-0">
        Indikator visualisasi meliputi weather risk, currency risk, news risk,
        port risk, inflation risk, dan total risk score.
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
                label: 'Risk Score',
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
            labels: ['Low', 'Medium', 'High', 'Critical'],
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
                label: 'Average Risk',
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
            labels: ['Weather', 'Currency', 'News', 'Port', 'Inflation'],
            datasets: [{
                label: 'Average Indicator Risk',
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