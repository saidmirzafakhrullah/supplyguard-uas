@extends('layouts.app')

@section('title', 'Data Visualization - SupplyGuard')
@section('page-title', 'Data Visualization Dashboard')

@section('content')
<div class="card sg-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold mb-1">Data Visualization Dashboard</h4>
            <p class="text-muted mb-0">
                Visualisasi data GDP, inflasi, kurs mata uang, cuaca, berita, dan risk score.
            </p>
        </div>

        <span class="badge bg-primary">Chart.js</span>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">GDP Score</small>
            <h3 class="fw-bold text-success">80</h3>
            <span class="badge-soft risk-low">Strong</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Inflation Risk</small>
            <h3 class="fw-bold text-warning">35</h3>
            <span class="badge-soft risk-medium">Medium</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Currency Risk</small>
            <h3 class="fw-bold text-warning">45</h3>
            <span class="badge-soft risk-medium">Watch</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">News Risk</small>
            <h3 class="fw-bold text-danger">50</h3>
            <span class="badge-soft risk-high">Alert</span>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-lg-6">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-3">Indicator Score</h5>
            <canvas id="indicatorChart" height="160"></canvas>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-3">Risk Trend</h5>
            <canvas id="riskTrendChart" height="160"></canvas>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-lg-6">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-3">News Sentiment Distribution</h5>
            <canvas id="sentimentChart" height="160"></canvas>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-3">Country Risk Comparison</h5>
            <canvas id="countryRiskChart" height="160"></canvas>
        </div>
    </div>
</div>

<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">Visualization Summary</h5>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Indicator</th>
                    <th>Value</th>
                    <th>Status</th>
                    <th>Business Meaning</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td>GDP Score</td>
                    <td>80</td>
                    <td><span class="badge-soft risk-low">Good</span></td>
                    <td>Ekonomi negara cukup kuat untuk aktivitas perdagangan.</td>
                </tr>

                <tr>
                    <td>Inflation Risk</td>
                    <td>35</td>
                    <td><span class="badge-soft risk-medium">Medium</span></td>
                    <td>Biaya produksi dan impor perlu dipantau.</td>
                </tr>

                <tr>
                    <td>Currency Risk</td>
                    <td>45</td>
                    <td><span class="badge-soft risk-medium">Medium</span></td>
                    <td>Perubahan kurs dapat mempengaruhi biaya impor.</td>
                </tr>

                <tr>
                    <td>News Risk</td>
                    <td>50</td>
                    <td><span class="badge-soft risk-high">High</span></td>
                    <td>Berita negatif dapat meningkatkan risiko supply chain.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    new Chart(document.getElementById('indicatorChart'), {
        type: 'bar',
        data: {
            labels: ['GDP', 'Inflation', 'Currency', 'Weather', 'News'],
            datasets: [{
                label: 'Indicator Score',
                data: [80, 35, 45, 25, 50],
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

    new Chart(document.getElementById('riskTrendChart'), {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Risk Trend',
                data: [30, 38, 42, 35, 45, 40],
                borderWidth: 2,
                tension: 0.4
            }]
        },
        options: {
            responsive: true
        }
    });

    new Chart(document.getElementById('sentimentChart'), {
        type: 'doughnut',
        data: {
            labels: ['Positive', 'Neutral', 'Negative'],
            datasets: [{
                data: [60, 25, 15]
            }]
        },
        options: {
            responsive: true
        }
    });

    new Chart(document.getElementById('countryRiskChart'), {
        type: 'bar',
        data: {
            labels: ['Indonesia', 'China', 'Germany', 'Australia', 'Japan'],
            datasets: [{
                label: 'Risk Score',
                data: [30.75, 44.60, 15.85, 23.45, 35.20],
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
</script>
@endpush