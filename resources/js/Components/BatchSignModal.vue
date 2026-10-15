<script setup lang="ts">
import { computed, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import Modal from "./Modal.vue";
import { KeyRound, Layers, AlertCircle } from "lucide-vue-next";

const props = defineProps<{
    show: boolean;
    documentIds: number[];
}>();

const emit = defineEmits(["update:show", "success"]);

const batchForm = useForm({
    document_ids: [] as number[],
    passphrase: "",
});

// Reset form + sinkronisasi documentIds tiap kali modal dibuka
watch(
    () => props.show,
    (newVal) => {
        if (newVal) {
            batchForm.reset();
            batchForm.clearErrors();
            batchForm.document_ids = [...props.documentIds];
        }
    },
);

// Kalau user mencentang/uncheck dokumen sembari modal terbuka
watch(
    () => props.documentIds,
    (ids) => {
        if (props.show) batchForm.document_ids = [...ids];
    },
    { deep: true },
);

const close = () => emit("update:show", false);

const submitBatchSign = () => {
    batchForm.document_ids = [...props.documentIds];
    batchForm.post(route("documents.batch_sign"), {
        preserveScroll: true,
        onSuccess: () => {
            emit("success");
            close();
        },
    });
};

const serverError = computed(
    () => (batchForm.errors as Record<string, string>).message ?? null,
);
</script>

<template>
    <Modal :show="show" max-width="md" @close="close">
        <div class="p-6">
            <div class="flex items-start gap-3">
                <span
                    class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-[#EAF7F3] text-[#0B7563]"
                >
                    <Layers class="h-5 w-5" />
                </span>
                <div class="min-w-0">
                    <h3 class="font-display text-lg font-bold text-[#0F2A2E]">
                        TTE Massal
                    </h3>
                    <p class="mt-0.5 text-xs text-[#6A8593]">
                        Mode Invisible — tanpa visual spesimen
                    </p>
                </div>
            </div>

            <div
                class="mt-5 rounded-xl border border-[#E9F0EE] bg-[#F8FCFB] p-3.5 text-sm"
            >
                <p class="text-[#4A6572]">
                    Anda akan menandatangani
                    <span class="font-bold text-[#0F2A2E]">
                        {{ documentIds.length }} dokumen
                    </span>
                    sekaligus.
                </p>
            </div>

            <div
                v-if="serverError"
                class="mt-4 flex items-start gap-2.5 rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700"
            >
                <AlertCircle class="mt-0.5 h-4 w-4 shrink-0" />
                <span class="font-medium">{{ serverError }}</span>
            </div>

            <!-- Error validasi field (passphrase, document_ids) -->
            <div
                v-if="
                    batchForm.errors.passphrase ||
                    batchForm.errors.document_ids ||
                    batchForm.errors['document_ids.0']
                "
                class="mt-4 flex items-start gap-2.5 rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700"
            >
                <AlertCircle class="mt-0.5 h-4 w-4 shrink-0" />
                <ul class="list-inside list-disc space-y-0.5 font-medium">
                    <li v-if="batchForm.errors.passphrase">
                        {{ batchForm.errors.passphrase }}
                    </li>
                    <li v-if="batchForm.errors.document_ids">
                        {{ batchForm.errors.document_ids }}
                    </li>
                    <li v-if="batchForm.errors['document_ids.0']">
                        {{ batchForm.errors["document_ids.0"] }}
                    </li>
                </ul>
            </div>

            <form @submit.prevent="submitBatchSign" class="mt-5 space-y-4">
                <div>
                    <label
                        class="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                    >
                        <KeyRound class="h-3.5 w-3.5" />
                        Passphrase BSrE
                    </label>
                    <input
                        type="password"
                        v-model="batchForm.passphrase"
                        required
                        placeholder="••••••••"
                        autocomplete="off"
                        class="mt-1.5 block w-full rounded-xl border border-[#D9E6E3] bg-[#F8FCFB] px-3.5 py-2.5 text-sm text-[#0F2A2E] placeholder:text-[#A8BCC2] focus:border-[#0B8570] focus:outline-none focus:ring-4 focus:ring-[#0B8570]/10"
                    />
                </div>

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
                        :disabled="batchForm.processing"
                        class="rounded-xl bg-[#0B8570] px-4 py-2 text-sm font-semibold text-white shadow-[0_18px_40px_-24px_rgba(11,133,112,.8)] transition-all hover:-translate-y-0.5 hover:bg-[#086B5B] disabled:opacity-50 disabled:hover:translate-y-0"
                    >
                        {{
                            batchForm.processing ? "Memproses…" : "Proses Semua"
                        }}
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>
