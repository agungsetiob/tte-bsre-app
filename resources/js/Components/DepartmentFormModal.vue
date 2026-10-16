<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import { watch } from 'vue';
import type { DepartmentRow } from '@/types';
import { Loader } from 'lucide-vue-next';

const props = defineProps<{
    show: boolean;
    item: DepartmentRow | null;
    parents: DepartmentRow[];
}>();

const emit = defineEmits<{ close: [] }>();

const form = useForm({
    name: '',
    code: '',
    parent_id: '' as number | string,
});

// Isi ulang form setiap kali modal dibuka / item berubah
watch(
    () => [props.show, props.item],
    ([visible]) => {
        if (!visible) return;
        if (props.item) {
            form.name = props.item.name;
            form.code = props.item.code;
            form.parent_id = props.item.parent_id ?? '';
        } else {
            form.reset();
        }
        form.clearErrors();
    },
    { immediate: true },
);

const isEditing = () => !!props.item;

const submit = () => {
    if (isEditing() && props.item) {
        form.put(route('admin.departments.update', props.item.id), {
            preserveScroll: true,
            onSuccess: () => emit('close'),
        });
    } else {
        form.post(route('admin.departments.store'), {
            preserveScroll: true,
            onSuccess: () => emit('close'),
        });
    }
};
</script>

<template>
    <Modal :show="show" max-width="md" @close="$emit('close')">
        <div class="p-6">
            <h3 class="mb-4 text-lg font-bold text-slate-800">
                {{ isEditing() ? 'Edit Unit Kerja' : 'Tambah Unit Kerja' }}
            </h3>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-500">Nama Unit</label>
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        placeholder="Cth: Sub Bagian Keuangan"
                        class="mt-1 block w-full rounded-xl border border-slate-200 text-sm focus:border-[#0B8570] focus:outline-none focus:ring-0"
                    />
                    <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-500">Kode Surat</label>
                    <input
                        v-model="form.code"
                        type="text"
                        required
                        placeholder="Cth: RSUD-UMUM.3"
                        class="mt-1 block w-full rounded-xl border border-slate-200 text-sm focus:border-[#0B8570] focus:outline-none focus:ring-0"
                    />
                    <p v-if="form.errors.code" class="mt-1 text-xs text-red-600">{{ form.errors.code }}</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-500">Induk Unit (Parent)</label>
                    <select
                        v-model="form.parent_id"
                        class="mt-1 block w-full rounded-xl border border-slate-200 text-sm focus:border-[#0B8570] focus:outline-none focus:ring-0"
                    >
                        <option value="">-- Puncak Hierarki (Tidak ada Induk) --</option>
                        <template v-for="parent in parents" :key="parent.id">
                            <option v-if="parent.id !== item?.id" :value="parent.id">
                                {{ parent.name }}
                            </option>
                        </template>
                    </select>
                    <p v-if="form.errors.parent_id" class="mt-1 text-xs text-red-600">{{ form.errors.parent_id }}</p>
                </div>

                <div class="mt-6 flex justify-end gap-2 border-t border-slate-100 pt-4">
                    <button
                        type="button"
                        @click="$emit('close')"
                        class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-200"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="flex justify-center gap-2 items-center rounded-xl bg-[#0B8570] px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-[#086B5B] disabled:opacity-50"
                    >
                        <Loader v-if="form.processing" class="h-4 w-4 animate-spin" />
                        {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>