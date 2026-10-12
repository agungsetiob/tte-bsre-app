<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\BsreService; // Tambahkan ini

class AdminController extends Controller
{
    public function index()
    {
        // Mengambil semua user, diurutkan dari yang terbaru mendaftar
        $users = User::orderBy('created_at', 'desc')->get();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users
        ]);
    }

    public function toggleActive(User $user)
    {
        // Mencegah admin menonaktifkan akunnya sendiri
        if ($user->id === auth()->id()) {
            return back()->withErrors(['message' => 'Anda tidak dapat menonaktifkan akun Anda sendiri.']);
        }

        $user->update([
            'is_active' => !$user->is_active
        ]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        
        return back()->with('success', "Akun {$user->name} berhasil {$status}.");
    }

    // FUNGSI BARU UNTUK CEK STATUS BSRE
    public function checkBsreStatus(User $user, BsreService $bsreService)
    {
        if (empty($user->nik)) {
            return response()->json(['status' => 'NOT_REGISTERED']);
        }

        try {
            $response = $bsreService->checkUserStatus($user->nik);
            
            // Pastikan $response tidak null dan request berhasil (HTTP 200)
            if ($response && $response->successful()) {
                return response()->json(['status' => $response->json('status') ?? 'UNKNOWN']);
            }

            return response()->json(['status' => 'ERROR_KONEKSI'], 500);
            
        } catch (\Exception $e) {
            // Tangkap error jika server BSrE down atau timeout
            return response()->json(['status' => 'ERROR_KONEKSI'], 500);
        }
    }
}