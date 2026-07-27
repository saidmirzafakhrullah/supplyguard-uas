@extends('layouts.app')

@section('title', 'Pemantauan Cuaca - SupplyGuard')
@section('page-title', 'Pemantauan Cuaca Global')

@section('content')
<div class="card sg-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold mb-1">Pemantauan Cuaca Global</h4>
            <p class="text-muted mb-0">
                Pemantauan cuaca global untuk semua negara menggunakan koordinat negara
                dan format data Open-Meteo API.
            </p>
        </div>

        <span class="badge bg-info text-dark">Open-Meteo API Siap</span>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Suhu</small>
            <h3 id="temperatureValue" class="fw-bold">-</h3>
            <span id="temperatureStatus" class="badge-soft risk-low">-</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Curah Hujan</small>
            <h3 id="rainfallValue" class="fw-bold">-</h3>
            <span id="rainfallStatus" class="badge-soft risk-low">-</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Kecepatan Angin</small>
            <h3 id="windValue" class="fw-bold">-</h3>
            <span id="windStatus" class="badge-soft risk-low">-</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Risiko Badai</small>
            <h3 id="stormValue" class="fw-bold">-</h3>
            <span id="stormStatus" class="badge-soft risk-low">-</span>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-lg-5">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-3">Pemilihan Cuaca Negara</h5>

            <label class="form-label">Negara</label>

            <select id="countrySelect" class="form-select mb-3">
                @foreach($countries as $index => $country)
                    <option value="{{ $index }}">
                        {{ $country['name'] }}
                    </option>
                @endforeach
            </select>

            <label class="form-label">Area Logistik</label>

            <select id="areaSelect" class="form-select mb-3">
                <option value="port">Area Pelabuhan</option>
                <option value="warehouse">Area Gudang</option>
                <option value="shipping">Jalur Pengiriman</option>
                <option value="distribution">Pusat Distribusi</option>
            </select>

            <button onclick="showCountryWeather()" class="btn btn-primary w-100">
                Periksa Risiko Cuaca
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
                        <td>Garis Lintang</td>
                        <td id="countryLatitude" class="fw-bold text-end">-</td>
                    </tr>

                    <tr>
                        <td>Garis Bujur</td>
                        <td id="countryLongitude" class="fw-bold text-end">-</td>
                    </tr>

                    <tr>
                        <td>Area Dianalisis</td>
                        <td id="selectedArea" class="fw-bold text-end">-</td>
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
            <h5 class="fw-bold mb-3">Hasil Risiko Cuaca</h5>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Skor Cuaca</small>
                        <h3 id="weatherScore" class="fw-bold mb-0">-</h3>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Kategori Cuaca</small>
                        <h3 id="weatherCategory" class="fw-bold mb-0">-</h3>
                    </div>
                </div>
            </div>

            <div id="recommendationBox" class="alert alert-primary mt-4 mb-0">
                Pilih negara untuk melihat rekomendasi cuaca.
            </div>
        </div>

        <div class="card sg-card p-4 mt-4">
            <h5 class="fw-bold mb-3">Grafik Komponen Cuaca</h5>
            <canvas id="weatherChart" height="130"></canvas>
        </div>
    </div>
</div>

