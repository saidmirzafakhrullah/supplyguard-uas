@extends('layouts.app')

@section('title', 'Daftar Pemantauan Favorit - SupplyGuard')
@section('page-title', 'Daftar Pemantauan Favorit')

@section('content')
@php
    $availableCountries = collect($countries)
        ->reject(fn ($country) => (bool) ($country['is_favorite'] ?? false))
        ->values();

    $categoryLabels = [
        'Low' => 'Rendah',
        'Medium' => 'Sedang',
        'High' => 'Tinggi',
        'Critical' => 'Kritis',
    ];

    $alertLabels = [
        'Safe' => 'Aman',
        'Monitor' => 'Pantau',
        'Warning' => 'Peringatan',
        'Critical' => 'Kritis',
        'Low' => 'Rendah',
        'Medium' => 'Sedang',
        'High' => 'Tinggi',
    ];
@endphp

{{-- PESAN SISTEM --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        {{ session('success') }}

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert"
            aria-label="Tutup"
        ></button>
    </div>
@endif

@if(session('info'))
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="bi bi-info-circle-fill me-2"></i>
        {{ session('info') }}

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert"
            aria-label="Tutup"
        ></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        {{ session('error') }}

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert"
            aria-label="Tutup"
        ></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        {{ $errors->first() }}

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert"
            aria-label="Tutup"
        ></button>
    </div>
@endif

{{-- JUDUL HALAMAN --}}
<div class="card sg-card border-0 p-4 mb-4">
    <div
        class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3"
    >
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span
                    class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary"
                    style="width: 42px; height: 42px;"
                >
                    <i class="bi bi-star-fill"></i>
                </span>

                <h4 class="fw-bold mb-0">
                    Daftar Pemantauan Favorit
                </h4>
            </div>

            <p class="text-muted mb-0">
                Simpan negara pilihan dan pantau risiko cuaca,
                nilai tukar, berita, serta akses pelabuhan berdasarkan
                akun yang sedang digunakan.
            </p>
        </div>

        <span
            class="badge rounded-pill bg-primary px-3 py-2 align-self-start align-self-lg-center"
        >
            <i class="bi bi-person-check me-1"></i>
            Favorit Saya
        </span>
    </div>
</div>

{{-- RINGKASAN --}}
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card sg-card border-0 h-100 p-4">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <small class="text-muted">
                        Total Negara Tersedia
                    </small>

                    <h3 class="fw-bold mb-1">
                        {{ number_format($summary['total_countries']) }}
                    </h3>

                    <span class="small text-primary">
                        REST Countries API
                    </span>
                </div>

                <span
                    class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary"
                    style="width: 42px; height: 42px;"
                >
                    <i class="bi bi-globe2"></i>
                </span>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card sg-card border-0 h-100 p-4">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <small class="text-muted">
                        Negara Favorit
                    </small>

                    <h3 class="fw-bold mb-1">
                        {{ number_format($summary['watchlist_count']) }}
                    </h3>

                    <span class="small text-warning">
                        Sedang dipantau
                    </span>
                </div>

                <span
                    class="d-inline-flex align-items-center justify-content-center rounded-circle bg-warning-subtle text-warning"
                    style="width: 42px; height: 42px;"
                >
                    <i class="bi bi-star"></i>
                </span>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card sg-card border-0 h-100 p-4">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <small class="text-muted">
                        Risiko Rendah
                    </small>

                    <h3 class="fw-bold text-success mb-1">
                        {{ number_format($summary['low_risk']) }}
                    </h3>

                    <span class="small text-success">
                        Relatif aman
                    </span>
                </div>

                <span
                    class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success-subtle text-success"
                    style="width: 42px; height: 42px;"
                >
                    <i class="bi bi-shield-check"></i>
                </span>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card sg-card border-0 h-100 p-4">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <small class="text-muted">
                        Perlu Perhatian
                    </small>

                    <h3 class="fw-bold text-danger mb-1">
                        {{
                            number_format(
                                $summary['high_risk']
                                + $summary['critical_risk']
                            )
                        }}
                    </h3>

                    <span class="small text-danger">
                        Tinggi atau kritis
                    </span>
                </div>

                <span
                    class="d-inline-flex align-items-center justify-content-center rounded-circle bg-danger-subtle text-danger"
                    style="width: 42px; height: 42px;"
                >
                    <i class="bi bi-exclamation-triangle"></i>
                </span>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    {{-- FORM TAMBAH FAVORIT --}}
    <div class="col-lg-5">
        <div class="card sg-card border-0 h-100 p-4">
            <div class="d-flex align-items-center gap-2 mb-3">
                <span
                    class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary"
                    style="width: 38px; height: 38px;"
                >
                    <i class="bi bi-plus-lg"></i>
                </span>

                <div>
                    <h5 class="fw-bold mb-0">
                        Tambah Negara Favorit
                    </h5>

                    <small class="text-muted">
                        Pilih satu negara untuk mulai dipantau.
                    </small>
                </div>
            </div>

            @if($availableCountries->isNotEmpty())
                <form
                    method="POST"
                    action="{{ route('watchlist.store') }}"
                    id="watchlistForm"
                >
                    @csrf

                    <label
                        for="countrySelect"
                        class="form-label fw-semibold"
                    >
                        Negara
                    </label>

                    <select
                        id="countrySelect"
                        name="country_code"
                        class="form-select @error('country_code') is-invalid @enderror"
                        required
                    >
                        <option value="">
                            -- Pilih negara --
                        </option>

                        @foreach($availableCountries as $country)
                            <option
                                value="{{ $country['code'] }}"
                                @selected(
                                    old('country_code')
                                    === $country['code']
                                )
                            >
                                {{ $country['name'] }}
                                ({{ $country['code'] }})
                            </option>
                        @endforeach
                    </select>

                    @error('country_code')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                    <div
                        id="countryPreview"
                        class="border rounded-3 p-3 mt-3 d-none"
                    >
                        <div class="d-flex align-items-center gap-3">
                            <img
                                id="previewFlag"
                                src=""
                                alt="Bendera negara"
                                class="rounded border d-none"
                                style="
                                    width: 58px;
                                    height: 40px;
                                    object-fit: cover;
                                "
                            >

                            <div class="flex-grow-1">
                                <h6
                                    id="previewName"
                                    class="fw-bold mb-1"
                                >
                                    -
                                </h6>

                                <div
                                    id="previewDetails"
                                    class="small text-muted"
                                >
                                    -
                                </div>
                            </div>
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="btn btn-primary w-100 mt-3"
                    >
                        <i class="bi bi-star-fill me-2"></i>
                        Tambahkan ke Favorit
                    </button>
                </form>
            @else
                <div class="alert alert-success mb-0">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    Seluruh negara yang tersedia sudah masuk ke
                    daftar favorit Anda.
                </div>
            @endif

            <div class="alert alert-light border mt-3 mb-0 small">
                <i class="bi bi-info-circle text-primary me-1"></i>
                Negara yang ditambahkan akan disimpan khusus
                untuk akun Anda.
            </div>
        </div>
    </div>

    {{-- GRAFIK RINGKAS --}}
    <div class="col-lg-7">
        <div class="card sg-card border-0 h-100 p-4">
            <div
                class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3"
            >
                <div>
                    <h5 class="fw-bold mb-1">
                        Perbandingan Total Risiko
                    </h5>

                    <small class="text-muted">
                        Skor risiko seluruh negara favorit.
                    </small>
                </div>

                <span class="badge bg-light text-dark border">
                    Skala 0–100
                </span>
            </div>

            @if(count($watchlistCountries) > 0)
                <div style="height: 300px;">
                    <canvas id="watchlistRiskChart"></canvas>
                </div>
            @else
                <div
                    class="d-flex flex-column align-items-center justify-content-center text-center py-5 h-100"
                >
                    <span
                        class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light text-muted mb-3"
                        style="width: 64px; height: 64px;"
                    >
                        <i class="bi bi-bar-chart fs-3"></i>
                    </span>

                    <h6 class="fw-bold">
                        Grafik belum tersedia
                    </h6>

                    <p class="text-muted mb-0">
                        Tambahkan negara favorit untuk menampilkan
                        perbandingan risiko.
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- STATUS API --}}
<div class="alert alert-info border-0 shadow-sm mb-4">
    <div class="d-flex gap-2">
        <i class="bi bi-broadcast-pin mt-1"></i>

        <div>
            <strong>Status Data:</strong>
            {{ $apiStatus }}
        </div>
    </div>
