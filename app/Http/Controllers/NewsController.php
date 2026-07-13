<?php

namespace App\Http\Controllers;

use App\Services\ApiLogService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class NewsController extends Controller
{
    /**
     * Menampilkan halaman Intelijen Berita.
     */
    public function index()
    {
        $countries = $this->getCountries();

        if (empty($countries)) {
            $countries = $this->fallbackCountries();
        }

        $articles = $this->getNewsArticles();
        $usingRealNews = !empty($articles);

        $countries = collect($countries)
            ->map(function (array $country) use (
                $articles,
                $usingRealNews
            ) {
                if ($usingRealNews) {
                    return $this->addRealNewsIntelligence(
                        $country,
                        $articles
                    );
                }

                return $this->addFallbackNewsIntelligence(
                    $country
                );
            })
            ->sortBy('name')
            ->values()
            ->toArray();

        $summary = [
            'total_news' => count($countries),

            'positive_news' => collect($countries)
                ->where('sentiment', 'Positive')
                ->count(),

            'neutral_news' => collect($countries)
                ->where('sentiment', 'Neutral')
                ->count(),

            'negative_news' => collect($countries)
                ->where('sentiment', 'Negative')
                ->count(),
        ];

        $positiveWords = $this->positiveWords();
        $negativeWords = $this->negativeWords();

        if ($usingRealNews) {
            $apiStatus = 'Berita aktual berhasil diambil dari GNews API. '
                . count($articles)
                . ' artikel digunakan untuk analisis seluruh negara.';
        } else {
            $apiStatus = 'GNews API tidak dapat diakses atau tidak '
                . 'mengembalikan artikel. Sistem menggunakan data '
                . 'berita cadangan.';
        }

        return view('news.index', compact(
            'countries',
            'summary',
            'positiveWords',
            'negativeWords',
            'apiStatus'
        ));
    }

    /**
     * Mengambil seluruh negara dari REST Countries API v5.
     */
    private function getCountries(): array
    {
        return Cache::remember(
            'supplyguard.api.countries.v5',
            now()->addHours(12),
            function () {
                $startedAt = microtime(true);
                $response = null;

                $baseUrl = rtrim(
                    config(
                        'services.rest_countries.base_url',
                        'https://api.restcountries.com/countries/v5'
                    ),
                    '/'
                );

                try {
                    $apiKey = config(
                        'services.rest_countries.key'
                    );

                    if (empty($apiKey)) {
                        throw new RuntimeException(
                            'REST Countries API key belum dikonfigurasi.'
                        );
                    }

                    $allCountries = [];
                    $limit = 100;
                    $offset = 0;
                    $more = true;

                    while ($more) {
                        $response = Http::withToken($apiKey)
                            ->acceptJson()
                            ->timeout(30)
                            ->retry(2, 500)
                            ->get($baseUrl, [
                                'limit' => $limit,
                                'offset' => $offset,

                                'response_fields' => implode(',', [
                                    'names.common',
                                    'names.official',
                                    'codes.alpha_3',
                                    'capitals',
                                    'region',
                                    'subregion',
                                    'population',
                                    'currencies',
                                    'coordinates.lat',
                                    'coordinates.lng',
                                    'flag.url_png',
                                    'flag.url_svg',
                                    'landlocked',
                                ]),
                            ]);

                        if (!$response->successful()) {
                            throw new RuntimeException(
                                'REST Countries API gagal dengan '
                                . 'status HTTP '
                                . $response->status()
                            );
                        }

                        $objects = $response->json(
                            'data.objects',
                            []
                        );

                        $meta = $response->json(
                            'data.meta',
                            []
                        );

                        if (!is_array($objects)) {
                            $objects = [];
                        }

                        $allCountries = array_merge(
                            $allCountries,
                            $objects
                        );

                        $more = (bool) (
                            $meta['more'] ?? false
                        );

                        $offset += $limit;

                        /*
                         * Pengamanan agar perulangan tidak
                         * berjalan tanpa batas.
                         */
                        if ($offset > 500) {
                            break;
                        }
                    }

                    $countries = collect($allCountries)
                        ->map(function (array $item) {
                            $currencies = data_get(
                                $item,
                                'currencies',
                                []
                            );

                            $currencyCode = 'USD';
                            $currencyName = 'Unknown Currency';

                            if (
                                is_array($currencies)
                                && !empty($currencies)
                            ) {
                                if (array_is_list($currencies)) {
                                    $firstCurrency =
                                        $currencies[0] ?? [];

                                    if (is_array($firstCurrency)) {
                                        $currencyCode = (string) data_get(
                                            $firstCurrency,
                                            'code',
                                            data_get(
                                                $firstCurrency,
                                                'currency_code',
                                                data_get(
                                                    $firstCurrency,
                                                    'iso_code',
                                                    'USD'
                                                )
                                            )
                                        );

                                        $currencyName = (string) data_get(
                                            $firstCurrency,
                                            'name',
                                            $currencyCode
                                        );
                                    }
                                } else {
                                    $firstCurrencyCode =
                                        array_key_first($currencies);

                                    if (
                                        is_string($firstCurrencyCode)
                                        && $firstCurrencyCode !== ''
                                    ) {
                                        $currencyCode =
                                            $firstCurrencyCode;

                                        $currencyName = (string) data_get(
                                            $currencies,
                                            $currencyCode . '.name',
                                            $currencyCode
                                        );
                                    }
                                }
                            }

                            return [
                                'name' => data_get(
                                    $item,
                                    'names.common',
                                    'Unknown'
                                ),

                                'official_name' => data_get(
                                    $item,
                                    'names.official',
                                    'Unknown'
                                ),

                                'code' => data_get(
                                    $item,
                                    'codes.alpha_3',
                                    '-'
                                ),

                                'capital' => data_get(
                                    $item,
                                    'capitals.0.name',
                                    '-'
                                ),

                                'region' => data_get(
                                    $item,
                                    'region',
                                    '-'
                                ),

                                'subregion' => data_get(
                                    $item,
                                    'subregion',
                                    '-'
                                ),

                                'population' => (int) data_get(
                                    $item,
                                    'population',
                                    0
                                ),

                                'currency_code' => $currencyCode,
                                'currency_name' => $currencyName,

                                'flag' => data_get(
                                    $item,
                                    'flag.url_png'
                                ) ?? data_get(
                                    $item,
                                    'flag.url_svg'
                                ),

                                'latitude' => (float) data_get(
                                    $item,
                                    'coordinates.lat',
                                    0
                                ),

                                'longitude' => (float) data_get(
                                    $item,
                                    'coordinates.lng',
                                    0
                                ),

                                'landlocked' => (bool) data_get(
                                    $item,
                                    'landlocked',
                                    false
                                ),
                            ];
                        })
                        ->filter(function (array $country) {
                            return $country['name'] !== 'Unknown'
                                && $country['code'] !== '-';
                        })
                        ->sortBy('name')
                        ->values()
                        ->all();

                    if (empty($countries)) {
                        throw new RuntimeException(
                            'REST Countries API tidak '
                            . 'mengembalikan data negara.'
                        );
                    }

                    $responseTime = (int) round(
                        (microtime(true) - $startedAt) * 1000
                    );

                    ApiLogService::success(
                        apiName: 'REST Countries API v5',
                        endpoint: $baseUrl,
                        feature: 'Intelijen Berita',
                        statusCode: $response?->status() ?? 200,
                        responseTime: $responseTime,
                        description: 'Berhasil mengambil '
                            . count($countries)
                            . ' data negara untuk Intelijen Berita.'
                    );

                    return $countries;
                } catch (Throwable $exception) {
                    $responseTime = (int) round(
                        (microtime(true) - $startedAt) * 1000
                    );

                    ApiLogService::failed(
                        apiName: 'REST Countries API v5',
                        endpoint: $baseUrl,
                        feature: 'Intelijen Berita',
                        statusCode: $response?->status(),
                        responseTime: $responseTime,
                        description: 'Gagal mengambil data negara '
                            . 'untuk Intelijen Berita.',
                        errorMessage: $exception->getMessage()
                    );

                    report($exception);

                    return $this->fallbackCountries();
                }
            }
        );
    }

    /**
     * Mengambil berita aktual dari GNews API.
     */
    private function getNewsArticles(): array
    {
        $cacheKey = 'supplyguard.news.gnews.global.v1';

        $cachedArticles = Cache::get($cacheKey);

        if (
            is_array($cachedArticles)
            && !empty($cachedArticles)
        ) {
            return $cachedArticles;
        }

        $startedAt = microtime(true);
        $response = null;

        $baseUrl = rtrim(
            config(
                'services.gnews.base_url',
                'https://gnews.io/api/v4'
            ),
            '/'
        );

        $endpoint = $baseUrl . '/search';

        try {
            $apiKey = config('services.gnews.key');

            if (empty($apiKey)) {
                throw new RuntimeException(
                    'GNews API key belum dikonfigurasi.'
                );
            }

            /*
             * Dibatasi maksimal 10 agar aman digunakan
             * pada paket gratis GNews.
             */
            $maxArticles = (int) config(
                'services.gnews.max_articles',
                10
            );

            $maxArticles = max(
                1,
                min(10, $maxArticles)
            );

            $language = config(
                'services.gnews.language',
                'en'
            );

            $query = '"supply chain" OR logistics OR shipping '
                . 'OR trade OR port OR inflation';

            $response = Http::withHeaders([
                'X-Api-Key' => $apiKey,
            ])
                ->acceptJson()
                ->timeout(30)
                ->retry(2, 500)
                ->get($endpoint, [
                    'q' => $query,
                    'lang' => $language,
                    'max' => $maxArticles,
                    'in' => 'title,description',
                    'sortby' => 'publishedAt',
                ]);

            if (!$response->successful()) {
                throw new RuntimeException(
                    'GNews API gagal dengan status HTTP '
                    . $response->status()
                );
            }

            $articles = $response->json(
                'articles',
                []
            );

            if (!is_array($articles)) {
                $articles = [];
            }

            $articles = collect($articles)
                ->filter(function ($article) {
                    return is_array($article)
                        && !empty($article['title']);
                })
                ->map(function (array $article) {
                    return [
                        'title' => (string) data_get(
                            $article,
                            'title',
                            'Berita tanpa judul'
                        ),

                        'description' => (string) data_get(
                            $article,
                            'description',
                            ''
                        ),

                        'content' => (string) data_get(
                            $article,
                            'content',
                            ''
                        ),

                        'url' => (string) data_get(
                            $article,
                            'url',
                            ''
                        ),

                        'image' => data_get(
                            $article,
                            'image'
                        ),

                        'published_at' => data_get(
                            $article,
                            'publishedAt'
                        ),

                        'source_name' => (string) data_get(
                            $article,
                            'source.name',
                            'GNews'
                        ),

                        'source_url' => (string) data_get(
                            $article,
                            'source.url',
                            ''
                        ),
                    ];
                })
                ->values()
                ->all();

            if (empty($articles)) {
                throw new RuntimeException(
                    'GNews API tidak mengembalikan artikel.'
                );
            }

            Cache::put(
                $cacheKey,
                $articles,
                now()->addMinutes(30)
            );

            $responseTime = (int) round(
                (microtime(true) - $startedAt) * 1000
            );

            ApiLogService::success(
                apiName: 'GNews API',
                endpoint: $endpoint,
                feature: 'Intelijen Berita',
                statusCode: $response->status(),
                responseTime: $responseTime,
                description: 'Berhasil mengambil '
                    . count($articles)
                    . ' artikel berita aktual untuk '
                    . 'analisis sentimen.'
            );

            return $articles;
        } catch (Throwable $exception) {
            $responseTime = (int) round(
                (microtime(true) - $startedAt) * 1000
            );

            ApiLogService::failed(
                apiName: 'GNews API',
                endpoint: $endpoint,
                feature: 'Intelijen Berita',
                statusCode: $response?->status(),
                responseTime: $responseTime,
                description: 'Gagal mengambil berita aktual. '
                    . 'Sistem menggunakan data berita cadangan.',
                errorMessage: $exception->getMessage()
            );

            /*
             * Simpan kegagalan sebentar agar halaman
             * tidak terus-menerus memanggil API.
             */
            Cache::put(
                $cacheKey,
                [],
                now()->addMinutes(5)
            );

            report($exception);

            return [];
        }
    }

    /**
     * Menambahkan intelijen berita asli
     * pada setiap negara.
     */
    private function addRealNewsIntelligence(
        array $country,
        array $articles
    ): array {
        $article = $this->selectArticleForCountry(
            $country,
            $articles
        );

        if ($article === null) {
            return $this->addFallbackNewsIntelligence(
                $country
            );
        }

        $title = $article['title']
            ?? 'Berita tanpa judul';

        $description = $article['description'] ?? '';

        $analysisText = trim(
            $title . ' ' . $description
        );

        $analysis = $this->analyzeSentiment(
            $analysisText
        );

        $sentiment = $analysis['sentiment'];
        $positiveCount = $analysis['positive_count'];
        $negativeCount = $analysis['negative_count'];

        $newsRisk = $this->calculateNewsRisk(
            $sentiment,
            $positiveCount,
            $negativeCount
        );

        $riskInfo = $this->getRiskInformation(
            $newsRisk
        );

        $country['news_title'] = $title;

        $country['news_description'] =
            $description !== ''
                ? $description
                : 'Tidak tersedia deskripsi berita.';

        $country['source'] =
            $article['source_name'] ?? 'GNews';

        $country['news_url'] =
            $article['url'] ?? '';

        $country['news_image'] =
            $article['image'] ?? null;

        $country['published_at'] =
            $article['published_at'] ?? null;

        $country['news_category'] =
            $this->detectNewsCategory(
                $analysisText
            );

        $country['sentiment'] = $sentiment;
        $country['positive_count'] = $positiveCount;
        $country['negative_count'] = $negativeCount;
        $country['news_risk'] = $newsRisk;

        $country['risk_category'] =
            $riskInfo['category'];

        $country['badge'] =
            $riskInfo['badge'];

        $country['recommendation'] =
            $riskInfo['recommendation'];

        $country['news_data_source'] =
            'GNews API';

        return $country;
    }

    /**
     * Memilih artikel yang paling sesuai untuk negara.
     */
    private function selectArticleForCountry(
        array $country,
        array $articles
    ): ?array {
        if (empty($articles)) {
            return null;
        }

        $countryKeywords = array_filter([
            $country['name'] ?? null,
            $country['official_name'] ?? null,
            $country['capital'] ?? null,
        ]);

        foreach ($articles as $article) {
            $articleText = strtolower(
                trim(
                    ($article['title'] ?? '')
                    . ' '
                    . ($article['description'] ?? '')
                )
            );

            foreach ($countryKeywords as $keyword) {
                $keyword = strtolower(
                    trim((string) $keyword)
                );

                if (
                    strlen($keyword) >= 3
                    && str_contains(
                        $articleText,
                        $keyword
                    )
                ) {
                    return $article;
                }
            }
        }

        /*
         * Jika tidak ada artikel yang menyebut negara,
         * pilih artikel secara konsisten berdasarkan
         * kode negara.
         */
        $seed = abs(
            crc32(
                ($country['name'] ?? '')
                . ($country['code'] ?? '')
            )
        );

        $index = $seed % count($articles);

        return $articles[$index] ?? $articles[0];
    }

    /**
     * Analisis sentimen berbasis kamus kata.
     */
    private function analyzeSentiment(
        string $text
    ): array {
        $positiveCount = 0;
        $negativeCount = 0;

        foreach ($this->positiveWords() as $word) {
            $positiveCount += $this->countWordMatches(
                $text,
                $word
            );
        }

        foreach ($this->negativeWords() as $word) {
            $negativeCount += $this->countWordMatches(
                $text,
                $word
            );
        }

        if ($positiveCount > $negativeCount) {
            $sentiment = 'Positive';
        } elseif ($negativeCount > $positiveCount) {
            $sentiment = 'Negative';
        } else {
            $sentiment = 'Neutral';
        }

        return [
            'sentiment' => $sentiment,
            'positive_count' => $positiveCount,
            'negative_count' => $negativeCount,
        ];
    }

    /**
     * Menghitung kemunculan kata dalam teks.
     */
    private function countWordMatches(
        string $text,
        string $word
    ): int {
        $pattern = '/\b'
            . preg_quote($word, '/')
            . '\b/ui';

        $result = preg_match_all(
            $pattern,
            strtolower($text)
        );

        return $result !== false
            ? $result
            : 0;
    }

    /**
     * Menentukan kategori berita.
     */
    private function detectNewsCategory(
        string $text
    ): string {
        $text = strtolower($text);

        if (
            str_contains($text, 'port')
            || str_contains($text, 'harbor')
            || str_contains($text, 'terminal')
            || str_contains($text, 'congestion')
        ) {
            return 'Port';
        }

        if (
            str_contains($text, 'shipping')
            || str_contains($text, 'ship')
            || str_contains($text, 'vessel')
            || str_contains($text, 'maritime')
            || str_contains($text, 'freight')
        ) {
            return 'Shipping';
        }

        if (
            str_contains($text, 'logistics')
            || str_contains($text, 'supply chain')
            || str_contains($text, 'delivery')
            || str_contains($text, 'warehouse')
        ) {
            return 'Logistics';
        }

        if (
            str_contains($text, 'trade')
            || str_contains($text, 'import')
            || str_contains($text, 'export')
            || str_contains($text, 'tariff')
        ) {
            return 'Trade';
        }

        if (
            str_contains($text, 'weather')
            || str_contains($text, 'storm')
            || str_contains($text, 'flood')
            || str_contains($text, 'drought')
        ) {
            return 'Weather';
        }

        if (
            str_contains($text, 'war')
            || str_contains($text, 'conflict')
            || str_contains($text, 'sanction')
            || str_contains($text, 'geopolitic')
        ) {
            return 'Geopolitics';
        }

        return 'Economy';
    }

    /**
     * Menghitung risiko berita.
     */
    private function calculateNewsRisk(
        string $sentiment,
        int $positiveCount,
        int $negativeCount
    ): int {
        if ($sentiment === 'Positive') {
            $risk = 25
                - ($positiveCount * 3)
                + ($negativeCount * 6);
        } elseif ($sentiment === 'Negative') {
            $risk = 60
                + ($negativeCount * 8)
                - ($positiveCount * 3);
        } else {
            $risk = 40
                + ($negativeCount * 5)
                - ($positiveCount * 2);
        }

        return max(
            0,
            min(100, $risk)
        );
    }

    /**
     * Menentukan kategori risiko dan rekomendasi.
     */
    private function getRiskInformation(
        int|float $risk
    ): array {
        if ($risk <= 25) {
            return [
                'category' => 'Low',
                'badge' => 'risk-low',
                'recommendation' =>
                    'Sentimen berita relatif positif. '
                    . 'Aktivitas impor cukup aman.',
            ];
        }

        if ($risk <= 50) {
            return [
                'category' => 'Medium',
                'badge' => 'risk-medium',
                'recommendation' =>
                    'Pantau perkembangan berita sebelum '
                    . 'melakukan aktivitas impor.',
            ];
        }

        if ($risk <= 75) {
            return [
                'category' => 'High',
                'badge' => 'risk-high',
                'recommendation' =>
                    'Siapkan jalur pengiriman atau negara '
                    . 'pemasok alternatif.',
            ];
        }

        return [
            'category' => 'Critical',
            'badge' => 'bg-dark text-white',
            'recommendation' =>
                'Tunda keputusan impor sampai risiko '
                . 'berita menurun.',
        ];
    }

    /**
     * Data berita cadangan ketika GNews gagal.
     */
    private function addFallbackNewsIntelligence(
        array $country
    ): array {
        $seed = abs(
            crc32(
                $country['name']
                . $country['code']
                . $country['region']
            )
        );

        $categories = [
            'Economy',
            'Shipping',
            'Trade',
            'Logistics',
            'Port',
            'Weather',
            'Geopolitics',
        ];

        $category = $categories[
            $seed % count($categories)
        ];

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

        $title = $titles[
            $seed % count($titles)
        ];

        $analysis = $this->analyzeSentiment(
            $title
        );

        $positiveCount =
            $analysis['positive_count'];

        $negativeCount =
            $analysis['negative_count'];

        $newsRisk = $this->calculateNewsRisk(
            $sentiment,
            $positiveCount,
            $negativeCount
        );

        $riskInfo = $this->getRiskInformation(
            $newsRisk
        );

        $country['news_title'] = $title;

        $country['news_description'] =
            'Data berita cadangan internal SupplyGuard.';

        $country['source'] =
            'Simulasi Internal SupplyGuard';

        $country['news_url'] = '';
        $country['news_image'] = null;
        $country['published_at'] = null;

        $country['news_category'] = $category;
        $country['sentiment'] = $sentiment;
        $country['positive_count'] = $positiveCount;
        $country['negative_count'] = $negativeCount;
        $country['news_risk'] = $newsRisk;

        $country['risk_category'] =
            $riskInfo['category'];

        $country['badge'] =
            $riskInfo['badge'];

        $country['recommendation'] =
            $riskInfo['recommendation'];

        $country['news_data_source'] =
            'Data cadangan';

        return $country;
    }

    /**
     * Kamus kata positif.
     */
    private function positiveWords(): array
    {
        return [
            'growth',
            'increase',
            'profit',
            'stable',
            'improve',
            'improved',
            'recovery',
            'strong',
            'safe',
            'export',
            'investment',
            'gain',
            'boost',
            'surge',
            'agreement',
            'expansion',
            'record',
            'success',
            'secure',
            'opportunity',
        ];
    }

    /**
     * Kamus kata negatif.
     */
    private function negativeWords(): array
    {
        return [
            'war',
            'crisis',
            'inflation',
            'delay',
            'delays',
            'delayed',
            'disaster',
            'conflict',
            'strike',
            'shortage',
            'congestion',
            'decline',
            'risk',
            'disruption',
            'attack',
            'sanction',
            'tariff',
            'loss',
            'drop',
            'slowdown',
            'uncertainty',
            'threat',
            'blocked',
            'closure',
        ];
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
                'subregion' =>
                    'South-Eastern Asia',
                'population' => 277000000,
                'currency_code' => 'IDR',
                'currency_name' =>
                    'Indonesian Rupiah',
                'flag' => null,
                'latitude' => -6.2,
                'longitude' => 106.8,
                'landlocked' => false,
            ],

            [
                'name' => 'Germany',
                'official_name' =>
                    'Federal Republic of Germany',
                'code' => 'DEU',
                'capital' => 'Berlin',
                'region' => 'Europe',
                'subregion' =>
                    'Western Europe',
                'population' => 84000000,
                'currency_code' => 'EUR',
                'currency_name' => 'Euro',
                'flag' => null,
                'latitude' => 52.5,
                'longitude' => 13.4,
                'landlocked' => false,
            ],

            [
                'name' => 'China',
                'official_name' =>
                    'People’s Republic of China',
                'code' => 'CHN',
                'capital' => 'Beijing',
                'region' => 'Asia',
                'subregion' =>
                    'Eastern Asia',
                'population' => 1400000000,
                'currency_code' => 'CNY',
                'currency_name' =>
                    'Chinese Yuan',
                'flag' => null,
                'latitude' => 39.9,
                'longitude' => 116.4,
                'landlocked' => false,
            ],

            [
                'name' => 'Australia',
                'official_name' =>
                    'Commonwealth of Australia',
                'code' => 'AUS',
                'capital' => 'Canberra',
                'region' => 'Oceania',
                'subregion' =>
                    'Australia and New Zealand',
                'population' => 26000000,
                'currency_code' => 'AUD',
                'currency_name' =>
                    'Australian Dollar',
                'flag' => null,
                'latitude' => -35.3,
                'longitude' => 149.1,
                'landlocked' => false,
            ],
        ];
    }
}