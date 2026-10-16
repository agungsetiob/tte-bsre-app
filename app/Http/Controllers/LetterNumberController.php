<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\LetterClassification;
use App\Models\LetterType;
use App\Models\LetterNumber;
use App\Services\LetterNumberService;

class LetterNumberController extends Controller
{
    // Menampilkan riwayat penomoran pengguna dan form request
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user->department_id) {
            return flash_error(
                'Anda belum di-assign ke Unit Kerja mana pun. Silakan hubungi Admin atau edit profil Anda.'
            );
        }

        $classifications = LetterClassification::where('is_active', true)->orderBy('code', 'asc')->get();
        $letterTypes = LetterType::where('is_active', true)->orderBy('code', 'asc')->get();

        $history = LetterNumber::with(['classification', 'letterType'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        return Inertia::render('LetterNumbers/Index', [
            'classifications' => $classifications,
            'letterTypes'     => $letterTypes,
            'history'         => $history,
            'userDepartment'  => $user->department->name,
        ]);
    }

    // Memproses pembuatan nomor surat
    public function store(Request $request, LetterNumberService $service)
    {
        $request->validate([
            'classification_id' => 'required|exists:letter_classifications,id',
            'letter_type_id'    => 'required|exists:letter_types,id',
            'purpose'           => 'required|string|max:255',
            'letter_date'       => 'required|date',
        ]);

        $user = $request->user();

        try {
            $letterNumber = $service->generateNumber(
                $user->id,
                $user->department_id,
                $request->classification_id,
                $request->letter_type_id,
                $request->purpose,
                $request->letter_date
            );

            return flash_success('Nomor surat berhasil dibuat: ' . $letterNumber->full_number);
        } catch (\Exception $e) {
            return flash_error('Gagal membuat nomor surat. Silakan coba lagi.');
        }
    }
}
