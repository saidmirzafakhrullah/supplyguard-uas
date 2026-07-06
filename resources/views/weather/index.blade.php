@extends('layouts.app')

@section('title', 'Weather Monitoring - SupplyGuard')
@section('page-title', 'Global Weather Monitoring')

@section('content')
<div class="card sg-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold mb-1">Global Weather Monitoring</h4>
            <p class="text-muted mb-0">
                Monitoring cuaca global untuk semua negara menggunakan koordinat negara dan format data Open-Meteo API.
            </p>
        </div>

        <span class="badge bg-info text-dark">Open-Meteo API Ready</span>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Temperature</small>
            <h3 id="temperatureValue" class="fw-bold">-</h3>
            <span id="temperatureStatus" class="badge-soft risk-low">-</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Rainfall</small>
            <h3 id="rainfallValue" class="fw-bold">-</h3>
            <span id="rainfallStatus" class="badge-soft risk-low">-</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Wind Speed</small>
            <h3 id="windValue" class="fw-bold">-</h3>
            <span id="windStatus" class="badge-soft risk-low">-</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Storm Risk</small>
            <h3 id="stormValue" class="fw-bold">-</h3>
            <span id="stormStatus" class="badge-soft risk-low">-</span>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-lg-5">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-3">Country Weather Selector</h5>

            <label class="form-label">Country</label>
            <select id="countrySelect" class="form-select mb-3">
                @foreach($countries as $index => $country)
                    <option value="{{ $index }}">
                        {{ $country['name'] }}
                    </option>
                @endforeach
            </select>

            <label class="form-label">Logistics Area</label>
            <select id="areaSelect" class="form-select mb-3">
                <option>Port Area</option>
                <option>Warehouse Area</option>
                <option>Shipping Route</option>
                <option>Distribution Center</option>
            </select>

            <button onclick="showCountryWeather()" class="btn btn-primary w-100">
                Check Weather Risk
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
                        <td>Latitude</td>
                        <td id="countryLatitude" class="fw-bold text-end">-</td>
                    </tr>
                    <tr>
                        <td>Longitude</td>
                        <td id="countryLongitude" class="fw-bold text-end">-</td>
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
            <h5 class="fw-bold mb-3">Weather Risk Result</h5>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Weather Score</small>
                        <h3 id="weatherScore" class="fw-bold mb-0">-</h3>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Weather Category</small>
                        <h3 id="weatherCategory" class="fw-bold mb-0">-</h3>
                    </div>
                </div>
            </div>

            <div id="recommendationBox" class="alert alert-primary mt-4 mb-0">
                Pilih negara untuk melihat rekomendasi cuaca.
            </div>
        </div>

        <div class="card sg-card p-4 mt-4">
            <h5 class="fw-bold mb-3">Weather Component Chart</h5>
            <canvas id="weatherChart" height="130"></canvas>
        </div>
    </div>
</div>

<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">Weather Risk Rules</h5>

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
                    <td>Wind speed lebih dari 45 km/h</td>
                    <td><span class="badge-soft risk-high">High Risk</span></td>
                    <td>Pengiriman laut dan pelabuhan dapat tertunda.</td>
                </tr>
                <tr>
                    <td>Rainfall lebih dari 20 mm</td>
                    <td><span class="badge-soft risk-medium">Medium Risk</span></td>
                    <td>Distribusi darat perlu dipantau.</td>
                </tr>
                <tr>
                    <td>Temperature lebih dari 38°C</td>
                    <td><span class="badge-soft risk-medium">Medium Risk</span></td>
                    <td>Barang sensitif suhu perlu perlakuan khusus.</td>
                </tr>
                <tr>
                    <td>Storm risk tinggi</td>
                    <td><span class="badge-soft risk-high">High Risk</span></td>
                    <td>Jadwal pengiriman perlu disiapkan ulang.</td>
                </tr>
                <tr>
                    <td>Normal weather</td>
                    <td><span class="badge-soft risk-low">Low Risk</span></td>
                    <td>Aktivitas impor aman berjalan.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">All Countries Weather Preview</h5>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Country</th>
                    <th>Code</th>
                    <th>Region</th>
                    <th>Temperature</th>
                    <th>Rainfall</th>
                    <th>Wind</th>
                    <th>Storm</th>
                    <th>Score</th>
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
                        <td>{{ $country['temperature'] }}°C</td>
                        <td>{{ $country['rainfall'] }} mm</td>
                        <td>{{ $country['wind_speed'] }} km/h</td>
                        <td>{{ $country['storm_risk'] }}%</td>
                        <td class="fw-bold">{{ $country['weather_score'] }}</td>
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
    <h5 class="fw-bold mb-3">Weather Risk Calculation</h5>

    <p class="text-muted mb-2">
        Sistem menghitung weather risk berdasarkan temperature, rainfall, wind speed, dan storm risk.
    </p>

    <div class="alert alert-info mb-0">
        Weather Score =
        (Temperature Impact × 25%) +
        (Rainfall Impact × 30%) +
        (Wind Impact × 25%) +
        (Storm Risk × 20%).
    </div>
