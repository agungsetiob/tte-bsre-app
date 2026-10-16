<script setup lang="ts">
import { computed, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import Modal from "./Modal.vue";
import { AlertTriangle, Loader } from "lucide-vue-next";

const props = defineProps<{
    show: boolean;
    request: any;
}>();

const emit = defineEmits(["update:show", "success"]);

const rejectForm = useForm({ reason: "" });

watch(
    () => props.show,
    (newVal) => {
        if (newVal) {
            rejectForm.reset();
            rejectForm.clearErrors();
        }
    },
);

const close = () => emit("update:show", false);

const submitReject = () => {
    if (!props.request?.document?.id) return;

    rejectForm.post(route("documents.rejectSign", props.request.document.id), {
        preserveScroll: true,
        onSuccess: () => {
            emit("success");
            close();
        },
    });
};

const serverError = computed(
    () => (rejectForm.errors as Record<string, string>).message ?? null,
);
</script>

<template>
    <Modal :show="show" max-width="md" @close="close">
        <div class="p-6">
            <div class="flex items-start gap-3">
                <span
                    class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-rose-50 text-rose-600"
                >
                    <AlertTriangle class="h-5 w-5" />
                </span>
                <div class="min-w-0">
                    <h3 class="font-display text-lg font-bold text-[#0F2A2E]">
                        Tolak Permintaan TTE
                    </h3>
                    <p class="mt-0.5 truncate text-xs text-[#6A8593]">
                        {{ request?.document?.title }}
                    </p>
                </div>
            </div>

            <p class="mt-5 text-sm leading-relaxed text-[#4A6572]">
                Dokumen akan dikembalikan ke staf pengunggah. Mohon berikan
                alasan yang jelas agar dapat diperbaiki.
            </p>

            <div
                v-if="serverError"
                class="mt-4 flex items-start gap-2.5 rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700"
            >
                <AlertTriangle class="mt-0.5 h-4 w-4 shrink-0" />
                <span class="font-medium">{{ serverError }}</span>
            </div>

            <form @submit.prevent="submitReject" class="mt-4 space-y-4">
                <div>
                    <label
                        class="block text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                    >
                        Alasan Penolakan
                    </label>
                    <textarea
                        v-model="rejectForm.reason"
                        required
                        rows="3"
                        placeholder="Contoh: Ada salah ketik pada nominal angka…"
                        class="mt-1.5 block w-full resize-none rounded-xl border border-[#D9E6E3] bg-[#F8FCFB] px-3.5 py-2.5 text-sm text-[#0F2A2E] placeholder:text-[#A8BCC2] focus:border-rose-500 focus:outline-none focus:ring-4 focus:ring-rose-500/10"
                    />
                    <p
                        v-if="rejectForm.errors.reason"
                        class="mt-1 text-xs text-rose-600"
                    >
                        {{ rejectForm.errors.reason }}
                    </p>
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
                        :disabled="rejectForm.processing"
                        class="flex justify-center items-center gap-2 rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-[0_18px_40px_-24px_rgba(225,29,72,.5)] transition-all hover:-translate-y-0.5 hover:bg-rose-700 disabled:opacity-50 disabled:hover:translate-y-0"
                    >
                        <Loader v-if="rejectForm.processing" class="h-4 w-4 animate-spin" />
                        {{
                            rejectForm.processing
                                ? "Mengirim…"
                                : "Kirim Penolakan"
                        }}
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>
