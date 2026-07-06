<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class ApiLogController extends Controller
{
    public function index()
    {
        $apiLogs = [
            [
                'id' => 1,
                'api_name' => 'REST Countries API',
                'endpoint' => '/v3.1/all',
                'method' => 'GET',
                'feature' => 'Global Country Dashboard',
                'status' => 'Success',
                'status_code' => 200,
                'response_time' => 842,
                'requested_at' => '03 Jul 2026 09:15',
                'description' => 'Mengambil data negara, wilayah, mata uang, populasi, dan koordinat.',
            ],
            [
                'id' => 2,
                'api_name' => 'Open-Meteo API',
                'endpoint' => '/v1/forecast',
                'method' => 'GET',
                'feature' => 'Weather Monitoring',
                'status' => 'Success',
                'status_code' => 200,
                'response_time' => 610,
                'requested_at' => '03 Jul 2026 09:20',
                'description' => 'Mengambil data cuaca global berdasarkan latitude dan longitude negara.',
            ],
            [
                'id' => 3,
                'api_name' => 'ExchangeRate API',
                'endpoint' => '/v6/latest/USD',
                'method' => 'GET',
                'feature' => 'Currency Impact',
                'status' => 'Success',
                'status_code' => 200,
                'response_time' => 735,
                'requested_at' => '03 Jul 2026 09:25',
                'description' => 'Mengambil data nilai tukar mata uang untuk analisis risiko kurs.',
            ],
            [
                'id' => 4,
                'api_name' => 'GNews API',
                'endpoint' => '/api/v4/search',
                'method' => 'GET',
                'feature' => 'News Intelligence',
                'status' => 'Success',
                'status_code' => 200,
                'response_time' => 920,
                'requested_at' => '03 Jul 2026 09:30',
                'description' => 'Mengambil berita ekonomi, logistik, perdagangan, dan geopolitik.',
            ],
            [
                'id' => 5,
                'api_name' => 'World Bank API',
                'endpoint' => '/country/indicator',
                'method' => 'GET',
                'feature' => 'Data Visualization',
                'status' => 'Success',
                'status_code' => 200,
                'response_time' => 1050,
                'requested_at' => '03 Jul 2026 09:35',
                'description' => 'Mengambil indikator ekonomi seperti GDP, inflasi, populasi, ekspor, dan impor.',
            ],
            [
                'id' => 6,
                'api_name' => 'World Port Index Dataset',
                'endpoint' => '/ports-dataset',
                'method' => 'GET',
                'feature' => 'Port Location Dashboard',
                'status' => 'Success',
                'status_code' => 200,
                'response_time' => 480,
                'requested_at' => '03 Jul 2026 09:40',
                'description' => 'Mengambil dataset pelabuhan dunia untuk analisis logistik.',
            ],
            [
                'id' => 7,
                'api_name' => 'OpenStreetMap / Leaflet',
                'endpoint' => '/map-tiles',
                'method' => 'GET',
                'feature' => 'Geospatial Visualization',
                'status' => 'Success',
                'status_code' => 200,
                'response_time' => 390,
                'requested_at' => '03 Jul 2026 09:45',
                'description' => 'Menampilkan peta interaktif untuk lokasi negara dan pelabuhan.',
            ],
            [
                'id' => 8,
                'api_name' => 'GNews API',
                'endpoint' => '/api/v4/search',
                'method' => 'GET',
                'feature' => 'News Intelligence',
                'status' => 'Failed',
                'status_code' => 429,
                'response_time' => 0,
                'requested_at' => '03 Jul 2026 10:00',
                'description' => 'Request gagal karena limit API, sistem menggunakan data cache/simulasi.',
            ],
        ];

        $summary = [
            'total_logs' => count($apiLogs),
            'success_logs' => collect($apiLogs)->where('status', 'Success')->count(),
            'failed_logs' => collect($apiLogs)->where('status', 'Failed')->count(),
            'external_apis' => collect($apiLogs)->pluck('api_name')->unique()->count(),
            'average_response_time' => round(
                collect($apiLogs)
                    ->where('response_time', '>', 0)
                    ->avg('response_time'),
                2
            ),
        ];

        return view('admin.api_logs.index', compact('apiLogs', 'summary'));
    }
}