<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\User;
use Database\Seeders\PortSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminPortSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_sync_world_port_index(): void
    {
        Country::query()->create([
            'name' => 'Indonesia', 'code' => 'IDN', 'alpha2_code' => 'ID',
            'population' => 277000000, 'region' => 'Asia',
        ]);
        $admin = User::factory()->create(['role' => 'admin']);

        Http::fake(['*World_Port_Index_Viewer*' => Http::response([
            'features' => [[
                'attributes' => [
                    'objectid' => 10, 'wpinumber' => 51000,
                    'main_port_name' => 'Tanjung Priok',
                    'harbor_size_code' => 'L', 'harbor_type_code' => 'CB',
                    'harbor_use_code' => 'Cargo', 'wpi_cc' => 'ID',
                    'unlocode' => 'ID TPP',
                ],
                'geometry' => ['x' => 106.88, 'y' => -6.104],
            ]],
            'exceededTransferLimit' => false,
        ])]);

        $this->actingAs($admin)->post(route('admin.ports.sync-wpi'))
            ->assertRedirect(route('admin.ports.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('ports', [
            'external_id' => 'WPI-51000', 'country_code' => 'IDN',
            'port_name' => 'Tanjung Priok', 'source' => 'NGA World Port Index',
            'capacity' => 'high', 'unlocode' => 'ID TPP',
        ]);

        Http::assertSent(function ($request) {
            return $request->method() === 'GET'
                && (int) $request['resultRecordCount'] === 200
                && (int) $request['resultOffset'] === 0;
        });
    }

    public function test_port_seeder_does_not_delete_existing_wpi_data(): void
    {
        \App\Models\Port::query()->create([
            'port_name' => 'Official Port', 'country' => 'Indonesia',
            'country_code' => 'IDN', 'latitude' => -6, 'longitude' => 106,
            'source' => 'NGA World Port Index', 'external_id' => 'WPI-SAFE',
        ]);

        $this->seed(PortSeeder::class);

        $this->assertDatabaseHas('ports', [
            'external_id' => 'WPI-SAFE',
            'source' => 'NGA World Port Index',
        ]);
        $this->assertDatabaseCount('ports', 1);
    }
}
