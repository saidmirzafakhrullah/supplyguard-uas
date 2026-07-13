@extends('layouts.app')

@section('title', 'Manajemen Pengguna - SupplyGuard')
@section('page-title', 'Manajemen Admin - Pengguna')

@section('content')

{{-- PESAN SISTEM --}}
@if(session('success'))
    <div
        class="alert alert-success alert-dismissible fade show"
        role="alert"
    >
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
    <div
        class="alert alert-info alert-dismissible fade show"
        role="alert"
    >
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
    <div
        class="alert alert-danger alert-dismissible fade show"
        role="alert"
    >
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
    <div
        class="alert alert-danger alert-dismissible fade show"
        role="alert"
    >
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

{{-- JUDUL --}}
<div class="card sg-card p-4 mb-4">
    <div
        class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3"
    >
        <div>
            <h4 class="fw-bold mb-1">
                Manajemen Pengguna
            </h4>

            <p class="text-muted mb-0">
                Kelola akun pengguna dan tentukan hak akses
                Administrator atau Pengguna pada sistem SupplyGuard.
            </p>
        </div>

        <span class="badge bg-primary px-3 py-2">
            <i class="bi bi-shield-lock me-1"></i>
            Panel Administrator
        </span>
    </div>
</div>

{{-- RINGKASAN --}}
<div class="row g-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card sg-card p-4 h-100">
            <div
                class="d-flex justify-content-between align-items-start"
            >
                <div>
                    <small class="text-muted">
                        Total Pengguna
                    </small>

                    <h3 class="fw-bold mb-1">
                        {{ $summary['total_users'] }}
                    </h3>

                    <span class="small text-primary">
                        Akun terdaftar
                    </span>
                </div>

                <div class="sg-stat-icon">
                    <i class="bi bi-people"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card sg-card p-4 h-100">
            <div
                class="d-flex justify-content-between align-items-start"
            >
                <div>
                    <small class="text-muted">
                        Administrator
                    </small>

                    <h3 class="fw-bold text-danger mb-1">
                        {{ $summary['admin_users'] }}
                    </h3>

                    <span class="small text-danger">
                        Akses manajemen
                    </span>
                </div>

                <div
                    class="sg-stat-icon bg-danger-subtle text-danger"
                >
                    <i class="bi bi-shield-lock"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card sg-card p-4 h-100">
            <div
                class="d-flex justify-content-between align-items-start"
            >
                <div>
                    <small class="text-muted">
                        Pengguna Biasa
                    </small>

                    <h3 class="fw-bold text-success mb-1">
                        {{ $summary['regular_users'] }}
                    </h3>

                    <span class="small text-success">
                        Akses fitur utama
                    </span>
                </div>

                <div
                    class="sg-stat-icon bg-success-subtle text-success"
                >
                    <i class="bi bi-person"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card sg-card p-4 h-100">
            <div
                class="d-flex justify-content-between align-items-start"
            >
                <div>
                    <small class="text-muted">
                        Terdaftar Bulan Ini
                    </small>

                    <h3 class="fw-bold text-warning mb-1">
                        {{ $summary['new_users_this_month'] }}
                    </h3>

                    <span class="small text-warning">
                        Pengguna baru
                    </span>
                </div>

                <div
                    class="sg-stat-icon bg-warning-subtle text-warning"
                >
                    <i class="bi bi-person-plus"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- GRAFIK DAN INFORMASI --}}
