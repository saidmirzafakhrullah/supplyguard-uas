@extends('layouts.app')

@section('title', 'Risk Scoring - SupplyGuard')
@section('page-title', 'Risk Scoring Engine')

@section('content')
<div class="card sg-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold mb-1">SG-Risk Weighted Scoring Algorithm</h4>
            <p class="text-muted mb-0">
                Risk scoring untuk semua negara berdasarkan cuaca, inflasi, kurs mata uang,
                sentimen berita, dan ketersediaan pelabuhan.
            </p>
        </div>

        <span class="badge bg-danger">All Countries Risk Engine</span>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card sg-card p-4">
            <h5 class="fw-bold">Pilih Negara</h5>
            <p class="text-muted">
                Pilih negara dari dataset global untuk melihat hasil risk score.
            </p>

            <label class="form-label">Country</label>
            <select id="countrySelect" class="form-select mb-3">
                @foreach($countries as $index => $country)
                    <option value="{{ $index }}">
                        {{ $country['name'] }}
                    </option>
                @endforeach
            </select>

            <button onclick="showCountryRisk()" class="btn btn-primary w-100">
                Hitung Risk Score
            </button>

            <div class="alert alert-info mt-3 mb-0">
                Total negara tersedia:
                <b>{{ count($countries) }}</b>
            </div>
        </div>

        <div class="card sg-card p-4 mt-4">
            <h5 class="fw-bold mb-3">Formula Risk Score</h5>

            <table class="table align-middle">
                <tbody>
                    <tr>
                        <td>Weather Risk</td>
                        <td class="fw-bold text-end">30%</td>
                    </tr>
                    <tr>
                        <td>Inflation Risk</td>
                        <td class="fw-bold text-end">20%</td>
                    </tr>
                    <tr>
                        <td>Currency Risk</td>
                        <td class="fw-bold text-end">15%</td>
                    </tr>
                    <tr>
                        <td>News Sentiment Risk</td>
                        <td class="fw-bold text-end">25%</td>
                    </tr>
                    <tr>
                        <td>Port Availability Risk</td>
                        <td class="fw-bold text-end">10%</td>
                    </tr>
                </tbody>
            </table>

            <div class="alert alert-primary mb-0">
                Total Risk = semua indikator dikalikan bobot masing-masing.
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card sg-card p-4">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h5 class="fw-bold mb-1">Country Risk Profile</h5>
                    <small class="text-muted">Hasil perhitungan risiko berdasarkan negara yang dipilih.</small>
                </div>

                <img id="countryFlag" src="" alt="Flag" style="width: 70px; border-radius: 8px; display: none;">
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Country</small>
                        <h5 id="countryName" class="mb-0">-</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Region</small>
                        <h5 id="region" class="mb-0">-</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Currency</small>
                        <h5 id="currency" class="mb-0">-</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Capital</small>
                        <h5 id="capital" class="mb-0">-</h5>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="border rounded p-3">
                        <small class="text-muted">Weather Risk</small>
                        <h5 id="weatherRisk" class="mb-0">-</h5>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="border rounded p-3">
                        <small class="text-muted">Inflation Risk</small>
                        <h5 id="inflationRisk" class="mb-0">-</h5>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="border rounded p-3">
                        <small class="text-muted">Currency Risk</small>
                        <h5 id="currencyRisk" class="mb-0">-</h5>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="border rounded p-3">
                        <small class="text-muted">News Risk</small>
                        <h5 id="newsRisk" class="mb-0">-</h5>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="border rounded p-3">
                        <small class="text-muted">Port Risk</small>
                        <h5 id="portRisk" class="mb-0">-</h5>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="border rounded p-3">
                        <small class="text-muted">Total Risk</small>
                        <h5 id="totalRisk" class="mb-0 fw-bold">-</h5>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <span id="riskCategory" class="badge-soft risk-low">-</span>
            </div>

            <div id="recommendationBox" class="alert alert-primary mt-4 mb-0">
                Pilih negara untuk melihat rekomendasi.
            </div>
        </div>

        <div class="card sg-card p-4 mt-4">
            <h5 class="fw-bold mb-3">Selected Country Risk Component</h5>
            <canvas id="componentChart" height="120"></canvas>
        </div>
    </div>
