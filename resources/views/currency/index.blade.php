@extends('layouts.app')

@section('title', 'Currency Impact - SupplyGuard')
@section('page-title', 'Currency Impact Dashboard')

@section('content')
<div class="card sg-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold mb-1">Currency Impact Dashboard</h4>
            <p class="text-muted mb-0">
                Monitoring dampak perubahan kurs mata uang terhadap biaya impor untuk semua negara.
            </p>
        </div>

        <span class="badge bg-warning text-dark">Exchange Rate API Ready</span>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Base Currency</small>
            <h3 class="fw-bold">USD</h3>
            <span class="badge-soft risk-low">Reference</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Target Currency</small>
            <h3 id="targetCurrencyCard" class="fw-bold">-</h3>
            <span class="badge-soft risk-medium">Monitored</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Exchange Rate</small>
            <h3 id="exchangeRateCard" class="fw-bold">-</h3>
            <span id="exchangeStatusCard" class="badge-soft risk-low">-</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Currency Risk</small>
            <h3 id="currencyRiskCard" class="fw-bold">-</h3>
            <span id="currencyRiskStatusCard" class="badge-soft risk-low">-</span>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-lg-5">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-3">Currency Impact Selector</h5>

            <label class="form-label">Country</label>
            <select id="countrySelect" class="form-select mb-3">
                @foreach($countries as $index => $country)
                    <option value="{{ $index }}">
                        {{ $country['name'] }} - {{ $country['currency_code'] }}
                    </option>
                @endforeach
            </select>

            <label class="form-label">Import Value in USD</label>
            <input id="importValue" type="number" class="form-control mb-3" value="10000">

            <button onclick="calculateCurrencyImpact()" class="btn btn-primary w-100">
                Calculate Currency Impact
            </button>

            <div class="alert alert-info mt-3 mb-0">
                Total negara tersedia:
                <b>{{ count($countries) }}</b>
            </div>
        </div>

        <div class="card sg-card p-4 mt-4">
            <h5 class="fw-bold mb-3">Selected Country</h5>

            <div class="d-flex align-items-center gap-3 mb-3">
                <img id="countryFlag" src="" alt="Flag" style="width: 70px; border-radius: 8px; display: none;">
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
                        <td>Currency Code</td>
                        <td id="currencyCode" class="fw-bold text-end">-</td>
                    </tr>
                    <tr>
                        <td>Currency Name</td>
                        <td id="currencyName" class="fw-bold text-end">-</td>
                    </tr>
                    <tr>
                        <td>Data Source</td>
                        <td id="dataSource" class="fw-bold text-end">-</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-3">Currency Impact Result</h5>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Selected Country</small>
                        <h5 id="selectedCountry" class="fw-bold mb-0">-</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Exchange Rate</small>
                        <h5 id="exchangeRate" class="fw-bold mb-0">-</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Import Value USD</small>
                        <h5 id="usdValue" class="fw-bold mb-0">-</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Converted Import Value</small>
                        <h5 id="convertedValue" class="fw-bold mb-0">-</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Currency Volatility</small>
                        <h5 id="volatilityValue" class="fw-bold mb-0">-</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Exchange Change</small>
                        <h5 id="exchangeChangeValue" class="fw-bold mb-0">-</h5>
                    </div>
                </div>
            </div>

            <div id="riskBox" class="alert alert-primary mt-4 mb-0">
                Pilih negara untuk melihat dampak kurs.
            </div>
        </div>

        <div class="card sg-card p-4 mt-4">
            <h5 class="fw-bold mb-3">Currency Risk Trend</h5>
            <canvas id="currencyChart" height="130"></canvas>
        </div>
    </div>
</div>

<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">Currency Risk Rules</h5>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Condition</th>
                    <th>Risk Impact</th>
                    <th>Business Effect</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Currency risk 0 - 25</td>
                    <td><span class="badge-soft risk-low">Low Risk</span></td>
                    <td>Transaksi impor relatif aman.</td>
                </tr>
                <tr>
                    <td>Currency risk 26 - 50</td>
                    <td><span class="badge-soft risk-medium">Medium Risk</span></td>
                    <td>Kurs perlu dipantau sebelum transaksi.</td>
                </tr>
                <tr>
                    <td>Currency risk 51 - 75</td>
                    <td><span class="badge-soft risk-high">High Risk</span></td>
                    <td>Perlu dana cadangan atau negara alternatif.</td>
                </tr>
                <tr>
                    <td>Currency risk 76 - 100</td>
                    <td><span class="badge bg-dark text-white">Critical Risk</span></td>
                    <td>Transaksi impor sebaiknya ditunda.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">All Countries Currency Preview</h5>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Country</th>
                    <th>Code</th>
                    <th>Region</th>
                    <th>Currency</th>
                    <th>Rate to USD</th>
                    <th>Volatility</th>
                    <th>Change</th>
                    <th>Risk</th>
                    <th>Status</th>
                    <th>Recommendation</th>
                </tr>
            </thead>

            <tbody>
                @foreach(array_slice($countries, 0, 25) as $index => $country)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $country['name'] }}</td>
                        <td>{{ $country['code'] }}</td>
                        <td>{{ $country['region'] }}</td>
                        <td>{{ $country['currency_code'] }}</td>
                        <td>{{ $country['exchange_rate'] }}</td>
                        <td>{{ $country['volatility'] }}%</td>
                        <td>{{ $country['exchange_change'] }}%</td>
                        <td class="fw-bold">{{ $country['currency_risk'] }}</td>
                        <td>
                            <span class="badge-soft {{ $country['badge'] }}">
                                {{ $country['category'] }}
                            </span>
                        </td>
                        <td>{{ $country['recommendation'] }}</td>
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
    <h5 class="fw-bold mb-3">Currency Risk Calculation</h5>

    <p class="text-muted mb-2">
        Sistem menghitung risiko kurs berdasarkan volatilitas mata uang dan perubahan nilai tukar.
    </p>

    <div class="alert alert-info mb-0">
        Currency Risk =
        (Currency Volatility × 60%) +
        (Absolute Exchange Change × 4).
    </div>
