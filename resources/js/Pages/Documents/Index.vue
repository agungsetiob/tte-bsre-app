<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, watch } from 'vue';
import TteProcessModal from '@/Components/TteProcessModal.vue';
import DocumentUploadModal from '@/Components/DocumentUploadModal.vue';
import DocumentEditModal from '@/Components/DocumentEditModal.vue';
import DocumentDeleteModal from '@/Components/DocumentDeleteModal.vue';
import {
    FileText, XCircle, CheckCircle2, Clock4, Search, Plus, 
    Inbox, ExternalLink, Download, PenLine, Filter, ChevronDown, Trash2,
} from 'lucide-vue-next';

const props = defineProps<{
    documents: { data: Array<any>; links: Array<any> };
    filters: { search?: string; status?: string };
    auth: { user: { name: string; specimen_path: string | null } };
}>();

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');

// State Modals
const showUploadModal = ref(false);
const showSignModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);

const activeDoc = ref<any>(null);
const documentToEdit = ref<any>(null);
const documentToDelete = ref<any>(null);

const openSignModal = (doc: any) => { activeDoc.value = doc; showSignModal.value = true; };
const openEditModal = (doc: any) => { documentToEdit.value = doc; showEditModal.value = true; };
const openDeleteModal = (doc: any) => { documentToDelete.value = doc; showDeleteModal.value = true; };

let searchTimeout: any = null;
watch([search, statusFilter], ([newSearch, newStatus]) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(
            route('documents.index'),
            { search: newSearch, status: newStatus },
            { preserveState: true, replace: true }
        );
    }, 500);
});

const statusMeta = (s: string) =>
    ({
        pending: { label: 'Menunggu TTE', cls: 'bg-amber-50 text-amber-700 border-amber-200', dot: 'bg-amber-500' },
        forwarded: { label: 'Diteruskan', cls: 'bg-sky-50 text-sky-700 border-sky-200', dot: 'bg-sky-500' },
        signed: { label: 'Selesai di-TTE', cls: 'bg-[#EAF7F3] text-[#0B7563] border-[#BCE5DA]', dot: 'bg-[#0B8570]' },
        rejected: { label: 'Ditolak / Dikembalikan', cls: 'bg-rose-50 text-rose-700 border-rose-200', dot: 'bg-rose-500' },
    }[s] || { label: s || '—', cls: 'bg-slate-50 text-slate-700 border-slate-200', dot: 'bg-slate-400' });
</script>

