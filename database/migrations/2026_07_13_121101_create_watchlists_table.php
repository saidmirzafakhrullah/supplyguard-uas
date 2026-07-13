<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel daftar pemantauan favorit.
     */
    public function up(): void
    {
        Schema::create('watchlists', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('country_code', 3);
            $table->string('country_name');

            $table->timestamps();

            /*
             * Satu pengguna tidak boleh menambahkan
             * negara yang sama lebih dari satu kali.
             */
            $table->unique([
                'user_id',
                'country_code',
            ]);
        });
    }

    /**
     * Menghapus tabel daftar pemantauan favorit.
     */
    public function down(): void
    {
        Schema::dropIfExists('watchlists');
    }
};