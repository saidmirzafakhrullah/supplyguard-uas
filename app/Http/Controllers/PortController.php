<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class PortController extends Controller
{
    public function index()
    {
        $countries = [];
        $apiStatus = 'Mengambil data semua negara untuk port location dashboard';

        try {
            $url = 'https://restcountries.com/v3.1/all?fields=name,cca2,capital,region,subregion,flags,latlng,landlocked';

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
                return $this->addPortData($country);
            })
            ->sortBy('name')
            ->values()
            ->toArray();

        $summary = [
            'total_countries' => count($countries),
            'available_ports' => collect($countries)->where('port_status', 'Available')->count(),
            'limited_ports' => collect($countries)->where('port_status', 'Limited')->count(),
            'no_seaport' => collect($countries)->where('port_status', 'No Seaport')->count(),
        ];

        return view('ports.index', compact('countries', 'summary', 'apiStatus'));
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

    private function addPortData(array $country)
    {
        $knownPorts = [
            'ID' => [
                'port_name' => 'Tanjung Priok Port',
                'city' => 'Jakarta',
                'latitude' => -6.104,
                'longitude' => 106.880,
            ],
            'CN' => [
                'port_name' => 'Shanghai Port',
                'city' => 'Shanghai',
                'latitude' => 31.230,
                'longitude' => 121.473,
            ],
            'DE' => [
                'port_name' => 'Hamburg Port',
                'city' => 'Hamburg',
                'latitude' => 53.546,
                'longitude' => 9.966,
            ],
            'SG' => [
                'port_name' => 'Port of Singapore',
                'city' => 'Singapore',
                'latitude' => 1.264,
                'longitude' => 103.840,
            ],
            'JP' => [
                'port_name' => 'Yokohama Port',
                'city' => 'Yokohama',
                'latitude' => 35.443,
                'longitude' => 139.638,
            ],
            'AU' => [
                'port_name' => 'Port Botany',
                'city' => 'Sydney',
                'latitude' => -33.969,
                'longitude' => 151.225,
            ],
            'US' => [
                'port_name' => 'Port of Los Angeles',
                'city' => 'Los Angeles',
                'latitude' => 33.740,
                'longitude' => -118.270,
            ],
            'GB' => [
                'port_name' => 'Port of Felixstowe',
                'city' => 'Felixstowe',
                'latitude' => 51.954,
                'longitude' => 1.351,
            ],
            'MY' => [
                'port_name' => 'Port Klang',
                'city' => 'Selangor',
                'latitude' => 3.000,
                'longitude' => 101.400,
            ],
            'TH' => [
                'port_name' => 'Laem Chabang Port',
                'city' => 'Chonburi',
                'latitude' => 13.083,
                'longitude' => 100.883,
            ],
        ];

        $seed = abs(crc32($country['name'] . $country['code']));

        if (isset($knownPorts[$country['code']])) {
            $port = $knownPorts[$country['code']];
            $portName = $port['port_name'];
            $portCity = $port['city'];
            $portLatitude = $port['latitude'];
            $portLongitude = $port['longitude'];
            $portStatus = 'Available';
            $portCount = 3 + ($seed % 8);
            $portRisk = 10 + ($seed % 20);
        } elseif ($country['landlocked']) {
            $portName = 'No direct seaport';
            $portCity = 'Landlocked country';
            $portLatitude = $country['latitude'];
            $portLongitude = $country['longitude'];
            $portStatus = 'No Seaport';
            $portCount = 0;
            $portRisk = 70 + ($seed % 20);
        } else {
            $portName = 'Main International Port of ' . $country['name'];
            $portCity = $country['capital'];
            $portLatitude = $country['latitude'];
            $portLongitude = $country['longitude'];
            $portStatus = 'Available';
            $portCount = 1 + ($seed % 5);
            $portRisk = 20 + ($seed % 35);
        }

        if (!$country['landlocked'] && $portCount <= 2) {
            $portStatus = 'Limited';
            $portRisk = 40 + ($seed % 25);
        }

        $category = 'Low';
        $badge = 'risk-low';
        $recommendation = 'Port availability supports import activity.';

        if ($portRisk > 25 && $portRisk <= 50) {
            $category = 'Medium';
            $badge = 'risk-medium';
            $recommendation = 'Monitor port capacity before shipment.';
        } elseif ($portRisk > 50 && $portRisk <= 75) {
            $category = 'High';
            $badge = 'risk-high';
            $recommendation = 'Prepare alternative port or shipping route.';
        } elseif ($portRisk > 75) {
            $category = 'Critical';
            $badge = 'bg-dark text-white';
            $recommendation = 'Delay shipment or use neighboring country port.';
        }

        $country['port_name'] = $portName;
        $country['port_city'] = $portCity;
        $country['port_latitude'] = $portLatitude;
        $country['port_longitude'] = $portLongitude;
        $country['port_status'] = $portStatus;
        $country['port_count'] = $portCount;
        $country['port_risk'] = $portRisk;
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
                'flag' => '',
                'latitude' => 39.9,
                'longitude' => 116.4,
                'landlocked' => false,
            ],
            [
                'name' => 'Singapore',
                'official_name' => 'Republic of Singapore',
                'code' => 'SG',
                'capital' => 'Singapore',
                'region' => 'Asia',
                'subregion' => 'Southeast Asia',
                'flag' => '',
                'latitude' => 1.35,
                'longitude' => 103.82,
                'landlocked' => false,
            ],
        ];
    }
}