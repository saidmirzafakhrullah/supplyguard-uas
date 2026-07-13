@extends('layouts.app')

@section('title', 'Kelola Pelabuhan - SupplyGuard')
@section('page-title', 'Manajemen Admin - Pelabuhan')

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle-fill me-2"></i>
        {{ session('success') }}

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert"
        ></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        {{ $errors->first() }}

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert"
        ></button>
    </div>
@endif

<div class="card sg-card p-4 mb-4">
    <div
        class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3"
    >
        <div>
            <h4 class="fw-bold mb-1">
                Kelola Dataset Pelabuhan
            </h4>

            <p class="text-muted mb-0">
                Tambah, ubah, cari, dan hapus data pelabuhan
                yang digunakan dalam pemantauan rantai pasok.
            </p>
        </div>

        <button
            type="button"
            class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#addPortModal"
        >
            <i class="bi bi-plus-lg me-1"></i>
            Tambah Pelabuhan
        </button>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card sg-card p-4 h-100">
            <small class="text-muted">Total Pelabuhan</small>
            <h3 class="fw-bold mb-1">
                {{ $summary['total_ports'] }}
            </h3>
            <span class="text-primary small">
                Dataset tersimpan
            </span>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card sg-card p-4 h-100">
            <small class="text-muted">Pelabuhan Aktif</small>
            <h3 class="fw-bold text-success mb-1">
                {{ $summary['active_ports'] }}
            </h3>
            <span class="text-success small">
                Beroperasi normal
            </span>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card sg-card p-4 h-100">
            <small class="text-muted">Operasi Terbatas</small>
            <h3 class="fw-bold text-warning mb-1">
                {{ $summary['limited_ports'] }}
            </h3>
            <span class="text-warning small">
                Perlu pemantauan
            </span>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card sg-card p-4 h-100">
            <small class="text-muted">Risiko Tinggi</small>
            <h3 class="fw-bold text-danger mb-1">
                {{ $summary['high_risk_ports'] }}
            </h3>
            <span class="text-danger small">
                Tinggi atau kritis
            </span>
        </div>
    </div>
</div>

