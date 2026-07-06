@extends('layouts.app')

@section('title', 'News Intelligence - SupplyGuard')
@section('page-title', 'News Intelligence')

@section('content')
<div class="card sg-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold mb-1">News Intelligence</h4>
            <p class="text-muted mb-0">
                Sistem membaca berita ekonomi, logistik, shipping, trade, dan geopolitik untuk semua negara
                agar dapat membantu menghitung risiko rantai pasok.
            </p>
        </div>

        <span class="badge bg-warning text-dark">GNews API Ready</span>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Total Countries News</small>
            <h3 class="fw-bold">{{ $summary['total_news'] }}</h3>
            <span class="badge bg-primary">All Countries</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Positive News</small>
            <h3 class="fw-bold text-success">{{ $summary['positive_news'] }}</h3>
            <span class="badge-soft risk-low">Positive</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Neutral News</small>
            <h3 class="fw-bold text-warning">{{ $summary['neutral_news'] }}</h3>
            <span class="badge-soft risk-medium">Neutral</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Negative News</small>
            <h3 class="fw-bold text-danger">{{ $summary['negative_news'] }}</h3>
            <span class="badge-soft risk-high">Negative</span>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-lg-5">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-3">Country News Selector</h5>

            <label class="form-label">Country</label>
            <select id="countrySelect" class="form-select mb-3">
                @foreach($countries as $index => $country)
                    <option value="{{ $index }}">
                        {{ $country['name'] }}
                    </option>
                @endforeach
            </select>

            <button onclick="showCountryNews()" class="btn btn-primary w-100">
                Analyze News Risk
            </button>

            <div class="alert alert-info mt-3 mb-0">
                Total negara tersedia:
                <b>{{ count($countries) }}</b>
            </div>
        </div>

        <div class="card sg-card p-4 mt-4">
            <h5 class="fw-bold mb-3">Selected Country</h5>

            <div class="d-flex align-items-center gap-3 mb-3">
                <img id="countryFlag" src="" alt="Flag" style="width: 70px; border-radius: 8px; display: none;">
                <div>
                    <h5 id="countryName" class="fw-bold mb-0">-</h5>
                    <small id="countryRegion" class="text-muted">-</small>
                </div>
            </div>

            <table class="table align-middle mb-0">
                <tbody>
                    <tr>
                        <td>Capital</td>
                        <td id="countryCapital" class="fw-bold text-end">-</td>
                    </tr>
                    <tr>
                        <td>Country Code</td>
                        <td id="countryCode" class="fw-bold text-end">-</td>
                    </tr>
                    <tr>
                        <td>Data Source</td>
                        <td id="dataSource" class="fw-bold text-end">GNews API Simulation</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-3">News Intelligence Result</h5>

            <div class="border rounded p-3 mb-3">
                <small class="text-muted">News Title</small>
                <h5 id="newsTitle" class="fw-bold mb-0">-</h5>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">News Category</small>
                        <h5 id="newsCategory" class="fw-bold mb-0">-</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Sentiment</small>
                        <h5 id="sentimentResult" class="fw-bold mb-0">-</h5>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="border rounded p-3">
                        <small class="text-muted">Positive Words</small>
                        <h5 id="positiveCount" class="fw-bold mb-0">-</h5>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="border rounded p-3">
                        <small class="text-muted">Negative Words</small>
                        <h5 id="negativeCount" class="fw-bold mb-0">-</h5>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="border rounded p-3">
                        <small class="text-muted">News Risk</small>
                        <h5 id="newsRisk" class="fw-bold mb-0">-</h5>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <span id="riskCategory" class="badge-soft risk-low">-</span>
            </div>

            <div id="recommendationBox" class="alert alert-primary mt-4 mb-0">
                Pilih negara untuk melihat analisis berita.
            </div>
        </div>

        <div class="card sg-card p-4 mt-4">
            <h5 class="fw-bold mb-3">News Sentiment Component</h5>
            <canvas id="newsChart" height="130"></canvas>
        </div>
    </div>
</div>

