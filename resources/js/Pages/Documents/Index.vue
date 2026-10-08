<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed } from 'vue';
import VuePdfEmbed from 'vue-pdf-embed';
import { useEventListener } from '@vueuse/core';

defineProps<{
    documents: Array<any>;
    auth: { user: { name: string; specimen_path: string | null } };
}>();

const page = usePage();
const errorMessage = computed(() => (page.props.errors as any)?.message);

// Modal States
const showUploadModal = ref(false);
const showSignExistingModal = ref(false);
const showVerifyModal = ref(false);

const verificationData = ref<any>(null);
const isVerifying = ref(false);
const activeDoc = ref<any>(null);

const assetUrl = (path: string) => '/storage/' + path;

// 1. Form untuk Upload + TTE Sekaligus
const uploadForm = useForm({
    title: '',
    file: null as File | null,
    passphrase: '',
    tampilan: 'INVISIBLE',
    page: 1,
    originX: 0,
    originY: 0,
    width: 150,
    height: 60,
});

// 2. Form untuk TTE Ulang Dokumen Pending (Sudah ada di DB)
const signForm = useForm({
    passphrase: '',
    tampilan: 'INVISIBLE',
    page: 1,
    originX: 0,
    originY: 0,
    width: 150,
    height: 60,
});

// Drag & Drop States untuk Upload Modal
const uploadPreviewUrl = ref<string | null>(null);
const uploadPdfContainer = ref<HTMLElement | null>(null);
const isDraggingUpload = ref(false);
const uploadSpecX = ref(50);
const uploadSpecY = ref(50);

// Drag & Drop States untuk Sign Existing Modal
const existingPdfContainer = ref<HTMLElement | null>(null);
const isDraggingExisting = ref(false);
const existingSpecX = ref(50);
const existingSpecY = ref(50);

const handleUploadFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        const file = target.files[0];
        uploadForm.file = file;
        uploadPreviewUrl.value = URL.createObjectURL(file);
    }
};

// Generic Drag Handlers
useEventListener(window, 'mousemove', (e: MouseEvent) => {
    if (isDraggingUpload.value && uploadPdfContainer.value) {
        const rect = uploadPdfContainer.value.getBoundingClientRect();
        uploadSpecX.value = Math.max(0, Math.min(e.clientX - rect.left - (uploadForm.width / 2), rect.width - uploadForm.width));
        uploadSpecY.value = Math.max(0, Math.min(e.clientY - rect.top - (uploadForm.height / 2), rect.height - uploadForm.height));
    }
    if (isDraggingExisting.value && existingPdfContainer.value) {
        const rect = existingPdfContainer.value.getBoundingClientRect();
        existingSpecX.value = Math.max(0, Math.min(e.clientX - rect.left - (signForm.width / 2), rect.width - signForm.width));
        existingSpecY.value = Math.max(0, Math.min(e.clientY - rect.top - (signForm.height / 2), rect.height - signForm.height));
    }
});
useEventListener(window, 'mouseup', () => {
    isDraggingUpload.value = false;
    isDraggingExisting.value = false;
});

const submitUploadAndSign = () => {
    if (uploadForm.tampilan === 'VISIBLE' && uploadPdfContainer.value) {
        const h = uploadPdfContainer.value.getBoundingClientRect().height;
        uploadForm.originX = Math.round(uploadSpecX.value);
        uploadForm.originY = Math.round(h - uploadSpecY.value - uploadForm.height);
    }
    uploadForm.post(route('documents.store'), {
        preserveScroll: true,
        onSuccess: () => { showUploadModal.value = false; uploadForm.reset(); uploadPreviewUrl.value = null; }
    });
};

const openSignExistingModal = (doc: any) => {
    activeDoc.value = doc;
    signForm.reset();
    showSignExistingModal.value = true;
};

const submitSignExisting = () => {
    if (signForm.tampilan === 'VISIBLE' && existingPdfContainer.value) {
        const h = existingPdfContainer.value.getBoundingClientRect().height;
        signForm.originX = Math.round(existingSpecX.value);
        signForm.originY = Math.round(h - existingSpecY.value - signForm.height);
    }
    
    signForm.post(route('documents.sign', activeDoc.value.id), {
        preserveScroll: true,
        onSuccess: () => { showSignExistingModal.value = false; }
    });
};
const openVerifyModal = async (doc: any) => {
    activeDoc.value = doc;
    verificationData.value = null;
    isVerifying.value = true;
    showVerifyModal.value = true;
    try {
        const res = await fetch(route('documents.verifyApi', doc.id));
        verificationData.value = await res.json();
    } catch (e) {
        alert('Gagal memuat status verifikasi.');
    } finally {
        isVerifying.value = false;
    }
};

