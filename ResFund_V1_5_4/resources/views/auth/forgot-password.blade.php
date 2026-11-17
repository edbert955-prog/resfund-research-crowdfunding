@extends('layouts.app')
@section('title', 'Lupa Kata Sandi')
@section('content')
<section class="auth-section auth-section-simple"><div class="auth-card auth-card-narrow"><div class="auth-card-heading"><span class="auth-mark"><img src="{{ asset('assets/images/resfund-mark.png') }}" alt="" width="48" height="48"></span><span class="eyebrow">Bantuan akun</span><h1>Lupa kata sandi?</h1><p>Fitur pengiriman email pemulihan belum diaktifkan pada prototype ResFund</p></div><div class="form-alert form-alert-info" role="status"><strong>Akun demo</strong><p>Untuk mencoba autentikasi, gunakan akun demo yang tersedia pada halaman Masuk.</p><code>donatur@resfund.test · ResFund123!</code></div><a href="{{ route('login') }}" class="button button-primary button-full">Kembali ke halaman masuk</a></div></section>
@endsection
