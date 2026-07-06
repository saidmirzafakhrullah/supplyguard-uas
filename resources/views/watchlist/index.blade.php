@extends('layouts.app')

@section('title', 'Favorite Monitoring List - SupplyGuard')
@section('page-title', 'Favorite Monitoring List')

@section('content')

{{-- HEADER --}}
<div class="card sg-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold mb-1">Favorite Monitoring List</h4>
            <p class="text-muted mb-0">
                Memantau negara favorit untuk melihat risiko cuaca, kurs, berita,
                pelabuhan, dan total risiko impor.
            </p>
        </div>

        <span class="badge bg-primary">Watchlist Monitoring</span>
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
            <small class="text-muted">Watchlist Countries</small>
            <h3 class="fw-bold">{{ $summary['watchlist_count'] }}</h3>
            <span class="badge-soft risk-medium">Monitored</span>
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
            <span class="badge-soft risk-high">Warning</span>
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

{{-- SELECTOR + RESULT --}}
<div class="row g-4 mt-1">
    <div class="col-lg-5">

        {{-- COUNTRY SELECTOR --}}
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-3">Add Country to Watchlist</h5>

            <label class="form-label">Country</label>
            <select id="countrySelect" class="form-select mb-3">
                @foreach($countries as $index => $country)
                    <option value="{{ $index }}">
                        {{ $country['name'] }}
                    </option>
                @endforeach
            </select>

            <button onclick="showSelectedCountry()" class="btn btn-primary w-100">
                Check Monitoring Risk
            </button>

            <div class="alert alert-info mt-3 mb-0">
                Total negara tersedia:
                <b>{{ count($countries) }}</b>
            </div>
        </div>

        {{-- SELECTED COUNTRY --}}
        <div class="card sg-card p-4 mt-4">
            <h5 class="fw-bold mb-3">Selected Country</h5>

            <div class="d-flex align-items-center gap-3 mb-3">
                <img
                    id="countryFlag"
                    src=""
                    alt="Flag"
                    style="width: 70px; border-radius: 8px; display: none;"
                >

                <div>
                    <h5 id="countryName" class="fw-bold mb-0">-</h5>
                    <small id="countryRegion" class="text-muted">-</small>
                </div>
            </div>

            <table class="table align-middle mb-0">
                <tbody>
                    <tr>
                        <td>Capital</td>
                        <td id="countryCapital" class="fw-bold text-end">-</td>
                    </tr>

                    <tr>
                        <td>Country Code</td>
                        <td id="countryCode" class="fw-bold text-end">-</td>
                    </tr>

                    <tr>
                        <td>Alert Level</td>
                        <td id="alertLevel" class="fw-bold text-end">-</td>
                    </tr>

                    <tr>
                        <td>Status</td>
                        <td class="text-end">
                            <span id="selectedStatus" class="badge-soft risk-low">-</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>

    <div class="col-lg-7">

        {{-- RISK RESULT --}}
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-3">Monitoring Risk Result</h5>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Weather Risk</small>
                        <h5 id="weatherRisk" class="fw-bold mb-0">-</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Currency Risk</small>
                        <h5 id="currencyRisk" class="fw-bold mb-0">-</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">News Risk</small>
                        <h5 id="newsRisk" class="fw-bold mb-0">-</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Port Risk</small>
                        <h5 id="portRisk" class="fw-bold mb-0">-</h5>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="border rounded p-3">
                        <small class="text-muted">Total Risk Score</small>
                        <h3 id="totalRisk" class="fw-bold mb-0">-</h3>
                    </div>
                </div>
            </div>

            <div id="recommendationBox" class="alert alert-primary mt-4 mb-0">
                Pilih negara untuk melihat rekomendasi monitoring.
            </div>
        </div>

        {{-- CHART --}}
        <div class="card sg-card p-4 mt-4">
            <h5 class="fw-bold mb-3">Monitoring Risk Chart</h5>

            <div style="height: 300px;">
                <canvas id="monitoringChart"></canvas>
            </div>
        </div>

    </div>
</div>

