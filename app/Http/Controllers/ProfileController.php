<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Department;
use App\Services\BsreService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request, BsreService $bsreService)
    {
        $user = $request->user();
        
        $bsreStatus = 'TIDAK_DICEK';
        
        if ($user->nik) {
            try {
                $response = $bsreService->checkUserStatus($user->nik);
                
                // 1. Jika Berhasil
                if ($response && $response->successful()) {
                    $bsreStatus = $response->json('status') ?? 'UNKNOWN';
                } 
                // 2. Jika Gagal dari API BSrE (Tangkap pesan aslinya)
                elseif ($response && $response->failed()) {
                    if (is_array($response->json())) {
                        $jsonError = $response->json();
                        $bsreStatus = $jsonError['error'] ?? $jsonError['message'] ?? 'Kesalahan server BSrE';
                    } else {
                        $bsreStatus = 'HTTP ' . $response->status() . ' Error';
                    }
                } 
                // 3. Fallback lainnya
                else {
                    $bsreStatus = 'ERROR_KONEKSI';
                }
            } catch (\Exception $e) {
                // Server BSrE down / Timeout / IP Diblokir
                $bsreStatus = 'TIDAK_DAPAT_TERHUBUNG';
            }
        }

        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => session('status'),
            'bsreStatus' => $bsreStatus,
            'departments' => Department::where('is_active', true)->orderBy('name', 'asc')->get(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        if ($request->hasFile('specimen')) {
            if ($user->specimen_path) {
                Storage::disk('public')->delete($user->specimen_path);
            }

            $path = $request->file('specimen')->store('specimens', 'public');
            $user->specimen_path = $path;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('success', 'profile updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
