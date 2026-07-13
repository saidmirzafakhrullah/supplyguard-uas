<?php

namespace Database\Seeders;

use App\Models\Port;
use Illuminate\Database\Seeder;

class PortSeeder extends Seeder
{
    /**
     * Mengisi data awal pelabuhan utama dunia.
     */
    public function run(): void
    {
        $ports = [
            [
                'port_name' => 'Pelabuhan Tanjung Priok',
                'country' => 'Indonesia',
                'country_code' => 'IDN',
                'city' => 'Jakarta',
                'region' => 'Asia',
                'latitude' => -6.1045000,
                'longitude' => 106.8860000,
                'status' => 'active',
                'capacity' => 'high',
                'congestion_level' => 'medium',
                'risk_level' => 'low',
                'notes' => 'Pelabuhan utama Indonesia untuk aktivitas ekspor dan impor.',
            ],
            [
                'port_name' => 'Port of Shanghai',
                'country' => 'China',
                'country_code' => 'CHN',
                'city' => 'Shanghai',
                'region' => 'Asia',
                'latitude' => 31.2304000,
                'longitude' => 121.4737000,
                'status' => 'active',
                'capacity' => 'high',
                'congestion_level' => 'high',
                'risk_level' => 'medium',
                'notes' => 'Salah satu pelabuhan peti kemas terbesar di dunia.',
            ],
            [
                'port_name' => 'Port of Hamburg',
                'country' => 'Germany',
                'country_code' => 'DEU',
                'city' => 'Hamburg',
                'region' => 'Europe',
                'latitude' => 53.5511000,
                'longitude' => 9.9937000,
                'status' => 'active',
                'capacity' => 'high',
                'congestion_level' => 'medium',
                'risk_level' => 'low',
                'notes' => 'Pelabuhan utama perdagangan dan logistik Jerman.',
            ],
            [
                'port_name' => 'Port of Singapore',
                'country' => 'Singapore',
                'country_code' => 'SGP',
                'city' => 'Singapore',
                'region' => 'Asia',
                'latitude' => 1.2644000,
                'longitude' => 103.8200000,
                'status' => 'active',
                'capacity' => 'high',
                'congestion_level' => 'medium',
                'risk_level' => 'low',
                'notes' => 'Pelabuhan transit utama dalam perdagangan internasional.',
            ],
            [
                'port_name' => 'Port of Yokohama',
                'country' => 'Japan',
                'country_code' => 'JPN',
                'city' => 'Yokohama',
                'region' => 'Asia',
                'latitude' => 35.4437000,
                'longitude' => 139.6380000,
                'status' => 'active',
                'capacity' => 'high',
                'congestion_level' => 'low',
                'risk_level' => 'low',
                'notes' => 'Pelabuhan utama Jepang untuk perdagangan internasional.',
            ],
            [
                'port_name' => 'Port Botany',
                'country' => 'Australia',
                'country_code' => 'AUS',
                'city' => 'Sydney',
                'region' => 'Oceania',
                'latitude' => -33.9608000,
                'longitude' => 151.2250000,
                'status' => 'active',
                'capacity' => 'high',
                'congestion_level' => 'medium',
                'risk_level' => 'low',
                'notes' => 'Pelabuhan peti kemas utama di Sydney.',
            ],
            [
                'port_name' => 'Port of Los Angeles',
                'country' => 'United States',
                'country_code' => 'USA',
                'city' => 'Los Angeles',
                'region' => 'Americas',
                'latitude' => 33.7405000,
                'longitude' => -118.2775000,
                'status' => 'active',
                'capacity' => 'high',
                'congestion_level' => 'high',
                'risk_level' => 'medium',
                'notes' => 'Pelabuhan perdagangan internasional utama Amerika Serikat.',
            ],
            [
                'port_name' => 'Port of Felixstowe',
                'country' => 'United Kingdom',
                'country_code' => 'GBR',
                'city' => 'Felixstowe',
                'region' => 'Europe',
                'latitude' => 51.9542000,
                'longitude' => 1.3511000,
                'status' => 'active',
                'capacity' => 'high',
                'congestion_level' => 'medium',
                'risk_level' => 'low',
                'notes' => 'Pelabuhan peti kemas utama di Inggris.',
            ],
            [
                'port_name' => 'Port Klang',
                'country' => 'Malaysia',
                'country_code' => 'MYS',
                'city' => 'Selangor',
                'region' => 'Asia',
                'latitude' => 3.0000000,
                'longitude' => 101.4000000,
                'status' => 'active',
                'capacity' => 'high',
                'congestion_level' => 'medium',
                'risk_level' => 'low',
                'notes' => 'Pelabuhan utama Malaysia untuk perdagangan regional.',
            ],
            [
                'port_name' => 'Laem Chabang Port',
                'country' => 'Thailand',
                'country_code' => 'THA',
                'city' => 'Chonburi',
                'region' => 'Asia',
                'latitude' => 13.0827000,
                'longitude' => 100.8830000,
                'status' => 'active',
                'capacity' => 'high',
                'congestion_level' => 'medium',
                'risk_level' => 'low',
                'notes' => 'Pelabuhan utama Thailand untuk ekspor dan impor.',
            ],
        ];

        foreach ($ports as $port) {
            Port::query()->updateOrCreate(
                [
                    'port_name' => $port['port_name'],
                    'country_code' => $port['country_code'],
                ],
                $port
            );
        }
    }
}