@extends('layouts.app')

@section('title', 'Kata Sentimen - SupplyGuard')
@section('page-title', 'Manajemen Admin - Kata Sentimen')

@section('content')

{{-- BAGIAN JUDUL --}}
<div class="card sg-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold mb-1">Manajemen Kata Sentimen</h4>
            <p class="text-muted mb-0">
                Halaman admin untuk memantau kamus kata positif dan negatif
                yang digunakan dalam Analisis Sentimen Berbasis Leksikon.
            </p>
        </div>

        <span class="badge bg-primary">Berbasis Leksikon</span>
    </div>
</div>

{{-- RINGKASAN --}}
<div class="row g-4">
    <div class="col-md-2">
        <div class="card sg-card p-4">
            <small class="text-muted">Total Kata</small>
            <h3 class="fw-bold">{{ $summary['total_words'] }}</h3>
            <span class="badge bg-primary">Kamus</span>
        </div>
    </div>

    <div class="col-md-2">
        <div class="card sg-card p-4">
            <small class="text-muted">Kata Positif</small>
            <h3 class="fw-bold text-success">{{ $summary['positive_words'] }}</h3>
            <span class="badge bg-success">Positif</span>
        </div>
    </div>

    <div class="col-md-2">
        <div class="card sg-card p-4">
            <small class="text-muted">Kata Negatif</small>
            <h3 class="fw-bold text-danger">{{ $summary['negative_words'] }}</h3>
            <span class="badge bg-danger">Negatif</span>
        </div>
    </div>

    <div class="col-md-2">
        <div class="card sg-card p-4">
            <small class="text-muted">Skor Positif</small>
            <h3 class="fw-bold text-success">{{ $summary['positive_score'] }}</h3>
            <span class="badge bg-success">Skor</span>
        </div>
    </div>

    <div class="col-md-2">
        <div class="card sg-card p-4">
            <small class="text-muted">Skor Negatif</small>
            <h3 class="fw-bold text-danger">{{ $summary['negative_score'] }}</h3>
            <span class="badge bg-danger">Skor</span>
        </div>
    </div>

    <div class="col-md-2">
        <div class="card sg-card p-4">
            <small class="text-muted">Hasil</small>

            <h3 class="fw-bold">
                {{
                    [
                        'Positive' => 'Positif',
                        'Negative' => 'Negatif',
                        'Neutral' => 'Netral'
                    ][$summary['sentiment']] ?? $summary['sentiment']
                }}
            </h3>

            @if($summary['sentiment'] === 'Positive')
                <span class="badge bg-success">Positif</span>
            @elseif($summary['sentiment'] === 'Negative')
                <span class="badge bg-danger">Negatif</span>
            @else
                <span class="badge bg-primary">Netral</span>
            @endif
        </div>
    </div>
</div>

{{-- GRAFIK DAN CONTOH ANALISIS --}}
<div class="row g-4 mt-1">
    <div class="col-lg-5">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-1">Distribusi Kamus Kata</h5>

            <small class="text-muted">
                Perbandingan jumlah kata positif dan negatif.
            </small>

            <div class="mt-3" style="height: 300px;">
                <canvas id="wordDistributionChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-3">Contoh Analisis Sentimen</h5>

            <div class="alert alert-light border">
                <small class="text-muted">Contoh Teks</small>

                <div class="fw-bold mt-1">
                    "{{ $sampleText }}"
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Kata Positif yang Ditemukan</small>

                        <div class="mt-2">
                            @forelse($analysis['positive_matches'] as $word)
                                <span class="badge bg-success me-1">
                                    {{ $word }}
                                </span>
                            @empty
                                <span class="text-muted">
                                    Tidak ada kata positif
                                </span>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Kata Negatif yang Ditemukan</small>

                        <div class="mt-2">
                            @forelse($analysis['negative_matches'] as $word)
                                <span class="badge bg-danger me-1">
                                    {{ $word }}
                                </span>
                            @empty
                                <span class="text-muted">
                                    Tidak ada kata negatif
                                </span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                @if($analysis['sentiment'] === 'Positive')
                    <div class="alert alert-success mb-0">
                        Hasil analisis: <b>Sentimen Positif</b>.
                        Berita cenderung aman untuk rantai pasok.
                    </div>
                @elseif($analysis['sentiment'] === 'Negative')
                    <div class="alert alert-danger mb-0">
                        Hasil analisis: <b>Sentimen Negatif</b>.
                        Berita berpotensi meningkatkan risiko rantai pasok.
                    </div>
                @else
                    <div class="alert alert-primary mb-0">
                        Hasil analisis: <b>Sentimen Netral</b>.
                        Berita belum menunjukkan kecenderungan positif atau negatif.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- KAMUS KATA --}}
