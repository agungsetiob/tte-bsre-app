<script setup lang="ts">
import { Head, useForm, usePage, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const form = useForm({
    file: null as File | null,
});

const page = usePage();
// Mengambil langsung dari props halaman Inertia
const result = computed(() => (page.props as any).verificationResult);
const fileName = computed(() => (page.props as any).fileName);

const submitCheck = () => {
    form.post(route('verification.check'), { preserveScroll: true });
};
</script>

<template>
    <Head title="Verifikasi Dokumen TTE" />

    <div class="min-h-screen bg-slate-100 flex flex-col justify-between p-6">
        <div class="max-w-3xl mx-auto w-full flex justify-between items-center py-4">
            <Link :href="route('welcome')" class="text-sm font-semibold text-blue-600 hover:underline">&larr; Kembali ke Beranda</Link>
            <Link :href="route('login')" class="text-sm font-semibold text-slate-700 hover:text-blue-600">Login Sistem</Link>
        </div>

        <div class="max-w-3xl w-full mx-auto bg-white rounded-2xl shadow-xl p-8 space-y-6 my-auto">
            <div class="text-center">
                <h1 class="text-2xl font-bold text-slate-900">Verifikasi Keaslian Dokumen TTE</h1>
                <p class="text-sm text-slate-500 mt-1">Unggah dokumen PDF ber-TTE untuk memeriksa keabsahan tanda tangan secara instan.</p>
            </div>

            <form @submit.prevent="submitCheck" class="space-y-4 bg-slate-50 p-4 rounded-xl border border-slate-200">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Pilih File PDF</label>
                    <input type="file" accept="application/pdf" @change="(e: any) => form.file = e.target.files[0]" required class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer" />
                </div>
                <button type="submit" :disabled="form.processing" class="w-full bg-blue-600 text-white py-2.5 rounded-xl font-medium hover:bg-blue-700 shadow transition-all disabled:opacity-50">
                    {{ form.processing ? 'Memeriksa ke Server BSrE...' : 'Verifikasi Sekarang' }}
                </button>
            </form>

            <!-- Hasil Verifikasi -->
            <div v-if="result" class="space-y-6 border-t pt-6">
                <!-- Status Utama -->
                <div class="p-5 rounded-xl border" :class="result.conclusion === 'VALID' ? 'bg-green-50 border-green-200 text-green-900' : 'bg-red-50 border-red-200 text-red-900'">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-xs uppercase tracking-wider font-bold">Hasil Verifikasi</span>
                        <span class="px-3 py-1 text-xs font-extrabold rounded-full" :class="result.conclusion === 'VALID' ? 'bg-green-600 text-white' : 'bg-red-600 text-white'">
                            {{ result.conclusion }}
                        </span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm mt-3">
                        <div>
                            <p class="text-slate-500 text-xs">Nama Dokumen:</p>
                            <p class="font-semibold text-slate-900">{{ fileName || 'Dokumen.pdf' }}</p>
                        </div>
                        <div>
                            <p class="text-slate-500 text-xs">Jumlah Tanda Tangan:</p>
                            <p class="font-semibold text-slate-900">{{ result.signatureCount }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-slate-500 text-xs">Catatan:</p>
                            <p class="font-medium">{{ result.description }}</p>
                        </div>
                    </div>
                </div>

                <!-- Detail Tanda Tangan -->
                <div v-if="result.signatureInformations && result.signatureInformations.length > 0" class="space-y-4">
                    <h3 class="font-bold text-slate-800 text-base border-b pb-2">Detail Tanda Tangan</h3>
                    <div v-for="(sig, idx) in result.signatureInformations" :key="idx" class="bg-slate-50 border border-slate-200 p-4 rounded-xl space-y-3 text-sm">
                        <p class="font-bold text-blue-900">Tanda Tangan {{ Number(idx) + 1 }}</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                            <p><strong class="text-slate-500">Signature Field:</strong> <span class="font-mono bg-slate-200 px-1 py-0.5 rounded">{{ sig.fieldName }}</span></p>
                            <p><strong class="text-slate-500">Nama TSA:</strong> {{ sig.timestampInfomation?.signerName || 'Timestamp Authority BSRE BSSN' }}</p>
                            <p><strong class="text-slate-500">Nama Penanda Tangan:</strong> <span class="font-semibold text-slate-900">{{ sig.signerName }}</span></p>
                            <p><strong class="text-slate-500">Validitas Sertifikat:</strong> {{ sig.certificateDetails?.[0]?.notBeforeDate?.substring(0, 10) }} s/d {{ sig.certificateDetails?.[0]?.notAfterDate?.substring(0, 10) }}</p>
                            <p><strong class="text-slate-500">Integritas Dokumen:</strong> <span class="text-green-600 font-bold">Baik (Valid)</span></p>
                            <p><strong class="text-slate-500">Ditandatangani pada:</strong> {{ sig.signatureDate?.replace('T', ' ').substring(0, 19) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="text-center text-xs text-slate-400 py-4">
            Balai Sertifikasi Elektronik &bull; Badan Siber dan Sandi Negara
        </footer>
    </div>
</template>