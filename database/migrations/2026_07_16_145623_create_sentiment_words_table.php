<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel kamus kata sentimen.
     */
    public function up(): void
    {
        Schema::create('sentiment_words', function (Blueprint $table) {
            $table->id();

            $table->string('word')->unique();

            /*
             * positive = kata positif
             * negative = kata negatif
             */
            $table->string('type', 20);

            $table->string('category', 100)->nullable();

            /*
             * Bobot kata untuk analisis sentimen.
             * Semakin besar bobot, semakin kuat pengaruhnya.
             */
            $table->unsignedTinyInteger('weight')
                ->default(1);

            $table->text('meaning')->nullable();

            $table->string('status', 20)
                ->default('active');

            $table->timestamps();

            $table->index('type');
            $table->index('status');
            $table->index('category');
        });
    }

    /**
     * Menghapus tabel kamus kata sentimen.
     */
    public function down(): void
    {
        Schema::dropIfExists('sentiment_words');
    }
};