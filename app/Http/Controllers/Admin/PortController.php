<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Port;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

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

        return $validated;
    }
}