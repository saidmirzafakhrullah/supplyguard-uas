<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->string('alpha2_code', 2)->nullable()->unique()->after('code');
        });

        Schema::table('ports', function (Blueprint $table) {
            $table->string('source')->default('Manual Admin')->after('notes')->index();
            $table->string('external_id')->nullable()->unique()->after('source');
            $table->unsignedInteger('wpi_number')->nullable()->index()->after('external_id');
            $table->string('unlocode', 20)->nullable()->index()->after('wpi_number');
            $table->string('harbor_size', 20)->nullable()->after('unlocode');
            $table->string('harbor_type', 50)->nullable()->after('harbor_size');
            $table->string('harbor_use', 30)->nullable()->after('harbor_type');
        });
    }

    public function down(): void
    {
        Schema::table('ports', function (Blueprint $table) {
            $table->dropColumn([
                'source', 'external_id', 'wpi_number', 'unlocode',
                'harbor_size', 'harbor_type', 'harbor_use',
            ]);
        });

        Schema::table('countries', function (Blueprint $table) {
            $table->dropUnique(['alpha2_code']);
            $table->dropColumn('alpha2_code');
        });
    }
};
