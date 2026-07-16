<?php

namespace Database\Seeders;

use App\Models\SentimentWord;
use Illuminate\Database\Seeder;

class SentimentWordSeeder extends Seeder
{
    /**
     * Mengisi data awal kamus kata sentimen.
     */
    public function run(): void
    {
        $words = [
            [
                'word' => 'growth',
                'type' => 'positive',
                'category' => 'Economy',
                'weight' => 2,
                'meaning' => 'Menunjukkan pertumbuhan ekonomi atau perdagangan.',
                'status' => 'active',
            ],
            [
                'word' => 'increase',
                'type' => 'positive',
                'category' => 'Trade',
                'weight' => 1,
                'meaning' => 'Menunjukkan kenaikan aktivitas perdagangan.',
                'status' => 'active',
            ],
            [
                'word' => 'profit',
                'type' => 'positive',
                'category' => 'Business',
                'weight' => 2,
                'meaning' => 'Menunjukkan keuntungan bisnis.',
                'status' => 'active',
            ],
            [
                'word' => 'stable',
                'type' => 'positive',
                'category' => 'Currency',
                'weight' => 2,
                'meaning' => 'Menunjukkan kondisi stabil.',
                'status' => 'active',
            ],
            [
                'word' => 'improve',
                'type' => 'positive',
                'category' => 'Logistics',
                'weight' => 1,
                'meaning' => 'Menunjukkan perbaikan kondisi.',
                'status' => 'active',
            ],
            [
                'word' => 'recovery',
                'type' => 'positive',
                'category' => 'Supply Chain',
                'weight' => 2,
                'meaning' => 'Menunjukkan pemulihan rantai pasok.',
                'status' => 'active',
            ],
            [
                'word' => 'surplus',
                'type' => 'positive',
                'category' => 'Trade',
                'weight' => 1,
                'meaning' => 'Menunjukkan kelebihan atau keuntungan perdagangan.',
                'status' => 'active',
            ],
            [
                'word' => 'war',
                'type' => 'negative',
                'category' => 'Geopolitics',
                'weight' => 3,
                'meaning' => 'Menunjukkan konflik yang dapat mengganggu perdagangan.',
                'status' => 'active',
            ],
            [
                'word' => 'crisis',
                'type' => 'negative',
                'category' => 'Economy',
                'weight' => 3,
                'meaning' => 'Menunjukkan kondisi krisis ekonomi atau logistik.',
                'status' => 'active',
            ],
            [
                'word' => 'inflation',
                'type' => 'negative',
                'category' => 'Economy',
                'weight' => 2,
                'meaning' => 'Menunjukkan kenaikan harga dan biaya produksi.',
                'status' => 'active',
            ],
            [
                'word' => 'delay',
                'type' => 'negative',
                'category' => 'Logistics',
                'weight' => 2,
                'meaning' => 'Menunjukkan keterlambatan pengiriman.',
                'status' => 'active',
            ],
            [
                'word' => 'disaster',
                'type' => 'negative',
                'category' => 'Weather',
                'weight' => 3,
                'meaning' => 'Menunjukkan bencana yang mengganggu rantai pasok.',
                'status' => 'active',
            ],
            [
                'word' => 'storm',
                'type' => 'negative',
                'category' => 'Weather',
                'weight' => 2,
                'meaning' => 'Menunjukkan cuaca ekstrem.',
                'status' => 'active',
            ],
            [
                'word' => 'congestion',
                'type' => 'negative',
                'category' => 'Port',
                'weight' => 2,
                'meaning' => 'Menunjukkan kemacetan pelabuhan.',
                'status' => 'active',
            ],
            [
                'word' => 'decrease',
                'type' => 'negative',
                'category' => 'Trade',
                'weight' => 1,
                'meaning' => 'Menunjukkan penurunan aktivitas perdagangan.',
                'status' => 'active',
            ],
        ];

        foreach ($words as $word) {
            SentimentWord::query()->updateOrCreate(
                ['word' => $word['word']],
                $word
            );
        }
    }
}