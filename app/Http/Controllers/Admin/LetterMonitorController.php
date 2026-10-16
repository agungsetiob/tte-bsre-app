<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LetterNumber;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LetterMonitorController extends Controller
{
    public function index(Request $request)
    {
        $query = LetterNumber::with(['user', 'department', 'classification', 'letterType']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('full_number', 'like', "%{$search}%")
                  ->orWhere('purpose', 'like', "%{$search}%");
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $letters = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Admin/LetterMonitor/Index', [
            'letters' => $letters,
            'filters' => $request->only(['search', 'department_id']),
            'departments' => \App\Models\Department::select('id', 'name')->orderBy('name')->get()
        ]);
    }

    public function export(Request $request)
    {
        $query = LetterNumber::with(['user', 'department', 'classification', 'letterType']);

        // Terapkan filter yang sama dengan tampilan tabel
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('full_number', 'like', "%{$search}%")
                  ->orWhere('purpose', 'like', "%{$search}%");
            });
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $letters = $query->orderBy('letter_date', 'desc')->get();

        $fileName = 'Laporan_Buku_Agenda_Surat_' . date('Ymd_His') . '.csv';

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        // Header Kolom Excel
        $columns = [
            'No', 
            'Tanggal Surat', 
            'Nomor Surat', 
            'Perihal', 
            'Unit Kerja', 
            'Peminta', 
            'Klasifikasi Keamanan', 
            'Jenis Surat', 
            'Waktu Dibuat di Sistem'
        ];

        $callback = function() use($letters, $columns) {
            $file = fopen('php://output', 'w');
            
            // Tambahkan BOM agar karakter khusus terbaca dengan baik di Excel Windows
            fputs($file, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
            
            fputcsv($file, $columns, ';');

            $rowNum = 1;
            foreach ($letters as $letter) {
                $row = [
                    $rowNum++,
                    $letter->letter_date ? \Carbon\Carbon::parse($letter->letter_date)->format('d/m/Y') : '-',
                    $letter->full_number,
                    $letter->purpose,
                    $letter->department ? $letter->department->name : '-',
                    $letter->user ? $letter->user->name : '-',
                    $letter->classification ? $letter->classification->name : '-',
                    $letter->letterType ? $letter->letterType->name : '-',
                    $letter->created_at ? \Carbon\Carbon::parse($letter->created_at)->format('d/m/Y H:i') : '-'
                ];
                fputcsv($file, $row, ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}