@php
    $editing = isset($word) && $word;

    $typeOptions = [
        'positive' => 'Positif',
        'negative' => 'Negatif',
    ];

    $statusOptions = [
        'active' => 'Aktif',
        'inactive' => 'Tidak Aktif',
    ];
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">
            Kata
        </label>

        <input
            type="text"
            name="word"
            class="form-control text-lowercase"
            value="{{ $editing ? $word->word : old('word') }}"
            placeholder="Contoh: growth"
            required
        >
    </div>

    <div class="col-md-6">
        <label class="form-label">
            Jenis Sentimen
        </label>

        <select
            name="type"
            class="form-select"
            required
        >
            @foreach($typeOptions as $value => $label)
                <option
                    value="{{ $value }}"
                    @selected(
                        ($editing ? $word->type : old('type', 'positive'))
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
            Kategori
        </label>

        <input
            type="text"
            name="category"
            class="form-control"
            value="{{ $editing ? $word->category : old('category') }}"
            placeholder="Contoh: Economy, Logistics, Port"
        >
    </div>

    <div class="col-md-3">
        <label class="form-label">
            Bobot
        </label>

        <input
            type="number"
            name="weight"
            class="form-control"
            min="1"
            max="5"
            value="{{ $editing ? $word->weight : old('weight', 1) }}"
            required
        >

        <small class="text-muted">
            1 sampai 5
        </small>
    </div>

    <div class="col-md-3">
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
                        ($editing ? $word->status : old('status', 'active'))
                        === $value
                    )
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-12">
        <label class="form-label">
            Makna Kata
        </label>

        <textarea
            name="meaning"
            class="form-control"
            rows="3"
            placeholder="Tuliskan arti atau pengaruh kata ini terhadap analisis sentimen..."
        >{{ $editing ? $word->meaning : old('meaning') }}</textarea>
    </div>
</div>