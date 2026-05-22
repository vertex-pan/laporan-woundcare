<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WoundReportController;
use App\Http\Controllers\AuthController;

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login/bypass', [AuthController::class, 'loginBypass'])->name('login.bypass');
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// First-time Google link profile setup
Route::get('/login/setup-profile', [AuthController::class, 'showSetupProfile'])->name('login.setup-profile');
Route::post('/login/setup-profile', [AuthController::class, 'saveSetupProfile'])->name('login.setup-profile.save');

// Protected Routes
Route::middleware(['operator.auth'])->group(function () {
    Route::get('/', [WoundReportController::class, 'index'])->name('dashboard');
    Route::post('/wound-reports', [WoundReportController::class, 'store'])->name('wound-reports.store');
    Route::delete('/wound-reports/{id}', [WoundReportController::class, 'destroy'])->name('wound-reports.destroy');
    
    // Coordinator approval routes
    Route::post('/wound-reports/{id}/approve', [WoundReportController::class, 'approve'])->name('wound-reports.approve');
    Route::post('/wound-reports/{id}/reject', [WoundReportController::class, 'reject'])->name('wound-reports.reject');
});
