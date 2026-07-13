<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ApiLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class SupplyGuardApiController extends Controller
{
    /**
     * GET /api/countries
     */
    public function countries(Request $request): JsonResponse
    {
        $countries = $this->filterCountries(
            $this->getCountries(),
            $request->query('country')
        );

        return response()->json([
            'success' => true,
            'message' => 'Data negara berhasil diambil.',
            'data' => $countries,
            'meta' => [
                'total' => count($countries),
                'source' => 'REST Countries API v5',
                'generated_at' => now()->toISOString(),
            ],
        ]);
    }

    /**
     * GET /api/risk
     */
    public function risk(Request $request): JsonResponse
    {
        $countries = $this->filterCountries(
            $this->getCountries(),
            $request->query('country')
        );

        $data = array_map(function (array $country) {
            return $this->buildRiskData($country);
        }, $countries);

        return response()->json([
            'success' => true,
            'message' => 'Data penilaian risiko berhasil dihitung.',
            'data' => $data,
            'meta' => [
                'total' => count($data),
                'method' => 'SG-Risk Weighted Scoring',
                'weights' => [
                    'weather' => 30,
                    'inflation' => 20,
                    'currency' => 15,
                    'news' => 25,
                    'port' => 10,
                ],
                'generated_at' => now()->toISOString(),
            ],
        ]);
    }

    /**
     * GET /api/ports
     */
    public function ports(Request $request): JsonResponse
    {
        $countries = $this->filterCountries(
            $this->getCountries(),
            $request->query('country')
        );

        $data = array_map(function (array $country) {
            return $this->buildPortData($country);
        }, $countries);

        return response()->json([
            'success' => true,
            'message' => 'Data pelabuhan berhasil diambil.',
            'data' => $data,
            'meta' => [
                'total' => count($data),
                'source' => 'Dataset internal dan simulasi pelabuhan',
                'generated_at' => now()->toISOString(),
            ],
        ]);
    }

    /**
     * GET /api/news
     */
    public function news(Request $request): JsonResponse
    {
        $countries = $this->filterCountries(
            $this->getCountries(),
            $request->query('country')
        );

        $data = array_map(function (array $country) {
            return $this->buildNewsData($country);
        }, $countries);

        return response()->json([
            'success' => true,
            'message' => 'Data intelijen berita berhasil diambil.',
            'data' => $data,
            'meta' => [
                'total' => count($data),
                'method' => 'Lexicon Based Sentiment Analysis',
                'source' => 'Simulasi internal',
                'generated_at' => now()->toISOString(),
            ],
        ]);
    }

    /**
     * GET /api/currency
     */
    public function currency(Request $request): JsonResponse
    {
        $countries = $this->filterCountries(
            $this->getCountries(),
            $request->query('country')
        );

        $rates = $this->getExchangeRates();

        $data = array_map(function (array $country) use ($rates) {
            return $this->buildCurrencyData($country, $rates);
        }, $countries);

        return response()->json([
            'success' => true,
            'message' => 'Data mata uang berhasil diambil.',
            'data' => $data,
            'meta' => [
                'total' => count($data),
                'base_currency' => 'USD',
                'source' => 'Exchange Rate API dengan data cadangan',
                'generated_at' => now()->toISOString(),
            ],
        ]);
    }

    /**
     * Mengambil dan menormalkan data seluruh negara
     * dari REST Countries API v5.
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
                    $apiKey = config('services.rest_countries.key');

                    if (empty($apiKey)) {
                        throw new \RuntimeException(
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
                            throw new \RuntimeException(
                                'REST Countries API gagal. Status HTTP: '
                                . $response->status()
                            );
                        }

                        $objects = $response->json('data.objects', []);
                        $meta = $response->json('data.meta', []);

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

                                'flag' => data_get(
                                    $item,
                                    'flag.url_png'
                                ) ?? data_get(
                                    $item,
                                    'flag.url_svg'
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
                        throw new \RuntimeException(
                            'REST Countries API tidak mengembalikan data negara.'
                        );
                    }

                    $responseTime = (int) round(
                        (microtime(true) - $startedAt) * 1000
                    );

                    ApiLogService::success(
                        apiName: 'REST Countries API v5',
                        endpoint: $baseUrl,
                        feature: 'Negara Global',
                        statusCode: $response?->status() ?? 200,
                        responseTime: $responseTime,
                        description: 'Berhasil mengambil '
                            . count($countries)
                            . ' data negara dari REST Countries API v5.'
                    );

                    return $countries;
                } catch (Throwable $exception) {
                    $responseTime = (int) round(
                        (microtime(true) - $startedAt) * 1000
                    );

                    ApiLogService::failed(
                        apiName: 'REST Countries API v5',
                        endpoint: $baseUrl,
                        feature: 'Negara Global',
                        statusCode: $response?->status(),
                        responseTime: $responseTime,
                        description: 'Gagal mengambil data negara. Sistem menggunakan data cadangan.',
                        errorMessage: $exception->getMessage()
                    );

                    report($exception);

                    return $this->fallbackCountries();
                }
            }
        );
    }

    /**
     * Menyaring negara berdasarkan nama atau kode.
     */
    private function filterCountries(
        array $countries,
        ?string $keyword
    ): array {
        if ($keyword === null || trim($keyword) === '') {
            return array_values($countries);
        }

        $keyword = strtolower(trim($keyword));

        return array_values(array_filter(
            $countries,
            function (array $country) use ($keyword) {
                return str_contains(
                    strtolower($country['name']),
                    $keyword
                ) || str_contains(
                    strtolower($country['code']),
                    $keyword
                );
            }
        ));
    }

    /**
     * Membuat data penilaian risiko.
     */
    private function buildRiskData(array $country): array
    {
        $seed = abs(crc32($country['code']));

        $weatherRisk = 10 + ($seed % 66);
        $inflationRisk = 10 + (($seed >> 2) % 66);
        $currencyRisk = 10 + (($seed >> 4) % 66);
        $newsRisk = 10 + (($seed >> 6) % 66);

        $portRisk = $country['landlocked']
            ? 85
            : 10 + (($seed >> 8) % 51);

        $totalRisk = round(
            ($weatherRisk * 0.30)
            + ($inflationRisk * 0.20)
            + ($currencyRisk * 0.15)
            + ($newsRisk * 0.25)
            + ($portRisk * 0.10),
            2
        );

        $riskInfo = $this->getRiskInfo($totalRisk);

        return [
            'country' => [
                'name' => $country['name'],
                'code' => $country['code'],
                'region' => $country['region'],
                'flag' => $country['flag'],
            ],
            'indicators' => [
                'weather_risk' => $weatherRisk,
                'inflation_risk' => $inflationRisk,
                'currency_risk' => $currencyRisk,
                'news_risk' => $newsRisk,
                'port_risk' => $portRisk,
            ],
            'total_risk' => $totalRisk,
            'category' => $riskInfo['category'],
            'recommendation' => $riskInfo['recommendation'],
        ];
    }

    /**
     * Membuat data pelabuhan.
     */
    private function buildPortData(array $country): array
    {
        $knownPorts = [
            'IDN' => [
                'name' => 'Pelabuhan Tanjung Priok',
                'city' => 'Jakarta',
                'latitude' => -6.104,
                'longitude' => 106.886,
                'count' => 12,
            ],
            'SGP' => [
                'name' => 'Port of Singapore',
                'city' => 'Singapore',
                'latitude' => 1.264,
                'longitude' => 103.840,
                'count' => 5,
            ],
            'MYS' => [
                'name' => 'Port Klang',
                'city' => 'Klang',
                'latitude' => 3.000,
                'longitude' => 101.400,
                'count' => 8,
            ],
            'CHN' => [
                'name' => 'Port of Shanghai',
                'city' => 'Shanghai',
                'latitude' => 31.230,
                'longitude' => 121.474,
                'count' => 34,
            ],
            'JPN' => [
                'name' => 'Port of Yokohama',
                'city' => 'Yokohama',
                'latitude' => 35.450,
                'longitude' => 139.650,
                'count' => 22,
            ],
            'DEU' => [
                'name' => 'Port of Hamburg',
                'city' => 'Hamburg',
                'latitude' => 53.546,
                'longitude' => 9.966,
                'count' => 10,
            ],
            'USA' => [
                'name' => 'Port of Los Angeles',
                'city' => 'Los Angeles',
                'latitude' => 33.740,
                'longitude' => -118.270,
                'count' => 30,
            ],
            'NLD' => [
                'name' => 'Port of Rotterdam',
                'city' => 'Rotterdam',
                'latitude' => 51.950,
                'longitude' => 4.140,
                'count' => 14,
            ],
        ];

        if ($country['landlocked']) {
            return [
                'country' => $country['name'],
                'country_code' => $country['code'],
                'region' => $country['region'],
                'port_name' => 'Tidak Memiliki Pelabuhan Laut',
                'port_city' => '-',
                'port_count' => 0,
                'status' => 'No Seaport',
                'latitude' => $country['latitude'],
                'longitude' => $country['longitude'],
                'port_risk' => 85,
                'category' => 'High',
                'recommendation' =>
                    'Gunakan pelabuhan negara tetangga untuk akses pengiriman.',
            ];
        }

        $port = $knownPorts[$country['code']] ?? [
            'name' => 'Pelabuhan Utama ' . $country['name'],
            'city' => $country['capital'],
            'latitude' => $country['latitude'],
            'longitude' => $country['longitude'],
            'count' => 1,
        ];

        $seed = abs(crc32($country['code']));
        $portRisk = 15 + ($seed % 36);
        $riskInfo = $this->getRiskInfo($portRisk);

        return [
            'country' => $country['name'],
            'country_code' => $country['code'],
            'region' => $country['region'],
            'port_name' => $port['name'],
            'port_city' => $port['city'],
            'port_count' => $port['count'],
            'status' => $portRisk > 40 ? 'Limited' : 'Active',
            'latitude' => $port['latitude'],
            'longitude' => $port['longitude'],
            'port_risk' => $portRisk,
            'category' => $riskInfo['category'],
            'recommendation' => $portRisk > 40
                ? 'Pantau kapasitas pelabuhan dan jadwal pengiriman.'
                : 'Pelabuhan tersedia dan aktivitas impor dapat berjalan normal.',
        ];
    }

    /**
     * Membuat data berita dan sentimen.
     */
    private function buildNewsData(array $country): array
    {
        $seed = abs(crc32($country['code']));
        $sentimentNumber = $seed % 3;

        if ($sentimentNumber === 0) {
            $sentiment = 'Positive';
            $newsRisk = 20;
            $positiveCount = 3;
            $negativeCount = 0;

            $title =
                'Perdagangan dan aktivitas logistik menunjukkan pertumbuhan positif';

            $recommendation =
                'Kondisi berita mendukung kestabilan rantai pasok.';
        } elseif ($sentimentNumber === 1) {
            $sentiment = 'Neutral';
            $newsRisk = 45;
            $positiveCount = 1;
            $negativeCount = 1;

            $title =
                'Perusahaan memantau perkembangan ekonomi dan aktivitas pengiriman';

            $recommendation =
                'Lanjutkan pemantauan perkembangan berita terbaru.';
        } else {
            $sentiment = 'Negative';
            $newsRisk = 75;
            $positiveCount = 0;
            $negativeCount = 3;

            $title =
                'Keterlambatan pengiriman dan tekanan inflasi meningkatkan risiko';

            $recommendation =
                'Siapkan negara pemasok dan jalur pengiriman alternatif.';
        }

        $riskInfo = $this->getRiskInfo($newsRisk);

        return [
            'country' => $country['name'],
            'country_code' => $country['code'],
            'region' => $country['region'],
            'title' => $title . ' di ' . $country['name'],
            'category' => 'Logistics',
            'sentiment' => $sentiment,
            'positive_words' => $positiveCount,
            'negative_words' => $negativeCount,
            'news_risk' => $newsRisk,
            'risk_category' => $riskInfo['category'],
            'recommendation' => $recommendation,
            'source' => 'Simulasi internal SupplyGuard',
        ];
    }

    /**
     * Membuat data mata uang.
     */
    private function buildCurrencyData(
        array $country,
        array $rates
    ): array {
        $currencyCode = $country['currency_code'];
        $exchangeRate = $rates[$currencyCode] ?? 1;

        $seed = abs(crc32($country['code']));

        $volatility = round(
            1 + (($seed % 900) / 100),
            2
        );

        $exchangeChange = round(
            (($seed % 1000) - 500) / 100,
            2
        );

        $currencyRisk = round(
            min(
                100,
                ($volatility * 0.60)
                + (abs($exchangeChange) * 4)
            ),
            2
        );

        $riskInfo = $this->getRiskInfo($currencyRisk);

        return [
            'country' => $country['name'],
            'country_code' => $country['code'],
            'currency_code' => $currencyCode,
            'currency_name' => $country['currency_name'],
            'base_currency' => 'USD',
            'exchange_rate' => $exchangeRate,
            'volatility' => $volatility,
            'exchange_change' => $exchangeChange,
            'currency_risk' => $currencyRisk,
            'category' => $riskInfo['category'],
            'recommendation' => $riskInfo['recommendation'],
        ];
    }

    /**
     * Mengambil nilai tukar dengan mata uang dasar USD
     * dan mencatat pemanggilan API ke database.
     */
    private function getExchangeRates(): array
    {
        return Cache::remember(
            'supplyguard.api.exchange_rates',
            now()->addHours(6),
            function () {
                $startedAt = microtime(true);
                $response = null;

                $endpoint = 'https://open.er-api.com/v6/latest/USD';

                try {
                    $response = Http::timeout(15)
                        ->retry(2, 300)
                        ->get($endpoint);

                    $responseTime = (int) round(
                        (microtime(true) - $startedAt) * 1000
                    );

                    if (!$response->successful()) {
                        throw new \RuntimeException(
                            'Exchange Rate API gagal dengan status HTTP '
                            . $response->status()
                        );
                    }

                    $rates = $response->json('rates', []);

                    if (!is_array($rates) || empty($rates)) {
                        throw new \RuntimeException(
                            'Exchange Rate API tidak mengembalikan data nilai tukar.'
                        );
                    }

                    ApiLogService::success(
                        apiName: 'Exchange Rate API',
                        endpoint: $endpoint,
                        feature: 'Dampak Mata Uang',
                        statusCode: $response->status(),
                        responseTime: $responseTime,
                        description: 'Berhasil mengambil data nilai tukar mata uang dengan basis USD.'
                    );

                    return $rates;
                } catch (Throwable $exception) {
                    $responseTime = (int) round(
                        (microtime(true) - $startedAt) * 1000
                    );

                    ApiLogService::failed(
                        apiName: 'Exchange Rate API',
                        endpoint: $endpoint,
                        feature: 'Dampak Mata Uang',
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
                        'MYR' => 4.70,
                        'AUD' => 1.52,
                    ];
                }
            }
        );
    }

    /**
     * Menentukan kategori dan rekomendasi risiko.
     */
    private function getRiskInfo(float|int $risk): array
    {
        if ($risk <= 25) {
            return [
                'category' => 'Low',
                'recommendation' =>
                    'Aktivitas impor relatif aman untuk dilakukan.',
            ];
        }

        if ($risk <= 50) {
            return [
                'category' => 'Medium',
                'recommendation' =>
                    'Pantau indikator risiko sebelum melakukan impor.',
            ];
        }

        if ($risk <= 75) {
            return [
                'category' => 'High',
                'recommendation' =>
                    'Siapkan negara pemasok dan jalur pengiriman alternatif.',
            ];
        }

        return [
            'category' => 'Critical',
            'recommendation' =>
                'Aktivitas impor sebaiknya ditunda sampai risiko menurun.',
        ];
    }

    /**
     * Data cadangan jika REST Countries API gagal.
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
                'latitude' => -5,
                'longitude' => 120,
                'flag' => null,
                'landlocked' => false,
            ],
            [
                'name' => 'Singapore',
                'official_name' => 'Republic of Singapore',
                'code' => 'SGP',
                'capital' => 'Singapore',
                'region' => 'Asia',
                'subregion' => 'South-Eastern Asia',
                'population' => 5900000,
                'currency_code' => 'SGD',
                'currency_name' => 'Singapore Dollar',
                'latitude' => 1.3667,
                'longitude' => 103.8,
                'flag' => null,
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
                'latitude' => 51,
                'longitude' => 9,
                'flag' => null,
                'landlocked' => false,
            ],
            [
                'name' => 'United States',
                'official_name' =>
                    'United States of America',
                'code' => 'USA',
                'capital' => 'Washington, D.C.',
                'region' => 'Americas',
                'subregion' => 'North America',
                'population' => 334000000,
                'currency_code' => 'USD',
                'currency_name' => 'United States Dollar',
                'latitude' => 38,
                'longitude' => -97,
                'flag' => null,
                'landlocked' => false,
            ],
        ];
    }
}