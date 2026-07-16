@php
    $editing = isset($article) && $article;

    $categoryOptions = [
        'supply_chain' => 'Rantai Pasok',
        'weather' => 'Cuaca',
        'currency' => 'Mata Uang',
        'port' => 'Pelabuhan',
        'news' => 'Berita',
        'economy' => 'Ekonomi',
        'geopolitics' => 'Geopolitik',
        'logistics' => 'Logistik',
    ];

    $statusOptions = [
        'draft' => 'Draft',
        'published' => 'Dipublikasi',
    ];

    $sentimentOptions = [
        'positive' => 'Positif',
        'neutral' => 'Netral',
        'negative' => 'Negatif',
    ];

    $riskOptions = [
        'low' => 'Rendah',
        'medium' => 'Sedang',
        'high' => 'Tinggi',
        'critical' => 'Kritis',
    ];
@endphp

<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label">
            Judul Artikel
        </label>

        <input
            type="text"
            name="title"
            class="form-control"
            value="{{ $editing ? $article->title : old('title') }}"
            placeholder="Contoh: Risiko Cuaca Ekstrem terhadap Pengiriman Barang"
            required
        >
    </div>

    <div class="col-md-4">
        <label class="form-label">
            Kategori
        </label>

        <select
            name="category"
            class="form-select"
            required
        >
            @foreach($categoryOptions as $value => $label)
                <option
                    value="{{ $value }}"
                    @selected(
                        ($editing ? $article->category : old('category', 'supply_chain'))
                        === $value
                    )
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">
            Status
        </label>

        <select
            name="status"
            class="form-select"
            required
        >
            @foreach($statusOptions as $value => $label)
                <option
                    value="{{ $value }}"
                    @selected(
                        ($editing ? $article->status : old('status', 'draft'))
                        === $value
                    )
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">
            Sentimen
        </label>

        <select
            name="sentiment"
            class="form-select"
            required
        >
            @foreach($sentimentOptions as $value => $label)
                <option
                    value="{{ $value }}"
                    @selected(
                        ($editing ? $article->sentiment : old('sentiment', 'neutral'))
                        === $value
                    )
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">
            Level Risiko
        </label>

        <select
            name="risk_level"
            class="form-select"
            required
        >
            @foreach($riskOptions as $value => $label)
                <option
                    value="{{ $value }}"
                    @selected(
                        ($editing ? $article->risk_level : old('risk_level', 'medium'))
                        === $value
                    )
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">
            Sumber Artikel
        </label>

        <input
            type="text"
            name="source"
            class="form-control"
            value="{{ $editing ? $article->source : old('source') }}"
            placeholder="Contoh: SupplyGuard Internal Analysis"
        >
    </div>

    <div class="col-md-6">
        <label class="form-label">
            Penulis
        </label>

        <input
            type="text"
            name="author"
            class="form-control"
            value="{{ $editing ? $article->author : old('author', 'Administrator SupplyGuard') }}"
            placeholder="Nama penulis"
        >
    </div>

    <div class="col-md-6">
        <label class="form-label">
            Tanggal Publikasi
        </label>

        <input
            type="datetime-local"
            name="published_at"
            class="form-control"
            value="{{
                $editing && $article->published_at
                    ? $article->published_at->format('Y-m-d\TH:i')
                    : old('published_at')
            }}"
        >

        <small class="text-muted">
            Boleh dikosongkan. Jika status dipublikasi, sistem akan mengisi otomatis.
        </small>
    </div>

    <div class="col-12">
        <label class="form-label">
            Ringkasan
        </label>

        <textarea
            name="summary"
            class="form-control"
            rows="3"
            placeholder="Tuliskan ringkasan singkat artikel..."
        >{{ $editing ? $article->summary : old('summary') }}</textarea>
    </div>

    <div class="col-12">
        <label class="form-label">
            Isi Artikel
        </label>

        <textarea
            name="content"
            class="form-control"
            rows="8"
            placeholder="Tuliskan isi artikel analisis..."
            required
        >{{ $editing ? $article->content : old('content') }}</textarea>
    </div>
</div>