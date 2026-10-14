<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import { ref, computed, watch } from 'vue';
import {
    History,
    FileText,
    Search,
    Calendar,
    Filter,
    ChevronDown,
    Trash2,
    CheckCircle2,
    XCircle,
    AlertCircle,
    ExternalLink,
    RotateCcw,
    Clock4,
} from 'lucide-vue-next';

const props = defineProps<{
    histories: { data: Array<any>; links: Array<any> };
    filters: {
        search?: string;
        status?: string;
        date_from?: string;
        date_to?: string;
    };
}>();

// ============ FILTER STATE ============
const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');

const hasActiveFilter = computed(
    () =>
        !!search.value ||
        !!statusFilter.value ||
        !!dateFrom.value ||
        !!dateTo.value
);

// Debounced router update
let searchTimeout: any = null;
watch([search, statusFilter, dateFrom, dateTo], ([s, st, df, dt]) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(
            route('histories.index'),
            {
                search: s || undefined,
                status: st || undefined,
                date_from: df || undefined,
                date_to: dt || undefined,
            },
            { preserveState: true, replace: true, preserveScroll: true }
        );
    }, 400);
});

const resetFilters = () => {
    search.value = '';
    statusFilter.value = '';
    dateFrom.value = '';
    dateTo.value = '';
};

// ============ DELETE ============
const form = useForm({});
const showDeleteModal = ref(false);
const itemToDelete = ref<any>(null);

const confirmDelete = (history: any) => {
    itemToDelete.value = history;
    showDeleteModal.value = true;
};

const submitDelete = () => {
    if (!itemToDelete.value) return;

    form.delete(route('histories.destroy', itemToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            itemToDelete.value = null;
        },
    });
};

// ============ HELPERS ============
const formatDateTime = (dateString: string) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const actionLabel = (action: string) => {
    const map: Record<string, string> = {
        sign: 'Tanda Tangan',
        batch_sign: 'TTE Massal',
        verify: 'Verifikasi',
        reject: 'Penolakan',
        cancel: 'Pembatalan',
    };
    return map[action] || action;
};

const statusMeta = (status: string) => {
    const map: Record<
        string,
        { label: string; cls: string; dot: string; Icon: any }
    > = {
        success: {
            label: 'BERHASIL',
            cls: 'bg-[#EAF7F3] text-[#0B7563] border-[#BCE5DA]',
            dot: 'bg-[#0B8570]',
            Icon: CheckCircle2,
        },
        failed: {
            label: 'GAGAL',
            cls: 'bg-rose-50 text-rose-700 border-rose-200',
            dot: 'bg-rose-500',
            Icon: XCircle,
        },
    };
    return (
        map[status] || {
            label: (status || '—').toUpperCase(),
            cls: 'bg-slate-50 text-slate-700 border-slate-200',
            dot: 'bg-slate-400',
            Icon: AlertCircle,
        }
    );
};
</script>

