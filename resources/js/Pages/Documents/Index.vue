<script setup lang="ts">
import { Head, useForm, usePage, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed } from 'vue';
import TteProcessModal from '@/Components/TteProcessModal.vue';
import { Clock } from 'lucide-vue-next';

defineProps<{
    documents: Array<any>;
    auth: { user: { name: string; specimen_path: string | null } };
}>();

const page = usePage();
const flashSuccess = computed(() => (page.props.flash as any)?.success);
const errorMessage = computed(() => (page.props.errors as any)?.message);

// Modals
const showUploadModal = ref(false);
const showSignModal = ref(false);
const activeDoc = ref<any>(null);

const assetUrl = (path: string) => '/storage/' + path;

// 1. Form Murni Upload
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
                    <button @click="showUploadModal = true; uploadForm.reset();" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700">
                        + Unggah Dokumen
                    </button>
                </div>
            </div>
        </template>

        <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div v-if="flashSuccess" class="p-4 bg-green-50 text-green-700 rounded-lg">{{ flashSuccess }}</div>
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase">Judul</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase">Informasi</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-slate-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <tr v-for="doc in documents" :key="doc.id" class="hover:bg-slate-50">
                            <td class="px-6 py-4 text-sm font-medium text-slate-900">{{ doc.title }}</td>
                            <td class="px-6 py-4 text-sm">
                                <div class="flex flex-col items-start gap-1.5">
                                    <!-- Status Utama -->
                                    <span 
                                        :class="{
                                            'bg-green-100 text-green-800': doc.status === 'signed',
                                            'bg-red-100 text-red-800': doc.status === 'rejected',
                                            'bg-yellow-100 text-yellow-800': doc.status === 'forwarded' || doc.status === 'pending'
                                        }" 
                                        class="px-2 py-1 text-xs font-bold rounded-md"
                                    >
                                        {{ doc.status === 'signed' ? 'Selesai di-TTE' : (doc.status === 'rejected' ? 'Ditolak / Dikembalikan' : (doc.status === 'forwarded' ? 'Proses (Diteruskan)' : 'Menunggu')) }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div class="flex flex-col items-start gap-1.5">
                                    <div v-if="doc.signers && doc.signers.length > 0" class="flex flex-col gap-1 mt-1">
                                        <template v-for="signer in doc.signers" :key="signer.id">
                                            <div v-if="signer.status === 'pending'" class="inline-flex items-center gap-1 bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded border border-slate-200 text-[10px] font-semibold whitespace-nowrap">
                                                <Clock class="w-3 h-3 text-slate-400" />
                                                Menunggu: {{ signer.signer.name }}
                                            </div>
                                            <!-- Tambahan: Jika ditolak, tampilkan juga siapa yang menolak -->
                                            <div v-else-if="signer.status === 'rejected'" class="inline-flex items-center gap-1 bg-red-50 text-red-600 px-1.5 py-0.5 rounded border border-red-100 text-[10px] font-semibold whitespace-nowrap">
                                                Ditolak oleh: {{ signer.signer.name }}
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium space-x-3">
                                <Link :href="route('documents.show', doc.id)" class="text-slate-600 hover:text-slate-900">Detail</Link>
                                <button @click="openSignModal(doc)" class="text-blue-600 hover:text-blue-900 font-bold">Proses TTE</button>
                                <a :href="route('documents.download', doc.id)" target="_blank" class="text-slate-600 hover:text-slate-900">Unduh</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- MODAL UPLOAD MURNI -->
        <div v-if="showUploadModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
            <div class="bg-white rounded-xl p-6 w-full max-w-md shadow-2xl">
                <h3 class="text-lg font-bold text-slate-900 mb-4">Unggah Dokumen Baru</h3>
                <form @submit.prevent="submitUpload" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium">Judul Dokumen</label>
                        <input type="text" v-model="uploadForm.title" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium">File PDF</label>
                        <input type="file" accept="application/pdf" @change="(e: any) => uploadForm.file = e.target.files[0]" required class="mt-1 block w-full text-sm text-slate-500" />
                    </div>
                    <div class="flex justify-end gap-2 pt-4">
                        <button type="button" @click="showUploadModal = false" class="px-4 py-2 bg-slate-100 rounded-lg text-sm">Batal</button>
                        <button type="submit" :disabled="uploadForm.processing" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">Unggah Dokumen</button>
                    </div>
                </form>
            </div>
        </div>

        <TteProcessModal v-if="activeDoc" v-model:show="showSignModal" :document="activeDoc"
            :specimen-path="auth.user.specimen_path" />
    </AuthenticatedLayout>
</template>