{{-- WATCHLIST TABLE --}}
<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">My Favorite Monitoring List</h5>

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
                    <th>Total Risk</th>
                    <th>Alert</th>
                    <th>Recommendation</th>
                </tr>
            </thead>

            <tbody>
                @foreach($watchlistCountries as $index => $country)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $country['name'] }}</td>
                        <td>{{ $country['region'] }}</td>
                        <td>{{ $country['weather_risk'] }}</td>
                        <td>{{ $country['currency_risk'] }}</td>
                        <td>{{ $country['news_risk'] }}</td>
                        <td>{{ $country['port_risk'] }}</td>
                        <td class="fw-bold">{{ $country['risk_score'] }}</td>
                        <td>
                            <span class="badge-soft {{ $country['badge'] }}">
                                {{ $country['alert_level'] }}
                            </span>
                        </td>
                        <td>{{ $country['recommendation'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <small class="text-muted">
        Tabel ini menampilkan contoh negara favorit yang dimonitor.
        Semua negara tetap tersedia pada dropdown.
    </small>
</div>

{{-- ALL COUNTRIES PREVIEW --}}
<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">All Countries Monitoring Preview</h5>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Country</th>
                    <th>Code</th>
                    <th>Region</th>
                    <th>Total Risk</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                @foreach(array_slice($countries, 0, 25) as $index => $country)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $country['name'] }}</td>
                        <td>{{ $country['code'] }}</td>
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

    <small class="text-muted">
        Preview ini hanya menampilkan 25 negara pertama.
        Semua negara tetap diproses dan tersedia di dropdown.
    </small>
</div>

{{-- EXPLANATION --}}
<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">Favorite Monitoring Explanation</h5>

    <p class="text-muted mb-2">
        Fitur Favorite Monitoring List digunakan untuk menyimpan dan memantau negara
        yang sering digunakan sebagai tujuan impor. Sistem membantu melihat apakah
        negara tersebut masih aman atau mulai berisiko.
    </p>

    <div class="alert alert-info mb-0">
        Monitoring risk dihitung dari weather risk, currency risk, news risk,
        dan port risk. Negara dengan risiko tinggi perlu dipantau lebih sering.
    </div>
</div>

@endsection

@push('scripts')
<script>
    const countries = @json($countries);

    let monitoringChart = null;

    function showSelectedCountry() {
        const selectedIndex = document.getElementById('countrySelect').value;
        const country = countries[selectedIndex];

        updateSelectedCountry(country);
        updateRiskResult(country);
        updateMonitoringChart(country);
    }

    function updateSelectedCountry(country) {
        document.getElementById('countryName').innerText = country.name ?? '-';

        document.getElementById('countryRegion').innerText =
            (country.region ?? '-') + ' / ' + (country.subregion ?? '-');

        document.getElementById('countryCapital').innerText = country.capital ?? '-';
        document.getElementById('countryCode').innerText = country.code ?? '-';
        document.getElementById('alertLevel').innerText = country.alert_level ?? '-';

        const selectedStatus = document.getElementById('selectedStatus');
        selectedStatus.className = 'badge-soft ' + country.badge;
        selectedStatus.innerText = country.category + ' Risk';

        const flag = document.getElementById('countryFlag');

        if (country.flag) {
            flag.src = country.flag;
            flag.style.display = 'block';
        } else {
            flag.style.display = 'none';
        }
    }

    function updateRiskResult(country) {
        document.getElementById('weatherRisk').innerText = country.weather_risk ?? '-';
        document.getElementById('currencyRisk').innerText = country.currency_risk ?? '-';
        document.getElementById('newsRisk').innerText = country.news_risk ?? '-';
        document.getElementById('portRisk').innerText = country.port_risk ?? '-';
        document.getElementById('totalRisk').innerText = country.risk_score ?? '-';

        const recommendationBox = document.getElementById('recommendationBox');
        recommendationBox.innerText = country.recommendation;

        if (country.category === 'Low') {
            recommendationBox.className = 'alert alert-success mt-4 mb-0';
        } else if (country.category === 'Medium') {
            recommendationBox.className = 'alert alert-warning mt-4 mb-0';
        } else if (country.category === 'High') {
            recommendationBox.className = 'alert alert-danger mt-4 mb-0';
        } else {
            recommendationBox.className = 'alert alert-dark mt-4 mb-0';
        }
    }

    function updateMonitoringChart(country) {
        const ctx = document.getElementById('monitoringChart');

        if (monitoringChart) {
            monitoringChart.destroy();
        }

        monitoringChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [
                    'Weather',
                    'Currency',
                    'News',
                    'Port',
                    'Total Risk'
                ],
                datasets: [{
                    label: country.name + ' Monitoring Risk',
                    data: [
                        country.weather_risk,
                        country.currency_risk,
                        country.news_risk,
                        country.port_risk,
                        country.risk_score
                    ],
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
    }

    document.addEventListener('DOMContentLoaded', function () {
        const indonesiaIndex = countries.findIndex(country => country.name === 'Indonesia');

        if (indonesiaIndex !== -1) {
            document.getElementById('countrySelect').value = indonesiaIndex;
        }

        showSelectedCountry();
    });
</script>
@endpush