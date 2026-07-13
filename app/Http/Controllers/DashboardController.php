<?php

namespace App\Http\Controllers;

use App\Models\ApiLog;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Menampilkan Dasbor Utama SupplyGuard.
     */
    public function index()
    {
        $countries = $this->getCachedCountries();
        $weatherData = $this->getCachedWeather();
        $inflationData = $this->getCachedInflation();
        $exchangeRates = $this->getCachedExchangeRates();
        $cachedNewsArticles = $this->getCachedNewsArticles();

        /*
         * Berita disaring agar hanya berisi topik
         * rantai pasok, logistik, ekonomi, dan perdagangan.
         */
        $newsArticles = $this->filterRelevantNews(
            $cachedNewsArticles
        );

        $usingFallbackNews = false;

        /*
         * Gunakan berita internal apabila tidak ada
         * artikel GNews yang relevan.
         */
        if (empty($newsArticles)) {
            $newsArticles = $this->fallbackSupplyChainNews();
            $usingFallbackNews = true;
        }

        $monitoredCodes = [
            'IDN',
            'CHN',
            'DEU',
            'AUS',
            'JPN',
            'SGP',
        ];

        $monitoredCountries = collect($countries)
            ->filter(function (array $country) use ($monitoredCodes) {
                return in_array(
                    $country['code'] ?? '',
                    $monitoredCodes,
                    true
                );
            })
            ->map(function (array $country) use (
                $weatherData,
                $inflationData,
                $exchangeRates,
                $newsArticles
            ) {
                return $this->addDashboardRisk(
                    country: $country,
                    weather: $weatherData[$country['code']] ?? null,
                    inflation: $inflationData[$country['code']] ?? null,
                    exchangeRates: $exchangeRates,
                    newsArticles: $newsArticles
                );
            })
            ->sortBy('name')
            ->values();

        /*
         * Data cadangan dipakai jika cache negara
         * belum tersedia.
         */
        if ($monitoredCountries->isEmpty()) {
            $monitoredCountries = collect(
                $this->fallbackCountries()
            )
                ->map(function (array $country) use ($newsArticles) {
                    return $this->addDashboardRisk(
                        country: $country,
                        weather: null,
                        inflation: null,
                        exchangeRates: [],
                        newsArticles: $newsArticles
                    );
                })
                ->sortBy('name')
                ->values();
        }

        $riskLabels = $monitoredCountries
            ->pluck('name')
            ->values()
            ->all();

        $riskData = $monitoredCountries
            ->pluck('total_risk')
            ->map(function ($risk) {
                return round((float) $risk, 2);
            })
            ->values()
            ->all();

        $summary = [
            'countries' => !empty($countries)
                ? count($countries)
                : 254,

            /*
             * Negara yang tidak terkunci daratan
             * dianggap memiliki akses pelabuhan laut.
             */
            'ports' => !empty($countries)
                ? collect($countries)
                    ->where('landlocked', false)
                    ->count()
                : 208,

            /*
             * Tetap menampilkan jumlah artikel API,
             * sedangkan tabel hanya menampilkan berita relevan.
             */
            'news' => !empty($cachedNewsArticles)
                ? count($cachedNewsArticles)
                : count($newsArticles),

            'average_risk' => round(
                (float) $monitoredCountries->avg('total_risk'),
                2
            ),

            'low_risk' => $monitoredCountries
                ->where('category', 'Low')
                ->count(),

            'medium_risk' => $monitoredCountries
                ->where('category', 'Medium')
                ->count(),

            'high_risk' => $monitoredCountries
                ->whereIn('category', [
                    'High',
                    'Critical',
                ])
                ->count(),
        ];

        $latestNews = $this->buildLatestNews(
            $newsArticles,
            $monitoredCountries->all()
        );

        $apiSummary = [
            'total_logs' => ApiLog::query()->count(),

            'success_logs' => ApiLog::query()
                ->where('status', 'Success')
                ->count(),

            'failed_logs' => ApiLog::query()
                ->where('status', 'Failed')
                ->count(),

            'external_apis' => ApiLog::query()
                ->distinct()
                ->count('api_name'),

            'latest_request' => ApiLog::query()
                ->latest('id')
                ->value('requested_at'),
        ];

        $apiStatus = $this->buildApiStatus(
            countries: $countries,
            weatherData: $weatherData,
            inflationData: $inflationData,
            exchangeRates: $exchangeRates,
            cachedNewsArticles: $cachedNewsArticles,
            usingFallbackNews: $usingFallbackNews
        );

        return view(
            'dashboard',
            compact(
                'summary',
                'riskLabels',
                'riskData',
                'latestNews',
                'apiSummary',
                'apiStatus'
            )
        );
    }

    /**
     * Mengambil cache data negara terbaru.
     */
    private function getCachedCountries(): array
    {
        $cacheKeys = [
            'supplyguard.risk.countries.v5',
            'supplyguard.watchlist.countries.v5',
            'supplyguard.comparison.countries.v5',
            'supplyguard.port.countries.v5',
            'supplyguard.api.countries.v5',
        ];

        foreach ($cacheKeys as $key) {
            $data = Cache::get($key);

            if (is_array($data) && !empty($data)) {
                return $data;
            }
        }

        return [];
    }

    /**
     * Mengambil cache data cuaca terbaru.
     */
    private function getCachedWeather(): array
    {
        $cacheKeys = [
            'supplyguard.risk.weather.v1',
            'supplyguard.weather.current.global.v1',
            'supplyguard.watchlist.weather.v1',
        ];

        foreach ($cacheKeys as $key) {
            $data = Cache::get($key);

            if (is_array($data) && !empty($data)) {
                return $data;
            }
        }

        return [];
    }

    /**
     * Mengambil cache data inflasi World Bank.
     */
    private function getCachedInflation(): array
    {
        $riskInflation = Cache::get(
            'supplyguard.risk.world_bank.inflation.v1'
        );

        if (
            is_array($riskInflation)
            && !empty($riskInflation)
        ) {
            return $riskInflation;
        }

        $comparisonData = Cache::get(
            'supplyguard.comparison.world_bank.v1'
        );

        if (
            is_array($comparisonData)
            && !empty($comparisonData)
        ) {
            return collect($comparisonData)
                ->map(function (array $item) {
                    return $item['inflation'] ?? null;
                })
                ->filter()
                ->all();
        }

        $visualizationData = Cache::get(
            'supplyguard.world_bank.indicators.global.v1'
        );

        if (
            is_array($visualizationData)
            && !empty($visualizationData)
        ) {
            return collect($visualizationData)
                ->map(function (array $item) {
                    return $item['inflation'] ?? null;
                })
                ->filter()
                ->all();
        }

        return [];
    }

    /**
     * Mengambil cache nilai tukar terbaru.
     */
    private function getCachedExchangeRates(): array
    {
        $cacheKeys = [
            'supplyguard.risk.exchange_rates.v1',
            'supplyguard.watchlist.exchange_rates.v1',
            'supplyguard.api.exchange_rates',
        ];

        foreach ($cacheKeys as $key) {
            $data = Cache::get($key);

            if (is_array($data) && !empty($data)) {
                return $data;
            }
        }

        return [];
    }

    /**
     * Mengambil cache berita GNews terbaru.
     */
    private function getCachedNewsArticles(): array
    {
        $cacheKeys = [
            'supplyguard.risk.gnews.v1',
            'supplyguard.watchlist.gnews.v1',
            'supplyguard.news.gnews.global.v1',
        ];

        foreach ($cacheKeys as $key) {
            $data = Cache::get($key);

            if (is_array($data) && !empty($data)) {
                return $data;
            }
        }

        return [];
    }

    /**
     * Menyaring berita yang sesuai dengan
     * tema rantai pasok SupplyGuard.
     */
    private function filterRelevantNews(
        array $articles
    ): array {
        if (empty($articles)) {
            return [];
        }

        return collect($articles)
            ->filter(function ($article) {
                if (!is_array($article)) {
                    return false;
                }

                $text = strtolower(
                    trim(
                        ($article['title'] ?? '')
                        . ' '
                        . ($article['description'] ?? '')
                        . ' '
                        . ($article['content'] ?? '')
                    )
                );

                if ($text === '') {
                    return false;
                }

                return $this->isRelevantSupplyChainText(
                    $text
                );
            })
            ->unique(function (array $article) {
                return strtolower(
                    trim(
                        (string) (
                            $article['title'] ?? ''
                        )
                    )
                );
            })
            ->values()
            ->all();
    }

    /**
     * Memastikan artikel bukan berita olahraga,
     * hiburan, selebritas, atau topik tidak relevan.
     */
    private function isRelevantSupplyChainText(
        string $text
    ): bool {
        $text = strtolower($text);

        /*
         * Kata yang menandakan berita olahraga
         * atau hiburan.
         */
        $excludedKeywords = [
            'football',
            'soccer',
            'basketball',
            'baseball',
            'volleyball',
            'tennis',
            'badminton',
            'boxing',
            'formula 1',
            'motogp',
            'nba',
            'nfl',
            'mlb',
            'nhl',
            'fifa',
            'uefa',
            'player',
            'players',
            'coach',
            'manager',
            'match',
            'game',
            'games',
            'season',
            'league',
            'tournament',
            'championship',
            'score',
            'goal',
            'goals',
            'sixers',
            'lakers',
            'warriors',
            'hawthorn',
            'trade talks',
            'player trade',
            'draft pick',
            'transfer window',
            'playoff',
            'quarterback',
            'striker',
            'midfielder',
            'actor',
            'actress',
            'movie',
            'film',
            'music',
            'singer',
            'celebrity',
            'concert',
            'album',
            'netflix',
            'hollywood',
        ];

        foreach ($excludedKeywords as $keyword) {
            if (str_contains($text, $keyword)) {
                return false;
            }
        }

        /*
         * Kata utama yang berhubungan langsung
         * dengan rantai pasok dan kegiatan impor.
         */
        $supplyChainKeywords = [
            'supply chain',
            'supply-chain',
            'logistics',
            'logistic',
            'shipping',
            'shipment',
            'freight',
            'cargo',
            'container',
            'port congestion',
            'shipping port',
            'seaport',
            'harbor',
            'harbour',
            'maritime',
            'trade route',
            'global trade',
            'international trade',
            'foreign trade',
            'import',
            'imports',
            'export',
            'exports',
            'tariff',
            'tariffs',
            'customs',
            'warehouse',
            'distribution',
            'delivery',
            'transportation',
            'procurement',
            'supplier',
            'suppliers',
            'manufacturing',
            'production',
            'commodity',
            'commodities',
            'inventory',
            'shortage',
            'shortages',
            'route disruption',
            'shipping route',
            'oil price',
            'fuel price',
            'exchange rate',
            'trade war',
            'trade deficit',
            'trade surplus',
            'global commerce',
            'rantai pasok',
            'logistik',
            'pengiriman',
            'pelabuhan',
            'perdagangan internasional',
            'jalur perdagangan',
            'impor',
            'ekspor',
            'bea cukai',
            'gudang',
            'distribusi',
            'pemasok',
            'transportasi',
            'nilai tukar',
            'komoditas',
            'kekurangan barang',
        ];

        foreach ($supplyChainKeywords as $keyword) {
            if (str_contains($text, $keyword)) {
                return true;
            }
        }

        /*
         * Kata ekonomi umum hanya diterima apabila
         * juga memiliki konteks bisnis atau industri.
         */
        $economicKeywords = [
            'inflation',
            'economy',
            'economic',
            'currency',
            'exchange',
            'market',
            'sanction',
            'recession',
            'interest rate',
            'inflasi',
            'ekonomi',
            'mata uang',
            'pasar',
            'sanksi',
            'resesi',
            'suku bunga',
        ];

        $businessKeywords = [
            'business',
            'company',
            'companies',
            'industry',
            'production',
            'goods',
            'commerce',
            'manufacturer',
            'factory',
            'export',
            'import',
            'supplier',
            'shipping',
            'logistics',
            'perusahaan',
            'industri',
            'produksi',
            'barang',
            'perdagangan',
            'pabrik',
            'pemasok',
            'pengiriman',
            'logistik',
        ];

        $hasEconomicKeyword = false;
        $hasBusinessKeyword = false;

        foreach ($economicKeywords as $keyword) {
            if (str_contains($text, $keyword)) {
                $hasEconomicKeyword = true;
                break;
            }
        }

        foreach ($businessKeywords as $keyword) {
            if (str_contains($text, $keyword)) {
                $hasBusinessKeyword = true;
                break;
            }
        }

        return $hasEconomicKeyword
            && $hasBusinessKeyword;
    }

    /**
     * Berita cadangan yang pasti relevan
     * dengan SupplyGuard.
     */
    private function fallbackSupplyChainNews(): array
    {
        return [
            [
                'title' =>
                    'Aktivitas pelabuhan global membutuhkan pemantauan kapasitas pengiriman',

                'description' =>
                    'Perusahaan perlu memantau kepadatan pelabuhan dan jadwal pengiriman untuk mengurangi risiko keterlambatan.',

                'url' => '',

                'source_name' =>
                    'SupplyGuard Internal',

                'published_at' =>
                    now()->toISOString(),
            ],

            [
                'title' =>
                    'Perubahan inflasi dan nilai tukar memengaruhi biaya impor',

                'description' =>
                    'Fluktuasi ekonomi dan mata uang dapat meningkatkan biaya pembelian barang dari negara pemasok.',

                'url' => '',

                'source_name' =>
                    'SupplyGuard Internal',

                'published_at' =>
                    now()->subHour()->toISOString(),
            ],

            [
                'title' =>
                    'Cuaca ekstrem dapat mengganggu jalur logistik dan distribusi',

                'description' =>
                    'Hujan lebat, angin kencang, dan badai berpotensi menghambat transportasi serta pengiriman barang.',

                'url' => '',

                'source_name' =>
                    'SupplyGuard Internal',

                'published_at' =>
                    now()->subHours(2)->toISOString(),
            ],
        ];
    }

    /**
     * Menambahkan skor risiko dasbor.
     */
    private function addDashboardRisk(
        array $country,
        ?array $weather,
        ?array $inflation,
        array $exchangeRates,
        array $newsArticles
    ): array {
        $seed = abs(
            crc32(
                ($country['name'] ?? '')
                . ($country['code'] ?? '')
            )
        );

        $weatherRisk = $this->calculateWeatherRisk(
            $weather,
            $seed
        );

        $inflationRisk = $this->calculateInflationRisk(
            $inflation['value'] ?? null,
            $seed
        );

        $currencyCode = $country['currency_code']
            ?? 'USD';

        $currencyRisk = $this->calculateCurrencyRisk(
            $currencyCode,
            $exchangeRates[$currencyCode] ?? null,
            $seed
        );

        $newsAnalysis = $this->calculateNewsRisk(
            $country,
            $newsArticles,
            $seed
        );

        $portRisk = $this->calculatePortRisk(
            $country,
            $seed
        );

        /*
         * Bobot algoritma SG-Risk:
         * Cuaca 30%
         * Inflasi 20%
         * Mata uang 15%
         * Berita 25%
         * Pelabuhan 10%
         */
        $totalRisk = round(
            ($weatherRisk * 0.30)
            + ($inflationRisk * 0.20)
            + ($currencyRisk * 0.15)
            + ($newsAnalysis['risk'] * 0.25)
            + ($portRisk * 0.10),
            2
        );

        $riskInformation = $this->getRiskInformation(
            $totalRisk
        );

        $country['weather_risk'] = $weatherRisk;
        $country['inflation_risk'] = $inflationRisk;
        $country['currency_risk'] = $currencyRisk;
        $country['news_risk'] = $newsAnalysis['risk'];
        $country['port_risk'] = $portRisk;
        $country['total_risk'] = $totalRisk;

        $country['category'] =
            $riskInformation['category'];

        $country['badge'] =
            $riskInformation['badge'];

        $country['recommendation'] =
            $riskInformation['recommendation'];

        return $country;
    }

    /**
     * Menghitung risiko cuaca.
     */
    private function calculateWeatherRisk(
        ?array $weather,
        int $seed
    ): int {
        if (!is_array($weather)) {
            return 10 + ($seed % 55);
        }

        $temperature = (float) (
            $weather['temperature'] ?? 0
        );

        $rainfall = (float) (
            $weather['rainfall'] ?? 0
        );

        $windSpeed = max(
            (float) ($weather['wind_speed'] ?? 0),
            (float) ($weather['wind_gusts'] ?? 0)
        );

        $weatherCode = (int) (
            $weather['weather_code'] ?? 0
        );

        $risk = 10;

        if (
            $temperature > 42
            || $temperature < -10
        ) {
            $risk += 35;
        } elseif (
            $temperature > 35
            || $temperature < 0
        ) {
            $risk += 20;
        } elseif (
            $temperature > 30
            || $temperature < 5
        ) {
            $risk += 10;
        }

        if ($rainfall > 60) {
            $risk += 30;
        } elseif ($rainfall > 30) {
            $risk += 20;
        } elseif ($rainfall > 10) {
            $risk += 10;
        }

        if ($windSpeed > 70) {
            $risk += 30;
        } elseif ($windSpeed > 45) {
            $risk += 20;
        } elseif ($windSpeed > 25) {
            $risk += 10;
        }

        if (
            in_array(
                $weatherCode,
                [95, 96, 99],
                true
            )
        ) {
            $risk += 30;
        } elseif (
            in_array(
                $weatherCode,
                [80, 81, 82],
                true
            )
        ) {
            $risk += 15;
        }

        return min(100, $risk);
    }

    /**
     * Menghitung risiko inflasi.
     */
    private function calculateInflationRisk(
        mixed $inflation,
        int $seed
    ): int {
        if (!is_numeric($inflation)) {
            return 5 + (intdiv($seed, 7) % 55);
        }

        $inflation = abs(
            (float) $inflation
        );

        if ($inflation <= 2) {
            return 15;
        }

        if ($inflation <= 5) {
            return 30;
        }

        if ($inflation <= 10) {
            return 50;
        }

        if ($inflation <= 20) {
            return 70;
        }

        return 90;
    }

    /**
     * Menghitung risiko mata uang.
     */
    private function calculateCurrencyRisk(
        string $currencyCode,
        mixed $exchangeRate,
        int $seed
    ): int {
        if (!is_numeric($exchangeRate)) {
            return 5 + (intdiv($seed, 11) % 55);
        }

        $risk = 15 + (
            intdiv($seed, 11) % 35
        );

        if ($currencyCode === 'USD') {
            $risk -= 8;
        }

        return max(
            5,
            min(100, $risk)
        );
    }

    /**
     * Menghitung risiko berita.
     */
    private function calculateNewsRisk(
        array $country,
        array $articles,
        int $seed
    ): array {
        if (empty($articles)) {
            return [
                'risk' =>
                    10 + (intdiv($seed, 13) % 60),

                'sentiment' => 'Neutral',
            ];
        }

        $article = $this->selectArticle(
            $country,
            $articles,
            $seed
        );

        $text = strtolower(
            ($article['title'] ?? '')
            . ' '
            . ($article['description'] ?? '')
        );

        $positiveWords = [
            'growth',
            'increase',
            'stable',
            'improve',
            'recovery',
            'strong',
            'safe',
            'export',
            'investment',
            'agreement',
            'success',
            'opportunity',
            'pertumbuhan',
            'meningkat',
            'stabil',
            'pemulihan',
            'aman',
            'keberhasilan',
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
            'decline',
            'disruption',
            'sanction',
            'loss',
            'slowdown',
            'uncertainty',
            'threat',
            'perang',
            'krisis',
            'inflasi',
            'keterlambatan',
            'bencana',
            'konflik',
            'kekurangan',
            'kepadatan',
            'gangguan',
            'ancaman',
        ];

        $positiveCount = 0;
        $negativeCount = 0;

        foreach ($positiveWords as $word) {
            if (str_contains($text, $word)) {
                $positiveCount++;
            }
        }

        foreach ($negativeWords as $word) {
            if (str_contains($text, $word)) {
                $negativeCount++;
            }
        }

        if ($positiveCount > $negativeCount) {
            return [
                'risk' => max(
                    10,
                    30 - ($positiveCount * 3)
                ),

                'sentiment' => 'Positive',
            ];
        }

        if ($negativeCount > $positiveCount) {
            return [
                'risk' => min(
                    95,
                    55 + ($negativeCount * 7)
                ),

                'sentiment' => 'Negative',
            ];
        }

        return [
            'risk' => 40,
            'sentiment' => 'Neutral',
        ];
    }

    /**
     * Memilih artikel yang paling sesuai
     * dengan negara.
     */
    private function selectArticle(
        array $country,
        array $articles,
        int $seed
    ): array {
        $keywords = array_filter([
            $country['name'] ?? null,
            $country['official_name'] ?? null,
            $country['capital'] ?? null,
        ]);

        foreach ($articles as $article) {
            $text = strtolower(
                ($article['title'] ?? '')
                . ' '
                . ($article['description'] ?? '')
            );

            foreach ($keywords as $keyword) {
                $keyword = strtolower(
                    trim((string) $keyword)
                );

                if (
                    strlen($keyword) >= 3
                    && str_contains($text, $keyword)
                ) {
                    return $article;
                }
            }
        }

        return $articles[
            $seed % count($articles)
        ] ?? $articles[0];
    }

    /**
     * Menghitung risiko pelabuhan.
     */
    private function calculatePortRisk(
        array $country,
        int $seed
    ): int {
        if (
            (bool) (
                $country['landlocked'] ?? false
            )
        ) {
            return 85;
        }

        $majorPortCountries = [
            'IDN',
            'SGP',
            'CHN',
            'JPN',
            'DEU',
            'USA',
            'NLD',
            'MYS',
            'AUS',
            'GBR',
        ];

        if (
            in_array(
                $country['code'] ?? '',
                $majorPortCountries,
                true
            )
        ) {
            return 15 + ($seed % 25);
        }

        return 25 + ($seed % 35);
    }

    /**
     * Menentukan kategori risiko.
     */
    private function getRiskInformation(
        float|int $risk
    ): array {
        if ($risk <= 25) {
            return [
                'category' => 'Low',

                'badge' => 'risk-low',

                'recommendation' =>
                    'Jalur relatif aman untuk aktivitas impor.',
            ];
        }

        if ($risk <= 50) {
            return [
                'category' => 'Medium',

                'badge' => 'risk-medium',

                'recommendation' =>
                    'Pantau indikator risiko sebelum melakukan impor.',
            ];
        }

        if ($risk <= 75) {
            return [
                'category' => 'High',

                'badge' => 'risk-high',

                'recommendation' =>
                    'Siapkan negara atau jalur pengiriman alternatif.',
            ];
        }

        return [
            'category' => 'Critical',

            'badge' => 'bg-dark text-white',

            'recommendation' =>
                'Tunda aktivitas impor sampai risiko menurun.',
        ];
    }

    /**
     * Menyiapkan berita terbaru untuk dasbor.
     */
    private function buildLatestNews(
        array $articles,
        array $countries
    ): array {
        if (empty($articles)) {
            return [
                [
                    'title' =>
                        'Pemantauan berita rantai pasok sedang diperbarui',

                    'country' => 'Global',

                    'sentiment' => 'Neutral',

                    'risk' => 'Medium',
                ],
            ];
        }

        return collect($articles)
            ->take(3)
            ->values()
            ->map(function (
                array $article
            ) use ($countries) {
                $analysis = $this->analyzeArticleSentiment(
                    ($article['title'] ?? '')
                    . ' '
                    . ($article['description'] ?? '')
                );

                return [
                    'title' => $article['title']
                        ?? 'Berita tanpa judul',

                    'country' => $this->detectArticleCountry(
                        $article,
                        $countries
                    ),

                    'sentiment' =>
                        $analysis['sentiment'],

                    'risk' =>
                        $analysis['risk_category'],
                ];
            })
            ->all();
    }

    /**
     * Mendeteksi negara yang disebutkan
     * di dalam judul atau deskripsi artikel.
     */
    private function detectArticleCountry(
        array $article,
        array $countries
    ): string {
        $text = strtolower(
            ($article['title'] ?? '')
            . ' '
            . ($article['description'] ?? '')
        );

        foreach ($countries as $country) {
            $countryName = strtolower(
                trim(
                    (string) (
                        $country['name'] ?? ''
                    )
                )
            );

            $officialName = strtolower(
                trim(
                    (string) (
                        $country['official_name'] ?? ''
                    )
                )
            );

            $capital = strtolower(
                trim(
                    (string) (
                        $country['capital'] ?? ''
                    )
                )
            );

            if (
                $countryName !== ''
                && strlen($countryName) >= 3
                && str_contains($text, $countryName)
            ) {
                return $country['name'];
            }

            if (
                $officialName !== ''
                && strlen($officialName) >= 5
                && str_contains($text, $officialName)
            ) {
                return $country['name'];
            }

            if (
                $capital !== ''
                && strlen($capital) >= 4
                && str_contains($text, $capital)
            ) {
                return $country['name'];
            }
        }

        return 'Global';
    }

    /**
     * Menganalisis sentimen berita dasbor.
     */
    private function analyzeArticleSentiment(
        string $text
    ): array {
        $text = strtolower($text);

        $positiveWords = [
            'growth',
            'increase',
            'stable',
            'improve',
            'recovery',
            'strong',
            'safe',
            'export',
            'investment',
            'success',
            'pertumbuhan',
            'meningkat',
            'stabil',
            'pemulihan',
            'aman',
        ];

        $negativeWords = [
            'war',
            'crisis',
            'inflation',
            'delay',
            'conflict',
            'shortage',
            'congestion',
            'decline',
            'disruption',
            'sanction',
            'perang',
            'krisis',
            'inflasi',
            'keterlambatan',
            'konflik',
            'kekurangan',
            'kepadatan',
            'gangguan',
        ];

        $positiveCount = 0;
        $negativeCount = 0;

        foreach ($positiveWords as $word) {
            if (str_contains($text, $word)) {
                $positiveCount++;
            }
        }

        foreach ($negativeWords as $word) {
            if (str_contains($text, $word)) {
                $negativeCount++;
            }
        }

        if ($positiveCount > $negativeCount) {
            return [
                'sentiment' => 'Positive',
                'risk_category' => 'Low',
            ];
        }

        if ($negativeCount > $positiveCount) {
            return [
                'sentiment' => 'Negative',
                'risk_category' => 'High',
            ];
        }

        return [
            'sentiment' => 'Neutral',
            'risk_category' => 'Medium',
        ];
    }

    /**
     * Menyusun keterangan sumber data dasbor.
     */
    private function buildApiStatus(
        array $countries,
        array $weatherData,
        array $inflationData,
        array $exchangeRates,
        array $cachedNewsArticles,
        bool $usingFallbackNews
    ): string {
        $activeSources = [];

        if (!empty($countries)) {
            $activeSources[] = 'REST Countries';
        }

        if (!empty($weatherData)) {
            $activeSources[] = 'Open-Meteo';
        }

        if (!empty($inflationData)) {
            $activeSources[] = 'World Bank';
        }

        if (!empty($exchangeRates)) {
            $activeSources[] = 'Exchange Rate API';
        }

        if (
            !empty($cachedNewsArticles)
            && !$usingFallbackNews
        ) {
            $activeSources[] = 'GNews';
        }

        if (empty($activeSources)) {
            return 'Dasbor menggunakan data cadangan karena cache API belum tersedia.';
        }

        $message = 'Dasbor menggunakan data terbaru dari '
            . implode(', ', $activeSources)
            . '.';

        if ($usingFallbackNews) {
            $message .= ' Berita menggunakan data cadangan karena artikel GNews yang relevan belum tersedia.';
        }

        return $message;
    }

    /**
     * Data negara cadangan.
     */
    private function fallbackCountries(): array
    {
        return [
            [
                'name' => 'Indonesia',
                'official_name' =>
                    'Republic of Indonesia',
                'code' => 'IDN',
                'capital' => 'Jakarta',
                'region' => 'Asia',
                'population' => 277000000,
                'currency_code' => 'IDR',
                'landlocked' => false,
            ],

            [
                'name' => 'China',
                'official_name' =>
                    'People’s Republic of China',
                'code' => 'CHN',
                'capital' => 'Beijing',
                'region' => 'Asia',
                'population' => 1400000000,
                'currency_code' => 'CNY',
                'landlocked' => false,
            ],

            [
                'name' => 'Germany',
                'official_name' =>
                    'Federal Republic of Germany',
                'code' => 'DEU',
                'capital' => 'Berlin',
                'region' => 'Europe',
                'population' => 84000000,
                'currency_code' => 'EUR',
                'landlocked' => false,
            ],

            [
                'name' => 'Australia',
                'official_name' =>
                    'Commonwealth of Australia',
                'code' => 'AUS',
                'capital' => 'Canberra',
                'region' => 'Oceania',
                'population' => 26000000,
                'currency_code' => 'AUD',
                'landlocked' => false,
            ],

            [
                'name' => 'Japan',
                'official_name' => 'Japan',
                'code' => 'JPN',
                'capital' => 'Tokyo',
                'region' => 'Asia',
                'population' => 125000000,
                'currency_code' => 'JPY',
                'landlocked' => false,
            ],

            [
                'name' => 'Singapore',
                'official_name' =>
                    'Republic of Singapore',
                'code' => 'SGP',
                'capital' => 'Singapore',
                'region' => 'Asia',
                'population' => 5900000,
                'currency_code' => 'SGD',
                'landlocked' => false,
            ],
        ];
    }
}