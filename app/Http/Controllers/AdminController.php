<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\BsreService; // Tambahkan ini

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // 1. Filter Pencarian (Nama, Email, NIK)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        // 2. Filter Status Akun
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === '1');
        }

        // 3. Filter Status TTE BSrE
        if ($request->filled('bsre_status')) {
            $query->where('bsre_status', $request->bsre_status);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'is_active', 'bsre_status'])
        ]);
    }

    public function checkBsreStatus(User $user, BsreService $bsreService)
    {
        if (empty($user->nik)) {
            $user->update(['bsre_status' => 'NOT_REGISTERED']);
            return response()->json(['status' => 'NOT_REGISTERED']);
        }

        try {
            $response = $bsreService->checkUserStatus($user->nik);
            
            // 1. Tangani Respon Berhasil (HTTP 200)
            if ($response && $response->successful()) {
                $status = $response->json('status') ?? 'UNKNOWN';
                
                $user->update(['bsre_status' => $status]);
                
                return response()->json(['status' => $status]);
            }

            // 2. Tangani Respon Gagal dari API BSrE (HTTP 4xx / 5xx)
            if ($response && $response->failed()) {
                if (is_array($response->json())) {
                    $jsonError = $response->json();
                    $msg = $jsonError['error'] ?? $jsonError['message'] ?? 'Kesalahan server BSrE';
                } else {
                    $msg = 'HTTP ' . $response->status() . ' Error';
                }

                // Simpan pesan error asli ke database sebagai status terakhir
                $user->update(['bsre_status' => $msg]);

                return response()->json(['status' => $msg], $response->status());
            }

            return response()->json(['status' => 'ERROR_KONEKSI'], 500);
            
        } catch (\Exception $e) {
            // Tangani jika server down / timeout
            return response()->json(['status' => 'TIDAK_DAPAT_TERHUBUNG'], 500);
        }
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
}