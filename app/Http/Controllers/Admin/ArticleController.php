<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ArticleController extends Controller
{
    /**
     * Menampilkan halaman manajemen artikel.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $articles = Article::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%")
                        ->orWhere('summary', 'like', "%{$search}%")
                        ->orWhere('source', 'like', "%{$search}%")
                        ->orWhere('author', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhere('sentiment', 'like', "%{$search}%")
                        ->orWhere('risk_level', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $summary = [
            'total_articles' => Article::query()->count(),

            'published_articles' => Article::query()
                ->where('status', 'published')
                ->count(),

            'draft_articles' => Article::query()
                ->where('status', 'draft')
                ->count(),

            'high_risk_articles' => Article::query()
                ->whereIn('risk_level', ['high', 'critical'])
                ->count(),
        ];

        return view(
            'admin.articles.index',
            compact(
                'articles',
                'summary',
                'search'
            )
        );
    }

    /**
     * Menyimpan artikel baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateArticle($request);

        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        if ($data['status'] === 'draft') {
            $data['published_at'] = null;
        }

        Article::query()->create($data);

        return redirect()
            ->route('admin.articles.index')
            ->with(
                'success',
                'Artikel analisis berhasil ditambahkan.'
            );
    }

    /**
     * Memperbarui artikel.
     */
    public function update(
        Request $request,
        Article $article
    ): RedirectResponse {
        $data = $this->validateArticle($request);

        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        if ($data['status'] === 'draft') {
            $data['published_at'] = null;
        }

        $article->update($data);

        return redirect()
            ->route('admin.articles.index')
            ->with(
                'success',
                'Artikel analisis berhasil diperbarui.'
            );
    }

    /**
     * Menghapus artikel.
     */
    public function destroy(Article $article): RedirectResponse
    {
        $title = $article->title;

        $article->delete();

        return redirect()
            ->route('admin.articles.index')
            ->with(
                'success',
                'Artikel "' . $title . '" berhasil dihapus.'
            );
    }

    /**
     * Validasi data artikel.
     */
    private function validateArticle(Request $request): array
    {
        return $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'category' => [
                'required',
                Rule::in([
                    'supply_chain',
                    'weather',
                    'currency',
                    'port',
                    'news',
                    'economy',
                    'geopolitics',
                    'logistics',
                ]),
            ],

            'summary' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'content' => [
                'required',
                'string',
                'min:20',
            ],

            'source' => [
                'nullable',
                'string',
                'max:255',
            ],

            'author' => [
                'nullable',
                'string',
                'max:100',
            ],

            'status' => [
                'required',
                Rule::in([
                    'draft',
                    'published',
                ]),
            ],

            'sentiment' => [
                'required',
                Rule::in([
                    'positive',
                    'neutral',
                    'negative',
                ]),
            ],

            'risk_level' => [
                'required',
                Rule::in([
                    'low',
                    'medium',
                    'high',
                    'critical',
                ]),
            ],

            'published_at' => [
                'nullable',
                'date',
            ],
        ], [
            'title.required' => 'Judul artikel wajib diisi.',
            'category.required' => 'Kategori artikel wajib dipilih.',
            'content.required' => 'Isi artikel wajib diisi.',
            'content.min' => 'Isi artikel minimal 20 karakter.',
            'status.required' => 'Status artikel wajib dipilih.',
            'sentiment.required' => 'Sentimen artikel wajib dipilih.',
            'risk_level.required' => 'Level risiko artikel wajib dipilih.',
        ]);
    }
}