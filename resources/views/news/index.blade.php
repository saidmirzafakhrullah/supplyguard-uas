@extends('layouts.app')

@section('title', 'News Intelligence - SupplyGuard')
@section('page-title', 'News Intelligence')

@section('content')
<div class="card sg-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold mb-1">News Intelligence</h4>
            <p class="text-muted mb-0">
                Sistem membaca berita ekonomi, logistik, shipping, trade, dan geopolitik
                untuk membantu menghitung risiko negara.
            </p>
        </div>

        <span class="badge bg-warning text-dark">GNews API</span>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Total News</small>
            <h3 class="fw-bold">16</h3>
            <span class="badge bg-primary">Cached Articles</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Positive News</small>
            <h3 class="fw-bold text-success">6</h3>
            <span class="badge-soft risk-low">Positive</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Neutral News</small>
            <h3 class="fw-bold text-warning">5</h3>
            <span class="badge-soft risk-medium">Neutral</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Negative News</small>
            <h3 class="fw-bold text-danger">5</h3>
            <span class="badge-soft risk-high">Negative</span>
        </div>
    </div>
</div>

<div class="card sg-card p-4 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold mb-0">Latest Global Supply Chain News</h5>
            <small class="text-muted">Berita simulasi untuk analisis risiko rantai pasok.</small>
        </div>

        <button class="btn btn-primary btn-sm">
            Refresh News
        </button>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Country</th>
                    <th>Category</th>
                    <th>Sentiment</th>
                    <th>Risk</th>
                    <th>Recommendation</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td>
                        <div class="fw-semibold">Port congestion causes shipping delays</div>
                        <small class="text-muted">Source: Global Logistics Daily</small>
                    </td>
                    <td>China</td>
                    <td>Shipping</td>
                    <td>Negative</td>
                    <td><span class="badge-soft risk-high">High</span></td>
                    <td>Prepare alternative shipping route.</td>
                </tr>

                <tr>
                    <td>
                        <div class="fw-semibold">Export activity improves after currency stabilization</div>
                        <small class="text-muted">Source: Economy Watch</small>
                    </td>
                    <td>Germany</td>
                    <td>Economy</td>
                    <td>Positive</td>
                    <td><span class="badge-soft risk-low">Low</span></td>
                    <td>Safe for import activity.</td>
                </tr>

                <tr>
                    <td>
                        <div class="fw-semibold">Heavy rainfall may disrupt local logistics</div>
                        <small class="text-muted">Source: Weather Trade News</small>
                    </td>
                    <td>Indonesia</td>
                    <td>Weather</td>
                    <td>Neutral</td>
                    <td><span class="badge-soft risk-medium">Medium</span></td>
                    <td>Monitor weather before shipment.</td>
                </tr>

                <tr>
                    <td>
                        <div class="fw-semibold">Fuel prices increase and affect delivery cost</div>
                        <small class="text-muted">Source: Supply Chain Report</small>
                    </td>
                    <td>Australia</td>
                    <td>Transport</td>
                    <td>Negative</td>
                    <td><span class="badge-soft risk-medium">Medium</span></td>
                    <td>Recalculate logistics budget.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-lg-6">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-3">Lexicon Based Sentiment Analysis</h5>

            <p class="text-muted">
                Sistem akan menghitung kata positif dan negatif dari judul atau isi berita.
                Jika kata negatif lebih banyak, maka berita dikategorikan sebagai Negative.
            </p>

            <div class="alert alert-info mb-0">
                Contoh: berita berisi kata <b>delay</b>, <b>crisis</b>, dan <b>inflation</b>
                akan meningkatkan News Sentiment Risk.
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-3">Sentiment Dictionary Example</h5>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <h6 class="fw-bold text-success">Positive Words</h6>
                        <p class="mb-0 text-muted">
                            growth, increase, profit, stable, improve, recovery, strong, safe
                        </p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <h6 class="fw-bold text-danger">Negative Words</h6>
                        <p class="mb-0 text-muted">
                            war, crisis, inflation, delay, disaster, conflict, strike, shortage
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection