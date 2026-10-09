<script setup lang="ts">
import axios from 'axios';
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed } from 'vue';
import VuePdfEmbed from 'vue-pdf-embed';
import { useEventListener } from '@vueuse/core';
import { 
    ArrowLeft, FileText, UserPlus, CheckCircle2, Clock, 
    Send, ShieldAlert, PenTool, XCircle 
} from 'lucide-vue-next';

const props = defineProps<{
    document: any;
    activeUsers: Array<{ id: number; name: string; role: string }>;
}>();

const page = usePage();
const authUser = computed(() => (page.props.auth as any).user);
const flashSuccess = computed(() => (page.props.flash as any)?.success);
const errorMessage = computed(() => (page.props.errors as any)?.message);

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
        const res = await axios.get(route('api.users.search'), { params: { q: searchQuery.value } });
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
const pdfUrl = computed(() => assetUrl(props.document.signed_file_path || props.document.file_path));

// 1. STATE FORM PERMINTAAN TTE
const requestForm = useForm({
    user_id: '',
});

const submitRequest = () => {
    requestForm.post(route('documents.requestSign', props.document.id), {
        preserveScroll: true,
        onSuccess: () => {
            requestForm.reset('user_id');
            searchQuery.value = ''; // Kosongkan form pencarian
        },
    });
};

// 2. STATE MODAL TTE DARI HALAMAN DETAIL
const showSignModal = ref(false);
const signForm = useForm({
    passphrase: '',
    tampilan: 'INVISIBLE',
    page: 1,
    originX: 0,
    originY: 0,
    width: 150,
    height: 60,
});

const pdfContainer = ref<HTMLElement | null>(null);
const isDragging = ref(false);
const specX = ref(50);
const specY = ref(50);

useEventListener(window, 'mousemove', (e: MouseEvent) => {
    if (isDragging.value && pdfContainer.value) {
        const rect = pdfContainer.value.getBoundingClientRect();
        specX.value = Math.max(0, Math.min(e.clientX - rect.left - (signForm.width / 2), rect.width - signForm.width));
        specY.value = Math.max(0, Math.min(e.clientY - rect.top - (signForm.height / 2), rect.height - signForm.height));
    }
});
useEventListener(window, 'mouseup', () => { isDragging.value = false; });

const submitSign = () => {
    if (signForm.tampilan === 'VISIBLE' && pdfContainer.value) {
        const h = pdfContainer.value.getBoundingClientRect().height;
        signForm.originX = Math.round(specX.value);
        signForm.originY = Math.round(h - specY.value - signForm.height);
    }
    signForm.post(route('documents.sign', props.document.id), {
        preserveScroll: true,
        onSuccess: () => { 
            showSignModal.value = false;
        }
    });
};

const formatDate = (dateString: string) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
};

const hasPendingRequest = computed(() => {
    return props.document.signers.some((signer: any) => signer.status === 'pending');
});

const showReviseModal = ref(false);
const reviseForm = useForm({
    file: null as File | null
});

const submitRevise = () => {
    reviseForm.post(route('documents.revise', props.document.id), {
        preserveScroll: true,
        onSuccess: () => { 
            showReviseModal.value = false; 
            reviseForm.reset();
        }
    });
};

