@extends('layouts.app')

@section('title', 'Risk Scoring - SupplyGuard')
@section('page-title', 'Risk Scoring Engine')

@section('content')
<div class="card sg-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold mb-1">SG-Risk Weighted Scoring Algorithm</h4>
            <p class="text-muted mb-0">
                Algoritma ini menghitung risiko rantai pasok berdasarkan cuaca, inflasi,
                kurs mata uang, sentimen berita, dan ketersediaan pelabuhan.
            </p>
        </div>

        <span class="badge bg-danger">Risk Engine</span>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-3">Formula Risk Score</h5>

            <table class="table align-middle">
                <tbody>
                    <tr>
                        <td>Weather Risk</td>
                        <td class="fw-bold text-end">30%</td>
                    </tr>
                    <tr>
                        <td>Inflation Risk</td>
                        <td class="fw-bold text-end">20%</td>
                    </tr>
                    <tr>
                        <td>Currency Risk</td>
                        <td class="fw-bold text-end">15%</td>
                    </tr>
                    <tr>
                        <td>News Sentiment Risk</td>
                        <td class="fw-bold text-end">25%</td>
                    </tr>
                    <tr>
                        <td>Port Availability Risk</td>
                        <td class="fw-bold text-end">10%</td>
                    </tr>
                </tbody>
            </table>

            <div class="alert alert-primary mb-0">
                Total Risk Score = semua indikator dikalikan bobot masing-masing.
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-3">Risk Category</h5>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="p-3 rounded risk-low">
                        <b>0 - 25</b><br>
                        Low Risk<br>
                        <small>Aman untuk aktivitas impor.</small>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 rounded risk-medium">
                        <b>26 - 50</b><br>
                        Medium Risk<br>
                        <small>Masih aman, tetapi perlu dipantau.</small>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 rounded risk-high">
                        <b>51 - 75</b><br>
                        High Risk<br>
                        <small>Perlu menyiapkan negara alternatif.</small>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 rounded bg-dark text-white">
                        <b>76 - 100</b><br>
                        Critical Risk<br>
                        <small>Pengiriman sebaiknya ditunda.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">Sample Risk Calculation</h5>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Country</th>
                    <th>Weather</th>
                    <th>Inflation</th>
                    <th>Currency</th>
                    <th>News</th>
                    <th>Port</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Recommendation</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Indonesia</td>
                    <td>20</td>
                    <td>35</td>
                    <td>30</td>
                    <td>45</td>
                    <td>10</td>
                    <td class="fw-bold">30.75</td>
                    <td><span class="badge-soft risk-medium">Medium</span></td>
                    <td>Monitor currency and weather.</td>
                </tr>

                <tr>
                    <td>Germany</td>
                    <td>12</td>
                    <td>18</td>
                    <td>20</td>
                    <td>15</td>
                    <td>8</td>
                    <td class="fw-bold">15.85</td>
                    <td><span class="badge-soft risk-low">Low</span></td>
                    <td>Safe for import activity.</td>
                </tr>

                <tr>
                    <td>China</td>
                    <td>35</td>
                    <td>42</td>
                    <td>38</td>
                    <td>60</td>
                    <td>25</td>
                    <td class="fw-bold">44.60</td>
                    <td><span class="badge-soft risk-medium">Medium</span></td>
                    <td>Monitor shipping and news risk.</td>
                </tr>

                <tr>
                    <td>Australia</td>
                    <td>18</td>
                    <td>28</td>
                    <td>25</td>
                    <td>20</td>
                    <td>12</td>
                    <td class="fw-bold">23.45</td>
                    <td><span class="badge-soft risk-low">Low</span></td>
                    <td>Recommended.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">Business Decision Output</h5>

    <div class="row g-3">
        <div class="col-md-3">
            <div class="border rounded p-3">
                <small class="text-muted">Low Risk</small>
                <h6 class="mb-0 text-success">Safe Route</h6>
            </div>
        </div>

        <div class="col-md-3">
            <div class="border rounded p-3">
                <small class="text-muted">Medium Risk</small>
                <h6 class="mb-0 text-warning">Watch Carefully</h6>
            </div>
        </div>

        <div class="col-md-3">
            <div class="border rounded p-3">
                <small class="text-muted">High Risk</small>
                <h6 class="mb-0 text-danger">Prepare Alternative</h6>
            </div>
        </div>

        <div class="col-md-3">
            <div class="border rounded p-3">
                <small class="text-muted">Critical Risk</small>
                <h6 class="mb-0 text-dark">Delay Import</h6>
            </div>
        </div>
    </div>
</div>
@endsection