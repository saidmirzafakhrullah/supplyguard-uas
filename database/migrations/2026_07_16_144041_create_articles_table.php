<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel artikel analisis.
     */
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->string('category', 50)->default('supply_chain');
            $table->text('summary')->nullable();
            $table->longText('content');

            $table->string('source')->nullable();
            $table->string('author')->nullable();

            $table->string('status', 20)->default('draft');
            $table->string('sentiment', 20)->default('neutral');
            $table->string('risk_level', 20)->default('medium');

            $table->timestamp('published_at')->nullable();

            $table->timestamps();

            $table->index('category');
            $table->index('status');
            $table->index('sentiment');
            $table->index('risk_level');
        });
    }

    /**
     * Menghapus tabel artikel analisis.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};