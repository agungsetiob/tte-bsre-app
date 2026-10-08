<?php

use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicVerificationController;
use App\Http\Controllers\TteController;
use App\Http\Controllers\TteHistoryController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('welcome');

Route::get('/verify-document', [PublicVerificationController::class, 'index'])->name('verification.public');
Route::post('/verify-document', [PublicVerificationController::class, 'check'])->name('verification.check');

Route::middleware(['auth', 'verified'])->group(function () {
    // Manajemen Dokumen
    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    Route::get('/documents/{document}/verify-api', [DocumentController::class, 'verify'])->name('documents.verifyApi');

    // Proses TTE
    Route::get('/documents/{document}/sign', [TteController::class, 'showSignPage'])->name('documents.showSign');
    Route::post('/documents/{document}/sign', [TteController::class, 'sign'])->name('documents.sign');

    // Histori TTE
    Route::get('/histories', [TteHistoryController::class, 'index'])->name('histories.index');
    Route::delete('/histories/{history}', [TteHistoryController::class, 'destroy'])->name('histories.destroy');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
