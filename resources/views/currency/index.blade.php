@extends('layouts.app')

@section('title', 'Dampak Mata Uang - SupplyGuard')
@section('page-title', 'Dasbor Dampak Mata Uang')

@section('content')
<div class="card sg-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold mb-1">Dasbor Dampak Mata Uang</h4>
            <p class="text-muted mb-0">
                Pemantauan dampak perubahan kurs mata uang terhadap biaya impor untuk semua negara.
            </p>
        </div>

        <span class="badge bg-warning text-dark">Exchange Rate API Siap</span>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Mata Uang Dasar</small>
            <h3 class="fw-bold">USD</h3>
            <span class="badge-soft risk-low">Acuan</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Mata Uang Tujuan</small>
            <h3 id="targetCurrencyCard" class="fw-bold">-</h3>
            <span class="badge-soft risk-medium">Dipantau</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Nilai Tukar</small>
            <h3 id="exchangeRateCard" class="fw-bold">-</h3>
            <span id="exchangeStatusCard" class="badge-soft risk-low">-</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Risiko Mata Uang</small>
            <h3 id="currencyRiskCard" class="fw-bold">-</h3>
            <span id="currencyRiskStatusCard" class="badge-soft risk-low">-</span>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-lg-5">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-3">Pemilihan Dampak Mata Uang</h5>

            <label class="form-label">Negara</label>

            <select id="countrySelect" class="form-select mb-3">
                @foreach($countries as $index => $country)
                    <option value="{{ $index }}">
                        {{ $country['name'] }} - {{ $country['currency_code'] }}
                    </option>
                @endforeach
            </select>

            <label class="form-label">Nilai Impor dalam USD</label>

            <input
                id="importValue"
                type="number"
                class="form-control mb-3"
                value="10000"
            >

            <button onclick="calculateCurrencyImpact()" class="btn btn-primary w-100">
                Hitung Dampak Mata Uang
            </button>

            <div class="alert alert-info mt-3 mb-0">
                Total negara tersedia:
                <b>{{ count($countries) }}</b>
            </div>
        </div>

        <div class="card sg-card p-4 mt-4">
            <h5 class="fw-bold mb-3">Negara Terpilih</h5>

            <div class="d-flex align-items-center gap-3 mb-3">
                <img
                    id="countryFlag"
                    src=""
                    alt="Bendera"
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
                        <td>Ibu Kota</td>
                        <td id="countryCapital" class="fw-bold text-end">-</td>
                    </tr>

                    <tr>
                        <td>Kode Mata Uang</td>
                        <td id="currencyCode" class="fw-bold text-end">-</td>
                    </tr>

                    <tr>
                        <td>Nama Mata Uang</td>
                        <td id="currencyName" class="fw-bold text-end">-</td>
                    </tr>

                    <tr>
                        <td>Sumber Data</td>
                        <td id="dataSource" class="fw-bold text-end">-</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-3">Hasil Dampak Mata Uang</h5>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Negara Terpilih</small>
                        <h5 id="selectedCountry" class="fw-bold mb-0">-</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Nilai Tukar</small>
                        <h5 id="exchangeRate" class="fw-bold mb-0">-</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Nilai Impor USD</small>
                        <h5 id="usdValue" class="fw-bold mb-0">-</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Nilai Impor Setelah Konversi</small>
                        <h5 id="convertedValue" class="fw-bold mb-0">-</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Volatilitas Mata Uang</small>
                        <h5 id="volatilityValue" class="fw-bold mb-0">-</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Perubahan Nilai Tukar</small>
                        <h5 id="exchangeChangeValue" class="fw-bold mb-0">-</h5>
                    </div>
                </div>
            </div>

            <div id="riskBox" class="alert alert-primary mt-4 mb-0">
                Pilih negara untuk melihat dampak kurs.
            </div>
        </div>

        <div class="card sg-card p-4 mt-4">
            <h5 class="fw-bold mb-3">Tren Risiko Mata Uang</h5>
            <canvas id="currencyChart" height="130"></canvas>
        </div>
    </div>