const deleteDoc = (id: number) => {
    if (confirm('Yakin ingin menghapus dokumen ini?')) {
        useForm({}).delete(route('documents.destroy', id), { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Manajemen Dokumen" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manajemen Dokumen TTE</h2>
                <button @click="showUploadModal = true; uploadForm.reset(); uploadPreviewUrl = null;" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 shadow-sm transition-colors">
                    + Unggah & TTE Dokumen
                </button>
            </div>
        </template>

        <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Judul</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr v-for="doc in documents" :key="doc.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ doc.title }}</td>
                            <td class="px-6 py-4 text-sm">
                                <span :class="doc.status === 'signed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'" class="px-2 py-1 text-xs font-semibold rounded-full">
                                    {{ doc.status === 'signed' ? 'Sudah di-TTE' : 'Menunggu (Belum TTE)' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium space-x-2">
                                <!-- Tombol Proses TTE Tetap Muncul Jika Belum di-TTE -->
                                <button v-if="doc.status !== 'signed'" @click="openSignExistingModal(doc)" class="text-blue-600 hover:text-blue-900 font-bold">Proses TTE</button>
                                
                                <button v-if="doc.status === 'signed'" @click="openVerifyModal(doc)" class="text-indigo-600 hover:text-indigo-900">Validitas TTE</button>
                                <a :href="route('documents.download', doc.id)" target="_blank" class="text-gray-600 hover:text-gray-900">Unduh</a>
                                <button @click="deleteDoc(doc.id)" class="text-red-600 hover:text-red-900">Hapus</button>
                            </td>
                        </tr>
                        <tr v-if="documents.length === 0">
                            <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500">Belum ada dokumen.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- MODAL UGGAH & TTE TERPADU -->
        <div v-if="showUploadModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 overflow-y-auto">
            <div class="bg-white rounded-xl p-6 w-full max-w-4xl shadow-2xl flex gap-6 max-h-[90vh] overflow-y-auto">
                <div :class="uploadForm.tampilan === 'VISIBLE' ? 'w-1/2' : 'w-full'" class="space-y-4">
                    <h3 class="text-lg font-bold text-gray-900">Unggah & Tanda Tangan Elektronik</h3>
                    <div v-if="errorMessage" class="p-3 text-sm text-red-700 bg-red-100 rounded-lg border"><strong>Perhatian:</strong> {{ errorMessage }}</div>
                    <form @submit.prevent="submitUploadAndSign" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium">Judul Dokumen</label>
                            <input type="text" v-model="uploadForm.title" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium">File PDF</label>
                            <input type="file" accept="application/pdf" @change="handleUploadFileChange" required class="mt-1 block w-full text-sm text-gray-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Passphrase BSrE</label>
                            <input type="password" v-model="uploadForm.passphrase" autocomplete="off" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Tampilan TTE</label>
                            <select v-model="uploadForm.tampilan" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="INVISIBLE">Invisible (Tanpa Visual)</option>
                                <option value="VISIBLE">Visible (Gunakan Spesimen)</option>
                            </select>
                        </div>
                        <div v-if="uploadForm.tampilan === 'VISIBLE'" class="space-y-3 border-t pt-3">
                            <div class="grid grid-cols-3 gap-2">
                                <div><label class="text-xs">Lebar</label><input type="number" v-model="uploadForm.width" class="w-full rounded border-gray-300 text-sm" /></div>
                                <div><label class="text-xs">Tinggi</label><input type="number" v-model="uploadForm.height" class="w-full rounded border-gray-300 text-sm" /></div>
                                <div><label class="text-xs">Halaman</label><input type="number" v-model="uploadForm.page" min="1" class="w-full rounded border-gray-300 text-sm" /></div>
                            </div>
                        </div>
                        <div class="flex justify-end gap-2 pt-4">
                            <button type="button" @click="showUploadModal = false" class="px-4 py-2 bg-gray-100 rounded-lg text-sm">Batal</button>
                            <button type="submit" :disabled="uploadForm.processing" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">Proses & Simpan</button>
                        </div>
                    </form>
                </div>
                <div v-if="uploadForm.tampilan === 'VISIBLE'" class="w-1/2 bg-gray-100 p-2 rounded-lg flex justify-center overflow-auto relative items-start">
                    <div v-if="uploadPreviewUrl" class="relative border bg-white shadow" ref="uploadPdfContainer" style="width: fit-content;">
                        <VuePdfEmbed :source="uploadPreviewUrl" :page="uploadForm.page" />
                        <div v-if="auth.user.specimen_path" class="absolute border-2 border-blue-500 bg-blue-100/40 cursor-move"
                            :style="{ left: uploadSpecX + 'px', top: uploadSpecY + 'px', width: uploadForm.width + 'px', height: uploadForm.height + 'px' }"
                            @mousedown.prevent="isDraggingUpload = true">
                            <img :src="assetUrl(auth.user.specimen_path)" class="w-full h-full object-contain pointer-events-none" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL TTE ULANG UNTUK DOKUMEN PENDING -->
        <div v-if="showSignExistingModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 overflow-y-auto">
            <div class="bg-white rounded-xl p-6 w-full max-w-4xl shadow-2xl flex gap-6 max-h-[90vh] overflow-y-auto">
                <div :class="signForm.tampilan === 'VISIBLE' ? 'w-1/2' : 'w-full'" class="space-y-4">
                    <h3 class="text-lg font-bold text-gray-900">Proses TTE: {{ activeDoc?.title }}</h3>
                    <div v-if="errorMessage" class="p-3 text-sm text-red-700 bg-red-100 rounded-lg border"><strong>Perhatian:</strong> {{ errorMessage }}</div>
                    <form @submit.prevent="submitSignExisting" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium">Passphrase BSrE</label>
                            <input type="password" v-model="signForm.passphrase" autocomplete="off" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Tampilan TTE</label>
                            <select v-model="signForm.tampilan" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="INVISIBLE">Invisible (Tanpa Visual)</option>
                                <option value="VISIBLE">Visible (Gunakan Spesimen)</option>
                            </select>
                        </div>
                        <div v-if="signForm.tampilan === 'VISIBLE'" class="space-y-3 border-t pt-3">
                            <div class="grid grid-cols-3 gap-2">
                                <div><label class="text-xs">Lebar</label><input type="number" v-model="signForm.width" class="w-full rounded border-gray-300 text-sm" /></div>
                                <div><label class="text-xs">Tinggi</label><input type="number" v-model="signForm.height" class="w-full rounded border-gray-300 text-sm" /></div>
                                <div><label class="text-xs">Halaman</label><input type="number" v-model="signForm.page" min="1" class="w-full rounded border-gray-300 text-sm" /></div>
                            </div>
                        </div>
                        <div class="flex justify-end gap-2 pt-4">
                            <button type="button" @click="showSignExistingModal = false" class="px-4 py-2 bg-gray-100 rounded-lg text-sm">Batal</button>
                            <button type="submit" :disabled="signForm.processing" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">Proses TTE</button>
                        </div>
                    </form>
                </div>
                <div v-if="signForm.tampilan === 'VISIBLE'" class="w-1/2 bg-gray-100 p-2 rounded-lg flex justify-center overflow-auto relative items-start">
                    <div v-if="activeDoc" class="relative border bg-white shadow" ref="existingPdfContainer" style="width: fit-content;">
                        <VuePdfEmbed :source="assetUrl(activeDoc.file_path)" :page="signForm.page" />
                        <div v-if="auth.user.specimen_path" class="absolute border-2 border-blue-500 bg-blue-100/40 cursor-move"
                            :style="{ left: existingSpecX + 'px', top: existingSpecY + 'px', width: signForm.width + 'px', height: signForm.height + 'px' }"
                            @mousedown.prevent="isDraggingExisting = true">
                            <img :src="assetUrl(auth.user.specimen_path)" class="w-full h-full object-contain pointer-events-none" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL VALIDITAS -->
        <div v-if="showVerifyModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-white rounded-xl p-6 w-full max-w-2xl shadow-2xl space-y-4 max-h-[85vh] overflow-y-auto">
                <h3 class="text-lg font-bold text-gray-900">Validitas TTE</h3>
                <div v-if="isVerifying" class="text-center py-8 text-gray-500 text-sm">Memeriksa keaslian tanda tangan ke server BSrE...</div>
                <div v-else-if="verificationData" class="space-y-3 text-sm">
                    <div class="p-4 rounded-lg" :class="verificationData.signatureCount > 0 ? 'bg-green-50 text-green-800' : 'bg-yellow-50 text-yellow-800'">
                        <p class="font-bold text-base">Kesimpulan: {{ verificationData.conclusion }}</p>
                        <p>{{ verificationData.description }}</p>
                    </div>
                </div>
                <div class="flex justify-end"><button @click="showVerifyModal = false" class="px-4 py-2 bg-gray-100 rounded-lg text-sm">Tutup</button></div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>