@extends('layouts.app')

@section('title', 'Favorite Monitoring - SupplyGuard')
@section('page-title', 'Favorite Monitoring List')

@section('content')
<div class="card sg-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold mb-1">Favorite Monitoring List</h4>
            <p class="text-muted mb-0">
                User dapat menyimpan negara favorit yang sering dipantau untuk monitoring risiko supply chain.
            </p>
        </div>

        <span class="badge bg-warning text-dark">Watchlist</span>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Total Watchlist</small>
            <h3 class="fw-bold">4</h3>
            <span class="badge bg-primary">Countries</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Low Risk</small>
            <h3 class="fw-bold text-success">2</h3>
            <span class="badge-soft risk-low">Safe Route</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Medium Risk</small>
            <h3 class="fw-bold text-warning">2</h3>
            <span class="badge-soft risk-medium">Watch Carefully</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">High Risk</small>
            <h3 class="fw-bold text-danger">0</h3>
            <span class="badge-soft risk-low">No Alert</span>
        </div>
    </div>
</div>

<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">Add Country to Watchlist</h5>

    <div class="row g-3">
        <div class="col-md-5">
            <label class="form-label">Country</label>
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
            <label class="form-label">Monitoring Priority</label>
            <select class="form-select">
                <option>High Priority</option>
                <option>Medium Priority</option>
                <option>Low Priority</option>
            </select>
        </div>

        <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-primary w-100">
                Add
            </button>
        </div>
    </div>
</div>

<div class="card sg-card p-4 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold mb-0">My Watchlist</h5>
            <small class="text-muted">
                Daftar negara yang disimpan untuk pemantauan rutin.
            </small>
        </div>

        <button class="btn btn-outline-primary btn-sm">
            Refresh Risk
        </button>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Country</th>
                    <th>Currency</th>
                    <th>Weather Risk</th>
                    <th>Currency Risk</th>
                    <th>News Risk</th>
                    <th>Total Risk</th>
                    <th>Status</th>
                    <th>Recommendation</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td>
                        <div class="fw-semibold">Indonesia</div>
                        <small class="text-muted">Southeast Asia</small>
                    </td>
                    <td>IDR</td>
                    <td>20</td>
                    <td>30</td>
                    <td>45</td>
                    <td class="fw-bold">30.75</td>
                    <td><span class="badge-soft risk-medium">Medium</span></td>
                    <td>Monitor currency and weather.</td>
                    <td>
                        <button class="btn btn-sm btn-outline-danger">Remove</button>
                    </td>
                </tr>

                <tr>
                    <td>
                        <div class="fw-semibold">Germany</div>
                        <small class="text-muted">Europe</small>
                    </td>
                    <td>EUR</td>
                    <td>12</td>
                    <td>20</td>
                    <td>15</td>
                    <td class="fw-bold">15.85</td>
                    <td><span class="badge-soft risk-low">Low</span></td>
                    <td>Safe for import activity.</td>
                    <td>
                        <button class="btn btn-sm btn-outline-danger">Remove</button>
                    </td>
                </tr>

                <tr>
                    <td>
                        <div class="fw-semibold">China</div>
                        <small class="text-muted">East Asia</small>
                    </td>
                    <td>CNY</td>
                    <td>35</td>
                    <td>38</td>
                    <td>60</td>
                    <td class="fw-bold">44.60</td>
                    <td><span class="badge-soft risk-medium">Medium</span></td>
                    <td>Monitor shipping and news risk.</td>
                    <td>
                        <button class="btn btn-sm btn-outline-danger">Remove</button>
                    </td>
                </tr>

                <tr>
                    <td>
                        <div class="fw-semibold">Australia</div>
                        <small class="text-muted">Oceania</small>
                    </td>
                    <td>AUD</td>
                    <td>18</td>
                    <td>25</td>
                    <td>20</td>
                    <td class="fw-bold">23.45</td>
                    <td><span class="badge-soft risk-low">Low</span></td>
                    <td>Recommended import route.</td>
                    <td>
                        <button class="btn btn-sm btn-outline-danger">Remove</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">Watchlist Decision Summary</h5>

    <div class="alert alert-success">
        Germany dan Australia memiliki risiko rendah, sehingga lebih aman untuk aktivitas impor.
    </div>

    <div class="alert alert-warning mb-0">
        Indonesia dan China masih dapat dipantau, tetapi perlu perhatian pada kurs, cuaca,
        dan berita logistik.
    </div>
</div>
@endsection