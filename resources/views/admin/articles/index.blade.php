@extends('layouts.app')

@section('title', 'Admin Artikel - SupplyGuard')
@section('page-title', 'Manajemen Admin - Artikel')

@section('content')

{{-- BAGIAN JUDUL --}}
<div class="card sg-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold mb-1">Manajemen Artikel</h4>
            <p class="text-muted mb-0">
                Halaman admin untuk memantau artikel analisis logistik, ekonomi,
                cuaca, kurs, dan risiko rantai pasok global.
            </p>
        </div>

        <span class="badge bg-primary">Dataset Artikel</span>
    </div>
</div>

{{-- RINGKASAN --}}
<div class="row g-4">
    <div class="col-md-2">
        <div class="card sg-card p-4">
            <small class="text-muted">Total Artikel</small>
            <h3 class="fw-bold">{{ $summary['total_articles'] }}</h3>
            <span class="badge bg-primary">Artikel</span>
        </div>
    </div>

    <div class="col-md-2">
        <div class="card sg-card p-4">
            <small class="text-muted">Dipublikasikan</small>
            <h3 class="fw-bold text-success">{{ $summary['published'] }}</h3>
            <span class="badge bg-success">Dipublikasikan</span>
        </div>
    </div>

    <div class="col-md-2">
        <div class="card sg-card p-4">
            <small class="text-muted">Draf</small>
            <h3 class="fw-bold text-warning">{{ $summary['draft'] }}</h3>
            <span class="badge bg-warning text-dark">Draf</span>
        </div>
    </div>

    <div class="col-md-2">
        <div class="card sg-card p-4">
            <small class="text-muted">Positif</small>
            <h3 class="fw-bold text-success">{{ $summary['positive'] }}</h3>
            <span class="badge bg-success">Positif</span>
        </div>
    </div>

    <div class="col-md-2">
        <div class="card sg-card p-4">
            <small class="text-muted">Netral</small>
            <h3 class="fw-bold text-primary">{{ $summary['neutral'] }}</h3>
            <span class="badge bg-primary">Netral</span>
        </div>
    </div>

    <div class="col-md-2">
        <div class="card sg-card p-4">
            <small class="text-muted">Negatif</small>
            <h3 class="fw-bold text-danger">{{ $summary['negative'] }}</h3>
            <span class="badge bg-danger">Negatif</span>
        </div>
    </div>
</div>

{{-- GRAFIK DAN INFORMASI --}}
<div class="row g-4 mt-1">
    <div class="col-lg-5">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-1">Distribusi Sentimen Artikel</h5>
            <small class="text-muted">
                Distribusi sentimen artikel analisis.
            </small>

            <div class="mt-3" style="height: 300px;">
                <canvas id="articleSentimentChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-3">Informasi Artikel</h5>

            <div class="alert alert-info">
                Artikel digunakan sebagai data analisis untuk mendukung fitur Intelijen Berita
                dan sentimen risiko rantai pasok.
            </div>

            <table class="table align-middle mb-0">
                <tbody>
                    <tr>
                        <td>Jenis Dataset</td>
                        <td class="fw-bold text-end">Artikel Analisis</td>
                    </tr>

                    <tr>
                        <td>Digunakan Untuk</td>
                        <td class="fw-bold text-end">Intelijen Berita</td>
                    </tr>

                    <tr>
                        <td>Metode Sentimen</td>
                        <td class="fw-bold text-end">Berbasis Leksikon</td>
                    </tr>

                    <tr>
                        <td>Fungsi Admin</td>
                        <td class="fw-bold text-end">Pemantauan Artikel</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- TABEL ARTIKEL --}}
<div class="card sg-card p-4 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold mb-1">Dataset Artikel</h5>
            <small class="text-muted">
                Daftar artikel analisis yang digunakan dalam sistem.
            </small>
        </div>

        <div style="width: 280px;">
            <input
                type="text"
                id="articleSearch"
                class="form-control"
                placeholder="Cari artikel..."
            >
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Judul</th>
                    <th>Kategori</th>
                    <th>Negara</th>
                    <th>Sentimen</th>
                    <th>Risiko</th>
                    <th>Status</th>
                    <th>Tanggal Publikasi</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @foreach($articles as $index => $article)
                    <tr
                        class="article-row"
                        data-search="{{ strtolower($article['title'] . ' ' . $article['category'] . ' ' . $article['country'] . ' ' . $article['sentiment'] . ' ' . $article['status']) }}"
                    >
                        <td>{{ $index + 1 }}</td>

                        <td>
                            <div class="fw-bold">{{ $article['title'] }}</div>
                            <small class="text-muted">{{ $article['summary'] }}</small>
                        </td>

                        <td>
                            {{
                                [
                                    'Logistics' => 'Logistik',
                                    'Economy' => 'Ekonomi',
                                    'Weather' => 'Cuaca',
                                    'Currency' => 'Mata Uang',
                                    'Trade' => 'Perdagangan',
                                    'Shipping' => 'Pengiriman',
                                    'Inflation' => 'Inflasi',
                                    'Geopolitics' => 'Geopolitik',
                                    'Port' => 'Pelabuhan'
                                ][$article['category']] ?? $article['category']
                            }}
                        </td>

                        <td>{{ $article['country'] }}</td>

                        <td>
                            @if($article['sentiment'] === 'Positive')
                                <span class="badge bg-success">Positif</span>
                            @elseif($article['sentiment'] === 'Neutral')
                                <span class="badge bg-primary">Netral</span>
                            @else
                                <span class="badge bg-danger">Negatif</span>
                            @endif
                        </td>

                        <td>
                            @if($article['risk_level'] === 'Low')
                                <span class="badge bg-success">Rendah</span>
                            @elseif($article['risk_level'] === 'Medium')
                                <span class="badge bg-warning text-dark">Sedang</span>
                            @else
                                <span class="badge bg-danger">Tinggi</span>
                            @endif
                        </td>

                        <td>
                            @if($article['status'] === 'Published')
                                <span class="badge bg-success">Dipublikasikan</span>
                            @else
                                <span class="badge bg-warning text-dark">Draf</span>
                            @endif
                        </td>

                        <td>{{ $article['published_at'] }}</td>

                        <td>
                            <button class="btn btn-sm btn-outline-primary" disabled>
                                Detail
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- PENJELASAN --}}
<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">Penjelasan Manajemen Artikel</h5>

    <p class="text-muted mb-2">
        Fitur Manajemen Artikel digunakan admin untuk memantau artikel analisis
        yang berkaitan dengan logistik, perdagangan, cuaca, kurs, inflasi, dan ekonomi.
    </p>

    <div class="alert alert-info mb-0">
        Artikel ini dapat digunakan untuk mendukung analisis Intelijen Berita
        dan proses analisis sentimen berbasis kamus kata positif dan negatif.
    </div>
</div>

@endsection

@push('scripts')
<script>
    const positiveArticles = {{ $summary['positive'] }};
    const neutralArticles = {{ $summary['neutral'] }};
    const negativeArticles = {{ $summary['negative'] }};

    new Chart(document.getElementById('articleSentimentChart'), {
        type: 'doughnut',
        data: {
            labels: [
                'Positif',
                'Netral',
                'Negatif'
            ],
            datasets: [{
                data: [
                    positiveArticles,
                    neutralArticles,
                    negativeArticles
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    document.getElementById('articleSearch').addEventListener('keyup', function () {
        const keyword = this.value.toLowerCase();
        const rows = document.querySelectorAll('.article-row');

        rows.forEach(function (row) {
            const text = row.getAttribute('data-search');

            if (text.includes(keyword)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
</script>
@endpush