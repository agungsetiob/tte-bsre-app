<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Document;
use App\Models\DocumentSigner;
use App\Models\TteHistory;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $data = [];

        // 1. DATA KHUSUS ADMIN
        if ($user->role === 'admin') {
            $data['stats'] = [
                'total_users'   => User::count(),
                'total_docs'    => Document::count(),
                'pending_tte'   => DocumentSigner::where('status', 'pending')->count(),
                'success_tte'   => DocumentSigner::where('status', 'signed')->count(),
            ];

            // Grafik 7 Hari Terakhir (Aktivitas TTE Sukses)
            $last7Days = [];
            $chartData = [];
            
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                $last7Days[] = $date->translatedFormat('d M');
                
                // Hitung dari histori TTE sukses per hari
                $count = TteHistory::where('status', 'success')
                    ->whereDate('created_at', $date)
                    ->count();
                $chartData[] = $count;
            }

            $data['chart'] = [
                'labels' => $last7Days,
                'data'   => $chartData
            ];
        } 
        // 2. DATA KHUSUS USER (PEGAWAI)
        else {
            $data['stats'] = [
                'need_my_sign' => DocumentSigner::where('user_id', $user->id)->where('status', 'pending')->count(),
                'my_docs'      => Document::where('user_id', $user->id)->count(),
                'my_signed'    => DocumentSigner::where('user_id', $user->id)->where('status', 'signed')->count(),
            ];

            // Tabel 5 Permintaan Mendesak
            $data['urgent_requests'] = DocumentSigner::with(['document.owner'])
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->latest()
                ->take(5)
                ->get();
        }

        return Inertia::render('Dashboard', [
            'dashboardData' => $data
        ]);
    }
}