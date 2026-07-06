<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class WeatherController extends Controller
{
    public function index()
    {
        $countries = [];
        $apiStatus = 'Mengambil data semua negara untuk weather monitoring';

        try {
            $url = 'https://restcountries.com/v3.1/all?fields=name,cca2,capital,region,subregion,population,currencies,flags,latlng,landlocked';

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
                return $this->addWeatherRisk($country);
            })
            ->sortBy('name')
            ->values()
            ->toArray();

        return view('weather.index', compact('countries', 'apiStatus'));
    }

    private function mapRestCountries(array $data)
    {
        return collect($data)
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

    private function mapMledozeCountries(array $data)
    {
        return collect($data)
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

    private function addWeatherRisk(array $country)
    {
        $seed = abs(crc32($country['name'] . $country['code'] . $country['region']));

        $temperature = 12 + ($seed % 30);
        $rainfall = intdiv($seed, 7) % 45;
        $windSpeed = 5 + (intdiv($seed, 11) % 55);
        $stormRisk = intdiv($seed, 13) % 85;

        $temperatureImpact = 10;
        if ($temperature > 38) {
            $temperatureImpact = 75;
        } elseif ($temperature > 32) {
            $temperatureImpact = 45;
        } elseif ($temperature < 5) {
            $temperatureImpact = 50;
        }

        $rainfallImpact = 10;
        if ($rainfall > 30) {
            $rainfallImpact = 75;
        } elseif ($rainfall > 20) {
            $rainfallImpact = 55;
        } elseif ($rainfall > 10) {
            $rainfallImpact = 35;
        }

        $windImpact = 10;
        if ($windSpeed > 45) {
            $windImpact = 75;
        } elseif ($windSpeed > 35) {
            $windImpact = 55;
        } elseif ($windSpeed > 25) {
            $windImpact = 35;
        }

        $weatherScore = round(
            ($temperatureImpact * 0.25) +
            ($rainfallImpact * 0.30) +
            ($windImpact * 0.25) +
            ($stormRisk * 0.20),
            2
        );

        $category = 'Low';
        $badge = 'risk-low';
        $recommendation = 'Weather condition is safe for import activity.';

        if ($weatherScore > 25 && $weatherScore <= 50) {
            $category = 'Medium';
            $badge = 'risk-medium';
            $recommendation = 'Monitor weather condition before shipment.';
        } elseif ($weatherScore > 50 && $weatherScore <= 75) {
            $category = 'High';
            $badge = 'risk-high';
            $recommendation = 'Prepare alternative shipping schedule.';
        } elseif ($weatherScore > 75) {
            $category = 'Critical';
            $badge = 'bg-dark text-white';
            $recommendation = 'Delay shipment until weather condition improves.';
        }

        $country['temperature'] = $temperature;
        $country['rainfall'] = $rainfall;
        $country['wind_speed'] = $windSpeed;
        $country['storm_risk'] = $stormRisk;
        $country['weather_score'] = $weatherScore;
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