</div>

<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">Aturan Risiko Mata Uang</h5>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Kondisi</th>
                    <th>Dampak Risiko</th>
                    <th>Dampak Bisnis</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td>Risiko mata uang 0–25</td>
                    <td>
                        <span class="badge-soft risk-low">Risiko Rendah</span>
                    </td>
                    <td>Transaksi impor relatif aman.</td>
                </tr>

                <tr>
                    <td>Risiko mata uang 26–50</td>
                    <td>
                        <span class="badge-soft risk-medium">Risiko Sedang</span>
                    </td>
                    <td>Kurs perlu dipantau sebelum transaksi.</td>
                </tr>

                <tr>
                    <td>Risiko mata uang 51–75</td>
                    <td>
                        <span class="badge-soft risk-high">Risiko Tinggi</span>
                    </td>
                    <td>Perlu dana cadangan atau negara alternatif.</td>
                </tr>

                <tr>
                    <td>Risiko mata uang 76–100</td>
                    <td>
                        <span class="badge bg-dark text-white">Risiko Kritis</span>
                    </td>
                    <td>Transaksi impor sebaiknya ditunda.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">Pratinjau Mata Uang Seluruh Negara</h5>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Negara</th>
                    <th>Kode</th>
                    <th>Wilayah</th>
                    <th>Mata Uang</th>
                    <th>Kurs terhadap USD</th>
                    <th>Volatilitas</th>
                    <th>Perubahan</th>
                    <th>Risiko</th>
                    <th>Status</th>
                    <th>Rekomendasi</th>
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

                        <td>
                            {{
                                [
                                    'Currency condition is relatively stable for import transactions.'
                                        => 'Kondisi mata uang relatif stabil untuk transaksi impor.',

                                    'Monitor exchange rate changes before import transaction.'
                                        => 'Pantau perubahan nilai tukar sebelum melakukan transaksi impor.',

                                    'Prepare reserve funds or alternative supplier country.'
                                        => 'Siapkan dana cadangan atau negara pemasok alternatif.',

                                    'Delay import transaction until currency risk decreases.'
                                        => 'Tunda transaksi impor sampai risiko mata uang menurun.'
                                ][$country['recommendation']] ?? $country['recommendation']
                            }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <small class="text-muted">
        Tabel ini menampilkan 25 negara pertama sebagai pratinjau.
        Semua negara tetap tersedia di daftar pilihan negara.
    </small>
</div>

<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">Perhitungan Risiko Mata Uang</h5>

    <p class="text-muted mb-2">
        Sistem menghitung risiko kurs berdasarkan volatilitas mata uang
        dan perubahan nilai tukar.
    </p>

    <div class="alert alert-info mb-0">
        Risiko Mata Uang =
        (Volatilitas Mata Uang × 60%) +
        (Nilai Mutlak Perubahan Kurs × 4).
    </div>
</div>

<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">Hasil Keputusan Bisnis</h5>

    <p class="text-muted mb-0">
        Fitur Dampak Mata Uang membantu perusahaan melihat pengaruh perubahan nilai tukar
        terhadap biaya impor. Jika risiko kurs tinggi, perusahaan disarankan menunda transaksi,
        menyiapkan dana cadangan, atau memilih negara alternatif dengan risiko kurs lebih rendah.
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
                message: 'Risiko mata uang berada pada kategori Rendah. Transaksi impor relatif aman.'
            };
        }

        if (risk <= 50) {
            return {
                category: 'Medium',
                badge: 'risk-medium',
                alert: 'alert alert-warning mt-4 mb-0',
                message: 'Risiko mata uang berada pada kategori Sedang. Perusahaan perlu memantau perubahan kurs sebelum impor.'
            };
        }

        if (risk <= 75) {
            return {
                category: 'High',
                badge: 'risk-high',
                alert: 'alert alert-danger mt-4 mb-0',
                message: 'Risiko mata uang berada pada kategori Tinggi. Perusahaan perlu menyiapkan dana cadangan atau negara alternatif.'
            };
        }

        return {
            category: 'Critical',
            badge: 'bg-dark text-white',
            alert: 'alert alert-dark mt-4 mb-0',
            message: 'Risiko mata uang berada pada kategori Kritis. Transaksi impor sebaiknya ditunda.'
        };
    }

    function translateCategory(category) {
        const categoryTranslations = {
            Low: 'Rendah',
            Medium: 'Sedang',
            High: 'Tinggi',
            Critical: 'Kritis'
        };

        return categoryTranslations[category] ?? category;
    }

    function updateChart(risk) {
        const ctx = document.getElementById('currencyChart');

        if (currencyChart) {
            currencyChart.destroy();
        }

        currencyChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
                datasets: [{
                    label: 'Tren Risiko Mata Uang',
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
        const importValue =
            parseFloat(document.getElementById('importValue').value) || 0;

        const convertedValue = importValue * exchangeRate;
        const risk = parseFloat(country.currency_risk);
        const riskInfo = getRiskInfo(risk);

        document.getElementById('targetCurrencyCard').innerText =
            country.currency_code;

        document.getElementById('exchangeRateCard').innerText =
            formatNumber(exchangeRate);

        document.getElementById('exchangeStatusCard').innerText =
            translateCategory(riskInfo.category);

        document.getElementById('exchangeStatusCard').className =
            'badge-soft ' + riskInfo.badge;

        document.getElementById('currencyRiskCard').innerText =
            risk + '%';

        document.getElementById('currencyRiskStatusCard').innerText =
            'Risiko ' + translateCategory(riskInfo.category);

        document.getElementById('currencyRiskStatusCard').className =
            'badge-soft ' + riskInfo.badge;

        document.getElementById('countryName').innerText =
            country.name ?? '-';

        document.getElementById('countryRegion').innerText =
            (country.region ?? '-') +
            ' / ' +
            (country.subregion ?? '-');

        document.getElementById('countryCapital').innerText =
            country.capital ?? '-';

        document.getElementById('currencyCode').innerText =
            country.currency_code ?? '-';

        document.getElementById('currencyName').innerText =
            country.currency_name ?? '-';

        document.getElementById('dataSource').innerText =
            source;

        document.getElementById('selectedCountry').innerText =
            country.name;

        document.getElementById('exchangeRate').innerText =
            '1 USD = ' +
            formatNumber(exchangeRate) +
            ' ' +
            country.currency_code;

        document.getElementById('usdValue').innerText =
            formatNumber(importValue) + ' USD';

        document.getElementById('convertedValue').innerText =
            formatNumber(convertedValue) +
            ' ' +
            country.currency_code;

        document.getElementById('volatilityValue').innerText =
            country.volatility + '%';

        document.getElementById('exchangeChangeValue').innerText =
            country.exchange_change + '%';

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
        const selectedIndex =
            document.getElementById('countrySelect').value;

        const country = countries[selectedIndex];

        document.getElementById('dataSource').innerText =
            'Memuat Exchange Rate API...';

        try {
            const response = await fetch(
                'https://open.er-api.com/v6/latest/USD'
            );

            const data = await response.json();

            if (!data.rates || !data.rates[country.currency_code]) {
                throw new Error('Mata uang tidak tersedia di API');
            }

            const apiRate = data.rates[country.currency_code];

            updateCurrencyUI(
                country,
                apiRate,
                'Exchange Rate API'
            );
        } catch (error) {
            updateCurrencyUI(
                country,
                country.exchange_rate,
                'Data simulasi cadangan'
            );
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        calculateCurrencyImpact();
    });
</script>
@endpush