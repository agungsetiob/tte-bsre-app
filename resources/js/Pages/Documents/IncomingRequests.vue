<script setup lang="ts">
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed, watch } from 'vue';
import TteProcessModal from '@/Components/TteProcessModal.vue';
import { ArrowLeft, Inbox, PenTool, CheckCircle2, FileText, XCircle } from 'lucide-vue-next';

const props = defineProps<{
    incomingRequests: {
        data: Array<any>;
        links: Array<any>;
    };
    filters: {
        search?: string;
        status?: string;
        date?: string;
    };
    auth: { user: { name: string; specimen_path: string | null } };
}>();

const page = usePage();
const flashSuccess = computed(() => (page.props.flash as any)?.success);
const errorMessage = computed(() => (page.props.errors as any)?.message);

// STATE FILTER & SEARCH
const search = ref(props.filters.search || '');
// Default ke 'pending' jika filter belum ada, agar behave seperti Inbox murni di awal
const statusFilter = ref(props.filters.status ?? 'pending'); 
const dateFilter = ref(props.filters.date || '');

// Watcher untuk Filter dengan Debounce
let searchTimeout: any = null;
watch([search, statusFilter, dateFilter], ([newSearch, newStatus, newDate]) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('documents.requests'), {
            search: newSearch,
            status: newStatus,
            date: newDate
        }, { preserveState: true, replace: true });
    }, 500);
});

const showSignModal = ref(false);
const activeDoc = ref(null);
const openSignModal = (req: any) => {
    activeDoc.value = req.document;
    showSignModal.value = true;
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
};

const showRejectModal = ref(false);
const requestToReject = ref<any>(null);
const rejectForm = useForm({ reason: '' });

const openRejectModal = (req: any) => {
    requestToReject.value = req;
    rejectForm.reset();
    showRejectModal.value = true;
};

const submitReject = () => {
    rejectForm.post(route('documents.rejectSign', requestToReject.value.document.id), {
        preserveScroll: true,
        onSuccess: () => { showRejectModal.value = false; }
    });
};

const selectedDocs = ref<number[]>([]);
const showBatchModal = ref(false);
const batchForm = useForm({ document_ids: [] as number[], passphrase: '' });

// Fitur Select All - HANYA MEMILIH DOKUMEN YANG STATUSNYA PENDING
const toggleSelectAll = (e: Event) => {
    const checked = (e.target as HTMLInputElement).checked;
    if (checked) {
        selectedDocs.value = props.incomingRequests.data
            .filter((req: any) => req.status === 'pending')
            .map((req: any) => req.document.id);
    } else {
        selectedDocs.value = [];
    }
};

const submitBatchSign = () => {
    batchForm.document_ids = selectedDocs.value;
    batchForm.post(route('documents.batch_sign'), {
        preserveScroll: true,
        onSuccess: (page) => {
            if (!page.props.errors || Object.keys(page.props.errors).length === 0) {
                showBatchModal.value = false;
                selectedDocs.value = []; 
                batchForm.reset();
            }
        }
    });
};

const getStatusBadge = (status: string) => {
    switch (status) {
        case 'pending': return 'bg-yellow-100 text-yellow-800 border-yellow-200';
        case 'signed': return 'bg-green-100 text-green-800 border-green-200';
        case 'rejected': return 'bg-red-100 text-red-800 border-red-200';
        default: return 'bg-slate-100 text-slate-600 border-slate-200';
    }
};

const getStatusLabel = (status: string) => {
    switch (status) {
        case 'pending': return 'Menunggu TTE';
        case 'signed': return 'Selesai di-TTE';
        case 'rejected': return 'Ditolak';
        default: return status;
    }
};
</script>

