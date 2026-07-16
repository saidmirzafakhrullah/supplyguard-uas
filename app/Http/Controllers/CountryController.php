<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class CountryController extends Controller
{
    public function index()
    {
        $countries = [];
        $apiStatus = 'Mengambil data negara global';

        try {
            $countries = $this->getCountriesFromRestCountriesV5();
            $apiStatus = 'Data berhasil dimuat dari REST Countries API v5';
        } catch (Throwable $exception) {
            report($exception);

            try {
                $countries = $this->getCountriesFromRestCountriesPublic();
                $apiStatus = 'Data berhasil dimuat dari REST Countries Public API';
            } catch (Throwable $secondException) {
                report($secondException);

                $countries = $this->fallbackCountries();
                $apiStatus = 'API negara gagal, memakai data cadangan lokal';
            }
        }

        $countries = collect($countries)
            ->filter(function (array $country) {
                return $country['name'] !== '-'
                    && preg_match('/^[A-Z]{3}$/', $country['code']);
            })
            ->unique('code')
            ->sortBy('name')
            ->values()
            ->toArray();

        return view(
            'countries.index',
            compact('countries', 'apiStatus')
        );
    }

    private function getCountriesFromRestCountriesV5(): array
    {
        return Cache::remember(
            'supplyguard.countries.page.v5.cleaned',
            now()->addHours(12),
            function () {
                $apiKey = config('services.rest_countries.key');

                if (empty($apiKey)) {
                    throw new RuntimeException(
                        'REST Countries API key belum dikonfigurasi.'
                    );
                }

                $baseUrl = rtrim(
                    config(
                        'services.rest_countries.base_url',
                        'https://api.restcountries.com/countries/v5'
                    ),
                    '/'
                );

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
                                'languages',
                                'coordinates.lat',
                                'coordinates.lng',
                                'flag.url_png',
                                'flag.url_svg',
                            ]),
                        ]);

                    if (!$response->successful()) {
                        throw new RuntimeException(
                            'REST Countries API v5 gagal. Status HTTP: '
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

                if (empty($allCountries)) {
                    throw new RuntimeException(
                        'REST Countries API v5 tidak mengembalikan data.'
                    );
                }

                return $this->mapRestCountriesV5($allCountries);
            }
        );
    }

    private function mapRestCountriesV5(array $data): array
    {
        return collect($data)
            ->map(function (array $country) {
                $currencyText = $this->formatCurrencies(
                    data_get($country, 'currencies', [])
                );

                $languageText = $this->formatLanguages(
                    data_get($country, 'languages', [])
                );

                return [
                    'name' => trim(
                        (string) data_get(
                            $country,
                            'names.common',
                            '-'
                        )
                    ),

                    'official_name' => trim(
                        (string) data_get(
                            $country,
                            'names.official',
                            '-'
                        )
                    ),

                    'code' => strtoupper(
                        trim(
                            (string) data_get(
                                $country,
                                'codes.alpha_3',
                                ''
                            )
                        )
                    ),

                    'capital' => data_get(
                        $country,
                        'capitals.0.name',
                        '-'
                    ),

                    'region' => data_get(
                        $country,
                        'region',
                        '-'
                    ),

                    'subregion' => data_get(
                        $country,
                        'subregion',
                        '-'
                    ),

                    'population' => (int) data_get(
                        $country,
                        'population',
                        0
                    ),

                    'currency' => $currencyText,
                    'languages' => $languageText,

                    'flag' => data_get(
                        $country,
                        'flag.url_png'
                    ) ?? data_get(
                        $country,
                        'flag.url_svg',
                        ''
                    ),

                    'lat' => (float) data_get(
                        $country,
                        'coordinates.lat',
                        0
                    ),

                    'lng' => (float) data_get(
                        $country,
                        'coordinates.lng',
                        0
                    ),
                ];
            })
            ->values()
            ->toArray();
    }

    private function getCountriesFromRestCountriesPublic(): array
    {
        return Cache::remember(
            'supplyguard.countries.page.public.v3.cleaned',
            now()->addHours(12),
            function () {
                $restUrl = 'https://restcountries.com/v3.1/all?fields=name,cca3,capital,region,subregion,population,currencies,languages,flags,latlng';

                $response = Http::timeout(30)
                    ->withOptions(['verify' => false])
                    ->get($restUrl);

                if (!$response->successful()) {
                    throw new RuntimeException(
                        'REST Countries Public API gagal.'
                    );
                }

                return $this->mapRestCountriesPublic(
                    $response->json()
                );
            }
        );
    }

    private function mapRestCountriesPublic(array $data): array
    {
        return collect($data)
            ->map(function (array $country) {
                $currencyText = '-';
                $languageText = '-';

                if (!empty($country['currencies'])) {
                    $currencies = collect($country['currencies'])
                        ->map(function ($currency, $code) {
                            return $code
                                . ' - '
                                . ($currency['name'] ?? '-');
                        });

                    $currencyText = $currencies->implode(', ');
                }

                if (!empty($country['languages'])) {
                    $languageText = collect($country['languages'])
                        ->values()
                        ->implode(', ');
                }

                return [
                    'name' => $country['name']['common'] ?? '-',
                    'official_name' => $country['name']['official'] ?? '-',
                    'code' => strtoupper($country['cca3'] ?? ''),
                    'capital' => $country['capital'][0] ?? '-',
                    'region' => $country['region'] ?? '-',
                    'subregion' => $country['subregion'] ?? '-',
                    'population' => (int) ($country['population'] ?? 0),
                    'currency' => $currencyText,
                    'languages' => $languageText,
                    'flag' => $country['flags']['png'] ?? '',
                    'lat' => (float) ($country['latlng'][0] ?? 0),
                    'lng' => (float) ($country['latlng'][1] ?? 0),
                ];
            })
            ->values()
            ->toArray();
    }

    private function formatCurrencies(mixed $currencies): string
    {
        if (!is_array($currencies) || empty($currencies)) {
            return '-';
        }

        if (array_is_list($currencies)) {
            return collect($currencies)
                ->map(function ($currency) {
                    if (!is_array($currency)) {
                        return null;
                    }

                    $code = data_get(
                        $currency,
                        'code',
                        data_get(
                            $currency,
                            'currency_code',
                            data_get($currency, 'iso_code', '')
                        )
                    );

                    $name = data_get($currency, 'name', $code);

                    if ($code === '') {
                        return null;
                    }

                    return strtoupper($code) . ' - ' . $name;
                })
                ->filter()
                ->values()
                ->implode(', ') ?: '-';
        }

        return collect($currencies)
            ->map(function ($currency, $code) {
                if (!is_array($currency)) {
                    return strtoupper((string) $code);
                }

                return strtoupper((string) $code)
                    . ' - '
                    . data_get($currency, 'name', $code);
            })
            ->values()
            ->implode(', ') ?: '-';
    }

    private function formatLanguages(mixed $languages): string
    {
        if (!is_array($languages) || empty($languages)) {
            return '-';
        }

        if (array_is_list($languages)) {
            return collect($languages)
                ->map(function ($language) {
                    if (is_array($language)) {
                        return data_get($language, 'name');
                    }

                    return $language;
                })
                ->filter()
                ->values()
                ->implode(', ') ?: '-';
        }

        return collect($languages)
            ->values()
            ->filter()
            ->implode(', ') ?: '-';
    }

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
                'currency' => 'IDR - Indonesian Rupiah',
                'languages' => 'Indonesian',
                'flag' => '',
                'lat' => -5,
                'lng' => 120,
            ],
            [
                'name' => 'Germany',
                'official_name' => 'Federal Republic of Germany',
                'code' => 'DEU',
                'capital' => 'Berlin',
                'region' => 'Europe',
                'subregion' => 'Western Europe',
                'population' => 84000000,
                'currency' => 'EUR - Euro',
                'languages' => 'German',
                'flag' => '',
                'lat' => 51,
                'lng' => 9,
            ],
            [
                'name' => 'China',
                'official_name' => 'People’s Republic of China',
                'code' => 'CHN',
                'capital' => 'Beijing',
                'region' => 'Asia',
                'subregion' => 'Eastern Asia',
                'population' => 1400000000,
                'currency' => 'CNY - Chinese Yuan',
                'languages' => 'Chinese',
                'flag' => '',
                'lat' => 35,
                'lng' => 105,
            ],
            [
                'name' => 'United States',
                'official_name' => 'United States of America',
                'code' => 'USA',
                'capital' => 'Washington, D.C.',
                'region' => 'Americas',
                'subregion' => 'North America',
                'population' => 334000000,
                'currency' => 'USD - United States Dollar',
                'languages' => 'English',
                'flag' => '',
                'lat' => 38,
                'lng' => -97,
            ],
        ];
    }
}