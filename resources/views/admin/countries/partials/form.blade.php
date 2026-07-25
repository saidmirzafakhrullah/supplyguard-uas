@php($item = $country ?? null)

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Nama Negara *</label>
        <input class="form-control" name="name" required maxlength="150"
            value="{{ old('name', $item?->name) }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Kode ISO-2</label>
        <input class="form-control text-uppercase" name="alpha2_code" maxlength="2"
            value="{{ old('alpha2_code', $item?->alpha2_code) }}" placeholder="ID">
    </div>
    <div class="col-md-3">
        <label class="form-label">Kode ISO-3 *</label>
        <input class="form-control text-uppercase" name="code" required maxlength="3"
            value="{{ old('code', $item?->code) }}" placeholder="IDN">
    </div>
    <div class="col-12">
        <label class="form-label">Nama Resmi</label>
        <input class="form-control" name="official_name" maxlength="255"
            value="{{ old('official_name', $item?->official_name) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Ibu Kota</label>
        <input class="form-control" name="capital" value="{{ old('capital', $item?->capital) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Wilayah</label>
        <input class="form-control" name="region" value="{{ old('region', $item?->region) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Subwilayah</label>
        <input class="form-control" name="subregion" value="{{ old('subregion', $item?->subregion) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Populasi *</label>
        <input type="number" min="0" class="form-control" name="population" required
            value="{{ old('population', $item?->population ?? 0) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Kode Mata Uang</label>
        <input class="form-control text-uppercase" name="currency_code" maxlength="10"
            value="{{ old('currency_code', $item?->currency_code) }}" placeholder="IDR">
    </div>
    <div class="col-md-4">
        <label class="form-label">Nama Mata Uang</label>
        <input class="form-control" name="currency_name"
            value="{{ old('currency_name', $item?->currency_name) }}">
    </div>
    <div class="col-12">
        <label class="form-label">Bahasa</label>
        <input class="form-control" name="languages" maxlength="1000"
            value="{{ old('languages', $item?->languages) }}"
            placeholder="Contoh: Indonesian, English">
    </div>
    <div class="col-md-6">
        <label class="form-label">Latitude</label>
        <input type="number" step="any" class="form-control" name="latitude"
            value="{{ old('latitude', $item?->latitude) }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Longitude</label>
        <input type="number" step="any" class="form-control" name="longitude"
            value="{{ old('longitude', $item?->longitude) }}">
    </div>
    <div class="col-12">
        <label class="form-label">URL Bendera</label>
        <input type="url" class="form-control" name="flag"
            value="{{ old('flag', $item?->flag) }}">
    </div>
    <div class="col-12">
        <div class="form-check">
            <input type="hidden" name="landlocked" value="0">
            <input class="form-check-input" type="checkbox" name="landlocked" value="1"
                id="landlocked{{ $item?->id ?? 'New' }}"
                @checked(old('landlocked', $item?->landlocked ?? false))>
            <label class="form-check-label" for="landlocked{{ $item?->id ?? 'New' }}">
                Negara tanpa akses laut
            </label>
        </div>
    </div>
</div>
