<script setup lang="ts">
import { computed } from 'vue';
import Modal from '@/Components/Modal.vue';
import { AlertTriangle } from 'lucide-vue-next';

const props = withDefaults(
    defineProps<{
        show: boolean;
        title?: string;
        description?: string;
        confirmLabel?: string;
        cancelLabel?: string;
        processing?: boolean;
    }>(),
    {
        title: 'Konfirmasi Hapus',
        confirmLabel: 'Hapus',
        cancelLabel: 'Batal',
        processing: false,
    },
);

const emit = defineEmits<{ close: []; confirm: [] }>();

const hasDescription = computed(() => !!props.description);
</script>

<template>
    <Modal :show="show" max-width="sm" @close="$emit('close')">
        <div class="p-6">
            <div class="flex items-start gap-3">
                <span
                    class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-amber-50 text-[#B45309]"
                >
                    <AlertTriangle class="h-4 w-4" />
                </span>
                <div class="min-w-0">
                    <h3 class="text-base font-bold text-slate-800">{{ title }}</h3>
                    <p
                        v-if="hasDescription"
                        class="mt-1 text-sm text-slate-500"
                    >
                        {{ description }}
                    </p>
                    <slot />
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2 border-t border-slate-100 pt-4">
                <button
                    type="button"
                    @click="$emit('close')"
                    class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-200"
                >
                    {{ cancelLabel }}
                </button>
                <button
                    type="button"
                    :disabled="processing"
                    @click="$emit('confirm')"
                    class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-[0_18px_40px_-24px_rgba(225,29,72,.8)] transition-all hover:bg-rose-700 disabled:opacity-50"
                >
                    {{ confirmLabel }}
                </button>
            </div>
        </div>
    </Modal>
</template>