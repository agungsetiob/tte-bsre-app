<?php

use App\Http\Controllers\AdminController;
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

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/pending-approval', function () {
        if (auth()->user()->is_active) {
            return redirect()->route('dashboard');
        }
        return Inertia::render('Auth/PendingApproval');
    })->name('approval.notice');
});

// Halaman UI Verifikasi
Route::get('/v/{code}', [PublicVerificationController::class, 'showVerifyPage'])->name('verify.document');

// Endpoint API (Dipanggil dari onMounted Vue untuk loading indikator)
Route::post('/v/{code}/check', [PublicVerificationController::class, 'checkQrAsync'])->name('verify.check_async');

// Download File
Route::get('/v/{code}/download', [PublicVerificationController::class, 'downloadDocument'])->name('verify.download');

Route::get('/verify-document', [PublicVerificationController::class, 'index'])->name('verification.public');
Route::post('/verify-document', [PublicVerificationController::class, 'check'])->name('verification.check');

Route::middleware(['auth', 'verified', 'active'])->group(function () {
    // Manajemen Dokumen
    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::get('/documents/requests', [DocumentController::class, 'incomingRequests'])->name('documents.requests');
    Route::get('/api/users/search', [DocumentController::class, 'searchUsers'])->name('api.users.search');
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    Route::get('/documents/{document}/verify-api', [DocumentController::class, 'verify'])->name('documents.verifyApi');
    Route::get('/documents/{document}', [DocumentController::class, 'show'])->name('documents.show');
    Route::post('/documents/{document}/reject-sign', [DocumentController::class, 'rejectSign'])->name('documents.rejectSign');
    Route::post('/documents/{document}/revise', [DocumentController::class, 'revise'])->name('documents.revise');
    Route::delete('/documents/{document}/signers/{signer}', [DocumentController::class, 'cancelRequest'])->name('documents.cancelRequest');

    // Proses request TTE
    Route::post('/documents/{document}/request-sign', [DocumentController::class, 'requestSign'])->name('documents.requestSign');
    Route::post('/documents/{document}/reject-sign', [DocumentController::class, 'rejectSign'])->name('documents.rejectSign');

    // Proses TTE
    Route::get('/documents/{document}/sign', [TteController::class, 'showSignPage'])->name('documents.showSign');
    Route::post('/documents/{document}/sign', [TteController::class, 'sign'])->name('documents.sign');
    Route::post('/documents/batch-sign', [TteController::class, 'batchSign'])->name('documents.batch_sign');

    // Histori TTE
    Route::get('/histories', [TteHistoryController::class, 'index'])->name('histories.index');
    Route::delete('/histories/{history}', [TteHistoryController::class, 'destroy'])->name('histories.destroy');
});

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/admin/users', [AdminController::class, 'index'])->name('admin.users.index');
    Route::patch('/admin/users/{user}/toggle', [AdminController::class, 'toggleActive'])->name('admin.users.toggle');
    Route::post('/users/{user}/check-bsre', [AdminController::class, 'checkBsreStatus'])->name('users.check_bsre');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
