<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import LetterTypeFormModal from '@/Components/LetterTypeFormModal.vue';
import DeleteConfirmModal from '@/Components/DeleteConfirmModal.vue';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import {
    ShieldAlert,
    Plus,
    PenLine,
    Trash2,
    XCircle,
    CheckCircle
} from 'lucide-vue-next';

interface LetterType {
    id: number;
    code: string;
    name: string;
    is_active: boolean;
}

const props = defineProps<{ letterTypes: LetterType[] }>();

// ---- Form modal state ----
const showFormModal = ref(false);
const editingItem = ref<LetterType | null>(null);

const openCreate = () => {
    editingItem.value = null;
    showFormModal.value = true;
};

const openEdit = (item: LetterType) => {
    editingItem.value = item;
    showFormModal.value = true;
};

// ---- Delete modal state ----
const showDeleteModal = ref(false);
const deleteTarget = ref<LetterType | null>(null);
const isDeleting = ref(false);

const openDelete = (item: LetterType) => {
    deleteTarget.value = item;
    showDeleteModal.value = true;
};

const closeDelete = () => {
    showDeleteModal.value = false;
    deleteTarget.value = null;
};

const confirmDelete = () => {
    if (!deleteTarget.value) return;
    isDeleting.value = true;
    router.delete(route('admin.letter-types.destroy', deleteTarget.value.id), {
        preserveScroll: true,
        onFinish: () => {
            isDeleting.value = false;
            closeDelete();
        },
    });
};

const toggleStatus = (id: number) => {
    router.patch(route('admin.letter-types.toggle', id), {}, { preserveScroll: true });
};
</script>

<template>
    <Head title="Master Jenis Surat" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-display text-xl font-bold text-slate-800">
                        Master Jenis Surat
                    </h2>
                    <p class="text-xs text-slate-500">
                        Kelola jenis-jenis surat yang dapat dibuat.
                    </p>
                </div>
                <button
                    @click="openCreate"
                    class="flex items-center gap-2 rounded-xl bg-[#0B8570] px-4 py-2 text-sm font-semibold text-white hover:bg-[#086B5B]"
                >
                    <Plus class="h-4 w-4" /> Tambah Jenis Surat
                </button>
            </div>
        </template>

        <div class="mx-auto w-full max-w-8xl space-y-5 px-5 py-8 md:px-8">

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">Kode</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">Nama Jenis Surat</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-bold uppercase text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr
                            v-for="item in letterTypes"
                            :key="item.id"
                            class="hover:bg-slate-50"
                        >
                            <td class="px-6 py-4 font-mono text-sm font-bold text-slate-700">
                                {{ item.code }}
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-slate-800">
                                <div class="flex items-center gap-2">
                                    <ShieldAlert class="h-4 w-4 text-amber-500" /> {{ item.name }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span v-if="item.is_active" class="inline-flex rounded-full bg-green-100 px-2 py-1 text-[10px] font-bold text-green-700">Aktif</span>
                                <span v-else class="inline-flex rounded-full bg-rose-100 px-2 py-1 text-[10px] font-bold text-rose-700">Nonaktif</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <button
                                        @click="toggleStatus(item.id)"
                                        class="mr-2 rounded px-2 py-1 text-xs font-medium transition-colors"
                                        :class="
                                            item.is_active
                                                ? 'text-slate-400 hover:text-amber-600'
                                                : 'text-slate-400 hover:text-green-600'
                                        "
                                    >
                                        <XCircle v-if="item.is_active" class="h-4 w-4" />
                                        <CheckCircle v-else class="h-4 w-4" />
                                        </button>
                                    <button
                                        @click="openEdit(item)"
                                        class="p-1.5 text-slate-400 hover:text-blue-600"
                                    >
                                        <PenLine class="h-4 w-4" />
                                    </button>
                                    <button
                                        @click="openDelete(item)"
                                        class="ml-1 p-1.5 text-slate-400 hover:text-red-600"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <LetterTypeFormModal
            :show="showFormModal"
            :item="editingItem"
            @close="showFormModal = false"
        />

        <DeleteConfirmModal
            :show="showDeleteModal"
            title="Hapus Jenis Surat"
            :description="deleteTarget ? `Hapus '${deleteTarget.name}'? Tindakan ini tidak dapat dibatalkan.` : ''"
            :processing="isDeleting"
            @close="closeDelete"
            @confirm="confirmDelete"
        />
    </AuthenticatedLayout>
</template>