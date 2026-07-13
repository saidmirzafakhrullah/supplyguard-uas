@extends('layouts.app')

@section('title', 'Lokasi Pelabuhan - SupplyGuard')
@section('page-title', 'Lokasi Pelabuhan Global')

@section('content')

<link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
/>

<style>
    #portMap {
        width: 100%;
        height: 520px;
        border-radius: 16px;
        z-index: 1;
    }

    .port-flag {
        width: 38px;
        height: 26px;
        object-fit: cover;
        border-radius: 5px;
        border: 1px solid #dee2e6;
    }

    .map-legend {
        background: #ffffff;
        padding: 10px 12px;
        border-radius: 10px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.15);
        font-size: 12px;
        line-height: 1.8;
    }

    .legend-dot {
        width: 11px;
        height: 11px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 7px;
    }

    .table-port-name {
        min-width: 190px;
    }

    .port-source {
        font-size: 11px;
    }
</style>

{{-- JUDUL HALAMAN --}}
<div class="card sg-card p-4 mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div>
            <h4 class="fw-bold mb-1">Lokasi Pelabuhan Global</h4>

            <p class="text-muted mb-0">
                Pantau ketersediaan, lokasi, dan tingkat risiko pelabuhan
                pada seluruh negara.
            </p>
        </div>

        <span class="badge bg-primary px-3 py-2">
            Peta OpenStreetMap & Leaflet
        </span>
    </div>
</div>

{{-- STATUS API --}}
<div class="alert alert-info mb-4">
    <div class="d-flex align-items-start gap-2">
        <i class="bi bi-info-circle-fill mt-1"></i>

        <div>
            <strong>Status Data</strong>
            <div>{{ $apiStatus }}</div>
        </div>
    </div>
</div>

{{-- RINGKASAN --}}
<div class="row g-4 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card sg-card p-4 h-100">
            <small class="text-muted">Total Negara</small>
            <h3 class="fw-bold mb-2">
                {{ $summary['total_countries'] }}
            </h3>
            <span class="badge bg-primary">Negara Terpantau</span>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card sg-card p-4 h-100">
            <small class="text-muted">Pelabuhan Tersedia</small>
            <h3 class="fw-bold text-success mb-2">
                {{ $summary['available_ports'] }}
            </h3>
            <span class="badge bg-success">Tersedia</span>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card sg-card p-4 h-100">
            <small class="text-muted">Pelabuhan Terbatas</small>
            <h3 class="fw-bold text-warning mb-2">
                {{ $summary['limited_ports'] }}
            </h3>
            <span class="badge bg-warning text-dark">Terbatas</span>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card sg-card p-4 h-100">
            <small class="text-muted">Tanpa Pelabuhan Laut</small>
            <h3 class="fw-bold text-danger mb-2">
                {{ $summary['no_seaport'] }}
            </h3>
            <span class="badge bg-danger">Tanpa Akses Laut</span>
        </div>
    </div>
</div>

{{-- PETA --}}
<div class="card sg-card p-4 mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
        <div>
            <h5 class="fw-bold mb-1">Peta Pelabuhan Dunia</h5>

            <small class="text-muted">
                Klik penanda pada peta untuk melihat informasi pelabuhan.
            </small>
        </div>

        <div class="d-flex gap-2">
            <select
                id="mapStatusFilter"
                class="form-select"
                style="min-width: 210px;"
            >
                <option value="">Semua Status</option>
                <option value="Available">Tersedia</option>
                <option value="Limited">Terbatas</option>
                <option value="No Seaport">Tanpa Pelabuhan Laut</option>
            </select>

            <button
                type="button"
                id="resetMapButton"
                class="btn btn-outline-primary"
            >
                <i class="bi bi-arrow-clockwise me-1"></i>
                Atur Ulang
            </button>
        </div>
    </div>

    <div id="portMap"></div>
</div>