<div class="card sg-card p-4">
    <div
        class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3"
    >
        <div>
            <h5 class="fw-bold mb-1">
                Dataset Pelabuhan
            </h5>

            <small class="text-muted">
                Data berasal dari tabel ports pada database MySQL.
            </small>
        </div>

        <form
            method="GET"
            action="{{ route('admin.ports.index') }}"
            class="d-flex gap-2"
        >
            <input
                type="text"
                name="search"
                class="form-control"
                value="{{ $search }}"
                placeholder="Cari pelabuhan atau negara..."
                style="width: 280px; max-width: 100%;"
            >

            <button class="btn btn-outline-primary">
                <i class="bi bi-search"></i>
            </button>

            @if($search !== '')
                <a
                    href="{{ route('admin.ports.index') }}"
                    class="btn btn-outline-secondary"
                >
                    Reset
                </a>
            @endif
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Pelabuhan</th>
                    <th>Negara</th>
                    <th>Koordinat</th>
                    <th>Status</th>
                    <th>Kapasitas</th>
                    <th>Kemacetan</th>
                    <th>Risiko</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($ports as $index => $port)
                    <tr>
                        <td>
                            {{ $ports->firstItem() + $index }}
                        </td>

                        <td>
                            <div class="fw-bold">
                                {{ $port->port_name }}
                            </div>

                            <small class="text-muted">
                                {{ $port->city ?: '-' }}
                            </small>
                        </td>

                        <td>
                            <div class="fw-semibold">
                                {{ $port->country }}
                            </div>

                            <small class="text-muted">
                                {{ $port->country_code }}
                                ·
                                {{ $port->region ?: '-' }}
                            </small>
                        </td>

                        <td class="text-nowrap">
                            <small>
                                {{ number_format($port->latitude, 4) }},
                                {{ number_format($port->longitude, 4) }}
                            </small>
                        </td>

                        <td>
                            @if($port->status === 'active')
                                <span class="badge bg-success">
                                    Aktif
                                </span>
                            @elseif($port->status === 'limited')
                                <span class="badge bg-warning text-dark">
                                    Terbatas
                                </span>
                            @else
                                <span class="badge bg-secondary">
                                    Tidak Aktif
                                </span>
                            @endif
                        </td>

                        <td>
                            {{
                                [
                                    'low' => 'Rendah',
                                    'medium' => 'Sedang',
                                    'high' => 'Tinggi',
                                ][$port->capacity] ?? $port->capacity
                            }}
                        </td>

                        <td>
                            {{
                                [
                                    'low' => 'Rendah',
                                    'medium' => 'Sedang',
                                    'high' => 'Tinggi',
                                ][$port->congestion_level]
                                ?? $port->congestion_level
                            }}
                        </td>

                        <td>
                            @php
                                $riskClasses = [
                                    'low' => 'bg-success',
                                    'medium' => 'bg-warning text-dark',
                                    'high' => 'bg-danger',
                                    'critical' => 'bg-dark',
                                ];

                                $riskLabels = [
                                    'low' => 'Rendah',
                                    'medium' => 'Sedang',
                                    'high' => 'Tinggi',
                                    'critical' => 'Kritis',
                                ];
                            @endphp

                            <span
                                class="badge {{ $riskClasses[$port->risk_level] ?? 'bg-secondary' }}"
                            >
                                {{
                                    $riskLabels[$port->risk_level]
                                    ?? $port->risk_level
                                }}
                            </span>
                        </td>

                        <td>
                            <div
                                class="d-flex justify-content-center gap-2"
                            >
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editPortModal{{ $port->id }}"
                                    title="Edit"
                                >
                                    <i class="bi bi-pencil-square"></i>
                                </button>

                                <form
                                    method="POST"
                                    action="{{ route('admin.ports.destroy', $port) }}"
                                    onsubmit="return confirm('Hapus pelabuhan {{ addslashes($port->port_name) }}?')"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="btn btn-sm btn-outline-danger"
                                        title="Hapus"
                                    >
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    <div
                        class="modal fade"
                        id="editPortModal{{ $port->id }}"
                        tabindex="-1"
                    >
                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                            <div class="modal-content">
                                <form
                                    method="POST"
                                    action="{{ route('admin.ports.update', $port) }}"
                                >
                                    @csrf
                                    @method('PUT')

                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            Edit Pelabuhan
                                        </h5>

                                        <button
                                            type="button"
                                            class="btn-close"
                                            data-bs-dismiss="modal"
                                        ></button>
                                    </div>

                                    <div class="modal-body">
                                        @include(
                                            'admin.ports.partials.form',
                                            ['port' => $port]
                                        )
                                    </div>

                                    <div class="modal-footer">
                                        <button
                                            type="button"
                                            class="btn btn-light"
                                            data-bs-dismiss="modal"
                                        >
                                            Batal
                                        </button>

                                        <button
                                            type="submit"
                                            class="btn btn-primary"
                                        >
                                            Simpan Perubahan
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr>
                        <td
                            colspan="9"
                            class="text-center text-muted py-5"
                        >
                            <i class="bi bi-geo-alt fs-2 d-block mb-2"></i>

                            Belum ada data pelabuhan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($ports->hasPages())
        <div class="mt-4">
            {{
                $ports
                    ->onEachSide(1)
                    ->links('pagination::bootstrap-5')
            }}
        </div>
    @endif
</div>

<div
    class="modal fade"
    id="addPortModal"
    tabindex="-1"
>
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form
                method="POST"
                action="{{ route('admin.ports.store') }}"
            >
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">
                        Tambah Pelabuhan
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                    ></button>
                </div>

                <div class="modal-body">
                    @include(
                        'admin.ports.partials.form',
                        ['port' => null]
                    )
                </div>

                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-light"
                        data-bs-dismiss="modal"
                    >
                        Batal
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Tambahkan Pelabuhan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection