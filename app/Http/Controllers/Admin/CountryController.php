<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class CountryController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $countries = Country::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('name', 'like', "%{$search}%")
                        ->orWhere('official_name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('region', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.countries.index', compact('countries', 'search'));
    }

    public function store(Request $request): RedirectResponse
    {
        Country::query()->create($this->validateCountry($request));

        return to_route('admin.countries.index')
            ->with('success', 'Data negara berhasil ditambahkan.');
    }

    public function update(Request $request, Country $country): RedirectResponse
    {
        $country->update($this->validateCountry($request, $country));

        return to_route('admin.countries.index')
            ->with('success', 'Data negara berhasil diperbarui.');
    }

    public function destroy(Country $country): RedirectResponse
    {
        $name = $country->name;
        $country->delete();

        return to_route('admin.countries.index')
            ->with('success', "Negara {$name} berhasil dihapus.");
    }

    public function sync(): RedirectResponse
    {
        try {
            $apiKey = config('services.rest_countries.key');
            if (empty($apiKey)) {
                throw new RuntimeException('REST Countries API key belum dikonfigurasi.');
            }

            $baseUrl = rtrim(config(
                'services.rest_countries.base_url',
                'https://api.restcountries.com/countries/v5'
            ), '/');

            $allCountries = [];
            $limit = 100;
            $offset = 0;
            $more = true;

            while ($more) {
                $response = Http::withToken($apiKey)
                    ->acceptJson()
                    ->timeout(30)
                    ->retry(2, 500)
                    ->get($baseUrl, [
                        'limit' => $limit,
                        'offset' => $offset,
                        'response_fields' => implode(',', [
                            'names.common', 'names.official',
                            'codes.alpha_2', 'codes.alpha_3', 'capitals', 'region',
                            'subregion', 'population', 'currencies',
                            'languages',
                            'coordinates.lat', 'coordinates.lng',
                            'flag.url_png', 'flag.url_svg', 'landlocked',
                        ]),
                    ]);

                if (!$response->successful()) {
                    throw new RuntimeException(
                        'REST Countries API gagal dengan status HTTP '.$response->status().'.'
                    );
                }

                $objects = $response->json('data.objects', []);
                $meta = $response->json('data.meta', []);
                if (!is_array($objects)) {
                    throw new RuntimeException('Format respons REST Countries API tidak valid.');
                }

                $allCountries = array_merge($allCountries, $objects);
                $more = (bool) ($meta['more'] ?? false);
                $offset += $limit;

                if ($offset > 500) {
                    break;
                }
            }

            if ($allCountries === []) {
                throw new RuntimeException('REST Countries API tidak mengembalikan data negara.');
            }

            $synced = 0;
            foreach ($allCountries as $item) {
                $code = strtoupper((string) data_get($item, 'codes.alpha_3', ''));
                if (!preg_match('/^[A-Z]{3}$/', $code)) {
                    continue;
                }

                $currencyCode = array_key_first($item['currencies'] ?? []);
                $currency = $currencyCode
                    ? ($item['currencies'][$currencyCode] ?? [])
                    : [];

                if (array_is_list($item['currencies'] ?? [])) {
                    $currency = data_get($item, 'currencies.0', []);
                    $currencyCode = data_get($currency, 'code')
                        ?? data_get($currency, 'currency_code');
                }

                $languages = collect($item['languages'] ?? [])
                    ->map(function ($language) {
                        return is_array($language)
                            ? data_get($language, 'name')
                            : $language;
                    })
                    ->filter()
                    ->values()
                    ->implode(', ');

                Country::query()->updateOrCreate(
                    ['code' => $code],
                    [
                        'name' => data_get($item, 'names.common', $code),
                        'alpha2_code' => strtoupper((string) data_get($item, 'codes.alpha_2')) ?: null,
                        'official_name' => data_get($item, 'names.official'),
                        'capital' => data_get($item, 'capitals.0.name')
                            ?? data_get($item, 'capitals.0'),
                        'region' => $item['region'] ?? null,
                        'subregion' => $item['subregion'] ?? null,
                        'population' => max(0, (int) ($item['population'] ?? 0)),
                        'currency_code' => $currencyCode,
                        'currency_name' => $currency['name'] ?? null,
                        'languages' => $languages ?: null,
                        'latitude' => data_get($item, 'coordinates.lat'),
                        'longitude' => data_get($item, 'coordinates.lng'),
                        'flag' => data_get($item, 'flag.url_png')
                            ?? data_get($item, 'flag.url_svg'),
                        'landlocked' => (bool) ($item['landlocked'] ?? false),
                        'source' => 'REST Countries API v5',
                        'last_synced_at' => now(),
                    ]
                );
                $synced++;
            }

            if ($synced === 0) {
                throw new RuntimeException('Tidak ada kode negara valid pada respons API.');
            }

            return to_route('admin.countries.index')
                ->with('success', "Sinkronisasi selesai: {$synced} negara tersimpan.");
        } catch (Throwable $exception) {
            report($exception);

            return to_route('admin.countries.index')
                ->with('error', 'Sinkronisasi gagal: '.$exception->getMessage());
        }
    }

    private function validateCountry(Request $request, ?Country $country = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'official_name' => ['nullable', 'string', 'max:255'],
            'code' => [
                'required', 'string', 'size:3',
                Rule::unique('countries', 'code')->ignore($country),
            ],
            'alpha2_code' => [
                'nullable', 'string', 'size:2',
                Rule::unique('countries', 'alpha2_code')->ignore($country),
            ],
            'capital' => ['nullable', 'string', 'max:150'],
            'region' => ['nullable', 'string', 'max:100'],
            'subregion' => ['nullable', 'string', 'max:150'],
            'population' => ['required', 'integer', 'min:0'],
            'currency_code' => ['nullable', 'string', 'max:10'],
            'currency_name' => ['nullable', 'string', 'max:100'],
            'languages' => ['nullable', 'string', 'max:1000'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'flag' => ['nullable', 'url', 'max:2000'],
            'landlocked' => ['nullable', 'boolean'],
        ]);

        $data['code'] = strtoupper($data['code']);
        $data['alpha2_code'] = strtoupper((string) ($data['alpha2_code'] ?? '')) ?: null;
        $data['currency_code'] = strtoupper((string) ($data['currency_code'] ?? '')) ?: null;
        $data['landlocked'] = $request->boolean('landlocked');
        $data['source'] = 'Manual Admin';

        return $data;
    }
}
