@extends('layouts.app')

@section('title', 'Kamus Kata Sentimen - SupplyGuard')
@section('page-title', 'Manajemen Admin - Kata Sentimen')

@section('content')

@php
    $typeLabels = [
        'positive' => 'Positif',
        'negative' => 'Negatif',
    ];

    $typeClasses = [
        'positive' => 'bg-success',
        'negative' => 'bg-danger',
    ];

    $statusLabels = [
        'active' => 'Aktif',
        'inactive' => 'Tidak Aktif',
    ];

    $statusClasses = [
        'active' => 'bg-success',
        'inactive' => 'bg-secondary',
    ];

    $sentimentClasses = [
        'Positive' => 'bg-success',
        'Neutral' => 'bg-secondary',
        'Negative' => 'bg-danger',
    ];

    $sentimentLabels = [
        'Positive' => 'Positif',
        'Neutral' => 'Netral',
        'Negative' => 'Negatif',
    ];
@endphp

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
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div>
            <h4 class="fw-bold mb-1">
                Kamus Kata Sentimen
            </h4>

            <p class="text-muted mb-0">
                Kelola kata positif dan negatif yang digunakan untuk analisis sentimen berita rantai pasok.
            </p>
        </div>

        <button
            type="button"
            class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#addWordModal"
        >
            <i class="bi bi-plus-lg me-1"></i>
            Tambah Kata
        </button>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card sg-card p-4 h-100">
            <small class="text-muted">Total Kata</small>
            <h3 class="fw-bold mb-1">
                {{ $summary['total_words'] }}
            </h3>
            <span class="text-primary small">
                Seluruh kamus
            </span>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card sg-card p-4 h-100">
            <small class="text-muted">Kata Positif</small>
            <h3 class="fw-bold text-success mb-1">
                {{ $summary['positive_words'] }}
            </h3>
            <span class="text-success small">
                Mendukung sentimen baik
            </span>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card sg-card p-4 h-100">
            <small class="text-muted">Kata Negatif</small>
            <h3 class="fw-bold text-danger mb-1">
                {{ $summary['negative_words'] }}
            </h3>
            <span class="text-danger small">
                Menunjukkan risiko
            </span>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card sg-card p-4 h-100">
            <small class="text-muted">Kata Aktif</small>
            <h3 class="fw-bold text-primary mb-1">
                {{ $summary['active_words'] }}
            </h3>
            <span class="text-primary small">
                Digunakan analisis
            </span>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-5">
        <div class="card sg-card p-4 h-100">
            <h5 class="fw-bold mb-2">
                Simulasi Analisis Sentimen
            </h5>

            <p class="text-muted mb-3">
                Contoh kalimat dianalisis menggunakan kamus kata aktif.
            </p>

            <div class="alert alert-light border">
                <strong>Teks Contoh:</strong><br>
                {{ $sampleText }}
            </div>

            <table class="table align-middle mb-0">
                <tbody>
                    <tr>
                        <td>Skor Positif</td>
                        <td class="text-end fw-bold text-success">
                            {{ $analysis['positive_score'] }}
                        </td>
                    </tr>

                    <tr>
                        <td>Skor Negatif</td>
                        <td class="text-end fw-bold text-danger">
                            {{ $analysis['negative_score'] }}
                        </td>
                    </tr>

                    <tr>
                        <td>Kata Positif Terdeteksi</td>
                        <td class="text-end">
                            @forelse($analysis['positive_matches'] as $word)
                                <span class="badge bg-success">
                                    {{ $word }}
                                </span>
                            @empty
                                <span class="text-muted">-</span>
                            @endforelse
                        </td>
                    </tr>

                    <tr>
                        <td>Kata Negatif Terdeteksi</td>
                        <td class="text-end">
                            @forelse($analysis['negative_matches'] as $word)
                                <span class="badge bg-danger">
                                    {{ $word }}
                                </span>
                            @empty
                                <span class="text-muted">-</span>
                            @endforelse
                        </td>
                    </tr>

                    <tr>
                        <td>Hasil Sentimen</td>
                        <td class="text-end">
                            <span class="badge {{ $sentimentClasses[$analysis['sentiment']] ?? 'bg-secondary' }}">
                                {{
                                    $sentimentLabels[$analysis['sentiment']]
                                    ?? $analysis['sentiment']
                                }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card sg-card p-4 h-100">
            <h5 class="fw-bold mb-2">
                Penjelasan Kamus Sentimen
            </h5>

            <p class="text-muted mb-3">
                Fitur ini mendukung kebutuhan tugas pada bagian
                <strong>Lexicon Based Sentiment Analysis</strong>.
                Sistem menghitung jumlah kata positif dan negatif dari berita,
                lalu menentukan hasil sentimen.
            </p>

            <div class="alert alert-info mb-0">
                Kata dengan status <strong>Aktif</strong> digunakan dalam
                simulasi analisis. Bobot kata menentukan seberapa besar
                pengaruh kata tersebut terhadap hasil sentimen.
            </div>
        </div>
    </div>
</div>

<div class="card sg-card p-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
        <div>
            <h5 class="fw-bold mb-1">
                Daftar Kata Sentimen
            </h5>

            <small class="text-muted">
                Data kata tersimpan pada tabel sentiment_words.
            </small>
        </div>

        <form
            method="GET"
            action="{{ route('admin.words.index') }}"
            class="d-flex gap-2"
        >
            <input
                type="text"
                name="search"
                class="form-control"
                value="{{ $search }}"
                placeholder="Cari kata..."
                style="width: 280px; max-width: 100%;"
            >

            <button class="btn btn-outline-primary">
                <i class="bi bi-search"></i>
            </button>

            @if($search !== '')
                <a
                    href="{{ route('admin.words.index') }}"
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
                    <th>Kata</th>
                    <th>Jenis</th>
                    <th>Kategori</th>
                    <th>Bobot</th>
                    <th>Makna</th>
                    <th>Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($words as $index => $word)
                    <tr>
                        <td>
                            {{ $words->firstItem() + $index }}
                        </td>

                        <td class="fw-bold">
                            {{ $word->word }}
                        </td>

                        <td>
                            <span class="badge {{ $typeClasses[$word->type] ?? 'bg-secondary' }}">
                                {{ $typeLabels[$word->type] ?? $word->type }}
                            </span>
                        </td>

                        <td>
                            {{ $word->category ?: '-' }}
                        </td>

                        <td>
                            <span class="badge bg-primary">
                                {{ $word->weight }}
                            </span>
                        </td>

                        <td style="min-width: 260px;">
                            <small>
                                {{ $word->meaning ?: '-' }}
                            </small>
                        </td>

                        <td>
                            <span class="badge {{ $statusClasses[$word->status] ?? 'bg-secondary' }}">
                                {{ $statusLabels[$word->status] ?? $word->status }}
                            </span>
                        </td>

                        <td>
                            <div class="d-flex justify-content-center gap-2">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editWordModal{{ $word->id }}"
                                    title="Edit kata"
                                >
                                    <i class="bi bi-pencil-square"></i>
                                </button>

                                <form
                                    method="POST"
                                    action="{{ route('admin.words.destroy', $word) }}"
                                    onsubmit="return confirm('Hapus kata {{ addslashes($word->word) }}?')"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="btn btn-sm btn-outline-danger"
                                        title="Hapus kata"
                                    >
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td
                            colspan="8"
                            class="text-center text-muted py-5"
                        >
                            <i class="bi bi-chat-square-text fs-2 d-block mb-2"></i>
                            Belum ada kata sentimen.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($words->hasPages())
        <div class="mt-4">
            {{
                $words
                    ->onEachSide(1)
                    ->links('pagination::bootstrap-5')
            }}
        </div>
    @endif
</div>

<div
    class="modal fade"
    id="addWordModal"
    tabindex="-1"
>
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form
                method="POST"
                action="{{ route('admin.words.store') }}"
            >
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">
                        Tambah Kata Sentimen
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                    ></button>
                </div>

                <div class="modal-body">
                    @include(
                        'admin.sentiment_words.partials.form',
                        ['word' => null]
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
                        Tambahkan Kata
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($words as $word)
    <div
        class="modal fade"
        id="editWordModal{{ $word->id }}"
        tabindex="-1"
    >
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form
                    method="POST"
                    action="{{ route('admin.words.update', $word) }}"
                >
                    @csrf
                    @method('PUT')

                    <div class="modal-header">
                        <h5 class="modal-title">
                            Edit Kata Sentimen
                        </h5>

                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                        ></button>
                    </div>

                    <div class="modal-body">
                        @include(
                            'admin.sentiment_words.partials.form',
                            ['word' => $word]
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
@endforeach

@endsection