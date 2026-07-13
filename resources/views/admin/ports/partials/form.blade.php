@php
    $editing = isset($port) && $port;
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">
            Nama Pelabuhan
        </label>

        <input
            type="text"
            name="port_name"
            class="form-control"
            value="{{ $editing ? $port->port_name : old('port_name') }}"
            required
        >
    </div>

    <div class="col-md-6">
        <label class="form-label">
            Negara
        </label>

        <input
            type="text"
            name="country"
            class="form-control"
            value="{{ $editing ? $port->country : old('country') }}"
            required
        >
    </div>

    <div class="col-md-4">
        <label class="form-label">
            Kode Negara
        </label>

        <input
            type="text"
            name="country_code"
            class="form-control text-uppercase"
            maxlength="3"
            value="{{ $editing ? $port->country_code : old('country_code') }}"
            placeholder="IDN"
            required
        >
    </div>

    <div class="col-md-4">
        <label class="form-label">
            Kota
        </label>

        <input
            type="text"
            name="city"
            class="form-control"
            value="{{ $editing ? $port->city : old('city') }}"
        >
    </div>

    <div class="col-md-4">
        <label class="form-label">
            Wilayah
        </label>

        <input
            type="text"
            name="region"
            class="form-control"
            value="{{ $editing ? $port->region : old('region') }}"
            placeholder="Asia"
        >
    </div>

    <div class="col-md-6">
        <label class="form-label">
            Latitude
        </label>

        <input
            type="number"
            step="0.0000001"
            name="latitude"
            class="form-control"
            value="{{ $editing ? $port->latitude : old('latitude') }}"
            required
        >
    </div>

    <div class="col-md-6">
        <label class="form-label">
            Longitude
        </label>

        <input
            type="number"
            step="0.0000001"
            name="longitude"
            class="form-control"
            value="{{ $editing ? $port->longitude : old('longitude') }}"
            required
        >
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
            @foreach([
                'active' => 'Aktif',
                'limited' => 'Terbatas',
                'inactive' => 'Tidak Aktif'
            ] as $value => $label)
                <option
                    value="{{ $value }}"
                    @selected(
                        ($editing ? $port->status : old('status', 'active'))
                        === $value
                    )
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label">
            Kapasitas
        </label>

        <select
            name="capacity"
            class="form-select"
            required
        >
            @foreach([
                'low' => 'Rendah',
                'medium' => 'Sedang',
                'high' => 'Tinggi'
            ] as $value => $label)
                <option
                    value="{{ $value }}"
                    @selected(
                        ($editing ? $port->capacity : old('capacity', 'medium'))
                        === $value
                    )
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label">
            Kemacetan
        </label>

        <select
            name="congestion_level"
            class="form-select"
            required
        >
            @foreach([
                'low' => 'Rendah',
                'medium' => 'Sedang',
                'high' => 'Tinggi'
            ] as $value => $label)
                <option
                    value="{{ $value }}"
                    @selected(
                        (
                            $editing
                                ? $port->congestion_level
                                : old('congestion_level', 'medium')
                        ) === $value
                    )
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label">
            Risiko
        </label>

        <select
            name="risk_level"
            class="form-select"
            required
        >
            @foreach([
                'low' => 'Rendah',
                'medium' => 'Sedang',
                'high' => 'Tinggi',
                'critical' => 'Kritis'
            ] as $value => $label)
                <option
                    value="{{ $value }}"
                    @selected(
                        (
                            $editing
                                ? $port->risk_level
                                : old('risk_level', 'low')
                        ) === $value
                    )
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-12">
        <label class="form-label">
            Catatan
        </label>

        <textarea
            name="notes"
            class="form-control"
            rows="3"
        >{{ $editing ? $port->notes : old('notes') }}</textarea>
    </div>
</div>