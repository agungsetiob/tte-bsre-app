<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import VuePdfEmbed from 'vue-pdf-embed';
import { useEventListener } from '@vueuse/core';
import Modal from './Modal.vue';
import {
    FileSignature,
    KeyRound,
    Eye,
    EyeOff,
    Move,
    AlertCircle,
    X,
} from 'lucide-vue-next';

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

watch(
    () => props.show,
    (newVal) => {
        if (newVal) signForm.reset();
    }
);

const pdfContainer = ref<HTMLElement | null>(null);
const pdfScale = ref(1);
const isDragging = ref(false);
const specX = ref(50);
const specY = ref(50);
const pdfOriginalWidth = ref(595.28);
const pdfOriginalHeight = ref(841.89);

/** Lebar render PDF dalam piksel (≈ A4 pada 96 DPI) */
const PDF_RENDER_WIDTH = 700;

const onPdfRendered = () => {
    if (!pdfContainer.value) return;

    const canvas = pdfContainer.value.querySelector('canvas');
    if (canvas) {
        const renderedWidth =
            canvas.clientWidth || pdfContainer.value.getBoundingClientRect().width;
        const aspectRatio = canvas.height / canvas.width;

        pdfOriginalWidth.value = aspectRatio < 1 ? 841.89 : 595.28;
        pdfOriginalHeight.value = pdfOriginalWidth.value * aspectRatio;

        pdfScale.value = pdfOriginalWidth.value / renderedWidth;
    } else {
        const rect = pdfContainer.value.getBoundingClientRect();
        pdfScale.value = 595.28 / rect.width;
    }
};

const submitSign = () => {
    if (signForm.tampilan === 'VISIBLE' && pdfContainer.value) {
        const rect = pdfContainer.value.getBoundingClientRect();

        const scaleX = pdfOriginalWidth.value / rect.width;
        const scaleY = pdfOriginalHeight.value / rect.height;

        signForm.originX = Math.round(specX.value * scaleX);
        signForm.originY = Math.round(specY.value * scaleY);
    }

    signForm.post(route('documents.sign', props.document.id), {
        preserveScroll: true,
        onSuccess: () => {
            emit('update:show', false);
            emit('success');
        },
    });
};

