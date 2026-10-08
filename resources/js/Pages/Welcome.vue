<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    canLogin: boolean;
    canRegister: boolean;
}>();
</script>

<template>
    <Head title="Selamat Datang - TTE BSrE" />

    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-950 to-slate-900 text-white flex flex-col justify-between p-6 md:p-12">
        <!-- Header Nav -->
        <header class="max-w-7xl mx-auto w-full flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="bg-blue-600 p-2.5 rounded-xl font-bold text-lg tracking-wider shadow-lg">BSrE</div>
                <span class="font-semibold text-lg tracking-wide">Aplikasi Tanda Tangan Elektronik</span>
            </div>
            <nav class="space-x-4">
                <Link v-if="canLogin" :href="route('login')" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-lg text-sm font-medium transition-colors shadow">
                    Masuk (Login)
                </Link>
            </nav>
        </header>

        <!-- Hero Section -->
        <main class="max-w-4xl mx-auto text-center space-y-6 my-auto py-12">
            <h1 class="text-4xl md:text-6xl font-extrabold tracking-tight leading-tight">
                Layanan Tanda Tangan & Verifikasi Dokumen Elektronik
            </h1>
            <p class="text-slate-300 text-lg max-w-2xl mx-auto">
                Sistem terintegrasi Balai Sertifikasi Elektronik (BSrE) BSSN untuk pengesahan dokumen instansi yang aman, sah, dan terpercaya.
            </p>
            <div class="flex flex-wrap justify-center gap-4 pt-4">
                <Link :href="route('verification.public')" class="px-6 py-3 bg-white text-slate-900 hover:bg-slate-100 rounded-xl font-semibold shadow-lg transition-all flex items-center gap-2">
                    🔍 Verifikasi Dokumen Publik
                </Link>
                <Link :href="route('login')" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold shadow-lg transition-all">
                    Mulai Mengelola Dokumen
                </Link>
            </div>
        </main>

        <!-- Footer -->
        <footer class="max-w-7xl mx-auto w-full text-center text-xs text-slate-500 border-t border-slate-800 pt-6">
            &copy; 2026 Balai Sertifikasi Elektronik (BSrE) &bull; Badan Siber dan Sandi Negara
        </footer>
    </div>
</template>