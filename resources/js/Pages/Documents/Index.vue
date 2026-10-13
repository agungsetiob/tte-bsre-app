<script setup lang="ts">
import { Head, useForm, usePage, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed, watch } from 'vue';
import TteProcessModal from '@/Components/TteProcessModal.vue';
import { FileText, XCircle, CheckCircle, Clock } from 'lucide-vue-next';

const props = defineProps<{
    documents: {
        data: Array<any>;
        links: Array<any>;
    };
    filters: {
        search?: string;
        status?: string;
    };
    auth: {
        user: {
            name: string;
            specimen_path: string | null;
        };
    };
}>();

const page = usePage();
const flashSuccess = computed(() => (page.props.flash as any)?.success);

// State Filter
const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');

// Modals
const showUploadModal = ref(false);
const showSignModal = ref(false);
const activeDoc = ref<any>(null);

const assetUrl = (path: string) => '/storage/' + path;

// Form Murni Upload
const uploadForm = useForm({
    title: '',
    file: null as File | null,
});

const submitUpload = () => {
    uploadForm.post(route('documents.store'), {
        preserveScroll: true,
        onSuccess: () => { showUploadModal.value = false; uploadForm.reset(); }
    });
};

const openSignModal = (doc: any) => {
    activeDoc.value = doc;
    showSignModal.value = true;
};

// Auto-trigger pencarian saat user mengetik (dengan jeda 500ms)
let searchTimeout: any = null;
watch([search, statusFilter], ([newSearch, newStatus]) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('documents.index'), {
            search: newSearch,
            status: newStatus
        }, { preserveState: true, replace: true });
    }, 500);
});
</script>

