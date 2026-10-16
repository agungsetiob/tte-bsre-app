<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import { watch } from 'vue';

interface ClassificationRow {
    id: number;
    code: string;
    name: string;
    is_active: boolean;
}

const props = defineProps<{
    show: boolean;
    item: ClassificationRow | null;
}>();

const emit = defineEmits<{ close: [] }>();

const form = useForm({ code: '', name: '' });

// Isi ulang form setiap kali modal dibuka / item berubah
watch(
    () => [props.show, props.item],
    ([visible]) => {
        if (!visible) return;
        if (props.item) {
            form.code = props.item.code;
            form.name = props.item.name;
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
        form.put(route('admin.classifications.update', props.item.id), {
            preserveScroll: true,
            onSuccess: () => emit('close'),
        });
    } else {
        form.post(route('admin.classifications.store'), {
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
                {{ isEditing() ? 'Edit' : 'Tambah' }} Klasifikasi Keamanan
            </h3>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-500">
                        Kode (Cth: B, R, SR)
                    </label>
                    <input
                        v-model="form.code"
                        type="text"
                        required
                        class="mt-1 block w-full rounded-xl border border-slate-200 text-sm focus:border-[#0B8570] focus:outline-none focus:ring-0"
                    />
                    <p v-if="form.errors.code" class="mt-1 text-xs text-red-600">
                        {{ form.errors.code }}
                    </p>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-500">
                        Nama Klasifikasi
                    </label>
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        class="mt-1 block w-full rounded-xl border border-slate-200 text-sm focus:border-[#0B8570] focus:outline-none focus:ring-0"
                    />
                    <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">
                        {{ form.errors.name }}
                    </p>
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
                        class="rounded-xl bg-[#0B8570] px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-[#086B5B] disabled:opacity-50"
                    >
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>