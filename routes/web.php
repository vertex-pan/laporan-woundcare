<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WoundReportController;
use App\Http\Controllers\AuthController;

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login/phone', [AuthController::class, 'loginByPhone'])->name('login.phone');
Route::post('/login/magic-link/send', [AuthController::class, 'sendMagicLink'])->name('login.magic-link.send');
Route::get('/login/verify', [AuthController::class, 'verifyMagicLink'])->name('login.verify');
Route::post('/login/bypass', [AuthController::class, 'loginBypass'])->name('login.bypass');
Route::post('/login/emergency-pin', [AuthController::class, 'loginWithPin'])->name('login.emergency-pin');
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// First-time Google link profile setup
Route::get('/login/setup-profile', [AuthController::class, 'showSetupProfile'])->name('login.setup-profile');
Route::post('/login/setup-profile', [AuthController::class, 'saveSetupProfile'])->name('login.setup-profile.save');

// Protected Routes
Route::middleware(['operator.auth'])->group(function () {
    Route::get('/', [WoundReportController::class, 'index'])->name('dashboard');
    Route::get('/wound-reports/export', [WoundReportController::class, 'export'])->name('wound-reports.export');
    Route::post('/wound-reports', [WoundReportController::class, 'store'])->name('wound-reports.store');
    Route::delete('/wound-reports/{id}', [WoundReportController::class, 'destroy'])->name('wound-reports.destroy');
    
    // Setting route
    Route::post('/settings/whatsapp', [WoundReportController::class, 'updateMyWhatsapp'])->name('settings.whatsapp.update');
    
    // Coordinator approval routes
    Route::post('/wound-reports/{id}/approve', [WoundReportController::class, 'approve'])->name('wound-reports.approve');
    Route::post('/wound-reports/{id}/reject', [WoundReportController::class, 'reject'])->name('wound-reports.reject');
    Route::post('/operators/{id}/reset-email', [WoundReportController::class, 'resetEmail'])->name('operators.reset-email');
    Route::put('/operators/{id}', [WoundReportController::class, 'updateOperator'])->name('operators.update');
    Route::delete('/operators/{id}', [WoundReportController::class, 'destroyOperator'])->name('operators.destroy');
    Route::post('/operators/{id}/generate-pin', [WoundReportController::class, 'generateEmergencyPin'])->name('operators.generate-pin');
    Route::post('/operators/{id}/generate-late-pin', [WoundReportController::class, 'generateLatePin'])->name('operators.generate-late-pin');
});