</div>

{{-- TABEL FAVORIT --}}
<div class="card sg-card border-0 p-4">
    <div
        class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3"
    >
        <div>
            <h5 class="fw-bold mb-1">
                Negara Favorit Saya
            </h5>

            <small class="text-muted">
                Hasil pemantauan terbaru berdasarkan data API
                dan perhitungan SupplyGuard.
            </small>
        </div>

        <span class="badge bg-primary rounded-pill px-3 py-2">
            {{ number_format($summary['watchlist_count']) }}
            Negara
        </span>
    </div>

    @if(count($watchlistCountries) > 0)
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-nowrap">No</th>
                        <th class="text-nowrap">Negara</th>
                        <th class="text-nowrap">Cuaca</th>
                        <th class="text-nowrap">Kurs</th>
                        <th class="text-nowrap">Berita</th>
                        <th class="text-nowrap">Pelabuhan</th>
                        <th class="text-nowrap">Total Risiko</th>
                        <th class="text-nowrap">Status</th>
                        <th style="min-width: 230px;">
                            Rekomendasi
                        </th>
                        <th class="text-center">
                            Aksi
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($watchlistCountries as $index => $country)
                        <tr>
                            <td>
                                {{ $index + 1 }}
                            </td>

                            <td>
                                <div
                                    class="d-flex align-items-center gap-2"
                                    style="min-width: 180px;"
                                >
                                    @if(!empty($country['flag']))
                                        <img
                                            src="{{ $country['flag'] }}"
                                            alt="Bendera {{ $country['name'] }}"
                                            class="rounded border"
                                            style="
                                                width: 38px;
                                                height: 26px;
                                                object-fit: cover;
                                            "
                                        >
                                    @else
                                        <span
                                            class="d-inline-flex align-items-center justify-content-center rounded bg-light text-muted"
                                            style="
                                                width: 38px;
                                                height: 26px;
                                            "
                                        >
                                            <i class="bi bi-flag"></i>
                                        </span>
                                    @endif

                                    <div>
                                        <div class="fw-semibold">
                                            {{ $country['name'] }}
                                        </div>

                                        <small class="text-muted">
                                            {{ $country['code'] }}
                                            ·
                                            {{ $country['region'] }}
                                        </small>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div class="fw-semibold">
                                    {{
                                        number_format(
                                            (float) $country['weather_risk'],
                                            0
                                        )
                                    }}
                                </div>

                                <small class="text-muted">
                                    @if(
                                        is_numeric(
                                            $country['temperature'] ?? null
                                        )
                                    )
                                        {{
                                            number_format(
                                                (float) $country['temperature'],
                                                1
                                            )
                                        }}°C
                                    @else
                                        Data cadangan
                                    @endif
                                </small>
                            </td>

                            <td>
                                <div class="fw-semibold">
                                    {{
                                        number_format(
                                            (float) $country['currency_risk'],
                                            0
                                        )
                                    }}
                                </div>

                                <small class="text-muted">
                                    {{ $country['currency_code'] ?? '-' }}

                                    @if(
                                        is_numeric(
                                            $country['exchange_rate'] ?? null
                                        )
                                    )
                                        ·
                                        {{
                                            number_format(
                                                (float) $country['exchange_rate'],
                                                2
                                            )
                                        }}
                                    @endif
                                </small>
                            </td>

                            <td>
                                <div class="fw-semibold">
                                    {{
                                        number_format(
                                            (float) $country['news_risk'],
                                            0
                                        )
                                    }}
                                </div>

                                <small class="text-muted">
                                    {{
                                        [
                                            'Positive' => 'Positif',
                                            'Neutral' => 'Netral',
                                            'Negative' => 'Negatif',
                                        ][$country['news_sentiment'] ?? 'Neutral']
                                        ?? ($country['news_sentiment'] ?? 'Netral')
                                    }}
                                </small>
                            </td>

                            <td>
                                <div class="fw-semibold">
                                    {{
                                        number_format(
                                            (float) $country['port_risk'],
                                            0
                                        )
                                    }}
                                </div>

                                <small class="text-muted">
                                    {{
                                        ($country['landlocked'] ?? false)
                                            ? 'Tanpa akses laut'
                                            : 'Memiliki akses laut'
                                    }}
                                </small>
                            </td>

                            <td>
                                <span class="fw-bold fs-6">
                                    {{
                                        number_format(
                                            (float) $country['risk_score'],
                                            2
                                        )
                                    }}
                                </span>
                            </td>

                            <td>
                                <span
                                    class="badge-soft {{ $country['badge'] }} text-nowrap"
                                >
                                    {{
                                        $categoryLabels[
                                            $country['category']
                                        ]
                                        ?? $country['category']
                                    }}
                                </span>

                                <div class="small text-muted mt-1">
                                    {{
                                        $alertLabels[
                                            $country['alert_level']
                                        ]
                                        ?? $country['alert_level']
                                    }}
                                </div>
                            </td>

                            <td>
                                <small>
                                    {{ $country['recommendation'] }}
                                </small>
                            </td>

                            <td class="text-center">
                                <form
                                    method="POST"
                                    action="{{ route('watchlist.destroy', $country['code']) }}"
                                    onsubmit="return confirm('Hapus {{ addslashes($country['name']) }} dari daftar favorit?')"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="btn btn-sm btn-outline-danger"
                                        title="Hapus favorit"
                                    >
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-5">
            <span
                class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light text-muted mb-3"
                style="width: 72px; height: 72px;"
            >
                <i class="bi bi-star fs-2"></i>
            </span>

            <h5 class="fw-bold">
                Belum ada negara favorit
            </h5>

            <p class="text-muted mb-0">
                Pilih negara pada formulir di atas untuk
                memulai pemantauan.
            </p>
        </div>
    @endif
