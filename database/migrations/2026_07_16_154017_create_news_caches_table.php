<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_cache', function (Blueprint $table) {
            $table->id();
            $table->string('country_code', 3)->nullable()->index();
            $table->string('country_name')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('url')->nullable();
            $table->string('source_name')->nullable();
            $table->string('category')->default('Logistics');
            $table->string('sentiment')->default('Neutral');
            $table->unsignedInteger('positive_words')->default(0);
            $table->unsignedInteger('negative_words')->default(0);
            $table->unsignedTinyInteger('news_risk')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('fetched_at')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_cache');
    }
};