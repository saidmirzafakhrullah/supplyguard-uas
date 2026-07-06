<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RiskController extends Controller
{
    public function index()
    {
        $countries = [];
        $apiStatus = 'Mengambil data semua negara untuk risk scoring';

        try {
            $url = 'https://restcountries.com/v3.1/all?fields=name,cca2,capital,region,subregion,population,currencies,languages,flags,latlng,landlocked';

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
                return $this->addRiskScore($country);
            })
            ->sortBy('name')
            ->values()
            ->toArray();

        return view('risk.index', compact('countries', 'apiStatus'));
    }

    private function mapRestCountries(array $data)
    {
        return collect($data)
            ->map(function ($country) {
                $currencyText = '-';

                if (!empty($country['currencies'])) {
                    $currencies = collect($country['currencies'])->map(function ($currency, $code) {
                        return $code . ' - ' . ($currency['name'] ?? '-');
                    });

                    $currencyText = $currencies->implode(', ');
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
                    'flag' => $country['flags']['png'] ?? '',
                    'landlocked' => $country['landlocked'] ?? false,
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

                if (!empty($country['currencies'])) {
                    $currencies = collect($country['currencies'])->map(function ($currency, $code) {
                        return $code . ' - ' . ($currency['name'] ?? '-');
                    });

                    $currencyText = $currencies->implode(', ');
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
                    'flag' => '',
                    'landlocked' => $country['landlocked'] ?? false,
                ];
            })
            ->sortBy('name')
            ->values()
            ->toArray();
    }

    private function addRiskScore(array $country)
    {
        $seed = abs(crc32($country['name'] . $country['code']));

        $weatherRisk = 10 + ($seed % 55);
        $inflationRisk = 5 + (intdiv($seed, 7) % 55);
        $currencyRisk = 5 + (intdiv($seed, 11) % 55);
        $newsRisk = 10 + (intdiv($seed, 13) % 60);

        if ($country['landlocked']) {
            $portRisk = 60;
        } else {
            $portRisk = 5 + (intdiv($seed, 17) % 35);
        }

        $totalRisk = round(
            ($weatherRisk * 0.30) +
            ($inflationRisk * 0.20) +
            ($currencyRisk * 0.15) +
            ($newsRisk * 0.25) +
            ($portRisk * 0.10),
            2
        );

        $category = 'Low';
        $badge = 'risk-low';
        $recommendation = 'Safe route for import activity.';

        if ($totalRisk > 25 && $totalRisk <= 50) {
            $category = 'Medium';
            $badge = 'risk-medium';
            $recommendation = 'Monitor weather, currency, and news before import.';
        } elseif ($totalRisk > 50 && $totalRisk <= 75) {
            $category = 'High';
            $badge = 'risk-high';
            $recommendation = 'Prepare alternative country or shipping route.';
        } elseif ($totalRisk > 75) {
            $category = 'Critical';
            $badge = 'bg-dark text-white';
            $recommendation = 'Delay import activity until risk decreases.';
        }

        $country['weather_risk'] = $weatherRisk;
        $country['inflation_risk'] = $inflationRisk;
        $country['currency_risk'] = $currencyRisk;
        $country['news_risk'] = $newsRisk;
        $country['port_risk'] = $portRisk;
        $country['total_risk'] = $totalRisk;
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
                'currency' => 'IDR - Indonesian Rupiah',
                'flag' => '',
                'landlocked' => false,
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
                'flag' => '',
                'landlocked' => false,
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
                'flag' => '',
                'landlocked' => false,
            ],
        ];
    }
}