<div class="row g-4 mt-1">
    <div class="col-lg-5">
        <div class="card sg-card p-4 h-100">
            <h5 class="fw-bold mb-1">
                Distribusi Peran Pengguna
            </h5>

            <small class="text-muted">
                Perbandingan Administrator dan Pengguna biasa.
            </small>

            <div class="mt-3" style="height: 280px;">
                <canvas id="userRoleChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card sg-card p-4 h-100">
            <h5 class="fw-bold mb-3">
                Informasi Hak Akses
            </h5>

            <div class="alert alert-danger">
                <div class="fw-bold mb-1">
                    <i class="bi bi-shield-lock me-1"></i>
                    Administrator
                </div>

                Dapat membuka menu Manajemen Admin,
                mengelola pengguna, pelabuhan, artikel,
                kata sentimen, dan melihat log API.
            </div>

            <div class="alert alert-success mb-0">
                <div class="fw-bold mb-1">
                    <i class="bi bi-person me-1"></i>
                    Pengguna
                </div>

                Dapat memakai dashboard, pemantauan risiko,
                cuaca, mata uang, berita, pelabuhan,
                perbandingan negara, dan daftar favorit.
            </div>
        </div>
    </div>
</div>

{{-- TABEL PENGGUNA --}}
<div class="card sg-card p-4 mt-4">
    <div
        class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3"
    >
        <div>
            <h5 class="fw-bold mb-1">
                Pengguna Terdaftar
            </h5>

            <small class="text-muted">
                Role dibaca langsung dari kolom
                <strong>role</strong> pada tabel users.
            </small>
        </div>

        <div style="width: 280px; max-width: 100%;">
            <div class="input-group">
                <span class="input-group-text bg-white">
                    <i class="bi bi-search"></i>
                </span>

                <input
                    type="text"
                    id="userSearch"
                    class="form-control"
                    placeholder="Cari nama, email, atau role..."
                >
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Pengguna</th>
                    <th>Email</th>
                    <th>Peran Saat Ini</th>
                    <th>Tanggal Terdaftar</th>
                    <th style="min-width: 230px;">
                        Ubah Peran
                    </th>
                    <th class="text-center">
                        Hapus
                    </th>
                </tr>
            </thead>

            <tbody id="userTableBody">
                @forelse($users as $index => $user)
                    @php
                        $isCurrentUser =
                            (int) $user['id']
                            === (int) auth()->id();
                    @endphp

                    <tr
                        class="user-row"
                        data-search="{{
                            strtolower(
                                $user['name']
                                . ' '
                                . $user['email']
                                . ' '
                                . $user['role']
                            )
                        }}"
                    >
                        <td>
                            {{ $index + 1 }}
                        </td>

                        <td>
                            <div
                                class="d-flex align-items-center gap-2"
                                style="min-width: 180px;"
                            >
                                <div
                                    class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold"
                                    style="
                                        width: 40px;
                                        height: 40px;
                                        flex-shrink: 0;
                                    "
                                >
                                    {{
                                        strtoupper(
                                            substr(
                                                $user['name'],
                                                0,
                                                1
                                            )
                                        )
                                    }}
                                </div>

                                <div>
                                    <div class="fw-bold">
                                        {{ $user['name'] }}

                                        @if($isCurrentUser)
                                            <span
                                                class="badge bg-primary ms-1"
                                            >
                                                Akun Anda
                                            </span>
                                        @endif
                                    </div>

                                    <small class="text-muted">
                                        ID: {{ $user['id'] }}
                                    </small>
                                </div>
                            </div>
                        </td>

                        <td>
                            {{ $user['email'] }}
                        </td>

                        <td>
                            @if($user['role'] === 'admin')
                                <span class="badge bg-danger">
                                    <i
                                        class="bi bi-shield-lock me-1"
                                    ></i>
                                    Administrator
                                </span>
                            @else
                                <span class="badge bg-success">
                                    <i
                                        class="bi bi-person me-1"
                                    ></i>
                                    Pengguna
                                </span>
                            @endif
                        </td>

                        <td class="text-nowrap">
                            {{ $user['created_at'] }}
                        </td>

                        <td>
                            <form
                                method="POST"
                                action="{{
                                    route(
                                        'admin.users.update-role',
                                        $user['id']
                                    )
                                }}"
                                class="d-flex gap-2"
                                onsubmit="return confirm('Simpan perubahan role pengguna ini?')"
                            >
                                @csrf
                                @method('PATCH')

                                <select
                                    name="role"
                                    class="form-select form-select-sm"
                                    @disabled($isCurrentUser)
                                >
                                    <option
                                        value="user"
                                        @selected(
                                            $user['role']
                                            === 'user'
                                        )
                                    >
                                        Pengguna
                                    </option>

                                    <option
                                        value="admin"
                                        @selected(
                                            $user['role']
                                            === 'admin'
                                        )
                                    >
                                        Administrator
                                    </option>
                                </select>

                                <button
                                    type="submit"
                                    class="btn btn-sm btn-outline-primary text-nowrap"
                                    @disabled($isCurrentUser)
                                >
                                    <i class="bi bi-check-lg"></i>
                                    Simpan
                                </button>
                            </form>

                            @if($isCurrentUser)
                                <small class="text-muted">
                                    Role akun aktif tidak dapat diubah.
                                </small>
                            @endif
                        </td>

                        <td class="text-center">
                            @if(!$isCurrentUser)
                                <form
                                    method="POST"
                                    action="{{
                                        route(
                                            'admin.users.destroy',
                                            $user['id']
                                        )
                                    }}"
                                    onsubmit="return confirm('Hapus akun {{ addslashes($user['name']) }} secara permanen?')"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="btn btn-sm btn-outline-danger"
                                        title="Hapus pengguna"
                                    >
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            @else
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-secondary"
                                    disabled
                                    title="Akun aktif tidak dapat dihapus"
                                >
                                    <i class="bi bi-lock"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td
                            colspan="7"
                            class="text-center text-muted py-5"
                        >
                            <i
                                class="bi bi-people fs-2 d-block mb-2"
                            ></i>

                            Belum ada pengguna yang terdaftar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div
        id="noSearchResult"
        class="text-center text-muted py-4 d-none"
    >
        Pengguna yang dicari tidak ditemukan.
    </div>
