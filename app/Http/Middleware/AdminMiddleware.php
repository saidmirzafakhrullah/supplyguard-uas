<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Memastikan hanya administrator
     * yang dapat membuka halaman admin.
     */
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $user = $request->user();

        if (!$user || $user->role !== 'admin') {
            return redirect()
                ->route('dashboard')
                ->with(
                    'error',
                    'Akses ditolak. Halaman ini hanya dapat dibuka oleh administrator.'
                );
        }

        return $next($request);
    }
}