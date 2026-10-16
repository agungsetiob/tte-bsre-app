<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import { Loader, Trash2 } from 'lucide-vue-next';

const props = defineProps<{ show: boolean; document: any }>();
const emit = defineEmits(['close']);

const form = useForm({});

const submit = () => {
    form.delete(route('documents.destroy', props.document.id), {
        preserveScroll: true,
        onSuccess: () => emit('close'),
    });
};
</script>

<template>
    <Modal :show="show" max-width="md" @close="emit('close')">
        <div class="p-6">
            <div class="flex items-start gap-3">
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-rose-100 text-rose-600">
                    <Trash2 class="h-5 w-5" />
                </span>
                <div>
                    <h3 class="font-display text-lg font-bold text-[#0F2A2E]">Hapus Dokumen</h3>
                    <p class="mt-0.5 text-sm text-[#6A8593]">
                        Apakah Anda yakin ingin menghapus <strong>{{ document?.title }}</strong> secara permanen? Aksi ini tidak dapat dibatalkan.
                    </p>
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t border-[#EEF4F2] pt-5 mt-5">
                <button type="button" @click="emit('close')" class="rounded-xl border border-[#D9E6E3] bg-white px-4 py-2 text-sm font-semibold text-[#4A6572] transition-colors hover:bg-[#F2FBF8]">Batal</button>
                <button type="button" @click="submit" :disabled="form.processing" class="inline-flex items-center justify-center gap-2 rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-[0_18px_40px_-24px_rgba(225,29,72,.8)] transition-all hover:bg-rose-700 disabled:opacity-50">
                    <Loader v-if="form.processing" class="h-4 w-4 animate-spin" />
                    {{ form.processing ? 'Menghapus...' : 'Ya, Hapus Dokumen' }}
                </button>
            </div>
        </div>
    </Modal>
</template>