<template>
    <Head title="Histori TTE" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <p
                    class="text-[11px] font-bold uppercase tracking-[0.16em] text-[#0B7563]"
                >
                    Riwayat Aktivitas
                </p>
                <h2
                    class="font-display mt-0.5 flex items-center gap-2 text-xl font-bold leading-tight text-[#0F2A2E]"
                >
                    Histori Tanda Tangan Elektronik
                </h2>
                <p class="mt-0.5 text-xs text-[#6A8593]">
                    Semua aktivitas TTE yang pernah Anda lakukan, tercatat rapi.
                </p>
            </div>
        </template>

        <div
            class="mx-auto w-full max-w-8xl space-y-5 px-5 py-8 text-[#0F2A2E] md:px-8"
        >
            <!-- ============ FILTER BAR ============ -->
            <div
                class="rounded-2xl border border-[#DCE8E5] bg-white p-4 shadow-[0_18px_40px_-32px_rgba(15,42,46,.35)]"
            >
                <div class="grid grid-cols-1 gap-3 lg:grid-cols-12">
                    <!-- Search -->
                    <div class="relative lg:col-span-5">
                        <Search
                            class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#6A8593]"
                        />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari judul dokumen..."
                            class="w-full rounded-xl border border-[#D9E6E3] bg-[#F8FCFB] py-2.5 pl-9 pr-3 text-sm text-[#0F2A2E] placeholder:text-[#A8BCC2] transition-colors focus:border-[#0B8570] focus:outline-none focus:ring-0"
                        />
                    </div>

                    <!-- Status -->
                    <div class="relative lg:col-span-3">
                        <Filter
                            class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#6A8593]"
                        />
                        <select
                            v-model="statusFilter"
                            class="w-full appearance-none rounded-xl border border-[#D9E6E3] bg-[#F8FCFB] py-2.5 pl-9 pr-8 text-sm text-[#0F2A2E] transition-colors focus:border-[#0B8570] focus:outline-none focus:ring-0"
                        >
                            <option value="">Semua Status</option>
                            <option value="success">Berhasil</option>
                            <option value="error">Gagal</option>
                        </select>
                        <ChevronDown
                            class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#6A8593]"
                        />
                    </div>

                    <!-- Date From -->
                    <div class="relative lg:col-span-2">
                        <Calendar
                            class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#6A8593]"
                        />
                        <input
                            v-model="dateFrom"
                            type="date"
                            :max="dateTo || undefined"
                            class="w-full rounded-xl border border-[#D9E6E3] bg-[#F8FCFB] py-2.5 pl-9 pr-2 text-sm text-[#0F2A2E] transition-colors focus:border-[#0B8570] focus:outline-none focus:ring-0"
                            placeholder="Dari"
                        />
                    </div>

                    <!-- Date To -->
                    <div class="relative lg:col-span-2">
                        <Calendar
                            class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#6A8593]"
                        />
                        <input
                            v-model="dateTo"
                            type="date"
                            :min="dateFrom || undefined"
                            class="w-full rounded-xl border border-[#D9E6E3] bg-[#F8FCFB] py-2.5 pl-9 pr-2 text-sm text-[#0F2A2E] transition-colors focus:border-[#0B8570] focus:outline-none focus:ring-0"
                            placeholder="Sampai"
                        />
                    </div>
                </div>

                <!-- Active filter indicator -->
                <div
                    v-if="hasActiveFilter"
                    class="mt-3 flex items-center justify-between gap-3 border-t border-[#EEF4F2] pt-3"
                >
                    <p class="text-[11px] text-[#6A8593]">
                        Filter aktif — menampilkan
                        <span class="font-bold text-[#0F2A2E]">
                            {{ histories.data.length }}
                        </span>
                        dari total hasil
                    </p>
                    <button
                        type="button"
                        @click="resetFilters"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-[#E9F0EE] bg-white px-2.5 py-1.5 text-[11px] font-semibold text-[#6A8593] transition-colors hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600"
                    >
                        <RotateCcw class="h-3 w-3" />
                        Reset Filter
                    </button>
                </div>
            </div>

            <!-- ============ TABLE CARD ============ -->
            <div
                class="overflow-hidden rounded-2xl border border-[#DCE8E5] bg-white shadow-[0_18px_40px_-32px_rgba(15,42,46,.35)]"
            >
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-[#E9F0EE] bg-[#F8FCFB]">
                                <th
                                    class="px-6 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                                >
                                    Waktu
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                                >
                                    Dokumen
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                                >
                                    Aksi
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                                >
                                    Status
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                                >
                                    Keterangan
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                                >
                                    Opsi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#EEF4F2]">
                            <!-- Empty -->
                            <tr v-if="histories.data.length === 0">
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <div
                                        class="flex flex-col items-center gap-3"
                                    >
                                        <div
                                            class="grid h-14 w-14 place-items-center rounded-2xl bg-[#EAF7F3]"
                                        >
                                            <History
                                                class="h-7 w-7 text-[#0B7563]"
                                            />
                                        </div>
                                        <div>
                                            <p
                                                class="font-display text-sm font-bold text-[#0F2A2E]"
                                            >
                                                Belum ada histori TTE
                                            </p>
                                            <p
                                                class="mt-1 text-xs text-[#6A8593]"
                                            >
                                                {{
                                                    hasActiveFilter
                                                        ? 'Coba ubah kata kunci atau tanggal filter.'
                                                        : 'Aktivitas TTE Anda akan muncul di sini.'
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <!-- Rows -->
                            <tr
                                v-for="history in histories.data"
                                :key="history.id"
                                class="transition-colors hover:bg-[#F8FCFB]"
                            >
                                <!-- Waktu -->
                                <td class="px-6 py-4 align-top">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-[#EEF4F2] text-[#6A8593]"
                                        >
                                            <Clock4 class="h-3.5 w-3.5" />
                                        </span>
                                        <p
                                            class="whitespace-nowrap text-xs font-semibold text-[#0F2A2E]"
                                        >
                                            {{ formatDateTime(history.created_at) }}
                                        </p>
                                    </div>
                                </td>

                                <!-- Dokumen -->
                                <td class="px-6 py-4 align-top">
                                    <div class="flex items-start gap-2.5">
                                        <span
                                            class="mt-0.5 grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-[#EAF7F3] text-[#0B7563]"
                                        >
                                            <FileText class="h-3.5 w-3.5" />
                                        </span>
                                        <div class="min-w-0">
                                            <p
                                                class="truncate text-sm font-semibold text-[#0F2A2E]"
                                                :title="
                                                    history.document?.title ||
                                                    'Dokumen Dihapus'
                                                "
                                            >
                                                {{
                                                    history.document?.title ||
                                                    'Dokumen Dihapus'
                                                }}
                                            </p>
                                            <Link
                                                v-if="history.document"
                                                :href="
                                                    route(
                                                        'documents.show',
                                                        history.document.id
                                                    )
                                                "
                                                class="mt-0.5 inline-flex items-center gap-1 text-[11px] font-medium text-[#0B7563] hover:underline"
                                            >
                                                Lihat detail
                                                <ExternalLink class="h-2.5 w-2.5" />
                                            </Link>
                                            <p
                                                v-else
                                                class="mt-0.5 text-[11px] italic text-[#A8BCC2]"
                                            >
                                                Dokumen sudah tidak tersedia
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Aksi -->
                                <td class="px-6 py-4 align-top">
                                    <span
                                        class="inline-flex items-center rounded-lg border border-[#E9F0EE] bg-[#F8FCFB] px-2.5 py-1 text-[11px] font-semibold text-[#4A6572]"
                                    >
                                        {{ actionLabel(history.action) }}
                                    </span>
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-4 align-top">
                                    <span
                                        :class="statusMeta(history.status).cls"
                                        class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider"
                                    >
                                        <span
                                            class="h-1.5 w-1.5 rounded-full"
                                            :class="statusMeta(history.status).dot"
                                        />
                                        {{ statusMeta(history.status).label }}
                                    </span>
                                </td>

                                <!-- Keterangan -->
                                <td class="px-6 py-4 align-top">
                                    <p
                                        class="line-clamp-2 max-w-xs text-xs leading-relaxed text-[#4A6572]"
                                        :title="history.response_message"
                                    >
                                        {{ history.response_message || '—' }}
                                    </p>
                                </td>

                                <!-- Opsi -->
                                <td class="px-6 py-4 text-right align-top">
                                    <button
                                        type="button"
                                        @click="confirmDelete(history)"
                                        class="inline-flex items-center gap-1 rounded-lg border border-[#E9F0EE] bg-white px-2.5 py-1.5 text-[11px] font-semibold text-[#6A8593] transition-colors hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600"
                                    >
                                        <Trash2 class="h-3.5 w-3.5" />
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ============ PAGINATION ============ -->
            <div
                v-if="histories.links && histories.links.length > 3"
                class="flex justify-center pt-2"
            >
                <div
                    class="inline-flex items-center gap-1 rounded-xl border border-[#DCE8E5] bg-white p-1 shadow-[0_18px_40px_-32px_rgba(15,42,46,.35)]"
                >
                    <template v-for="(link, k) in histories.links" :key="k">
                        <div
                            v-if="link.url === null"
                            class="cursor-not-allowed rounded-lg px-3 py-1.5 text-xs font-semibold text-[#A8BCC2]"
                            v-html="link.label"
                        />
                        <Link
                            v-else
                            :href="link.url"
                            preserve-scroll
                            :class="
                                link.active
                                    ? 'bg-[#0B8570] text-white'
                                    : 'text-[#4A6572] hover:bg-[#F2FBF8] hover:text-[#0B7563]'
                            "
                            class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-colors"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>
        </div>

        <!-- ============ DELETE MODAL ============ -->
        <Modal :show="showDeleteModal" max-width="md" @close="showDeleteModal = false">
            <div class="p-6">
                <div class="flex items-start gap-3">
                    <span
                        class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-rose-50 text-rose-600"
                    >
                        <Trash2 class="h-5 w-5" />
                    </span>
                    <div class="min-w-0">
                        <h3 class="font-display text-lg font-bold text-[#0F2A2E]">
                            Hapus Histori?
                        </h3>
                        <p class="mt-0.5 text-xs text-[#6A8593]">
                            Tindakan ini tidak dapat dibatalkan.
                        </p>
                    </div>
                </div>

                <div
                    class="mt-5 rounded-xl border border-[#E9F0EE] bg-[#F8FCFB] p-3.5 text-sm"
                >
                    <p class="text-[11px] text-[#6A8593]">Dokumen</p>
                    <p class="mt-0.5 truncate font-semibold text-[#0F2A2E]">
                        {{ itemToDelete?.document?.title || 'Dokumen Dihapus' }}
                    </p>
                    <p class="mt-2 text-[11px] text-[#6A8593]">
                        {{ formatDateTime(itemToDelete?.created_at) }}
                    </p>
                </div>

                <div class="mt-5 flex justify-end gap-2 border-t border-[#EEF4F2] pt-4">
                    <button
                        type="button"
                        @click="showDeleteModal = false"
                        class="rounded-xl border border-[#D9E6E3] bg-white px-4 py-2 text-sm font-semibold text-[#4A6572] transition-colors hover:bg-[#F2FBF8]"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        :disabled="form.processing"
                        @click="submitDelete"
                        class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-[0_18px_40px_-24px_rgba(225,29,72,.55)] transition-all hover:-translate-y-0.5 hover:bg-rose-700 disabled:opacity-50 disabled:hover:translate-y-0"
                    >
                        {{ form.processing ? 'Menghapus…' : 'Hapus Histori' }}
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>