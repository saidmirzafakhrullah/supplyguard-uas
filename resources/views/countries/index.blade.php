@extends('layouts.app')

@section('title', 'Global Country - SupplyGuard')
@section('page-title', 'Global Country Dashboard')

@section('content')
<div class="card sg-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold mb-1">Global Country Dashboard</h4>
            <p class="text-muted mb-0">
                Monitoring data negara, ekonomi, populasi, mata uang, dan indikator risiko supply chain.
            </p>
        </div>

        <span class="badge bg-primary">REST Countries + World Bank</span>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card sg-card p-4">
            <h5 class="fw-bold">Pilih Negara</h5>
            <p class="text-muted">
                Pilih negara yang ingin dipantau untuk melihat profil negara dan indikator risiko.
            </p>

            <label class="form-label">Country</label>
            <select class="form-select mb-3">
                <option>Indonesia</option>
                <option>China</option>
                <option>Germany</option>
                <option>Australia</option>
                <option>Japan</option>
                <option>Singapore</option>
            </select>

            <button class="btn btn-primary w-100">
                Tampilkan Data
            </button>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-3">Country Profile</h5>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Country</small>
                        <h5 class="mb-0">Indonesia</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Capital</small>
                        <h5 class="mb-0">Jakarta</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Currency</small>
                        <h5 class="mb-0">IDR - Indonesian Rupiah</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Region</small>
                        <h5 class="mb-0">Southeast Asia</h5>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="border rounded p-3">
                        <small class="text-muted">GDP Score</small>
                        <h5 class="mb-0 text-success">78</h5>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="border rounded p-3">
                        <small class="text-muted">Inflation Risk</small>
                        <h5 class="mb-0 text-warning">35</h5>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="border rounded p-3">
                        <small class="text-muted">Import Status</small>
                        <h5 class="mb-0 text-primary">Monitoring</h5>
                    </div>
                </div>
            </div>

            <div class="alert alert-info mt-4 mb-0">
                Nantinya halaman ini akan mengambil data dari REST Countries API, World Bank API,
                dan Open-Meteo API sesuai kebutuhan project UAS.
            </div>
        </div>
    </div>
</div>
@endsection