@extends('layouts.app')

@section('title', 'Port Location - SupplyGuard')
@section('page-title', 'Port Location Dashboard')

@section('content')

{{-- HEADER --}}
<div class="card sg-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold mb-1">Port Location Dashboard</h4>
            <p class="text-muted mb-0">
                Monitoring lokasi pelabuhan, status port, dan risiko jalur pengiriman untuk semua negara.
            </p>
        </div>

        <span class="badge bg-success">Leaflet.js Map</span>
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
            <small class="text-muted">Available Ports</small>
            <h3 class="fw-bold text-success">{{ $summary['available_ports'] }}</h3>
            <span class="badge-soft risk-low">Available</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">Limited Ports</small>
            <h3 class="fw-bold text-warning">{{ $summary['limited_ports'] }}</h3>
            <span class="badge-soft risk-medium">Limited</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card sg-card p-4">
            <small class="text-muted">No Seaport</small>
            <h3 class="fw-bold text-danger">{{ $summary['no_seaport'] }}</h3>
            <span class="badge-soft risk-high">Landlocked</span>
        </div>
    </div>
</div>

{{-- SELECTOR DAN HASIL --}}
<div class="row g-4 mt-1">
    <div class="col-lg-5">

        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-3">Country Port Selector</h5>

            <label class="form-label">Country</label>
            <select id="countrySelect" class="form-select mb-3">
                @foreach($countries as $index => $country)
                    <option value="{{ $index }}">
                        {{ $country['name'] }}
                    </option>
                @endforeach
            </select>

            <button onclick="showPortLocation()" class="btn btn-primary w-100">
                Show Port Location
            </button>

            <div class="alert alert-info mt-3 mb-0">
                Total negara tersedia:
                <b>{{ count($countries) }}</b>
            </div>
        </div>

        <div class="card sg-card p-4 mt-4">
            <h5 class="fw-bold mb-3">Selected Country</h5>

            <div class="d-flex align-items-center gap-3 mb-3">
                <img
                    id="countryFlag"
                    src=""
                    alt="Flag"
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
                        <td>Capital</td>
                        <td id="countryCapital" class="fw-bold text-end">-</td>
                    </tr>

                    <tr>
                        <td>Country Code</td>
                        <td id="countryCode" class="fw-bold text-end">-</td>
                    </tr>

                    <tr>
                        <td>Landlocked</td>
                        <td id="landlockedStatus" class="fw-bold text-end">-</td>
                    </tr>

                    <tr>
                        <td>Data Source</td>
                        <td class="fw-bold text-end">World Port Dataset Simulation</td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>

    <div class="col-lg-7">

        <div class="card sg-card p-4">
            <h5 class="fw-bold mb-3">Port Location Result</h5>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Port Name</small>
                        <h5 id="portName" class="fw-bold mb-0">-</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Port City</small>
                        <h5 id="portCity" class="fw-bold mb-0">-</h5>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="border rounded p-3">
                        <small class="text-muted">Port Count</small>
                        <h5 id="portCount" class="fw-bold mb-0">-</h5>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="border rounded p-3">
                        <small class="text-muted">Port Status</small>
                        <h5 id="portStatus" class="fw-bold mb-0">-</h5>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="border rounded p-3">
                        <small class="text-muted">Port Risk</small>
                        <h5 id="portRisk" class="fw-bold mb-0">-</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Latitude</small>
                        <h5 id="portLatitude" class="fw-bold mb-0">-</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Longitude</small>
                        <h5 id="portLongitude" class="fw-bold mb-0">-</h5>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <span id="riskCategory" class="badge-soft risk-low">-</span>
            </div>

            <div id="recommendationBox" class="alert alert-primary mt-4 mb-0">
                Pilih negara untuk melihat lokasi port.
            </div>
        </div>

    </div>
</div>

{{-- MAP --}}
<div class="card sg-card p-4 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold mb-0">Interactive Port Map</h5>
            <small class="text-muted">
                Peta lokasi port menggunakan Leaflet.js dan OpenStreetMap.
            </small>
        </div>

        <span class="badge bg-success">OpenStreetMap</span>
    </div>

    <div id="portMap" style="height: 420px; border-radius: 16px;"></div>
</div>

{{-- RULES --}}
<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">Port Risk Rules</h5>

    <div class="table-responsive">
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
                    <td>Pelabuhan tersedia dan aktif</td>
                    <td>
                        <span class="badge-soft risk-low">Low Risk</span>
                    </td>
                    <td>Aktivitas impor dapat berjalan normal.</td>
                </tr>

                <tr>
                    <td>Jumlah pelabuhan terbatas</td>
                    <td>
                        <span class="badge-soft risk-medium">Medium Risk</span>
                    </td>
                    <td>Perlu memantau kapasitas pelabuhan.</td>
                </tr>

                <tr>
                    <td>Negara tidak memiliki akses laut langsung</td>
                    <td>
                        <span class="badge-soft risk-high">High Risk</span>
                    </td>
                    <td>Perlu memakai pelabuhan negara tetangga.</td>
                </tr>

                <tr>
                    <td>Port congestion atau keterlambatan tinggi</td>
                    <td>
                        <span class="badge bg-dark text-white">Critical Risk</span>
                    </td>
                    <td>Pengiriman sebaiknya ditunda atau dialihkan.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- PREVIEW --}}
