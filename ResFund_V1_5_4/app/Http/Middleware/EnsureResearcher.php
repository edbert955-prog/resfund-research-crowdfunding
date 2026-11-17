<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureResearcher
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->session()->get('prototype_user');

        if (! is_array($user)) {
            return redirect()->route('login')->with('info', 'Masuk menggunakan akun Peneliti untuk membuka dashboard.');
        }

        if (($user['role'] ?? null) !== 'peneliti') {
            return redirect()->route('home')->with('info', 'Area ini hanya dapat diakses menggunakan akun Peneliti.');
        }

        return $next($request);
    }
}
