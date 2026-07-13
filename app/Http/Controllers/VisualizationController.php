<?php

namespace App\Http\Controllers;

use App\Services\ApiLogService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class VisualizationController extends Controller
{
    /**
     * Menampilkan halaman Visualisasi Data.
     */
    public function index()
    {
        $countries = $this->getCountries();

        if (empty($countries)) {
            $countries = $this->fallbackCountries();
        }

        $worldBankData = $this->getWorldBankData();

        $countries = collect($countries)
            ->map(function (array $country) use ($worldBankData) {
                $economicData = $worldBankData[$country['code']] ?? null;

                return $this->addRiskData(
                    $country,
                    $economicData
                );
            })
            ->sortBy('name')
            ->values()
            ->toArray();

        $data = collect($countries);

        $summary = [
            'total_countries' => count($countries),

            'average_risk' => round(
                (float) $data->avg('risk_score'),
                2
            ),

            'low_risk' => $data
                ->where('category', 'Low')
                ->count(),

            'medium_risk' => $data
                ->where('category', 'Medium')
                ->count(),

            'high_risk' => $data
                ->where('category', 'High')
                ->count(),

            'critical_risk' => $data
                ->where('category', 'Critical')
                ->count(),
        ];

        $regionSummary = $data
            ->groupBy('region')
            ->map(function ($items, $region) {
                return [
                    'region' => $region,
                    'total' => $items->count(),

                    'average_risk' => round(
                        (float) $items->avg('risk_score'),
                        2
                    ),
                ];
            })
            ->values()
            ->toArray();

        $topRiskCountries = $data
            ->sortByDesc('risk_score')
            ->take(10)
            ->values()
            ->toArray();

        $lowRiskCountries = $data
            ->sortBy('risk_score')
            ->take(10)
            ->values()
            ->toArray();

        $worldBankCountryCount = $data
            ->where('economic_data_source', 'World Bank API')
            ->count();

        if ($worldBankCountryCount > 0) {
            $apiStatus = 'Data ekonomi World Bank berhasil digunakan untuk '
                . $worldBankCountryCount
                . ' negara.';
        } else {
            $apiStatus = 'World Bank API tidak dapat diakses. '
                . 'Sistem menggunakan perhitungan cadangan.';
        }

        return view('visualization.index', compact(
            'countries',
            'summary',
            'regionSummary',
            'topRiskCountries',
            'lowRiskCountries',
            'apiStatus'
        ));
    }

    /**
     * Mengambil seluruh negara dari REST Countries API v5.
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
                         * Pengamanan agar proses tidak
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
                        feature: 'Visualisasi Data',
                        statusCode: $response?->status() ?? 200,
                        responseTime: $responseTime,
                        description: 'Berhasil mengambil '
                            . count($countries)
                            . ' data negara untuk Visualisasi Data.'
                    );

                    return $countries;
                } catch (Throwable $exception) {
                    $responseTime = (int) round(
                        (microtime(true) - $startedAt) * 1000
                    );

                    ApiLogService::failed(
                        apiName: 'REST Countries API v5',
                        endpoint: $baseUrl,
                        feature: 'Visualisasi Data',
                        statusCode: $response?->status(),
                        responseTime: $responseTime,
                        description: 'Gagal mengambil data negara '
                            . 'untuk Visualisasi Data.',
                        errorMessage: $exception->getMessage()
                    );

                    report($exception);

                    return $this->fallbackCountries();
                }
            }
        );
    }

    /**
     * Mengambil indikator ekonomi dari World Bank API.
     */
    private function getWorldBankData(): array
    {
        $cacheKey =
            'supplyguard.world_bank.indicators.global.v1';

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
            'population' => [
                'code' => 'SP.POP.TOTL',
                'label' => 'Populasi',
            ],

            'gdp' => [
                'code' => 'NY.GDP.MKTP.CD',
                'label' => 'GDP',
            ],

            'inflation' => [
                'code' => 'FP.CPI.TOTL.ZG',
                'label' => 'Inflasi',
            ],

            'exports' => [
                'code' => 'NE.EXP.GNFS.ZS',
                'label' => 'Ekspor',
            ],

            'imports' => [
                'code' => 'NE.IMP.GNFS.ZS',
                'label' => 'Impor',
            ],
        ];

        $economicData = [];
        $successfulIndicators = [];
        $failedIndicators = [];
        $lastSuccessfulStatus = 200;
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

                    $totalValidRecords = 0;

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

                        $value = $record['value'] ?? null;
                        $year = (int) ($record['date'] ?? 0);

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
                         * Hanya simpan data terbaru
                         * yang memiliki nilai.
                         */
                        if (
                            !isset(
                                $economicData[$countryCode][$key]
                            )
                            || $year > $existingYear
                        ) {
                            $economicData[$countryCode][$key] = [
                                'value' => (float) $value,
                                'year' => $year,
                            ];
                        }

                        $totalValidRecords++;
                    }

                    if ($totalValidRecords > 0) {
                        $successfulIndicators[] =
                            $indicator['label'];

                        $lastSuccessfulStatus =
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
                    ?? 'World Bank API tidak mengembalikan data ekonomi.'
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
                . ' negara.';

            if (!empty($failedIndicators)) {
                $description .= ' Indikator yang tidak tersedia: '
                    . implode(', ', array_unique($failedIndicators))
                    . '.';
            }

            ApiLogService::success(
                apiName: 'World Bank API',
                endpoint: $baseUrl,
                feature: 'Visualisasi Data',
                statusCode: $lastSuccessfulStatus,
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
                feature: 'Visualisasi Data',
                statusCode: null,
                responseTime: $responseTime,
                description: 'Gagal mengambil indikator ekonomi. '
                    . 'Sistem menggunakan perhitungan cadangan.',
                errorMessage: $exception->getMessage()
            );

            /*
             * Cache kegagalan hanya sebentar agar API
             * dapat dicoba kembali.
             */
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
     * Menambahkan data risiko dan ekonomi
     * pada setiap negara.
     */
    private function addRiskData(
        array $country,
        ?array $economicData = null
    ): array {
        $seed = abs(
            crc32(
                $country['name']
                . $country['code']
                . $country['region']
            )
        );

        $weatherRisk =
            10 + ($seed % 70);

        $currencyRisk =
            10 + (intdiv($seed, 7) % 70);

        $newsRisk =
            10 + (intdiv($seed, 11) % 75);

        /*
         * Menggunakan inflasi asli World Bank
         * apabila tersedia.
         */
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
                10 + (intdiv($seed, 17) % 65);
        }

        if ($country['landlocked']) {
            $portRisk =
                65 + ($seed % 25);
        } else {
            $portRisk =
                10 + (intdiv($seed, 13) % 55);
        }

        $riskScore = round(
            ($weatherRisk * 0.25)
            + ($currencyRisk * 0.20)
            + ($newsRisk * 0.25)
            + ($portRisk * 0.15)
            + ($inflationRisk * 0.15),
            2
        );

        $category = 'Low';
        $badge = 'risk-low';

        if (
            $riskScore > 25
            && $riskScore <= 50
        ) {
            $category = 'Medium';
            $badge = 'risk-medium';
        } elseif (
            $riskScore > 50
            && $riskScore <= 75
        ) {
            $category = 'High';
            $badge = 'risk-high';
        } elseif ($riskScore > 75) {
            $category = 'Critical';
            $badge = 'bg-dark text-white';
        }

        $worldBankPopulation = data_get(
            $economicData,
            'population.value'
        );

        if (is_numeric($worldBankPopulation)) {
            $country['population'] =
                (int) round($worldBankPopulation);
        }

        $country['gdp'] = $this->nullableNumber(
            data_get($economicData, 'gdp.value')
        );

        $country['gdp_year'] = data_get(
            $economicData,
            'gdp.year'
        );

        $country['inflation'] = $this->nullableNumber(
            $inflationValue
        );

        $country['inflation_year'] = data_get(
            $economicData,
            'inflation.year'
        );

        $country['exports_percent_gdp'] =
            $this->nullableNumber(
                data_get(
                    $economicData,
                    'exports.value'
                )
            );

        $country['exports_year'] = data_get(
            $economicData,
            'exports.year'
        );

        $country['imports_percent_gdp'] =
            $this->nullableNumber(
                data_get(
                    $economicData,
                    'imports.value'
                )
            );

        $country['imports_year'] = data_get(
            $economicData,
            'imports.year'
        );

        $country['weather_risk'] = $weatherRisk;
        $country['currency_risk'] = $currencyRisk;
        $country['news_risk'] = $newsRisk;
        $country['port_risk'] = $portRisk;
        $country['inflation_risk'] = $inflationRisk;
        $country['risk_score'] = $riskScore;
        $country['category'] = $category;
        $country['badge'] = $badge;

        $country['economic_data_source'] =
            is_array($economicData)
            && !empty($economicData)
                ? 'World Bank API'
                : 'Data cadangan';

        return $country;
    }

    /**
     * Mengubah nilai inflasi menjadi risiko 0–100.
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