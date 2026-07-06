<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class VisualizationController extends Controller
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
                return $this->addRiskData($country);
            })
            ->sortBy('name')
            ->values()
            ->toArray();

        $data = collect($countries);

        $summary = [
            'total_countries' => count($countries),
            'average_risk' => round($data->avg('risk_score'), 2),
            'low_risk' => $data->where('category', 'Low')->count(),
            'medium_risk' => $data->where('category', 'Medium')->count(),
            'high_risk' => $data->where('category', 'High')->count(),
            'critical_risk' => $data->where('category', 'Critical')->count(),
        ];

        $regionSummary = $data
            ->groupBy('region')
            ->map(function ($items, $region) {
                return [
                    'region' => $region,
                    'total' => $items->count(),
                    'average_risk' => round($items->avg('risk_score'), 2),
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

        return view('visualization.index', compact(
            'countries',
            'summary',
            'regionSummary',
            'topRiskCountries',
            'lowRiskCountries'
        ));
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

    private function addRiskData(array $country)
    {
        $seed = abs(crc32($country['name'] . $country['code'] . $country['region']));

        $weatherRisk = 10 + ($seed % 70);
        $currencyRisk = 10 + (intdiv($seed, 7) % 70);
        $newsRisk = 10 + (intdiv($seed, 11) % 75);
        $inflationRisk = 10 + (intdiv($seed, 17) % 65);

        if ($country['landlocked']) {
            $portRisk = 65 + ($seed % 25);
        } else {
            $portRisk = 10 + (intdiv($seed, 13) % 55);
        }

        $riskScore = round(
            ($weatherRisk * 0.25) +
            ($currencyRisk * 0.20) +
            ($newsRisk * 0.25) +
            ($portRisk * 0.15) +
            ($inflationRisk * 0.15),
            2
        );

        $category = 'Low';
        $badge = 'risk-low';

        if ($riskScore > 25 && $riskScore <= 50) {
            $category = 'Medium';
            $badge = 'risk-medium';
        } elseif ($riskScore > 50 && $riskScore <= 75) {
            $category = 'High';
            $badge = 'risk-high';
        } elseif ($riskScore > 75) {
            $category = 'Critical';
            $badge = 'bg-dark text-white';
        }

        $country['weather_risk'] = $weatherRisk;
        $country['currency_risk'] = $currencyRisk;
        $country['news_risk'] = $newsRisk;
        $country['port_risk'] = $portRisk;
        $country['inflation_risk'] = $inflationRisk;
        $country['risk_score'] = $riskScore;
        $country['category'] = $category;
        $country['badge'] = $badge;

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