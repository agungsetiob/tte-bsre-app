<script setup lang="ts">
import axios from 'axios';
import type { Component } from 'vue';
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed } from 'vue';
import TteProcessModal from '@/Components/TteProcessModal.vue';
import Modal from '@/Components/Modal.vue';
import {
    ArrowLeft,
    FileText,
    UserPlus,
    CheckCircle2,
    Clock4,
    Send,
    ShieldAlert,
    PenLine,
    XCircle,
    ExternalLink,
    UploadCloud,
    Search,
    UserCheck,
    X,
    AlertCircle,
    Info,
    SendHorizonal,
    SendHorizontal,
    Loader,
} from 'lucide-vue-next';

const props = defineProps<{
    document: any;
}>();

const page = usePage();
const authUser = computed(() => (page.props.auth as any).user);

const searchQuery = ref('');
const searchResults = ref<Array<any>>([]);
const isSearching = ref(false);
const showDropdown = ref(false);

const searchUsers = async () => {
    if (searchQuery.value.length < 2) {
        searchResults.value = [];
        showDropdown.value = false;
        return;
    }

    isSearching.value = true;
    try {
        const res = await axios.get(route('api.users.search'), {
            params: { q: searchQuery.value },
        });
        searchResults.value = res.data;
        showDropdown.value = true;
    } catch (error) {
        console.error(error);
    } finally {
        isSearching.value = false;
    }
};

const selectUser = (user: any) => {
    requestForm.user_id = user.id;
    searchQuery.value = `${user.name} (${user.nik})`;
    showDropdown.value = false;
};

const assetUrl = (path: string) => '/storage/' + path;
const pdfUrl = computed(() =>
    assetUrl(props.document.signed_file_path || props.document.file_path)
);

// 1. FORM PERMINTAAN TTE
const requestForm = useForm({
    user_id: '',
});

const submitRequest = () => {
    requestForm.post(route('documents.requestSign', props.document.id), {
        preserveScroll: true,
        onSuccess: () => {
            requestForm.reset('user_id');
            searchQuery.value = '';
        },
    });
};

// 2. TTE MODAL
const showSignModal = ref(false);

const formatDate = (dateString: string) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const hasPendingRequest = computed(() => {
    return props.document.signers.some(
        (signer: any) => signer.status === 'pending'
    );
});

// 3. REVISI
const showReviseModal = ref(false);
const reviseForm = useForm({
    file: null as File | null,
});

const submitRevise = () => {
    reviseForm.post(route('documents.revise', props.document.id), {
        preserveScroll: true,
        onSuccess: () => {
            showReviseModal.value = false;
            reviseForm.reset();
        },
    });
};

const cancelSignRequest = (signerId: number) => {
    if (confirm('Apakah Anda yakin ingin menarik kembali permintaan TTE ini?')) {
        router.delete(
            route('documents.cancelRequest', {
                document: props.document.id,
                signer: signerId,
            }),
            { preserveScroll: true }
        );
    }
};

type StatusMeta = {
    label: string;
    cls: string;
    dot: string;
};

const statusMap: Record<string, StatusMeta> = {
    signed: {
        label: 'Selesai di-TTE',
        cls: 'bg-[#EAF7F3] text-[#0B7563] border-[#BCE5DA]',
        dot: 'bg-[#0B8570]',
    },
    rejected: {
        label: 'Ditolak',
        cls: 'bg-rose-50 text-rose-700 border-rose-200',
        dot: 'bg-rose-500',
    },
    pending: {
        label: 'Menunggu / Proses',
        cls: 'bg-amber-50 text-amber-700 border-amber-200',
        dot: 'bg-amber-500',
    },
    forwarded: {
        label: 'Diteruskan',
        cls: 'bg-sky-50 text-sky-700 border-sky-200',
        dot: 'bg-sky-500',
    },
};

const fallbackStatus: StatusMeta = {
    label: '—',
    cls: 'bg-slate-50 text-slate-700 border-slate-200',
    dot: 'bg-slate-400',
};