<template>
    <Head title="Permintaan TTE Masuk" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('documents.index')" class="p-2 text-slate-500 hover:text-slate-900 hover:bg-slate-200 rounded-lg transition-colors">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <h2 class="font-semibold text-xl text-slate-800 leading-tight flex items-center gap-2">
                    <Inbox class="w-5 h-5 text-blue-600" />
                    Permintaan TTE Masuk
                </h2>
            </div>
        </template>

        <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div v-if="flashSuccess" class="p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl flex items-center gap-2 shadow-sm font-medium">
                <CheckCircle2 class="w-5 h-5" />
                {{ flashSuccess }}
            </div>
            
            <div v-if="selectedDocs.length > 0" class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-xl flex items-center justify-between shadow-sm">
                <span class="text-blue-900 font-semibold text-sm">{{ selectedDocs.length }} dokumen terpilih</span>
                <button @click="showBatchModal = true" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow transition-colors">
                    TTE Massal Sekarang
                </button>
            </div>

            <!-- FILTER & SEARCH BAR -->
            <div class="flex flex-col sm:flex-row gap-4 justify-between bg-white p-4 rounded-xl shadow-sm border border-slate-100">
                <div class="w-full sm:w-1/3">
                    <input type="text" v-model="search" placeholder="Cari nama dokumen atau peminta..." class="w-full rounded-lg border-slate-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="flex gap-2 w-full sm:w-auto">
                    <select v-model="dateFilter" class="rounded-lg border-slate-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Waktu</option>
                        <option value="today">Hari Ini</option>
                        <option value="week">Minggu Ini</option>
                        <option value="month">Bulan Ini</option>
                    </select>

                    <select v-model="statusFilter" class="rounded-lg border-slate-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="all">Semua Status</option>
                        <option value="pending">Menunggu TTE</option>
                        <option value="signed">Sudah di-TTE</option>
                        <option value="rejected">Ditolak</option>
                    </select>
                </div>
            </div>

            <!-- Tabel Permintaan -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-slate-200 pb-4">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left w-12">
                                    <input type="checkbox" @change="toggleSelectAll" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">Dokumen</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">Diminta Oleh</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">Waktu Permintaan</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-slate-500 uppercase">Status</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-slate-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            <tr v-if="incomingRequests.data.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                    <Inbox class="w-12 h-12 text-slate-300 mx-auto mb-3" />
                                    <p class="text-sm font-medium">Tidak ada dokumen yang ditemukan.</p>
                                    <p class="text-xs mt-1">Coba sesuaikan filter atau pencarian Anda.</p>
                                </td>
                            </tr>
                            <tr v-for="req in incomingRequests.data" :key="req.id" class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <!-- Hanya tampilkan checkbox jika status masih pending -->
                                    <input v-if="req.status === 'pending'" type="checkbox" :value="req.document.id" v-model="selectedDocs" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 bg-blue-50 text-blue-600 rounded-lg">
                                            <FileText class="w-5 h-5" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-900">{{ req.document.title }}</p>
                                            <p class="text-xs text-slate-500">Pemilik: {{ req.document.owner.name }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-slate-700">
                                    {{ req.requester.name }}
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500">
                                    {{ formatDate(req.created_at) }}
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <span :class="getStatusBadge(req.status)" class="px-2.5 py-1 text-xs font-bold rounded-md border inline-flex items-center gap-1">
                                        {{ getStatusLabel(req.status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <Link :href="route('documents.show', req.document.id)" class="text-slate-600 hover:text-blue-600 text-sm font-medium">
                                        Detail
                                    </Link>
                                    
                                    <!-- Tombol TTE & Tolak hanya muncul jika status masih pending -->
                                    <template v-if="req.status === 'pending'">
                                        <button @click="openRejectModal(req)" class="inline-flex items-center gap-1 bg-red-50 text-red-600 px-3 py-1.5 rounded-lg text-sm font-semibold hover:bg-red-100 border border-red-200 transition-colors">
                                            Tolak
                                        </button>
                                        <button @click="openSignModal(req)" class="inline-flex items-center gap-1 bg-blue-600 text-white px-3 py-1.5 rounded-lg text-sm font-semibold hover:bg-blue-700 shadow-sm transition-colors">
                                            <PenTool class="w-4 h-4" /> TTE Sekarang
                                        </button>
                                    </template>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION -->
                <div v-if="incomingRequests.links && incomingRequests.links.length > 3" class="mt-6 flex justify-center">
                    <div class="flex flex-wrap gap-1">
                        <template v-for="(link, k) in incomingRequests.links" :key="k">
                            <div v-if="link.url === null" class="px-3 py-1.5 text-sm text-slate-400 border border-slate-200 rounded-lg bg-slate-50" v-html="link.label"></div>
                            <Link v-else :href="link.url" :class="link.active ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-50'" class="px-3 py-1.5 text-sm border rounded-lg transition-colors shadow-sm" v-html="link.label" preserve-scroll />
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <TteProcessModal v-if="activeDoc" v-model:show="showSignModal" :document="activeDoc" :specimen-path="auth.user.specimen_path" />

        <!-- MODAL TOLAK TTE -->
        <div v-if="showRejectModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
            <div class="bg-white rounded-xl p-6 w-full max-w-md shadow-2xl">
                <h3 class="text-lg font-bold text-red-600 mb-2">Tolak Permintaan TTE</h3>
                <p class="text-sm text-slate-500 mb-4">
                    Anda akan mengembalikan dokumen <strong>{{ requestToReject?.document.title }}</strong>. Silakan berikan alasan penolakan agar staf dapat memperbaikinya.
                </p>
                <form @submit.prevent="submitReject" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Alasan Penolakan</label>
                        <textarea v-model="rejectForm.reason" required rows="3" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:ring-red-500 focus:border-red-500 text-sm" placeholder="Contoh: Ada salah ketik pada nominal angka..."></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="showRejectModal = false" class="px-4 py-2 bg-slate-100 rounded-lg text-sm font-medium hover:bg-slate-200">Batal</button>
                        <button type="submit" :disabled="rejectForm.processing" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition-colors">Kirim Penolakan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL BATCH SIGN -->
        <div v-if="showBatchModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
            <div class="bg-white rounded-xl p-6 w-full max-w-md shadow-2xl">
                <h3 class="text-lg font-bold text-slate-900 mb-2">TTE Massal (Invisible)</h3>
                <p class="text-sm text-slate-500 mb-4">Anda akan menandatangani <strong>{{ selectedDocs.length }} dokumen</strong> sekaligus. Mode yang digunakan adalah Invisible (Tanpa visual spesimen pada PDF).</p>
                <div v-if="errorMessage" class="p-3 mb-4 text-sm text-red-700 bg-red-100 rounded-lg border border-red-200">{{ errorMessage }}</div>
                <div v-if="Object.keys(batchForm.errors).length > 0" class="p-3 mb-4 text-sm text-red-700 bg-red-100 rounded-lg border border-red-200">
                    <ul class="list-disc pl-4">
                        <li v-for="(error, key) in batchForm.errors" :key="key">{{ error }}</li>
                    </ul>
                </div>

                <form @submit.prevent="submitBatchSign" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium">Passphrase BSrE</label>
                        <input type="password" v-model="batchForm.passphrase" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm text-sm" placeholder="Masukkan Passphrase" />
                    </div>
                    <div class="flex justify-end gap-2 pt-4 border-t border-slate-100">
                        <button type="button" @click="showBatchModal = false" class="px-4 py-2 bg-slate-100 rounded-lg text-sm font-medium">Batal</button>
                        <button type="submit" :disabled="batchForm.processing" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50">
                            {{ batchForm.processing ? 'Memproses...' : 'Proses Semua' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>