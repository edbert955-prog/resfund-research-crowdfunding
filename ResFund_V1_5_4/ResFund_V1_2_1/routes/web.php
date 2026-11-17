<?php

use App\Http\Controllers\AuthPrototypeController;
use App\Http\Controllers\DonorDashboardController;
use App\Http\Controllers\DonationPrototypeController;
use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

// Area publik.
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/proyek', [PublicController::class, 'projects'])->name('projects.index');
Route::get('/proyek/{slug}', [PublicController::class, 'project'])->name('projects.show');
Route::get('/proyek/{slug}/donasi', [PublicController::class, 'donationEntry'])->name('donation.entry');
Route::get('/laporan-keuangan/{slug?}', [PublicController::class, 'finance'])->name('finance.public');
Route::get('/cara-kerja', [PublicController::class, 'howItWorks'])->name('how-it-works');

// Autentikasi prototype.
Route::get('/login', [AuthPrototypeController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthPrototypeController::class, 'login'])->name('login.submit');
Route::get('/register', [AuthPrototypeController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthPrototypeController::class, 'register'])->name('register.submit');
Route::get('/lupa-password', [AuthPrototypeController::class, 'showForgotPassword'])->name('password.request');
Route::post('/logout', [AuthPrototypeController::class, 'logout'])->name('logout');


// Checkout dan Payment Gateway simulasi untuk alur bisnis donatur.
Route::middleware('donatur')->prefix('donasi')->name('donation.')->group(function (): void {
    Route::get('/{slug}/checkout', [DonationPrototypeController::class, 'checkout'])->name('checkout');
    Route::post('/{slug}/checkout', [DonationPrototypeController::class, 'createPayment'])->name('create-payment');
    Route::get('/pembayaran/{reference}', [DonationPrototypeController::class, 'payment'])->name('payment');
    Route::post('/pembayaran/{reference}/simulasi-berhasil', [DonationPrototypeController::class, 'simulatePaid'])->name('simulate-paid');
    Route::get('/konfirmasi/{reference}', [DonationPrototypeController::class, 'confirmation'])->name('confirmation');
});

// Area privat khusus aktor Donatur sesuai Information Architecture BAB V.
Route::middleware('donatur')->prefix('donatur')->name('donor.')->group(function (): void {
    Route::get('/dashboard', [DonorDashboardController::class, 'dashboard'])->name('dashboard');

    // S08 - Donasi Saya / Riwayat dan detail donasi.
    Route::get('/donasi', [DonorDashboardController::class, 'donations'])->name('donations.index');
    Route::get('/donasi/{donationId}', [DonorDashboardController::class, 'donationDetail'])->name('donations.show');

    // S09 - Monitoring proyek dan milestone.
    Route::get('/monitoring', [DonorDashboardController::class, 'monitoring'])->name('monitoring.index');
    Route::get('/monitoring/{slug}', [DonorDashboardController::class, 'monitoringDetail'])->name('monitoring.show');

    // S10 - Ringkasan keuangan publik, ditampilkan dalam shell dashboard bila dibuka Donatur.
    Route::get('/laporan-keuangan/{slug?}', [DonorDashboardController::class, 'financeSummary'])->name('finance');

    // S11 - Ledger privat dan preview bukti transaksi.
    Route::get('/ledger', [DonorDashboardController::class, 'ledgerIndex'])->name('ledger.index');
    Route::get('/proyek/{slug}/ledger', [DonorDashboardController::class, 'ledger'])->name('ledger.show');

    // S12 - Klarifikasi audit.
    Route::get('/klarifikasi-audit', [DonorDashboardController::class, 'auditIndex'])->name('audit.index');
    Route::get('/proyek/{slug}/klarifikasi-audit', [DonorDashboardController::class, 'auditCreate'])->name('audit.create');
    Route::post('/proyek/{slug}/klarifikasi-audit', [DonorDashboardController::class, 'auditStore'])->name('audit.store');

    // Profil dan notifikasi.
    Route::get('/profil-notifikasi', [DonorDashboardController::class, 'profile'])->name('profile');
    Route::post('/profil', [DonorDashboardController::class, 'updateProfile'])->name('profile.update');
    Route::post('/notifikasi/{id}/baca', [DonorDashboardController::class, 'markNotificationRead'])->name('notifications.read');
    Route::post('/notifikasi/baca-semua', [DonorDashboardController::class, 'markAllNotificationsRead'])->name('notifications.read-all');
});
