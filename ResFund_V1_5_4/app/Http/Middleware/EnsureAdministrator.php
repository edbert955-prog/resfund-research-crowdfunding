<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdministrator
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->session()->get('prototype_user');

        if (! is_array($user)) {
            return redirect()->route('login')->with('info', 'Masuk menggunakan akun Administrator untuk membuka area administrasi.');
        }

        if (($user['role'] ?? null) !== 'administrator') {
            return redirect()->route('home')->with('info', 'Area ini hanya dapat diakses menggunakan akun Administrator.');
        }

        return $next($request);
    }
}
