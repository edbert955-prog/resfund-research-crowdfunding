<?php

namespace App\Http\Middleware;

use App\Support\ReviewerPrototypeData;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureReviewer
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->session()->get('prototype_user');

        if (! is_array($user)) {
            return redirect()->route('login')->with('info', 'Masuk menggunakan akun Reviewer untuk membuka ruang penilaian.');
        }

        if (($user['role'] ?? null) !== 'reviewer') {
            return redirect()->route('home')->with('info', 'Area ini hanya dapat diakses menggunakan akun Reviewer.');
        }

        $reviewer = ReviewerPrototypeData::reviewerCatalog($request)[$user['reviewer_id'] ?? ''] ?? null;
        abort_unless(is_array($reviewer), 403, 'Identitas Reviewer tidak terdaftar pada sesi prototype ini.');
        if (($reviewer['status'] ?? 'active') !== 'active') {
            $request->session()->forget('prototype_user');
            return redirect()->route('login')->with('info', 'Akses Reviewer sedang dinonaktifkan oleh Administrator.');
        }

        return $next($request);
    }
}
