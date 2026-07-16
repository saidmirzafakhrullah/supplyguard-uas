@extends('layouts.app')

@section('title', 'Manajemen Artikel - SupplyGuard')
@section('page-title', 'Manajemen Admin - Artikel')

@section('content')

@php
    $categoryLabels = [
        'supply_chain' => 'Rantai Pasok',
        'weather' => 'Cuaca',
        'currency' => 'Mata Uang',
        'port' => 'Pelabuhan',
        'news' => 'Berita',
        'economy' => 'Ekonomi',
        'geopolitics' => 'Geopolitik',
        'logistics' => 'Logistik',
    ];

    $statusLabels = [
        'draft' => 'Draft',
        'published' => 'Dipublikasi',
    ];

    $sentimentLabels = [
        'positive' => 'Positif',
        'neutral' => 'Netral',
        'negative' => 'Negatif',
    ];

    $riskLabels = [
        'low' => 'Rendah',
        'medium' => 'Sedang',
        'high' => 'Tinggi',
        'critical' => 'Kritis',
    ];

    $riskClasses = [
        'low' => 'bg-success',
        'medium' => 'bg-warning text-dark',
        'high' => 'bg-danger',
        'critical' => 'bg-dark',
    ];

    $sentimentClasses = [
        'positive' => 'bg-success',
        'neutral' => 'bg-secondary',
        'negative' => 'bg-danger',
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
    <div
        class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3"
    >
        <div>
            <h4 class="fw-bold mb-1">
                Manajemen Artikel Analisis
            </h4>

            <p class="text-muted mb-0">
                Kelola artikel analisis yang membahas risiko cuaca,
                kurs, pelabuhan, berita, ekonomi, dan rantai pasok global.
            </p>
        </div>

        <button
            type="button"
            class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#addArticleModal"
        >
            <i class="bi bi-plus-lg me-1"></i>
            Tambah Artikel
        </button>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card sg-card p-4 h-100">
            <small class="text-muted">Total Artikel</small>
            <h3 class="fw-bold mb-1">
                {{ $summary['total_articles'] }}
            </h3>
            <span class="text-primary small">
                Semua data
            </span>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card sg-card p-4 h-100">
            <small class="text-muted">Dipublikasi</small>
            <h3 class="fw-bold text-success mb-1">
                {{ $summary['published_articles'] }}
            </h3>
            <span class="text-success small">
                Artikel aktif
            </span>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card sg-card p-4 h-100">
            <small class="text-muted">Draft</small>
            <h3 class="fw-bold text-warning mb-1">
                {{ $summary['draft_articles'] }}
            </h3>
            <span class="text-warning small">
                Belum dipublikasi
            </span>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card sg-card p-4 h-100">
            <small class="text-muted">Risiko Tinggi</small>
            <h3 class="fw-bold text-danger mb-1">
                {{ $summary['high_risk_articles'] }}
            </h3>
            <span class="text-danger small">
                High / Critical
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
                Daftar Artikel Analisis
            </h5>

            <small class="text-muted">
                Data artikel tersimpan pada tabel articles.
            </small>
        </div>

        <form
            method="GET"
            action="{{ route('admin.articles.index') }}"
            class="d-flex gap-2"
        >
            <input
                type="text"
                name="search"
                class="form-control"
                value="{{ $search }}"
                placeholder="Cari artikel..."
                style="width: 280px; max-width: 100%;"
            >

            <button class="btn btn-outline-primary">
                <i class="bi bi-search"></i>
            </button>

            @if($search !== '')
                <a
                    href="{{ route('admin.articles.index') }}"
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
                    <th>Artikel</th>
                    <th>Kategori</th>
                    <th>Status</th>
                    <th>Sentimen</th>
                    <th>Risiko</th>
                    <th>Tanggal Publikasi</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($articles as $index => $article)
                    <tr>
                        <td>
                            {{ $articles->firstItem() + $index }}
                        </td>

                        <td style="min-width: 280px;">
                            <div class="fw-bold">
                                {{ $article->title }}
                            </div>

                            <small class="text-muted d-block">
                                {{ $article->summary ?: 'Tidak ada ringkasan.' }}
                            </small>

                            <small class="text-muted">
                                Sumber:
                                {{ $article->source ?: '-' }}
                            </small>
                        </td>

                        <td>
                            <span class="badge bg-primary">
                                {{
                                    $categoryLabels[$article->category]
                                    ?? $article->category
                                }}
                            </span>
                        </td>

                        <td>
                            @if($article->status === 'published')
                                <span class="badge bg-success">
                                    Dipublikasi
                                </span>
                            @else
                                <span class="badge bg-warning text-dark">
                                    Draft
                                </span>
                            @endif
                        </td>

                        <td>
                            <span
                                class="badge {{ $sentimentClasses[$article->sentiment] ?? 'bg-secondary' }}"
                            >
                                {{
                                    $sentimentLabels[$article->sentiment]
                                    ?? $article->sentiment
                                }}
                            </span>
                        </td>

                        <td>
                            <span
                                class="badge {{ $riskClasses[$article->risk_level] ?? 'bg-secondary' }}"
                            >
                                {{
                                    $riskLabels[$article->risk_level]
                                    ?? $article->risk_level
                                }}
                            </span>
                        </td>

                        <td class="text-nowrap">
                            {{
                                $article->published_at
                                    ? $article->published_at->format('d M Y, H:i')
                                    : '-'
                            }}
                        </td>

                        <td>
                            <div class="d-flex justify-content-center gap-2">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editArticleModal{{ $article->id }}"
                                    title="Edit artikel"
                                >
                                    <i class="bi bi-pencil-square"></i>
                                </button>

                                <form
                                    method="POST"
                                    action="{{ route('admin.articles.destroy', $article) }}"
                                    onsubmit="return confirm('Hapus artikel {{ addslashes($article->title) }}?')"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="btn btn-sm btn-outline-danger"
                                        title="Hapus artikel"
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
                            <i class="bi bi-journal-text fs-2 d-block mb-2"></i>
                            Belum ada artikel analisis.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($articles->hasPages())
        <div class="mt-4">
            {{
                $articles
                    ->onEachSide(1)
                    ->links('pagination::bootstrap-5')
            }}
        </div>
    @endif
</div>

{{-- MODAL TAMBAH ARTIKEL --}}
<div
    class="modal fade"
    id="addArticleModal"
    tabindex="-1"
>
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <form
                method="POST"
                action="{{ route('admin.articles.store') }}"
            >
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">
                        Tambah Artikel Analisis
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                    ></button>
                </div>

                <div class="modal-body">
                    @include(
                        'admin.articles.partials.form',
                        ['article' => null]
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
                        Tambahkan Artikel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL EDIT ARTIKEL --}}
@foreach($articles as $article)
    <div
        class="modal fade"
        id="editArticleModal{{ $article->id }}"
        tabindex="-1"
    >
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <form
                    method="POST"
                    action="{{ route('admin.articles.update', $article) }}"
                >
                    @csrf
                    @method('PUT')

                    <div class="modal-header">
                        <h5 class="modal-title">
                            Edit Artikel Analisis
                        </h5>

                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                        ></button>
                    </div>

                    <div class="modal-body">
                        @include(
                            'admin.articles.partials.form',
                            ['article' => $article]
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

{{-- PENJELASAN --}}
<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">
        Penjelasan Manajemen Artikel
    </h5>

    <p class="text-muted mb-2">
        Fitur ini digunakan administrator untuk mengelola artikel
        analisis rantai pasok. Artikel dapat diberi kategori, sentimen,
        level risiko, dan status publikasi.
    </p>

    <div class="alert alert-info mb-0">
        Artikel analisis dapat menjadi bahan pendukung pada fitur
        News Intelligence dan penilaian risiko berbasis berita.
    </div>
</div>

@endsection