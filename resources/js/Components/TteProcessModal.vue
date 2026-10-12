<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import VuePdfEmbed from 'vue-pdf-embed';
import { useEventListener } from '@vueuse/core';

const props = defineProps<{
    show: boolean;
    document: any;
    specimenPath: string | null;
}>();

const emit = defineEmits(['update:show', 'success']);

const assetUrl = (path: string) => '/storage/' + path;

const signForm = useForm({
    passphrase: '',
    tampilan: 'INVISIBLE',
    page: 1,
    originX: 0,
    originY: 0,
    width: 150,
    height: 60,
});

const page = usePage();

const errorMessage = computed(() => {
    return (page.props.errors as any)?.message || (signForm.errors as any)?.message || null;
});

// Reset form tiap kali modal dibuka
watch(() => props.show, (newVal) => {
    if (newVal) signForm.reset();
});

const pdfContainer = ref<HTMLElement | null>(null);
const pdfScale = ref(1);
const isDragging = ref(false);
const specX = ref(50);
const specY = ref(50);
// State dimensi asli PDF (Poin)
const pdfOriginalWidth = ref(595.28);
const pdfOriginalHeight = ref(841.89);

// Hitung Skala & Ukuran Asli PDF secara Dinamis
const onPdfRendered = () => {
    if (!pdfContainer.value) return;

    // Cari elemen canvas atau container render dari VuePdfEmbed
    const canvas = pdfContainer.value.querySelector('canvas');
    if (canvas) {
        // Ambil ukuran piksel render nyata
        const renderedWidth = canvas.clientWidth || pdfContainer.value.getBoundingClientRect().width;
        
        // Ambil rasio aspek dari canvas internal
        const aspectRatio = canvas.height / canvas.width;
        
        // Dapatkan estimasi lebar poin asli berdasarkan rasio canvas
        // (A4 portrait = 595.28, A4 landscape = 841.89)
        pdfOriginalWidth.value = aspectRatio < 1 ? 841.89 : 595.28;
        pdfOriginalHeight.value = pdfOriginalWidth.value * aspectRatio;

        // Hitung skala layar (Poin per Piksel Layar)
        pdfScale.value = pdfOriginalWidth.value / renderedWidth;
    } else {
        // Fallback jika menggunakan SVG/HTML render
        const rect = pdfContainer.value.getBoundingClientRect();
        pdfScale.value = 595.28 / rect.width;
    }
};

const submitSign = () => {
    if (signForm.tampilan === 'VISIBLE' && pdfContainer.value) {
        const rect = pdfContainer.value.getBoundingClientRect();
        
        // Dapatkan rasio skala (Poin PDF per Piksel Layar)
        const scaleX = pdfOriginalWidth.value / rect.width;
        const scaleY = pdfOriginalHeight.value / rect.height;

        // 1. originX: Jarak dari KIRI halaman (dalam Poin PDF)
        signForm.originX = Math.round(specX.value * scaleX);

        // 2. originY: Jarak dari ATAS halaman (murni dari Kiri Atas)
        // Kita tidak lagi membalik dari rect.height
        signForm.originY = Math.round(specY.value * scaleY);
    }
    
    signForm.post(route('documents.sign', props.document.id), {
        preserveScroll: true,
        onSuccess: () => {
            emit('update:show', false);
            emit('success');
        }
    });
};

useEventListener(window, 'mousemove', (e: MouseEvent) => {
    if (isDragging.value && pdfContainer.value) {
        const rect = pdfContainer.value.getBoundingClientRect();
        // Konversi ukuran form (poin) ke ukuran layar (pixel)
        const domWidth = signForm.width / pdfScale.value;
        const domHeight = signForm.height / pdfScale.value;
        
        specX.value = Math.max(0, Math.min(e.clientX - rect.left - (domWidth / 2), rect.width - domWidth));
        specY.value = Math.max(0, Math.min(e.clientY - rect.top - (domHeight / 2), rect.height - domHeight));
    }
});
useEventListener(window, 'mouseup', () => { isDragging.value = false; });

