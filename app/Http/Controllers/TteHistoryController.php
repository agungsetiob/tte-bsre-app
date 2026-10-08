<?php

namespace App\Http\Controllers;

use App\Models\TteHistory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TteHistoryController extends Controller
{
    public function index(Request $request)
    {
        // Ambil histori milik user, urutkan dari yang terbaru, beserta relasi dokumennya
        $histories = TteHistory::with('document:id,title')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return Inertia::render('Histories/Index', [
            'histories' => $histories
        ]);
    }

    // Opsi tambahan jika pengguna ingin menghapus histori tertentu
    public function destroy(TteHistory $history)
    {
        if ($history->user_id !== auth()->id()) {
            abort(403);
        }

        $history->delete();

        return back()->with('success', 'Histori berhasil dihapus.');
    }
}