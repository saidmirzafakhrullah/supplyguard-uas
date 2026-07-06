@extends('layouts.app')

@section('title', 'Sentiment Words - SupplyGuard')
@section('page-title', 'Admin Management - Sentiment Words')

@section('content')

<div class="card sg-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold mb-1">Sentiment Words Management</h4>
            <p class="text-muted mb-0">
                Halaman admin untuk memantau kamus kata positif dan negatif
                yang digunakan dalam Lexicon Based Sentiment Analysis.
            </p>
        </div>

        <span class="badge bg-primary">Lexicon Based</span>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-2">
        <div class="card sg-card p-4">
            <small class="text-muted">Total Words</small>
            <h3 class="fw-bold">{{ $summary['total_words'] }}</h3>
            <span class="badge bg-primary">Dictionary</span>
        </div>
    </div>

    <div class="col-md-2">
        <div class="card sg-card p-4">
            <small class="text-muted">Positive Words</small>
            <h3 class="fw-bold text-success">{{ $summary['positive_words'] }}</h3>
            <span class="badge bg-success">Positive</span>
        </div>
    </div>

    <div class="col-md-2">
        <div class="card sg-card p-4">
            <small class="text-muted">Negative Words</small>
            <h3 class="fw-bold text-danger">{{ $summary['negative_words'] }}</h3>
            <span class="badge bg-danger">Negative</span>
        </div>
    </div>

    <div class="col-md-2">
        <div class="card sg-card p-4">
            <small class="text-muted">Positive Score</small>
            <h3 class="fw-bold text-success">{{ $summary['positive_score'] }}</h3>
            <span class="badge bg-success">Score</span>
        </div>
    </div>

    <div class="col-md-2">
        <div class="card sg-card p-4">
            <small class="text-muted">Negative Score</small>
            <h3 class="fw-bold text-danger">{{ $summary['negative_score'] }}</h3>
            <span class="badge bg-danger">Score</span>
        </div>
    </div>

    <div class="col-md-2">
        <div class="card sg-card p-4">
            <small class="text-muted">Result</small>
            <h3 class="fw-bold">
                {{ $summary['sentiment'] }}
            </h3>

            @if($summary['sentiment'] === 'Positive')
                <span class="badge bg-success">Positive</span>
            @elseif($summary['sentiment'] === 'Negative')
                <span class="badge bg-danger">Negative</span>
            @else
                <span class="badge bg-primary">Neutral</span>
            @endif
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-lg-5">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-1">Dictionary Distribution</h5>
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
            <h5 class="fw-bold mb-3">Sample Sentiment Analysis</h5>

            <div class="alert alert-light border">
                <small class="text-muted">Sample Text</small>
                <div class="fw-bold mt-1">
                    "{{ $sampleText }}"
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Positive Matches</small>

                        <div class="mt-2">
                            @forelse($analysis['positive_matches'] as $word)
                                <span class="badge bg-success me-1">{{ $word }}</span>
                            @empty
                                <span class="text-muted">No positive word</span>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Negative Matches</small>

                        <div class="mt-2">
                            @forelse($analysis['negative_matches'] as $word)
                                <span class="badge bg-danger me-1">{{ $word }}</span>
                            @empty
                                <span class="text-muted">No negative word</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                @if($analysis['sentiment'] === 'Positive')
                    <div class="alert alert-success mb-0">
                        Hasil analisis: <b>Positive Sentiment</b>.
                        Berita cenderung aman untuk rantai pasok.
                    </div>
                @elseif($analysis['sentiment'] === 'Negative')
                    <div class="alert alert-danger mb-0">
                        Hasil analisis: <b>Negative Sentiment</b>.
                        Berita berpotensi meningkatkan risiko rantai pasok.
                    </div>
                @else
                    <div class="alert alert-primary mb-0">
                        Hasil analisis: <b>Neutral Sentiment</b>.
                        Berita belum menunjukkan kecenderungan positif atau negatif.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-lg-6">
        <div class="card sg-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold mb-1">Positive Words Dictionary</h5>
                    <small class="text-muted">
                        Kamus kata yang menunjukkan sentimen positif.
                    </small>
                </div>

                <div style="width: 220px;">
                    <input
                        type="text"
                        id="positiveSearch"
                        class="form-control"
                        placeholder="Search positive..."
                    >
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Word</th>
                            <th>Category</th>
                            <th>Weight</th>
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
                                <td class="fw-bold text-success">{{ $word['word'] }}</td>
                                <td>{{ $word['category'] }}</td>
                                <td>{{ $word['weight'] }}</td>
                                <td>
                                    <span class="badge bg-success">{{ $word['status'] }}</span>
                                </td>
                            </tr>

                            <tr class="positive-row-detail">
                                <td></td>
                                <td colspan="4">
                                    <small class="text-muted">{{ $word['meaning'] }}</small>
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
                    <h5 class="fw-bold mb-1">Negative Words Dictionary</h5>
                    <small class="text-muted">
                        Kamus kata yang menunjukkan sentimen negatif.
                    </small>
                </div>

                <div style="width: 220px;">
                    <input
                        type="text"
                        id="negativeSearch"
                        class="form-control"
                        placeholder="Search negative..."
                    >
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Word</th>
                            <th>Category</th>
                            <th>Weight</th>
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
                                <td class="fw-bold text-danger">{{ $word['word'] }}</td>
                                <td>{{ $word['category'] }}</td>
                                <td>{{ $word['weight'] }}</td>
                                <td>
                                    <span class="badge bg-danger">{{ $word['status'] }}</span>
                                </td>
                            </tr>

                            <tr class="negative-row-detail">
                                <td></td>
                                <td colspan="4">
                                    <small class="text-muted">{{ $word['meaning'] }}</small>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">Lexicon Based Sentiment Formula</h5>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="border rounded p-3 h-100">
                <h6 class="fw-bold">1. Text Cleaning</h6>
                <p class="text-muted mb-0">
                    Sistem mengubah teks menjadi huruf kecil dan menghapus tanda baca.
                </p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="border rounded p-3 h-100">
                <h6 class="fw-bold">2. Word Matching</h6>
                <p class="text-muted mb-0">
                    Setiap kata dicocokkan dengan kamus positive_words dan negative_words.
                </p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="border rounded p-3 h-100">
                <h6 class="fw-bold">3. Sentiment Result</h6>
                <p class="text-muted mb-0">
                    Jika skor negatif lebih tinggi, berita dianggap berisiko untuk rantai pasok.
                </p>
            </div>
        </div>
    </div>

    <div class="alert alert-info mt-4 mb-0">
        Rumus sederhana:
        <b>Positive Score</b> dibandingkan dengan <b>Negative Score</b>.
        Jika Negative Score lebih besar, maka hasilnya Negative Sentiment.
    </div>
</div>

<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">Sentiment Words Explanation</h5>

    <p class="text-muted mb-2">
        Fitur Sentiment Words digunakan untuk menyimpan kamus kata positif dan negatif.
        Kamus ini digunakan pada fitur News Intelligence untuk menentukan apakah berita
        memiliki sentimen positif, netral, atau negatif terhadap risiko rantai pasok.
    </p>

    <div class="alert alert-info mb-0">
        Fitur ini sesuai dengan konsep Lexicon Based Sentiment Analysis,
        yaitu analisis sentimen berdasarkan pencocokan kata dengan dictionary.
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
            labels: ['Positive Words', 'Negative Words'],
            datasets: [{
                data: [positiveWords, negativeWords]
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