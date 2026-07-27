<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\NewsCache;
use App\Models\Port;
use App\Models\RiskScore;
use App\Models\SentimentWord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SupplyGuardCoreFeaturesTest extends TestCase
{
    use RefreshDatabase;

    private function country(): Country
    {
        return Country::query()->create([
            'name' => 'Indonesia', 'official_name' => 'Republic of Indonesia',
            'code' => 'IDN', 'capital' => 'Jakarta', 'region' => 'Asia',
            'subregion' => 'South-Eastern Asia', 'population' => 277000000,
            'currency_code' => 'IDR', 'currency_name' => 'Indonesian Rupiah',
            'latitude' => -5, 'longitude' => 120, 'source' => 'Manual Admin',
        ]);
    }

    public function test_country_page_and_api_use_database_country(): void
    {
        $this->country();
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('countries.index'))
            ->assertOk()->assertSee('Indonesia')->assertSee('Database');

        $this->getJson('/api/countries?country=IDN')
            ->assertOk()->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.code', 'IDN');
    }

    public function test_live_news_is_saved_to_database_cache(): void
    {
        $this->country();
        SentimentWord::query()->create([
            'word' => 'bespokeupside', 'type' => 'positive',
            'status' => 'active', 'weight' => 1,
        ]);
        config(['services.gnews.key' => 'test-key']);
        Http::fake([
            '*gnews*' => Http::response(['articles' => [[
                'title' => 'Bespokeupside for supply chain',
                'description' => 'Shipping and logistics report.',
                'url' => 'https://example.test/news/1',
                'image' => 'https://example.test/news.jpg',
                'publishedAt' => now()->toISOString(),
                'source' => ['name' => 'Test News', 'url' => 'https://example.test'],
            ]]]),
        ]);

        $this->actingAs(User::factory()->create())->get(route('news.index'))->assertOk();
        $this->assertDatabaseHas('news_cache', [
            'title' => 'Bespokeupside for supply chain',
            'source_name' => 'Test News',
            'sentiment' => 'Positive',
            'positive_words' => 1,
        ]);
    }

    public function test_risk_page_persists_daily_snapshot(): void
    {
        $this->country();
        Http::fake(['*' => Http::response([])]);

        $this->actingAs(User::factory()->create())->get(route('risk.index'))->assertOk();

        $this->assertSame(1, RiskScore::query()->where('country_code', 'IDN')->count());
        $this->assertDatabaseHas('risk_scores', ['country_code' => 'IDN', 'score_date' => now()->toDateString()]);
    }

    public function test_currency_page_uses_live_exchange_rate(): void
    {
        $this->country();
        Http::fake(['*open.er-api.com*' => Http::response([
            'result' => 'success', 'rates' => ['USD' => 1, 'IDR' => 16000],
        ])]);

        $this->actingAs(User::factory()->create())->get(route('currency.index'))
            ->assertOk()->assertSee('Live API')->assertSee('16000');

        $this->assertDatabaseHas('exchange_rates', [
            'base_currency' => 'USD',
            'currency_code' => 'IDR',
            'rate_date' => now()->toDateString(),
        ]);
    }

    public function test_ports_api_reads_paginated_world_port_index_data(): void
    {
        Port::query()->create([
            'port_name' => 'Tanjung Priok', 'country' => 'Indonesia',
            'country_code' => 'IDN', 'latitude' => -6.104, 'longitude' => 106.88,
            'source' => 'NGA World Port Index', 'external_id' => 'WPI-51000',
            'status' => 'active', 'capacity' => 'high',
            'congestion_level' => 'low', 'risk_level' => 'low',
        ]);

        $this->getJson('/api/ports?country=IDN&per_page=10')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('meta.source', 'Database ports / NGA World Port Index')
            ->assertJsonPath('data.0.external_id', 'WPI-51000');
    }

    public function test_risk_api_reads_latest_database_snapshot(): void
    {
        RiskScore::query()->create([
            'country_code' => 'IDN', 'country_name' => 'Indonesia',
            'weather_risk' => 20, 'inflation_risk' => 30,
            'currency_risk' => 10, 'news_risk' => 40, 'port_risk' => 15,
            'total_risk' => 25, 'category' => 'Low',
            'score_date' => now()->toDateString(), 'source' => 'API Eksternal',
        ]);

        $this->getJson('/api/risk?country=IDN')
            ->assertOk()
            ->assertJsonPath('meta.source', 'Database risk_scores')
            ->assertJsonPath('data.0.country_code', 'IDN');
    }

    public function test_news_api_reads_database_cache(): void
    {
        NewsCache::query()->create([
            'country_code' => 'IDN', 'country_name' => 'Indonesia',
            'title' => 'Supply chain recovery', 'sentiment' => 'Positive',
            'source_name' => 'Test News', 'published_at' => now(),
        ]);

        $this->getJson('/api/news?country=IDN&sentiment=Positive')
            ->assertOk()
            ->assertJsonPath('meta.source', 'Database news_cache')
            ->assertJsonPath('data.0.title', 'Supply chain recovery');
    }

    public function test_comparison_and_visualization_share_latest_risk_snapshot(): void
    {
        $this->country();
        RiskScore::query()->create([
            'country_code' => 'IDN', 'country_name' => 'Indonesia',
            'weather_risk' => 70, 'inflation_risk' => 72,
            'currency_risk' => 74, 'news_risk' => 76, 'port_risk' => 78,
            'total_risk' => 77, 'category' => 'Critical',
            'recommendation' => 'Perlu mitigasi segera.',
            'score_date' => now()->toDateString(), 'source' => 'API Eksternal',
        ]);
        Http::fake(['*' => Http::response([])]);
        $user = User::factory()->create();

        foreach (['comparison.index', 'visualization.index'] as $route) {
            $this->actingAs($user)->get(route($route))
                ->assertOk()
                ->assertViewHas('countries', function (array $countries) {
                    $indonesia = collect($countries)->firstWhere('code', 'IDN');

                    return $indonesia !== null
                        && (float) ($indonesia['total_risk'] ?? $indonesia['risk_score']) === 77.0
                        && $indonesia['category'] === 'Critical'
                        && $indonesia['risk_data_source'] === 'Database risk_scores';
                });
        }
    }

    public function test_dashboard_uses_database_risk_port_and_news_data(): void
    {
        $this->country();
        RiskScore::query()->create([
            'country_code' => 'IDN', 'country_name' => 'Indonesia',
            'weather_risk' => 70, 'inflation_risk' => 70,
            'currency_risk' => 70, 'news_risk' => 70, 'port_risk' => 70,
            'total_risk' => 70, 'category' => 'High',
            'score_date' => now()->toDateString(), 'source' => 'Database',
        ]);
        Port::query()->create([
            'port_name' => 'Tanjung Priok', 'country' => 'Indonesia',
            'country_code' => 'IDN', 'latitude' => -6.104, 'longitude' => 106.88,
            'status' => 'active', 'risk_level' => 'high',
        ]);
        NewsCache::query()->create([
            'country_code' => 'IDN', 'country_name' => 'Indonesia',
            'title' => 'Supply chain disruption in Indonesia',
            'description' => 'Shipping and logistics update.',
            'sentiment' => 'Negative', 'news_risk' => 80,
            'source_name' => 'Test News', 'published_at' => now(),
            'fetched_at' => now(),
        ]);

        $this->actingAs(User::factory()->create())->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Tanjung Priok')
            ->assertSee('Supply chain disruption in Indonesia')
            ->assertViewHas('summary', fn (array $summary) =>
                $summary['average_risk'] === 70.0
                && $summary['high_risk'] === 1
            )
            ->assertViewHas('mapPorts', fn (array $ports) =>
                count($ports) === 1 && $ports[0]['name'] === 'Tanjung Priok'
            );
    }

}
