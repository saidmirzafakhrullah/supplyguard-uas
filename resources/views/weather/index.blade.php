@extends('layouts.app')

@section('title', 'Weather Monitoring - SupplyGuard')
@section('page-title', 'Global Weather Monitoring')

@section('content')
<div class="card sg-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold mb-1">Global Weather Monitoring</h4>
            <p class="text-muted mb-0">
                Monitoring cuaca global untuk melihat potensi gangguan pengiriman akibat hujan,
                badai, suhu ekstrem, dan angin kencang.
            </p>
        </div>

        <span class="badge bg-info text-dark">Open-Meteo API</span>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Temperature</small>
            <h3 class="fw-bold">29°C</h3>
            <span class="badge-soft risk-low">Normal</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Rainfall</small>
            <h3 class="fw-bold">12 mm</h3>
            <span class="badge-soft risk-medium">Moderate</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Wind Speed</small>
            <h3 class="fw-bold">24 km/h</h3>
            <span class="badge-soft risk-low">Safe</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Storm Risk</small>
            <h3 class="fw-bold">18%</h3>
            <span class="badge-soft risk-low">Low</span>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-lg-5">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-3">Country Weather Selector</h5>

            <label class="form-label">Country</label>
            <select class="form-select mb-3">
                <option>Indonesia</option>
                <option>China</option>
                <option>Germany</option>
                <option>Australia</option>
                <option>Japan</option>
                <option>Singapore</option>
            </select>

            <label class="form-label">Logistics Area</label>
            <select class="form-select mb-3">
                <option>Port Area</option>
                <option>Warehouse Area</option>
                <option>Shipping Route</option>
                <option>Distribution Center</option>
            </select>

            <button class="btn btn-primary w-100">
                Check Weather Risk
            </button>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-3">Weather Risk Rules</h5>

            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Condition</th>
                        <th>Risk Impact</th>
                        <th>Business Effect</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Wind speed lebih dari 40 km/h</td>
                        <td><span class="badge-soft risk-high">High Risk</span></td>
                        <td>Pengiriman laut dapat tertunda.</td>
                    </tr>
                    <tr>
                        <td>Rainfall lebih dari 20 mm</td>
                        <td><span class="badge-soft risk-medium">Medium Risk</span></td>
                        <td>Distribusi darat perlu dipantau.</td>
                    </tr>
                    <tr>
                        <td>Temperature lebih dari 38°C</td>
                        <td><span class="badge-soft risk-medium">Medium Risk</span></td>
                        <td>Barang sensitif suhu perlu perlakuan khusus.</td>
                    </tr>
                    <tr>
                        <td>Normal weather</td>
                        <td><span class="badge-soft risk-low">Low Risk</span></td>
                        <td>Aktivitas impor aman berjalan.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">Weather Risk Summary</h5>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Country</th>
                    <th>Temperature</th>
                    <th>Rainfall</th>
                    <th>Wind</th>
                    <th>Weather Risk</th>
                    <th>Recommendation</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Indonesia</td>
                    <td>29°C</td>
                    <td>12 mm</td>
                    <td>24 km/h</td>
                    <td><span class="badge-soft risk-medium">Medium</span></td>
                    <td>Monitor rainfall before shipment.</td>
                </tr>
                <tr>
                    <td>Germany</td>
                    <td>18°C</td>
                    <td>4 mm</td>
                    <td>15 km/h</td>
                    <td><span class="badge-soft risk-low">Low</span></td>
                    <td>Safe for import route.</td>
                </tr>
                <tr>
                    <td>China</td>
                    <td>31°C</td>
                    <td>22 mm</td>
                    <td>38 km/h</td>
                    <td><span class="badge-soft risk-high">High</span></td>
                    <td>Prepare alternative shipping schedule.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection