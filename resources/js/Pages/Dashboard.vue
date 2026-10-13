<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Users, FileText, Clock, CheckCircle, ArrowRight, PenTool } from 'lucide-vue-next';

// Setup Chart.js
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js';
import { Bar } from 'vue-chartjs';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

const props = defineProps<{
    auth: any;
    dashboardData: any;
}>();

// Konfigurasi Grafik Admin
const chartData = {
    labels: props.dashboardData.chart?.labels || [],
    datasets: [
        {
            label: 'TTE Berhasil',
            backgroundColor: '#2563eb',
            borderRadius: 6,
            data: props.dashboardData.chart?.data || []
        }
    ]
};

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false }
    },
    scales: {
        y: { beginAtZero: true, ticks: { precision: 0 } }
    }
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric'
    });
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                Halo, {{ auth.user.name }}!
            </h2>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- ============================ -->
                <!-- TAMPILAN KHUSUS ADMIN        -->
                <!-- ============================ -->
                <template v-if="auth.user.role === 'admin'">
                    <!-- 1. KARTU STATISTIK ADMIN -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
                            <div class="p-3 bg-blue-50 text-blue-600 rounded-xl"><Users class="w-6 h-6" /></div>
                            <div>
                                <p class="text-sm font-medium text-slate-500">Total Pegawai</p>
                                <p class="text-2xl font-bold text-slate-900">{{ dashboardData.stats.total_users }}</p>
                            </div>
                        </div>
                        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
                            <div class="p-3 bg-purple-50 text-purple-600 rounded-xl"><FileText class="w-6 h-6" /></div>
                            <div>
                                <p class="text-sm font-medium text-slate-500">Total Dokumen</p>
                                <p class="text-2xl font-bold text-slate-900">{{ dashboardData.stats.total_docs }}</p>
                            </div>
                        </div>
                        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
                            <div class="p-3 bg-amber-50 text-amber-600 rounded-xl"><Clock class="w-6 h-6" /></div>
                            <div>
                                <p class="text-sm font-medium text-slate-500">Antrean TTE</p>
                                <p class="text-2xl font-bold text-slate-900">{{ dashboardData.stats.pending_tte }}</p>
                            </div>
                        </div>
                        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
                            <div class="p-3 bg-green-50 text-green-600 rounded-xl"><CheckCircle class="w-6 h-6" /></div>
                            <div>
                                <p class="text-sm font-medium text-slate-500">TTE Berhasil</p>
                                <p class="text-2xl font-bold text-slate-900">{{ dashboardData.stats.success_tte }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- 2. GRAFIK AKTIVITAS TTE (7 HARI) -->
                    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="font-bold text-slate-800">Aktivitas TTE Berhasil (7 Hari Terakhir)</h3>
                        </div>
                        <div class="h-72 w-full">
                            <Bar :data="chartData" :options="chartOptions" />
                        </div>
                    </div>
                </template>

                <!-- ============================ -->
                <!-- TAMPILAN KHUSUS USER/PEGAWAI -->
                <!-- ============================ -->
                <template v-else>
                    <!-- 1. KARTU STATISTIK USER -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-gradient-to-br from-blue-600 to-blue-800 p-6 rounded-2xl shadow-sm text-white flex items-center justify-between">
                            <div>
                                <p class="text-blue-100 font-medium mb-1">Perlu Ditandatangani</p>
                                <p class="text-4xl font-bold">{{ dashboardData.stats.need_my_sign }}</p>
                            </div>
                            <PenTool class="w-12 h-12 text-blue-300 opacity-50" />
                        </div>
                        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
                            <div class="p-3 bg-slate-50 text-slate-600 rounded-xl"><FileText class="w-6 h-6" /></div>
                            <div>
                                <p class="text-sm font-medium text-slate-500">Dokumen Unggahan Saya</p>
                                <p class="text-2xl font-bold text-slate-900">{{ dashboardData.stats.my_docs }}</p>
                            </div>
                        </div>
                        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
                            <div class="p-3 bg-green-50 text-green-600 rounded-xl"><CheckCircle class="w-6 h-6" /></div>
                            <div>
                                <p class="text-sm font-medium text-slate-500">Riwayat TTE Saya</p>
                                <p class="text-2xl font-bold text-slate-900">{{ dashboardData.stats.my_signed }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- 2. SHORTCUT: 5 PERMINTAAN TTE MENDESAK -->
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                            <h3 class="font-bold text-slate-800 flex items-center gap-2">
                                <Clock class="w-5 h-5 text-amber-500" /> Antrean TTE Mendesak
                            </h3>
                            <Link :href="route('documents.requests')" class="text-sm text-blue-600 font-semibold hover:underline flex items-center gap-1">
                                Lihat Semua <ArrowRight class="w-4 h-4" />
                            </Link>
                        </div>
                        <div class="overflow-x-auto">
                            <table v-if="dashboardData.urgent_requests.length > 0" class="min-w-full divide-y divide-slate-200">
                                <tbody class="divide-y divide-slate-100">
                                    <tr v-for="req in dashboardData.urgent_requests" :key="req.id" class="hover:bg-slate-50">
                                        <td class="px-6 py-4">
                                            <p class="text-sm font-bold text-slate-900">{{ req.document.title }}</p>
                                            <p class="text-xs text-slate-500">Dari: {{ req.document.owner.name }}</p>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-500 text-right">
                                            {{ formatDate(req.created_at) }}
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <Link :href="route('documents.requests')" class="bg-blue-100 hover:bg-blue-200 text-blue-700 px-3 py-1.5 rounded-lg text-xs font-bold transition-colors">
                                                Buka Kotak Masuk
                                            </Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div v-else class="p-8 text-center text-slate-500">
                                <CheckCircle class="w-10 h-10 mx-auto text-green-400 mb-2" />
                                <p class="font-medium">Kerja bagus! Tidak ada dokumen yang menumpuk.</p>
                            </div>
                        </div>
                    </div>
                </template>

            </div>
        </div>
    </AuthenticatedLayout>
</template>