</div>

{{-- PENJELASAN --}}
<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">
        <i class="bi bi-info-circle text-primary me-2"></i>
        Ketentuan Keamanan
    </h5>

    <div class="alert alert-info mb-0">
        Administrator tidak dapat mengubah role atau menghapus
        akun yang sedang digunakan. Sistem juga menjaga agar
        selalu tersedia minimal satu akun Administrator.
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener(
        'DOMContentLoaded',
        function () {
            const chartCanvas =
                document.getElementById(
                    'userRoleChart'
                );

            if (
                chartCanvas
                && typeof Chart !== 'undefined'
            ) {
                new Chart(chartCanvas, {
                    type: 'doughnut',

                    data: {
                        labels: [
                            'Administrator',
                            'Pengguna'
                        ],

                        datasets: [{
                            data: [
                                {{ $summary['admin_users'] }},
                                {{ $summary['regular_users'] }}
                            ],

                            borderWidth: 2
                        }]
                    },

                    options: {
                        responsive: true,
                        maintainAspectRatio: false,

                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }

            const searchInput =
                document.getElementById(
                    'userSearch'
                );

            const noSearchResult =
                document.getElementById(
                    'noSearchResult'
                );

            if (searchInput) {
                searchInput.addEventListener(
                    'input',
                    function () {
                        const keyword =
                            this.value
                                .toLowerCase()
                                .trim();

                        const rows =
                            document.querySelectorAll(
                                '.user-row'
                            );

                        let visibleRows = 0;

                        rows.forEach(function (row) {
                            const text =
                                row.getAttribute(
                                    'data-search'
                                ) ?? '';

                            const visible =
                                text.includes(keyword);

                            row.style.display =
                                visible ? '' : 'none';

                            if (visible) {
                                visibleRows++;
                            }
                        });

                        if (noSearchResult) {
                            noSearchResult.classList.toggle(
                                'd-none',
                                visibleRows > 0
                            );
                        }
                    }
                );
            }
        }
    );
</script>
@endpush