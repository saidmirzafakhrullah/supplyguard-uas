@extends('layouts.app')

@section('title', 'Intelijen Berita - SupplyGuard')
@section('page-title', 'Intelijen Berita')

@section('content')
<div class="card sg-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold mb-1">Intelijen Berita</h4>
            <p class="text-muted mb-0">
                Sistem membaca berita ekonomi, logistik, pengiriman, perdagangan, dan geopolitik
                untuk semua negara agar dapat membantu menghitung risiko rantai pasok.
            </p>
        </div>

        <span class="badge bg-warning text-dark">GNews API Siap</span>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Total Berita Negara</small>
            <h3 class="fw-bold">{{ $summary['total_news'] }}</h3>
            <span class="badge bg-primary">Seluruh Negara</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Berita Positif</small>
            <h3 class="fw-bold text-success">{{ $summary['positive_news'] }}</h3>
            <span class="badge-soft risk-low">Positif</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Berita Netral</small>
            <h3 class="fw-bold text-warning">{{ $summary['neutral_news'] }}</h3>
            <span class="badge-soft risk-medium">Netral</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Berita Negatif</small>
            <h3 class="fw-bold text-danger">{{ $summary['negative_news'] }}</h3>
            <span class="badge-soft risk-high">Negatif</span>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-lg-5">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-3">Pemilihan Berita Negara</h5>

            <label class="form-label">Negara</label>

            <select id="countrySelect" class="form-select mb-3">
                @foreach($countries as $index => $country)
                    <option value="{{ $index }}">
                        {{ $country['name'] }}
                    </option>
                @endforeach
            </select>

            <button onclick="showCountryNews()" class="btn btn-primary w-100">
                Analisis Risiko Berita
            </button>

            <div class="alert alert-info mt-3 mb-0">
                Total negara tersedia:
                <b>{{ count($countries) }}</b>
            </div>
        </div>

        <div class="card sg-card p-4 mt-4">
            <h5 class="fw-bold mb-3">Negara Terpilih</h5>

            <div class="d-flex align-items-center gap-3 mb-3">
                <img
                    id="countryFlag"
                    src=""
                    alt="Bendera"
                    style="width: 70px; border-radius: 8px; display: none;"
                >

                <div>
                    <h5 id="countryName" class="fw-bold mb-0">-</h5>
                    <small id="countryRegion" class="text-muted">-</small>
                </div>
            </div>

            <table class="table align-middle mb-0">
                <tbody>
                    <tr>
                        <td>Ibu Kota</td>
                        <td id="countryCapital" class="fw-bold text-end">-</td>
                    </tr>

                    <tr>
                        <td>Kode Negara</td>
                        <td id="countryCode" class="fw-bold text-end">-</td>
                    </tr>

                    <tr>
                        <td>Sumber Data</td>
                        <td id="dataSource" class="fw-bold text-end">
                            Simulasi GNews API
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-3">Hasil Intelijen Berita</h5>

            <div class="border rounded p-3 mb-3">
                <small class="text-muted">Judul Berita</small>
                <h5 id="newsTitle" class="fw-bold mb-0">-</h5>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Kategori Berita</small>
                        <h5 id="newsCategory" class="fw-bold mb-0">-</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Sentimen</small>
                        <h5 id="sentimentResult" class="fw-bold mb-0">-</h5>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="border rounded p-3">
                        <small class="text-muted">Kata Positif</small>
                        <h5 id="positiveCount" class="fw-bold mb-0">-</h5>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="border rounded p-3">
                        <small class="text-muted">Kata Negatif</small>
                        <h5 id="negativeCount" class="fw-bold mb-0">-</h5>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="border rounded p-3">
                        <small class="text-muted">Risiko Berita</small>
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
            <h5 class="fw-bold mb-3">Komponen Sentimen Berita</h5>
            <canvas id="newsChart" height="130"></canvas>
        </div>
    </div>
</div>