</div>

{{-- PENJELASAN --}}
<div class="card sg-card border-0 p-4 mt-4">
    <h5 class="fw-bold mb-3">
        <i class="bi bi-info-circle text-primary me-2"></i>
        Cara Kerja Pemantauan Favorit
    </h5>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="border rounded-3 p-3 h-100">
                <div class="fw-semibold mb-1">
                    1. Pilih Negara
                </div>

                <small class="text-muted">
                    Tambahkan negara yang sering digunakan sebagai
                    sumber atau jalur impor.
                </small>
            </div>
        </div>

        <div class="col-md-4">
            <div class="border rounded-3 p-3 h-100">
                <div class="fw-semibold mb-1">
                    2. Ambil Data Aktual
                </div>

                <small class="text-muted">
                    Sistem mengambil data cuaca, kurs, berita,
                    dan kondisi akses pelabuhan.
                </small>
            </div>
        </div>

        <div class="col-md-4">
            <div class="border rounded-3 p-3 h-100">
                <div class="fw-semibold mb-1">
                    3. Hitung Risiko
                </div>

                <small class="text-muted">
                    SupplyGuard menghitung skor dan memberikan
                    rekomendasi pemantauan.
                </small>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const countries = @json($countries);

        const select =
            document.getElementById('countrySelect');

        const preview =
            document.getElementById('countryPreview');

        const previewFlag =
            document.getElementById('previewFlag');

        const previewName =
            document.getElementById('previewName');

        const previewDetails =
            document.getElementById('previewDetails');

        const showCountryPreview = function () {
            if (!select || !preview) {
                return;
            }

            const countryCode = select.value;

            const country = countries.find(function (item) {
                return item.code === countryCode;
            });

            if (!country) {
                preview.classList.add('d-none');
                return;
            }

            previewName.textContent =
                country.name ?? '-';

            previewDetails.textContent = [
                country.capital
                    ? 'Ibu kota: ' + country.capital
                    : null,

                country.region ?? null,

                country.currency_code
                    ? 'Mata uang: ' + country.currency_code
                    : null
            ]
                .filter(Boolean)
                .join(' · ');

            if (country.flag) {
                previewFlag.src = country.flag;

                previewFlag.alt =
                    'Bendera ' + (country.name ?? 'negara');

                previewFlag.classList.remove('d-none');
            } else {
                previewFlag.removeAttribute('src');
                previewFlag.classList.add('d-none');
            }

            preview.classList.remove('d-none');
        };

        if (select) {
            select.addEventListener(
                'change',
                showCountryPreview
            );

            if (select.value) {
                showCountryPreview();
            }
        }

        @if(count($watchlistCountries) > 0)
            const chartCanvas =
                document.getElementById(
                    'watchlistRiskChart'
                );

            if (
                chartCanvas
                && typeof Chart !== 'undefined'
            ) {
                new Chart(chartCanvas, {
                    type: 'bar',

                    data: {
                        labels: @json(
                            collect($watchlistCountries)
                                ->pluck('name')
                                ->values()
                        ),

                        datasets: [{
                            label: 'Total Risiko',

                            data: @json(
                                collect($watchlistCountries)
                                    ->pluck('risk_score')
                                    ->map(
                                        fn ($value) =>
                                            round((float) $value, 2)
                                    )
                                    ->values()
                            ),

                            borderWidth: 1,
                            borderRadius: 7,
                            maxBarThickness: 46
                        }]
                    },

                    options: {
                        responsive: true,
                        maintainAspectRatio: false,

                        plugins: {
                            legend: {
                                display: false
                            },

                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        return ' Risiko: '
                                            + Number(
                                                context.raw
                                            ).toFixed(2);
                                    }
                                }
                            }
                        },

                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,

                                ticks: {
                                    callback: function (value) {
                                        return value;
                                    }
                                }
                            }
                        }
                    }
                });
            }
        @endif
    });
</script>
@endpush