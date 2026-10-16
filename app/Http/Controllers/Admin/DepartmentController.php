<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DepartmentController extends Controller
{
    public function index()
    {
        // Ambil data departemen beserta relasi parent-nya
        $departments = Department::with('parent')->orderBy('name', 'asc')->get();
        // Ambil data untuk dropdown parent (opsi)
        $parents = Department::orderBy('name', 'asc')->get();

        return Inertia::render('Admin/Departments/Index', [
            'departments' => $departments,
            'parents' => $parents,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:departments,code',
            'parent_id' => 'nullable|exists:departments,id',
        ]);

        Department::create($request->only('name', 'code', 'parent_id'));

        return flash_success('Unit kerja berhasil ditambahkan.');
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:departments,code,' . $department->id,
            'parent_id' => 'nullable|exists:departments,id|not_in:' . $department->id, // Tidak boleh jadi parent untuk dirinya sendiri
        ]);

        $department->update($request->only('name', 'code', 'parent_id'));

        return flash_success('Unit kerja berhasil diperbarui.');
    }

    public function destroy(Department $department)
    {
        if ($department->children()->count() > 0) {
            return flash_error('Tidak dapat menghapus unit kerja ini karena masih memiliki sub-unit.');
        }
        
        if ($department->users()->count() > 0) {
            return flash_error('Tidak dapat menghapus unit kerja ini karena sedang digunakan oleh pegawai.');
        }

        $department->delete();

        return flash_success('Unit kerja berhasil dihapus.');
    }

    public function toggleActive(Department $department)
    {
        $department->update(['is_active' => !$department->is_active]);
        $status = $department->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Unit kerja berhasil {$status}.");
    }
}