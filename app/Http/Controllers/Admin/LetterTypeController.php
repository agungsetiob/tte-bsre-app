<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LetterType;
use App\Models\LetterNumber;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LetterTypeController extends Controller
{
    public function index()
    {
        $letterTypes = LetterType::orderBy('code', 'asc')->get();
        return Inertia::render('Admin/LetterTypes/Index', [
            'letterTypes' => $letterTypes,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:letter_types,code',
            'name' => 'required|string|max:255',
        ]);

        LetterType::create($request->only('code', 'name'));
        return flash_success('Jenis surat berhasil ditambahkan.');
    }

    public function update(Request $request, LetterType $letterType)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:letter_types,code,' . $letterType->id,
            'name' => 'required|string|max:255',
        ]);

        $letterType->update($request->only('code', 'name'));
        return flash_success('Jenis surat berhasil diperbarui.');
    }

    public function destroy(LetterType $letterType)
    {
        if (LetterNumber::where('letter_type_id', $letterType->id)->exists()) {
            return flash_error('Tidak dapat menghapus jenis surat ini karena sudah digunakan pada riwayat surat.');
        }

        $letterType->delete();
        return flash_success('Jenis surat berhasil dihapus.');
    }

    public function toggleActive(LetterType $letterType)
    {
        $letterType->update(['is_active' => !$letterType->is_active]);
        $status = $letterType->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Jenis surat berhasil {$status}.");
    }
}