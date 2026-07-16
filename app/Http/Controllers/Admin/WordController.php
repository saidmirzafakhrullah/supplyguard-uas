<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SentimentWord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WordController extends Controller
{
    /**
     * Menampilkan halaman kamus kata sentimen.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $words = SentimentWord::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('word', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%")
                        ->orWhere('meaning', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%");
                });
            })
            ->orderBy('type')
            ->orderBy('word')
            ->paginate(12)
            ->withQueryString();

        $positiveWords = SentimentWord::query()
            ->where('type', 'positive')
            ->where('status', 'active')
            ->get();

        $negativeWords = SentimentWord::query()
            ->where('type', 'negative')
            ->where('status', 'active')
            ->get();

        $sampleText = 'Inflation increase while exports decrease due to war and port delay.';

        $analysis = $this->analyzeSentiment(
            $sampleText,
            $positiveWords,
            $negativeWords
        );

        $summary = [
            'total_words' => SentimentWord::query()->count(),

            'positive_words' => SentimentWord::query()
                ->where('type', 'positive')
                ->count(),

            'negative_words' => SentimentWord::query()
                ->where('type', 'negative')
                ->count(),

            'active_words' => SentimentWord::query()
                ->where('status', 'active')
                ->count(),

            'positive_score' => $analysis['positive_score'],

            'negative_score' => $analysis['negative_score'],

            'sentiment' => $analysis['sentiment'],
        ];

        return view(
            'admin.sentiment_words.index',
            compact(
                'words',
                'summary',
                'sampleText',
                'analysis',
                'search'
            )
        );
    }

    /**
     * Menambahkan kata sentimen.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateWord($request);

        $data['word'] = strtolower(trim($data['word']));

        SentimentWord::query()->create($data);

        return redirect()
            ->route('admin.words.index')
            ->with(
                'success',
                'Kata sentimen berhasil ditambahkan.'
            );
    }

    /**
     * Memperbarui kata sentimen.
     */
    public function update(
        Request $request,
        SentimentWord $word
    ): RedirectResponse {
        $data = $this->validateWord(
            $request,
            $word->id
        );

        $data['word'] = strtolower(trim($data['word']));

        $word->update($data);

        return redirect()
            ->route('admin.words.index')
            ->with(
                'success',
                'Kata sentimen berhasil diperbarui.'
            );
    }

    /**
     * Menghapus kata sentimen.
     */
    public function destroy(SentimentWord $word): RedirectResponse
    {
        $deletedWord = $word->word;

        $word->delete();

        return redirect()
            ->route('admin.words.index')
            ->with(
                'success',
                'Kata "' . $deletedWord . '" berhasil dihapus.'
            );
    }

    /**
     * Validasi data kata sentimen.
     */
    private function validateWord(
        Request $request,
        ?int $ignoreId = null
    ): array {
        return $request->validate([
            'word' => [
                'required',
                'string',
                'max:100',
                Rule::unique('sentiment_words', 'word')
                    ->ignore($ignoreId),
            ],

            'type' => [
                'required',
                Rule::in([
                    'positive',
                    'negative',
                ]),
            ],

            'category' => [
                'nullable',
                'string',
                'max:100',
            ],

            'weight' => [
                'required',
                'integer',
                'min:1',
                'max:5',
            ],

            'meaning' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'status' => [
                'required',
                Rule::in([
                    'active',
                    'inactive',
                ]),
            ],
        ], [
            'word.required' => 'Kata wajib diisi.',
            'word.unique' => 'Kata tersebut sudah ada dalam kamus.',
            'type.required' => 'Jenis kata wajib dipilih.',
            'weight.required' => 'Bobot kata wajib diisi.',
            'weight.min' => 'Bobot minimal 1.',
            'weight.max' => 'Bobot maksimal 5.',
        ]);
    }

    /**
     * Analisis sentimen sederhana berbasis kamus.
     */
    private function analyzeSentiment(
        string $text,
        $positiveWords,
        $negativeWords
    ): array {
        $cleanText = strtolower($text);
        $cleanText = preg_replace('/[^a-zA-Z\s]/', '', $cleanText);
        $textWords = array_filter(explode(' ', $cleanText));

        $positiveDictionary = $positiveWords
            ->keyBy('word');

        $negativeDictionary = $negativeWords
            ->keyBy('word');

        $positiveMatches = [];
        $negativeMatches = [];

        $positiveScore = 0;
        $negativeScore = 0;

        foreach ($textWords as $textWord) {
            if ($positiveDictionary->has($textWord)) {
                $item = $positiveDictionary->get($textWord);

                $positiveMatches[] = $textWord;
                $positiveScore += (int) $item->weight;
            }

            if ($negativeDictionary->has($textWord)) {
                $item = $negativeDictionary->get($textWord);

                $negativeMatches[] = $textWord;
                $negativeScore += (int) $item->weight;
            }
        }

        $sentiment = 'Neutral';

        if ($positiveScore > $negativeScore) {
            $sentiment = 'Positive';
        }

        if ($negativeScore > $positiveScore) {
            $sentiment = 'Negative';
        }

        return [
            'positive_score' => $positiveScore,
            'negative_score' => $negativeScore,
            'positive_matches' => array_values(array_unique($positiveMatches)),
            'negative_matches' => array_values(array_unique($negativeMatches)),
            'sentiment' => $sentiment,
        ];
    }
}