@extends('layouts.app')

@section('title', 'Kelola Negara - SupplyGuard')
@section('page-title', 'Manajemen Admin - Negara')

@section('content')
@foreach(['success' => 'success', 'error' => 'danger'] as $key => $type)
    @if(session($key))
        <div class="alert alert-{{ $type }} alert-dismissible fade show">
            {{ session($key) }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
@endforeach

@if($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<div class="card sg-card p-4 mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div>
            <h4 class="fw-bold mb-1">Kelola Dataset Negara</h4>
            <p class="text-muted mb-0">
                CRUD negara manual dan sinkronisasi REST Countries API ke database.
            </p>
        </div>
        <div class="d-flex gap-2">
            <form method="POST" action="{{ route('admin.countries.sync') }}"
                id="countrySyncForm">
                @csrf
                <button class="btn btn-outline-primary" id="countrySyncButton">
                    <span id="countrySyncSpinner" class="spinner-border spinner-border-sm me-1 d-none"></span>
                    <i id="countrySyncIcon" class="bi bi-arrow-repeat me-1"></i>
                    <span id="countrySyncText">Sinkronkan API</span>
                </button>
            </form>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCountryModal">
                <i class="bi bi-plus-lg me-1"></i> Tambah Negara
            </button>
        </div>
    </div>
</div>

<div class="card sg-card p-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
        <div>
            <h5 class="fw-bold mb-1">Negara Tersimpan</h5>
            <small class="text-muted">Total: {{ $countries->total() }} negara</small>
        </div>
        <form method="GET" class="d-flex gap-2">
            <input class="form-control" name="search" value="{{ $search }}"
                placeholder="Cari nama, kode, atau wilayah...">
            <button class="btn btn-outline-primary"><i class="bi bi-search"></i></button>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr><th>Negara</th><th>Kode</th><th>Wilayah</th><th>Populasi</th><th>Sumber</th><th>Aksi</th></tr></thead>
            <tbody>
            @forelse($countries as $country)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            @if($country->flag)<img src="{{ $country->flag }}" alt="" width="36" class="rounded">@endif
                            <div><strong>{{ $country->name }}</strong><br><small class="text-muted">{{ $country->capital ?: '-' }}</small></div>
                        </div>
                    </td>
                    <td><span class="badge bg-primary">{{ $country->code }}</span></td>
                    <td>{{ $country->region ?: '-' }}<br><small class="text-muted">{{ $country->subregion ?: '-' }}</small></td>
                    <td>{{ number_format($country->population, 0, ',', '.') }}</td>
                    <td><small>{{ $country->source }}</small></td>
                    <td>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                data-bs-target="#editCountry{{ $country->id }}"><i class="bi bi-pencil-square"></i></button>
                            <form method="POST" action="{{ route('admin.countries.destroy', $country) }}"
                                onsubmit="return confirm('Hapus negara {{ addslashes($country->name) }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash3"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>

                <div class="modal fade" id="editCountry{{ $country->id }}" tabindex="-1">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
                        <form method="POST" action="{{ route('admin.countries.update', $country) }}">
                            @csrf @method('PUT')
                            <div class="modal-header"><h5 class="modal-title">Edit {{ $country->name }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                            <div class="modal-body">@include('admin.countries.partials.form', ['country' => $country])</div>
                            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan</button></div>
                        </form>
                    </div></div>
                </div>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-5">Belum ada negara tersimpan. Tambahkan manual atau sinkronkan API.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $countries->links('pagination::bootstrap-5') }}
</div>

<div class="modal fade" id="addCountryModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
        <form method="POST" action="{{ route('admin.countries.store') }}">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Tambah Negara</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">@include('admin.countries.partials.form', ['country' => null])</div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Tambahkan</button></div>
        </form>
    </div></div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('countrySyncForm')?.addEventListener('submit', function (event) {
        if (!confirm('Sinkronkan data negara dari API? Data dengan kode yang sama akan diperbarui.')) {
            event.preventDefault();
            return;
        }

        const button = document.getElementById('countrySyncButton');
        button.disabled = true;
        document.getElementById('countrySyncSpinner').classList.remove('d-none');
        document.getElementById('countrySyncIcon').classList.add('d-none');
        document.getElementById('countrySyncText').textContent = 'Sedang menyinkronkan...';
    });
</script>
@endpush
