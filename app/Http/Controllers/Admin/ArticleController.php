<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = [
            [
                'id' => 1,
                'title' => 'Global Shipping Delay Risk Analysis',
                'category' => 'Logistics',
                'country' => 'China',
                'sentiment' => 'Negative',
                'risk_level' => 'High',
                'status' => 'Published',
                'author' => 'Admin',
                'published_at' => '03 Jul 2026',
                'summary' => 'Analisis keterlambatan pengiriman akibat kepadatan pelabuhan dan peningkatan permintaan ekspor.',
            ],
            [
                'id' => 2,
                'title' => 'Currency Stability Improves Import Planning',
                'category' => 'Currency',
                'country' => 'Germany',
                'sentiment' => 'Positive',
                'risk_level' => 'Low',
                'status' => 'Published',
                'author' => 'Admin',
                'published_at' => '03 Jul 2026',
                'summary' => 'Stabilitas kurs membantu perusahaan menyusun rencana impor dengan biaya yang lebih terkendali.',
            ],
            [
                'id' => 3,
                'title' => 'Extreme Weather Warning for Sea Transportation',
                'category' => 'Weather',
                'country' => 'Japan',
                'sentiment' => 'Negative',
                'risk_level' => 'High',
                'status' => 'Published',
                'author' => 'Admin',
                'published_at' => '04 Jul 2026',
                'summary' => 'Cuaca ekstrem berpotensi mengganggu transportasi laut dan jadwal distribusi barang.',
            ],
            [
                'id' => 4,
                'title' => 'Port Congestion Monitoring Update',
                'category' => 'Port',
                'country' => 'Singapore',
                'sentiment' => 'Neutral',
                'risk_level' => 'Medium',
                'status' => 'Draft',
                'author' => 'Admin',
                'published_at' => '-',
                'summary' => 'Pemantauan kepadatan pelabuhan digunakan untuk menganalisis potensi keterlambatan pengiriman.',
            ],
            [
                'id' => 5,
                'title' => 'Inflation Pressure Affects Import Cost',
                'category' => 'Economy',
                'country' => 'Indonesia',
                'sentiment' => 'Negative',
                'risk_level' => 'Medium',
                'status' => 'Published',
                'author' => 'Admin',
                'published_at' => '05 Jul 2026',
                'summary' => 'Inflasi dapat meningkatkan biaya produksi dan harga barang impor.',
            ],
            [
                'id' => 6,
                'title' => 'Trade Growth Supports Supply Chain Recovery',
                'category' => 'Trade',
                'country' => 'Australia',
                'sentiment' => 'Positive',
                'risk_level' => 'Low',
                'status' => 'Published',
                'author' => 'Admin',
                'published_at' => '05 Jul 2026',
                'summary' => 'Pertumbuhan perdagangan membantu pemulihan rantai pasok dan meningkatkan stabilitas impor.',
            ],
        ];

        $summary = [
            'total_articles' => count($articles),
            'published' => collect($articles)->where('status', 'Published')->count(),
            'draft' => collect($articles)->where('status', 'Draft')->count(),
            'positive' => collect($articles)->where('sentiment', 'Positive')->count(),
            'neutral' => collect($articles)->where('sentiment', 'Neutral')->count(),
            'negative' => collect($articles)->where('sentiment', 'Negative')->count(),
        ];

        return view('admin.articles.index', compact('articles', 'summary'));
    }
}