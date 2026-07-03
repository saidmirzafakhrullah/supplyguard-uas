@extends('layouts.app')

@section('title', 'Country Comparison - SupplyGuard')
@section('page-title', 'Country Comparison Engine')

@section('content')
<div class="card sg-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold mb-1">Country Comparison Engine</h4>
            <p class="text-muted mb-0">
                Membandingkan dua negara berdasarkan GDP, inflasi, cuaca, kurs, risk score,
                berita, dan pelabuhan untuk membantu keputusan impor.
            </p>
        </div>

        <span class="badge bg-primary">Decision Support System</span>
    </div>
</div>

<div class="card sg-card p-4 mb-4">
    <h5 class="fw-bold mb-3">Compare Countries</h5>

    <div class="row g-3">
        <div class="col-md-5">
            <label class="form-label">Country 1</label>
            <select class="form-select">
                <option>Indonesia</option>
                <option>Germany</option>
                <option>China</option>
                <option>Australia</option>
                <option>Japan</option>
                <option>Singapore</option>
            </select>
        </div>

        <div class="col-md-5">
            <label class="form-label">Country 2</label>
            <select class="form-select">
                <option>Germany</option>
                <option>Indonesia</option>
                <option>China</option>
                <option>Australia</option>
                <option>Japan</option>
                <option>Singapore</option>
            </select>
        </div>

        <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-primary w-100">
                Compare
            </button>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card sg-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold mb-0">Indonesia</h5>
                    <small class="text-muted">Country 1</small>
                </div>

                <span class="badge-soft risk-medium">Medium Risk</span>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">GDP Score</small>
                        <h5 class="mb-0 text-success">78</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Inflation Risk</small>
                        <h5 class="mb-0 text-warning">35</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Weather Risk</small>
                        <h5 class="mb-0 text-warning">20</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Currency Risk</small>
                        <h5 class="mb-0 text-warning">30</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">News Risk</small>
                        <h5 class="mb-0 text-danger">45</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Total Risk</small>
                        <h5 class="mb-0 text-warning">30.75</h5>
                    </div>
                </div>
            </div>

            <div class="alert alert-warning mt-4 mb-0">
                Indonesia masih layak untuk impor, tetapi perlu monitoring cuaca dan kurs.
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card sg-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold mb-0">Germany</h5>
                    <small class="text-muted">Country 2</small>
                </div>

                <span class="badge-soft risk-low">Low Risk</span>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">GDP Score</small>
                        <h5 class="mb-0 text-success">88</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Inflation Risk</small>
                        <h5 class="mb-0 text-success">18</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Weather Risk</small>
                        <h5 class="mb-0 text-success">12</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Currency Risk</small>
                        <h5 class="mb-0 text-success">20</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">News Risk</small>
                        <h5 class="mb-0 text-success">15</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Total Risk</small>
                        <h5 class="mb-0 text-success">15.85</h5>
                    </div>
                </div>
            </div>

            <div class="alert alert-success mt-4 mb-0">
                Germany lebih direkomendasikan karena risiko lebih rendah dan indikator lebih stabil.
            </div>
        </div>
    </div>
</div>

<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">Comparison Result Table</h5>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Indicator</th>
                    <th>Indonesia</th>
                    <th>Germany</th>
                    <th>Better Country</th>
                    <th>Reason</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td>GDP Score</td>
                    <td>78</td>
                    <td>88</td>
                    <td><span class="badge bg-success">Germany</span></td>
                    <td>GDP score lebih tinggi.</td>
                </tr>

                <tr>
                    <td>Inflation Risk</td>
                    <td>35</td>
                    <td>18</td>
                    <td><span class="badge bg-success">Germany</span></td>
                    <td>Risiko inflasi lebih rendah.</td>
                </tr>

                <tr>
                    <td>Weather Risk</td>
                    <td>20</td>
                    <td>12</td>
                    <td><span class="badge bg-success">Germany</span></td>
                    <td>Cuaca lebih stabil.</td>
                </tr>

                <tr>
                    <td>Currency Risk</td>
                    <td>30</td>
                    <td>20</td>
                    <td><span class="badge bg-success">Germany</span></td>
                    <td>Kurs lebih stabil.</td>
                </tr>

                <tr>
                    <td>News Risk</td>
                    <td>45</td>
                    <td>15</td>
                    <td><span class="badge bg-success">Germany</span></td>
                    <td>Berita negatif lebih sedikit.</td>
                </tr>

                <tr>
                    <td>Total Risk</td>
                    <td>30.75</td>
                    <td>15.85</td>
                    <td><span class="badge-soft risk-low">Germany Recommended</span></td>
                    <td>Lebih aman untuk aktivitas impor.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">Final Business Recommendation</h5>

    <div class="alert alert-success mb-0">
        Berdasarkan hasil perbandingan, <b>Germany</b> lebih direkomendasikan sebagai negara impor
        karena memiliki risk score lebih rendah, inflasi lebih stabil, cuaca lebih aman,
        dan sentimen berita lebih positif.
    </div>
</div>
@endsection