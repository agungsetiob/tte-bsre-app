<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps<{
    document: any;
}>();

const verificationResult = ref<any>(null);
const isLoading = ref(true);
const isError = ref(false);

onMounted(async () => {
    try {
        // Melakukan request background ke controller
        const response = await axios.post(route('verify.check_async', props.document.verification_code));
        verificationResult.value = response.data;
    } catch (error) {
        console.error(error);
        isError.value = true;
    } finally {
        isLoading.value = false;
    }
});

const formatDate = (dateString: string) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', { 
        year: 'numeric', month: 'long', day: 'numeric', 
        hour: '2-digit', minute: '2-digit' 
    });
};
</script>

<template>
    <Head :title="'Verifikasi Dokumen: ' + document.title" />

    <div class="min-h-screen bg-slate-50 py-12 px-4 sm:px-6 lg:px-8 flex items-center justify-center">
        <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-100">
            
            <!-- STATE 1: LOADING -->
            <div v-if="isLoading" class="bg-blue-600 px-6 py-10 text-center text-white">
                <div class="flex justify-center mb-4">
                    <svg class="animate-spin h-10 w-10 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </div>
                <h2 class="text-xl font-bold mb-1 animate-pulse">Menghubungi BSrE BSSN...</h2>
                <p class="text-sm opacity-90">Memeriksa keaslian dokumen</p>
            </div>

            <!-- STATE 2: ERROR KONEKSI -->
            <div v-else-if="isError" class="bg-amber-500 px-6 py-8 text-center text-white">
                <div class="mb-4 inline-flex items-center justify-center w-16 h-16 rounded-full bg-white/20 backdrop-blur-sm">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <h2 class="text-2xl font-bold mb-1">Pengecekan Tertunda</h2>
                <p class="text-sm opacity-90">Gagal terhubung ke server BSrE. Data di bawah adalah informasi dari sistem lokal.</p>
            </div>

            <!-- STATE 3: SELESAI (SUKSES DARI BSSN) -->
            <div v-else :class="verificationResult?.conclusion === 'VALID' ? 'bg-green-600' : 'bg-red-600'" class="px-6 py-8 text-center text-white transition-colors duration-500">
                <div class="mb-4 inline-flex items-center justify-center w-16 h-16 rounded-full bg-white/20 backdrop-blur-sm">
                    <svg v-if="verificationResult?.conclusion === 'VALID'" class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <svg v-else class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h2 class="text-2xl font-bold mb-1">
                    {{ verificationResult?.description }}
                </h2>
            </div>

            <div class="p-6 space-y-5">
                <!-- Detail Dasar Dokumen (Dari Database Lokal - Langsung Muncul) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Judul Dokumen</label>
                        <p class="text-slate-900 font-medium leading-snug">{{ document.title }}</p>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Dibuat Oleh</label>
                        <p class="text-slate-900 font-medium">{{ document.owner?.name || 'Sistem' }}</p>
                    </div>
                </div>

                <!-- Hasil Cek Kriptografi -->
                <div v-if="verificationResult" class="border-t pt-4">
                    <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3 block">Detail Tanda Tangan Elektronik</label>
                    
                    <div v-if="verificationResult.signatureInformations?.length > 0" class="space-y-3">
                        <div v-for="(sig, idx) in verificationResult.signatureInformations" :key="idx" class="bg-slate-50 border border-slate-200 p-3 rounded-xl text-sm">
                            <div class="flex justify-between border-b border-slate-200 pb-2 mb-2">
                                <span class="font-bold text-blue-900">Penanda Tangan {{ Number(idx) + 1 }}</span>
                                <span class="text-green-600 font-bold text-xs bg-green-100 px-2 py-0.5 rounded">{{verificationResult.conclusion}}</span>
                            </div>
                            <div class="space-y-1 text-xs">
                                <p><strong class="text-slate-500">Nama:</strong> <span class="font-semibold text-slate-900">{{ sig.signerName }}</span></p>
                                <p><strong class="text-slate-500">Waktu TTE:</strong> {{ sig.signatureDate?.replace('T', ' ').substring(0, 19) }}</p>
                                <p v-if="sig.certificateDetails?.length"><strong class="text-slate-500">Masa Aktif Sertifikat:</strong> {{ sig.certificateDetails[0].notBeforeDate?.substring(0, 10) }} s/d {{ sig.certificateDetails[0].notAfterDate?.substring(0, 10) }}</p>
                            </div>
                        </div>
                    </div>

                    <div v-if="verificationResult" class="mt-4 text-xs text-slate-600">
                        <p><strong>Kesimpulan:</strong> <span :class="verificationResult?.conclusion === 'VALID' ? 'text-green-600' : 'text-red-600'">{{ verificationResult.conclusion == 'VALID' ? verificationResult.signatureInformations[0].reason : 'Dokumen Tidak Valid' }}</span></p>
                    </div>
                    
                    <div v-else class="text-center bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <p class="text-sm text-slate-500">Dokumen ini belum memiliki Tanda Tangan Elektronik.</p>
                    </div>
                </div>

                <!-- Skeleton Loading untuk TTE -->
                <div v-else-if="isLoading" class="border-t pt-4 space-y-3 animate-pulse">
                     <div class="h-3 bg-slate-200 rounded w-1/2 mb-4"></div>
                     <div class="h-24 bg-slate-100 rounded-xl w-full"></div>
                </div>

                <!-- Tombol Unduh (Langsung bisa diklik meskipun BSrE masih loading) -->
                <div v-if="verificationResult?.conclusion === 'VALID'" class="pt-4">
                    <a :href="route('verify.download', document.verification_code)" 
                       class="w-full flex justify-center items-center gap-2 py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Unduh Dokumen PDF
                    </a>
                </div>
            </div>
            
            <div class="bg-slate-100 p-4 text-center">
                <p class="text-xs text-slate-500 font-medium">
                    Balai Sertifikasi Elektronik &bull; Badan Siber dan Sandi Negara
                </p>
            </div>
        </div>
    </div>
</template>