</div>

<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">Business Decision Output</h5>

    <p class="text-muted mb-0">
        Fitur Currency Impact membantu perusahaan melihat pengaruh perubahan nilai tukar terhadap biaya impor.
        Jika risiko kurs tinggi, perusahaan disarankan menunda transaksi, menyiapkan dana cadangan,
        atau memilih negara alternatif dengan risiko kurs lebih rendah.
    </p>
</div>
@endsection

@push('scripts')
<script>
    const countries = @json($countries);
    let currencyChart = null;

    function formatNumber(number) {
        return new Intl.NumberFormat('en-US').format(number);
    }

    function getRiskInfo(risk) {
        if (risk <= 25) {
            return {
                category: 'Low',
                badge: 'risk-low',
                alert: 'alert alert-success mt-4 mb-0',
                message: 'Currency risk berada pada kategori Low. Transaksi impor relatif aman.'
            };
        }

        if (risk <= 50) {
            return {
                category: 'Medium',
                badge: 'risk-medium',
                alert: 'alert alert-warning mt-4 mb-0',
                message: 'Currency risk berada pada kategori Medium. Perusahaan perlu memantau perubahan kurs sebelum impor.'
            };
        }

        if (risk <= 75) {
            return {
                category: 'High',
                badge: 'risk-high',
                alert: 'alert alert-danger mt-4 mb-0',
                message: 'Currency risk berada pada kategori High. Perusahaan perlu menyiapkan dana cadangan atau negara alternatif.'
            };
        }

        return {
            category: 'Critical',
            badge: 'bg-dark text-white',
            alert: 'alert alert-dark mt-4 mb-0',
            message: 'Currency risk berada pada kategori Critical. Transaksi impor sebaiknya ditunda.'
        };
    }

    function updateChart(risk) {
        const ctx = document.getElementById('currencyChart');

        if (currencyChart) {
            currencyChart.destroy();
        }

        currencyChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Currency Risk Trend',
                    data: [
                        Math.max(risk - 10, 0),
                        Math.max(risk - 5, 0),
                        risk,
                        Math.min(risk + 6, 100),
                        Math.max(risk - 2, 0),
                        Math.min(risk + 8, 100)
                    ],
                    tension: 0.4,
                    borderWidth: 2
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

    function updateCurrencyUI(country, exchangeRate, source) {
        const importValue = parseFloat(document.getElementById('importValue').value) || 0;
        const convertedValue = importValue * exchangeRate;
        const risk = parseFloat(country.currency_risk);
        const riskInfo = getRiskInfo(risk);

        document.getElementById('targetCurrencyCard').innerText = country.currency_code;
        document.getElementById('exchangeRateCard').innerText = formatNumber(exchangeRate);
        document.getElementById('exchangeStatusCard').innerText = riskInfo.category;
        document.getElementById('exchangeStatusCard').className = 'badge-soft ' + riskInfo.badge;

        document.getElementById('currencyRiskCard').innerText = risk + '%';
        document.getElementById('currencyRiskStatusCard').innerText = riskInfo.category + ' Risk';
        document.getElementById('currencyRiskStatusCard').className = 'badge-soft ' + riskInfo.badge;

        document.getElementById('countryName').innerText = country.name ?? '-';
        document.getElementById('countryRegion').innerText = (country.region ?? '-') + ' / ' + (country.subregion ?? '-');
        document.getElementById('countryCapital').innerText = country.capital ?? '-';
        document.getElementById('currencyCode').innerText = country.currency_code ?? '-';
        document.getElementById('currencyName').innerText = country.currency_name ?? '-';
        document.getElementById('dataSource').innerText = source;

        document.getElementById('selectedCountry').innerText = country.name;
        document.getElementById('exchangeRate').innerText = '1 USD = ' + formatNumber(exchangeRate) + ' ' + country.currency_code;
        document.getElementById('usdValue').innerText = formatNumber(importValue) + ' USD';
        document.getElementById('convertedValue').innerText = formatNumber(convertedValue) + ' ' + country.currency_code;
        document.getElementById('volatilityValue').innerText = country.volatility + '%';
        document.getElementById('exchangeChangeValue').innerText = country.exchange_change + '%';

        const flag = document.getElementById('countryFlag');

        if (country.flag) {
            flag.src = country.flag;
            flag.style.display = 'block';
        } else {
            flag.style.display = 'none';
        }

        const riskBox = document.getElementById('riskBox');
        riskBox.className = riskInfo.alert;
        riskBox.innerText = riskInfo.message;

        updateChart(risk);
    }

    async function calculateCurrencyImpact() {
        const selectedIndex = document.getElementById('countrySelect').value;
        const country = countries[selectedIndex];

        document.getElementById('dataSource').innerText = 'Loading Exchange Rate API...';

        try {
            const response = await fetch('https://open.er-api.com/v6/latest/USD');
            const data = await response.json();

            if (!data.rates || !data.rates[country.currency_code]) {
                throw new Error('Currency tidak tersedia di API');
            }

            const apiRate = data.rates[country.currency_code];
            updateCurrencyUI(country, apiRate, 'Exchange Rate API');
        } catch (error) {
            updateCurrencyUI(country, country.exchange_rate, 'Fallback simulation data');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        calculateCurrencyImpact();
    });
</script>
@endpush