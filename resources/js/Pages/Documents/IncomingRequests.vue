<script setup lang="ts">
import { Head, Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { ref, watch } from "vue";
import TteProcessModal from "@/Components/TteProcessModal.vue";
import RejectSignModal from "@/Components/RejectSignModal.vue";
import BatchSignModal from "@/Components/BatchSignModal.vue";
import {
    ArrowLeft,
    Inbox,
    PenLine,
    FileText,
    XCircle,
    Search,
    Calendar,
    Filter,
    ChevronDown,
    Layers,
    ExternalLink,
} from "lucide-vue-next";
const props = defineProps<{
    incomingRequests: { data: Array<any>; links: Array<any> };
    filters: { search?: string; status?: string; date?: string };
    auth: { user: { name: string; specimen_path: string | null } };
}>();

// STATE FILTER
const search = ref(props.filters.search || "");
const statusFilter = ref(props.filters.status ?? "pending");
const dateFilter = ref(props.filters.date || "");

let searchTimeout: any = null;
watch([search, statusFilter, dateFilter], ([newSearch, newStatus, newDate]) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(
            route("documents.requests"),
            { search: newSearch, status: newStatus, date: newDate },
            { preserveState: true, replace: true },
        );
    }, 500);
});

// Sign Modal
const showSignModal = ref(false);
const activeDoc = ref<any>(null);
const openSignModal = (req: any) => {
    activeDoc.value = req.document;
    showSignModal.value = true;
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "short",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

const showRejectModal = ref(false);
const requestToReject = ref<any>(null);

const openRejectModal = (req: any) => {
    requestToReject.value = req;
    showRejectModal.value = true;
};

// Batch
const selectedDocs = ref<number[]>([]);
const showBatchModal = ref(false);

const toggleSelectAll = (e: Event) => {
    const checked = (e.target as HTMLInputElement).checked;
    if (checked) {
        selectedDocs.value = props.incomingRequests.data
            .filter((req: any) => req.status === "pending")
            .map((req: any) => req.document.id);
    } else {
        selectedDocs.value = [];
    }
};

const statusMeta = (s: string) =>
    ({
        pending: {
            label: "Menunggu TTE",
            cls: "bg-amber-50 text-amber-700 border-amber-200",
            dot: "bg-amber-500",
        },
        signed: {
            label: "Selesai di-TTE",
            cls: "bg-[#EAF7F3] text-[#0B7563] border-[#BCE5DA]",
            dot: "bg-[#0B8570]",
        },
        rejected: {
            label: "Ditolak",
            cls: "bg-rose-50 text-rose-700 border-rose-200",
            dot: "bg-rose-500",
        },
    })[s] || {
        label: s || "—",
        cls: "bg-slate-50 text-slate-700 border-slate-200",
        dot: "bg-slate-400",
    };
</script>

<template>
    <Head title="Permintaan TTE Masuk" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('documents.index')"
                    class="grid h-9 w-9 shrink-0 place-items-center rounded-lg border border-[#E9F0EE] text-[#6A8593] transition-colors hover:bg-[#F2FBF8] hover:text-[#0B7563]"
                >
                    <ArrowLeft class="h-4 w-4" />
                </Link>
                <div>
                    <p
                        class="text-[11px] font-bold uppercase tracking-[0.16em] text-[#0B7563]"
                    >
                        Kotak Masuk
                    </p>
                    <h2
                        class="font-display mt-0.5 flex items-center gap-2 text-xl font-bold leading-tight text-[#0F2A2E]"
                    >
                        Permintaan TTE
                    </h2>
                </div>
            </div>
        </template>

        <div
            class="mx-auto w-full max-w-8xl space-y-5 px-5 py-8 text-[#0F2A2E] md:px-8"
        >
            <!-- Batch action bar -->
            <Transition
                enter-active-class="ease-out duration-200"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="ease-in duration-150"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2"
            >
                <div
                    v-if="selectedDocs.length > 0"
                    class="flex flex-col items-start gap-3 rounded-2xl border border-[#BCE5DA] bg-gradient-to-r from-[#EAF7F3] to-[#F2FBF8] p-4 shadow-[0_18px_40px_-32px_rgba(11,133,112,.4)] sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex items-center gap-3">
                        <span
                            class="grid h-9 w-9 place-items-center rounded-lg bg-[#0B8570] text-white"
                        >
                            <Layers class="h-4 w-4" />
                        </span>
                        <div>
                            <p class="text-sm font-bold text-[#0F2A2E]">
                                {{ selectedDocs.length }} dokumen terpilih
                            </p>
                            <p class="text-[11px] text-[#6A8593]">
                                Siap untuk ditandatangani massal (Invisible)
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="showBatchModal = true"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#0B8570] px-4 py-2.5 text-sm font-semibold text-white shadow-[0_18px_40px_-24px_rgba(11,133,112,.8)] transition-all hover:-translate-y-0.5 hover:bg-[#086B5B] sm:w-auto"
                    >
                        <PenLine class="h-4 w-4" />
                        TTE Massal Sekarang
                    </button>
                </div>
            </Transition>

            <!-- Filter bar -->
            <div
                class="rounded-2xl border border-[#DCE8E5] bg-white p-4 shadow-[0_18px_40px_-32px_rgba(15,42,46,.35)]"
            >
                <div class="flex flex-col gap-3 lg:flex-row">
                    <!-- Search -->
                    <div class="relative flex-1">
                        <Search
                            class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#6A8593]"
                        />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari nama dokumen atau peminta..."
                            class="w-full rounded-xl border border-[#D9E6E3] bg-[#F8FCFB] py-2.5 pl-9 pr-3 text-sm text-[#0F2A2E] placeholder:text-[#A8BCC2] transition-colors focus:border-[#0B8570] focus:outline-none focus:ring-0"
                        />
                    </div>

                    <!-- Date -->
                    <div class="relative lg:w-52">
                        <Calendar
                            class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#6A8593]"
                        />
                        <select
                            v-model="dateFilter"
                            class="w-full appearance-none rounded-xl border border-[#D9E6E3] bg-[#F8FCFB] py-2.5 pl-9 pr-8 text-sm text-[#0F2A2E] transition-colors focus:border-[#0B8570] focus:outline-none focus:ring-0"
                        >
                            <option value="">Semua Waktu</option>
                            <option value="today">Hari Ini</option>
                            <option value="week">Minggu Ini</option>
                            <option value="month">Bulan Ini</option>
                        </select>
                        <ChevronDown
                            class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#6A8593]"
                        />
                    </div>

                    <!-- Status -->
                    <div class="relative lg:w-52">
                        <Filter
                            class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#6A8593]"
                        />
                        <select
                            v-model="statusFilter"
                            class="w-full appearance-none rounded-xl border border-[#D9E6E3] bg-[#F8FCFB] py-2.5 pl-9 pr-8 text-sm text-[#0F2A2E] transition-colors focus:border-[#0B8570] focus:outline-none focus:ring-0"
                        >
                            <option value="all">Semua Status</option>
                            <option value="pending">Menunggu TTE</option>
                            <option value="signed">Sudah di-TTE</option>
                            <option value="rejected">Ditolak</option>
                        </select>
                        <ChevronDown
                            class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#6A8593]"
                        />
                    </div>
                </div>
            </div>

            <!-- Table card -->
            <div
                class="overflow-hidden rounded-2xl border border-[#DCE8E5] bg-white shadow-[0_18px_40px_-32px_rgba(15,42,46,.35)]"
            >
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-[#E9F0EE] bg-[#F8FCFB]">
                                <th class="w-12 px-6 py-3 text-left">
                                    <input
                                        type="checkbox"
                                        @change="toggleSelectAll"
                                        class="h-4 w-4 cursor-pointer rounded border-[#D9E6E3] text-[#0B8570] focus:ring-2 focus:ring-[#0B8570]/20"
                                    />
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                                >
                                    Dokumen
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                                >
                                    Diminta Oleh
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                                >
                                    Waktu
                                </th>
                                <th
                                    class="px-6 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                                >
                                    Status
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                                >
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#EEF4F2]">
                            <!-- Empty -->
                            <tr v-if="incomingRequests.data.length === 0">
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <div
                                        class="flex flex-col items-center gap-3"
                                    >
                                        <div
                                            class="grid h-14 w-14 place-items-center rounded-2xl bg-[#EAF7F3]"
                                        >
                                            <Inbox
                                                class="h-7 w-7 text-[#0B7563]"
                                            />
                                        </div>
                                        <div>
                                            <p
                                                class="font-display text-sm font-bold text-[#0F2A2E]"
                                            >
                                                Tidak ada permintaan ditemukan
                                            </p>
                                            <p
                                                class="mt-1 text-xs text-[#6A8593]"
                                            >
                                                Sesuaikan filter atau kata kunci
                                                pencarian Anda.
                                            </p>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <!-- Rows -->
                            <tr
                                v-for="req in incomingRequests.data"
                                :key="req.id"
                                :class="
                                    selectedDocs.includes(req.document.id)
                                        ? 'bg-[#F2FBF8]'
                                        : ''
                                "
                                class="transition-colors hover:bg-[#F8FCFB]"
                            >
                                <td class="px-6 py-4 align-top">
                                    <input
                                        v-if="req.status === 'pending'"
                                        type="checkbox"
                                        :value="req.document.id"
                                        v-model="selectedDocs"
                                        class="h-4 w-4 cursor-pointer rounded border-[#D9E6E3] text-[#0B8570] focus:ring-2 focus:ring-[#0B8570]/20"
                                    />
                                </td>
                                <td class="px-6 py-4 align-top">
                                    <div class="flex items-start gap-3">
                                        <span
                                            class="mt-0.5 grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-[#EAF7F3] text-[#0B7563]"
                                        >
                                            <FileText class="h-4 w-4" />
                                        </span>
                                        <div class="min-w-0">
                                            <p
                                                class="truncate text-sm font-semibold text-[#0F2A2E]"
                                                :title="req.document.title"
                                            >
                                                {{ req.document.title }}
                                            </p>
                                            <p
                                                class="mt-0.5 text-[11px] text-[#6A8593]"
                                            >
                                                Pemilik:
                                                <span
                                                    class="font-medium text-[#4A6572]"
                                                >
                                                    {{
                                                        req.document.owner.name
                                                    }}
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td
                                    class="px-6 py-4 align-top text-sm font-medium text-[#4A6572]"
                                >
                                    {{ req.requester.name }}
                                </td>
                                <td
                                    class="whitespace-nowrap px-6 py-4 align-top text-xs text-[#6A8593]"
                                >
                                    {{ formatDate(req.created_at) }}
                                </td>
                                <td
                                    class="whitespace-nowrap px-6 py-4 text-center align-top"
                                >
                                    <span
                                        :class="statusMeta(req.status).cls"
                                        class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[11px] font-semibold"
                                    >
                                        <span
                                            class="h-1.5 w-1.5 rounded-full"
                                            :class="statusMeta(req.status).dot"
                                        />
                                        {{ statusMeta(req.status).label }}
                                    </span>
                                </td>
                                <td
                                    class="whitespace-nowrap px-6 py-4 text-right align-top"
                                >
                                    <div
                                        class="flex items-center justify-end gap-1"
                                    >
                                        <Link
                                            :href="
                                                route(
                                                    'documents.show',
                                                    req.document.id,
                                                )
                                            "
                                            class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-[#4A6572] transition-colors hover:bg-[#EEF4F2] hover:text-[#0F2A2E]"
                                        >
                                            <ExternalLink class="h-3.5 w-3.5" />
                                            Detail
                                        </Link>

                                        <template
                                            v-if="req.status === 'pending'"
                                        >
                                            <button
                                                type="button"
                                                @click="openRejectModal(req)"
                                                class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-2.5 py-1.5 text-xs font-semibold text-rose-700 transition-colors hover:bg-rose-100"
                                            >
                                                <XCircle class="h-3.5 w-3.5" />
                                                Tolak
                                            </button>
                                            <button
                                                type="button"
                                                @click="openSignModal(req)"
                                                class="inline-flex items-center gap-1 rounded-lg bg-[#0B8570] px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm transition-all hover:-translate-y-0.5 hover:bg-[#086B5B]"
                                            >
                                                <PenLine class="h-3.5 w-3.5" />
                                                TTE Sekarang
                                            </button>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div
                v-if="
                    incomingRequests.links && incomingRequests.links.length > 3
                "
                class="flex justify-center pt-2"
            >
                <div
                    class="inline-flex items-center gap-1 rounded-xl border border-[#DCE8E5] bg-white p-1 shadow-[0_18px_40px_-32px_rgba(15,42,46,.35)]"
                >
                    <template
                        v-for="(link, k) in incomingRequests.links"
                        :key="k"
                    >
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

        <!-- TTE Modal -->
        <TteProcessModal
            v-if="activeDoc"
            v-model:show="showSignModal"
            :document="activeDoc"
            :specimen-path="auth.user.specimen_path"
        />

        <!-- Reject Modal -->
        <RejectSignModal
            v-model:show="showRejectModal"
            :request="requestToReject"
            @success="requestToReject = null"
        />

        <!-- Batch Sign Modal -->
        <BatchSignModal
            v-model:show="showBatchModal"
            :document-ids="selectedDocs"
            @success="selectedDocs = []"
        />
    </AuthenticatedLayout>
</template>
