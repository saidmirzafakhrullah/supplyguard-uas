<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminCountryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_and_delete_country(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.countries.store'), [
            'name' => 'Indonesia', 'official_name' => 'Republik Indonesia',
            'code' => 'idn', 'capital' => 'Jakarta', 'region' => 'Asia',
            'population' => 277000000, 'currency_code' => 'idr',
            'currency_name' => 'Rupiah', 'latitude' => -5, 'longitude' => 120,
            'landlocked' => 0,
        ])->assertRedirect(route('admin.countries.index'));

        $country = Country::query()->where('code', 'IDN')->firstOrFail();
        $this->assertSame('Manual Admin', $country->source);

        $this->actingAs($admin)->put(route('admin.countries.update', $country), [
            'name' => 'Indonesia', 'code' => 'IDN', 'capital' => 'Nusantara',
            'region' => 'Asia', 'population' => 278000000, 'landlocked' => 0,
        ])->assertRedirect(route('admin.countries.index'));

        $this->assertDatabaseHas('countries', ['code' => 'IDN', 'capital' => 'Nusantara']);

        $this->actingAs($admin)->delete(route('admin.countries.destroy', $country))
            ->assertRedirect(route('admin.countries.index'));
        $this->assertDatabaseMissing('countries', ['code' => 'IDN']);
    }

    public function test_regular_user_cannot_manage_countries(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user)->get(route('admin.countries.index'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_admin_can_sync_countries_from_version_five_api(): void
    {
        config([
            'services.rest_countries.key' => 'test-key',
            'services.rest_countries.base_url' => 'https://api.restcountries.test/countries/v5',
        ]);

        Http::fake([
            'api.restcountries.test/*' => Http::response([
                'data' => [
                    'objects' => [[
                        'names' => [
                            'common' => 'Indonesia',
                            'official' => 'Republic of Indonesia',
                        ],
                        'codes' => ['alpha_2' => 'ID', 'alpha_3' => 'IDN'],
                        'capitals' => [['name' => 'Jakarta']],
                        'region' => 'Asia',
                        'subregion' => 'South-Eastern Asia',
                        'population' => 277000000,
                        'currencies' => [[
                            'code' => 'IDR',
                            'name' => 'Indonesian Rupiah',
                        ]],
                        'languages' => [
                            ['code' => 'id', 'name' => 'Indonesian'],
                            ['code' => 'en', 'name' => 'English'],
                        ],
                        'coordinates' => ['lat' => -5, 'lng' => 120],
                        'flag' => ['url_png' => 'https://example.test/id.png'],
                        'landlocked' => false,
                    ]],
                    'meta' => ['more' => false],
                ],
            ]),
        ]);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->post(route('admin.countries.sync'))
            ->assertRedirect(route('admin.countries.index'))
            ->assertSessionHas('success', 'Sinkronisasi selesai: 1 negara tersimpan.');

        $this->assertDatabaseHas('countries', [
            'code' => 'IDN',
            'alpha2_code' => 'ID',
            'currency_code' => 'IDR',
            'languages' => 'Indonesian, English',
            'source' => 'REST Countries API v5',
        ]);
    }
}