const documentStatus = computed<StatusMeta>(() => {
    const s = String(props.document.status ?? '');
    return statusMap[s] ?? { ...fallbackStatus, label: s || '—' };
});

type SignerStatusMeta = StatusMeta & {
    ring: string;
    Icon: Component;
};

const signerStatusMap: Record<string, SignerStatusMeta> = {
    signed: {
        label: 'Selesai',
        cls: 'bg-[#EAF7F3] text-[#0B7563] border-[#BCE5DA]',
        dot: 'bg-[#0B8570]',
        ring: 'border-[#0B8570] text-[#0B7563] bg-[#EAF7F3]',
        Icon: CheckCircle2,
    },
    rejected: {
        label: 'Ditolak',
        cls: 'bg-rose-50 text-rose-700 border-rose-200',
        dot: 'bg-rose-500',
        ring: 'border-rose-500 text-rose-600 bg-rose-50',
        Icon: XCircle,
    },
    pending: {
        label: 'Menunggu',
        cls: 'bg-amber-50 text-amber-700 border-amber-200',
        dot: 'bg-amber-500',
        ring: 'border-amber-400 text-amber-600 bg-amber-50',
        Icon: Clock4,
    },
    forwarded: {
        label: 'Menunggu',
        cls: 'bg-amber-50 text-amber-700 border-amber-200',
        dot: 'bg-amber-500',
        ring: 'border-amber-400 text-amber-600 bg-amber-50',
        Icon: Clock4,
    },
};

const fallbackSigner: SignerStatusMeta = {
    label: '—',
    cls: 'bg-slate-50 text-slate-700 border-slate-200',
    dot: 'bg-slate-400',
    ring: 'border-slate-300 text-slate-500 bg-slate-50',
    Icon: Info,
};

const signerStatusMeta = (s: string): SignerStatusMeta => {
    const key = String(s ?? '');
    return signerStatusMap[key] ?? { ...fallbackSigner, label: key || '—' };
};
</script>

