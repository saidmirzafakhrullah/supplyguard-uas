<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class WordController extends Controller
{
    public function index()
    {
        $positiveWords = [
            [
                'id' => 1,
                'word' => 'growth',
                'category' => 'Economy',
                'weight' => 2,
                'meaning' => 'Menunjukkan pertumbuhan ekonomi atau perdagangan.',
                'status' => 'Active',
            ],
            [
                'id' => 2,
                'word' => 'increase',
                'category' => 'Trade',
                'weight' => 1,
                'meaning' => 'Menunjukkan kenaikan aktivitas perdagangan.',
                'status' => 'Active',
            ],
            [
                'id' => 3,
                'word' => 'profit',
                'category' => 'Business',
                'weight' => 2,
                'meaning' => 'Menunjukkan keuntungan bisnis.',
                'status' => 'Active',
            ],
            [
                'id' => 4,
                'word' => 'stable',
                'category' => 'Currency',
                'weight' => 2,
                'meaning' => 'Menunjukkan kondisi stabil.',
                'status' => 'Active',
            ],
            [
                'id' => 5,
                'word' => 'improve',
                'category' => 'Logistics',
                'weight' => 1,
                'meaning' => 'Menunjukkan perbaikan kondisi.',
                'status' => 'Active',
            ],
            [
                'id' => 6,
                'word' => 'recovery',
                'category' => 'Supply Chain',
                'weight' => 2,
                'meaning' => 'Menunjukkan pemulihan rantai pasok.',
                'status' => 'Active',
            ],
            [
                'id' => 7,
                'word' => 'surplus',
                'category' => 'Trade',
                'weight' => 1,
                'meaning' => 'Menunjukkan kelebihan atau keuntungan perdagangan.',
                'status' => 'Active',
            ],
        ];

        $negativeWords = [
            [
                'id' => 1,
                'word' => 'war',
                'category' => 'Geopolitics',
                'weight' => 3,
                'meaning' => 'Menunjukkan konflik yang dapat mengganggu perdagangan.',
                'status' => 'Active',
            ],
            [
                'id' => 2,
                'word' => 'crisis',
                'category' => 'Economy',
                'weight' => 3,
                'meaning' => 'Menunjukkan kondisi krisis ekonomi atau logistik.',
                'status' => 'Active',
            ],
            [
                'id' => 3,
                'word' => 'inflation',
                'category' => 'Economy',
                'weight' => 2,
                'meaning' => 'Menunjukkan kenaikan harga dan biaya produksi.',
                'status' => 'Active',
            ],
            [
                'id' => 4,
                'word' => 'delay',
                'category' => 'Logistics',
                'weight' => 2,
                'meaning' => 'Menunjukkan keterlambatan pengiriman.',
                'status' => 'Active',
            ],
            [
                'id' => 5,
                'word' => 'disaster',
                'category' => 'Weather',
                'weight' => 3,
                'meaning' => 'Menunjukkan bencana yang mengganggu rantai pasok.',
                'status' => 'Active',
            ],
            [
                'id' => 6,
                'word' => 'storm',
                'category' => 'Weather',
                'weight' => 2,
                'meaning' => 'Menunjukkan cuaca ekstrem.',
                'status' => 'Active',
            ],
            [
                'id' => 7,
                'word' => 'congestion',
                'category' => 'Port',
                'weight' => 2,
                'meaning' => 'Menunjukkan kemacetan pelabuhan.',
                'status' => 'Active',
            ],
            [
                'id' => 8,
                'word' => 'decrease',
                'category' => 'Trade',
                'weight' => 1,
                'meaning' => 'Menunjukkan penurunan aktivitas perdagangan.',
                'status' => 'Active',
            ],
        ];

        $sampleText = 'Inflation increase while exports decrease due to war and port delay.';

        $analysis = $this->analyzeSentiment(
            $sampleText,
            $positiveWords,
            $negativeWords
        );

        $summary = [
            'total_words' => count($positiveWords) + count($negativeWords),
            'positive_words' => count($positiveWords),
            'negative_words' => count($negativeWords),
            'positive_score' => $analysis['positive_score'],
            'negative_score' => $analysis['negative_score'],
            'sentiment' => $analysis['sentiment'],
        ];

        return view('admin.sentiment_words.index', compact(
            'positiveWords',
            'negativeWords',
            'sampleText',
            'analysis',
            'summary'
        ));
    }

    private function analyzeSentiment($text, $positiveWords, $negativeWords)
    {
        $cleanText = strtolower($text);
        $cleanText = preg_replace('/[^a-zA-Z\s]/', '', $cleanText);
        $words = explode(' ', $cleanText);

        $positiveDictionary = collect($positiveWords)->pluck('word')->toArray();
        $negativeDictionary = collect($negativeWords)->pluck('word')->toArray();

        $positiveMatches = [];
        $negativeMatches = [];

        $positiveScore = 0;
        $negativeScore = 0;

        foreach ($words as $word) {
            if (in_array($word, $positiveDictionary)) {
                $positiveMatches[] = $word;
                $positiveScore++;
            }

            if (in_array($word, $negativeDictionary)) {
                $negativeMatches[] = $word;
                $negativeScore++;
            }
        }

        $sentiment = 'Neutral';

        if ($positiveScore > $negativeScore) {
            $sentiment = 'Positive';
        } elseif ($negativeScore > $positiveScore) {
            $sentiment = 'Negative';
        }

        return [
            'positive_score' => $positiveScore,
            'negative_score' => $negativeScore,
            'positive_matches' => $positiveMatches,
            'negative_matches' => $negativeMatches,
            'sentiment' => $sentiment,
        ];
    }
}