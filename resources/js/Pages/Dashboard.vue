<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    Users,
    FileText,
    Clock4,
    CheckCircle2,
    ArrowRight,
    PenLine,
} from 'lucide-vue-next';

import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale,
} from 'chart.js';
import type { ChartData, ChartOptions } from 'chart.js';
import { Bar } from 'vue-chartjs';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

const props = defineProps<{
    auth: any;
    dashboardData: any;
}>();

// Konfigurasi Grafik Admin (warna brand)
const chartData: ChartData<'bar'> = {
    labels: props.dashboardData.chart?.labels || [],
    datasets: [
        {
            label: 'TTE Berhasil',
            backgroundColor: '#0B8570',
            hoverBackgroundColor: '#086B5B',
            borderRadius: 8,
            borderSkipped: false,
            maxBarThickness: 42,
            data: props.dashboardData.chart?.data || [],
        },
    ],
};

const chartOptions: ChartOptions<'bar'> = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: '#0F2A2E',
            padding: 10,
            cornerRadius: 8,
            displayColors: false,
            titleFont: { family: 'Plus Jakarta Sans', weight: 600 },
            bodyFont: { family: 'Plus Jakarta Sans' },
        },
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                precision: 0,
                color: '#6A8593',
                font: { family: 'Plus Jakarta Sans', size: 11 },
            },
            grid: { color: '#EEF4F2' },
            border: { display: false },
        },
        x: {
            ticks: {
                color: '#6A8593',
                font: { family: 'Plus Jakarta Sans', size: 11 },
            },
            grid: { display: false },
            border: { display: false },
        },
    },
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-[#0B7563]">
                    Dashboard
                </p>
                <h2 class="font-display mt-1 text-xl font-bold leading-tight text-[#0F2A2E]">
                    Halo, {{ auth.user.name }}
                </h2>
                <p class="mt-0.5 text-xs text-[#6A8593]">
                    {{
                        auth.user.role === 'admin'
                            ? 'Ringkasan aktivitas TTE dan pengguna rumah sakit.'
                            : 'Ringkasan dokumen dan antrean tanda tangan Anda.'
                    }}
                </p>
            </div>
        </template>

        <div class="mx-auto w-full max-w-8xl space-y-5 px-5 py-8 text-[#0F2A2E] md:px-8">
            <!-- ============================ -->
            <!-- TAMPILAN KHUSUS ADMIN        -->
            <!-- ============================ -->
            <template v-if="auth.user.role === 'admin'">
                <!-- 1. KARTU STATISTIK ADMIN -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Total Pegawai -->
                    <div
                        class="group rounded-2xl border border-[#DCE8E5] bg-white p-5 shadow-[0_18px_40px_-32px_rgba(15,42,46,.35)] transition-all hover:-translate-y-0.5 hover:border-[#BCE5DA]"
                    >
                        <div class="flex items-start justify-between">
                            <span
                                class="grid h-11 w-11 place-items-center rounded-xl bg-[#EAF7F3] text-[#0B7563]"
                            >
                                <Users class="h-5 w-5" />
                            </span>
                        </div>
                        <p
                            class="mt-4 text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                        >
                            Total Pegawai
                        </p>
                        <p
                            class="font-display mt-1 text-3xl font-extrabold leading-none text-[#0F2A2E]"
                        >
                            {{ dashboardData.stats.total_users }}
                        </p>
                    </div>

                    <!-- Total Dokumen -->
                    <div
                        class="group rounded-2xl border border-[#DCE8E5] bg-white p-5 shadow-[0_18px_40px_-32px_rgba(15,42,46,.35)] transition-all hover:-translate-y-0.5 hover:border-[#BCE5DA]"
                    >
                        <div class="flex items-start justify-between">
                            <span
                                class="grid h-11 w-11 place-items-center rounded-xl bg-[#EEF4F2] text-[#4A6572]"
                            >
                                <FileText class="h-5 w-5" />
                            </span>
                        </div>
                        <p
                            class="mt-4 text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                        >
                            Total Dokumen
                        </p>
                        <p
                            class="font-display mt-1 text-3xl font-extrabold leading-none text-[#0F2A2E]"
                        >
                            {{ dashboardData.stats.total_docs }}
                        </p>
                    </div>

                    <!-- Antrean TTE -->
                    <div
                        class="group rounded-2xl border border-[#DCE8E5] bg-white p-5 shadow-[0_18px_40px_-32px_rgba(15,42,46,.35)] transition-all hover:-translate-y-0.5 hover:border-[#F5D9A8]"
                    >
                        <div class="flex items-start justify-between">
                            <span
                                class="grid h-11 w-11 place-items-center rounded-xl bg-amber-50 text-amber-600"
                            >
                                <Clock4 class="h-5 w-5" />
                            </span>
                        </div>
                        <p
                            class="mt-4 text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                        >
                            Antrean TTE
                        </p>
                        <p
                            class="font-display mt-1 text-3xl font-extrabold leading-none text-[#0F2A2E]"
                        >
                            {{ dashboardData.stats.pending_tte }}
                        </p>
                    </div>

                    <!-- TTE Berhasil -->
                    <div
                        class="group rounded-2xl border border-[#DCE8E5] bg-white p-5 shadow-[0_18px_40px_-32px_rgba(15,42,46,.35)] transition-all hover:-translate-y-0.5 hover:border-[#BCE5DA]"
                    >
                        <div class="flex items-start justify-between">
                            <span
                                class="grid h-11 w-11 place-items-center rounded-xl bg-[#EAF7F3] text-[#0B7563]"
                            >
                                <CheckCircle2 class="h-5 w-5" />
                            </span>
                        </div>
                        <p
                            class="mt-4 text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                        >
                            TTE Berhasil
                        </p>
                        <p
                            class="font-display mt-1 text-3xl font-extrabold leading-none text-[#0F2A2E]"
                        >
                            {{ dashboardData.stats.success_tte }}
                        </p>
                    </div>
                </div>

                <!-- 2. GRAFIK AKTIVITAS TTE -->
                <div
                    class="rounded-2xl border border-[#DCE8E5] bg-white p-6 shadow-[0_18px_40px_-32px_rgba(15,42,46,.35)]"
                >
                    <div class="mb-5 flex items-center justify-between">
                        <div>
                            <h3
                                class="font-display text-base font-bold text-[#0F2A2E]"
                            >
                                Aktivitas TTE Berhasil
                            </h3>
                            <p class="mt-0.5 text-xs text-[#6A8593]">
                                7 hari terakhir
                            </p>
                        </div>
                        <span
                            class="hidden items-center gap-1.5 rounded-full border border-[#BCE5DA] bg-[#EAF7F3] px-2.5 py-1 text-[11px] font-semibold text-[#0B7563] sm:inline-flex"
                        >
                            Real-time
                        </span>
                    </div>
                    <div class="h-72 w-full">
                        <Bar :data="chartData" :options="chartOptions" />
                    </div>
                </div>
            </template>

            <!-- ============================ -->
            <!-- TAMPILAN KHUSUS USER         -->
            <!-- ============================ -->
            <template v-else>
                <!-- 1. KARTU STATISTIK USER -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <!-- Hero: Perlu Ditandatangani -->
                    <div
                        class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#0F2A2E] via-[#0B7563] to-[#0B8570] p-6 text-white shadow-[0_24px_60px_-30px_rgba(11,133,112,.55)]"
                    >
                        <!-- glow -->
                        <div
                            class="pointer-events-none absolute -right-12 -top-12 h-40 w-40 rounded-full bg-[#E29A2D] opacity-25 blur-3xl"
                        />
                        <div class="relative flex items-start justify-between">
                            <div>
                                <p
                                    class="text-[11px] font-bold uppercase tracking-[0.16em] text-white/70"
                                >
                                    Perlu Ditandatangani
                                </p>
                                <p
                                    class="font-display mt-3 text-4xl font-extrabold leading-none"
                                >
                                    {{ dashboardData.stats.need_my_sign }}
                                </p>
                                <Link
                                    :href="route('documents.requests')"
                                    class="mt-5 inline-flex items-center gap-1.5 rounded-lg bg-white/15 px-3 py-1.5 text-xs font-semibold text-white backdrop-blur transition-colors hover:bg-white/25"
                                >
                                    Buka Kotak Masuk
                                    <ArrowRight class="h-3.5 w-3.5" />
                                </Link>
                            </div>
                            <PenLine class="h-10 w-10 shrink-0 text-white/40" />
                        </div>
                    </div>

                    <!-- Dokumen Saya -->
                    <div
                        class="rounded-2xl border border-[#DCE8E5] bg-white p-5 shadow-[0_18px_40px_-32px_rgba(15,42,46,.35)] transition-all hover:-translate-y-0.5 hover:border-[#BCE5DA]"
                    >
                        <div class="flex items-start justify-between">
                            <span
                                class="grid h-11 w-11 place-items-center rounded-xl bg-[#EEF4F2] text-[#4A6572]"
                            >
                                <FileText class="h-5 w-5" />
                            </span>
                        </div>
                        <p
                            class="mt-4 text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                        >
                            Dokumen Unggahan Saya
                        </p>
                        <p
                            class="font-display mt-1 text-3xl font-extrabold leading-none text-[#0F2A2E]"
                        >
                            {{ dashboardData.stats.my_docs }}
                        </p>
                    </div>

                    <!-- Riwayat TTE Saya -->
                    <div
                        class="rounded-2xl border border-[#DCE8E5] bg-white p-5 shadow-[0_18px_40px_-32px_rgba(15,42,46,.35)] transition-all hover:-translate-y-0.5 hover:border-[#BCE5DA]"
                    >
                        <div class="flex items-start justify-between">
                            <span
                                class="grid h-11 w-11 place-items-center rounded-xl bg-[#EAF7F3] text-[#0B7563]"
                            >
                                <CheckCircle2 class="h-5 w-5" />
                            </span>
                        </div>
                        <p
                            class="mt-4 text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                        >
                            Riwayat TTE Saya
                        </p>
                        <p
                            class="font-display mt-1 text-3xl font-extrabold leading-none text-[#0F2A2E]"
                        >
                            {{ dashboardData.stats.my_signed }}
                        </p>
                    </div>
                </div>

                <!-- 2. SHORTCUT: PERMINTAAN TTE MENDESAK -->
                <div
                    class="overflow-hidden rounded-2xl border border-[#DCE8E5] bg-white shadow-[0_18px_40px_-32px_rgba(15,42,46,.35)]"
                >
                    <!-- Card header -->
                    <div
                        class="flex items-center justify-between gap-3 border-b border-[#E9F0EE] bg-[#F8FCFB] px-6 py-4"
                    >
                        <div class="flex items-center gap-3">
                            <span
                                class="grid h-9 w-9 place-items-center rounded-xl bg-amber-50 text-amber-600"
                            >
                                <Clock4 class="h-4 w-4" />
                            </span>
                            <div>
                                <h3
                                    class="font-display text-sm font-bold text-[#0F2A2E]"
                                >
                                    Antrean TTE Mendesak
                                </h3>
                                <p class="text-[11px] text-[#6A8593]">
                                    Dokumen yang menunggu tanda tangan Anda
                                </p>
                            </div>
                        </div>
                        <Link
                            :href="route('documents.requests')"
                            class="hidden items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-[#0B7563] transition-colors hover:bg-[#EAF7F3] sm:inline-flex"
                        >
                            Lihat Semua
                            <ArrowRight class="h-3.5 w-3.5" />
                        </Link>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table
                            v-if="dashboardData.urgent_requests.length > 0"
                            class="min-w-full"
                        >
                            <tbody class="divide-y divide-[#EEF4F2]">
                                <tr
                                    v-for="req in dashboardData.urgent_requests"
                                    :key="req.id"
                                    class="transition-colors hover:bg-[#F8FCFB]"
                                >
                                    <td class="px-6 py-4">
                                        <div class="flex items-start gap-3">
                                            <span
                                                class="mt-0.5 grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-[#EAF7F3] text-[#0B7563]"
                                            >
                                                <FileText class="h-4 w-4" />
                                            </span>
                                            <div class="min-w-0">
                                                <p
                                                    class="truncate text-sm font-semibold text-[#0F2A2E]"
                                                    :title="req.document.title"
                                                >
                                                    {{ req.document.title }}
                                                </p>
                                                <p
                                                    class="mt-0.5 flex items-center gap-1 text-[11px] text-[#6A8593]"
                                                >
                                                    Dari:
                                                    <span
                                                        class="font-medium text-[#4A6572]"
                                                    >
                                                        {{ req.document.owner.name }}
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-right text-xs text-[#6A8593]"
                                    >
                                        {{ formatDate(req.created_at) }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right">
                                        <Link
                                            :href="route('documents.requests')"
                                            class="inline-flex items-center gap-1 rounded-lg bg-[#0B8570] px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition-all hover:-translate-y-0.5 hover:bg-[#086B5B]"
                                        >
                                            Buka Kotak Masuk
                                            <ArrowRight class="h-3.5 w-3.5" />
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Empty state -->
                        <div
                            v-else
                            class="flex flex-col items-center gap-3 px-6 py-14 text-center"
                        >
                            <div
                                class="grid h-14 w-14 place-items-center rounded-2xl bg-[#EAF7F3]"
                            >
                                <CheckCircle2 class="h-7 w-7 text-[#0B7563]" />
                            </div>
                            <div>
                                <p
                                    class="font-display text-sm font-bold text-[#0F2A2E]"
                                >
                                    Kerja bagus!
                                </p>
                                <p class="mt-1 text-xs text-[#6A8593]">
                                    Tidak ada dokumen yang menumpuk saat ini.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </AuthenticatedLayout>
</template>