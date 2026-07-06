<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()
            ->get()
            ->map(function ($user, $index) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $index === 0 ? 'Admin' : 'User',
                    'status' => 'Active',
                    'created_at' => $user->created_at
                        ? $user->created_at->format('d M Y')
                        : '-',
                ];
            })
            ->toArray();

        $summary = [
            'total_users' => count($users),
            'admin_users' => collect($users)->where('role', 'Admin')->count(),
            'regular_users' => collect($users)->where('role', 'User')->count(),
            'active_users' => collect($users)->where('status', 'Active')->count(),
        ];

        return view('admin.users.index', compact('users', 'summary'));
    }
}