<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DepartmentFormModal from '@/Components/DepartmentFormModal.vue';
import DeleteConfirmModal from '@/Components/DeleteConfirmModal.vue';
import { ref } from 'vue';
import type { DepartmentRow } from '@/types';
import { Building2, Plus, PenLine, Trash2, XCircle, CheckCircle, Loader } from 'lucide-vue-next';

defineProps<{
    departments: DepartmentRow[];
    parents: DepartmentRow[];
}>();

// ---- Form modal state ----
const showFormModal = ref(false);
const editingItem = ref<DepartmentRow | null>(null);

const togglingId = ref<number | null>(null);

const openCreate = () => {
    editingItem.value = null;
    showFormModal.value = true;
};

const openEdit = (dept: DepartmentRow) => {
    editingItem.value = dept;
    showFormModal.value = true;
};

// ---- Delete modal state ----
const showDeleteModal = ref(false);
const deleteTarget = ref<DepartmentRow | null>(null);
const isDeleting = ref(false);

const openDelete = (dept: DepartmentRow) => {
    deleteTarget.value = dept;
    showDeleteModal.value = true;
};

const closeDelete = () => {
    showDeleteModal.value = false;
    deleteTarget.value = null;
};

const confirmDelete = () => {
    if (!deleteTarget.value) return;
    isDeleting.value = true;
    router.delete(route('admin.departments.destroy', deleteTarget.value.id), {
        preserveScroll: true,
        onFinish: () => {
            isDeleting.value = false;
            closeDelete();
        },
    });
};

const toggleStatus = (id: number) => {
    togglingId.value = id;
    router.patch(route('admin.departments.toggle', id), {}, {
        preserveScroll: true,
        onFinish: () => {
            togglingId.value = null;
        },
    });
};
</script>

<template>

    <Head title="Master Unit Kerja" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-display text-xl font-bold leading-tight text-slate-800">Master Unit Kerja</h2>
                    <p class="mt-1 text-xs text-slate-500">Kelola hierarki dan kode surat bagian/bidang rumah sakit.</p>
                </div>
                <button @click="openCreate"
                    class="inline-flex items-center gap-2 rounded-xl bg-[#0B8570] px-4 py-2 text-sm font-semibold text-white transition-all hover:bg-[#086B5B]">
                    <Plus class="h-4 w-4" /> Tambah Unit
                </button>
            </div>
        </template>

        <div class="mx-auto w-full max-w-8xl space-y-5 px-5 py-8 md:px-8">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                Nama
                                Unit Kerja</th>
                            <th
                                class="px-6 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                Kode
                                Surat</th>
                            <th
                                class="px-6 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                Induk
                                (Parent)</th>
                            <th
                                class="px-6 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                Status
                            </th>
                            <th
                                class="px-6 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        <tr v-for="dept in departments" :key="dept.id" class="hover:bg-slate-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="grid h-8 w-8 place-items-center rounded-lg bg-blue-50 text-blue-600">
                                        <Building2 class="h-4 w-4" />
                                    </div>
                                    <span class="text-sm font-semibold text-slate-800">{{ dept.name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 font-mono text-sm font-bold text-slate-600">{{ dept.code }}</td>
                            <td class="px-6 py-4 text-sm text-slate-500">
                                <span v-if="dept.parent"
                                    class="inline-flex rounded-md bg-slate-100 px-2 py-1 text-xs font-medium text-slate-600">
                                    {{ dept.parent.name }}
                                </span>
                                <span v-else class="text-xs italic text-slate-400">Puncak Hierarki</span>
                            </td>
                            <td class="px-6 py-4">
                                <span v-if="dept.is_active"
                                    class="inline-flex rounded-full bg-green-100 px-2 py-1 text-[10px] font-bold text-green-700">Aktif</span>
                                <span v-else
                                    class="inline-flex rounded-full bg-rose-100 px-2 py-1 text-[10px] font-bold text-rose-700">Nonaktif</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button @click="toggleStatus(dept.id)" :disabled="togglingId === dept.id" class="p-1.5"
                                    :class="[
                                        dept.is_active
                                            ? 'text-slate-400 hover:text-amber-600'
                                            : 'text-slate-400 hover:text-green-600',
                                        togglingId === dept.id
                                            ? 'cursor-not-allowed opacity-50'
                                            : ''
                                    ]">
                                    <Loader v-if="togglingId === dept.id" class="h-4 w-4 animate-spin text-blue-700" />
                                    <XCircle v-else-if="dept.is_active" class="h-4 w-4" />
                                    <CheckCircle v-else class="h-4 w-4" />
                                </button>
                                <button @click="openEdit(dept)"
                                    class="p-1.5 text-slate-400 transition-colors hover:text-blue-600">
                                    <PenLine class="h-4 w-4" />
                                </button>
                                <button @click="openDelete(dept)"
                                    class="p-1.5 text-slate-400 transition-colors hover:text-red-600">
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <DepartmentFormModal :show="showFormModal" :item="editingItem" :parents="parents"
            @close="showFormModal = false" />

        <DeleteConfirmModal :show="showDeleteModal" title="Hapus Unit Kerja"
            :description="deleteTarget ? `Hapus '${deleteTarget.name}'? Tindakan ini tidak dapat dibatalkan.` : ''"
            :processing="isDeleting" @close="closeDelete" @confirm="confirmDelete" />
    </AuthenticatedLayout>
</template>