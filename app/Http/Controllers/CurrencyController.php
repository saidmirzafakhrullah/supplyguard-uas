<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class CurrencyController extends Controller
{
    public function index()
    {
        $countries = [];
        $apiStatus = 'Mengambil data semua negara untuk currency impact';

        try {
            $url = 'https://restcountries.com/v3.1/all?fields=name,cca2,capital,region,subregion,currencies,flags,population';

            $response = Http::timeout(30)
                ->withOptions(['verify' => false])
                ->get($url);

            if ($response->successful()) {
                $countries = $this->mapRestCountries($response->json());
                $apiStatus = 'Data negara berhasil dari REST Countries API';
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
                    $apiStatus = 'Data negara berhasil dari dataset global cadangan';
                }
            } catch (\Exception $e) {
                $apiStatus = 'Semua API gagal, memakai data cadangan lokal';
            }
        }

        if (count($countries) === 0) {
            $countries = $this->fallbackCountries();
        }

        $countries = collect($countries)
            ->map(function ($country) {
                return $this->addCurrencyImpact($country);
            })
            ->sortBy('name')
            ->values()
            ->toArray();

        return view('currency.index', compact('countries', 'apiStatus'));
    }

    private function mapRestCountries(array $data)
    {
        return collect($data)
            ->map(function ($country) {
                $currencyCode = 'N/A';
                $currencyName = 'No official currency data';

                if (!empty($country['currencies'])) {
                    $firstCode = array_key_first($country['currencies']);
                    $currencyCode = $firstCode;
                    $currencyName = $country['currencies'][$firstCode]['name'] ?? 'Unknown currency';
                }

                return [
                    'name' => $country['name']['common'] ?? '-',
                    'official_name' => $country['name']['official'] ?? '-',
                    'code' => $country['cca2'] ?? '-',
                    'capital' => $country['capital'][0] ?? '-',
                    'region' => $country['region'] ?? '-',
                    'subregion' => $country['subregion'] ?? '-',
                    'population' => $country['population'] ?? 0,
                    'currency_code' => $currencyCode,
                    'currency_name' => $currencyName,
                    'flag' => $country['flags']['png'] ?? '',
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
                $currencyCode = 'N/A';
                $currencyName = 'No official currency data';

                if (!empty($country['currencies'])) {
                    $firstCode = array_key_first($country['currencies']);
                    $currencyCode = $firstCode;
                    $currencyName = $country['currencies'][$firstCode]['name'] ?? 'Unknown currency';
                }

                return [
                    'name' => $country['name']['common'] ?? '-',
                    'official_name' => $country['name']['official'] ?? '-',
                    'code' => $country['cca2'] ?? '-',
                    'capital' => $country['capital'][0] ?? '-',
                    'region' => $country['region'] ?? '-',
                    'subregion' => $country['subregion'] ?? '-',
                    'population' => $country['population'] ?? 0,
                    'currency_code' => $currencyCode,
                    'currency_name' => $currencyName,
                    'flag' => '',
                ];
            })
            ->sortBy('name')
            ->values()
            ->toArray();
    }

    private function addCurrencyImpact(array $country)
    {
        $currencyCode = $country['currency_code'];

        if ($currencyCode === 'N/A') {
            $country['exchange_rate'] = 0;
            $country['volatility'] = 0;
            $country['exchange_change'] = 0;
            $country['currency_risk'] = 0;
            $country['category'] = 'No Data';
            $country['badge'] = 'bg-secondary text-white';
            $country['recommendation'] = 'Currency data is not available for this country.';

            return $country;
        }

        $baseRates = [
            'USD' => 1,
            'IDR' => 16200,
            'EUR' => 0.92,
            'CNY' => 7.25,
            'JPY' => 157,
            'SGD' => 1.35,
            'AUD' => 1.50,
            'MYR' => 4.70,
            'THB' => 36,
            'PHP' => 58,
            'GBP' => 0.79,
            'KRW' => 1380,
            'INR' => 83,
            'CAD' => 1.37,
            'CHF' => 0.89,
        ];

        $seed = abs(crc32($country['name'] . $country['currency_code']));

        if (isset($baseRates[$currencyCode])) {
            $exchangeRate = $baseRates[$currencyCode];
        } else {
            $exchangeRate = round(1 + ($seed % 9000) / 100, 2);
        }

        $volatility = 5 + ($seed % 60);
        $exchangeChange = round(((intdiv($seed, 7) % 200) - 100) / 10, 2);

        $currencyRisk = round(
            ($volatility * 0.60) +
            (abs($exchangeChange) * 4),
            2
        );

        if ($currencyRisk > 100) {
            $currencyRisk = 100;
        }

        $category = 'Low';
        $badge = 'risk-low';
        $recommendation = 'Currency condition is stable for import transaction.';

        if ($currencyRisk > 25 && $currencyRisk <= 50) {
            $category = 'Medium';
            $badge = 'risk-medium';
            $recommendation = 'Monitor exchange rate before import transaction.';
        } elseif ($currencyRisk > 50 && $currencyRisk <= 75) {
            $category = 'High';
            $badge = 'risk-high';
            $recommendation = 'Prepare currency buffer or alternative supplier country.';
        } elseif ($currencyRisk > 75) {
            $category = 'Critical';
            $badge = 'bg-dark text-white';
            $recommendation = 'Delay import transaction until currency risk decreases.';
        }

        $country['exchange_rate'] = $exchangeRate;
        $country['volatility'] = $volatility;
        $country['exchange_change'] = $exchangeChange;
        $country['currency_risk'] = $currencyRisk;
        $country['category'] = $category;
        $country['badge'] = $badge;
        $country['recommendation'] = $recommendation;

        return $country;
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
                'currency_code' => 'IDR',
                'currency_name' => 'Indonesian Rupiah',
                'flag' => '',
            ],
            [
                'name' => 'Germany',
                'official_name' => 'Federal Republic of Germany',
                'code' => 'DE',
                'capital' => 'Berlin',
                'region' => 'Europe',
                'subregion' => 'Western Europe',
                'population' => 83240525,
                'currency_code' => 'EUR',
                'currency_name' => 'Euro',
                'flag' => '',
            ],
            [
                'name' => 'China',
                'official_name' => 'People’s Republic of China',
                'code' => 'CN',
                'capital' => 'Beijing',
                'region' => 'Asia',
                'subregion' => 'Eastern Asia',
                'population' => 1402112000,
                'currency_code' => 'CNY',
                'currency_name' => 'Chinese Yuan',
                'flag' => '',
            ],
            [
                'name' => 'Japan',
                'official_name' => 'Japan',
                'code' => 'JP',
                'capital' => 'Tokyo',
                'region' => 'Asia',
                'subregion' => 'Eastern Asia',
                'population' => 125800000,
                'currency_code' => 'JPY',
                'currency_name' => 'Japanese Yen',
                'flag' => '',
            ],
        ];
    }
}