<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LetterClassification;
use App\Models\LetterNumber;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClassificationController extends Controller
{
    public function index()
    {
        $classifications = LetterClassification::orderBy('code', 'asc')->get();
        return Inertia::render('Admin/Classifications/Index', [
            'classifications' => $classifications,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:letter_classifications,code',
            'name' => 'required|string|max:255',
        ]);

        LetterClassification::create($request->only('code', 'name'));
        return flash_success('Klasifikasi keamanan berhasil ditambahkan.');
    }

    public function update(Request $request, LetterClassification $classification)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:letter_classifications,code,' . $classification->id,
            'name' => 'required|string|max:255',
        ]);

        $classification->update($request->only('code', 'name'));
        return flash_success('Klasifikasi keamanan berhasil diperbarui.');
    }

    public function destroy(LetterClassification $classification)
    {
        // Cegah hapus jika sudah dipakai di riwayat penomoran
        if (LetterNumber::where('classification_id', $classification->id)->exists()) {
            return flash_error('Tidak dapat menghapus klasifikasi ini karena sudah digunakan pada riwayat surat.');
        }

        $classification->delete();
        return flash_success('Klasifikasi keamanan berhasil dihapus.');
    }

    public function toggleActive(LetterClassification $classification)
    {
        $classification->update(['is_active' => !$classification->is_active]);
        $status = $classification->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Klasifikasi berhasil {$status}.");
    }
}