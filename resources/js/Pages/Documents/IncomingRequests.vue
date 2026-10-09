<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed } from 'vue';
import VuePdfEmbed from 'vue-pdf-embed';
import { useEventListener } from '@vueuse/core';
import { ArrowLeft, Inbox, PenTool, CheckCircle2, FileText } from 'lucide-vue-next';

defineProps<{
    incomingRequests: Array<any>;
    auth: { user: { name: string; specimen_path: string | null } };
}>();

const page = usePage();
const flashSuccess = computed(() => (page.props.flash as any)?.success);
const errorMessage = computed(() => (page.props.errors as any)?.message);

const assetUrl = (path: string) => '/storage/' + path;

// Modal TTE States
const showSignModal = ref(false);
const activeDoc = ref<any>(null);

const signForm = useForm({
    passphrase: '',
    tampilan: 'INVISIBLE',
    page: 1,
    originX: 0,
    originY: 0,
    width: 150,
    height: 60,
});

// Drag State untuk Spesimen
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

const openSignModal = (req: any) => {
    // Mengekstrak objek dokumen dari data request
    activeDoc.value = req.document;
    signForm.reset();
    showSignModal.value = true;
};

const submitSign = () => {
    if (signForm.tampilan === 'VISIBLE' && pdfContainer.value) {
        const h = pdfContainer.value.getBoundingClientRect().height;
        signForm.originX = Math.round(specX.value);
        signForm.originY = Math.round(h - specY.value - signForm.height);
    }
    signForm.post(route('documents.sign', activeDoc.value.id), {
        preserveScroll: true,
        onSuccess: () => { showSignModal.value = false; }
    });
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
};

const showRejectModal = ref(false);
const requestToReject = ref<any>(null);
const rejectForm = useForm({
    reason: ''
});

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
            <!-- Notifikasi -->
            <div v-if="flashSuccess" class="p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl flex items-center gap-2 shadow-sm">
                <CheckCircle2 class="w-5 h-5" />
                <span class="font-medium">{{ flashSuccess }}</span>
            </div>

            <!-- Tabel Permintaan -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">Dokumen</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">Diminta Oleh</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">Waktu Permintaan</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-slate-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <tr v-for="req in incomingRequests" :key="req.id" class="hover:bg-slate-50 transition-colors">
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
                            <td class="px-6 py-4 text-right space-x-3">
                                <Link :href="route('documents.show', req.document.id)" class="text-slate-600 hover:text-slate-900 text-sm font-medium">
                                    Detail
                                </Link>
                                <button @click="openRejectModal(req)" class="inline-flex items-center gap-1 bg-red-50 text-red-600 px-3 py-1.5 rounded-lg text-sm font-semibold hover:bg-red-100 border border-red-200 transition-colors">
                                    Tolak
                                </button>
                                <button @click="openSignModal(req)" class="inline-flex items-center gap-1 bg-blue-600 text-white px-3 py-1.5 rounded-lg text-sm font-semibold hover:bg-blue-700 shadow-sm transition-colors">
                                    <PenTool class="w-4 h-4" />
                                    TTE Sekarang
                                </button>
                            </td>
                        </tr>
                        <tr v-if="incomingRequests.length === 0">
                            <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                <Inbox class="w-12 h-12 text-slate-300 mx-auto mb-3" />
                                <p class="text-sm font-medium">Tidak ada permintaan tanda tangan yang masuk.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- MODAL PROSES TTE -->
        <div v-if="showSignModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 overflow-y-auto">
            <div class="bg-white rounded-xl p-6 w-full max-w-4xl shadow-2xl flex gap-6 max-h-[90vh] overflow-y-auto">
                
                <!-- Panel Form -->
                <div :class="signForm.tampilan === 'VISIBLE' ? 'w-1/2' : 'w-full'" class="space-y-4">
                    <h3 class="text-lg font-bold text-slate-900">Penandatanganan: {{ activeDoc?.title }}</h3>
                    
                    <div v-if="errorMessage" class="p-3 text-sm text-red-700 bg-red-100 rounded-lg border border-red-200">
                        {{ errorMessage }}
                    </div>
                    
                    <form @submit.prevent="submitSign" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium">Passphrase BSrE</label>
                            <input type="password" v-model="signForm.passphrase" autocomplete="off" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Tampilan TTE</label>
                            <select v-model="signForm.tampilan" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                                <option value="INVISIBLE">Invisible (Tanpa Visual)</option>
                                <option value="VISIBLE">Visible (Gunakan Spesimen)</option>
                            </select>
                        </div>
                        <div v-if="signForm.tampilan === 'VISIBLE'" class="space-y-3 border-t pt-3">
                            <div class="grid grid-cols-3 gap-2">
                                <div><label class="text-xs">Lebar</label><input type="number" v-model="signForm.width" class="w-full rounded border-slate-300 text-sm" /></div>
                                <div><label class="text-xs">Tinggi</label><input type="number" v-model="signForm.height" class="w-full rounded border-slate-300 text-sm" /></div>
                                <div><label class="text-xs">Halaman</label><input type="number" v-model="signForm.page" min="1" class="w-full rounded border-slate-300 text-sm" /></div>
                            </div>
                        </div>
                        <div class="flex justify-end gap-2 pt-4">
                            <button type="button" @click="showSignModal = false" class="px-4 py-2 bg-slate-100 rounded-lg text-sm font-medium">Batal</button>
                            <button type="submit" :disabled="signForm.processing" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50">
                                Proses TTE
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Panel Preview & Drag Drop -->
                <div v-if="signForm.tampilan === 'VISIBLE'" class="w-1/2 bg-slate-100 p-2 rounded-xl flex justify-center overflow-auto relative items-start border border-slate-200">
                    <div v-if="activeDoc" class="relative border bg-white shadow-md" ref="pdfContainer" style="width: fit-content;">
                        <!-- Ambil file terbaru (signed_file_path jika ada, kalau tidak file_path asli) -->
                        <VuePdfEmbed :source="assetUrl(activeDoc.signed_file_path || activeDoc.file_path)" :page="signForm.page" />
                        
                        <div v-if="auth.user.specimen_path" class="absolute border-2 border-blue-500 bg-blue-100/40 cursor-move"
                            :style="{ left: specX + 'px', top: specY + 'px', width: signForm.width + 'px', height: signForm.height + 'px' }"
                            @mousedown.prevent="isDragging = true">
                            <img :src="assetUrl(auth.user.specimen_path)" class="w-full h-full object-contain pointer-events-none" />
                        </div>
                        <div v-else class="absolute inset-0 flex items-center justify-center bg-white/80 p-4 text-center text-red-600 text-xs font-semibold">
                            Anda belum mengunggah spesimen tanda tangan di profil.
                        </div>
                    </div>
                </div>

            </div>
        </div>

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
                        <button type="button" @click="showRejectModal = false" class="px-4 py-2 bg-slate-100 rounded-lg text-sm font-medium">Batal</button>
                        <button type="submit" :disabled="rejectForm.processing" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">Kirim Penolakan</button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>