<div class="row g-4 mt-1">
    <div class="col-lg-6">
        <div class="card sg-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold mb-1">Kamus Kata Positif</h5>

                    <small class="text-muted">
                        Kamus kata yang menunjukkan sentimen positif.
                    </small>
                </div>

                <div style="width: 220px;">
                    <input
                        type="text"
                        id="positiveSearch"
                        class="form-control"
                        placeholder="Cari kata positif..."
                    >
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kata</th>
                            <th>Kategori</th>
                            <th>Bobot</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($positiveWords as $index => $word)
                            <tr
                                class="positive-row"
                                data-search="{{ strtolower($word['word'] . ' ' . $word['category'] . ' ' . $word['meaning']) }}"
                            >
                                <td>{{ $index + 1 }}</td>

                                <td class="fw-bold text-success">
                                    {{ $word['word'] }}
                                </td>

                                <td>
                                    {{
                                        [
                                            'Logistics' => 'Logistik',
                                            'Economy' => 'Ekonomi',
                                            'Trade' => 'Perdagangan',
                                            'Shipping' => 'Pengiriman',
                                            'Weather' => 'Cuaca',
                                            'Currency' => 'Mata Uang',
                                            'Port' => 'Pelabuhan',
                                            'Supply Chain' => 'Rantai Pasok',
                                            'General' => 'Umum'
                                        ][$word['category']] ?? $word['category']
                                    }}
                                </td>

                                <td>{{ $word['weight'] }}</td>

                                <td>
                                    <span class="badge bg-success">
                                        {{
                                            [
                                                'Active' => 'Aktif',
                                                'Inactive' => 'Tidak Aktif'
                                            ][$word['status']] ?? $word['status']
                                        }}
                                    </span>
                                </td>
                            </tr>

                            <tr class="positive-row-detail">
                                <td></td>

                                <td colspan="4">
                                    <small class="text-muted">
                                        {{ $word['meaning'] }}
                                    </small>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card sg-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold mb-1">Kamus Kata Negatif</h5>

                    <small class="text-muted">
                        Kamus kata yang menunjukkan sentimen negatif.
                    </small>
                </div>

                <div style="width: 220px;">
                    <input
                        type="text"
                        id="negativeSearch"
                        class="form-control"
                        placeholder="Cari kata negatif..."
                    >
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kata</th>
                            <th>Kategori</th>
                            <th>Bobot</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($negativeWords as $index => $word)
                            <tr
                                class="negative-row"
                                data-search="{{ strtolower($word['word'] . ' ' . $word['category'] . ' ' . $word['meaning']) }}"
                            >
                                <td>{{ $index + 1 }}</td>

                                <td class="fw-bold text-danger">
                                    {{ $word['word'] }}
                                </td>

                                <td>
                                    {{
                                        [
                                            'Logistics' => 'Logistik',
                                            'Economy' => 'Ekonomi',
                                            'Trade' => 'Perdagangan',
                                            'Shipping' => 'Pengiriman',
                                            'Weather' => 'Cuaca',
                                            'Currency' => 'Mata Uang',
                                            'Port' => 'Pelabuhan',
                                            'Supply Chain' => 'Rantai Pasok',
                                            'General' => 'Umum'
                                        ][$word['category']] ?? $word['category']
                                    }}
                                </td>

                                <td>{{ $word['weight'] }}</td>

                                <td>
                                    <span class="badge bg-danger">
                                        {{
                                            [
                                                'Active' => 'Aktif',
                                                'Inactive' => 'Tidak Aktif'
                                            ][$word['status']] ?? $word['status']
                                        }}
                                    </span>
                                </td>
                            </tr>

                            <tr class="negative-row-detail">
                                <td></td>

                                <td colspan="4">
                                    <small class="text-muted">
                                        {{ $word['meaning'] }}
                                    </small>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- RUMUS SENTIMEN --}}
