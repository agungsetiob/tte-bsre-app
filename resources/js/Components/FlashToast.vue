<script setup lang="ts">
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { CheckCircle2, XCircle, X } from 'lucide-vue-next';

type ToastType = 'success' | 'error';

interface ToastItem {
    id: number;
    type: ToastType;
    message: string;
}

const DURATION = 5000;

const page = usePage();
const toasts = ref<ToastItem[]>([]);
let nextId = 1;

function pushToast(type: ToastType, message: string) {
    if (!message) return;
    const id = nextId++;
    toasts.value.push({ id, type, message });
    window.setTimeout(() => removeToast(id), DURATION);
}

function removeToast(id: number) {
    toasts.value = toasts.value.filter((t) => t.id !== id);
}

// Watch seluruh objek flash supaya tetap trigger walau pesannya sama persis
watch(
    () => page.props.flash,
    (flash: any) => {
        if (!flash) return;
        if (flash.success) pushToast('success', flash.success);
        if (flash.error) pushToast('error', flash.error);
    },
    { immediate: true }
);
</script>

<template>
    <Teleport to="body">
        <div
            class="pointer-events-none fixed right-4 top-4 z-[9999] flex w-full max-w-sm flex-col gap-3"
        >
            <TransitionGroup
                enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="translate-x-full opacity-0"
                enter-to-class="translate-x-0 opacity-100"
                leave-active-class="transition-all duration-200 ease-in"
                leave-from-class="translate-x-0 opacity-100"
                leave-to-class="translate-x-full opacity-0"
                move-class="transition-all duration-300"
            >
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    role="alert"
                    class="pointer-events-auto relative overflow-hidden rounded-2xl border bg-white/95 shadow-[0_20px_50px_-20px_rgba(15,42,46,.35)] backdrop-blur"
                    :class="
                        toast.type === 'success'
                            ? 'border-[#BCE5DA]'
                            : 'border-rose-200'
                    "
                >
                    <div class="flex items-start gap-3 p-4">
                        <div
                            class="grid h-9 w-9 shrink-0 place-items-center rounded-xl"
                            :class="
                                toast.type === 'success'
                                    ? 'bg-[#EAF7F3] text-[#0B7563]'
                                    : 'bg-rose-50 text-rose-600'
                            "
                        >
                            <CheckCircle2
                                v-if="toast.type === 'success'"
                                class="h-5 w-5"
                            />
                            <XCircle v-else class="h-5 w-5" />
                        </div>

                        <div class="min-w-0 flex-1 pt-0.5">
                            <p
                                class="text-[11px] font-bold uppercase tracking-wider"
                                :class="
                                    toast.type === 'success'
                                        ? 'text-[#0B7563]'
                                        : 'text-rose-600'
                                "
                            >
                                {{
                                    toast.type === 'success'
                                        ? 'Berhasil'
                                        : 'Gagal'
                                }}
                            </p>
                            <p
                                class="mt-0.5 break-words text-sm font-medium text-[#0F2A2E]"
                            >
                                {{ toast.message }}
                            </p>
                        </div>

                        <button
                            type="button"
                            aria-label="Tutup"
                            @click="removeToast(toast.id)"
                            class="shrink-0 rounded-lg p-1 text-[#6A8593] transition-colors hover:bg-[#F2FBF8] hover:text-[#0B7563]"
                        >
                            <X class="h-4 w-4" />
                        </button>
                    </div>

                    <!-- Progress bar -->
                    <div class="absolute inset-x-0 bottom-0 h-0.5 bg-black/5">
                        <div
                            class="h-full"
                            :class="
                                toast.type === 'success'
                                    ? 'bg-[#0B8570]'
                                    : 'bg-rose-500'
                            "
                            :style="{
                                animation: `flash-progress ${DURATION}ms linear forwards`,
                            }"
                        />
                    </div>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>

<style scoped>
@keyframes flash-progress {
    from {
        width: 100%;
    }
    to {
        width: 0%;
    }
}
</style>