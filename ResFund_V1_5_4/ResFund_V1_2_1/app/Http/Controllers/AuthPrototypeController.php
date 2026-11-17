<?php

namespace App\Http\Controllers;

use App\Support\PrototypeCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthPrototypeController extends Controller
{
    public function showLogin(Request $request)
    {
        $intent = $request->query('intent') === 'donasi' ? 'donasi' : null;
        $projectSlug = $this->resolveProjectSlug($request->query('project'));

        if ($request->session()->has('prototype_user')) {
            $user = $request->session()->get('prototype_user');

            if ($intent === 'donasi' && $projectSlug !== null) {
                if (($user['role'] ?? 'donatur') === 'donatur') {
                    return redirect()->route('donation.entry', ['slug' => $projectSlug]);
                }

                return redirect()->route('projects.show', ['slug' => $projectSlug])
                    ->with('info', 'Akun yang sedang digunakan adalah akun peneliti. Untuk berdonasi, silakan keluar dan masuk menggunakan akun Donatur.');
            }

            return ($user['role'] ?? null) === 'donatur'
                ? redirect()->route('donor.dashboard')
                : redirect()->route('home');
        }

        return view('auth.login', compact('intent', 'projectSlug'));
    }

    public function login(Request $request)
    {
        if ($request->session()->has('prototype_user')) {
            return redirect()->route('home')->with('info', 'Anda sudah masuk. Keluar terlebih dahulu jika ingin menggunakan akun lain.');
        }

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:190'],
            'password' => ['required', 'string', 'min:8'],
            'intent' => ['nullable', 'in:donasi'],
            'project_slug' => ['nullable', 'string', 'max:120'],
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Masukkan alamat email dengan format yang benar.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
        ]);

        $email = mb_strtolower(trim($validated['email']));
        $password = $validated['password'];
        $isDemoAccount = $email === 'donatur@resfund.test' && hash_equals('ResFund123!', $password);
        $registered = $request->session()->get('prototype_registered_account');
        $isRegisteredAccount = is_array($registered)
            && ($registered['email'] ?? null) === $email
            && Hash::check($password, $registered['password_hash'] ?? '');

        if (! $isDemoAccount && ! $isRegisteredAccount) {
            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi tidak cocok. Gunakan akun demo atau akun yang didaftarkan pada sesi browser ini.',
            ]);
        }

        $role = $isDemoAccount ? 'donatur' : ($registered['role'] ?? 'donatur');
        $name = $isDemoAccount ? 'Nadia Putri' : ($registered['name'] ?? 'Pengguna ResFund');
        $phone = $isDemoAccount ? '0812-0000-1234' : ($registered['phone'] ?? '');
        $verificationStatus = $isDemoAccount ? 'terverifikasi' : ($registered['verification_status'] ?? 'aktif');

        if (($validated['intent'] ?? null) === 'donasi' && $role !== 'donatur') {
            $projectSlug = $this->resolveProjectSlug($validated['project_slug'] ?? null);
            if ($projectSlug !== null) {
                return redirect()->route('projects.show', ['slug' => $projectSlug])
                    ->with('info', 'Akun ini terdaftar sebagai Peneliti. Gunakan akun Donatur untuk melanjutkan alur donasi.');
            }
        }

        $request->session()->regenerate();
        $request->session()->put('prototype_user', [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'role' => $role,
            'role_label' => $role === 'peneliti' ? 'Peneliti' : 'Donatur',
            'verification_status' => $verificationStatus,
            'institution' => $isDemoAccount ? null : ($registered['institution'] ?? null),
            'academic_id' => $isDemoAccount ? null : ($registered['academic_id'] ?? null),
        ]);

        if (($validated['intent'] ?? null) === 'donasi') {
            $projectSlug = $this->resolveProjectSlug($validated['project_slug'] ?? null);
            if ($projectSlug !== null) {
                return redirect()->route('donation.entry', ['slug' => $projectSlug]);
            }
        }

        if ($role === 'peneliti') {
            return redirect()->route('home')->with('info', 'Login simulasi berhasil. Akun Peneliti masih menunggu verifikasi identitas akademik. Dashboard Peneliti belum termasuk V1.2.1.');
        }

        return redirect()->route('donor.dashboard')->with('success', 'Login berhasil dalam simulasi. Dashboard Donatur V1.2.1 menggunakan data demonstrasi; transaksi dan data akun belum tersimpan pada basis data operasional.');
    }

    public function showRegister(Request $request)
    {
        if ($request->session()->has('prototype_user')) {
            $current = $request->session()->get('prototype_user');
            return ($current['role'] ?? null) === 'donatur' ? redirect()->route('donor.dashboard') : redirect()->route('home');
        }

        $intent = $request->query('intent') === 'donasi' ? 'donasi' : null;
        $projectSlug = $this->resolveProjectSlug($request->query('project'));

        return view('auth.register', compact('intent', 'projectSlug'));
    }

    public function register(Request $request)
    {
        if ($request->session()->has('prototype_user')) {
            return redirect()->route('home')->with('info', 'Anda sudah masuk. Keluar terlebih dahulu sebelum membuat akun lain.');
        }

        $validated = $request->validate([
            'role' => ['required', 'in:donatur,peneliti'],
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['required', 'string', 'min:9', 'max:20', 'regex:/^[0-9+().\-\s]+$/'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'researcher_type' => ['exclude_unless:role,peneliti', 'required', 'in:dosen,mahasiswa'],
            'academic_id' => ['exclude_unless:role,peneliti', 'required', 'string', 'min:5', 'max:30', 'regex:/^[A-Za-z0-9\/._-]+$/'],
            'institution' => ['exclude_unless:role,peneliti', 'required', 'string', 'min:3', 'max:150'],
            'terms' => ['accepted'],
            'intent' => ['nullable', 'in:donasi'],
            'project_slug' => ['nullable', 'string', 'max:120'],
        ], [
            'role.required' => 'Pilih jenis akun yang ingin dibuat.',
            'role.in' => 'Pendaftaran mandiri hanya tersedia untuk Donatur dan Peneliti.',
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.min' => 'Nama minimal terdiri dari 3 karakter.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Masukkan alamat email dengan format yang benar.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.min' => 'Nomor telepon terlalu pendek.',
            'phone.regex' => 'Nomor telepon hanya boleh berisi angka, spasi, dan tanda + ( ) atau -.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi belum sama.',
            'researcher_type.required' => 'Pilih status Peneliti sebagai dosen atau mahasiswa.',
            'academic_id.required' => 'NIDN/NIM wajib diisi untuk akun Peneliti.',
            'academic_id.regex' => 'NIDN/NIM hanya boleh berisi huruf, angka, titik, garis miring, garis bawah, atau tanda hubung.',
            'institution.required' => 'Nama perguruan tinggi wajib diisi untuk akun Peneliti.',
            'terms.accepted' => 'Persetujuan syarat dan ketentuan harus dicentang.',
        ]);

        // Jika pengguna datang dari tombol donasi, hanya akun Donatur yang dapat meneruskan alur.
        if (($validated['intent'] ?? null) === 'donasi' && $validated['role'] !== 'donatur') {
            throw ValidationException::withMessages([
                'role' => 'Untuk melanjutkan donasi, pilih jenis akun Donatur. Akun Peneliti digunakan untuk mengajukan dan mengelola proposal riset.',
            ]);
        }

        $email = mb_strtolower(trim($validated['email']));
        if ($email === 'donatur@resfund.test') {
            throw ValidationException::withMessages([
                'email' => 'Email tersebut digunakan oleh akun demo. Silakan gunakan email lain.',
            ]);
        }

        $existing = $request->session()->get('prototype_registered_account');
        if (is_array($existing) && ($existing['email'] ?? null) === $email) {
            throw ValidationException::withMessages([
                'email' => 'Email tersebut sudah digunakan pada sesi prototype ini. Gunakan email lain atau masuk ke akun yang sudah dibuat.',
            ]);
        }

        $role = $validated['role'];
        $verificationStatus = $role === 'peneliti' ? 'menunggu_verifikasi' : 'aktif';
        $account = [
            'name' => trim($validated['name']),
            'email' => $email,
            'phone' => trim($validated['phone']),
            'role' => $role,
            'role_label' => $role === 'peneliti' ? 'Peneliti' : 'Donatur',
            'verification_status' => $verificationStatus,
            'researcher_type' => $role === 'peneliti' ? $validated['researcher_type'] : null,
            'academic_id' => $role === 'peneliti' ? trim($validated['academic_id']) : null,
            'institution' => $role === 'peneliti' ? trim($validated['institution']) : null,
            'password_hash' => Hash::make($validated['password']),
        ];

        $request->session()->put('prototype_registered_account', $account);
        $request->session()->regenerate();
        $request->session()->put('prototype_user', [
            'name' => $account['name'],
            'email' => $account['email'],
            'phone' => $account['phone'],
            'role' => $account['role'],
            'role_label' => $account['role_label'],
            'verification_status' => $account['verification_status'],
            'institution' => $account['institution'],
            'academic_id' => $account['academic_id'],
        ]);

        if ($role === 'peneliti') {
            return redirect()->route('home')->with('info', 'Akun Peneliti berhasil dibuat dalam simulasi. Status identitas akademik: menunggu verifikasi. Integrasi SSO/PDDikti dan dashboard Peneliti belum tersedia pada V1.2.1.');
        }

        if (($validated['intent'] ?? null) === 'donasi') {
            $projectSlug = $this->resolveProjectSlug($validated['project_slug'] ?? null);
            if ($projectSlug !== null) {
                return redirect()->route('donation.entry', ['slug' => $projectSlug]);
            }
        }

        return redirect()->route('donor.dashboard')->with('success', 'Akun Donatur berhasil dibuat dalam simulasi dan Anda sudah masuk. Dashboard menampilkan data kosong sampai ada transaksi donasi terverifikasi. Data akun hanya tersimpan pada sesi prototype ini.');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('prototype_user');
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Anda telah keluar dari sesi prototype.');
    }

    private function resolveProjectSlug(mixed $slug): ?string
    {
        if (! is_string($slug) || $slug === '') {
            return null;
        }

        return collect(PrototypeCatalog::projects())->contains('slug', $slug) ? $slug : null;
    }
}