<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">Rumus Sentimen Berbasis Leksikon</h5>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="border rounded p-3 h-100">
                <h6 class="fw-bold">1. Pembersihan Teks</h6>

                <p class="text-muted mb-0">
                    Sistem mengubah teks menjadi huruf kecil dan menghapus tanda baca.
                </p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="border rounded p-3 h-100">
                <h6 class="fw-bold">2. Pencocokan Kata</h6>

                <p class="text-muted mb-0">
                    Setiap kata dicocokkan dengan kamus
                    <b>positive_words</b> dan <b>negative_words</b>.
                </p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="border rounded p-3 h-100">
                <h6 class="fw-bold">3. Hasil Sentimen</h6>

                <p class="text-muted mb-0">
                    Jika skor negatif lebih tinggi, berita dianggap berisiko
                    untuk rantai pasok.
                </p>
            </div>
        </div>
    </div>

    <div class="alert alert-info mt-4 mb-0">
        Rumus sederhana:
        <b>Skor Positif</b> dibandingkan dengan <b>Skor Negatif</b>.
        Jika Skor Negatif lebih besar, hasilnya adalah Sentimen Negatif.
    </div>
</div>

{{-- PENJELASAN --}}
<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">Penjelasan Kata Sentimen</h5>

    <p class="text-muted mb-2">
        Fitur Kata Sentimen digunakan untuk menyimpan kamus kata positif dan negatif.
        Kamus ini digunakan pada fitur Intelijen Berita untuk menentukan apakah berita
        memiliki sentimen positif, netral, atau negatif terhadap risiko rantai pasok.
    </p>

    <div class="alert alert-info mb-0">
        Fitur ini sesuai dengan konsep Analisis Sentimen Berbasis Leksikon,
        yaitu analisis sentimen berdasarkan pencocokan kata dengan kamus.
    </div>
</div>

@endsection

@push('scripts')
<script>
    const positiveWords = {{ $summary['positive_words'] }};
    const negativeWords = {{ $summary['negative_words'] }};

    new Chart(document.getElementById('wordDistributionChart'), {
        type: 'doughnut',
        data: {
            labels: [
                'Kata Positif',
                'Kata Negatif'
            ],
            datasets: [{
                data: [
                    positiveWords,
                    negativeWords
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    document.getElementById('positiveSearch').addEventListener('keyup', function () {
        const keyword = this.value.toLowerCase();
        const rows = document.querySelectorAll('.positive-row');

        rows.forEach(function (row) {
            const text = row.getAttribute('data-search');

            if (text.includes(keyword)) {
                row.style.display = '';

                if (row.nextElementSibling) {
                    row.nextElementSibling.style.display = '';
                }
            } else {
                row.style.display = 'none';

                if (row.nextElementSibling) {
                    row.nextElementSibling.style.display = 'none';
                }
            }
        });
    });

    document.getElementById('negativeSearch').addEventListener('keyup', function () {
        const keyword = this.value.toLowerCase();
        const rows = document.querySelectorAll('.negative-row');

        rows.forEach(function (row) {
            const text = row.getAttribute('data-search');

            if (text.includes(keyword)) {
                row.style.display = '';

                if (row.nextElementSibling) {
                    row.nextElementSibling.style.display = '';
                }
            } else {
                row.style.display = 'none';

                if (row.nextElementSibling) {
                    row.nextElementSibling.style.display = 'none';
                }
            }
        });
    });
</script>
@endpush