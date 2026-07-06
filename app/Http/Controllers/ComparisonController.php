<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class ComparisonController extends Controller
{
    public function index()
    {
        $countries = [];

        try {
            $url = 'https://restcountries.com/v3.1/all?fields=name,cca2,capital,region,subregion,population,flags,latlng,landlocked';

            $response = Http::timeout(30)
                ->withOptions(['verify' => false])
                ->get($url);

            if ($response->successful()) {
                $countries = $this->mapRestCountries($response->json());
            }
        } catch (\Exception $e) {
            $countries = [];
        }

        if (count($countries) < 100) {
            try {
                $backupUrl = 'https://raw.githubusercontent.com/mledoze/countries/master/countries.json';

                $backupResponse = Http::timeout(30)
                    ->withOptions(['verify' => false])
                    ->get($backupUrl);

                if ($backupResponse->successful()) {
                    $countries = $this->mapMledozeCountries($backupResponse->json());
                }
            } catch (\Exception $e) {
                $countries = [];
            }
        }

        if (count($countries) === 0) {
            $countries = $this->fallbackCountries();
        }

        $countries = collect($countries)
            ->map(function ($country) {
                return $this->addComparisonData($country);
            })
            ->sortBy('name')
            ->values()
            ->toArray();

        return view('comparison.index', compact('countries'));
    }

    private function mapRestCountries(array $items)
    {
        return collect($items)
            ->map(function ($country) {
                return [
                    'name' => $country['name']['common'] ?? '-',
                    'official_name' => $country['name']['official'] ?? '-',
                    'code' => $country['cca2'] ?? '-',
                    'capital' => $country['capital'][0] ?? '-',
                    'region' => $country['region'] ?? '-',
                    'subregion' => $country['subregion'] ?? '-',
                    'population' => $country['population'] ?? 0,
                    'flag' => $country['flags']['png'] ?? '',
                    'latitude' => $country['latlng'][0] ?? 0,
                    'longitude' => $country['latlng'][1] ?? 0,
                    'landlocked' => $country['landlocked'] ?? false,
                ];
            })
            ->sortBy('name')
            ->values()
            ->toArray();
    }

    private function mapMledozeCountries(array $items)
    {
        return collect($items)
            ->map(function ($country) {
                return [
                    'name' => $country['name']['common'] ?? '-',
                    'official_name' => $country['name']['official'] ?? '-',
                    'code' => $country['cca2'] ?? '-',
                    'capital' => $country['capital'][0] ?? '-',
                    'region' => $country['region'] ?? '-',
                    'subregion' => $country['subregion'] ?? '-',
                    'population' => $country['population'] ?? 0,
                    'flag' => '',
                    'latitude' => $country['latlng'][0] ?? 0,
                    'longitude' => $country['latlng'][1] ?? 0,
                    'landlocked' => $country['landlocked'] ?? false,
                ];
            })
            ->sortBy('name')
            ->values()
            ->toArray();
    }

    private function addComparisonData(array $country)
    {
        $seed = abs(crc32($country['name'] . $country['code'] . $country['region']));

        $gdpScore = 45 + ($seed % 50);
        $inflationRisk = 5 + (intdiv($seed, 7) % 60);
        $weatherRisk = 10 + (intdiv($seed, 11) % 60);
        $currencyRisk = 5 + (intdiv($seed, 13) % 60);
        $newsRisk = 10 + (intdiv($seed, 17) % 65);

        if ($country['landlocked']) {
            $portRisk = 65 + ($seed % 25);
        } else {
            $portRisk = 10 + (intdiv($seed, 19) % 50);
        }

        $totalRisk = round(
            ((100 - $gdpScore) * 0.15) +
            ($inflationRisk * 0.20) +
            ($weatherRisk * 0.20) +
            ($currencyRisk * 0.15) +
            ($newsRisk * 0.20) +
            ($portRisk * 0.10),
            2
        );

        $category = 'Low';
        $badge = 'risk-low';
        $recommendation = 'Negara ini relatif aman untuk aktivitas impor.';

        if ($totalRisk > 25 && $totalRisk <= 50) {
            $category = 'Medium';
            $badge = 'risk-medium';
            $recommendation = 'Negara masih layak untuk impor, tetapi perlu monitoring indikator risiko.';
        } elseif ($totalRisk > 50 && $totalRisk <= 75) {
            $category = 'High';
            $badge = 'risk-high';
            $recommendation = 'Perlu menyiapkan negara alternatif atau jadwal pengiriman cadangan.';
        } elseif ($totalRisk > 75) {
            $category = 'Critical';
            $badge = 'bg-dark text-white';
            $recommendation = 'Sebaiknya transaksi impor ditunda sampai risiko menurun.';
        }

        $country['gdp_score'] = $gdpScore;
        $country['inflation_risk'] = $inflationRisk;
        $country['weather_risk'] = $weatherRisk;
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
                'flag' => '',
                'latitude' => -6.2,
                'longitude' => 106.8,
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
                'flag' => '',
                'latitude' => 52.5,
                'longitude' => 13.4,
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
                'flag' => '',
                'latitude' => 39.9,
                'longitude' => 116.4,
                'landlocked' => false,
            ],
        ];
    }
}