<template>
    <Head :title="'Detail Dokumen - ' + document.title" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <div class="flex min-w-0 items-center gap-3">
                    <Link
                        :href="route('documents.index')"
                        class="grid h-9 w-9 shrink-0 place-items-center rounded-lg border border-[#E9F0EE] text-[#6A8593] transition-colors hover:bg-[#F2FBF8] hover:text-[#0B7563]"
                    >
                        <ArrowLeft class="h-4 w-4" />
                    </Link>
                    <div class="min-w-0">
                        <p
                            class="text-[11px] font-bold uppercase tracking-[0.16em] text-[#0B7563]"
                        >
                            Detail Dokumen
                        </p>
                        <h2
                            class="font-display mt-0.5 truncate text-xl font-bold leading-tight text-[#0F2A2E]"
                            :title="document.title"
                        >
                            {{ document.title }}
                        </h2>
                    </div>
                </div>
                <button
                    type="button"
                    @click="showSignModal = true"
                    class="group inline-flex shrink-0 items-center gap-2 rounded-xl bg-[#0B8570] px-4 py-2.5 text-sm font-semibold text-white shadow-[0_18px_40px_-24px_rgba(11,133,112,.8)] transition-all hover:-translate-y-0.5 hover:bg-[#086B5B]"
                >
                    <PenLine class="h-4 w-4" />
                    <span class="hidden sm:inline">Proses TTE</span>
                </button>
            </div>
        </template>

        <div
            class="mx-auto w-full max-w-8xl space-y-5 px-5 py-8 text-[#0F2A2E] md:px-8"
        >

            <!-- Main grid -->
            <div
                class="flex flex-col gap-5 lg:h-[calc(100vh-13rem)] lg:flex-row"
            >
                <!-- ============ PDF VIEWER ============ -->
                <div
                    class="flex w-full flex-col overflow-hidden rounded-2xl border border-[#DCE8E5] bg-white shadow-[0_18px_40px_-32px_rgba(15,42,46,.35)] lg:w-2/3"
                >
                    <div
                        class="flex shrink-0 items-center justify-between gap-3 border-b border-[#E9F0EE] bg-[#F8FCFB] px-4 py-2.5"
                    >
                        <span
                            class="flex items-center gap-2 text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                        >
                            <FileText class="h-4 w-4 text-[#0B7563]" />
                            Pratinjau Dokumen
                        </span>
                        <a
                            :href="
                                assetUrl(
                                    document.signed_file_path || document.file_path
                                )
                            "
                            target="_blank"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-[#D9E6E3] bg-white px-2.5 py-1.5 text-xs font-semibold text-[#0B7563] transition-colors hover:border-[#BCE5DA] hover:bg-[#F2FBF8]"
                        >
                            <ExternalLink class="h-3.5 w-3.5" />
                            Buka Penuh
                        </a>
                    </div>
                    <iframe
                        :src="pdfUrl"
                        class="min-h-[400px] w-full flex-1 border-none bg-[#EEF4F2]"
                        title="PDF Viewer"
                    />
                </div>

                <!-- ============ SIDEBAR ============ -->
                <div
                    class="flex w-full flex-col gap-4 lg:w-1/3 lg:overflow-y-auto lg:pr-1"
                >
                    <!-- Rejected alert -->
                    <div
                        v-if="document.status === 'rejected'"
                        class="rounded-2xl border border-rose-200 bg-rose-50 p-4 shadow-[0_18px_40px_-32px_rgba(15,42,46,.35)]"
                    >
                        <div class="flex items-start gap-3">
                            <span
                                class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-white text-rose-600"
                            >
                                <ShieldAlert class="h-4 w-4" />
                            </span>
                            <div class="min-w-0">
                                <p
                                    class="font-display text-sm font-bold text-rose-700"
                                >
                                    Dokumen Dikembalikan
                                </p>
                                <p
                                    class="mt-1 text-xs leading-relaxed text-rose-600"
                                >
                                    Dokumen ditolak oleh salah satu penandatangan.
                                    Periksa alasannya di riwayat bawah, lalu unggah
                                    file perbaikan.
                                </p>
                            </div>
                        </div>
                        <button
                            v-if="document.owner.id === authUser.id"
                            type="button"
                            @click="showReviseModal = true"
                            class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white shadow-[0_18px_40px_-24px_rgba(225,29,72,.55)] transition-all hover:-translate-y-0.5 hover:bg-rose-700"
                        >
                            <UploadCloud class="h-4 w-4" />
                            Unggah File Revisi
                        </button>
                    </div>

                    <!-- 1. Info Dokumen -->
                    <div
                        class="rounded-2xl border border-[#DCE8E5] bg-white p-5 shadow-[0_18px_40px_-32px_rgba(15,42,46,.35)]"
                    >
                        <h3
                            class="text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                        >
                            Informasi Dokumen
                        </h3>
                        <div class="mt-4 space-y-4">
                            <div>
                                <p class="text-[11px] text-[#6A8593]">
                                    Pemilik (Pengunggah)
                                </p>
                                <p
                                    class="mt-0.5 text-sm font-semibold text-[#0F2A2E]"
                                >
                                    {{ document.owner.name }}
                                </p>
                            </div>
                            <div>
                                <p class="text-[11px] text-[#6A8593]">
                                    Diunggah Pada
                                </p>
                                <p
                                    class="mt-0.5 text-sm font-semibold text-[#0F2A2E]"
                                >
                                    {{ formatDate(document.created_at) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-[11px] text-[#6A8593]">
                                    Status Saat Ini
                                </p>
                                <span
                                    :class="documentStatus.cls"
                                    class="mt-1.5 inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[11px] font-semibold"
                                >
                                    <span
                                        class="h-1.5 w-1.5 rounded-full"
                                        :class="documentStatus.dot"
                                    />
                                    {{ documentStatus.label }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Form Minta TTE -->
                    <div
                        class="rounded-2xl border border-[#BCE5DA] bg-gradient-to-b from-[#F8FCFB] to-[#EAF7F3]/60 p-5 shadow-[0_18px_40px_-32px_rgba(15,42,46,.35)]"
                    >
                        <div class="flex items-center gap-2">
                            <span
                                class="grid h-8 w-8 place-items-center rounded-lg bg-[#0B8570] text-white"
                            >
                                <UserPlus class="h-4 w-4" />
                            </span>
                            <div>
                                <h3
                                    class="font-display text-sm font-bold text-[#0F2A2E]"
                                >
                                    Minta Tanda Tangan
                                </h3>
                                <p class="text-[11px] text-[#6A8593]">
                                    Cari pegawai untuk ditambahkan sebagai penandatangan
                                </p>
                            </div>
                        </div>

                        <!-- Pending alert -->
                        <div
                            v-if="hasPendingRequest"
                            class="mt-4 flex items-start gap-2.5 rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs text-amber-800"
                        >
                            <Clock4 class="mt-0.5 h-4 w-4 shrink-0 text-amber-600" />
                            <span class="font-medium leading-relaxed">
                                Tindakan ditangguhkan. Masih ada permintaan tanda tangan
                                yang belum diselesaikan.
                            </span>
                        </div>

                        <!-- Form -->
                        <form
                            v-else
                            @submit.prevent="submitRequest"
                            class="mt-4 space-y-3"
                        >
                            <div class="relative">
                                <div class="relative">
                                    <Search
                                        class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#6A8593]"
                                    />
                                    <input
                                        v-model="searchQuery"
                                        type="text"
                                        @input="searchUsers"
                                        placeholder="Ketik nama atau NIK pegawai..."
                                        class="w-full rounded-xl border border-[#D9E6E3] bg-white py-2.5 pl-9 pr-9 text-sm text-[#0F2A2E] placeholder:text-[#A8BCC2] transition-colors focus:border-[#0B8570] focus:outline-none focus:ring-4 focus:ring-[#0B8570]/10"
                                    />
                                    <div
                                        v-if="isSearching"
                                        class="absolute right-3 top-1/2 -translate-y-1/2"
                                    >
                                        <div
                                            class="h-4 w-4 animate-spin rounded-full border-2 border-[#0B8570] border-t-transparent"
                                        />
                                    </div>
                                </div>

                                <!-- Dropdown -->
                                <ul
                                    v-if="showDropdown && searchResults.length > 0"
                                    class="absolute z-20 mt-1 max-h-60 w-full overflow-y-auto rounded-xl border border-[#DCE8E5] bg-white py-1 shadow-[0_20px_50px_-25px_rgba(15,42,46,.35)]"
                                >
                                    <li
                                        v-for="user in searchResults"
                                        :key="user.id"
                                        @click="selectUser(user)"
                                        class="cursor-pointer border-b border-[#EEF4F2] px-3.5 py-2.5 transition-colors last:border-0 hover:bg-[#F2FBF8]"
                                    >
                                        <p
                                            class="text-sm font-semibold text-[#0F2A2E]"
                                        >
                                            {{ user.name }}
                                        </p>
                                        <p class="text-[11px] text-[#6A8593]">
                                            NIK: {{ user.nik }}
                                        </p>
                                    </li>
                                </ul>

                                <div
                                    v-else-if="
                                        showDropdown &&
                                        searchResults.length === 0
                                    "
                                    class="absolute z-20 mt-1 w-full rounded-xl border border-[#DCE8E5] bg-white p-3 text-center text-xs text-[#6A8593] shadow-[0_20px_50px_-25px_rgba(15,42,46,.35)]"
                                >
                                    Pegawai tidak ditemukan.
                                </div>
                            </div>

                            <p
                                v-if="requestForm.errors.user_id"
                                class="text-xs text-rose-600"
                            >
                                {{ requestForm.errors.user_id }}
                            </p>

                            <button
                                type="submit"
                                :disabled="
                                    requestForm.processing ||
                                    !requestForm.user_id
                                "
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#0B8570] px-4 py-2.5 text-sm font-semibold text-white shadow-[0_18px_40px_-24px_rgba(11,133,112,.8)] transition-all hover:-translate-y-0.5 hover:bg-[#086B5B] disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:translate-y-0"
                            >
                                <Loader v-if="requestForm.processing" class="h-4 w-4 animate-spin" />
                                {{
                                    requestForm.processing
                                        ? 'Mengirim…'
                                        : 'Kirim Permintaan'
                                }}
                                <SendHorizontal class="h-4 w-4" />
                            </button>
                        </form>
                    </div>

                    <!-- 3. Riwayat Signers -->
                    <div
                        class="rounded-2xl border border-[#DCE8E5] bg-white p-5 shadow-[0_18px_40px_-32px_rgba(15,42,46,.35)]"
                    >
                        <div class="mb-4 flex items-center gap-2">
                            <span
                                class="grid h-8 w-8 place-items-center rounded-lg bg-[#EEF4F2] text-[#4A6572]"
                            >
                                <UserCheck class="h-4 w-4" />
                            </span>
                            <h3
                                class="font-display text-sm font-bold text-[#0F2A2E]"
                            >
                                Riwayat Penandatangan
                            </h3>
                        </div>

                        <div
                            v-if="document.signers.length === 0"
                            class="rounded-xl border border-dashed border-[#D9E6E3] bg-[#F8FCFB] p-6 text-center text-xs text-[#6A8593]"
                        >
                            Belum ada permintaan TTE pada dokumen ini.
                        </div>

                        <div v-else class="space-y-4">
                            <div
                                v-for="(signerInfo, index) in document.signers"
                                :key="signerInfo.id"
                                class="relative flex gap-3"
                            >
                                <!-- connector line -->
                                <div
                                    v-if="
                                        index !== document.signers.length - 1
                                    "
                                    class="absolute bottom-[-16px] left-[15px] top-9 w-px bg-[#D9E6E3]"
                                />

                                <!-- icon node -->
                                <div
                                    class="relative z-10 grid h-8 w-8 shrink-0 place-items-center rounded-full border-2 bg-white"
                                    :class="
                                        signerStatusMeta(signerInfo.status).ring
                                    "
                                >
                                    <component
                                        :is="
                                            signerStatusMeta(signerInfo.status)
                                                .Icon
                                        "
                                        class="h-3.5 w-3.5"
                                    />
                                </div>

                                <div class="min-w-0 flex-1 pb-1">
                                    <div
                                        class="flex items-start justify-between gap-2"
                                    >
                                        <div class="min-w-0">
                                            <p
                                                class="truncate text-sm font-semibold text-[#0F2A2E]"
                                                :class="{
                                                    'line-through opacity-60':
                                                        signerInfo.status ===
                                                        'rejected',
                                                }"
                                            >
                                                {{ signerInfo.signer.name }}
                                            </p>
                                            <p
                                                class="mt-0.5 text-[11px] text-[#6A8593]"
                                            >
                                                Diminta oleh:
                                                <span
                                                    class="font-medium text-[#4A6572]"
                                                >
                                                    {{
                                                        signerInfo.requester
                                                            .name
                                                    }}
                                                </span>
                                            </p>
                                        </div>
                                    </div>

                                    <div
                                        class="mt-2 flex flex-wrap items-center gap-1.5"
                                    >
                                        <span
                                            :class="
                                                signerStatusMeta(
                                                    signerInfo.status
                                                ).cls
                                            "
                                            class="inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider"
                                        >
                                            {{
                                                signerStatusMeta(
                                                    signerInfo.status
                                                ).label
                                            }}
                                        </span>

                                        <button
                                            v-if="
                                                signerInfo.status ===
                                                    'pending' &&
                                                (signerInfo.requester_id ===
                                                    authUser.id ||
                                                    document.owner.id ===
                                                        authUser.id)
                                            "
                                            type="button"
                                            @click="
                                                cancelSignRequest(signerInfo.id)
                                            "
                                            class="inline-flex items-center gap-1 rounded-full border border-[#E9F0EE] bg-white px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-[#6A8593] transition-colors hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600"
                                        >
                                            <X class="h-3 w-3" />
                                            Batalkan
                                        </button>
                                    </div>

                                    <div
                                        v-if="
                                            signerInfo.status === 'rejected' &&
                                            signerInfo.reject_reason
                                        "
                                        class="mt-2 rounded-xl border border-rose-100 bg-rose-50 p-2.5 text-xs text-rose-700"
                                    >
                                        <p
                                            class="text-[10px] font-bold uppercase tracking-wider text-rose-600"
                                        >
                                            Catatan
                                        </p>
                                        <p class="mt-0.5 leading-relaxed">
                                            {{ signerInfo.reject_reason }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TTE Modal -->
        <TteProcessModal
            v-if="document"
            v-model:show="showSignModal"
            :document="document"
            :specimen-path="authUser.specimen_path"
        />

        <!-- Revise Modal -->
        <Modal
            :show="showReviseModal"
            max-width="md"
            @close="showReviseModal = false"
        >
            <div class="p-6">
                <div class="flex items-start gap-3">
                    <span
                        class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-rose-50 text-rose-600"
                    >
                        <UploadCloud class="h-5 w-5" />
                    </span>
                    <div class="min-w-0">
                        <h3 class="font-display text-lg font-bold text-[#0F2A2E]">
                            Unggah Perbaikan Dokumen
                        </h3>
                        <p class="mt-0.5 truncate text-xs text-[#6A8593]">
                            {{ document.title }}
                        </p>
                    </div>
                </div>

                <div
                    class="mt-5 flex items-start gap-2.5 rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs text-amber-800"
                >
                    <AlertCircle class="mt-0.5 h-4 w-4 shrink-0 text-amber-600" />
                    <p class="leading-relaxed">
                        Mengunggah file baru akan <strong>me-reset</strong> status
                        dokumen ke <strong>Menunggu</strong> dan
                        <strong>menghapus seluruh</strong> riwayat TTE sebelumnya.
                    </p>
                </div>

                <form @submit.prevent="submitRevise" class="mt-5 space-y-4">
                    <div>
                        <label
                            class="block text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                        >
                            File PDF Revisi
                        </label>
                        <input
                            type="file"
                            accept="application/pdf"
                            @change="
                                (e: any) => (reviseForm.file = e.target.files[0])
                            "
                            required
                            class="mt-1.5 block w-full rounded-xl border border-[#D9E6E3] bg-[#F8FCFB] px-3 py-2.5 text-sm text-[#4A6572] file:mr-3 file:rounded-lg file:border-0 file:bg-[#0B8570] file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-[#086B5B]"
                        />
                        <p
                            v-if="reviseForm.errors.file"
                            class="mt-1 text-xs text-rose-600"
                        >
                            {{ reviseForm.errors.file }}
                        </p>
                    </div>

                    <div
                        class="flex justify-end gap-2 border-t border-[#EEF4F2] pt-4"
                    >
                        <button
                            type="button"
                            @click="showReviseModal = false"
                            class="rounded-xl border border-[#D9E6E3] bg-white px-4 py-2 text-sm font-semibold text-[#4A6572] transition-colors hover:bg-[#F2FBF8]"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="reviseForm.processing || !reviseForm.file"
                            class="rounded-xl bg-[#0B8570] px-4 py-2 text-sm font-semibold text-white shadow-[0_18px_40px_-24px_rgba(11,133,112,.8)] transition-all hover:-translate-y-0.5 hover:bg-[#086B5B] disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:translate-y-0"
                        >
                            {{
                                reviseForm.processing
                                    ? 'Mengunggah…'
                                    : 'Unggah Revisi'
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>