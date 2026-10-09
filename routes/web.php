<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

// Slide 1: Halaman Beranda / Home
Route::get('/', [HomeController::class, 'index'])->name('home');

// Quick Quota & Ticket verification endpoints
Route::post('/api/check-quota', [HomeController::class, 'checkQuota'])->name('quota.check');
Route::post('/api/check-ticket', [HomeController::class, 'checkTicket'])->name('ticket.check');

// Slide 2: Halaman Pilih Layanan & Jadwal
Route::get('/daftar', [RegistrationController::class, 'create'])->name('registration.create');
Route::get('/api/shifts', [RegistrationController::class, 'getShifts'])->name('registration.shifts');

// Slide 3: Halaman Formulir Data Pemohon & Syarat Berkas (Checklist)
Route::get('/daftar/formulir', [RegistrationController::class, 'form'])->name('registration.form');
Route::post('/daftar', [RegistrationController::class, 'store'])->name('registration.store');

// Slide 4: e-Tiket Antrean Resmi
Route::get('/tiket/{ticketNumber}', [RegistrationController::class, 'showTicket'])->name('ticket.show');

// Slide 5: Halaman Cek Status / Cari Tiket (Pencarian NIK & WhatsApp)
Route::get('/cek-tiket', [HomeController::class, 'searchTicketPage'])->name('ticket.search');

// Rute Akses Tersembunyi Administrator
Route::get('/login-admin', [AdminAuthController::class, 'showLoginForm'])->name('login-admin');

// Panel Pengelola / Admin Antrean
Route::prefix('admin')->name('admin.')->group(function () {
    // Guest Admin Auth Routes
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    // Protected Admin Routes (Requires admin.auth middleware)
    Route::middleware('admin.auth')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('dashboard');
        Route::post('/reset', [AdminController::class, 'reset'])->name('reset');
        Route::patch('/appointments/{appointment}/status', [AdminController::class, 'updateStatus'])->name('appointments.status');
    });
});
