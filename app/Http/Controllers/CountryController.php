<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CountryController extends Controller
{
    public function index()
    {
        $countries = [];
        $apiStatus = 'Mengambil data negara global';

        try {
            $restUrl = 'https://restcountries.com/v3.1/all?fields=name,cca2,capital,region,subregion,population,currencies,languages,flags,latlng';

            $response = Http::timeout(30)
                ->withOptions(['verify' => false])
                ->get($restUrl);

            if ($response->successful()) {
                $countries = $this->mapRestCountries($response->json());
                $apiStatus = 'Data berhasil dari REST Countries API';
            }
        } catch (\Exception $e) {
            $apiStatus = 'REST Countries gagal, mencoba dataset cadangan';
        }

        if (count($countries) < 100) {
            try {
                $backupUrl = 'https://raw.githubusercontent.com/mledoze/countries/master/countries.json';

                $backupResponse = Http::timeout(30)
                    ->withOptions(['verify' => false])
                    ->get($backupUrl);

                if ($backupResponse->successful()) {
                    $countries = $this->mapMledozeCountries($backupResponse->json());
                    $apiStatus = 'Data berhasil dari dataset global mledoze/countries';
                }
            } catch (\Exception $e) {
                $apiStatus = 'Semua API gagal, memakai data cadangan lokal';
            }
        }

        if (count($countries) === 0) {
            $countries = $this->fallbackCountries();
        }

        return view('countries.index', compact('countries', 'apiStatus'));
    }

    private function mapRestCountries(array $data)
    {
        return collect($data)
            ->map(function ($country) {
                $currencyText = '-';
                $languageText = '-';

                if (!empty($country['currencies'])) {
                    $currencies = collect($country['currencies'])->map(function ($currency, $code) {
                        return $code . ' - ' . ($currency['name'] ?? '-');
                    });

                    $currencyText = $currencies->implode(', ');
                }

                if (!empty($country['languages'])) {
                    $languageText = collect($country['languages'])->values()->implode(', ');
                }

                return [
                    'name' => $country['name']['common'] ?? '-',
                    'official_name' => $country['name']['official'] ?? '-',
                    'code' => $country['cca2'] ?? '-',
                    'capital' => $country['capital'][0] ?? '-',
                    'region' => $country['region'] ?? '-',
                    'subregion' => $country['subregion'] ?? '-',
                    'population' => $country['population'] ?? 0,
                    'currency' => $currencyText,
                    'languages' => $languageText,
                    'flag' => $country['flags']['png'] ?? '',
                    'lat' => $country['latlng'][0] ?? 0,
                    'lng' => $country['latlng'][1] ?? 0,
                ];
            })
            ->sortBy('name')
            ->values()
            ->toArray();
    }

    private function mapMledozeCountries(array $data)
    {
        return collect($data)
            ->map(function ($country) {
                $currencyText = '-';
                $languageText = '-';

                if (!empty($country['currencies'])) {
                    $currencies = collect($country['currencies'])->map(function ($currency, $code) {
                        return $code . ' - ' . ($currency['name'] ?? '-');
                    });

                    $currencyText = $currencies->implode(', ');
                }

                if (!empty($country['languages'])) {
                    $languageText = collect($country['languages'])->values()->implode(', ');
                }

                return [
                    'name' => $country['name']['common'] ?? '-',
                    'official_name' => $country['name']['official'] ?? '-',
                    'code' => $country['cca2'] ?? '-',
                    'capital' => $country['capital'][0] ?? '-',
                    'region' => $country['region'] ?? '-',
                    'subregion' => $country['subregion'] ?? '-',
                    'population' => $country['population'] ?? 0,
                    'currency' => $currencyText,
                    'languages' => $languageText,
                    'flag' => '',
                    'lat' => $country['latlng'][0] ?? 0,
                    'lng' => $country['latlng'][1] ?? 0,
                ];
            })
            ->sortBy('name')
            ->values()
            ->toArray();
    }

    private function fallbackCountries()
    {
        return [
            [
                'name' => 'Indonesia',
                'official_name' => 'Republic of Indonesia',
                'code' => 'ID',
                'capital' => 'Jakarta',
                'region' => 'Asia',
                'subregion' => 'Southeast Asia',
                'population' => 273523621,
                'currency' => 'IDR - Indonesian Rupiah',
                'languages' => 'Indonesian',
                'flag' => '',
                'lat' => -5,
                'lng' => 120,
            ],
            [
                'name' => 'Germany',
                'official_name' => 'Federal Republic of Germany',
                'code' => 'DE',
                'capital' => 'Berlin',
                'region' => 'Europe',
                'subregion' => 'Western Europe',
                'population' => 83240525,
                'currency' => 'EUR - Euro',
                'languages' => 'German',
                'flag' => '',
                'lat' => 51,
                'lng' => 9,
            ],
            [
                'name' => 'China',
                'official_name' => 'People’s Republic of China',
                'code' => 'CN',
                'capital' => 'Beijing',
                'region' => 'Asia',
                'subregion' => 'Eastern Asia',
                'population' => 1402112000,
                'currency' => 'CNY - Chinese Yuan',
                'languages' => 'Chinese',
                'flag' => '',
                'lat' => 35,
                'lng' => 105,
            ],
        ];
    }
}