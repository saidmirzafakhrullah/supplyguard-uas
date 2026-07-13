<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel dataset pelabuhan.
     */
    public function up(): void
    {
        Schema::create('ports', function (Blueprint $table) {
            $table->id();

            $table->string('port_name');
            $table->string('country');
            $table->string('country_code', 3);
            $table->string('city')->nullable();
            $table->string('region')->nullable();

            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);

            $table->string('status', 20)
                ->default('active');

            $table->string('capacity', 20)
                ->default('medium');

            $table->string('congestion_level', 20)
                ->default('medium');

            $table->string('risk_level', 20)
                ->default('low');

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('country_code');
            $table->index('status');
            $table->index('risk_level');
        });
    }

    /**
     * Menghapus tabel dataset pelabuhan.
     */
    public function down(): void
    {
        Schema::dropIfExists('ports');
    }
};