<div class="card sg-card p-4 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold mb-0">All Countries News Preview</h5>
            <small class="text-muted">
                Preview berita untuk beberapa negara. Semua negara tetap tersedia pada dropdown.
            </small>
        </div>

        <button class="btn btn-primary btn-sm" onclick="showCountryNews()">
            Refresh News
        </button>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Country</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Sentiment</th>
                    <th>Risk</th>
                    <th>Status</th>
                    <th>Recommendation</th>
                </tr>
            </thead>

            <tbody>
                @foreach(array_slice($countries, 0, 25) as $index => $country)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $country['name'] }}</td>
                        <td>
                            <div class="fw-semibold">{{ $country['news_title'] }}</div>
                            <small class="text-muted">Source: {{ $country['source'] }}</small>
                        </td>
                        <td>{{ $country['news_category'] }}</td>
                        <td>{{ $country['sentiment'] }}</td>
                        <td class="fw-bold">{{ $country['news_risk'] }}</td>
                        <td>
                            <span class="badge-soft {{ $country['badge'] }}">
                                {{ $country['risk_category'] }}
                            </span>
                        </td>
                        <td>{{ $country['recommendation'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <small class="text-muted">
        Tabel ini menampilkan 25 negara pertama sebagai preview. Semua negara tetap tersedia di dropdown.
    </small>
</div>

<div class="row g-4 mt-1">
    <div class="col-lg-7">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-3">Lexicon Based Sentiment Analysis</h5>

            <p class="text-muted">
                Sistem menghitung kata positif dan negatif dari judul berita. Jika kata negatif lebih banyak,
                maka berita dikategorikan sebagai Negative. Jika kata positif lebih banyak, maka berita
                dikategorikan sebagai Positive. Jika seimbang, maka hasilnya Neutral.
            </p>

            <div class="alert alert-info mb-0">
                Contoh: berita yang mengandung kata
                <b>delay</b>, <b>crisis</b>, <b>inflation</b>, atau <b>conflict</b>
                akan meningkatkan News Sentiment Risk.
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-3">Sentiment Dictionary Example</h5>

            <div class="border rounded p-3 mb-3">
                <h6 class="fw-bold text-success">Positive Words</h6>
                <p class="mb-0">
                    {{ implode(', ', $positiveWords) }}
                </p>
            </div>

            <div class="border rounded p-3">
                <h6 class="fw-bold text-danger">Negative Words</h6>
                <p class="mb-0">
                    {{ implode(', ', $negativeWords) }}
                </p>
            </div>
        </div>
    </div>
</div>

<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">News Risk Calculation</h5>

    <p class="text-muted mb-2">
        News risk dihitung dari hasil sentiment analysis. Berita negatif akan meningkatkan risiko rantai pasok,
        sedangkan berita positif menurunkan tingkat risiko.
    </p>

    <div class="alert alert-info mb-0">
        News Sentiment Risk =
        Positive News menghasilkan risiko rendah,
        Neutral News menghasilkan risiko sedang,
        dan Negative News menghasilkan risiko tinggi.
    </div>
</div>
@endsection

@push('scripts')
<script>
    const countries = @json($countries);
    let newsChart = null;

    function getSentimentBadge(sentiment) {
        if (sentiment === 'Positive') {
            return 'risk-low';
        }

        if (sentiment === 'Neutral') {
            return 'risk-medium';
        }

        return 'risk-high';
    }

    function showCountryNews() {
        const selectedIndex = document.getElementById('countrySelect').value;
        const country = countries[selectedIndex];

        document.getElementById('countryName').innerText = country.name ?? '-';
        document.getElementById('countryRegion').innerText = (country.region ?? '-') + ' / ' + (country.subregion ?? '-');
        document.getElementById('countryCapital').innerText = country.capital ?? '-';
        document.getElementById('countryCode').innerText = country.code ?? '-';
        document.getElementById('dataSource').innerText = country.source ?? 'GNews API Simulation';

        document.getElementById('newsTitle').innerText = country.news_title ?? '-';
        document.getElementById('newsCategory').innerText = country.news_category ?? '-';
        document.getElementById('sentimentResult').innerText = country.sentiment ?? '-';
        document.getElementById('positiveCount').innerText = country.positive_count ?? 0;
        document.getElementById('negativeCount').innerText = country.negative_count ?? 0;
        document.getElementById('newsRisk').innerText = country.news_risk ?? '-';

        const riskCategory = document.getElementById('riskCategory');
        riskCategory.className = 'badge-soft ' + country.badge;
        riskCategory.innerText = country.risk_category + ' Risk';

        const recommendationBox = document.getElementById('recommendationBox');
        recommendationBox.innerText = country.recommendation;
        recommendationBox.className = 'alert alert-primary mt-4 mb-0';

        const flag = document.getElementById('countryFlag');

        if (country.flag) {
            flag.src = country.flag;
            flag.style.display = 'block';
        } else {
            flag.style.display = 'none';
        }

        if (newsChart) {
            newsChart.destroy();
        }

        newsChart = new Chart(document.getElementById('newsChart'), {
            type: 'bar',
            data: {
                labels: ['Positive Words', 'Negative Words', 'News Risk'],
                datasets: [{
                    label: 'News Sentiment Analysis',
                    data: [
                        country.positive_count,
                        country.negative_count,
                        country.news_risk
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        showCountryNews();
    });
</script>
@endpush