</div>
@endsection

@push('scripts')
<script>
    const countries = @json($countries);
    let weatherChart = null;

    function getStatus(value, type) {
        if (type === 'temperature') {
            if (value > 38 || value < 5) {
                return {
                    text: 'High',
                    badge: 'risk-high'
                };
            }

            if (value > 32) {
                return {
                    text: 'Medium',
                    badge: 'risk-medium'
                };
            }

            return {
                text: 'Normal',
                badge: 'risk-low'
            };
        }

        if (type === 'rainfall') {
            if (value > 30) {
                return {
                    text: 'High',
                    badge: 'risk-high'
                };
            }

            if (value > 10) {
                return {
                    text: 'Medium',
                    badge: 'risk-medium'
                };
            }

            return {
                text: 'Low',
                badge: 'risk-low'
            };
        }

        if (type === 'wind') {
            if (value > 45) {
                return {
                    text: 'High',
                    badge: 'risk-high'
                };
            }

            if (value > 25) {
                return {
                    text: 'Medium',
                    badge: 'risk-medium'
                };
            }

            return {
                text: 'Safe',
                badge: 'risk-low'
            };
        }

        if (type === 'storm') {
            if (value > 60) {
                return {
                    text: 'High',
                    badge: 'risk-high'
                };
            }

            if (value > 30) {
                return {
                    text: 'Medium',
                    badge: 'risk-medium'
                };
            }

            return {
                text: 'Low',
                badge: 'risk-low'
            };
        }
    }

    function calculateWeatherRisk(temperature, rainfall, windSpeed, stormRisk) {
        let temperatureImpact = 10;

        if (temperature > 38) {
            temperatureImpact = 75;
        } else if (temperature > 32) {
            temperatureImpact = 45;
        } else if (temperature < 5) {
            temperatureImpact = 50;
        }

        let rainfallImpact = 10;

        if (rainfall > 30) {
            rainfallImpact = 75;
        } else if (rainfall > 20) {
            rainfallImpact = 55;
        } else if (rainfall > 10) {
            rainfallImpact = 35;
        }

        let windImpact = 10;

        if (windSpeed > 45) {
            windImpact = 75;
        } else if (windSpeed > 35) {
            windImpact = 55;
        } else if (windSpeed > 25) {
            windImpact = 35;
        }

        const weatherScore = (
            (temperatureImpact * 0.25) +
            (rainfallImpact * 0.30) +
            (windImpact * 0.25) +
            (stormRisk * 0.20)
        ).toFixed(2);

        let category = 'Low';
        let badge = 'risk-low';
        let recommendation = 'Weather condition is safe for import activity.';

        if (weatherScore > 25 && weatherScore <= 50) {
            category = 'Medium';
            badge = 'risk-medium';
            recommendation = 'Monitor weather condition before shipment.';
        } else if (weatherScore > 50 && weatherScore <= 75) {
            category = 'High';
            badge = 'risk-high';
            recommendation = 'Prepare alternative shipping schedule.';
        } else if (weatherScore > 75) {
            category = 'Critical';
            badge = 'bg-dark text-white';
            recommendation = 'Delay shipment until weather condition improves.';
        }

        return {
            temperatureImpact,
            rainfallImpact,
            windImpact,
            weatherScore,
            category,
            badge,
            recommendation
        };
    }

    function updateBadge(elementId, value, type) {
        const status = getStatus(value, type);
        const element = document.getElementById(elementId);

        element.innerText = status.text;
        element.className = 'badge-soft ' + status.badge;
    }

    function updateWeatherUI(country, temperature, rainfall, windSpeed, stormRisk, source) {
        const result = calculateWeatherRisk(temperature, rainfall, windSpeed, stormRisk);

        document.getElementById('temperatureValue').innerText = temperature + '°C';
        document.getElementById('rainfallValue').innerText = rainfall + ' mm';
        document.getElementById('windValue').innerText = windSpeed + ' km/h';
        document.getElementById('stormValue').innerText = stormRisk + '%';

        updateBadge('temperatureStatus', temperature, 'temperature');
        updateBadge('rainfallStatus', rainfall, 'rainfall');
        updateBadge('windStatus', windSpeed, 'wind');
        updateBadge('stormStatus', stormRisk, 'storm');

        document.getElementById('countryName').innerText = country.name ?? '-';
        document.getElementById('countryRegion').innerText = (country.region ?? '-') + ' / ' + (country.subregion ?? '-');
        document.getElementById('countryCapital').innerText = country.capital ?? '-';
        document.getElementById('countryLatitude').innerText = country.latitude ?? '-';
        document.getElementById('countryLongitude').innerText = country.longitude ?? '-';
        document.getElementById('dataSource').innerText = source;

        const flag = document.getElementById('countryFlag');

        if (country.flag) {
            flag.src = country.flag;
            flag.style.display = 'block';
        } else {
            flag.style.display = 'none';
        }

        document.getElementById('weatherScore').innerText = result.weatherScore;
        document.getElementById('weatherCategory').innerText = result.category + ' Risk';
        document.getElementById('weatherCategory').className = 'fw-bold mb-0';

        const recommendationBox = document.getElementById('recommendationBox');
        recommendationBox.innerText = result.recommendation;
        recommendationBox.className = 'alert alert-primary mt-4 mb-0';

        if (weatherChart) {
            weatherChart.destroy();
        }

        weatherChart = new Chart(document.getElementById('weatherChart'), {
            type: 'bar',
            data: {
                labels: ['Temperature', 'Rainfall', 'Wind', 'Storm'],
                datasets: [{
                    label: 'Weather Component',
                    data: [
                        result.temperatureImpact,
                        result.rainfallImpact,
                        result.windImpact,
                        stormRisk
                    ],
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

    async function showCountryWeather() {
        const selectedIndex = document.getElementById('countrySelect').value;
        const country = countries[selectedIndex];

        document.getElementById('dataSource').innerText = 'Loading Open-Meteo...';

        try {
            const latitude = country.latitude;
            const longitude = country.longitude;

            const url = `https://api.open-meteo.com/v1/forecast?latitude=${latitude}&longitude=${longitude}&current=temperature_2m,precipitation,wind_speed_10m,weather_code`;

            const response = await fetch(url);
            const data = await response.json();

            if (!data.current) {
                throw new Error('Open-Meteo data tidak tersedia');
            }

            const temperature = Math.round(data.current.temperature_2m ?? country.temperature);
            const rainfall = Math.round(data.current.precipitation ?? country.rainfall);
            const windSpeed = Math.round(data.current.wind_speed_10m ?? country.wind_speed);

            let stormRisk = country.storm_risk;

            if (data.current.weather_code >= 95) {
                stormRisk = 80;
            } else if (windSpeed > 45 || rainfall > 30) {
                stormRisk = 60;
            } else if (windSpeed > 30 || rainfall > 15) {
                stormRisk = 35;
            } else {
                stormRisk = 15;
            }

            updateWeatherUI(country, temperature, rainfall, windSpeed, stormRisk, 'Open-Meteo API');
        } catch (error) {
            updateWeatherUI(
                country,
                country.temperature,
                country.rainfall,
                country.wind_speed,
                country.storm_risk,
                'Fallback simulation data'
            );
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        showCountryWeather();
    });
</script>
@endpush