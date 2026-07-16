<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('risk_scores', function (Blueprint $table) {
            $table->id();
            $table->string('country_code', 3)->index();
            $table->string('country_name');
            $table->unsignedTinyInteger('weather_risk')->default(0);
            $table->unsignedTinyInteger('inflation_risk')->default(0);
            $table->unsignedTinyInteger('currency_risk')->default(0);
            $table->unsignedTinyInteger('news_risk')->default(0);
            $table->unsignedTinyInteger('port_risk')->default(0);
            $table->decimal('total_risk', 5, 2)->default(0);
            $table->string('category')->default('Low');
            $table->text('recommendation')->nullable();
            $table->date('score_date')->index();
            $table->string('source')->default('Weighted Risk Model');
            $table->timestamps();

            $table->unique(['country_code', 'score_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risk_scores');
    }
};