{{-- TABEL --}}
<div class="card sg-card p-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
        <div>
            <h5 class="fw-bold mb-1">Data Lokasi Pelabuhan</h5>

            <small class="text-muted">
                Daftar pelabuhan, status, jumlah, koordinat, dan tingkat risiko.
            </small>
        </div>

        <div style="width: 100%; max-width: 330px;">
            <input
                type="text"
                id="portSearch"
                class="form-control"
                placeholder="Cari negara, kota, atau pelabuhan..."
            >
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Negara</th>
                    <th>Kode</th>
                    <th>Wilayah</th>
                    <th>Nama Pelabuhan</th>
                    <th>Kota</th>
                    <th>Status</th>
                    <th>Jumlah</th>
                    <th>Risiko</th>
                    <th>Sumber Data</th>
                </tr>
            </thead>

            <tbody>
                @forelse($countries as $index => $country)
                    <tr
                        class="port-row"
                        data-status="{{ $country['port_status'] }}"
                        data-search="{{ strtolower(
                            $country['name']
                            . ' '
                            . $country['code']
                            . ' '
                            . $country['region']
                            . ' '
                            . $country['port_name']
                            . ' '
                            . $country['port_city']
                        ) }}"
                    >
                        <td>{{ $index + 1 }}</td>

                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if(!empty($country['flag']))
                                    <img
                                        src="{{ $country['flag'] }}"
                                        alt="{{ $country['name'] }}"
                                        class="port-flag"
                                    >
                                @else
                                    <div
                                        class="port-flag bg-light d-flex align-items-center justify-content-center"
                                    >
                                        <i class="bi bi-flag text-muted"></i>
                                    </div>
                                @endif

                                <div>
                                    <div class="fw-bold">
                                        {{ $country['name'] }}
                                    </div>

                                    <small class="text-muted">
                                        {{ $country['official_name'] }}
                                    </small>
                                </div>
                            </div>
                        </td>

                        <td>
                            <span class="badge bg-light text-dark border">
                                {{ $country['code'] }}
                            </span>
                        </td>

                        <td>{{ $country['region'] }}</td>

                        <td class="table-port-name">
                            <div class="fw-semibold">
                                {{ $country['port_name'] }}
                            </div>

                            <small class="text-muted">
                                {{ number_format($country['port_latitude'], 4) }},
                                {{ number_format($country['port_longitude'], 4) }}
                            </small>
                        </td>

                        <td>{{ $country['port_city'] }}</td>

                        <td>
                            @if($country['port_status'] === 'Available')
                                <span class="badge bg-success">
                                    Tersedia
                                </span>
                            @elseif($country['port_status'] === 'Limited')
                                <span class="badge bg-warning text-dark">
                                    Terbatas
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    Tanpa Pelabuhan Laut
                                </span>
                            @endif
                        </td>

                        <td>
                            <span class="fw-bold">
                                {{ $country['port_count'] }}
                            </span>
                        </td>

                        <td>
                            <div class="mb-1">
                                @if($country['category'] === 'Low')
                                    <span class="badge bg-success">
                                        Rendah
                                    </span>
                                @elseif($country['category'] === 'Medium')
                                    <span class="badge bg-warning text-dark">
                                        Sedang
                                    </span>
                                @elseif($country['category'] === 'High')
                                    <span class="badge bg-danger">
                                        Tinggi
                                    </span>
                                @else
                                    <span class="badge bg-dark">
                                        Kritis
                                    </span>
                                @endif
                            </div>

                            <small class="text-muted">
                                Skor: {{ $country['port_risk'] }}
                            </small>
                        </td>

                        <td>
                            <small class="text-muted port-source">
                                {{ $country['port_data_source'] }}
                            </small>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-5">
                            <i class="bi bi-inbox fs-1 text-muted"></i>

                            <div class="mt-2 text-muted">
                                Data pelabuhan belum tersedia.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="alert alert-light border mt-3 mb-0">
        <strong>Keterangan:</strong>
        negara tanpa akses laut diberi status
        <b>Tanpa Pelabuhan Laut</b> dan disarankan menggunakan
        pelabuhan negara tetangga.
    </div>
</div>

