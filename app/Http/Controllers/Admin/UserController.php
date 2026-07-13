<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Menampilkan halaman manajemen pengguna.
     */
    public function index()
    {
        $users = User::query()
            ->orderByRaw(
                "CASE WHEN role = 'admin' THEN 0 ELSE 1 END"
            )
            ->orderBy('name')
            ->get()
            ->map(function (User $user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,

                    'role' => $user->role === 'admin'
                        ? 'admin'
                        : 'user',

                    'created_at' => $user->created_at
                        ? $user->created_at->format(
                            'd M Y, H:i'
                        )
                        : '-',
                ];
            })
            ->values()
            ->toArray();

        $summary = [
            'total_users' => User::query()->count(),

            'admin_users' => User::query()
                ->where('role', 'admin')
                ->count(),

            'regular_users' => User::query()
                ->where('role', 'user')
                ->count(),

            'new_users_this_month' => User::query()
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->count(),
        ];

        return view(
            'admin.users.index',
            compact('users', 'summary')
        );
    }

    /**
     * Mengubah role pengguna.
     */
    public function updateRole(
        Request $request,
        User $user
    ): RedirectResponse {
        $validated = $request->validate([
            'role' => [
                'required',
                Rule::in(['admin', 'user']),
            ],
        ], [
            'role.required' =>
                'Role pengguna wajib dipilih.',

            'role.in' =>
                'Role pengguna tidak valid.',
        ]);

        /*
         * Administrator tidak boleh mengubah
         * role akun yang sedang digunakan.
         */
        if (
            (int) $request->user()->id
            === (int) $user->id
        ) {
            return back()->with(
                'error',
                'Role akun yang sedang digunakan tidak dapat diubah.'
            );
        }

        /*
         * Mencegah sistem kehilangan seluruh admin.
         */
        if (
            $user->role === 'admin'
            && $validated['role'] === 'user'
            && User::query()
                ->where('role', 'admin')
                ->count() <= 1
        ) {
            return back()->with(
                'error',
                'Role tidak dapat diubah karena sistem harus memiliki minimal satu administrator.'
            );
        }

        if ($user->role === $validated['role']) {
            return back()->with(
                'info',
                'Role pengguna tidak mengalami perubahan.'
            );
        }

        $user->role = $validated['role'];
        $user->save();

        return back()->with(
            'success',
            'Role pengguna '
            . $user->name
            . ' berhasil diubah menjadi '
            . (
                $user->role === 'admin'
                    ? 'Administrator.'
                    : 'Pengguna.'
            )
        );
    }

    /**
     * Menghapus akun pengguna.
     */
    public function destroy(
        Request $request,
        User $user
    ): RedirectResponse {
        /*
         * Administrator tidak boleh menghapus
         * akun yang sedang digunakan.
         */
        if (
            (int) $request->user()->id
            === (int) $user->id
        ) {
            return back()->with(
                'error',
                'Akun yang sedang digunakan tidak dapat dihapus.'
            );
        }

        /*
         * Administrator terakhir tidak dapat dihapus.
         */
        if (
            $user->role === 'admin'
            && User::query()
                ->where('role', 'admin')
                ->count() <= 1
        ) {
            return back()->with(
                'error',
                'Administrator terakhir tidak dapat dihapus.'
            );
        }

        $userName = $user->name;

        $user->delete();

        return back()->with(
            'success',
            'Akun '
            . $userName
            . ' berhasil dihapus.'
        );
    }
}