const resetForm = () => {
    signForm.reset();
    signForm.clearErrors();

    Object.assign(signForm, {
        tampilan: 'INVISIBLE',
        page: 1,
        originX: 0,
        originY: 0,
        width: 150,
        height: 60,
    });

    specX.value = 50;
    specY.value = 50;
    isDragging.value = false;
};

const close = () => {
    resetForm();
    emit('update:show', false);
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 overflow-y-auto">
        <div class="bg-white rounded-xl p-6 w-full max-w-4xl shadow-2xl flex gap-6 max-h-[90vh] overflow-y-auto relative">
            
            <!-- Tombol Tutup -->
            <button @click="close" class="absolute top-4 right-4 text-slate-400 hover:text-slate-900 font-bold text-xl leading-none">&times;</button>

            <!-- Panel Kiri: Form -->
            <div :class="signForm.tampilan === 'VISIBLE' ? 'w-1/2' : 'w-full'" class="space-y-4 pt-2">
                <h3 class="text-lg font-bold text-slate-900">Proses TTE: {{ document?.title }}</h3>
                <div v-if="errorMessage" class="p-3 mb-2 text-sm text-red-700 bg-red-100 rounded-lg border border-red-200">
                    {{ errorMessage }}
                </div>
                <form @submit.prevent="submitSign" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium">Passphrase BSrE</label>
                        <input type="password" v-model="signForm.passphrase" autocomplete="off" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm text-sm" />
                        <p v-if="signForm.errors.passphrase" class="mt-1 text-xs text-red-600">{{ signForm.errors.passphrase }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Tampilan TTE</label>
                        <select v-model="signForm.tampilan" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm text-sm">
                            <option value="INVISIBLE">Invisible (Tanpa Visual)</option>
                            <option value="VISIBLE">Visible (Gunakan Spesimen)</option>
                        </select>
                    </div>
                    <div v-if="signForm.tampilan === 'VISIBLE'" class="space-y-3 border-t pt-3">
                        <div class="grid grid-cols-3 gap-2">
                            <div><label class="text-xs font-medium">Lebar</label><input type="number" v-model="signForm.width" class="w-full rounded border-slate-300 text-sm" /></div>
                            <div><label class="text-xs font-medium">Tinggi</label><input type="number" v-model="signForm.height" class="w-full rounded border-slate-300 text-sm" /></div>
                            <div><label class="text-xs font-medium">Halaman</label><input type="number" v-model="signForm.page" min="1" class="w-full rounded border-slate-300 text-sm" /></div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-4 border-t border-slate-100">
                        <button type="button" @click="close" class="px-4 py-2 bg-slate-100 rounded-lg text-sm font-medium">Batal</button>
                        <button type="submit" :disabled="signForm.processing" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50">
                            Proses TTE
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Panel Kanan: Preview & Drag -->
            <div v-if="signForm.tampilan === 'VISIBLE'" class="w-1/2 bg-slate-100 p-2 rounded-xl flex justify-center overflow-auto relative items-start border border-slate-200">
                <div v-if="document" class="relative border bg-white shadow-md" ref="pdfContainer" style="width: fit-content;">
                    <VuePdfEmbed 
                        :source="assetUrl(document.signed_file_path || document.file_path)" 
                        :page="signForm.page" 
                        @rendered="onPdfRendered" 
                    />
                    
                    <div v-if="specimenPath" class="absolute border-2 border-blue-500 bg-blue-100/40 cursor-move"
                        :style="{ 
                            left: specX + 'px', 
                            top: specY + 'px', 
                            width: (signForm.width / pdfScale) + 'px', 
                            height: (signForm.height / pdfScale) + 'px' 
                        }"
                        @mousedown.prevent="isDragging = true">
                        <img :src="assetUrl(specimenPath)" class="w-full h-full object-contain pointer-events-none" />
                    </div>
                    <div v-else class="absolute inset-0 flex items-center justify-center bg-white/90 p-4 text-center text-red-600 text-xs font-semibold backdrop-blur-sm">
                        Anda belum mengunggah spesimen tanda tangan di profil.
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>