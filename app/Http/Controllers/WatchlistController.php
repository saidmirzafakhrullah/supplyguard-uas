<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class WatchlistController extends Controller
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
                return $this->addMonitoringData($country);
            })
            ->sortBy('name')
            ->values()
            ->toArray();

        $watchlistCodes = ['ID', 'CN', 'DE', 'JP', 'SG', 'AU'];

        $watchlistCountries = collect($countries)
            ->filter(function ($country) use ($watchlistCodes) {
                return in_array($country['code'], $watchlistCodes);
            })
            ->values()
            ->toArray();

        $summary = [
            'total_countries' => count($countries),
            'watchlist_count' => count($watchlistCountries),
            'low_risk' => collect($watchlistCountries)->where('category', 'Low')->count(),
            'medium_risk' => collect($watchlistCountries)->where('category', 'Medium')->count(),
            'high_risk' => collect($watchlistCountries)->where('category', 'High')->count(),
            'critical_risk' => collect($watchlistCountries)->where('category', 'Critical')->count(),
        ];

        return view('watchlist.index', compact(
            'countries',
            'watchlistCountries',
            'summary'
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

    private function addMonitoringData(array $country)
    {
        $seed = abs(crc32($country['name'] . $country['code'] . $country['region']));

        $weatherRisk = 10 + ($seed % 60);
        $currencyRisk = 5 + (intdiv($seed, 7) % 60);
        $newsRisk = 10 + (intdiv($seed, 11) % 70);

        if ($country['landlocked']) {
            $portRisk = 65 + ($seed % 25);
        } else {
            $portRisk = 10 + (intdiv($seed, 13) % 50);
        }

        $riskScore = round(
            ($weatherRisk * 0.30) +
            ($currencyRisk * 0.20) +
            ($newsRisk * 0.30) +
            ($portRisk * 0.20),
            2
        );

        $category = 'Low';
        $badge = 'risk-low';
        $alertLevel = 'Safe';
        $recommendation = 'Negara aman untuk dipantau sebagai jalur impor.';

        if ($riskScore > 25 && $riskScore <= 50) {
            $category = 'Medium';
            $badge = 'risk-medium';
            $alertLevel = 'Monitor';
            $recommendation = 'Pantau perkembangan cuaca, kurs, dan berita sebelum impor.';
        } elseif ($riskScore > 50 && $riskScore <= 75) {
            $category = 'High';
            $badge = 'risk-high';
            $alertLevel = 'Warning';
            $recommendation = 'Siapkan negara alternatif atau jadwal pengiriman cadangan.';
        } elseif ($riskScore > 75) {
            $category = 'Critical';
            $badge = 'bg-dark text-white';
            $alertLevel = 'Critical';
            $recommendation = 'Sebaiknya transaksi impor ditunda sampai risiko menurun.';
        }

        $country['weather_risk'] = $weatherRisk;
        $country['currency_risk'] = $currencyRisk;
        $country['news_risk'] = $newsRisk;
        $country['port_risk'] = $portRisk;
        $country['risk_score'] = $riskScore;
        $country['category'] = $category;
        $country['badge'] = $badge;
        $country['alert_level'] = $alertLevel;
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