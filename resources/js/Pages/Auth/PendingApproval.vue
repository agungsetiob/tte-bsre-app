<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Clock, LogOut, ShieldCheck } from 'lucide-vue-next';

const form = useForm({});

const logout = () => {
    form.post(route('logout'));
};
</script>

<template>
    <Head title="Menunggu Persetujuan" />

    <div class="min-h-screen bg-slate-50 flex flex-col justify-center items-center p-6">
        <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden text-center">
            
            <!-- Header Aksesori -->
            <div class="bg-slate-900 p-6 flex justify-center">
                <ShieldCheck class="w-12 h-12 text-blue-500" />
            </div>

            <!-- Konten Utama -->
            <div class="p-8 space-y-6">
                <div class="flex justify-center">
                    <div class="bg-yellow-100 p-3 rounded-full">
                        <Clock class="w-8 h-8 text-yellow-600" />
                    </div>
                </div>
                
                <div class="space-y-2">
                    <h1 class="text-2xl font-bold text-slate-900">Akun Menunggu Aktivasi</h1>
                    <p class="text-sm text-slate-500 leading-relaxed">
                        Pendaftaran Anda telah berhasil. Namun, untuk menjaga keamanan sistem, akun Anda memerlukan verifikasi dan persetujuan dari Administrator instansi sebelum dapat menggunakan layanan Tanda Tangan Elektronik.
                    </p>
                </div>

                <div class="bg-slate-50 p-4 rounded-lg border border-slate-100 text-xs text-slate-600 text-left">
                    <ul class="list-disc list-inside space-y-1">
                        <li>Silakan hubungi Admin sistem TTE.</li>
                        <li>Cek kembali secara berkala.</li>
                        <li>Pastikan NIK yang Anda daftarkan sudah benar.</li>
                    </ul>
                </div>

                <form @submit.prevent="logout">
                    <button 
                        type="submit" 
                        class="w-full flex justify-center items-center gap-2 bg-slate-900 text-white px-4 py-2.5 rounded-xl text-sm font-medium hover:bg-slate-800 transition-colors shadow-md"
                    >
                        <LogOut class="w-4 h-4" />
                        Keluar dari Sistem
                    </button>
                </form>
            </div>
        </div>
        
        <div class="mt-8 text-xs text-slate-400">
            &copy; 2026 Balai Sertifikasi Elektronik (BSrE) &bull; BSSN
        </div>
    </div>
</template>