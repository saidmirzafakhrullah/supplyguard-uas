@extends('layouts.app')

@section('title', 'Country Comparison - SupplyGuard')
@section('page-title', 'Country Comparison Engine')

@section('content')

{{-- HEADER --}}
<div class="card sg-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold mb-1">Country Comparison Engine</h4>
            <p class="text-muted mb-0">
                Membandingkan dua negara berdasarkan GDP, inflasi, cuaca, kurs, berita,
                pelabuhan, dan total risk score untuk membantu keputusan impor.
            </p>
        </div>

        <span class="badge bg-primary">Decision Support System</span>
    </div>
</div>

{{-- SELECTOR --}}
<div class="card sg-card p-4 mb-4">
    <h5 class="fw-bold mb-3">Compare Countries</h5>

    <div class="row g-3 align-items-end">
        <div class="col-md-5">
            <label class="form-label">Country 1</label>
            <select id="countryOneSelect" class="form-select">
                @foreach($countries as $index => $country)
                    <option value="{{ $index }}">
                        {{ $country['name'] }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-5">
            <label class="form-label">Country 2</label>
            <select id="countryTwoSelect" class="form-select">
                @foreach($countries as $index => $country)
                    <option value="{{ $index }}">
                        {{ $country['name'] }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <button onclick="compareCountries()" class="btn btn-primary w-100">
                Compare
            </button>
        </div>
    </div>

    <div class="alert alert-info mt-3 mb-0">
        Total negara tersedia:
        <b>{{ count($countries) }}</b>
    </div>
</div>

{{-- COUNTRY RESULT CARDS --}}
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card sg-card p-4">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h5 id="countryOneName" class="fw-bold mb-0">-</h5>
                    <small class="text-muted">Country 1</small>
                </div>

                <span id="countryOneBadge" class="badge-soft risk-low">-</span>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">GDP Score</small>
                        <h5 id="countryOneGdp" class="mb-0 text-success">-</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Inflation Risk</small>
                        <h5 id="countryOneInflation" class="mb-0 text-warning">-</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Weather Risk</small>
                        <h5 id="countryOneWeather" class="mb-0 text-warning">-</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Currency Risk</small>
                        <h5 id="countryOneCurrency" class="mb-0 text-warning">-</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">News Risk</small>
                        <h5 id="countryOneNews" class="mb-0 text-danger">-</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Total Risk</small>
                        <h5 id="countryOneTotal" class="mb-0 fw-bold">-</h5>
                    </div>
                </div>
            </div>

            <div id="countryOneRecommendation" class="alert alert-warning mt-4 mb-0">
                -
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card sg-card p-4">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h5 id="countryTwoName" class="fw-bold mb-0">-</h5>
                    <small class="text-muted">Country 2</small>
                </div>

                <span id="countryTwoBadge" class="badge-soft risk-low">-</span>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">GDP Score</small>
                        <h5 id="countryTwoGdp" class="mb-0 text-success">-</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Inflation Risk</small>
                        <h5 id="countryTwoInflation" class="mb-0 text-warning">-</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Weather Risk</small>
                        <h5 id="countryTwoWeather" class="mb-0 text-warning">-</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Currency Risk</small>
                        <h5 id="countryTwoCurrency" class="mb-0 text-warning">-</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">News Risk</small>
                        <h5 id="countryTwoNews" class="mb-0 text-danger">-</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Total Risk</small>
                        <h5 id="countryTwoTotal" class="mb-0 fw-bold">-</h5>
                    </div>
                </div>
            </div>

            <div id="countryTwoRecommendation" class="alert alert-success mt-4 mb-0">
                -
            </div>
        </div>
    </div>
</div>

{{-- CHART --}}
<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-1">Comparison Chart</h5>
    <small class="text-muted">
        Grafik perbandingan indikator risiko antara dua negara.
    </small>

    <div class="mt-3" style="height: 340px;">
        <canvas id="comparisonChart"></canvas>
    </div>
</div>

{{-- COMPARISON TABLE --}}
<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">Comparison Result Table</h5>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Indicator</th>
                    <th id="tableCountryOne">Country 1</th>
                    <th id="tableCountryTwo">Country 2</th>
                    <th>Better Country</th>
                    <th>Reason</th>
                </tr>
            </thead>

            <tbody id="comparisonTableBody">
                {{-- Diisi otomatis oleh JavaScript --}}
            </tbody>
        </table>
    </div>
</div>

{{-- FINAL DECISION --}}
<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">Final Decision Recommendation</h5>

    <div id="finalDecisionBox" class="alert alert-primary mb-0">
        Pilih dua negara lalu klik Compare untuk melihat rekomendasi.
    </div>
</div>

{{-- PREVIEW --}}
<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">All Countries Comparison Preview</h5>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Country</th>
                    <th>Region</th>
                    <th>GDP</th>
                    <th>Inflation</th>
                    <th>Weather</th>
                    <th>Currency</th>
                    <th>News</th>
                    <th>Port</th>
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
                        <td>{{ $country['gdp_score'] }}</td>
                        <td>{{ $country['inflation_risk'] }}</td>
                        <td>{{ $country['weather_risk'] }}</td>
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
        Tabel ini hanya preview 25 negara pertama. Semua negara tetap tersedia di dropdown.
    </small>
</div>

@endsection

@push('scripts')
<script>
    const countries = @json($countries);

    let comparisonChart = null;

    function compareCountries() {
        const indexOne = document.getElementById('countryOneSelect').value;
        const indexTwo = document.getElementById('countryTwoSelect').value;

        const countryOne = countries[indexOne];
        const countryTwo = countries[indexTwo];

        updateCountryCard('One', countryOne);
        updateCountryCard('Two', countryTwo);
        updateComparisonTable(countryOne, countryTwo);
        updateFinalDecision(countryOne, countryTwo);
        updateComparisonChart(countryOne, countryTwo);
    }

    function updateCountryCard(position, country) {
        document.getElementById('country' + position + 'Name').innerText = country.name;
        document.getElementById('country' + position + 'Gdp').innerText = country.gdp_score;
        document.getElementById('country' + position + 'Inflation').innerText = country.inflation_risk;
        document.getElementById('country' + position + 'Weather').innerText = country.weather_risk;
        document.getElementById('country' + position + 'Currency').innerText = country.currency_risk;
        document.getElementById('country' + position + 'News').innerText = country.news_risk;
        document.getElementById('country' + position + 'Total').innerText = country.total_risk;

        const badge = document.getElementById('country' + position + 'Badge');
        badge.className = 'badge-soft ' + country.badge;
        badge.innerText = country.category + ' Risk';

        const box = document.getElementById('country' + position + 'Recommendation');
        box.innerText = country.recommendation;

        if (country.category === 'Low') {
            box.className = 'alert alert-success mt-4 mb-0';
        } else if (country.category === 'Medium') {
            box.className = 'alert alert-warning mt-4 mb-0';
        } else if (country.category === 'High') {
            box.className = 'alert alert-danger mt-4 mb-0';
        } else {
            box.className = 'alert alert-dark mt-4 mb-0';
        }
    }

    function updateComparisonTable(countryOne, countryTwo) {
        document.getElementById('tableCountryOne').innerText = countryOne.name;
        document.getElementById('tableCountryTwo').innerText = countryTwo.name;

        const rows = [
            {
                indicator: 'GDP Score',
                valueOne: countryOne.gdp_score,
                valueTwo: countryTwo.gdp_score,
                betterType: 'higher',
                reason: 'GDP score lebih tinggi lebih baik.'
            },
            {
                indicator: 'Inflation Risk',
                valueOne: countryOne.inflation_risk,
                valueTwo: countryTwo.inflation_risk,
                betterType: 'lower',
                reason: 'Risiko inflasi lebih rendah lebih baik.'
            },
            {
                indicator: 'Weather Risk',
                valueOne: countryOne.weather_risk,
                valueTwo: countryTwo.weather_risk,
                betterType: 'lower',
                reason: 'Cuaca lebih stabil lebih baik.'
            },
            {
                indicator: 'Currency Risk',
                valueOne: countryOne.currency_risk,
                valueTwo: countryTwo.currency_risk,
                betterType: 'lower',
                reason: 'Risiko kurs lebih rendah lebih baik.'
            },
            {
                indicator: 'News Risk',
                valueOne: countryOne.news_risk,
                valueTwo: countryTwo.news_risk,
                betterType: 'lower',
                reason: 'Sentimen berita lebih aman lebih baik.'
            },
            {
                indicator: 'Port Risk',
                valueOne: countryOne.port_risk,
                valueTwo: countryTwo.port_risk,
                betterType: 'lower',
                reason: 'Risiko pelabuhan lebih rendah lebih baik.'
            },
            {
                indicator: 'Total Risk',
                valueOne: countryOne.total_risk,
                valueTwo: countryTwo.total_risk,
                betterType: 'lower',
                reason: 'Total risk lebih rendah lebih direkomendasikan.'
            }
        ];

        let html = '';

        rows.forEach(function (row) {
            const betterCountry = getBetterCountry(
                row.valueOne,
                row.valueTwo,
                countryOne.name,
                countryTwo.name,
                row.betterType
            );

            html += `
                <tr>
                    <td>${row.indicator}</td>
                    <td>${row.valueOne}</td>
                    <td>${row.valueTwo}</td>
                    <td>
                        <span class="badge bg-success">${betterCountry}</span>
                    </td>
                    <td>${row.reason}</td>
                </tr>
            `;
        });

        document.getElementById('comparisonTableBody').innerHTML = html;
    }

    function getBetterCountry(valueOne, valueTwo, nameOne, nameTwo, type) {
        if (Number(valueOne) === Number(valueTwo)) {
            return 'Equal';
        }

        if (type === 'higher') {
            return Number(valueOne) > Number(valueTwo) ? nameOne : nameTwo;
        }

        return Number(valueOne) < Number(valueTwo) ? nameOne : nameTwo;
    }

    function updateFinalDecision(countryOne, countryTwo) {
        const box = document.getElementById('finalDecisionBox');

        if (Number(countryOne.total_risk) < Number(countryTwo.total_risk)) {
            box.className = 'alert alert-success mb-0';
            box.innerText =
                countryOne.name +
                ' lebih direkomendasikan untuk aktivitas impor karena total risk lebih rendah dibanding ' +
                countryTwo.name +
                '.';
        } else if (Number(countryTwo.total_risk) < Number(countryOne.total_risk)) {
            box.className = 'alert alert-success mb-0';
            box.innerText =
                countryTwo.name +
                ' lebih direkomendasikan untuk aktivitas impor karena total risk lebih rendah dibanding ' +
                countryOne.name +
                '.';
        } else {
            box.className = 'alert alert-warning mb-0';
            box.innerText =
                'Kedua negara memiliki total risk yang sama. Perlu melihat indikator lain seperti port risk, currency risk, dan news risk.';
        }
    }

    function updateComparisonChart(countryOne, countryTwo) {
        const ctx = document.getElementById('comparisonChart');

        if (comparisonChart) {
            comparisonChart.destroy();
        }

        comparisonChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [
                    'GDP',
                    'Inflation',
                    'Weather',
                    'Currency',
                    'News',
                    'Port',
                    'Total Risk'
                ],
                datasets: [
                    {
                        label: countryOne.name,
                        data: [
                            countryOne.gdp_score,
                            countryOne.inflation_risk,
                            countryOne.weather_risk,
                            countryOne.currency_risk,
                            countryOne.news_risk,
                            countryOne.port_risk,
                            countryOne.total_risk
                        ],
                        borderWidth: 1
                    },
                    {
                        label: countryTwo.name,
                        data: [
                            countryTwo.gdp_score,
                            countryTwo.inflation_risk,
                            countryTwo.weather_risk,
                            countryTwo.currency_risk,
                            countryTwo.news_risk,
                            countryTwo.port_risk,
                            countryTwo.total_risk
                        ],
                        borderWidth: 1
                    }
                ]
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
        const germanyIndex = countries.findIndex(country => country.name === 'Germany');

        if (indonesiaIndex !== -1) {
            document.getElementById('countryOneSelect').value = indonesiaIndex;
        }

        if (germanyIndex !== -1) {
            document.getElementById('countryTwoSelect').value = germanyIndex;
        }

        compareCountries();
    });
</script>
@endpush