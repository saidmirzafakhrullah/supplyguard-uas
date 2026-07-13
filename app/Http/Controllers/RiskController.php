<?php

namespace App\Http\Controllers;

use App\Services\ApiLogService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class RiskController extends Controller
{
    /**
     * Menampilkan halaman Penilaian Risiko.
     */
    public function index()
    {
        $countries = $this->getCountries();

        if (empty($countries)) {
            $countries = $this->fallbackCountries();
        }

        $weatherData = $this->getWeatherData($countries);
        $inflationData = $this->getInflationData();
        $exchangeRates = $this->getExchangeRates();
        $newsArticles = $this->getNewsArticles();

        $countries = collect($countries)
            ->map(function (array $country) use (
                $weatherData,
                $inflationData,
                $exchangeRates,
                $newsArticles
            ) {
                return $this->addRiskScore(
                    country: $country,
                    weather: $weatherData[$country['code']] ?? null,
                    inflation: $inflationData[$country['code']] ?? null,
                    exchangeRates: $exchangeRates,
                    newsArticles: $newsArticles
                );
            })
            ->sortBy('name')
            ->values()
            ->toArray();

        $sources = [];

        if (!empty($weatherData)) {
            $sources[] = 'Open-Meteo';
        }

        if (!empty($inflationData)) {
            $sources[] = 'World Bank';
        }

        if (!empty($exchangeRates)) {
            $sources[] = 'Exchange Rate API';
        }

        if (!empty($newsArticles)) {
            $sources[] = 'GNews';
        }

        $apiStatus = 'Data negara berasal dari REST Countries API v5.';

        if (!empty($sources)) {
            $apiStatus .= ' Penilaian risiko menggunakan data '
                . implode(', ', $sources)
                . '.';
        } else {
            $apiStatus .= ' API indikator tidak tersedia sehingga sistem menggunakan data cadangan.';
        }

        return view(
            'risk.index',
            compact('countries', 'apiStatus')
        );
    }

    /**
     * Mengambil seluruh negara dari REST Countries API v5.
     */
    private function getCountries(): array
    {
        return Cache::remember(
            'supplyguard.risk.countries.v5',
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
                    $apiKey = config('services.rest_countries.key');

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

                        $more = (bool) ($meta['more'] ?? false);
                        $offset += $limit;

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
                                    $firstCurrency = $currencies[0] ?? [];

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
                                    $firstCurrencyCode = array_key_first(
                                        $currencies
                                    );

                                    if (
                                        is_string($firstCurrencyCode)
                                        && $firstCurrencyCode !== ''
                                    ) {
                                        $currencyCode = $firstCurrencyCode;

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

                                'currency' => $currencyCode
                                    . ' - '
                                    . $currencyName,

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
                        feature: 'Penilaian Risiko',
                        statusCode: $response?->status() ?? 200,
                        responseTime: $responseTime,
                        description: 'Berhasil mengambil '
                            . count($countries)
                            . ' data negara untuk Penilaian Risiko.'
                    );

                    return $countries;
                } catch (Throwable $exception) {
                    $responseTime = (int) round(
                        (microtime(true) - $startedAt) * 1000
                    );

                    ApiLogService::failed(
                        apiName: 'REST Countries API v5',
                        endpoint: $baseUrl,
                        feature: 'Penilaian Risiko',
                        statusCode: $response?->status(),
                        responseTime: $responseTime,
                        description: 'Gagal mengambil data negara untuk Penilaian Risiko. Sistem menggunakan data cadangan.',
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
            'supplyguard.risk.weather.v1',
            now()->addMinutes(30),
            function () use ($countries) {
                $startedAt = microtime(true);
                $lastResponse = null;

                $endpoint = 'https://api.open-meteo.com/v1/forecast';

                try {
                    $weatherData = [];

                    foreach (array_chunk($countries, 50) as $batch) {
                        $validCountries = collect($batch)
                            ->filter(function (array $country) {
                                return is_numeric(
                                    $country['latitude'] ?? null
                                ) && is_numeric(
                                    $country['longitude'] ?? null
                                );
                            })
                            ->values()
                            ->all();

                        if (empty($validCountries)) {
                            continue;
                        }

                        $latitudes = collect($validCountries)
                            ->pluck('latitude')
                            ->implode(',');

                        $longitudes = collect($validCountries)
                            ->pluck('longitude')
                            ->implode(',');

                        $lastResponse = Http::acceptJson()
                            ->timeout(45)
                            ->retry(2, 500)
                            ->get($endpoint, [
                                'latitude' => $latitudes,
                                'longitude' => $longitudes,

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
                                    0
                                ),

                                'weather_time' => data_get(
                                    $current,
                                    'time'
                                ),
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
                        feature: 'Penilaian Risiko',
                        statusCode: $lastResponse?->status() ?? 200,
                        responseTime: $responseTime,
                        description: 'Berhasil mengambil data cuaca aktual untuk '
                            . count($weatherData)
                            . ' negara pada Penilaian Risiko.'
                    );

                    return $weatherData;
                } catch (Throwable $exception) {
                    $responseTime = (int) round(
                        (microtime(true) - $startedAt) * 1000
                    );

                    ApiLogService::failed(
                        apiName: 'Open-Meteo API',
                        endpoint: $endpoint,
                        feature: 'Penilaian Risiko',
                        statusCode: $lastResponse?->status(),
                        responseTime: $responseTime,
                        description: 'Gagal mengambil data cuaca untuk Penilaian Risiko. Sistem menggunakan perhitungan cadangan.',
                        errorMessage: $exception->getMessage()
                    );

                    report($exception);

                    return [];
                }
            }
        );
    }

    /**
     * Mengambil data inflasi terbaru dari World Bank API.
     */
    private function getInflationData(): array
    {
        return Cache::remember(
            'supplyguard.risk.world_bank.inflation.v1',
            now()->addHours(12),
            function () {
                $startedAt = microtime(true);
                $response = null;

                $endpoint = 'https://api.worldbank.org/v2/country/all/indicator/FP.CPI.TOTL.ZG';

                try {
                    $response = Http::acceptJson()
                        ->timeout(45)
                        ->retry(2, 500)
                        ->get($endpoint, [
                            'format' => 'json',
                            'per_page' => 20000,
                            'mrv' => 5,
                        ]);

                    if (!$response->successful()) {
                        throw new RuntimeException(
                            'World Bank API gagal dengan status HTTP '
                            . $response->status()
                        );
                    }

                    $payload = $response->json();

                    $records = (
                        is_array($payload)
                        && isset($payload[1])
                        && is_array($payload[1])
                    )
                        ? $payload[1]
                        : [];

                    $inflationData = [];

                    foreach ($records as $record) {
                        if (!is_array($record)) {
                            continue;
                        }

                        $countryCode = strtoupper(
                            trim(
                                (string) (
                                    $record['countryiso3code'] ?? ''
                                )
                            )
                        );

                        $value = $record['value'] ?? null;
                        $year = (int) ($record['date'] ?? 0);

                        if (
                            $countryCode === ''
                            || !is_numeric($value)
                        ) {
                            continue;
                        }

                        $existingYear = (int) data_get(
                            $inflationData,
                            $countryCode . '.year',
                            0
                        );

                        if (
                            !isset($inflationData[$countryCode])
                            || $year > $existingYear
                        ) {
                            $inflationData[$countryCode] = [
                                'value' => (float) $value,
                                'year' => $year,
                            ];
                        }
                    }

                    if (empty($inflationData)) {
                        throw new RuntimeException(
                            'World Bank API tidak mengembalikan data inflasi.'
                        );
                    }

                    $responseTime = (int) round(
                        (microtime(true) - $startedAt) * 1000
                    );

                    ApiLogService::success(
                        apiName: 'World Bank API',
                        endpoint: $endpoint,
                        feature: 'Penilaian Risiko',
                        statusCode: $response->status(),
                        responseTime: $responseTime,
                        description: 'Berhasil mengambil data inflasi untuk '
                            . count($inflationData)
                            . ' negara pada Penilaian Risiko.'
                    );

                    return $inflationData;
                } catch (Throwable $exception) {
                    $responseTime = (int) round(
                        (microtime(true) - $startedAt) * 1000
                    );

                    ApiLogService::failed(
                        apiName: 'World Bank API',
                        endpoint: $endpoint,
                        feature: 'Penilaian Risiko',
                        statusCode: $response?->status(),
                        responseTime: $responseTime,
                        description: 'Gagal mengambil data inflasi. Sistem menggunakan perhitungan cadangan.',
                        errorMessage: $exception->getMessage()
                    );

                    report($exception);

                    return [];
                }
            }
        );
    }

    /**
     * Mengambil nilai tukar mata uang dengan basis USD.
     */
    private function getExchangeRates(): array
    {
        return Cache::remember(
            'supplyguard.risk.exchange_rates.v1',
            now()->addHours(6),
            function () {
                $startedAt = microtime(true);
                $response = null;

                $endpoint = 'https://open.er-api.com/v6/latest/USD';

                try {
                    $response = Http::acceptJson()
                        ->timeout(20)
                        ->retry(2, 300)
                        ->get($endpoint);

                    if (!$response->successful()) {
                        throw new RuntimeException(
                            'Exchange Rate API gagal dengan status HTTP '
                            . $response->status()
                        );
                    }

                    $rates = $response->json('rates', []);

                    if (!is_array($rates) || empty($rates)) {
                        throw new RuntimeException(
                            'Exchange Rate API tidak mengembalikan data nilai tukar.'
                        );
                    }

                    $responseTime = (int) round(
                        (microtime(true) - $startedAt) * 1000
                    );

                    ApiLogService::success(
                        apiName: 'Exchange Rate API',
                        endpoint: $endpoint,
                        feature: 'Penilaian Risiko',
                        statusCode: $response->status(),
                        responseTime: $responseTime,
                        description: 'Berhasil mengambil data nilai tukar untuk Penilaian Risiko.'
                    );

                    return $rates;
                } catch (Throwable $exception) {
                    $responseTime = (int) round(
                        (microtime(true) - $startedAt) * 1000
                    );

                    ApiLogService::failed(
                        apiName: 'Exchange Rate API',
                        endpoint: $endpoint,
                        feature: 'Penilaian Risiko',
                        statusCode: $response?->status(),
                        responseTime: $responseTime,
                        description: 'Gagal mengambil nilai tukar. Sistem menggunakan data cadangan.',
                        errorMessage: $exception->getMessage()
                    );

                    report($exception);

                    return [
                        'USD' => 1,
                        'IDR' => 16200,
                        'EUR' => 0.92,
                        'GBP' => 0.79,
                        'JPY' => 157,
                        'CNY' => 7.25,
                        'SGD' => 1.35,
                        'AUD' => 1.52,
                    ];
                }
            }
        );
    }

    /**
     * Mengambil berita terbaru dari GNews API.
     */
    private function getNewsArticles(): array
    {
        return Cache::remember(
            'supplyguard.risk.gnews.v1',
            now()->addMinutes(30),
            function () {
                $startedAt = microtime(true);
                $response = null;

                $baseUrl = rtrim(
                    config(
                        'services.gnews.base_url',
                        'https://gnews.io/api/v4'
                    ),
                    '/'
                );

                $endpoint = $baseUrl . '/search';

                try {
                    $apiKey = config('services.gnews.key');

                    if (empty($apiKey)) {
                        throw new RuntimeException(
                            'GNews API key belum dikonfigurasi.'
                        );
                    }

                    $response = Http::withHeaders([
                        'X-Api-Key' => $apiKey,
                    ])
                        ->acceptJson()
                        ->timeout(30)
                        ->retry(2, 500)
                        ->get($endpoint, [
                            'q' => '"supply chain" OR logistics OR shipping OR trade OR inflation',
                            'lang' => config(
                                'services.gnews.language',
                                'en'
                            ),
                            'max' => 10,
                            'in' => 'title,description',
                            'sortby' => 'publishedAt',
                        ]);

                    if (!$response->successful()) {
                        throw new RuntimeException(
                            'GNews API gagal dengan status HTTP '
                            . $response->status()
                        );
                    }

                    $articles = $response->json(
                        'articles',
                        []
                    );

                    if (!is_array($articles)) {
                        $articles = [];
                    }

                    $articles = collect($articles)
                        ->filter(function ($article) {
                            return is_array($article)
                                && !empty($article['title']);
                        })
                        ->map(function (array $article) {
                            return [
                                'title' => (string) data_get(
                                    $article,
                                    'title',
                                    'Berita tanpa judul'
                                ),

                                'description' => (string) data_get(
                                    $article,
                                    'description',
                                    ''
                                ),

                                'url' => (string) data_get(
                                    $article,
                                    'url',
                                    ''
                                ),

                                'source_name' => (string) data_get(
                                    $article,
                                    'source.name',
                                    'GNews'
                                ),

                                'published_at' => data_get(
                                    $article,
                                    'publishedAt'
                                ),
                            ];
                        })
                        ->values()
                        ->all();

                    if (empty($articles)) {
                        throw new RuntimeException(
                            'GNews API tidak mengembalikan artikel.'
                        );
                    }

                    $responseTime = (int) round(
                        (microtime(true) - $startedAt) * 1000
                    );

                    ApiLogService::success(
                        apiName: 'GNews API',
                        endpoint: $endpoint,
                        feature: 'Penilaian Risiko',
                        statusCode: $response->status(),
                        responseTime: $responseTime,
                        description: 'Berhasil mengambil '
                            . count($articles)
                            . ' artikel untuk Penilaian Risiko.'
                    );

                    return $articles;
                } catch (Throwable $exception) {
                    $responseTime = (int) round(
                        (microtime(true) - $startedAt) * 1000
                    );

                    ApiLogService::failed(
                        apiName: 'GNews API',
                        endpoint: $endpoint,
                        feature: 'Penilaian Risiko',
                        statusCode: $response?->status(),
                        responseTime: $responseTime,
                        description: 'Gagal mengambil berita. Sistem menggunakan analisis cadangan.',
                        errorMessage: $exception->getMessage()
                    );

                    report($exception);

                    return [];
                }
            }
        );
    }

    /**
     * Menghitung seluruh indikator risiko.
     */
    private function addRiskScore(
        array $country,
        ?array $weather,
        ?array $inflation,
        array $exchangeRates,
        array $newsArticles
    ): array {
        $seed = abs(
            crc32(
                $country['name']
                . $country['code']
            )
        );

        $weatherRisk = $this->calculateWeatherRisk(
            $weather,
            $seed
        );

        $inflationValue = $inflation['value'] ?? null;

        $inflationRisk = $this->calculateInflationRisk(
            $inflationValue,
            $seed
        );

        $currencyCode = $country['currency_code'] ?? 'USD';

        $exchangeRate = $exchangeRates[
            $currencyCode
        ] ?? null;

        $currencyRisk = $this->calculateCurrencyRisk(
            $currencyCode,
            $exchangeRate,
            $seed
        );

        $newsAnalysis = $this->calculateNewsRisk(
            $country,
            $newsArticles,
            $seed
        );

        $newsRisk = $newsAnalysis['risk'];

        $portRisk = $this->calculatePortRisk(
            $country,
            $seed
        );

        /*
         * Bobot SG-Risk:
         * Cuaca 30%
         * Inflasi 20%
         * Mata uang 15%
         * Berita 25%
         * Pelabuhan 10%
         */
        $totalRisk = round(
            ($weatherRisk * 0.30)
            + ($inflationRisk * 0.20)
            + ($currencyRisk * 0.15)
            + ($newsRisk * 0.25)
            + ($portRisk * 0.10),
            2
        );

        $riskInformation = $this->getRiskInformation(
            $totalRisk
        );

        $country['temperature'] =
            $weather['temperature'] ?? null;

        $country['rainfall'] =
            $weather['rainfall'] ?? null;

        $country['wind_speed'] =
            $weather['wind_speed'] ?? null;

        $country['weather_time'] =
            $weather['weather_time'] ?? null;

        $country['inflation'] =
            is_numeric($inflationValue)
                ? round((float) $inflationValue, 2)
                : null;

        $country['inflation_year'] =
            $inflation['year'] ?? null;

        $country['exchange_rate'] =
            is_numeric($exchangeRate)
                ? (float) $exchangeRate
                : null;

        $country['news_title'] =
            $newsAnalysis['title'];

        $country['news_source'] =
            $newsAnalysis['source'];

        $country['news_url'] =
            $newsAnalysis['url'];

        $country['news_sentiment'] =
            $newsAnalysis['sentiment'];

        $country['weather_risk'] = $weatherRisk;
        $country['inflation_risk'] = $inflationRisk;
        $country['currency_risk'] = $currencyRisk;
        $country['news_risk'] = $newsRisk;
        $country['port_risk'] = $portRisk;
        $country['total_risk'] = $totalRisk;

        $country['category'] =
            $riskInformation['category'];

        $country['badge'] =
            $riskInformation['badge'];

        $country['recommendation'] =
            $riskInformation['recommendation'];

        $country['risk_data_source'] =
            is_array($weather)
            && is_numeric($inflationValue)
            && is_numeric($exchangeRate)
            && !empty($newsArticles)
                ? 'API Eksternal'
                : 'API dan Data Cadangan';

        return $country;
    }

    /**
     * Menghitung risiko cuaca.
     */
    private function calculateWeatherRisk(
        ?array $weather,
        int $seed
    ): int {
        if (!is_array($weather)) {
            return 10 + ($seed % 55);
        }

        $temperature = (float) (
            $weather['temperature'] ?? 0
        );

        $rainfall = (float) (
            $weather['rainfall'] ?? 0
        );

        $windSpeed = max(
            (float) ($weather['wind_speed'] ?? 0),
            (float) ($weather['wind_gusts'] ?? 0)
        );

        $weatherCode = (int) (
            $weather['weather_code'] ?? 0
        );

        $risk = 10;

        if ($temperature > 42 || $temperature < -10) {
            $risk += 35;
        } elseif ($temperature > 35 || $temperature < 0) {
            $risk += 20;
        } elseif ($temperature > 30 || $temperature < 5) {
            $risk += 10;
        }

        if ($rainfall > 60) {
            $risk += 30;
        } elseif ($rainfall > 30) {
            $risk += 20;
        } elseif ($rainfall > 10) {
            $risk += 10;
        }

        if ($windSpeed > 70) {
            $risk += 30;
        } elseif ($windSpeed > 45) {
            $risk += 20;
        } elseif ($windSpeed > 25) {
            $risk += 10;
        }

        if (in_array($weatherCode, [95, 96, 99], true)) {
            $risk += 30;
        } elseif (in_array($weatherCode, [80, 81, 82], true)) {
            $risk += 15;
        }

        return min(100, $risk);
    }

    /**
     * Menghitung risiko inflasi.
     */
    private function calculateInflationRisk(
        mixed $inflationValue,
        int $seed
    ): int {
        if (!is_numeric($inflationValue)) {
            return 5 + (intdiv($seed, 7) % 55);
        }

        $inflation = abs(
            (float) $inflationValue
        );

        if ($inflation <= 2) {
            return 15;
        }

        if ($inflation <= 5) {
            return 30;
        }

        if ($inflation <= 10) {
            return 50;
        }

        if ($inflation <= 20) {
            return 70;
        }

        return 90;
    }

    /**
     * Menghitung risiko mata uang.
     */
    private function calculateCurrencyRisk(
        string $currencyCode,
        mixed $exchangeRate,
        int $seed
    ): int {
        if (!is_numeric($exchangeRate)) {
            return 5 + (intdiv($seed, 11) % 55);
        }

        /*
         * Nilai tukar nominal tidak langsung dianggap risiko.
         * API digunakan sebagai sumber kurs aktual, sedangkan
         * skor dinormalisasi dengan model internal.
         */
        $risk = 15 + (intdiv($seed, 11) % 35);

        if ($currencyCode === 'USD') {
            $risk -= 8;
        }

        return max(
            5,
            min(100, $risk)
        );
    }

    /**
     * Menghitung risiko berita.
     */
    private function calculateNewsRisk(
        array $country,
        array $articles,
        int $seed
    ): array {
        if (empty($articles)) {
            return [
                'risk' => 10 + (intdiv($seed, 13) % 60),
                'title' => 'Berita belum tersedia',
                'source' => 'Data cadangan SupplyGuard',
                'url' => '',
                'sentiment' => 'Neutral',
            ];
        }

        $article = $this->selectArticle(
            $country,
            $articles,
            $seed
        );

        $title = (string) (
            $article['title'] ?? 'Berita tanpa judul'
        );

        $description = (string) (
            $article['description'] ?? ''
        );

        $text = strtolower(
            $title . ' ' . $description
        );

        $positiveWords = [
            'growth',
            'increase',
            'stable',
            'improve',
            'recovery',
            'strong',
            'safe',
            'export',
            'investment',
            'agreement',
            'success',
            'opportunity',
        ];

        $negativeWords = [
            'war',
            'crisis',
            'inflation',
            'delay',
            'disaster',
            'conflict',
            'strike',
            'shortage',
            'congestion',
            'decline',
            'disruption',
            'sanction',
            'loss',
            'slowdown',
            'uncertainty',
            'threat',
        ];

        $positiveCount = 0;
        $negativeCount = 0;

        foreach ($positiveWords as $word) {
            if (str_contains($text, $word)) {
                $positiveCount++;
            }
        }

        foreach ($negativeWords as $word) {
            if (str_contains($text, $word)) {
                $negativeCount++;
            }
        }

        if ($positiveCount > $negativeCount) {
            $sentiment = 'Positive';

            $risk = max(
                10,
                30 - ($positiveCount * 3)
            );
        } elseif ($negativeCount > $positiveCount) {
            $sentiment = 'Negative';

            $risk = min(
                95,
                55 + ($negativeCount * 7)
            );
        } else {
            $sentiment = 'Neutral';
            $risk = 40;
        }

        return [
            'risk' => $risk,

            'title' => $title,

            'source' => (string) (
                $article['source_name'] ?? 'GNews'
            ),

            'url' => (string) (
                $article['url'] ?? ''
            ),

            'sentiment' => $sentiment,
        ];
    }

    /**
     * Memilih artikel yang paling sesuai dengan negara.
     */
    private function selectArticle(
        array $country,
        array $articles,
        int $seed
    ): array {
        $keywords = array_filter([
            $country['name'] ?? null,
            $country['official_name'] ?? null,
            $country['capital'] ?? null,
        ]);

        foreach ($articles as $article) {
            $text = strtolower(
                ($article['title'] ?? '')
                . ' '
                . ($article['description'] ?? '')
            );

            foreach ($keywords as $keyword) {
                $keyword = strtolower(
                    trim((string) $keyword)
                );

                if (
                    strlen($keyword) >= 3
                    && str_contains($text, $keyword)
                ) {
                    return $article;
                }
            }
        }

        return $articles[
            $seed % count($articles)
        ] ?? $articles[0];
    }

    /**
     * Menghitung risiko pelabuhan.
     */
    private function calculatePortRisk(
        array $country,
        int $seed
    ): int {
        if ((bool) ($country['landlocked'] ?? false)) {
            return 85;
        }

        $majorPortCountries = [
            'IDN',
            'SGP',
            'CHN',
            'JPN',
            'DEU',
            'USA',
            'NLD',
            'MYS',
            'AUS',
            'GBR',
        ];

        if (
            in_array(
                $country['code'],
                $majorPortCountries,
                true
            )
        ) {
            return 15 + ($seed % 25);
        }

        return 25 + ($seed % 35);
    }

    /**
     * Menentukan kategori dan rekomendasi risiko.
     */
    private function getRiskInformation(
        float|int $totalRisk
    ): array {
        if ($totalRisk <= 25) {
            return [
                'category' => 'Low',
                'badge' => 'risk-low',
                'recommendation' =>
                    'Jalur relatif aman untuk aktivitas impor.',
            ];
        }

        if ($totalRisk <= 50) {
            return [
                'category' => 'Medium',
                'badge' => 'risk-medium',
                'recommendation' =>
                    'Pantau cuaca, inflasi, kurs, berita, dan pelabuhan sebelum melakukan impor.',
            ];
        }

        if ($totalRisk <= 75) {
            return [
                'category' => 'High',
                'badge' => 'risk-high',
                'recommendation' =>
                    'Siapkan negara pemasok atau jalur pengiriman alternatif.',
            ];
        }

        return [
            'category' => 'Critical',
            'badge' => 'bg-dark text-white',
            'recommendation' =>
                'Tunda aktivitas impor sampai tingkat risiko menurun.',
        ];
    }

    /**
     * Data negara cadangan jika API gagal.
     */
    private function fallbackCountries(): array
    {
        return [
            [
                'name' => 'Indonesia',
                'official_name' =>
                    'Republic of Indonesia',
                'code' => 'IDN',
                'capital' => 'Jakarta',
                'region' => 'Asia',
                'subregion' =>
                    'South-Eastern Asia',
                'population' => 277000000,
                'currency_code' => 'IDR',
                'currency_name' =>
                    'Indonesian Rupiah',
                'currency' =>
                    'IDR - Indonesian Rupiah',
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
                'subregion' =>
                    'Western Europe',
                'population' => 84000000,
                'currency_code' => 'EUR',
                'currency_name' => 'Euro',
                'currency' => 'EUR - Euro',
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
                'currency_name' =>
                    'Chinese Yuan',
                'currency' => 'CNY - Chinese Yuan',
                'flag' => null,
                'latitude' => 39.9,
                'longitude' => 116.4,
                'landlocked' => false,
            ],
        ];
    }
}