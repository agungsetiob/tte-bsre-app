<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
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
                
                // Pastikan $response adalah objek yang valid sebelum memanggil method-nya
                if ($response && $response->successful()) {
                    // json('status') akan aman meskipun response tidak memiliki key tersebut
                    $bsreStatus = $response->json('status') ?? 'UNKNOWN';
                } else {
                    // Akan masuk ke sini jika terblokir (HTTP 403), HTTP 500, atau Bad Request
                    $bsreStatus = 'ERROR_KONEKSI';
                }
            } catch (\Exception $e) {
                // Akan masuk ke sini jika server BSrE down, timeout, atau koneksi terputus total
                $bsreStatus = 'ERROR_KONEKSI';
            }
        }

        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => session('status'),
            'bsreStatus' => $bsreStatus,
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

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
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
