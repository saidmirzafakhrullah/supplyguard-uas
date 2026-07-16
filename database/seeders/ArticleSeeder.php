<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    /**
     * Mengisi data awal artikel analisis.
     */
    public function run(): void
    {
        $articles = [
            [
                'title' => 'Risiko Cuaca Ekstrem terhadap Pengiriman Barang Global',
                'category' => 'weather',
                'summary' => 'Cuaca ekstrem seperti badai, hujan lebat, dan angin kencang dapat mengganggu jadwal pengiriman barang lintas negara.',
                'content' => 'Cuaca ekstrem menjadi salah satu faktor penting dalam risiko rantai pasok global. Ketika suatu negara mengalami badai, hujan ekstrem, atau angin kencang, aktivitas pelabuhan dan transportasi dapat terganggu. Kondisi ini menyebabkan keterlambatan pengiriman, kenaikan biaya logistik, dan perubahan jadwal distribusi barang.',
                'source' => 'SupplyGuard Internal Analysis',
                'author' => 'Administrator SupplyGuard',
                'status' => 'published',
                'sentiment' => 'negative',
                'risk_level' => 'high',
                'published_at' => now()->subDays(4),
            ],
            [
                'title' => 'Perubahan Nilai Tukar Mata Uang dan Dampaknya pada Biaya Impor',
                'category' => 'currency',
                'summary' => 'Fluktuasi kurs mata uang dapat meningkatkan biaya impor dan memengaruhi keputusan pembelian barang dari luar negeri.',
                'content' => 'Perubahan nilai tukar mata uang memiliki pengaruh besar terhadap aktivitas impor. Ketika mata uang negara pengimpor melemah, biaya pembelian barang dari luar negeri dapat meningkat. Oleh karena itu, perusahaan perlu memantau kurs mata uang secara berkala sebelum menentukan negara pemasok.',
                'source' => 'SupplyGuard Internal Analysis',
                'author' => 'Administrator SupplyGuard',
                'status' => 'published',
                'sentiment' => 'neutral',
                'risk_level' => 'medium',
                'published_at' => now()->subDays(3),
            ],
            [
                'title' => 'Kemacetan Pelabuhan sebagai Faktor Risiko Rantai Pasok',
                'category' => 'port',
                'summary' => 'Pelabuhan yang padat dapat menyebabkan keterlambatan distribusi barang dan meningkatnya biaya logistik.',
                'content' => 'Kemacetan pelabuhan merupakan salah satu penyebab utama gangguan distribusi barang dalam rantai pasok global. Ketika volume kontainer meningkat dan kapasitas pelabuhan terbatas, proses bongkar muat dapat melambat. Kondisi ini berdampak pada keterlambatan pengiriman serta peningkatan biaya operasional.',
                'source' => 'SupplyGuard Internal Analysis',
                'author' => 'Administrator SupplyGuard',
                'status' => 'published',
                'sentiment' => 'negative',
                'risk_level' => 'high',
                'published_at' => now()->subDays(2),
            ],
            [
                'title' => 'Pemantauan Berita Ekonomi untuk Mendukung Keputusan Impor',
                'category' => 'news',
                'summary' => 'Berita ekonomi, logistik, dan geopolitik dapat digunakan untuk membaca potensi risiko perdagangan internasional.',
                'content' => 'Berita ekonomi dan geopolitik dapat memberikan sinyal awal terhadap potensi gangguan rantai pasok. Konflik, inflasi, sanksi, dan ketidakpastian pasar dapat meningkatkan risiko impor. SupplyGuard menggunakan pendekatan analisis sentimen berbasis kamus untuk membantu membaca kecenderungan berita.',
                'source' => 'SupplyGuard Internal Analysis',
                'author' => 'Administrator SupplyGuard',
                'status' => 'draft',
                'sentiment' => 'neutral',
                'risk_level' => 'medium',
                'published_at' => null,
            ],
            [
                'title' => 'Strategi Memilih Negara Pemasok dengan Risiko Lebih Rendah',
                'category' => 'supply_chain',
                'summary' => 'Perusahaan perlu membandingkan negara pemasok berdasarkan cuaca, inflasi, kurs, berita, dan pelabuhan.',
                'content' => 'Pemilihan negara pemasok tidak hanya bergantung pada harga barang. Perusahaan juga perlu mempertimbangkan risiko cuaca, stabilitas ekonomi, nilai tukar, berita geopolitik, dan akses pelabuhan. Dengan membandingkan indikator tersebut, perusahaan dapat mengambil keputusan impor yang lebih aman.',
                'source' => 'SupplyGuard Internal Analysis',
                'author' => 'Administrator SupplyGuard',
                'status' => 'published',
                'sentiment' => 'positive',
                'risk_level' => 'low',
                'published_at' => now()->subDay(),
            ],
        ];

        foreach ($articles as $article) {
            Article::query()->updateOrCreate(
                [
                    'title' => $article['title'],
                ],
                $article
            );
        }
    }
}