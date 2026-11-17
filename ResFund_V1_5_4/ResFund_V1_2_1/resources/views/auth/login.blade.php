@extends('layouts.app')
@section('title', 'Masuk')
@section('content')
<section class="auth-section">
    <div class="container auth-container">
        <div class="auth-aside">
            <span class="eyebrow eyebrow-light">Selamat datang kembali</span>
            <h1>Terhubung dengan riset yang Anda dukung.</h1>
            <p>Masuk menggunakan akun yang sudah dibuat. Hak akses ditentukan oleh peran akun, bukan dipilih ulang saat login.</p>
            <div class="auth-aside-points">
                <div><span aria-hidden="true">✓</span><p>Donatur menjelajahi dan mendukung proyek riset.</p></div>
                <div><span aria-hidden="true">✓</span><p>Peneliti melalui proses verifikasi identitas akademik.</p></div>
            </div>
            <div class="auth-decoration auth-decoration-one"></div><div class="auth-decoration auth-decoration-two"></div>
        </div>

        <div class="auth-card-wrap">
            <div class="auth-card">
                <div class="auth-card-heading">
                    <span class="auth-mark" aria-hidden="true">R</span>
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
                    <div class="demo-credentials-heading"><span aria-hidden="true">i</span><strong>Akun demo Donatur</strong></div>
                    <div><span>Email</span><code>donatur@resfund.test</code></div>
                    <div><span>Kata sandi</span><code>ResFund123!</code></div>
                    <button type="button" class="text-button" data-fill-demo>Isi akun demo otomatis</button>
                </div>
                <p class="auth-footnote">Autentikasi V1.2.1 merupakan simulasi berbasis sesi. Belum digunakan untuk akun operasional atau transaksi sungguhan.</p>
            </div>
        </div>
    </div>
</section>
@endsection
