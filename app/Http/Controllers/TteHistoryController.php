<?php

namespace App\Http\Controllers;

use App\Models\TteHistory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TteHistoryController extends Controller
{
    public function index(Request $request)
    {
        $histories = TteHistory::with('document:id,title')
            ->where('user_id', $request->user()->id)
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->whereHas('document', function ($qq) use ($request) {
                    $qq->where('title', 'like', '%' . $request->search . '%');
                });
            })
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->when($request->filled('date_from'), function ($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->date_to);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Histories/Index', [
            'histories' => $histories,
            'filters'   => $request->only(['search', 'status', 'date_from', 'date_to']),
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
