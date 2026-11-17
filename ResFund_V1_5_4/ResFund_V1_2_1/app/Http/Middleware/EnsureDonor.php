<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDonor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->session()->get('prototype_user');

        if (! is_array($user)) {
            return redirect()->route('login', ['next' => 'donatur'])
                ->with('info', 'Masuk menggunakan akun Donatur untuk membuka dashboard.');
        }

        if (($user['role'] ?? null) !== 'donatur') {
            return redirect()->route('home')
                ->with('info', 'Halaman ini hanya dapat diakses menggunakan akun Donatur.');
        }

        return $next($request);
    }
}
