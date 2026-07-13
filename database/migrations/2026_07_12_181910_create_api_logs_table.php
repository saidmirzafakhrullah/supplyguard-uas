<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menjalankan migration.
     */
    public function up(): void
    {
        Schema::create('api_logs', function (Blueprint $table) {
            $table->id();

            $table->string('api_name');
            $table->text('endpoint');
            $table->string('method', 10)->default('GET');
            $table->string('feature');

            $table->string('status', 20);
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->unsignedInteger('response_time')->default(0);

            $table->text('description')->nullable();
            $table->text('error_message')->nullable();

            $table->timestamp('requested_at')->nullable();

            $table->timestamps();

            $table->index('api_name');
            $table->index('feature');
            $table->index('status');
            $table->index('requested_at');
        });
    }

    /**
     * Membatalkan migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_logs');
    }
};