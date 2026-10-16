<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import { Loader, PenLine } from 'lucide-vue-next';
import { watch } from 'vue';

const props = defineProps<{ show: boolean; document: any }>();
const emit = defineEmits(['close']);

const form = useForm({
    _method: 'put',
    title: '',
    file: null as File | null,
});

watch(() => props.show, (isOpen) => {
    if (isOpen && props.document) {
        form.title = props.document.title;
        form.file = null;
        form.clearErrors();
    }
});

const submit = () => {
    form.post(route('documents.update', props.document.id), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            emit('close');
        },
    });
};
</script>

<template>
    <Modal :show="show" max-width="md" @close="emit('close')">
        <div class="p-6">
            <div class="flex items-start gap-3">
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-[#F2FBF8] text-[#0B7563]">
                    <PenLine class="h-5 w-5" />
                </span>
                <div>
                    <h3 class="font-display text-lg font-bold text-[#0F2A2E]">Edit Dokumen</h3>
                    <p class="mt-0.5 text-xs text-[#6A8593]">Ubah informasi dokumen.</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="mt-5 space-y-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-[#6A8593]">Judul Dokumen</label>
                    <input v-model="form.title" type="text" required class="mt-1.5 block w-full rounded-xl border border-[#D9E6E3] bg-[#F8FCFB] px-3 py-2.5 text-sm text-[#0F2A2E] focus:border-[#0B8570] focus:outline-none focus:ring-0" />
                    <p v-if="form.errors.title" class="mt-1 text-xs text-rose-600">{{ form.errors.title }}</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-[#6A8593]">File PDF Baru (Opsional)</label>
                    <input type="file" accept="application/pdf" @change="(e: any) => (form.file = e.target.files[0])" :disabled="document?.status !== 'pending'" :class="{'opacity-50 cursor-not-allowed': document?.status !== 'pending'}" class="mt-1.5 block w-full text-sm text-[#4A6572] file:mr-3 file:rounded-lg file:border-0 file:bg-[#EAF7F3] file:px-3 file:py-2 file:text-xs file:font-semibold file:text-[#0B7563] hover:file:bg-[#D9F0E9]" />
                    <p v-if="document?.status !== 'pending'" class="mt-1.5 text-xs text-amber-600 font-medium">*File fisik tidak dapat diubah karena dokumen sudah diproses.</p>
                    <p v-if="form.errors.file" class="mt-1 text-xs text-rose-600">{{ form.errors.file }}</p>
                </div>

                <div class="flex justify-end gap-2 border-t border-[#EEF4F2] pt-4">
                    <button type="button" @click="emit('close')" class="rounded-xl border border-[#D9E6E3] bg-white px-4 py-2 text-sm font-semibold text-[#4A6572] transition-colors hover:bg-[#F2FBF8]">Batal</button>
                    <button type="submit" :disabled="form.processing" class="inline-flex rounded-xl items-center justify-center gap-2 bg-[#0B8570] px-4 py-2 text-sm font-semibold text-white shadow-[0_18px_40px_-24px_rgba(11,133,112,.8)] transition-all hover:bg-[#086B5B] disabled:opacity-50">
                        <Loader v-if="form.processing" class="h-4 w-4 animate-spin" />
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>