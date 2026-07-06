<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class NewsController extends Controller
{
    public function index()
    {
        $countries = [];
        $apiStatus = 'Mengambil data semua negara untuk news intelligence';

        try {
            $url = 'https://restcountries.com/v3.1/all?fields=name,cca2,capital,region,subregion,flags';

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
                return $this->addNewsIntelligence($country);
            })
            ->sortBy('name')
            ->values()
            ->toArray();

        $summary = [
            'total_news' => count($countries),
            'positive_news' => collect($countries)->where('sentiment', 'Positive')->count(),
            'neutral_news' => collect($countries)->where('sentiment', 'Neutral')->count(),
            'negative_news' => collect($countries)->where('sentiment', 'Negative')->count(),
        ];

        $positiveWords = [
            'growth',
            'increase',
            'profit',
            'stable',
            'improve',
            'recovery',
            'strong',
            'safe',
            'export',
            'investment'
        ];

        $negativeWords = [
            'war',
            'crisis',
            'inflation',
            'delay',
            'disaster',
            'conflict',
            'strike',
            'shortage',
            'congestion',
            'decline'
        ];

        return view('news.index', compact(
            'countries',
            'summary',
            'positiveWords',
            'negativeWords',
            'apiStatus'
        ));
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
                ];
            })
            ->sortBy('name')
            ->values()
            ->toArray();
    }

    private function addNewsIntelligence(array $country)
    {
        $seed = abs(crc32($country['name'] . $country['code'] . $country['region']));

        $categories = [
            'Economy',
            'Shipping',
            'Trade',
            'Logistics',
            'Port',
            'Weather',
            'Geopolitics'
        ];

        $category = $categories[$seed % count($categories)];

        $newsTemplates = [
            'Positive' => [
                'Export activity improves after supply chain recovery',
                'Trade growth increases logistics confidence',
                'Port activity stable and import route remains safe',
                'Investment improves supply chain performance',
            ],
            'Neutral' => [
                'Government monitors trade route and logistics condition',
                'Supply chain activity remains under normal observation',
                'Shipping activity continues with standard monitoring',
                'Market condition remains stable but needs observation',
            ],
            'Negative' => [
                'Port congestion causes shipping delays',
                'Inflation pressure affects import cost',
                'Logistics shortage may disrupt delivery schedule',
                'Regional conflict increases supply chain uncertainty',
            ],
        ];

        $sentimentScore = $seed % 100;

        if ($sentimentScore <= 35) {
            $sentiment = 'Positive';
        } elseif ($sentimentScore <= 65) {
            $sentiment = 'Neutral';
        } else {
            $sentiment = 'Negative';
        }

        $titles = $newsTemplates[$sentiment];
        $title = $titles[$seed % count($titles)];

        $positiveCount = 0;
        $negativeCount = 0;

        $lowerTitle = strtolower($title);

        $positiveWords = [
            'growth',
            'increase',
            'profit',
            'stable',
            'improve',
            'recovery',
            'strong',
            'safe',
            'export',
            'investment'
        ];

        $negativeWords = [
            'war',
            'crisis',
            'inflation',
            'delay',
            'delays',
            'disaster',
            'conflict',
            'strike',
            'shortage',
            'congestion',
            'decline'
        ];

        foreach ($positiveWords as $word) {
            if (str_contains($lowerTitle, $word)) {
                $positiveCount++;
            }
        }

        foreach ($negativeWords as $word) {
            if (str_contains($lowerTitle, $word)) {
                $negativeCount++;
            }
        }

        $newsRisk = 20;

        if ($sentiment === 'Positive') {
            $newsRisk = 15 + ($seed % 15);
        } elseif ($sentiment === 'Neutral') {
            $newsRisk = 30 + ($seed % 20);
        } elseif ($sentiment === 'Negative') {
            $newsRisk = 55 + ($seed % 30);
        }

        $categoryRisk = 'Low';
        $badge = 'risk-low';
        $recommendation = 'News sentiment is positive. Import activity is relatively safe.';

        if ($newsRisk > 25 && $newsRisk <= 50) {
            $categoryRisk = 'Medium';
            $badge = 'risk-medium';
            $recommendation = 'Monitor news development before import activity.';
        } elseif ($newsRisk > 50 && $newsRisk <= 75) {
            $categoryRisk = 'High';
            $badge = 'risk-high';
            $recommendation = 'Prepare alternative shipping route or supplier country.';
        } elseif ($newsRisk > 75) {
            $categoryRisk = 'Critical';
            $badge = 'bg-dark text-white';
            $recommendation = 'Delay import decision until news risk decreases.';
        }

        $country['news_title'] = $title;
        $country['source'] = 'GNews API Simulation';
        $country['news_category'] = $category;
        $country['sentiment'] = $sentiment;
        $country['positive_count'] = $positiveCount;
        $country['negative_count'] = $negativeCount;
        $country['news_risk'] = $newsRisk;
        $country['risk_category'] = $categoryRisk;
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
            ],
            [
                'name' => 'Germany',
                'official_name' => 'Federal Republic of Germany',
                'code' => 'DE',
                'capital' => 'Berlin',
                'region' => 'Europe',
                'subregion' => 'Western Europe',
                'flag' => '',
            ],
            [
                'name' => 'China',
                'official_name' => 'People’s Republic of China',
                'code' => 'CN',
                'capital' => 'Beijing',
                'region' => 'Asia',
                'subregion' => 'Eastern Asia',
                'flag' => '',
            ],
            [
                'name' => 'Australia',
                'official_name' => 'Commonwealth of Australia',
                'code' => 'AU',
                'capital' => 'Canberra',
                'region' => 'Oceania',
                'subregion' => 'Australia and New Zealand',
                'flag' => '',
            ],
        ];
    }
}