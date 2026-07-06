@extends('layouts.app')

@section('title', 'Global Country - SupplyGuard')
@section('page-title', 'Global Country Dashboard')

@section('content')
<div class="card sg-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold mb-1">Global Country Dashboard</h4>
            <p class="text-muted mb-0">
                Monitoring data semua negara, mata uang, bahasa, populasi, wilayah, dan indikator awal risiko supply chain.
            </p>
        </div>

        <span class="badge bg-primary">REST Countries API</span>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card sg-card p-4">
            <h5 class="fw-bold">Pilih Negara</h5>
            <p class="text-muted">
                Pilih negara dari daftar global untuk melihat detail profil negara.
            </p>

            <label class="form-label">Country</label>
            <select id="countrySelect" class="form-select mb-3">
                @foreach($countries as $index => $country)
                    <option value="{{ $index }}">
                        {{ $country['name'] }}
                    </option>
                @endforeach
            </select>

            <button onclick="showCountry()" class="btn btn-primary w-100">
                Tampilkan Data
            </button>

            <div class="alert alert-info mt-3 mb-0">
                Total negara terbaca:
                <b>{{ count($countries) }}</b>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card sg-card p-4">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h5 class="fw-bold mb-1">Country Profile</h5>
                    <small class="text-muted">Detail negara berdasarkan data REST Countries API.</small>
                </div>

                <img id="countryFlag" src="" alt="Flag" style="width: 70px; border-radius: 8px; display: none;">
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Country</small>
                        <h5 id="countryName" class="mb-0">-</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Official Name</small>
                        <h5 id="officialName" class="mb-0">-</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Country Code</small>
                        <h5 id="countryCode" class="mb-0">-</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Capital</small>
                        <h5 id="capital" class="mb-0">-</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Region</small>
                        <h5 id="region" class="mb-0">-</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Subregion</small>
                        <h5 id="subregion" class="mb-0">-</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Population</small>
                        <h5 id="population" class="mb-0">-</h5>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3">
                        <small class="text-muted">Currency</small>
                        <h5 id="currency" class="mb-0">-</h5>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="border rounded p-3">
                        <small class="text-muted">Languages</small>
                        <h5 id="languages" class="mb-0">-</h5>
                    </div>
                </div>
            </div>

            <div class="alert alert-primary mt-4 mb-0">
                Data negara ini akan dipakai sebagai dasar untuk fitur cuaca, kurs mata uang,
                risk scoring, comparison country, dan watchlist.
            </div>
        </div>
    </div>
</div>

<div class="card sg-card p-4 mt-4">
    <h5 class="fw-bold mb-3">Global Country Dataset Preview</h5>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Flag</th>
                    <th>Country</th>
                    <th>Code</th>
                    <th>Capital</th>
                    <th>Region</th>
                    <th>Population</th>
                    <th>Currency</th>
                </tr>
            </thead>

            <tbody>
                @foreach(array_slice($countries, 0, 20) as $index => $country)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            @if($country['flag'])
                                <img src="{{ $country['flag'] }}" alt="Flag" style="width: 35px; border-radius: 4px;">
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $country['name'] }}</td>
                        <td>{{ $country['code'] }}</td>
                        <td>{{ $country['capital'] }}</td>
                        <td>{{ $country['region'] }}</td>
                        <td>{{ number_format($country['population']) }}</td>
                        <td>{{ $country['currency'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <small class="text-muted">
        Tabel ini menampilkan 20 negara pertama sebagai preview. Data lengkap tetap tersedia di dropdown.
    </small>
</div>
@endsection

@push('scripts')
<script>
    const countries = @json($countries);

    function showCountry() {
        const selectedIndex = document.getElementById('countrySelect').value;
        const country = countries[selectedIndex];

        document.getElementById('countryName').innerText = country.name ?? '-';
        document.getElementById('officialName').innerText = country.official_name ?? '-';
        document.getElementById('countryCode').innerText = country.code ?? '-';
        document.getElementById('capital').innerText = country.capital ?? '-';
        document.getElementById('region').innerText = country.region ?? '-';
        document.getElementById('subregion').innerText = country.subregion ?? '-';
        document.getElementById('population').innerText = Number(country.population ?? 0).toLocaleString();
        document.getElementById('currency').innerText = country.currency ?? '-';
        document.getElementById('languages').innerText = country.languages ?? '-';

        const flag = document.getElementById('countryFlag');

        if (country.flag) {
            flag.src = country.flag;
            flag.style.display = 'block';
        } else {
            flag.style.display = 'none';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        showCountry();
    });
</script>
@endpush