<div class="card sg-card p-4 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold mb-0">Pratinjau Berita Seluruh Negara</h5>

            <small class="text-muted">
                Pratinjau berita untuk beberapa negara.
                Semua negara tetap tersedia pada daftar pilihan.
            </small>
        </div>

        <button class="btn btn-primary btn-sm" onclick="showCountryNews()">
            Perbarui Berita
        </button>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Negara</th>
                    <th>Judul</th>
                    <th>Kategori</th>
                    <th>Sentimen</th>
                    <th>Risiko</th>
                    <th>Status</th>
                    <th>Rekomendasi</th>
                </tr>
            </thead>

            <tbody>
                @foreach(array_slice($countries, 0, 25) as $index => $country)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $country['name'] }}</td>

                        <td>
                            <div class="fw-semibold">
                                {{ $country['news_title'] }}
                            </div>

                            <small class="text-muted">
                                Sumber: {{ $country['source'] }}
                            </small>
                        </td>

                        <td>
                            {{
                                [
                                    'Logistics' => 'Logistik',
                                    'Shipping' => 'Pengiriman',
                                    'Trade' => 'Perdagangan',
                                    'Economy' => 'Ekonomi',
                                    'Geopolitics' => 'Geopolitik',
                                    'Currency' => 'Mata Uang',
                                    'Weather' => 'Cuaca',
                                    'Port' => 'Pelabuhan'
                                ][$country['news_category']] ?? $country['news_category']
                            }}
                        </td>

                        <td>
                            {{
                                [
                                    'Positive' => 'Positif',
                                    'Neutral' => 'Netral',
                                    'Negative' => 'Negatif'
                                ][$country['sentiment']] ?? $country['sentiment']
                            }}
                        </td>

                        <td class="fw-bold">
                            {{ $country['news_risk'] }}
                        </td>

                        <td>
                            <span class="badge-soft {{ $country['badge'] }}">
                                {{
                                    [
                                        'Low' => 'Rendah',
                                        'Medium' => 'Sedang',
                                        'High' => 'Tinggi',
                                        'Critical' => 'Kritis'
                                    ][$country['risk_category']] ?? $country['risk_category']
                                }}
                            </span>
                        </td>

                        <td>
                            {{
                                [
                                    'News condition is positive and supports supply chain stability.'
                                        => 'Kondisi berita positif dan mendukung kestabilan rantai pasok.',

                                    'News condition is neutral. Continue monitoring current developments.'
                                        => 'Kondisi berita netral. Lanjutkan pemantauan perkembangan terbaru.',

                                    'Negative news may increase supply chain risk.'
                                        => 'Berita negatif dapat meningkatkan risiko rantai pasok.',

                                    'Prepare an alternative supplier country and monitor geopolitical developments.'
                                        => 'Siapkan negara pemasok alternatif dan pantau perkembangan geopolitik.'
                                ][$country['recommendation']] ?? $country['recommendation']
                            }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <small class="text-muted">
        Tabel ini menampilkan 25 negara pertama sebagai pratinjau.
        Semua negara tetap tersedia di daftar pilihan negara.
    </small>
</div>

<div class="row g-4 mt-1">
    <div class="col-lg-7">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-3">Analisis Sentimen Berbasis Leksikon</h5>

            <p class="text-muted">
                Sistem menghitung kata positif dan negatif dari judul berita.
                Jika kata negatif lebih banyak, berita dikategorikan sebagai Negatif.
                Jika kata positif lebih banyak, berita dikategorikan sebagai Positif.
                Jika jumlahnya seimbang, hasilnya Netral.
            </p>

            <div class="alert alert-info mb-0">
                Contoh: berita yang mengandung kata
                <b>delay</b>, <b>crisis</b>, <b>inflation</b>, atau <b>conflict</b>
                akan meningkatkan risiko sentimen berita.
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-3">Contoh Kamus Sentimen</h5>

            <div class="border rounded p-3 mb-3">
                <h6 class="fw-bold text-success">Kata Positif</h6>

                <p class="mb-0">
                    {{ implode(', ', $positiveWords) }}
                </p>
            </div>

            <div class="border rounded p-3">
                <h6 class="fw-bold text-danger">Kata Negatif</h6>

                <p class="mb-0">
                    {{ implode(', ', $negativeWords) }}
                </p>
            </div>
        </div>
    </div>
</div>

<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">Perhitungan Risiko Berita</h5>

    <p class="text-muted mb-2">
        Risiko berita dihitung dari hasil analisis sentimen.
        Berita negatif akan meningkatkan risiko rantai pasok,
        sedangkan berita positif menurunkan tingkat risiko.
    </p>

    <div class="alert alert-info mb-0">
        Risiko Sentimen Berita:
        berita positif menghasilkan risiko rendah,
        berita netral menghasilkan risiko sedang,
        dan berita negatif menghasilkan risiko tinggi.
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

    function translateSentiment(sentiment) {
        const translations = {
            Positive: 'Positif',
            Neutral: 'Netral',
            Negative: 'Negatif'
        };

        return translations[sentiment] ?? sentiment;
    }

    function translateRiskCategory(category) {
        const translations = {
            Low: 'Rendah',
            Medium: 'Sedang',
            High: 'Tinggi',
            Critical: 'Kritis'
        };

        return translations[category] ?? category;
    }

    function translateNewsCategory(category) {
        const translations = {
            Logistics: 'Logistik',
            Shipping: 'Pengiriman',
            Trade: 'Perdagangan',
            Economy: 'Ekonomi',
            Geopolitics: 'Geopolitik',
            Currency: 'Mata Uang',
            Weather: 'Cuaca',
            Port: 'Pelabuhan'
        };

        return translations[category] ?? category;
    }

    function translateRecommendation(recommendation) {
        const translations = {
            'News condition is positive and supports supply chain stability.':
                'Kondisi berita positif dan mendukung kestabilan rantai pasok.',

            'News condition is neutral. Continue monitoring current developments.':
                'Kondisi berita netral. Lanjutkan pemantauan perkembangan terbaru.',

            'Negative news may increase supply chain risk.':
                'Berita negatif dapat meningkatkan risiko rantai pasok.',

            'Prepare an alternative supplier country and monitor geopolitical developments.':
                'Siapkan negara pemasok alternatif dan pantau perkembangan geopolitik.'
        };

        return translations[recommendation] ?? recommendation;
    }

    function translateSource(source) {
        if (source === 'GNews API Simulation') {
            return 'Simulasi GNews API';
        }

        return source;
    }

    function showCountryNews() {
        const selectedIndex =
            document.getElementById('countrySelect').value;

        const country = countries[selectedIndex];

        document.getElementById('countryName').innerText =
            country.name ?? '-';

        document.getElementById('countryRegion').innerText =
            (country.region ?? '-') +
            ' / ' +
            (country.subregion ?? '-');

        document.getElementById('countryCapital').innerText =
            country.capital ?? '-';

        document.getElementById('countryCode').innerText =
            country.code ?? '-';

        document.getElementById('dataSource').innerText =
            translateSource(country.source ?? 'GNews API Simulation');

        document.getElementById('newsTitle').innerText =
            country.news_title ?? '-';

        document.getElementById('newsCategory').innerText =
            translateNewsCategory(country.news_category ?? '-');

        document.getElementById('sentimentResult').innerText =
            translateSentiment(country.sentiment ?? '-');

        document.getElementById('positiveCount').innerText =
            country.positive_count ?? 0;

        document.getElementById('negativeCount').innerText =
            country.negative_count ?? 0;

        document.getElementById('newsRisk').innerText =
            country.news_risk ?? '-';

        const riskCategory =
            document.getElementById('riskCategory');

        riskCategory.className =
            'badge-soft ' + country.badge;

        riskCategory.innerText =
            'Risiko ' +
            translateRiskCategory(country.risk_category);

        const recommendationBox =
            document.getElementById('recommendationBox');

        recommendationBox.innerText =
            translateRecommendation(country.recommendation);

        recommendationBox.className =
            'alert alert-primary mt-4 mb-0';

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

        newsChart = new Chart(
            document.getElementById('newsChart'),
            {
                type: 'bar',
                data: {
                    labels: [
                        'Kata Positif',
                        'Kata Negatif',
                        'Risiko Berita'
                    ],
                    datasets: [{
                        label: 'Analisis Sentimen Berita',
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
            }
        );
    }

    document.addEventListener('DOMContentLoaded', function () {
        showCountryNews();
    });
</script>
@endpush