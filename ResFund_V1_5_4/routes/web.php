<?php

use App\Http\Controllers\AuthPrototypeController;
use App\Http\Controllers\DonorDashboardController;
use App\Http\Controllers\DonationPrototypeController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\ResearcherDashboardController;
use App\Http\Controllers\ReviewerDashboardController;
use App\Http\Controllers\AdministratorController;
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


// Area privat Peneliti sesuai IA: S13 Dashboard & Validasi Profil,
// S14 Proposal/Kampanye, S15 Milestone/Pencairan, S16 Pengeluaran/Kuitansi.
Route::middleware('peneliti')->prefix('peneliti')->name('researcher.')->group(function (): void {
    Route::get('/dashboard', [ResearcherDashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/profil-validasi', [ResearcherDashboardController::class, 'profile'])->name('profile');

    Route::get('/proposal', [ResearcherDashboardController::class, 'proposals'])->name('proposals.index');
    Route::get('/proposal/buat', [ResearcherDashboardController::class, 'proposalCreate'])->name('proposals.create');
    Route::post('/proposal', [ResearcherDashboardController::class, 'proposalStore'])->name('proposals.store');
    Route::get('/proposal/{slug}', [ResearcherDashboardController::class, 'proposalShow'])->name('proposals.show');
    Route::post('/proposal/{slug}/ajukan', [ResearcherDashboardController::class, 'proposalSubmit'])->name('proposals.submit');

    Route::get('/milestone', [ResearcherDashboardController::class, 'milestones'])->name('milestones');
    Route::post('/proyek/{slug}/milestone/{number}/ajukan', [ResearcherDashboardController::class, 'milestoneSubmit'])->name('milestones.submit');

    Route::get('/pengeluaran', [ResearcherDashboardController::class, 'expenses'])->name('expenses');
    Route::post('/pengeluaran', [ResearcherDashboardController::class, 'expenseStore'])->name('expenses.store');
    Route::post('/pengeluaran/{id}/publikasikan', [ResearcherDashboardController::class, 'expensePublish'])->name('expenses.publish');
    Route::get('/pengeluaran/{id}/kuitansi', [ResearcherDashboardController::class, 'expenseReceipt'])->name('expenses.receipt');
});


// Area privat Reviewer sesuai Information Architecture: Dashboard/Daftar Penugasan
// serta Detail Bukti dan Form Evaluasi. Penugasan hanya dapat dibuka oleh reviewer terkait.
Route::middleware('reviewer')->prefix('reviewer')->name('reviewer.')->group(function (): void {
    Route::get('/dashboard', [ReviewerDashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/penugasan', [ReviewerDashboardController::class, 'assignments'])->name('assignments.index');
    Route::get('/riwayat-keputusan', [ReviewerDashboardController::class, 'decisionHistory'])->name('decisions.index');
    Route::get('/profil-notifikasi', [ReviewerDashboardController::class, 'profile'])->name('profile');
    Route::post('/profil-notifikasi', [ReviewerDashboardController::class, 'updateProfile'])->name('profile.update');
    Route::post('/notifikasi/{notificationId}/baca', [ReviewerDashboardController::class, 'markNotificationRead'])->name('notifications.read');
    Route::post('/notifikasi/baca-semua', [ReviewerDashboardController::class, 'markAllNotificationsRead'])->name('notifications.read-all');
    Route::get('/penugasan/{assignmentId}', [ReviewerDashboardController::class, 'showAssignment'])->name('assignments.show');
    Route::post('/penugasan/{assignmentId}/penilaian', [ReviewerDashboardController::class, 'submitEvaluation'])->name('assignments.evaluate');
    Route::post('/penugasan/{assignmentId}/konflik', [ReviewerDashboardController::class, 'reportConflict'])->name('assignments.conflict');
});


// Area privat Administrator sesuai sitemap: dashboard, manajemen pengguna,
// validasi peneliti, manajemen/penugasan reviewer, serta monitoring & audit log.
Route::middleware('administrator')->prefix('administrator')->name('administrator.')->group(function (): void {
    Route::get('/dashboard', [AdministratorController::class, 'dashboard'])->name('dashboard');

    Route::get('/pengguna', [AdministratorController::class, 'users'])->name('users.index');
    Route::get('/pengguna/{userId}', [AdministratorController::class, 'userDetail'])->name('users.show');
    Route::post('/pengguna/{userId}/status', [AdministratorController::class, 'updateUserStatus'])->name('users.status');

    Route::get('/validasi-peneliti', [AdministratorController::class, 'researcherValidation'])->name('researchers.index');
    Route::get('/validasi-peneliti/{userId}', [AdministratorController::class, 'researcherDetail'])->name('researchers.show');
    Route::post('/validasi-peneliti/{userId}/keputusan', [AdministratorController::class, 'researcherDecision'])->name('researchers.decide');

    Route::get('/reviewer', [AdministratorController::class, 'reviewers'])->name('reviewers.index');
    Route::post('/reviewer', [AdministratorController::class, 'createReviewer'])->name('reviewers.store');
    Route::post('/reviewer/{reviewerId}/status', [AdministratorController::class, 'updateReviewerStatus'])->name('reviewers.status');

    Route::get('/penugasan-reviewer', [AdministratorController::class, 'assignments'])->name('assignments.index');
    Route::get('/penugasan-reviewer/{assignmentId}', [AdministratorController::class, 'assignmentDetail'])->name('assignments.show');
    Route::post('/penugasan-reviewer/{assignmentId}', [AdministratorController::class, 'assignReviewers'])->name('assignments.assign');

    Route::get('/monitoring-audit-log', [AdministratorController::class, 'auditLogs'])->name('monitoring.index');
});
