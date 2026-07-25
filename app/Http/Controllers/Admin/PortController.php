<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Port;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class PortController extends Controller
{
    /**
     * Menampilkan dataset pelabuhan.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $ports = Port::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('port_name', 'like', "%{$search}%")
                        ->orWhere('country', 'like', "%{$search}%")
                        ->orWhere('country_code', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('region', 'like', "%{$search}%");
                });
            })
            ->orderBy('country')
            ->orderBy('port_name')
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total_ports' => Port::query()->count(),

            'active_ports' => Port::query()
                ->where('status', 'active')
                ->count(),

            'limited_ports' => Port::query()
                ->where('status', 'limited')
                ->count(),

            'high_risk_ports' => Port::query()
                ->whereIn('risk_level', [
                    'high',
                    'critical',
                ])
                ->count(),
        ];

        return view(
            'admin.ports.index',
            compact(
                'ports',
                'summary',
                'search'
            )
        );
    }

    /**
     * Menambahkan data pelabuhan.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatePort($request);

        Port::query()->create($data);

        return redirect()
            ->route('admin.ports.index')
            ->with(
                'success',
                'Data pelabuhan berhasil ditambahkan.'
            );
    }

    /**
     * Memperbarui data pelabuhan.
     */
    public function update(
        Request $request,
        Port $port
    ): RedirectResponse {
        $data = $this->validatePort($request);

        $port->update($data);

        return redirect()
            ->route('admin.ports.index')
            ->with(
                'success',
                'Data pelabuhan berhasil diperbarui.'
            );
    }

    /**
     * Menghapus data pelabuhan.
     */
    public function destroy(Port $port): RedirectResponse
    {
        $portName = $port->port_name;

        $port->delete();

        return redirect()
            ->route('admin.ports.index')
            ->with(
                'success',
                'Pelabuhan '
                . $portName
                . ' berhasil dihapus.'
            );
    }

    /**
     * Mengimpor seluruh pelabuhan resmi dari NGA World Port Index.
     */
    public function syncWorldPortIndex(): RedirectResponse
    {
        $countryMap = Country::query()
            ->whereNotNull('alpha2_code')
            ->get()
            ->keyBy(fn (Country $country) => strtoupper($country->alpha2_code));

        if ($countryMap->isEmpty()) {
            return to_route('admin.ports.index')->with(
                'error',
                'Sinkronkan data negara terlebih dahulu agar kode negara WPI dapat dipetakan.'
            );
        }

        $endpoint = 'https://vcps.nga.mil/nauticalpubs-feature/rest/services/'
            . 'WPI/World_Port_Index_Viewer/FeatureServer/0/query';
        $offset = 0;
        $batchSize = 2000;
        $synced = 0;
        $skipped = 0;

        try {
            do {
                $response = Http::asForm()
                    ->withOptions(['verify' => false])
                    ->timeout(90)
                    ->retry(2, 1000)
                    ->post($endpoint, [
                        'where' => '1=1',
                        'outFields' => implode(',', [
                            'objectid', 'wpinumber', 'main_port_name',
                            'harbor_size_code', 'harbor_type_code',
                            'harbor_use_code', 'wpi_cc', 'unlocode',
                        ]),
                        'returnGeometry' => 'true',
                        'outSR' => '4326',
                        'orderByFields' => 'objectid ASC',
                        'resultOffset' => $offset,
                        'resultRecordCount' => $batchSize,
                        'f' => 'json',
                    ]);

                if (!$response->successful()) {
                    throw new RuntimeException('Server WPI merespons HTTP '.$response->status().'.');
                }

                $features = $response->json('features', []);
                if (!is_array($features)) {
                    throw new RuntimeException('Format respons WPI tidak valid.');
                }

                foreach ($features as $feature) {
                    $attributes = $feature['attributes'] ?? [];
                    $geometry = $feature['geometry'] ?? [];
                    $alpha2 = strtoupper((string) ($attributes['wpi_cc'] ?? ''));
                    $country = $countryMap->get($alpha2);

                    if (!$country || !isset($geometry['x'], $geometry['y'])) {
                        $skipped++;
                        continue;
                    }

                    $sizeCode = strtoupper((string) ($attributes['harbor_size_code'] ?? ''));
                    $capacity = match ($sizeCode) {
                        'L' => 'high',
                        'M' => 'medium',
                        default => 'low',
                    };
                    $riskLevel = $this->wpiRiskLevel(
                        $sizeCode,
                        (string) ($attributes['harbor_type_code'] ?? ''),
                        (string) ($attributes['harbor_use_code'] ?? '')
                    );
                    $wpiNumber = (int) ($attributes['wpinumber'] ?? 0);
                    $objectId = (int) ($attributes['objectid'] ?? 0);

                    Port::query()->updateOrCreate(
                        ['external_id' => 'WPI-'.($wpiNumber ?: $objectId)],
                        [
                            'port_name' => (string) ($attributes['main_port_name'] ?? 'Unknown Port'),
                            'country' => $country->name,
                            'country_code' => $country->code,
                            'region' => $country->region,
                            'latitude' => (float) $geometry['y'],
                            'longitude' => (float) $geometry['x'],
                            'status' => 'active',
                            'capacity' => $capacity,
                            'congestion_level' => 'low',
                            'risk_level' => $riskLevel,
                            'notes' => 'Data resmi NGA World Port Index.',
                            'source' => 'NGA World Port Index',
                            'wpi_number' => $wpiNumber ?: null,
                            'unlocode' => trim((string) ($attributes['unlocode'] ?? '')) ?: null,
                            'harbor_size' => $this->harborSize($sizeCode),
                            'harbor_type' => $this->harborType((string) ($attributes['harbor_type_code'] ?? '')),
                            'harbor_use' => $this->harborUse((string) ($attributes['harbor_use_code'] ?? '')),
                        ]
                    );
                    $synced++;
                }

                $offset += count($features);
                $more = (bool) $response->json('exceededTransferLimit', false);
            } while ($more && $features !== [] && $offset < 20000);

            if ($synced === 0) {
                throw new RuntimeException('Tidak ada pelabuhan yang dapat dipetakan ke negara tersimpan.');
            }

            return to_route('admin.ports.index')->with(
                'success',
                "Sinkronisasi WPI selesai: {$synced} pelabuhan tersimpan, {$skipped} dilewati."
            );
        } catch (Throwable $exception) {
            report($exception);
            return to_route('admin.ports.index')->with(
                'error',
                'Sinkronisasi World Port Index gagal: '.$exception->getMessage()
            );
        }
    }

    private function harborSize(string $code): string
    {
        return ['L' => 'Large', 'M' => 'Medium', 'S' => 'Small', 'V' => 'Very Small'][$code] ?? 'Unknown';
    }

    private function harborType(string $code): string
    {
        return [
            'CN' => 'Coastal Natural', 'CB' => 'Coastal Breakwater',
            'CT' => 'Coastal Tide Gates', 'RN' => 'River Natural',
            'RB' => 'River Basin', 'RT' => 'River Tide Gates',
            'LC' => 'Canal or Lake', 'OR' => 'Open Roadstead',
            'TH' => 'Typhoon Harbor', 'N' => 'None',
        ][strtoupper($code)] ?? 'Unknown';
    }

    private function harborUse(string $code): string
    {
        return [
            'FISH' => 'Fishing', 'MIL' => 'Military', 'CARGO' => 'Cargo',
            'FERRY' => 'Ferry', 'UNK' => 'Unknown',
        ][strtoupper($code)] ?? 'Unknown';
    }

    private function wpiRiskLevel(string $size, string $type, string $use): string
    {
        $score = ['L' => 15, 'M' => 25, 'S' => 35, 'V' => 45][$size] ?? 40;
        $score += in_array(strtoupper($type), ['N', 'OR'], true) ? 10 : 0;
        $score += strtoupper($use) === 'CARGO' ? -5 : 0;

        return match (true) {
            $score <= 25 => 'low',
            $score <= 50 => 'medium',
            $score <= 75 => 'high',
            default => 'critical',
        };
    }

    /**
     * Validasi data pelabuhan.
     */
    private function validatePort(Request $request): array
    {
        $validated = $request->validate([
            'port_name' => [
                'required',
                'string',
                'max:255',
            ],

            'country' => [
                'required',
                'string',
                'max:150',
            ],

            'country_code' => [
                'required',
                'string',
                'size:3',
            ],

            'city' => [
                'nullable',
                'string',
                'max:150',
            ],

            'region' => [
                'nullable',
                'string',
                'max:100',
            ],

            'latitude' => [
                'required',
                'numeric',
                'between:-90,90',
            ],

            'longitude' => [
                'required',
                'numeric',
                'between:-180,180',
            ],

            'status' => [
                'required',
                Rule::in([
                    'active',
                    'limited',
                    'inactive',
                ]),
            ],

            'capacity' => [
                'required',
                Rule::in([
                    'low',
                    'medium',
                    'high',
                ]),
            ],

            'congestion_level' => [
                'required',
                Rule::in([
                    'low',
                    'medium',
                    'high',
                ]),
            ],

            'risk_level' => [
                'required',
                Rule::in([
                    'low',
                    'medium',
                    'high',
                    'critical',
                ]),
            ],

            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ], [
            'port_name.required' =>
                'Nama pelabuhan wajib diisi.',

            'country.required' =>
                'Nama negara wajib diisi.',

            'country_code.required' =>
                'Kode negara wajib diisi.',

            'country_code.size' =>
                'Kode negara harus terdiri dari tiga karakter.',

            'latitude.required' =>
                'Latitude wajib diisi.',

            'longitude.required' =>
                'Longitude wajib diisi.',

            'latitude.between' =>
                'Latitude harus berada antara -90 sampai 90.',

            'longitude.between' =>
                'Longitude harus berada antara -180 sampai 180.',
        ]);

        $validated['country_code'] = strtoupper(
            $validated['country_code']
        );

        $validated['source'] = 'Manual Admin';

        return $validated;
    }
}
