<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiLog;

class ApiLogController extends Controller
{
    /**
     * Menampilkan riwayat penggunaan API eksternal.
     */
    public function index()
    {
        $apiLogRecords = ApiLog::query()
            ->orderByDesc('requested_at')
            ->orderByDesc('id')
            ->get();

        $apiLogs = $apiLogRecords
            ->map(function (ApiLog $log) {
                return [
                    'id' => $log->id,
                    'api_name' => $log->api_name,
                    'endpoint' => $log->endpoint,
                    'method' => $log->method,
                    'feature' => $log->feature,
                    'status' => $log->status,
                    'status_code' => $log->status_code,
                    'response_time' => $log->response_time,

                    'requested_at' => $log->requested_at
                        ? $log->requested_at->format('d M Y H:i')
                        : '-',

                    'description' => $log->description
                        ?? 'Tidak ada keterangan.',

                    'error_message' => $log->error_message,
                ];
            })
            ->values()
            ->all();

        $averageResponseTime = $apiLogRecords
            ->where('response_time', '>', 0)
            ->avg('response_time');

        /*
         * Jumlah API eksternal backend yang digunakan SupplyGuard.
         *
         * Angka ini dibuat tetap 5 karena project memang memakai
         * 5 API eksternal pada sisi backend Laravel:
         * 1. REST Countries API v5
         * 2. Open-Meteo API
         * 3. World Bank API
         * 4. Exchange Rate API
         * 5. GNews API
         *
         * Tabel log di bawah tetap menampilkan riwayat API
         * yang benar-benar tercatat setelah cache/log terakhir.
         */
        $externalApiNames = [
            'REST Countries API v5',
            'Open-Meteo API',
            'World Bank API',
            'Exchange Rate API',
            'GNews API',
        ];

        $summary = [
            'total_logs' => $apiLogRecords->count(),

            'success_logs' => $apiLogRecords
                ->where('status', 'Success')
                ->count(),

            'failed_logs' => $apiLogRecords
                ->where('status', 'Failed')
                ->count(),

            'external_apis' => count($externalApiNames),

            'average_response_time' => $averageResponseTime !== null
                ? round($averageResponseTime, 2)
                : 0,
        ];

        return view(
            'admin.api_logs.index',
            compact('apiLogs', 'summary', 'externalApiNames')
        );
    }
}