<template>
    <Head title="Manajemen Dokumen" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="font-display text-xl font-bold leading-tight text-[#0F2A2E]">
                        Manajemen Dokumen
                    </h2>
                    <p class="mt-1 text-xs text-[#6A8593]">
                        Kelola, tanda tangani, dan pantau dokumen rumah sakit dalam satu dasbor.
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Link
                        :href="route('documents.requests')"
                        class="inline-flex items-center gap-2 rounded-xl border border-[#B9D3CD] bg-white px-4 py-2 text-sm font-semibold text-[#0F2A2E] transition-all hover:border-[#0B8570] hover:bg-[#F2FBF8]"
                    >
                        <Inbox class="h-4 w-4 text-[#0B7563]" />
                        Permintaan TTE
                    </Link>
                    <button
                        type="button"
                        @click="showUploadModal = true"
                        class="group inline-flex items-center gap-2 rounded-xl bg-[#0B8570] px-4 py-2 text-sm font-semibold text-white shadow-[0_18px_40px_-24px_rgba(11,133,112,.8)] transition-all hover:-translate-y-0.5 hover:bg-[#086B5B]"
                    >
                        <Plus class="h-4 w-4" />
                        Unggah Dokumen
                    </button>
                </div>
            </div>
        </template>

        <div class="mx-auto w-full max-w-8xl space-y-5 px-5 py-8 text-[#0F2A2E] md:px-8">

            <div class="rounded-2xl border border-[#DCE8E5] bg-white p-4 shadow-[0_18px_40px_-32px_rgba(15,42,46,.35)]">
                <div class="flex flex-col gap-3 sm:flex-row">
                    <div class="relative flex-1">
                        <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#6A8593]" />
                        <input v-model="search" type="text" placeholder="Cari judul dokumen..." class="w-full rounded-xl border border-[#D9E6E3] bg-[#F8FCFB] py-2.5 pl-9 pr-3 text-sm text-[#0F2A2E] placeholder:text-[#A8BCC2] transition-colors focus:border-[#0B8570] focus:outline-none focus:ring-0" />
                    </div>
                    <div class="relative sm:w-64">
                        <Filter class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#6A8593]" />
                        <select v-model="statusFilter" class="w-full appearance-none rounded-xl border border-[#D9E6E3] bg-[#F8FCFB] py-2.5 pl-9 pr-8 text-sm text-[#0F2A2E] transition-colors focus:border-[#0B8570] focus:outline-none focus:ring-0">
                            <option value="">Semua Status</option>
                            <option value="pending">Menunggu TTE</option>
                            <option value="forwarded">Diteruskan</option>
                            <option value="signed">Selesai di-TTE</option>
                            <option value="rejected">Ditolak / Dikembalikan</option>
                        </select>
                        <ChevronDown class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#6A8593]" />
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-[#DCE8E5] bg-white shadow-[0_18px_40px_-32px_rgba(15,42,46,.35)]">
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-[#F8FCFB] text-[#0F2A2E]">
                            <tr class="border-b border-[#E9F0EE]">
                                <th class="px-6 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-[#6A8593]">Judul</th>
                                <th class="px-6 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-[#6A8593]">Status</th>
                                <th class="px-6 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-[#6A8593]">Informasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#EEF4F2]">
                            <tr v-if="documents.data.length === 0">
                                <td colspan="3" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="grid h-14 w-14 place-items-center rounded-2xl bg-[#EAF7F3]">
                                            <FileText class="h-7 w-7 text-[#0B7563]" />
                                        </div>
                                        <div>
                                            <p class="font-display text-sm font-bold text-[#0F2A2E]">Tidak ada dokumen ditemukan</p>
                                            <p class="mt-1 text-xs text-[#6A8593]">Sesuaikan kata kunci pencarian atau filter status Anda.</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <tr v-for="doc in documents.data" :key="doc.id" class="transition-colors hover:bg-[#F8FCFB]">
                                <td class="px-6 py-4 align-top">
                                    <div class="flex max-w-xs items-start gap-3">
                                        <span class="mt-0.5 grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-[#EAF7F3] text-[#0B7563]">
                                            <FileText class="h-4 w-4" />
                                        </span>
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold text-[#0F2A2E]" :title="doc.title">{{ doc.title }}</p>
                                            <p class="mt-0.5 text-[11px] text-[#6A8593]">#{{ doc.id }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 align-top">
                                    <span :class="statusMeta(doc.status).cls" class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full border px-2.5 py-1 text-[11px] font-semibold">
                                        <span class="h-1.5 w-1.5 rounded-full" :class="statusMeta(doc.status).dot" />
                                        {{ statusMeta(doc.status).label }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 align-top">
                                    <div v-if="doc.signers && doc.signers.length" class="flex flex-col gap-1.5">
                                        <template v-for="signer in doc.signers" :key="signer.id">
                                            <div v-if="signer.status === 'pending'" class="inline-flex w-fit items-center gap-1.5 rounded-md border border-[#E9F0EE] bg-[#F8FCFB] px-2 py-1 text-[11px] font-medium text-[#4A6572]">
                                                <Clock4 class="h-3 w-3 text-[#6A8593]" /> Menunggu: <span class="font-semibold text-[#0F2A2E]">{{ signer.signer?.name || signer.user?.name }}</span>
                                            </div>
                                            <div v-else-if="signer.status === 'rejected'" class="inline-flex w-fit items-center gap-1.5 rounded-md border border-rose-200 bg-rose-50 px-2 py-1 text-[11px] font-medium text-rose-700">
                                                <XCircle class="h-3 w-3" /> Ditolak: <span class="font-semibold">{{ signer.signer?.name || signer.user?.name }}</span>
                                            </div>
                                            <div v-else-if="signer.status === 'signed'" class="inline-flex w-fit items-center gap-1.5 rounded-md border border-[#BCE5DA] bg-[#EAF7F3] px-2 py-1 text-[11px] font-medium text-[#0B7563]">
                                                <CheckCircle2 class="h-3 w-3" /> Selesai: <span class="font-semibold">{{ signer.signer?.name || signer.user?.name }}</span>
                                            </div>
                                        </template>
                                    </div>
                                    <span v-else class="text-xs italic text-[#A8BCC2]">Belum ada penandatangan</span>
                                </td>

                                <td class="px-6 py-4 align-top text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <a :href="route('documents.download', doc.id)" target="_blank" class="inline-flex items-center gap-1 rounded-lg bg-sky-600 px-2.5 py-1.5 text-xs font-semibold text-white transition-all hover:bg-sky-700 hover:-translate-y-0.5">
                                            <Download class="h-3.5 w-3.5" /> Unduh
                                        </a>
                                        <button type="button" @click="openSignModal(doc)" class="inline-flex items-center gap-1 rounded-lg bg-[#0B8570] px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm transition-all hover:-translate-y-0.5 hover:bg-[#086B5B]">
                                            <PenLine class="h-3.5 w-3.5" /> Proses TTE
                                        </button>
                                        <button type="button" @click="openEditModal(doc)" class="inline-flex items-center gap-1 rounded-lg bg-[#F2FBF8] px-2.5 py-1.5 text-xs font-semibold text-[#0B7563] shadow-sm transition-all hover:-translate-y-0.5 hover:bg-[#D9F0E9]">
                                            <PenLine class="h-3.5 w-3.5" /> Edit
                                        </button>
                                        <button v-if="doc.status === 'pending'" type="button" @click="openDeleteModal(doc)" class="inline-flex items-center gap-1 rounded-lg bg-rose-50 px-2.5 py-1.5 text-xs font-semibold text-rose-700 shadow-sm transition-all hover:-translate-y-0.5 hover:bg-rose-100">
                                            <Trash2 class="h-3.5 w-3.5" /> Hapus
                                        </button>
                                        <Link :href="route('documents.show', doc.id)" class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-[#4A6572] transition-all hover:bg-[#EEF4F2] hover:text-[#0F2A2E] hover:-translate-y-0.5">
                                            <ExternalLink class="h-3.5 w-3.5" /> Detail
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="documents.links && documents.links.length > 3" class="px-6 py-4 border-t border-[#EEF4F2] bg-[#F8FCFB] justify-center flex">
                    <div class="inline-flex items-center gap-1 rounded-xl border border-[#DCE8E5] bg-white p-1">
                        <template v-for="(link, k) in documents.links" :key="k">
                            <div v-if="link.url === null" class="cursor-not-allowed rounded-lg px-3 py-1.5 text-xs font-semibold text-[#A8BCC2]" v-html="link.label" />
                            <Link v-else :href="link.url" preserve-scroll :class="link.active ? 'bg-[#0B8570] text-white' : 'text-[#4A6572] hover:bg-[#F2FBF8] hover:text-[#0B7563]'" class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-colors" v-html="link.label" />
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <DocumentUploadModal :show="showUploadModal" @close="showUploadModal = false" />
        <DocumentEditModal :show="showEditModal" :document="documentToEdit" @close="showEditModal = false" />
        <DocumentDeleteModal :show="showDeleteModal" :document="documentToDelete" @close="showDeleteModal = false" />
        
        <TteProcessModal v-if="activeDoc" v-model:show="showSignModal" :document="activeDoc" :specimen-path="auth.user.specimen_path" />
    </AuthenticatedLayout>
</template>