<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">All Countries Port Preview</h5>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Country</th>
                    <th>Code</th>
                    <th>Region</th>
                    <th>Port Name</th>
                    <th>Port City</th>
                    <th>Port Count</th>
                    <th>Status</th>
                    <th>Risk</th>
                    <th>Category</th>
                    <th>Recommendation</th>
                </tr>
            </thead>

            <tbody>
                @foreach(array_slice($countries, 0, 25) as $index => $country)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $country['name'] }}</td>
                        <td>{{ $country['code'] }}</td>
                        <td>{{ $country['region'] }}</td>
                        <td>{{ $country['port_name'] }}</td>
                        <td>{{ $country['port_city'] }}</td>
                        <td>{{ $country['port_count'] }}</td>
                        <td>{{ $country['port_status'] }}</td>
                        <td class="fw-bold">{{ $country['port_risk'] }}</td>
                        <td>
                            <span class="badge-soft {{ $country['badge'] }}">
                                {{ $country['category'] }}
                            </span>
                        </td>
                        <td>{{ $country['recommendation'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <small class="text-muted">
        Tabel ini menampilkan 25 negara pertama sebagai preview.
        Semua negara tetap tersedia di dropdown.
    </small>
</div>

{{-- EXPLANATION --}}
<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">Port Location Explanation</h5>

    <p class="text-muted mb-2">
        Fitur ini digunakan untuk membantu perusahaan melihat ketersediaan pelabuhan
        pada negara tujuan impor. Negara yang tidak memiliki akses laut langsung
        akan memiliki risiko lebih tinggi karena harus memakai pelabuhan negara tetangga.
    </p>

    <div class="alert alert-info mb-0">
        Port Risk dipengaruhi oleh status negara landlocked, jumlah pelabuhan,
        kapasitas port, dan ketersediaan jalur pengiriman.
    </div>
</div>

@endsection

@push('scripts')
<script>
    const countries = @json($countries);

    let portMap = null;
    let portMarker = null;

    function showPortLocation() {
        const selectedIndex = document.getElementById('countrySelect').value;
        const country = countries[selectedIndex];

        updateCountryInfo(country);
        updatePortInfo(country);
        updateMap(country);
    }

    function updateCountryInfo(country) {
        document.getElementById('countryName').innerText = country.name ?? '-';

        document.getElementById('countryRegion').innerText =
            (country.region ?? '-') + ' / ' + (country.subregion ?? '-');

        document.getElementById('countryCapital').innerText = country.capital ?? '-';
        document.getElementById('countryCode').innerText = country.code ?? '-';
        document.getElementById('landlockedStatus').innerText = country.landlocked ? 'Yes' : 'No';

        const flag = document.getElementById('countryFlag');

        if (country.flag) {
            flag.src = country.flag;
            flag.style.display = 'block';
        } else {
            flag.style.display = 'none';
        }
    }

    function updatePortInfo(country) {
        document.getElementById('portName').innerText = country.port_name ?? '-';
        document.getElementById('portCity').innerText = country.port_city ?? '-';
        document.getElementById('portCount').innerText = country.port_count ?? '-';
        document.getElementById('portStatus').innerText = country.port_status ?? '-';
        document.getElementById('portRisk').innerText = country.port_risk ?? '-';
        document.getElementById('portLatitude').innerText = country.port_latitude ?? '-';
        document.getElementById('portLongitude').innerText = country.port_longitude ?? '-';

        const riskCategory = document.getElementById('riskCategory');
        riskCategory.className = 'badge-soft ' + country.badge;
        riskCategory.innerText = country.category + ' Risk';

        const recommendationBox = document.getElementById('recommendationBox');
        recommendationBox.innerText = country.recommendation;
        recommendationBox.className = 'alert alert-primary mt-4 mb-0';
    }

    function updateMap(country) {
        const latitude = parseFloat(country.port_latitude) || 0;
        const longitude = parseFloat(country.port_longitude) || 0;

        if (!portMap) {
            portMap = L.map('portMap').setView([latitude, longitude], 4);

            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 8,
                attribution: '&copy; OpenStreetMap'
            }).addTo(portMap);
        } else {
            portMap.setView([latitude, longitude], 4);
        }

        if (portMarker) {
            portMap.removeLayer(portMarker);
        }

        portMarker = L.marker([latitude, longitude])
            .addTo(portMap)
            .bindPopup(`
                <b>${country.port_name}</b><br>
                Country: ${country.name}<br>
                City: ${country.port_city}<br>
                Status: ${country.port_status}<br>
                Risk: ${country.category}
            `)
            .openPopup();

        setTimeout(function () {
            portMap.invalidateSize();
        }, 200);
    }

    document.addEventListener('DOMContentLoaded', function () {
        showPortLocation();
    });
</script>
@endpush