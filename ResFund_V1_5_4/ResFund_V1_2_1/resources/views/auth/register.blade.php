@extends('layouts.app')
@section('title', 'Buat Akun ResFund')
@section('content')
<section class="auth-section auth-section-register">
    <div class="container auth-container">
        <div class="auth-aside">
            <span class="eyebrow eyebrow-light">Bergabung dengan ResFund</span>
            <h1>Satu akun untuk berkontribusi pada perkembangan riset.</h1>
            <p>Pilih jenis akun sesuai peran Anda. Sistem akan menampilkan proses yang relevan dengan kebutuhan setiap pengguna.</p>
            <div class="auth-aside-points">
                <div><span aria-hidden="true">✓</span><p>Donatur dapat menjelajahi dan mendukung proyek riset.</p></div>
                <div><span aria-hidden="true">✓</span><p>Peneliti mengajukan identitas akademik untuk diverifikasi.</p></div>
            </div>
            <div class="auth-decoration auth-decoration-one"></div><div class="auth-decoration auth-decoration-two"></div>
        </div>

        <div class="auth-card-wrap">
            <div class="auth-card auth-card-register">
                <div class="auth-card-heading">
                    <span class="auth-mark" aria-hidden="true">R</span>
                    <span class="eyebrow">Pendaftaran akun</span>
                    <h2>Buat akun ResFund</h2>
                    <p>Jenis akun dipilih saat pendaftaran dan menentukan akses di dalam sistem.</p>
                </div>

                @if($errors->any())
                    <div class="form-alert form-alert-error" role="alert" aria-live="assertive">
                        <strong>Periksa kembali data yang dimasukkan:</strong>
                        <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                @if($intent === 'donasi' && $projectSlug)
                    <div class="form-alert form-alert-info" role="status">
                        Anda melanjutkan dari halaman proyek. Pilih <strong>Donatur</strong> untuk meneruskan alur donasi setelah masuk.
                    </div>
                @endif

                <form method="POST" action="{{ route('register.submit') }}" class="auth-form">
                    @csrf
                    <input type="hidden" name="intent" value="{{ $intent }}">
                    <input type="hidden" name="project_slug" value="{{ $projectSlug }}">

                    <fieldset class="role-fieldset" aria-describedby="role-select-hint role-access-hint">
                        <legend>Daftar sebagai <span aria-hidden="true">*</span></legend>
                        <p class="role-select-hint" id="role-select-hint">Pilih satu jenis akun. Pilihan ini menentukan hak akses dan informasi tambahan yang diperlukan.</p>
                        <div class="role-choice-grid">
                            <label class="role-choice {{ old('role') === 'donatur' ? 'is-selected' : '' }}">
                                <input type="radio" name="role" value="donatur" @checked(old('role') === 'donatur') required>
                                <span class="role-choice-content">
                                    <span class="role-choice-title">Donatur</span>
                                    <span class="role-choice-description">Menjelajahi proyek, berdonasi, dan memantau riset.</span>
                                </span>
                            </label>
                            <label class="role-choice {{ old('role') === 'peneliti' ? 'is-selected' : '' }}">
                                <input type="radio" name="role" value="peneliti" @checked(old('role') === 'peneliti') required>
                                <span class="role-choice-content">
                                    <span class="role-choice-title">Peneliti</span>
                                    <span class="role-choice-description">Mengajukan proposal dengan identitas akademik.</span>
                                </span>
                            </label>
                        </div>
                        @error('role')<small class="field-error">{{ $message }}</small>@enderror
                        <p class="form-hint" id="role-access-hint">Reviewer dan Administrator tidak membuka pendaftaran mandiri. Akun keduanya diterbitkan atau ditugaskan oleh Administrator.</p>
                    </fieldset>

                    <div class="form-group">
                        <label for="name">Nama lengkap <span aria-hidden="true">*</span></label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" placeholder="Nama sesuai identitas" autocomplete="name" required @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
                        @error('name')<small class="field-error" id="name-error">{{ $message }}</small>@enderror
                    </div>

                    <div class="form-group">
                        <label for="email">Alamat email <span aria-hidden="true">*</span></label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="nama@email.com" autocomplete="email" required @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                        @error('email')<small class="field-error" id="email-error">{{ $message }}</small>@enderror
                    </div>

                    <div class="form-group">
                        <label for="phone">Nomor telepon <span aria-hidden="true">*</span></label>
                        <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx" autocomplete="tel" inputmode="tel" required @error('phone') aria-invalid="true" aria-describedby="phone-error" @enderror>
                        @error('phone')<small class="field-error" id="phone-error">{{ $message }}</small>@enderror
                    </div>

                    <div class="researcher-fields" data-researcher-fields aria-live="polite" @if(old('role') !== 'peneliti') hidden @endif>
                        <div class="researcher-fields-heading">
                            <strong>Informasi identitas akademik</strong>
                            <span>Wajib untuk akun Peneliti</span>
                        </div>
                        <div class="form-group">
                            <label for="researcher_type">Status peneliti <span aria-hidden="true">*</span></label>
                            <select id="researcher_type" name="researcher_type" data-researcher-required @if(old('role') === 'peneliti') required @endif>
                                <option value="">Pilih status</option>
                                <option value="dosen" @selected(old('researcher_type') === 'dosen')>Dosen</option>
                                <option value="mahasiswa" @selected(old('researcher_type') === 'mahasiswa')>Mahasiswa</option>
                            </select>
                            @error('researcher_type')<small class="field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="form-group">
                            <label for="academic_id">NIDN / NIM <span aria-hidden="true">*</span></label>
                            <input id="academic_id" name="academic_id" type="text" value="{{ old('academic_id') }}" placeholder="Masukkan NIDN atau NIM" autocomplete="off" data-researcher-required @if(old('role') === 'peneliti') required @endif>
                            @error('academic_id')<small class="field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="form-group">
                            <label for="institution">Perguruan tinggi <span aria-hidden="true">*</span></label>
                            <input id="institution" name="institution" type="text" value="{{ old('institution') }}" placeholder="Nama perguruan tinggi" autocomplete="organization" data-researcher-required @if(old('role') === 'peneliti') required @endif>
                            @error('institution')<small class="field-error">{{ $message }}</small>@enderror
                        </div>
                        <p class="form-hint form-hint-warning">Status akun Peneliti akan menunggu verifikasi identitas melalui layanan akademik. Pendaftaran V1.2.1 hanya simulasi dan belum terhubung ke SSO/PDDikti.</p>
                    </div>

                    <div class="form-row-two">
                        <div class="form-group">
                            <label for="password">Kata sandi <span aria-hidden="true">*</span></label>
                            <div class="password-field">
                                <input id="password" name="password" type="password" placeholder="Minimal 8 karakter" autocomplete="new-password" required @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
                                <button type="button" class="password-toggle" data-password-toggle="password" aria-label="Tampilkan kata sandi">Lihat</button>
                            </div>
                            @error('password')<small class="field-error" id="password-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="form-group">
                            <label for="password_confirmation">Konfirmasi kata sandi <span aria-hidden="true">*</span></label>
                            <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Ulangi kata sandi" autocomplete="new-password" required>
                        </div>
                    </div>

                    <div class="form-group terms-group">
                        <label class="checkbox-label" for="terms">
                            <input id="terms" type="checkbox" name="terms" value="1" @checked(old('terms')) required>
                            <span>Saya menyetujui syarat dan ketentuan serta memahami bahwa ini adalah pendaftaran simulasi untuk prototype.</span>
                        </label>
                        @error('terms')<small class="field-error">{{ $message }}</small>@enderror
                    </div>

                    <button type="submit" class="button button-primary button-full button-large">Buat akun</button>
                </form>

                <p class="auth-switch auth-switch-register">Sudah memiliki akun? <a href="{{ route('login', array_filter(['intent' => $intent, 'project' => $projectSlug])) }}">Masuk</a></p>
                <p class="auth-footnote" id="terms-note">Data akun V1.2.1 disimpan sementara pada sesi browser/server, bukan basis data permanen. Verifikasi email dan identitas akademik belum terhubung.</p>
            </div>
        </div>
    </div>
</section>
@endsection