<template>
    <Head title="Manajemen Dokumen" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-slate-800 leading-tight">Manajemen Dokumen</h2>
                <div class="space-x-3">
                    <Link :href="route('documents.requests')" class="bg-white text-blue-600 border border-blue-600 px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-50">
                        Lihat Permintaan TTE
                    </Link>
                    <button @click="showUploadModal = true; uploadForm.reset();" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 shadow-sm">
                        + Unggah Dokumen
                    </button>
                </div>
            </div>
        </template>

        <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div v-if="flashSuccess" class="p-4 bg-green-50 text-green-700 border border-green-200 shadow-sm rounded-lg font-medium">{{ flashSuccess }}</div>
            
            <!-- FILTER & SEARCH BAR -->
            <div class="flex flex-col sm:flex-row gap-4 justify-between bg-white p-4 rounded-xl shadow-sm border border-slate-200">
                <div class="w-full sm:w-1/3">
                    <input type="text" v-model="search" placeholder="Cari judul dokumen..." class="w-full rounded-lg border-slate-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="w-full sm:w-auto">
                    <select v-model="statusFilter" class="w-full sm:w-64 rounded-lg border-slate-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Status</option>
                        <option value="pending">Menunggu TTE</option>
                        <option value="forwarded">Diteruskan</option>
                        <option value="signed">Selesai di-TTE</option>
                        <option value="rejected">Ditolak / Dikembalikan</option>
                    </select>
                </div>
            </div>

            <!-- TABEL DOKUMEN -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-slate-200 pb-4">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Judul</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Informasi</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            
                            <!-- STATE JIKA DATA KOSONG -->
                            <tr v-if="documents.data.length === 0">
                                <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <FileText class="w-12 h-12 text-slate-300 mb-3" />
                                        <p class="font-medium text-slate-600">Tidak ada dokumen ditemukan</p>
                                        <p class="text-sm mt-1">Coba sesuaikan kata kunci pencarian atau filter status Anda.</p>
                                    </div>
                                </td>
                            </tr>

                            <!-- LOOPING DOKUMEN -->
                            <tr v-for="doc in documents.data" :key="doc.id" class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 text-sm font-medium text-slate-900">{{ doc.title }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex flex-col items-start gap-1.5">
                                        <span 
                                            :class="{
                                                'bg-green-100 text-green-800 border-green-200': doc.status === 'signed',
                                                'bg-red-100 text-red-800 border-red-200': doc.status === 'rejected',
                                                'bg-amber-100 text-amber-800 border-amber-200': doc.status === 'forwarded',
                                                'bg-yellow-100 text-yellow-800 border-yellow-200': doc.status === 'pending'
                                            }" 
                                            class="px-2.5 py-1 text-xs font-bold rounded-md border"
                                        >
                                            {{ 
                                                doc.status === 'signed' ? 'Selesai di-TTE' : 
                                                (doc.status === 'rejected' ? 'Ditolak / Dikembalikan' : 
                                                (doc.status === 'forwarded' ? 'Diteruskan' : 'Menunggu TTE')) 
                                            }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex flex-col items-start gap-1.5">
                                        <div v-if="doc.signers && doc.signers.length > 0" class="flex flex-col gap-1.5 mt-1">
                                            <template v-for="signer in doc.signers" :key="signer.id">
                                                <div v-if="signer.status === 'pending'" class="inline-flex items-center gap-1 bg-slate-100 text-slate-600 px-2 py-1 rounded border border-slate-200 text-[10px] font-semibold whitespace-nowrap">
                                                    <Clock class="w-3.5 h-3.5 text-slate-400" />
                                                    Menunggu: {{ signer.signer?.name || signer.user?.name }}
                                                </div>
                                                <div v-else-if="signer.status === 'rejected'" class="inline-flex items-center gap-1 bg-red-50 text-red-600 px-2 py-1 rounded border border-red-100 text-[10px] font-semibold whitespace-nowrap">
                                                    <XCircle class="w-3.5 h-3.5" />
                                                    Ditolak oleh: {{ signer.signer?.name || signer.user?.name }}
                                                </div>
                                                <div v-else-if="signer.status === 'signed'" class="inline-flex items-center gap-1 bg-green-50 text-green-700 px-2 py-1 rounded border border-green-100 text-[10px] font-semibold whitespace-nowrap">
                                                    <CheckCircle class="w-3.5 h-3.5" />
                                                    Selesai: {{ signer.signer?.name || signer.user?.name }}
                                                </div>
                                            </template>
                                        </div>
                                        <div v-else class="text-xs text-slate-400 italic">Belum ada penandatangan</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium space-x-3">
                                    <Link :href="route('documents.show', doc.id)" class="text-slate-600 hover:text-slate-900 transition-colors">Detail</Link>
                                    <button @click="openSignModal(doc)" class="text-blue-600 hover:text-blue-900 font-bold transition-colors">Proses TTE</button>
                                    <a :href="route('documents.download', doc.id)" target="_blank" class="text-slate-600 hover:text-slate-900 transition-colors">Unduh</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION LINKS -->
                <div v-if="documents.links && documents.links.length > 3" class="mt-6 flex justify-center">
                    <div class="flex flex-wrap gap-1">
                        <template v-for="(link, k) in documents.links" :key="k">
                            <div v-if="link.url === null" class="px-3 py-1.5 text-sm text-slate-400 border border-slate-200 rounded-lg bg-slate-50" v-html="link.label"></div>
                            <Link v-else :href="link.url" :class="link.active ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-50'" class="px-3 py-1.5 text-sm border rounded-lg transition-colors shadow-sm" v-html="link.label" preserve-scroll />
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL UPLOAD MURNI -->
        <div v-if="showUploadModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
            <div class="bg-white rounded-xl p-6 w-full max-w-md shadow-2xl">
                <h3 class="text-lg font-bold text-slate-900 mb-4">Unggah Dokumen Baru</h3>
                <form @submit.prevent="submitUpload" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Judul Dokumen</label>
                        <input type="text" v-model="uploadForm.title" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">File PDF</label>
                        <input type="file" accept="application/pdf" @change="(e: any) => uploadForm.file = e.target.files[0]" required class="mt-1 block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                    </div>
                    <div class="flex justify-end gap-2 pt-4">
                        <button type="button" @click="showUploadModal = false" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-200">Batal</button>
                        <button type="submit" :disabled="uploadForm.processing" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50 transition-colors">
                            {{ uploadForm.processing ? 'Mengunggah...' : 'Unggah Dokumen' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <TteProcessModal v-if="activeDoc" v-model:show="showSignModal" :document="activeDoc"
            :specimen-path="auth.user.specimen_path" />
    </AuthenticatedLayout>
</template>