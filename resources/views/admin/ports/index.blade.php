@extends('layouts.app')

@section('title', 'Manage Ports - SupplyGuard')
@section('page-title', 'Admin Management - Manage Ports')

@section('content')

{{-- HEADER --}}
<div class="card sg-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold mb-1">Manage Ports</h4>
            <p class="text-muted mb-0">
                Halaman admin untuk memantau dataset pelabuhan dunia yang digunakan
                pada sistem risiko rantai pasok.
            </p>
        </div>

        <span class="badge bg-primary">Port Dataset</span>
    </div>
</div>

{{-- SUMMARY --}}
<div class="row g-4">
    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Total Countries</small>
            <h3 class="fw-bold">{{ $summary['total_countries'] }}</h3>
            <span class="badge bg-primary">All Countries</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Total Ports Data</small>
            <h3 class="fw-bold">{{ $summary['total_ports'] }}</h3>
            <span class="badge bg-success">Dataset</span>
        </div>
    </div>

    <div class="col-md-2">
        <div class="card sg-card p-4">
            <small class="text-muted">Active Ports</small>
            <h3 class="fw-bold text-success">{{ $summary['active_ports'] }}</h3>
            <span class="badge bg-success">Active</span>
        </div>
    </div>

    <div class="col-md-2">
        <div class="card sg-card p-4">
            <small class="text-muted">Limited Ports</small>
            <h3 class="fw-bold text-warning">{{ $summary['limited_ports'] }}</h3>
            <span class="badge bg-warning text-dark">Limited</span>
        </div>
    </div>

    <div class="col-md-2">
        <div class="card sg-card p-4">
            <small class="text-muted">No Seaport</small>
            <h3 class="fw-bold text-danger">{{ $summary['no_seaport'] }}</h3>
            <span class="badge bg-danger">Landlocked</span>
        </div>
    </div>
</div>

{{-- CHART + INFO --}}
<div class="row g-4 mt-1">
    <div class="col-lg-5">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-1">Port Status Distribution</h5>
            <small class="text-muted">
                Distribusi status data pelabuhan global.
            </small>

            <div class="mt-3" style="height: 300px;">
                <canvas id="portStatusChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-3">Port Dataset Information</h5>

            <div class="alert alert-info">
                Menu ini digunakan admin untuk memantau dataset pelabuhan yang dipakai
                pada fitur Port Location Dashboard dan analisis risiko logistik.
            </div>

            <table class="table align-middle mb-0">
                <tbody>
                    <tr>
                        <td>Dataset Type</td>
                        <td class="fw-bold text-end">World Port Index / Public Port Dataset</td>
                    </tr>

                    <tr>
                        <td>Used For</td>
                        <td class="fw-bold text-end">Supply Chain Risk Monitoring</td>
                    </tr>

                    <tr>
                        <td>Port Indicator</td>
                        <td class="fw-bold text-end">Capacity, Congestion, Risk Level</td>
                    </tr>

                    <tr>
                        <td>Admin Function</td>
                        <td class="fw-bold text-end">Port Dataset Management</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- PORT TABLE --}}
<div class="card sg-card p-4 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold mb-1">Ports Dataset</h5>
            <small class="text-muted">
                Daftar data pelabuhan berdasarkan negara.
            </small>
        </div>

        <div style="width: 280px;">
            <input
                type="text"
                id="portSearch"
                class="form-control"
                placeholder="Search port or country..."
            >
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Country</th>
                    <th>Code</th>
                    <th>Region</th>
                    <th>Port Name</th>
                    <th>City</th>
                    <th>Status</th>
                    <th>Capacity</th>
                    <th>Congestion</th>
                    <th>Risk</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                @foreach($ports as $index => $port)
                    <tr
                        class="port-row"
                        data-search="{{ strtolower($port['country'] . ' ' . $port['country_code'] . ' ' . $port['port_name'] . ' ' . $port['city'] . ' ' . $port['region']) }}"
                    >
                        <td>{{ $index + 1 }}</td>
                        <td class="fw-bold">{{ $port['country'] }}</td>
                        <td>{{ $port['country_code'] }}</td>
                        <td>{{ $port['region'] }}</td>
                        <td>{{ $port['port_name'] }}</td>
                        <td>{{ $port['city'] }}</td>

                        <td>
                            @if($port['status'] === 'Active')
                                <span class="badge bg-success">Active</span>
                            @elseif($port['status'] === 'Limited')
                                <span class="badge bg-warning text-dark">Limited</span>
                            @else
                                <span class="badge bg-danger">No Seaport</span>
                            @endif
                        </td>

                        <td>{{ $port['capacity'] }}</td>

                        <td>
                            @if($port['congestion_level'] === 'High')
                                <span class="badge bg-danger">High</span>
                            @elseif($port['congestion_level'] === 'Medium')
                                <span class="badge bg-warning text-dark">Medium</span>
                            @else
                                <span class="badge bg-success">{{ $port['congestion_level'] }}</span>
                            @endif
                        </td>

                        <td>
                            @if($port['risk_level'] === 'High')
                                <span class="badge bg-danger">High</span>
                            @elseif($port['risk_level'] === 'Medium')
                                <span class="badge bg-warning text-dark">Medium</span>
                            @else
                                <span class="badge bg-success">Low</span>
                            @endif
                        </td>

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

    <small class="text-muted">
        Data ini digunakan sebagai dataset pelabuhan untuk analisis risiko logistik dan rantai pasok global.
    </small>
</div>

{{-- EXPLANATION --}}
<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">Manage Ports Explanation</h5>

    <p class="text-muted mb-2">
        Fitur Manage Ports digunakan admin untuk memantau dataset pelabuhan dunia.
        Dataset ini mendukung fitur Port Location Dashboard, analisis kemacetan pelabuhan,
        dan risiko keterlambatan pengiriman barang impor.
    </p>

    <div class="alert alert-info mb-0">
        Jika negara tidak memiliki pelabuhan langsung atau bersifat landlocked,
        sistem menandainya sebagai No Seaport karena membutuhkan akses pelabuhan
        dari negara tetangga.
    </div>
</div>

@endsection

@push('scripts')
<script>
    const activePorts = {{ $summary['active_ports'] }};
    const limitedPorts = {{ $summary['limited_ports'] }};
    const noSeaport = {{ $summary['no_seaport'] }};

    new Chart(document.getElementById('portStatusChart'), {
        type: 'doughnut',
        data: {
            labels: ['Active', 'Limited', 'No Seaport'],
            datasets: [{
                data: [activePorts, limitedPorts, noSeaport]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    document.getElementById('portSearch').addEventListener('keyup', function () {
        const keyword = this.value.toLowerCase();
        const rows = document.querySelectorAll('.port-row');

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
@endpus