</div>

<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">Risk Category</h5>

    <div class="row g-3">
        <div class="col-md-3">
            <div class="p-3 rounded risk-low">
                <b>0 - 25</b><br>
                Low Risk<br>
                <small>Aman untuk aktivitas impor.</small>
            </div>
        </div>

        <div class="col-md-3">
            <div class="p-3 rounded risk-medium">
                <b>26 - 50</b><br>
                Medium Risk<br>
                <small>Masih aman, tetapi perlu dipantau.</small>
            </div>
        </div>

        <div class="col-md-3">
            <div class="p-3 rounded risk-high">
                <b>51 - 75</b><br>
                High Risk<br>
                <small>Perlu menyiapkan negara alternatif.</small>
            </div>
        </div>

        <div class="col-md-3">
            <div class="p-3 rounded bg-dark text-white">
                <b>76 - 100</b><br>
                Critical Risk<br>
                <small>Pengiriman sebaiknya ditunda.</small>
            </div>
        </div>
    </div>
</div>

<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">All Countries Risk Preview</h5>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Country</th>
                    <th>Code</th>
                    <th>Region</th>
                    <th>Weather</th>
                    <th>Inflation</th>
                    <th>Currency</th>
                    <th>News</th>
                    <th>Port</th>
                    <th>Total</th>
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
                        <td>{{ $country['weather_risk'] }}</td>
                        <td>{{ $country['inflation_risk'] }}</td>
                        <td>{{ $country['currency_risk'] }}</td>
                        <td>{{ $country['news_risk'] }}</td>
                        <td>{{ $country['port_risk'] }}</td>
                        <td class="fw-bold">{{ $country['total_risk'] }}</td>
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
        Tabel ini menampilkan 25 negara pertama sebagai preview. Semua negara tetap tersedia di dropdown.
    </small>
</div>

<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">Calculation Explanation</h5>

    <p class="text-muted mb-2">
        Sistem menggunakan metode weighted scoring. Setiap indikator memiliki bobot berbeda:
    </p>

    <div class="alert alert-info mb-0">
        Total Risk Score =
        (Weather Risk × 30%) +
        (Inflation Risk × 20%) +
        (Currency Risk × 15%) +
        (News Sentiment Risk × 25%) +
        (Port Availability Risk × 10%).
    </div>
</div>
@endsection

@push('scripts')
<script>
    const countries = @json($countries);
    let componentChart = null;

    function showCountryRisk() {
        const selectedIndex = document.getElementById('countrySelect').value;
        const country = countries[selectedIndex];

        document.getElementById('countryName').innerText = country.name ?? '-';
        document.getElementById('region').innerText = country.region ?? '-';
        document.getElementById('currency').innerText = country.currency ?? '-';
        document.getElementById('capital').innerText = country.capital ?? '-';

        document.getElementById('weatherRisk').innerText = country.weather_risk ?? '-';
        document.getElementById('inflationRisk').innerText = country.inflation_risk ?? '-';
        document.getElementById('currencyRisk').innerText = country.currency_risk ?? '-';
        document.getElementById('newsRisk').innerText = country.news_risk ?? '-';
        document.getElementById('portRisk').innerText = country.port_risk ?? '-';
        document.getElementById('totalRisk').innerText = country.total_risk ?? '-';

        const category = document.getElementById('riskCategory');
        category.className = 'badge-soft ' + country.badge;
        category.innerText = country.category + ' Risk';

        document.getElementById('recommendationBox').innerText = country.recommendation;

        const flag = document.getElementById('countryFlag');

        if (country.flag) {
            flag.src = country.flag;
            flag.style.display = 'block';
        } else {
            flag.style.display = 'none';
        }

        const chartData = [
            country.weather_risk,
            country.inflation_risk,
            country.currency_risk,
            country.news_risk,
            country.port_risk
        ];

        if (componentChart) {
            componentChart.destroy();
        }

        componentChart = new Chart(document.getElementById('componentChart'), {
            type: 'bar',
            data: {
                labels: ['Weather', 'Inflation', 'Currency', 'News', 'Port'],
                datasets: [{
                    label: 'Risk Component',
                    data: chartData,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
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
        showCountryRisk();
    });
</script>
@endpush