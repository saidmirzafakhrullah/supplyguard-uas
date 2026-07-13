<?php

namespace App\Http\Controllers;

use App\Services\ApiLogService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class ComparisonController extends Controller
{
    /**
     * Menampilkan halaman Perbandingan Negara.
     */
    public function index()
    {
        $countries = $this->getCountries();

        if (empty($countries)) {
            $countries = $this->fallbackCountries();
        }

        $worldBankData = $this->getWorldBankData();

        $maximumGdp = collect($worldBankData)
            ->map(function (array $item) {
                return data_get($item, 'gdp.value');
            })
            ->filter(function ($value) {
                return is_numeric($value) && $value > 0;
            })
            ->max();

        $maximumGdp = is_numeric($maximumGdp)
            ? (float) $maximumGdp
            : 0;

        $countries = collect($countries)
            ->map(function (array $country) use (
                $worldBankData,
                $maximumGdp
            ) {
                $economicData = $worldBankData[
                    $country['code']
                ] ?? null;

                return $this->addComparisonData(
                    $country,
                    $economicData,
                    $maximumGdp
                );
            })
            ->sortBy('name')
            ->values()
            ->toArray();

        $worldBankCountryCount = collect($countries)
            ->where(
                'economic_data_source',
                'World Bank API'
            )
            ->count();

        if ($worldBankCountryCount > 0) {
            $apiStatus = 'Data negara berhasil dimuat dari '
                . 'REST Countries API v5. Data GDP dan inflasi '
                . 'World Bank tersedia untuk '
                . $worldBankCountryCount
                . ' negara.';
        } else {
            $apiStatus = 'Data negara berhasil dimuat, tetapi '
                . 'World Bank API tidak menyediakan data. '
                . 'Sistem menggunakan perhitungan cadangan.';
        }

        return view(
            'comparison.index',
            compact(
                'countries',
                'apiStatus'
            )
        );
    }

    /**
     * Mengambil seluruh negara dari
     * REST Countries API v5.
     */
    private function getCountries(): array
    {
        return Cache::remember(
            'supplyguard.comparison.countries.v5',
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
                                'REST Countries API gagal '
                                . 'dengan status HTTP '
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
                         * Pengamanan agar perulangan
                         * tidak berjalan tanpa batas.
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

                                'currency_code' =>
                                    $currencyCode,

                                'currency_name' =>
                                    $currencyName,

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
                            'REST Countries API tidak '
                            . 'mengembalikan data negara.'
                        );
                    }

                    $responseTime = (int) round(
                        (microtime(true) - $startedAt) * 1000
                    );

                    ApiLogService::success(
                        apiName: 'REST Countries API v5',
                        endpoint: $baseUrl,
                        feature: 'Perbandingan Negara',
                        statusCode: $response?->status() ?? 200,
                        responseTime: $responseTime,
                        description: 'Berhasil mengambil '
                            . count($countries)
                            . ' data negara untuk '
                            . 'Perbandingan Negara.'
                    );

                    return $countries;
                } catch (Throwable $exception) {
                    $responseTime = (int) round(
                        (microtime(true) - $startedAt) * 1000
                    );

                    ApiLogService::failed(
                        apiName: 'REST Countries API v5',
                        endpoint: $baseUrl,
                        feature: 'Perbandingan Negara',
                        statusCode: $response?->status(),
                        responseTime: $responseTime,
                        description: 'Gagal mengambil data negara '
                            . 'untuk Perbandingan Negara. '
                            . 'Sistem menggunakan data cadangan.',
                        errorMessage: $exception->getMessage()
                    );

                    report($exception);

                    return $this->fallbackCountries();
                }
            }
        );
    }

    /**
     * Mengambil GDP dan inflasi
     * dari World Bank API.
     */
    private function getWorldBankData(): array
    {
        $cacheKey =
            'supplyguard.comparison.world_bank.v1';

        $cachedData = Cache::get($cacheKey);

        if (
            is_array($cachedData)
            && !empty($cachedData)
        ) {
            return $cachedData;
        }

        $startedAt = microtime(true);

        $baseUrl =
            'https://api.worldbank.org/v2/country/all/indicator';

        $indicators = [
            'gdp' => [
                'code' => 'NY.GDP.MKTP.CD',
                'label' => 'GDP',
            ],

            'inflation' => [
                'code' => 'FP.CPI.TOTL.ZG',
                'label' => 'Inflasi',
            ],

            'population' => [
                'code' => 'SP.POP.TOTL',
                'label' => 'Populasi',
            ],
        ];

        $economicData = [];
        $successfulIndicators = [];
        $failedIndicators = [];
        $lastStatusCode = 200;
        $lastErrorMessage = null;

        try {
            foreach ($indicators as $key => $indicator) {
                $endpoint = $baseUrl
                    . '/'
                    . $indicator['code'];

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
                        $failedIndicators[] =
                            $indicator['label'];

                        $lastErrorMessage =
                            'World Bank indikator '
                            . $indicator['label']
                            . ' gagal dengan status HTTP '
                            . $response->status();

                        continue;
                    }

                    $payload = $response->json();

                    $records = (
                        is_array($payload)
                        && isset($payload[1])
                        && is_array($payload[1])
                    )
                        ? $payload[1]
                        : [];

                    if (empty($records)) {
                        $failedIndicators[] =
                            $indicator['label'];

                        continue;
                    }

                    $validRecords = 0;

                    foreach ($records as $record) {
                        if (!is_array($record)) {
                            continue;
                        }

                        $countryCode = strtoupper(
                            trim(
                                (string) (
                                    $record['countryiso3code']
                                    ?? ''
                                )
                            )
                        );

                        $value =
                            $record['value'] ?? null;

                        $year = (int) (
                            $record['date'] ?? 0
                        );

                        if (
                            $countryCode === ''
                            || !is_numeric($value)
                        ) {
                            continue;
                        }

                        $existingYear = (int) data_get(
                            $economicData,
                            $countryCode
                                . '.'
                                . $key
                                . '.year',
                            0
                        );

                        /*
                         * Simpan data terbaru
                         * yang memiliki nilai.
                         */
                        if (
                            !isset(
                                $economicData[
                                    $countryCode
                                ][$key]
                            )
                            || $year > $existingYear
                        ) {
                            $economicData[
                                $countryCode
                            ][$key] = [
                                'value' => (float) $value,
                                'year' => $year,
                            ];
                        }

                        $validRecords++;
                    }

                    if ($validRecords > 0) {
                        $successfulIndicators[] =
                            $indicator['label'];

                        $lastStatusCode =
                            $response->status();
                    } else {
                        $failedIndicators[] =
                            $indicator['label'];
                    }
                } catch (Throwable $exception) {
                    $failedIndicators[] =
                        $indicator['label'];

                    $lastErrorMessage =
                        $exception->getMessage();
                }
            }

            if (empty($economicData)) {
                throw new RuntimeException(
                    $lastErrorMessage
                    ?? 'World Bank API tidak '
                    . 'mengembalikan data ekonomi.'
                );
            }

            Cache::put(
                $cacheKey,
                $economicData,
                now()->addHours(12)
            );

            $responseTime = (int) round(
                (microtime(true) - $startedAt) * 1000
            );

            $description =
                'Berhasil mengambil indikator '
                . implode(', ', $successfulIndicators)
                . ' dari World Bank API untuk '
                . count($economicData)
                . ' negara pada fitur Perbandingan Negara.';

            if (!empty($failedIndicators)) {
                $description .= ' Indikator yang gagal: '
                    . implode(
                        ', ',
                        array_unique($failedIndicators)
                    )
                    . '.';
            }

            ApiLogService::success(
                apiName: 'World Bank API',
                endpoint: $baseUrl,
                feature: 'Perbandingan Negara',
                statusCode: $lastStatusCode,
                responseTime: $responseTime,
                description: $description
            );

            return $economicData;
        } catch (Throwable $exception) {
            $responseTime = (int) round(
                (microtime(true) - $startedAt) * 1000
            );

            ApiLogService::failed(
                apiName: 'World Bank API',
                endpoint: $baseUrl,
                feature: 'Perbandingan Negara',
                statusCode: null,
                responseTime: $responseTime,
                description: 'Gagal mengambil data ekonomi '
                    . 'untuk Perbandingan Negara. '
                    . 'Sistem menggunakan perhitungan cadangan.',
                errorMessage: $exception->getMessage()
            );

            Cache::put(
                $cacheKey,
                [],
                now()->addMinutes(5)
            );

            report($exception);

            return [];
        }
    }

    /**
     * Menambahkan indikator dan risiko
     * untuk perbandingan negara.
     */
    private function addComparisonData(
        array $country,
        ?array $economicData,
        float $maximumGdp
    ): array {
        $seed = abs(
            crc32(
                $country['name']
                . $country['code']
                . $country['region']
            )
        );

        $gdpValue = data_get(
            $economicData,
            'gdp.value'
        );

        if (
            is_numeric($gdpValue)
            && (float) $gdpValue > 0
            && $maximumGdp > 0
        ) {
            $gdpScore = $this->calculateGdpScore(
                (float) $gdpValue,
                $maximumGdp
            );
        } else {
            $gdpScore = 45 + ($seed % 50);
        }

        $inflationValue = data_get(
            $economicData,
            'inflation.value'
        );

        if (is_numeric($inflationValue)) {
            $inflationRisk =
                $this->calculateInflationRisk(
                    (float) $inflationValue
                );
        } else {
            $inflationRisk =
                5 + (intdiv($seed, 7) % 60);
        }

        $weatherRisk =
            10 + (intdiv($seed, 11) % 60);

        $currencyRisk =
            5 + (intdiv($seed, 13) % 60);

        $newsRisk =
            10 + (intdiv($seed, 17) % 65);

        if (
            (bool) (
                $country['landlocked'] ?? false
            )
        ) {
            $portRisk =
                65 + ($seed % 25);
        } else {
            $portRisk =
                10 + (intdiv($seed, 19) % 50);
        }

        $totalRisk = round(
            ((100 - $gdpScore) * 0.15)
            + ($inflationRisk * 0.20)
            + ($weatherRisk * 0.20)
            + ($currencyRisk * 0.15)
            + ($newsRisk * 0.20)
            + ($portRisk * 0.10),
            2
        );

        $riskInformation =
            $this->getRiskInformation(
                $totalRisk
            );

        $worldBankPopulation = data_get(
            $economicData,
            'population.value'
        );

        if (is_numeric($worldBankPopulation)) {
            $country['population'] =
                (int) round(
                    (float) $worldBankPopulation
                );
        }

        $country['gdp_value'] =
            $this->nullableNumber($gdpValue);

        $country['gdp_year'] = data_get(
            $economicData,
            'gdp.year'
        );

        $country['inflation'] =
            $this->nullableNumber(
                $inflationValue
            );

        $country['inflation_year'] = data_get(
            $economicData,
            'inflation.year'
        );

        $country['gdp_score'] = $gdpScore;

        $country['inflation_risk'] =
            $inflationRisk;

        $country['weather_risk'] =
            $weatherRisk;

        $country['currency_risk'] =
            $currencyRisk;

        $country['news_risk'] =
            $newsRisk;

        $country['port_risk'] =
            $portRisk;

        $country['total_risk'] =
            $totalRisk;

        $country['category'] =
            $riskInformation['category'];

        $country['badge'] =
            $riskInformation['badge'];

        $country['recommendation'] =
            $riskInformation['recommendation'];

        $country['economic_data_source'] =
            is_array($economicData)
            && !empty($economicData)
                ? 'World Bank API'
                : 'Data cadangan';

        return $country;
    }

    /**
     * Mengubah nilai GDP menjadi skor 0–100.
     */
    private function calculateGdpScore(
        float $gdp,
        float $maximumGdp
    ): int {
        if ($gdp <= 0 || $maximumGdp <= 0) {
            return 45;
        }

        /*
         * Akar kuadrat dipakai agar negara
         * dengan ekonomi menengah tetap
         * memperoleh skor yang proporsional.
         */
        $normalized = sqrt(
            $gdp / $maximumGdp
        );

        $score = 20 + ($normalized * 75);

        return (int) round(
            max(20, min(95, $score))
        );
    }

    /**
     * Mengubah nilai inflasi menjadi risiko.
     */
    private function calculateInflationRisk(
        float $inflation
    ): int {
        $absoluteInflation = abs($inflation);

        if ($absoluteInflation <= 2) {
            return 15;
        }

        if ($absoluteInflation <= 5) {
            return 30;
        }

        if ($absoluteInflation <= 10) {
            return 50;
        }

        if ($absoluteInflation <= 20) {
            return 70;
        }

        return 90;
    }

    /**
     * Menentukan kategori risiko.
     */
    private function getRiskInformation(
        float|int $totalRisk
    ): array {
        if ($totalRisk <= 25) {
            return [
                'category' => 'Low',

                'badge' => 'risk-low',

                'recommendation' =>
                    'Negara ini relatif aman '
                    . 'untuk aktivitas impor.',
            ];
        }

        if ($totalRisk <= 50) {
            return [
                'category' => 'Medium',

                'badge' => 'risk-medium',

                'recommendation' =>
                    'Negara masih layak untuk impor, '
                    . 'tetapi indikator risiko perlu dipantau.',
            ];
        }

        if ($totalRisk <= 75) {
            return [
                'category' => 'High',

                'badge' => 'risk-high',

                'recommendation' =>
                    'Siapkan negara alternatif atau '
                    . 'jadwal pengiriman cadangan.',
            ];
        }

        return [
            'category' => 'Critical',

            'badge' => 'bg-dark text-white',

            'recommendation' =>
                'Transaksi impor sebaiknya ditunda '
                . 'sampai tingkat risiko menurun.',
        ];
    }

    /**
     * Mengubah data menjadi angka atau null.
     */
    private function nullableNumber(
        mixed $value
    ): ?float {
        if (!is_numeric($value)) {
            return null;
        }

        return round(
            (float) $value,
            2
        );
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

                'subregion' =>
                    'Eastern Asia',

                'population' => 1400000000,

                'currency_code' => 'CNY',

                'currency_name' =>
                    'Chinese Yuan',

                'flag' => null,

                'latitude' => 39.9,

                'longitude' => 116.4,

                'landlocked' => false,
            ],
        ];
    }
}