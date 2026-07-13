<?php

namespace App\Services;

use App\Models\ApiLog;
use Throwable;

class ApiLogService
{
    /**
     * Menyimpan riwayat pemanggilan API ke database.
     */
    public static function record(
        string $apiName,
        string $endpoint,
        string $feature,
        string $status,
        ?int $statusCode,
        int $responseTime,
        ?string $description = null,
        ?string $errorMessage = null,
        string $method = 'GET'
    ): void {
        try {
            ApiLog::create([
                'api_name' => $apiName,
                'endpoint' => $endpoint,
                'method' => $method,
                'feature' => $feature,
                'status' => $status,
                'status_code' => $statusCode,
                'response_time' => max(0, $responseTime),
                'description' => $description,
                'error_message' => $errorMessage,
                'requested_at' => now(),
            ]);
        } catch (Throwable $exception) {
            /*
             * Kegagalan menyimpan log tidak boleh membuat
             * fitur utama SupplyGuard ikut berhenti.
             */
            report($exception);
        }
    }

    /**
     * Menyimpan log API berhasil.
     */
    public static function success(
        string $apiName,
        string $endpoint,
        string $feature,
        int $statusCode,
        int $responseTime,
        ?string $description = null,
        string $method = 'GET'
    ): void {
        self::record(
            apiName: $apiName,
            endpoint: $endpoint,
            feature: $feature,
            status: 'Success',
            statusCode: $statusCode,
            responseTime: $responseTime,
            description: $description,
            errorMessage: null,
            method: $method
        );
    }

    /**
     * Menyimpan log API gagal.
     */
    public static function failed(
        string $apiName,
        string $endpoint,
        string $feature,
        ?int $statusCode,
        int $responseTime,
        ?string $description = null,
        ?string $errorMessage = null,
        string $method = 'GET'
    ): void {
        self::record(
            apiName: $apiName,
            endpoint: $endpoint,
            feature: $feature,
            status: 'Failed',
            statusCode: $statusCode,
            responseTime: $responseTime,
            description: $description,
            errorMessage: $errorMessage,
            method: $method
        );
    }
}