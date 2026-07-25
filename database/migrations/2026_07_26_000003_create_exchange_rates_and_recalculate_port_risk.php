<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->string('base_currency', 10)->default('USD');
            $table->string('currency_code', 10);
            $table->decimal('rate', 20, 8);
            $table->date('rate_date');
            $table->string('source')->default('Exchange Rate API');
            $table->timestamps();
            $table->unique(['base_currency', 'currency_code', 'rate_date']);
            $table->index('rate_date');
        });

        DB::table('ports')
            ->where('source', 'NGA World Port Index')
            ->update([
                'risk_level' => DB::raw("CASE
                    WHEN harbor_size IN ('Large', 'Medium') THEN 'low'
                    WHEN harbor_size IN ('Small', 'Very Small') THEN 'medium'
                    ELSE 'medium' END"),
            ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
