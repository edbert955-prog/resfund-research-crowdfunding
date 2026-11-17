@extends('layouts.app')
@section('title', 'Masuk')
@section('content')
<section class="auth-section">
    <div class="container auth-container">
        <div class="auth-aside">
            <span class="eyebrow eyebrow-light">Research for Impact</span>
            <h1>Terhubung dengan riset yang Anda dukung.</h1>
            <p>ResFund mempertemukan donatur, peneliti, reviewer, dan administrator dalam satu alur pendanaan riset yang transparan.</p>
            <div class="auth-aside-points">
                <div><x-icon name="heart" size="20" /><p>Donatur mendukung dan memantau proyek riset.</p></div>
                <div><x-icon name="flask" size="20" /><p>Peneliti mengajukan proposal dan melaporkan milestone.</p></div>
                <div><x-icon name="clipboard-check" size="20" /><p>Reviewer menilai bukti secara independen.</p></div>
                <div><x-icon name="shield-check" size="20" /><p>Administrator mengelola validasi, penugasan, dan audit sistem.</p></div>
            </div>
        </div>

        <div class="auth-card-wrap">
            <div class="auth-card">
                <div class="auth-card-heading">
                    <span class="auth-mark"><img src="{{ asset('assets/images/resfund-mark.png') }}" alt="" width="48" height="48"></span>
                    <span class="eyebrow">Akses akun</span>
                    <h2>Masuk ke ResFund</h2>
                    <p>Gunakan email dan kata sandi sesuai akun Anda.</p>
                </div>

                @if($intent === 'donasi' && $projectSlug)
                    <div class="form-alert form-alert-info" role="status">Masuk atau buat akun sebagai <strong>Donatur</strong> untuk menghubungkan donasi dengan riwayat akun. Pembayaran tetap dilakukan pada tahap checkout melalui Payment Gateway.</div>
                @endif

                @if($errors->any())
                    <div class="form-alert form-alert-error" role="alert" aria-live="assertive">
                        <strong>Periksa kembali data yang dimasukkan:</strong>
                        <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.submit') }}" class="auth-form">
                    @csrf
                    <input type="hidden" name="intent" value="{{ $intent }}">
                    <input type="hidden" name="project_slug" value="{{ $projectSlug }}">
                    <div class="form-group">
                        <label for="email">Alamat email <span aria-hidden="true">*</span></label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="nama@email.com" autocomplete="email" required @if($errors->has('email')) aria-describedby="email-error" aria-invalid="true" @endif>
                        @error('email')<small class="field-error" id="email-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="form-group">
                        <div class="label-row"><label for="password">Kata sandi <span aria-hidden="true">*</span></label><a href="{{ route('password.request') }}">Lupa kata sandi?</a></div>
                        <div class="password-field">
                            <input id="password" name="password" type="password" placeholder="Masukkan kata sandi" autocomplete="current-password" required @if($errors->has('password')) aria-describedby="password-error" aria-invalid="true" @endif>
                            <button type="button" class="password-toggle" data-password-toggle="password" aria-label="Tampilkan kata sandi">Lihat</button>
                        </div>
                        @error('password')<small class="field-error" id="password-error">{{ $message }}</small>@enderror
                    </div>
                    <button type="submit" class="button button-primary button-full button-large">Masuk</button>
                </form>

                <div class="auth-separator"><span>atau</span></div>
                <p class="auth-switch">Belum memiliki akun? <a href="{{ route('register', array_filter(['intent' => $intent, 'project' => $projectSlug])) }}">Buat akun</a></p>

                <div class="role-access-note">
                    <strong>Bagaimana dengan Reviewer dan Administrator?</strong>
                    <p>Keduanya tidak mendaftar secara terbuka. Reviewer menerima akses setelah ditugaskan Administrator, sedangkan akun Administrator dibuat oleh pengelola sistem.</p>
                </div>

                <div class="demo-credentials">
                    <div class="demo-credentials-heading"><x-icon name="info" size="17" /><strong>Akun demo untuk mencoba prototype</strong></div>
                    <div><span>Donatur</span><code>donatur@resfund.test</code></div>
                    <div><span>Peneliti</span><code>peneliti@resfund.test</code></div>
                    <div><span>Reviewer A</span><code>reviewer@resfund.test</code></div>
                    <div><span>Reviewer B</span><code>reviewer2@resfund.test</code></div>
                    <div><span>Administrator</span><code>admin@resfund.test</code></div>
                    <div><span>Kata sandi demo</span><code>ResFund123!</code></div>
                    <div class="demo-account-actions"><button type="button" class="text-button" data-fill-demo>Isi Donatur</button><button type="button" class="text-button" data-fill-researcher>Isi Peneliti</button><button type="button" class="text-button" data-fill-reviewer="reviewer@resfund.test">Isi Reviewer A</button><button type="button" class="text-button" data-fill-reviewer="reviewer2@resfund.test">Isi Reviewer B</button><button type="button" class="text-button" data-fill-admin>Isi Administrator</button></div>
                </div>
                <p class="auth-footnote">Akun demo dan autentikasi pada prototipe ini menggunakan sesi sementara. Belum terhubung ke basis data pengguna operasional.</p>
            </div>
        </div>
    </div>
</section>
@endsection