<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">Aturan Risiko Cuaca</h5>

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
                    <td>Kecepatan angin lebih dari 45 km/jam</td>
                    <td>
                        <span class="badge-soft risk-high">Risiko Tinggi</span>
                    </td>
                    <td>Pengiriman laut dan pelabuhan dapat tertunda.</td>
                </tr>

                <tr>
                    <td>Curah hujan lebih dari 20 mm</td>
                    <td>
                        <span class="badge-soft risk-medium">Risiko Sedang</span>
                    </td>
                    <td>Distribusi darat perlu dipantau.</td>
                </tr>

                <tr>
                    <td>Suhu lebih dari 38°C</td>
                    <td>
                        <span class="badge-soft risk-medium">Risiko Sedang</span>
                    </td>
                    <td>Barang sensitif suhu perlu perlakuan khusus.</td>
                </tr>

                <tr>
                    <td>Risiko badai tinggi</td>
                    <td>
                        <span class="badge-soft risk-high">Risiko Tinggi</span>
                    </td>
                    <td>Jadwal pengiriman perlu disiapkan ulang.</td>
                </tr>

                <tr>
                    <td>Cuaca normal</td>
                    <td>
                        <span class="badge-soft risk-low">Risiko Rendah</span>
                    </td>
                    <td>Aktivitas impor aman berjalan.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">Pratinjau Cuaca Seluruh Negara</h5>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Negara</th>
                    <th>Kode</th>
                    <th>Wilayah</th>
                    <th>Suhu</th>
                    <th>Curah Hujan</th>
                    <th>Angin</th>
                    <th>Badai</th>
                    <th>Skor</th>
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
                        <td>{{ $country['temperature'] }}°C</td>
                        <td>{{ $country['rainfall'] }} mm</td>
                        <td>{{ $country['wind_speed'] }} km/jam</td>
                        <td>{{ $country['storm_risk'] }}%</td>
                        <td class="fw-bold">{{ $country['weather_score'] }}</td>

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
                                    'Weather condition is safe for import activity.'
                                        => 'Kondisi cuaca aman untuk aktivitas impor.',

                                    'Monitor weather condition before shipment.'
                                        => 'Pantau kondisi cuaca sebelum pengiriman.',

                                    'Prepare alternative shipping schedule.'
                                        => 'Siapkan jadwal pengiriman alternatif.',

                                    'Delay shipment until weather condition improves.'
                                        => 'Tunda pengiriman sampai kondisi cuaca membaik.'
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
    <h5 class="fw-bold mb-3">Perhitungan Risiko Cuaca</h5>

    <p class="text-muted">Bobot disesuaikan dengan karakteristik operasional setiap area.</p>
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead><tr><th>Area</th><th>Suhu</th><th>Hujan</th><th>Angin</th><th>Badai</th></tr></thead>
            <tbody>
                <tr><td>Pelabuhan</td><td>10%</td><td>25%</td><td>35%</td><td>30%</td></tr>
                <tr><td>Gudang</td><td>45%</td><td>35%</td><td>10%</td><td>10%</td></tr>
                <tr><td>Jalur Pengiriman</td><td>15%</td><td>30%</td><td>30%</td><td>25%</td></tr>
                <tr><td>Pusat Distribusi</td><td>25%</td><td>35%</td><td>25%</td><td>15%</td></tr>
            </tbody>
        </table>
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
                    text: 'Tinggi',
                    badge: 'risk-high'
                };
            }

            if (value > 32) {
                return {
                    text: 'Sedang',
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
                    text: 'Tinggi',
                    badge: 'risk-high'
                };
            }

            if (value > 10) {
                return {
                    text: 'Sedang',
                    badge: 'risk-medium'
                };
            }

            return {
                text: 'Rendah',
                badge: 'risk-low'
            };
        }

        if (type === 'wind') {
            if (value > 45) {
                return {
                    text: 'Tinggi',
                    badge: 'risk-high'
                };
            }

            if (value > 25) {
                return {
                    text: 'Sedang',
                    badge: 'risk-medium'
                };
            }

            return {
                text: 'Aman',
                badge: 'risk-low'
            };
        }

        if (type === 'storm') {
            if (value > 60) {
                return {
                    text: 'Tinggi',
                    badge: 'risk-high'
                };
            }

            if (value > 30) {
                return {
                    text: 'Sedang',
                    badge: 'risk-medium'
                };
            }

            return {
                text: 'Rendah',
                badge: 'risk-low'
            };
        }
    }

    const areaProfiles = {
        port: {
            label: 'Area Pelabuhan', weights: [0.10, 0.25, 0.35, 0.30],
            actions: ['Operasi pelabuhan aman berjalan.', 'Pantau angin dan jadwal sandar kapal.', 'Siapkan penundaan sandar atau pelabuhan alternatif.', 'Hentikan sementara operasi pelabuhan yang berisiko.']
        },
        warehouse: {
            label: 'Area Gudang', weights: [0.45, 0.35, 0.10, 0.10],
            actions: ['Kondisi penyimpanan relatif aman.', 'Periksa ventilasi, suhu, dan perlindungan barang.', 'Amankan barang sensitif suhu serta potensi kebocoran.', 'Pindahkan barang rentan dan hentikan aktivitas gudang berisiko.']
        },
        shipping: {
            label: 'Jalur Pengiriman', weights: [0.15, 0.30, 0.30, 0.25],
            actions: ['Jalur pengiriman relatif aman.', 'Pantau kondisi rute sebelum keberangkatan.', 'Siapkan jadwal dan jalur pengiriman alternatif.', 'Tunda pengiriman sampai kondisi cuaca membaik.']
        },
        distribution: {
            label: 'Pusat Distribusi', weights: [0.25, 0.35, 0.25, 0.15],
            actions: ['Aktivitas distribusi relatif aman.', 'Pantau akses jalan dan proses bongkar muat.', 'Atur ulang jadwal distribusi dan siapkan rute darat alternatif.', 'Batasi distribusi sampai akses dan cuaca kembali aman.']
        }
    };

    function calculateWeatherRisk(temperature, rainfall, windSpeed, stormRisk, areaType) {
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

        const profile = areaProfiles[areaType] || areaProfiles.port;
        const weatherScore = (
            (temperatureImpact * profile.weights[0]) +
            (rainfallImpact * profile.weights[1]) +
            (windImpact * profile.weights[2]) +
            (stormRisk * profile.weights[3])
        ).toFixed(2);

        let category = 'Low';
        let badge = 'risk-low';
        let recommendation = profile.actions[0];

        if (weatherScore > 25 && weatherScore <= 50) {
            category = 'Medium';
            badge = 'risk-medium';
            recommendation = profile.actions[1];
        } else if (weatherScore > 50 && weatherScore <= 75) {
            category = 'High';
            badge = 'risk-high';
            recommendation = profile.actions[2];
        } else if (weatherScore > 75) {
            category = 'Critical';
            badge = 'bg-dark text-white';
            recommendation = profile.actions[3];
        }

        return {
            temperatureImpact,
            rainfallImpact,
            windImpact,
            weatherScore,
            category,
            badge,
            recommendation,
            profile
        };
    }

    function updateBadge(elementId, value, type) {
        const status = getStatus(value, type);
        const element = document.getElementById(elementId);

        element.innerText = status.text;
        element.className = 'badge-soft ' + status.badge;
    }

    function updateWeatherUI(
        country,
        temperature,
        rainfall,
        windSpeed,
        stormRisk,
        source
    ) {
        const areaType = document.getElementById('areaSelect').value;
        const result = calculateWeatherRisk(
            temperature,
            rainfall,
            windSpeed,
            stormRisk,
            areaType
        );

        document.getElementById('temperatureValue').innerText =
            temperature + '°C';

        document.getElementById('rainfallValue').innerText =
            rainfall + ' mm';

        document.getElementById('windValue').innerText =
            windSpeed + ' km/jam';

        document.getElementById('stormValue').innerText =
            stormRisk + '%';

        updateBadge(
            'temperatureStatus',
            temperature,
            'temperature'
        );

        updateBadge(
            'rainfallStatus',
            rainfall,
            'rainfall'
        );

        updateBadge(
            'windStatus',
            windSpeed,
            'wind'
        );

        updateBadge(
            'stormStatus',
            stormRisk,
            'storm'
        );

        document.getElementById('countryName').innerText =
            country.name ?? '-';

        document.getElementById('countryRegion').innerText =
            (country.region ?? '-') +
            ' / ' +
            (country.subregion ?? '-');

        document.getElementById('countryCapital').innerText =
            country.capital ?? '-';

        document.getElementById('countryLatitude').innerText =
            country.latitude ?? '-';

        document.getElementById('countryLongitude').innerText =
            country.longitude ?? '-';

        document.getElementById('selectedArea').innerText =
            result.profile.label;

        document.getElementById('dataSource').innerText = source;

        const flag = document.getElementById('countryFlag');

        if (country.flag) {
            flag.src = country.flag;
            flag.style.display = 'block';
        } else {
            flag.style.display = 'none';
        }

        const categoryTranslations = {
            Low: 'Risiko Rendah',
            Medium: 'Risiko Sedang',
            High: 'Risiko Tinggi',
            Critical: 'Risiko Kritis'
        };

        document.getElementById('weatherScore').innerText =
            result.weatherScore;

        document.getElementById('weatherCategory').innerText =
            categoryTranslations[result.category] ?? result.category;

        document.getElementById('weatherCategory').className =
            'fw-bold mb-0';

        const recommendationBox =
            document.getElementById('recommendationBox');

        recommendationBox.innerText = result.recommendation;
        recommendationBox.className =
            'alert alert-primary mt-4 mb-0';

        if (weatherChart) {
            weatherChart.destroy();
        }

        weatherChart = new Chart(
            document.getElementById('weatherChart'),
            {
                type: 'bar',
                data: {
                    labels: [
                        'Suhu',
                        'Curah Hujan',
                        'Angin',
                        'Badai'
                    ],
                    datasets: [{
                        label: 'Dampak Cuaca - ' + result.profile.label,
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
            }
        );
    }

    async function showCountryWeather() {
        const selectedIndex =
            document.getElementById('countrySelect').value;

        const country = countries[selectedIndex];

        document.getElementById('dataSource').innerText =
            'Memuat data Open-Meteo...';

        try {
            const latitude = country.latitude;
            const longitude = country.longitude;

            const url =
                `https://api.open-meteo.com/v1/forecast?latitude=${latitude}&longitude=${longitude}&current=temperature_2m,precipitation,wind_speed_10m,weather_code`;

            const response = await fetch(url);
            const data = await response.json();

            if (!data.current) {
                throw new Error('Data Open-Meteo tidak tersedia');
            }

            const temperature = Math.round(
                data.current.temperature_2m ??
                country.temperature
            );

            const rainfall = Math.round(
                data.current.precipitation ??
                country.rainfall
            );

            const windSpeed = Math.round(
                data.current.wind_speed_10m ??
                country.wind_speed
            );

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

            updateWeatherUI(
                country,
                temperature,
                rainfall,
                windSpeed,
                stormRisk,
                'Open-Meteo API'
            );
        } catch (error) {
            updateWeatherUI(
                country,
                country.temperature,
                country.rainfall,
                country.wind_speed,
                country.storm_risk,
                'Data simulasi cadangan'
            );
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('countrySelect')
            .addEventListener('change', showCountryWeather);
        document.getElementById('areaSelect')
            .addEventListener('change', showCountryWeather);
        showCountryWeather();
    });
</script>
@endpush
