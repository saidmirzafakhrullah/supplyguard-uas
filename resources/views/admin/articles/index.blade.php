@extends('layouts.app')

@section('title', 'Admin Articles - SupplyGuard')
@section('page-title', 'Admin Management - Articles')

@section('content')

<div class="card sg-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold mb-1">Articles Management</h4>
            <p class="text-muted mb-0">
                Halaman admin untuk memantau artikel analisis logistik, ekonomi,
                cuaca, kurs, dan risiko rantai pasok global.
            </p>
        </div>

        <span class="badge bg-primary">Article Dataset</span>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-2">
        <div class="card sg-card p-4">
            <small class="text-muted">Total Articles</small>
            <h3 class="fw-bold">{{ $summary['total_articles'] }}</h3>
            <span class="badge bg-primary">Articles</span>
        </div>
    </div>

    <div class="col-md-2">
        <div class="card sg-card p-4">
            <small class="text-muted">Published</small>
            <h3 class="fw-bold text-success">{{ $summary['published'] }}</h3>
            <span class="badge bg-success">Published</span>
        </div>
    </div>

    <div class="col-md-2">
        <div class="card sg-card p-4">
            <small class="text-muted">Draft</small>
            <h3 class="fw-bold text-warning">{{ $summary['draft'] }}</h3>
            <span class="badge bg-warning text-dark">Draft</span>
        </div>
    </div>

    <div class="col-md-2">
        <div class="card sg-card p-4">
            <small class="text-muted">Positive</small>
            <h3 class="fw-bold text-success">{{ $summary['positive'] }}</h3>
            <span class="badge bg-success">Positive</span>
        </div>
    </div>

    <div class="col-md-2">
        <div class="card sg-card p-4">
            <small class="text-muted">Neutral</small>
            <h3 class="fw-bold text-primary">{{ $summary['neutral'] }}</h3>
            <span class="badge bg-primary">Neutral</span>
        </div>
    </div>

    <div class="col-md-2">
        <div class="card sg-card p-4">
            <small class="text-muted">Negative</small>
            <h3 class="fw-bold text-danger">{{ $summary['negative'] }}</h3>
            <span class="badge bg-danger">Negative</span>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-lg-5">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-1">Article Sentiment Distribution</h5>
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
            <h5 class="fw-bold mb-3">Articles Information</h5>

            <div class="alert alert-info">
                Artikel digunakan sebagai data analisis untuk mendukung fitur News Intelligence
                dan sentimen risiko rantai pasok.
            </div>

            <table class="table align-middle mb-0">
                <tbody>
                    <tr>
                        <td>Dataset Type</td>
                        <td class="fw-bold text-end">Analysis Articles</td>
                    </tr>

                    <tr>
                        <td>Used For</td>
                        <td class="fw-bold text-end">News Intelligence</td>
                    </tr>

                    <tr>
                        <td>Sentiment Method</td>
                        <td class="fw-bold text-end">Lexicon Based</td>
                    </tr>

                    <tr>
                        <td>Admin Function</td>
                        <td class="fw-bold text-end">Article Monitoring</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card sg-card p-4 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold mb-1">Articles Dataset</h5>
            <small class="text-muted">
                Daftar artikel analisis yang digunakan dalam sistem.
            </small>
        </div>

        <div style="width: 280px;">
            <input
                type="text"
                id="articleSearch"
                class="form-control"
                placeholder="Search article..."
            >
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Country</th>
                    <th>Sentiment</th>
                    <th>Risk</th>
                    <th>Status</th>
                    <th>Published</th>
                    <th>Action</th>
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

                        <td>{{ $article['category'] }}</td>
                        <td>{{ $article['country'] }}</td>

                        <td>
                            @if($article['sentiment'] === 'Positive')
                                <span class="badge bg-success">Positive</span>
                            @elseif($article['sentiment'] === 'Neutral')
                                <span class="badge bg-primary">Neutral</span>
                            @else
                                <span class="badge bg-danger">Negative</span>
                            @endif
                        </td>

                        <td>
                            @if($article['risk_level'] === 'Low')
                                <span class="badge bg-success">Low</span>
                            @elseif($article['risk_level'] === 'Medium')
                                <span class="badge bg-warning text-dark">Medium</span>
                            @else
                                <span class="badge bg-danger">High</span>
                            @endif
                        </td>

                        <td>
                            @if($article['status'] === 'Published')
                                <span class="badge bg-success">Published</span>
                            @else
                                <span class="badge bg-warning text-dark">Draft</span>
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

<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">Articles Management Explanation</h5>

    <p class="text-muted mb-2">
        Fitur Articles Management digunakan admin untuk memantau artikel analisis
        yang berkaitan dengan logistik, perdagangan, cuaca, kurs, inflasi, dan ekonomi.
    </p>

    <div class="alert alert-info mb-0">
        Artikel ini dapat digunakan untuk mendukung analisis News Intelligence
        dan proses sentiment analysis berbasis kamus kata positif dan negatif.
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
            labels: ['Positive', 'Neutral', 'Negative'],
            datasets: [{
                data: [positiveArticles, neutralArticles, negativeArticles]
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