const cancelSignRequest = (signerId: number) => {
    if (confirm('Apakah Anda yakin ingin menarik kembali permintaan TTE ini?')) {
        router.delete(route('documents.cancelRequest', { document: props.document.id, signer: signerId }), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <Head :title="'Detail Dokumen - ' + document.title" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-4">
                    <Link :href="route('documents.index')" class="p-2 text-slate-500 hover:text-slate-900 hover:bg-slate-200 rounded-lg transition-colors">
                        <ArrowLeft class="w-5 h-5" />
                    </Link>
                    <h2 class="font-semibold text-xl text-slate-800 leading-tight truncate">
                        {{ document.title }}
                    </h2>
                </div>
                <!-- Tombol TTE Utama -->
                <button @click="showSignModal = true; signForm.reset();" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 shadow-sm flex items-center gap-2">
                    <PenTool class="w-4 h-4" /> Proses TTE
                </button>
            </div>
        </template>

        <!-- Pesan Notifikasi -->
        <div v-if="flashSuccess" class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl flex items-center gap-2 shadow-sm">
            <CheckCircle2 class="w-5 h-5" />
            <span class="font-medium">{{ flashSuccess }}</span>
        </div>
        <div v-if="errorMessage" class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center gap-2 shadow-sm">
            <ShieldAlert class="w-5 h-5" />
            <span class="font-medium">{{ errorMessage }}</span>
        </div>

        <div class="flex flex-col lg:flex-row gap-6 h-[calc(100vh-12rem)]">
            
            <!-- PANEL KIRI: PDF VIEWER -->
            <div class="w-full lg:w-2/3 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col relative">
                <div class="bg-slate-50 p-3 border-b border-slate-200 flex justify-between items-center text-slate-700 text-sm font-semibold">
                    <span class="flex items-center gap-2"><FileText class="w-4 h-4 text-blue-600" /> Pratinjau Dokumen</span>
                    <a :href="assetUrl(document.signed_file_path || document.file_path)" target="_blank" class="text-blue-600 hover:underline text-xs font-bold">Buka Penuh</a>
                </div>
                <iframe :src="pdfUrl" class="w-full flex-1 border-none bg-slate-100" title="PDF Viewer"></iframe>
            </div>

            <!-- PANEL KANAN: SIDEBAR MANAJEMEN -->
            <div class="w-full lg:w-1/3 flex flex-col gap-6 overflow-y-auto pr-2 pb-12">

                <div v-if="document.status === 'rejected'" class="bg-red-50 border border-red-200 p-5 rounded-2xl shadow-sm space-y-3">
                    <div class="flex items-center gap-2 text-red-700 font-bold">
                        <ShieldAlert class="w-5 h-5" /> 
                        <span>Dokumen Dikembalikan!</span>
                    </div>
                    <p class="text-xs text-red-600 font-medium">
                        Dokumen ini telah ditolak oleh salah satu penandatangan. Silakan baca alasannya di riwayat bawah, lalu unggah dokumen perbaikannya.
                    </p>
                    <button v-if="document.owner.id === authUser.id" @click="showReviseModal = true" class="w-full bg-red-600 text-white py-2 rounded-xl text-sm font-medium hover:bg-red-700 transition shadow-sm mt-2">
                        + Unggah File Revisi
                    </button>
                </div>
                
                <!-- 1. INFO DOKUMEN -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 space-y-3">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Informasi Dokumen</h3>
                    <div>
                        <p class="text-xs text-slate-500">Pemilik (Pengunggah)</p>
                        <p class="text-sm font-semibold text-slate-900">{{ document.owner.name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">Diunggah Pada</p>
                        <p class="text-sm font-semibold text-slate-900">{{ formatDate(document.created_at) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">Status Saat Ini</p>
                        <span class="inline-flex mt-1 px-2.5 py-1 rounded-md text-xs font-bold" 
                              :class="{
                                  'bg-green-50 text-green-700 border border-green-200': document.status === 'signed',
                                  'bg-red-50 text-red-700 border border-red-200': document.status === 'rejected',
                                  'bg-blue-50 text-blue-700 border border-blue-200': document.status === 'pending' || document.status === 'forwarded'
                              }">
                            {{ document.status === 'signed' ? 'Selesai di-TTE' : (document.status === 'rejected' ? 'Ditolak' : 'Menunggu / Proses') }}
                        </span>
                    </div>
                </div>

                <!-- 2. FORM TERUSKAN / MINTA TTE -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-blue-100 bg-gradient-to-b from-white to-blue-50/50">
                    <h3 class="text-sm font-bold text-blue-900 flex items-center gap-2 mb-4">
                        <UserPlus class="w-4 h-4 text-blue-600" />
                        Minta Tanda Tangan
                    </h3>
                    
                    <!-- Alert Jika Masih Ada Request Pending -->
                    <div v-if="hasPendingRequest" class="p-3 bg-yellow-50 text-yellow-700 rounded-lg text-xs font-medium border border-yellow-200 flex gap-2">
                        <Clock class="w-4 h-4 shrink-0 text-yellow-600" />
                        <span>Tindakan ditangguhkan. Masih ada permintaan tanda tangan yang belum diselesaikan (berstatus "Menunggu").</span>
                    </div>

                    <!-- Form Pengajuan (Hanya tampil jika tidak ada yang pending) -->
                    <form v-else @submit.prevent="submitRequest" class="space-y-3">
                        <div class="relative">
                            <div class="relative">
                                <input 
                                    type="text" 
                                    v-model="searchQuery" 
                                    @input="searchUsers" 
                                    placeholder="Ketik nama atau NIK pegawai..."
                                    class="w-full text-sm border-slate-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm"
                                />
                                <div v-if="isSearching" class="absolute right-3 top-2.5">
                                    <div class="w-4 h-4 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
                                </div>
                            </div>

                            <!-- Dropdown Hasil Pencarian -->
                            <ul v-if="showDropdown && searchResults.length > 0" class="absolute z-20 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-xl max-h-60 overflow-y-auto">
                                <li 
                                    v-for="user in searchResults" 
                                    :key="user.id" 
                                    @click="selectUser(user)"
                                    class="px-4 py-2 hover:bg-blue-50 cursor-pointer border-b border-slate-100 last:border-0"
                                >
                                    <p class="text-sm font-bold text-slate-900">{{ user.name }}</p>
                                    <p class="text-xs text-slate-500">NIK: {{ user.nik }}</p>
                                </li>
                            </ul>
                            
                            <div v-else-if="showDropdown && searchResults.length === 0" class="absolute z-20 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-xl p-3 text-center text-xs text-slate-500">
                                Pegawai tidak ditemukan.
                            </div>
                        </div>
                        <button type="submit" :disabled="requestForm.processing || !requestForm.user_id" class="w-full flex justify-center items-center gap-2 bg-blue-600 text-white py-2.5 rounded-xl text-sm font-medium hover:bg-blue-700 transition shadow-sm disabled:opacity-50">
                            <Send class="w-4 h-4" /> Kirim Permintaan
                        </button>
                    </form>
                </div>

                <!-- 3. RIWAYAT PERMINTAAN INTERNAL (Signers) -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Riwayat Penandatangan</h3>
                    <div v-if="document.signers.length === 0" class="text-center text-sm text-slate-500 py-4 bg-slate-50 rounded-lg border border-dashed border-slate-200">
                        Belum ada permintaan TTE pada dokumen ini.
                    </div>
                    <div v-else class="space-y-4">
                        <div v-for="(signerInfo, index) in document.signers" :key="signerInfo.id" class="flex gap-3 relative">
                            <div v-if="index !== document.signers.length - 1" class="absolute left-4 top-8 bottom-[-16px] w-0.5 bg-slate-200"></div>
                            <div class="relative z-10 w-8 h-8 rounded-full flex items-center justify-center shrink-0 border-2 bg-white"
                                 :class="{
                                     'border-green-500 text-green-500': signerInfo.status === 'signed',
                                     'border-red-500 text-red-500': signerInfo.status === 'rejected',
                                     'border-yellow-400 text-yellow-500': signerInfo.status === 'pending' || signerInfo.status === 'forwarded'
                                 }">
                                <CheckCircle2 v-if="signerInfo.status === 'signed'" class="w-4 h-4" />
                                <XCircle v-else-if="signerInfo.status === 'rejected'" class="w-4 h-4" />
                                <Clock v-else class="w-4 h-4" />
                            </div>
                            
                            <div class="flex-1 pb-1">
                                <p class="text-sm font-bold text-slate-900" :class="{'line-through opacity-70': signerInfo.status === 'rejected'}">
                                    {{ signerInfo.signer.name }}
                                </p>
                                <p class="text-xs text-slate-500">Oleh: {{ signerInfo.requester.name }}</p>
                                <div class="mt-1 flex items-center gap-2">
                                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded"
                                          :class="{
                                              'bg-green-100 text-green-700': signerInfo.status === 'signed',
                                              'bg-red-100 text-red-700': signerInfo.status === 'rejected',
                                              'bg-yellow-100 text-yellow-700': signerInfo.status === 'pending' || signerInfo.status === 'forwarded'
                                          }">
                                        {{ signerInfo.status === 'signed' ? 'Selesai' : (signerInfo.status === 'rejected' ? 'Ditolak' : 'Menunggu') }}
                                    </span>
                                </div>
                                <div v-if="signerInfo.status === 'rejected' && signerInfo.reject_reason" class="mt-2 bg-red-50 border border-red-100 p-2.5 rounded-lg text-xs text-red-700 relative">
                                    <div class="absolute -top-1.5 left-4 w-3 h-3 bg-red-50 border-t border-l border-red-100 transform rotate-45"></div>
                                    <strong>Catatan:</strong> {{ signerInfo.reject_reason }}
                                </div>
                            </div>
                            <div class="mt-1 flex items-center gap-2">
                                <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded"
                                    :class="{
                                        'bg-green-100 text-green-700': signerInfo.status === 'signed',
                                        'bg-red-100 text-red-700': signerInfo.status === 'rejected',
                                        'bg-yellow-100 text-yellow-700': signerInfo.status === 'pending' || signerInfo.status === 'forwarded'
                                    }">
                                    {{ signerInfo.status === 'signed' ? 'Selesai' : (signerInfo.status === 'rejected' ? 'Ditolak' : 'Menunggu') }}
                                </span>
                                    
                                    <!-- FITUR MITIGASI SALAH KIRIM -->
                                <button 
                                    v-if="signerInfo.status === 'pending' && (signerInfo.requester_id === authUser.id || document.owner.id === authUser.id)"
                                    @click="cancelSignRequest(signerInfo.id)"
                                    class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-slate-100 text-slate-500 hover:bg-red-100 hover:text-red-600 transition-colors border border-slate-200 hover:border-red-200"
                                >
                                    Batalkan
                                </button>
                                </div>
                        </div>
                        
                    </div>
                </div>

            </div>
        </div>

        <!-- MODAL PROSES TTE -->
        <div v-if="showSignModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 overflow-y-auto">
            <div class="bg-white rounded-xl p-6 w-full max-w-4xl shadow-2xl flex gap-6 max-h-[90vh] overflow-y-auto">
                <div :class="signForm.tampilan === 'VISIBLE' ? 'w-1/2' : 'w-full'" class="space-y-4">
                    <h3 class="text-lg font-bold text-slate-900">Proses TTE: {{ document.title }}</h3>
                    <form @submit.prevent="submitSign" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium">Passphrase BSrE</label>
                            <input type="password" v-model="signForm.passphrase" autocomplete="off" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Tampilan TTE</label>
                            <select v-model="signForm.tampilan" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm text-sm">
                                <option value="INVISIBLE">Invisible (Tanpa Visual)</option>
                                <option value="VISIBLE">Visible (Gunakan Spesimen)</option>
                            </select>
                        </div>
                        <div v-if="signForm.tampilan === 'VISIBLE'" class="space-y-3 border-t pt-3">
                            <div class="grid grid-cols-3 gap-2">
                                <div><label class="text-xs font-medium">Lebar</label><input type="number" v-model="signForm.width" class="w-full rounded border-slate-300 text-sm" /></div>
                                <div><label class="text-xs font-medium">Tinggi</label><input type="number" v-model="signForm.height" class="w-full rounded border-slate-300 text-sm" /></div>
                                <div><label class="text-xs font-medium">Halaman</label><input type="number" v-model="signForm.page" min="1" class="w-full rounded border-slate-300 text-sm" /></div>
                            </div>
                        </div>
                        <div class="flex justify-end gap-2 pt-4">
                            <button type="button" @click="showSignModal = false" class="px-4 py-2 bg-slate-100 rounded-lg text-sm font-medium">Batal</button>
                            <button type="submit" :disabled="signForm.processing" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Proses TTE</button>
                        </div>
                    </form>
                </div>
                
                <div v-if="signForm.tampilan === 'VISIBLE'" class="w-1/2 bg-slate-100 p-2 rounded-xl flex justify-center overflow-auto relative items-start border border-slate-200">
                    <div class="relative border bg-white shadow-md" ref="pdfContainer" style="width: fit-content;">
                        <VuePdfEmbed :source="assetUrl(document.signed_file_path || document.file_path)" :page="signForm.page" />
                        <div v-if="authUser.specimen_path" class="absolute border-2 border-blue-500 bg-blue-100/40 cursor-move"
                            :style="{ left: specX + 'px', top: specY + 'px', width: signForm.width + 'px', height: signForm.height + 'px' }"
                            @mousedown.prevent="isDragging = true">
                            <img :src="assetUrl(authUser.specimen_path)" class="w-full h-full object-contain pointer-events-none" />
                        </div>
                        <div v-else class="absolute inset-0 flex items-center justify-center bg-white/80 p-4 text-center text-red-600 text-xs font-semibold">
                            Anda belum mengunggah spesimen tanda tangan di profil.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL UPLOAD REVISI -->
        <div v-if="showReviseModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
            <div class="bg-white rounded-xl p-6 w-full max-w-md shadow-2xl">
                <h3 class="text-lg font-bold text-slate-900 mb-2">Unggah Perbaikan Dokumen</h3>
                <p class="text-xs text-slate-500 mb-4">
                    Mengunggah dokumen baru akan me-reset status dokumen ke <strong>Menunggu</strong> dan <strong>MENGHAPUS SEMUA</strong> riwayat TTE sebelumnya pada dokumen ini.
                </p>
                <form @submit.prevent="submitRevise" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium">Pilih File PDF Revisi</label>
                        <input type="file" accept="application/pdf" @change="(e: any) => reviseForm.file = e.target.files[0]" required class="mt-1 block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer border border-slate-200" />
                    </div>
                    <div class="flex justify-end gap-2 pt-4 border-t border-slate-100">
                        <button type="button" @click="showReviseModal = false" class="px-4 py-2 bg-slate-100 rounded-lg text-sm font-medium">Batal</button>
                        <button type="submit" :disabled="reviseForm.processing || !reviseForm.file" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50">Upload Revisi</button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>