@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    const portCountries = @json($countries);

    const portMap = L.map('portMap', {
        worldCopyJump: true
    }).setView([15, 15], 2);

    L.tileLayer(
        'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        {
            maxZoom: 18,
            attribution:
                '&copy; OpenStreetMap contributors'
        }
    ).addTo(portMap);

    const markerLayer = L.layerGroup().addTo(portMap);

    function statusLabel(status) {
        if (status === 'Available') {
            return 'Tersedia';
        }

        if (status === 'Limited') {
            return 'Terbatas';
        }

        return 'Tanpa Pelabuhan Laut';
    }

    function riskLabel(category) {
        if (category === 'Low') {
            return 'Rendah';
        }

        if (category === 'Medium') {
            return 'Sedang';
        }

        if (category === 'High') {
            return 'Tinggi';
        }

        return 'Kritis';
    }

    function markerColor(status) {
        if (status === 'Available') {
            return '#198754';
        }

        if (status === 'Limited') {
            return '#ffc107';
        }

        return '#dc3545';
    }

    function renderMarkers(status = '') {
        markerLayer.clearLayers();

        const bounds = [];

        portCountries.forEach(function (country) {
            if (
                status !== ''
                && country.port_status !== status
            ) {
                return;
            }

            const latitude =
                Number(country.port_latitude);

            const longitude =
                Number(country.port_longitude);

            if (
                !Number.isFinite(latitude)
                || !Number.isFinite(longitude)
                || Math.abs(latitude) > 90
                || Math.abs(longitude) > 180
            ) {
                return;
            }

            const marker = L.circleMarker(
                [latitude, longitude],
                {
                    radius: 7,
                    color: markerColor(
                        country.port_status
                    ),
                    fillColor: markerColor(
                        country.port_status
                    ),
                    fillOpacity: 0.85,
                    weight: 2
                }
            );

            const flagHtml = country.flag
                ? `
                    <img
                        src="${country.flag}"
                        alt="${country.name}"
                        style="
                            width:32px;
                            height:22px;
                            object-fit:cover;
                            border-radius:4px;
                            margin-right:7px;
                        "
                    >
                `
                : '';

            marker.bindPopup(`
                <div style="min-width:230px;">
                    <div
                        style="
                            display:flex;
                            align-items:center;
                            margin-bottom:8px;
                        "
                    >
                        ${flagHtml}

                        <strong>
                            ${country.name}
                        </strong>
                    </div>

                    <div>
                        <b>Pelabuhan:</b>
                        ${country.port_name}
                    </div>

                    <div>
                        <b>Kota:</b>
                        ${country.port_city}
                    </div>

                    <div>
                        <b>Status:</b>
                        ${statusLabel(country.port_status)}
                    </div>

                    <div>
                        <b>Jumlah pelabuhan:</b>
                        ${country.port_count}
                    </div>

                    <div>
                        <b>Risiko:</b>
                        ${riskLabel(country.category)}
                        (${country.port_risk})
                    </div>

                    <hr style="margin:8px 0;">

                    <small>
                        ${country.recommendation}
                    </small>
                </div>
            `);

            marker.addTo(markerLayer);

            bounds.push([latitude, longitude]);
        });

        if (bounds.length > 0) {
            portMap.fitBounds(bounds, {
                padding: [35, 35],
                maxZoom: 4
            });
        }
    }

    const legend = L.control({
        position: 'bottomright'
    });

    legend.onAdd = function () {
        const div = L.DomUtil.create(
            'div',
            'map-legend'
        );

        div.innerHTML = `
            <strong>Status Pelabuhan</strong><br>

            <span
                class="legend-dot"
                style="background:#198754;"
            ></span>
            Tersedia<br>

            <span
                class="legend-dot"
                style="background:#ffc107;"
            ></span>
            Terbatas<br>

            <span
                class="legend-dot"
                style="background:#dc3545;"
            ></span>
            Tanpa Pelabuhan Laut
        `;

        return div;
    };

    legend.addTo(portMap);

    renderMarkers();

    document
        .getElementById('mapStatusFilter')
        .addEventListener('change', function () {
            renderMarkers(this.value);
        });

    document
        .getElementById('resetMapButton')
        .addEventListener('click', function () {
            document.getElementById(
                'mapStatusFilter'
            ).value = '';

            renderMarkers('');
        });

    document
        .getElementById('portSearch')
        .addEventListener('keyup', function () {
            const keyword =
                this.value.toLowerCase();

            const rows =
                document.querySelectorAll(
                    '.port-row'
                );

            rows.forEach(function (row) {
                const searchText =
                    row.getAttribute(
                        'data-search'
                    );

                row.style.display =
                    searchText.includes(keyword)
                        ? ''
                        : 'none';
            });
        });

    setTimeout(function () {
        portMap.invalidateSize();
    }, 300);
</script>
@endpush