<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\WoundReportController;

Route::get('/', [WoundReportController::class, 'index'])->name('dashboard');
Route::post('/wound-reports', [WoundReportController::class, 'store'])->name('wound-reports.store');
Route::delete('/wound-reports/{id}', [WoundReportController::class, 'destroy'])->name('wound-reports.destroy');