useEventListener(window, 'mousemove', (e: MouseEvent) => {
    if (isDragging.value && pdfContainer.value) {
        const rect = pdfContainer.value.getBoundingClientRect();
        const domWidth = signForm.width / pdfScale.value;
        const domHeight = signForm.height / pdfScale.value;

        specX.value = Math.max(
            0,
            Math.min(e.clientX - rect.left - domWidth / 2, rect.width - domWidth)
        );
        specY.value = Math.max(
            0,
            Math.min(e.clientY - rect.top - domHeight / 2, rect.height - domHeight)
        );
    }
});
useEventListener(window, 'mouseup', () => {
    isDragging.value = false;
});

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
    <Modal :show="show" max-width="7xl" @close="close">
        <div class="flex max-h-[90vh] flex-col bg-white">
            <!-- Header -->
            <div
                class="flex shrink-0 items-center gap-3 border-b border-[#E9F0EE] px-6 py-4"
            >
                <span
                    class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-[#EAF7F3] text-[#0B7563]"
                >
                    <FileSignature class="h-5 w-5" />
                </span>
                <div class="min-w-0 flex-1">
                    <h3
                        class="font-display text-lg font-bold leading-tight text-[#0F2A2E]"
                    >
                        Proses Tanda Tangan Elektronik
                    </h3>
                    <p
                        class="mt-0.5 truncate text-xs text-[#6A8593]"
                        :title="document?.title"
                    >
                        {{ document?.title }}
                    </p>
                </div>
                <button
                    type="button"
                    @click="close"
                    class="grid h-8 w-8 shrink-0 place-items-center rounded-lg text-[#6A8593] transition-colors hover:bg-[#F2FBF8] hover:text-[#0B7563]"
                    aria-label="Tutup"
                >
                    <X class="h-4 w-4" />
                </button>
            </div>

            <!-- Body -->
            <div
                class="flex min-h-0 flex-1 flex-col gap-5 overflow-y-auto p-6 lg:flex-row lg:gap-6 lg:overflow-hidden"
            >
                <!-- ============ PANEL KIRI : FORM ============ -->
                <div
                    :class="
                        signForm.tampilan === 'VISIBLE'
                            ? 'w-full lg:w-2/6 lg:shrink-0'
                            : 'w-full'
                    "
                    class="space-y-5 lg:overflow-y-auto lg:pr-1"
                >
                    <!-- Error -->
                    <div
                        v-if="errorMessage"
                        class="flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700"
                    >
                        <AlertCircle class="mt-0.5 h-4 w-4 shrink-0" />
                        <span class="font-medium">{{ errorMessage }}</span>
                    </div>

                    <form @submit.prevent="submitSign" class="space-y-5">
                        <!-- Passphrase -->
                        <div>
                            <label
                                class="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                            >
                                <KeyRound class="h-3.5 w-3.5" />
                                Passphrase BSrE
                            </label>
                            <input
                                type="password"
                                v-model="signForm.passphrase"
                                autocomplete="off"
                                required
                                placeholder="••••••••"
                                class="mt-2 block w-full rounded-xl border border-[#D9E6E3] bg-[#F8FCFB] px-3.5 py-2.5 text-sm text-[#0F2A2E] placeholder:text-[#A8BCC2] focus:border-[#0B8570] focus:outline-none focus:ring-0"
                            />
                            <p
                                v-if="signForm.errors.passphrase"
                                class="mt-1 text-xs text-rose-600"
                            >
                                {{ signForm.errors.passphrase }}
                            </p>
                        </div>

                        <!-- Tampilan TTE -->
                        <div>
                            <label
                                class="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                            >
                                <Eye class="h-3.5 w-3.5" />
                                Tampilan TTE
                            </label>
                            <div class="mt-2 grid grid-cols-2 gap-2">
                                <button
                                    type="button"
                                    @click="signForm.tampilan = 'INVISIBLE'"
                                    :class="
                                        signForm.tampilan === 'INVISIBLE'
                                            ? 'border-[#0B8570] bg-[#EAF7F3] text-[#0B7563] shadow-[0_0_0_3px_rgba(11,133,112,.15)]'
                                            : 'border-[#D9E6E3] bg-white text-[#4A6572] hover:border-[#BCE5DA] hover:bg-[#F8FCFB]'
                                    "
                                    class="flex flex-col items-start gap-1 rounded-xl border p-3 text-left transition-all"
                                >
                                    <EyeOff class="h-4 w-4" />
                                    <span class="text-sm font-semibold">Invisible</span>
                                    <span class="text-[11px] opacity-80">Tanpa visual</span>
                                </button>
                                <button
                                    type="button"
                                    @click="signForm.tampilan = 'VISIBLE'"
                                    :class="
                                        signForm.tampilan === 'VISIBLE'
                                            ? 'border-[#0B8570] bg-[#EAF7F3] text-[#0B7563] shadow-[0_0_0_3px_rgba(11,133,112,.15)]'
                                            : 'border-[#D9E6E3] bg-white text-[#4A6572] hover:border-[#BCE5DA] hover:bg-[#F8FCFB]'
                                    "
                                    class="flex flex-col items-start gap-1 rounded-xl border p-3 text-left transition-all"
                                >
                                    <Eye class="h-4 w-4" />
                                    <span class="text-sm font-semibold">Visible</span>
                                    <span class="text-[11px] opacity-80">Pakai spesimen</span>
                                </button>
                            </div>
                        </div>

                        <!-- Dimensi & Halaman -->
                        <Transition
                            enter-active-class="ease-out duration-200"
                            enter-from-class="opacity-0 -translate-y-1"
                            enter-to-class="opacity-100 translate-y-0"
                        >
                            <div
                                v-if="signForm.tampilan === 'VISIBLE'"
                                class="space-y-3 rounded-xl border border-[#E9F0EE] bg-[#F8FCFB] p-3.5"
                            >
                                <p
                                    class="text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                                >
                                    Dimensi & Halaman
                                </p>
                                <div class="grid grid-cols-3 gap-2">
                                    <div>
                                        <label
                                            class="text-[11px] font-medium text-[#6A8593]"
                                        >
                                            Lebar (pt)
                                        </label>
                                        <input
                                            type="number"
                                            v-model="signForm.width"
                                            class="mt-1 w-full rounded-lg border border-[#D9E6E3] bg-white px-2.5 py-1.5 text-sm text-[#0F2A2E] focus:border-[#0B8570] focus:outline-none focus:ring-0"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            class="text-[11px] font-medium text-[#6A8593]"
                                        >
                                            Tinggi (pt)
                                        </label>
                                        <input
                                            type="number"
                                            v-model="signForm.height"
                                            class="mt-1 w-full rounded-lg border border-[#D9E6E3] bg-white px-2.5 py-1.5 text-sm text-[#0F2A2E] focus:border-[#0B8570] focus:outline-none focus:ring-0"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            class="text-[11px] font-medium text-[#6A8593]"
                                        >
                                            Halaman
                                        </label>
                                        <input
                                            type="number"
                                            v-model="signForm.page"
                                            min="1"
                                            class="mt-1 w-full rounded-lg border border-[#D9E6E3] bg-white px-2.5 py-1.5 text-sm text-[#0F2A2E] focus:border-[#0B8570] focus:outline-none focus:ring-0"
                                        />
                                    </div>
                                </div>
                                <p
                                    class="flex items-start gap-1.5 text-[11px] leading-relaxed text-[#6A8593]"
                                >
                                    <Move class="mt-0.5 h-3 w-3 shrink-0" />
                                    Geser spesimen di preview untuk menentukan posisi tanda
                                    tangan.
                                </p>
                            </div>
                        </Transition>

                        <!-- Actions -->
                        <div
                            class="flex justify-end gap-2 border-t border-[#EEF4F2] pt-4"
                        >
                            <button
                                type="button"
                                @click="close"
                                class="rounded-xl border border-[#D9E6E3] bg-white px-4 py-2 text-sm font-semibold text-[#4A6572] transition-colors hover:bg-[#F2FBF8]"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                :disabled="signForm.processing"
                                class="rounded-xl bg-[#0B8570] px-4 py-2 text-sm font-semibold text-white shadow-[0_18px_40px_-24px_rgba(11,133,112,.8)] transition-all hover:-translate-y-0.5 hover:bg-[#086B5B] disabled:opacity-50 disabled:hover:translate-y-0"
                            >
                                {{ signForm.processing ? 'Memproses…' : 'Proses TTE' }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- ============ PANEL KANAN : PREVIEW PDF ============ -->
                <div
                    v-if="signForm.tampilan === 'VISIBLE'"
                    class="relative flex w-full flex-col lg:w-3/4 lg:min-h-0"
                >
                    <div
                        class="flex min-h-0 flex-1 flex-col overflow-hidden rounded-xl border border-[#D9E6E3] bg-[#EEF4F2]"
                    >
                        <!-- Preview header -->
                        <div
                            class="flex shrink-0 items-center justify-between border-b border-[#D9E6E3] bg-white/70 px-3 py-2 backdrop-blur"
                        >
                            <p
                                class="text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                            >
                                Preview Dokumen
                            </p>
                            <span
                                class="rounded-md bg-[#EAF7F3] px-2 py-0.5 text-[10px] font-bold text-[#0B7563]"
                            >
                                Hal. {{ signForm.page }}
                            </span>
                        </div>

                        <!-- Scroll area (vertikal & horizontal) -->
                        <div
                            class="min-h-0 flex-1 overflow-auto p-4"
                            style="max-height: calc(90vh - 13rem)"
                        >
                            <div
                                v-if="document"
                                ref="pdfContainer"
                                class="relative mx-auto bg-white shadow-[0_20px_50px_-30px_rgba(15,42,46,.4)] ring-1 ring-[#E0EAE7]"
                                style="width: fit-content"
                            >
                                <VuePdfEmbed
                                    :source="
                                        assetUrl(
                                            document.signed_file_path || document.file_path
                                        )
                                    "
                                    :page="signForm.page"
                                    :width="PDF_RENDER_WIDTH"
                                    @rendered="onPdfRendered"
                                />

                                <!-- Specimen overlay -->
                                <div
                                    v-if="specimenPath"
                                    class="absolute cursor-move border-2 border-[#0B8570] bg-[#0B8570]/15 shadow-[0_0_0_4px_rgba(11,133,112,.12)] transition-shadow"
                                    :class="isDragging ? 'shadow-[0_0_0_6px_rgba(11,133,112,.22)]' : ''"
                                    :style="{
                                        left: specX + 'px',
                                        top: specY + 'px',
                                        width: signForm.width / pdfScale + 'px',
                                        height: signForm.height / pdfScale + 'px',
                                    }"
                                    @mousedown.prevent="isDragging = true"
                                >
                                    <img
                                        :src="assetUrl(specimenPath)"
                                        class="pointer-events-none h-full w-full object-contain"
                                    />
                                </div>

                                <!-- Empty specimen -->
                                <div
                                    v-else
                                    class="absolute inset-0 flex items-center justify-center bg-white/90 p-4 text-center text-xs font-semibold text-rose-600 backdrop-blur-sm"
                                >
                                    Anda belum mengunggah spesimen tanda tangan di profil.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Modal>
</template>