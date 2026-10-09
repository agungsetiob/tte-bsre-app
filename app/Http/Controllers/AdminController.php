<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

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
}