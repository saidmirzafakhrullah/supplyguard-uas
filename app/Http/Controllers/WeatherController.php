<?php

namespace App\Http\Controllers;

use App\Services\ApiLogService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class WeatherController extends Controller
{
    /**
     * Menampilkan halaman pemantauan cuaca.
     */
    public function index()
    {
        $countries = $this->getCountries();

        if (empty($countries)) {
            $countries = $this->fallbackCountries();
        }

        $weatherData = $this->getWeatherData($countries);

        $countries = collect($countries)
            ->map(function (array $country) use ($weatherData) {
                $countryWeather = $weatherData[$country['code']] ?? null;

                return $this->addWeatherRisk(
                    $country,
                    $countryWeather
                );
            })
            ->sortBy('name')
            ->values()
            ->toArray();

        if (!empty($weatherData)) {
            $apiStatus = 'Data cuaca aktual berhasil diambil dari Open-Meteo API untuk '
                . count($weatherData)
                . ' negara.';
        } else {
            $apiStatus = 'Open-Meteo API tidak dapat diakses. Sistem menggunakan data cuaca cadangan.';
        }

        return view(
            'weather.index',
            compact('countries', 'apiStatus')
        );
    }

    /**
     * Mengambil seluruh data negara dari REST Countries API v5.
     */
    private function getCountries(): array
    {
        return Cache::remember(
            'supplyguard.api.countries.v5',
            now()->addHours(12),
            function () {
                $startedAt = microtime(true);
                $response = null;

                $baseUrl = rtrim(
                    config(
                        'services.rest_countries.base_url',
                        'https://api.restcountries.com/countries/v5'
                    ),
                    '/'
                );

                try {
                    $apiKey = config(
                        'services.rest_countries.key'
                    );

                    if (empty($apiKey)) {
                        throw new RuntimeException(
                            'REST Countries API key belum dikonfigurasi.'
                        );
                    }

                    $allCountries = [];
                    $limit = 100;
                    $offset = 0;
                    $more = true;

                    while ($more) {
                        $response = Http::withToken($apiKey)
                            ->acceptJson()
                            ->timeout(30)
                            ->retry(2, 500)
                            ->get($baseUrl, [
                                'limit' => $limit,
                                'offset' => $offset,
                                'response_fields' => implode(',', [
                                    'names.common',
                                    'names.official',
                                    'codes.alpha_3',
                                    'capitals',
                                    'region',
                                    'subregion',
                                    'population',
                                    'currencies',
                                    'coordinates.lat',
                                    'coordinates.lng',
                                    'flag.url_png',
                                    'flag.url_svg',
                                    'landlocked',
                                ]),
                            ]);

                        if (!$response->successful()) {
                            throw new RuntimeException(
                                'REST Countries API gagal dengan status HTTP '
                                . $response->status()
                            );
                        }

                        $objects = $response->json(
                            'data.objects',
                            []
                        );

                        $meta = $response->json(
                            'data.meta',
                            []
                        );

                        if (!is_array($objects)) {
                            $objects = [];
                        }

                        $allCountries = array_merge(
                            $allCountries,
                            $objects
                        );

                        $more = (bool) (
                            $meta['more'] ?? false
                        );

                        $offset += $limit;

                        /*
                         * Pengamanan agar perulangan tidak
                         * berjalan tanpa batas.
                         */
                        if ($offset > 500) {
                            break;
                        }
                    }

                    $countries = collect($allCountries)
                        ->map(function (array $item) {
                            $currencies = data_get(
                                $item,
                                'currencies',
                                []
                            );

                            $currencyCode = 'USD';
                            $currencyName = 'Unknown Currency';

                            if (
                                is_array($currencies)
                                && !empty($currencies)
                            ) {
                                if (array_is_list($currencies)) {
                                    $firstCurrency =
                                        $currencies[0] ?? [];

                                    if (is_array($firstCurrency)) {
                                        $currencyCode = (string) data_get(
                                            $firstCurrency,
                                            'code',
                                            data_get(
                                                $firstCurrency,
                                                'currency_code',
                                                data_get(
                                                    $firstCurrency,
                                                    'iso_code',
                                                    'USD'
                                                )
                                            )
                                        );

                                        $currencyName = (string) data_get(
                                            $firstCurrency,
                                            'name',
                                            $currencyCode
                                        );
                                    }
                                } else {
                                    $firstCurrencyCode =
                                        array_key_first($currencies);

                                    if (
                                        is_string($firstCurrencyCode)
                                        && $firstCurrencyCode !== ''
                                    ) {
                                        $currencyCode =
                                            $firstCurrencyCode;

                                        $currencyName = (string) data_get(
                                            $currencies,
                                            $currencyCode . '.name',
                                            $currencyCode
                                        );
                                    }
                                }
                            }

                            return [
                                'name' => data_get(
                                    $item,
                                    'names.common',
                                    'Unknown'
                                ),

                                'official_name' => data_get(
                                    $item,
                                    'names.official',
                                    'Unknown'
                                ),

                                'code' => data_get(
                                    $item,
                                    'codes.alpha_3',
                                    '-'
                                ),

                                'capital' => data_get(
                                    $item,
                                    'capitals.0.name',
                                    '-'
                                ),

                                'region' => data_get(
                                    $item,
                                    'region',
                                    '-'
                                ),

                                'subregion' => data_get(
                                    $item,
                                    'subregion',
                                    '-'
                                ),

                                'population' => (int) data_get(
                                    $item,
                                    'population',
                                    0
                                ),

                                'currency_code' => $currencyCode,
                                'currency_name' => $currencyName,

                                'flag' => data_get(
                                    $item,
                                    'flag.url_png'
                                ) ?? data_get(
                                    $item,
                                    'flag.url_svg'
                                ),

                                'latitude' => (float) data_get(
                                    $item,
                                    'coordinates.lat',
                                    0
                                ),

                                'longitude' => (float) data_get(
                                    $item,
                                    'coordinates.lng',
                                    0
                                ),

                                'landlocked' => (bool) data_get(
                                    $item,
                                    'landlocked',
                                    false
                                ),
                            ];
                        })
                        ->filter(function (array $country) {
                            return $country['name'] !== 'Unknown'
                                && $country['code'] !== '-';
                        })
                        ->sortBy('name')
                        ->values()
                        ->all();

                    if (empty($countries)) {
                        throw new RuntimeException(
                            'REST Countries API tidak mengembalikan data negara.'
                        );
                    }

                    $responseTime = (int) round(
                        (microtime(true) - $startedAt) * 1000
                    );

                    ApiLogService::success(
                        apiName: 'REST Countries API v5',
                        endpoint: $baseUrl,
                        feature: 'Pemantauan Cuaca',
                        statusCode: $response?->status() ?? 200,
                        responseTime: $responseTime,
                        description: 'Berhasil mengambil '
                            . count($countries)
                            . ' data negara untuk pemantauan cuaca.'
                    );

                    return $countries;
                } catch (Throwable $exception) {
                    $responseTime = (int) round(
                        (microtime(true) - $startedAt) * 1000
                    );

                    ApiLogService::failed(
                        apiName: 'REST Countries API v5',
                        endpoint: $baseUrl,
                        feature: 'Pemantauan Cuaca',
                        statusCode: $response?->status(),
                        responseTime: $responseTime,
                        description: 'Gagal mengambil data negara untuk pemantauan cuaca.',
                        errorMessage: $exception->getMessage()
                    );

                    report($exception);

                    return $this->fallbackCountries();
                }
            }
        );
    }

    /**
     * Mengambil cuaca aktual seluruh negara dari Open-Meteo.
     */
    private function getWeatherData(array $countries): array
    {
        return Cache::remember(
            'supplyguard.weather.current.global.v1',
            now()->addMinutes(30),
            function () use ($countries) {
                $startedAt = microtime(true);
                $lastResponse = null;

                $endpoint =
                    'https://api.open-meteo.com/v1/forecast';

                try {
                    $weatherData = [];

                    /*
                     * Data negara dibagi menjadi beberapa kelompok
                     * agar panjang URL tetap aman.
                     */
                    $countryBatches = array_chunk(
                        $countries,
                        50
                    );

                    foreach ($countryBatches as $batch) {
                        $latitudes = [];
                        $longitudes = [];
                        $validCountries = [];

                        foreach ($batch as $country) {
                            if (
                                !isset(
                                    $country['latitude'],
                                    $country['longitude']
                                )
                            ) {
                                continue;
                            }

                            if (
                                !is_numeric($country['latitude'])
                                || !is_numeric($country['longitude'])
                            ) {
                                continue;
                            }

                            $latitudes[] =
                                (string) $country['latitude'];

                            $longitudes[] =
                                (string) $country['longitude'];

                            $validCountries[] = $country;
                        }

                        if (empty($validCountries)) {
                            continue;
                        }

                        $lastResponse = Http::acceptJson()
                            ->timeout(45)
                            ->retry(2, 500)
                            ->get($endpoint, [
                                'latitude' => implode(
                                    ',',
                                    $latitudes
                                ),

                                'longitude' => implode(
                                    ',',
                                    $longitudes
                                ),

                                'current' => implode(',', [
                                    'temperature_2m',
                                    'precipitation',
                                    'weather_code',
                                    'wind_speed_10m',
                                    'wind_gusts_10m',
                                ]),

                                'daily' => implode(',', [
                                    'precipitation_sum',
                                    'wind_speed_10m_max',
                                    'weather_code',
                                ]),

                                'forecast_days' => 1,
                                'timezone' => 'auto',
                                'temperature_unit' => 'celsius',
                                'wind_speed_unit' => 'kmh',
                                'precipitation_unit' => 'mm',
                            ]);

                        if (!$lastResponse->successful()) {
                            throw new RuntimeException(
                                'Open-Meteo API gagal dengan status HTTP '
                                . $lastResponse->status()
                            );
                        }

                        $locations = $lastResponse->json();

                        if (!is_array($locations)) {
                            throw new RuntimeException(
                                'Format respons Open-Meteo tidak valid.'
                            );
                        }

                        /*
                         * Jika hanya satu koordinat, Open-Meteo
                         * mengembalikan satu objek, bukan daftar.
                         */
                        if (!array_is_list($locations)) {
                            $locations = [$locations];
                        }

                        foreach (
                            $validCountries as $index => $country
                        ) {
                            $location = $locations[$index] ?? [];

                            $current = data_get(
                                $location,
                                'current',
                                []
                            );

                            $daily = data_get(
                                $location,
                                'daily',
                                []
                            );

                            $weatherData[$country['code']] = [
                                'temperature' => (float) data_get(
                                    $current,
                                    'temperature_2m',
                                    0
                                ),

                                'current_precipitation' =>
                                    (float) data_get(
                                        $current,
                                        'precipitation',
                                        0
                                    ),

                                'rainfall' => (float) data_get(
                                    $daily,
                                    'precipitation_sum.0',
                                    data_get(
                                        $current,
                                        'precipitation',
                                        0
                                    )
                                ),

                                'wind_speed' => (float) data_get(
                                    $daily,
                                    'wind_speed_10m_max.0',
                                    data_get(
                                        $current,
                                        'wind_speed_10m',
                                        0
                                    )
                                ),

                                'wind_gusts' => (float) data_get(
                                    $current,
                                    'wind_gusts_10m',
                                    0
                                ),

                                'weather_code' => (int) data_get(
                                    $current,
                                    'weather_code',
                                    data_get(
                                        $daily,
                                        'weather_code.0',
                                        0
                                    )
                                ),

                                'weather_time' => data_get(
                                    $current,
                                    'time'
                                ),

                                'source' => 'Open-Meteo API',
                            ];
                        }
                    }

                    if (empty($weatherData)) {
                        throw new RuntimeException(
                            'Open-Meteo API tidak mengembalikan data cuaca.'
                        );
                    }

                    $responseTime = (int) round(
                        (microtime(true) - $startedAt) * 1000
                    );

                    ApiLogService::success(
                        apiName: 'Open-Meteo API',
                        endpoint: $endpoint,
                        feature: 'Pemantauan Cuaca',
                        statusCode: $lastResponse?->status() ?? 200,
                        responseTime: $responseTime,
                        description: 'Berhasil mengambil data cuaca aktual untuk '
                            . count($weatherData)
                            . ' negara.'
                    );

                    return $weatherData;
                } catch (Throwable $exception) {
                    $responseTime = (int) round(
                        (microtime(true) - $startedAt) * 1000
                    );

                    ApiLogService::failed(
                        apiName: 'Open-Meteo API',
                        endpoint: $endpoint,
                        feature: 'Pemantauan Cuaca',
                        statusCode: $lastResponse?->status(),
                        responseTime: $responseTime,
                        description: 'Gagal mengambil data cuaca. Sistem menggunakan data cadangan.',
                        errorMessage: $exception->getMessage()
                    );

                    report($exception);

                    return [];
                }
            }
        );
    }

    /**
     * Menambahkan perhitungan risiko cuaca.
     */
    private function addWeatherRisk(
        array $country,
        ?array $weather = null
    ): array {
        $usingRealWeather = is_array($weather)
            && !empty($weather);

        if ($usingRealWeather) {
            $temperature = round(
                (float) ($weather['temperature'] ?? 0),
                1
            );

            $rainfall = round(
                (float) ($weather['rainfall'] ?? 0),
                1
            );

            $windSpeed = round(
                (float) ($weather['wind_speed'] ?? 0),
                1
            );

            $windGusts = round(
                (float) ($weather['wind_gusts'] ?? 0),
                1
            );

            $weatherCode = (int) (
                $weather['weather_code'] ?? 0
            );

            $stormRisk = $this->calculateStormRisk(
                $weatherCode,
                $rainfall,
                max($windSpeed, $windGusts)
            );
        } else {
            /*
             * Data simulasi hanya digunakan ketika Open-Meteo
             * tidak menyediakan cuaca untuk negara tersebut.
             */
            $seed = abs(
                crc32(
                    $country['name']
                    . $country['code']
                    . $country['region']
                )
            );

            $temperature = 12 + ($seed % 30);
            $rainfall = intdiv($seed, 7) % 45;
            $windSpeed = 5 + (intdiv($seed, 11) % 55);
            $windGusts = $windSpeed + 5;
            $weatherCode = 0;
            $stormRisk = intdiv($seed, 13) % 85;
        }

        $temperatureImpact = 10;

        if ($temperature > 42) {
            $temperatureImpact = 90;
        } elseif ($temperature > 38) {
            $temperatureImpact = 75;
        } elseif ($temperature > 32) {
            $temperatureImpact = 45;
        } elseif ($temperature < 0) {
            $temperatureImpact = 75;
        } elseif ($temperature < 5) {
            $temperatureImpact = 50;
        }

        $rainfallImpact = 10;

        if ($rainfall > 60) {
            $rainfallImpact = 90;
        } elseif ($rainfall > 30) {
            $rainfallImpact = 75;
        } elseif ($rainfall > 20) {
            $rainfallImpact = 55;
        } elseif ($rainfall > 10) {
            $rainfallImpact = 35;
        }

        $windImpact = 10;

        if ($windSpeed > 70) {
            $windImpact = 90;
        } elseif ($windSpeed > 45) {
            $windImpact = 75;
        } elseif ($windSpeed > 35) {
            $windImpact = 55;
        } elseif ($windSpeed > 25) {
            $windImpact = 35;
        }

        $weatherScore = round(
            ($temperatureImpact * 0.25)
            + ($rainfallImpact * 0.30)
            + ($windImpact * 0.25)
            + ($stormRisk * 0.20),
            2
        );

        $category = 'Low';
        $badge = 'risk-low';
        $recommendation =
            'Kondisi cuaca relatif aman untuk aktivitas impor.';

        if (
            $weatherScore > 25
            && $weatherScore <= 50
        ) {
            $category = 'Medium';
            $badge = 'risk-medium';
            $recommendation =
                'Pantau kondisi cuaca sebelum melakukan pengiriman.';
        } elseif (
            $weatherScore > 50
            && $weatherScore <= 75
        ) {
            $category = 'High';
            $badge = 'risk-high';
            $recommendation =
                'Siapkan jadwal dan jalur pengiriman alternatif.';
        } elseif ($weatherScore > 75) {
            $category = 'Critical';
            $badge = 'bg-dark text-white';
            $recommendation =
                'Tunda pengiriman sampai kondisi cuaca membaik.';
        }

        $country['temperature'] = $temperature;
        $country['rainfall'] = $rainfall;
        $country['wind_speed'] = $windSpeed;
        $country['wind_gusts'] = $windGusts;
        $country['weather_code'] = $weatherCode;
        $country['storm_risk'] = $stormRisk;
        $country['weather_score'] = $weatherScore;
        $country['category'] = $category;
        $country['badge'] = $badge;
        $country['recommendation'] = $recommendation;

        $country['weather_source'] = $usingRealWeather
            ? 'Open-Meteo API'
            : 'Data cadangan';

        $country['weather_time'] = $weather['weather_time']
            ?? null;

        return $country;
    }

    /**
     * Menghitung tingkat risiko badai berdasarkan
     * kode cuaca WMO, hujan, dan kecepatan angin.
     */
    private function calculateStormRisk(
        int $weatherCode,
        float $rainfall,
        float $windSpeed
    ): int {
        $risk = 10;

        if (in_array($weatherCode, [95, 96, 99], true)) {
            $risk = 85;
        } elseif (in_array($weatherCode, [80, 81, 82], true)) {
            $risk = 60;
        } elseif (
            in_array(
                $weatherCode,
                [61, 63, 65, 66, 67],
                true
            )
        ) {
            $risk = 45;
        } elseif (
            in_array(
                $weatherCode,
                [71, 73, 75, 77, 85, 86],
                true
            )
        ) {
            $risk = 55;
        } elseif (in_array($weatherCode, [45, 48], true)) {
            $risk = 35;
        } elseif (
            in_array(
                $weatherCode,
                [51, 53, 55, 56, 57],
                true
            )
        ) {
            $risk = 30;
        }

        if ($rainfall > 30) {
            $risk += 15;
        } elseif ($rainfall > 15) {
            $risk += 10;
        } elseif ($rainfall > 5) {
            $risk += 5;
        }

        if ($windSpeed > 70) {
            $risk += 20;
        } elseif ($windSpeed > 45) {
            $risk += 15;
        } elseif ($windSpeed > 30) {
            $risk += 10;
        }

        return min(100, $risk);
    }

    /**
     * Data negara cadangan jika API gagal.
     */
    private function fallbackCountries(): array
    {
        return [
            [
                'name' => 'Indonesia',
                'official_name' => 'Republic of Indonesia',
                'code' => 'IDN',
                'capital' => 'Jakarta',
                'region' => 'Asia',
                'subregion' => 'South-Eastern Asia',
                'population' => 277000000,
                'currency_code' => 'IDR',
                'currency_name' => 'Indonesian Rupiah',
                'flag' => null,
                'latitude' => -6.2,
                'longitude' => 106.8,
                'landlocked' => false,
            ],
            [
                'name' => 'Germany',
                'official_name' =>
                    'Federal Republic of Germany',
                'code' => 'DEU',
                'capital' => 'Berlin',
                'region' => 'Europe',
                'subregion' => 'Western Europe',
                'population' => 84000000,
                'currency_code' => 'EUR',
                'currency_name' => 'Euro',
                'flag' => null,
                'latitude' => 52.5,
                'longitude' => 13.4,
                'landlocked' => false,
            ],
            [
                'name' => 'China',
                'official_name' =>
                    'People’s Republic of China',
                'code' => 'CHN',
                'capital' => 'Beijing',
                'region' => 'Asia',
                'subregion' => 'Eastern Asia',
                'population' => 1400000000,
                'currency_code' => 'CNY',
                'currency_name' => 'Chinese Yuan',
                'flag' => null,
                'latitude' => 39.9,
                'longitude' => 116.4,
                'landlocked' => false,
            ],
        ];
    }
}