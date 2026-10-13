<script setup lang="ts">
import axios from 'axios';
import { Head, useForm, usePage, router, Link } from '@inertiajs/vue3'; // Tambahkan router & Link
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ShieldCheck, User as UserIcon, CheckCircle, XCircle } from 'lucide-vue-next';
import { computed, ref, watch, onMounted } from 'vue';

// PERBAIKAN 1: Struktur Props Pagination
const props = defineProps<{
    users: {
        data: Array<{
            id: number;
            name: string;
            email: string;
            nik: string;
            role: string;
            is_active: boolean;
            created_at: string;
            bsre_status: string | null;
        }>;
        links: Array<any>;
    };
    filters: {
        search?: string;
        is_active?: string;
        bsre_status?: string;
    };
}>();

const page = usePage();
const flashSuccess = computed(() => (page.props.flash as any)?.success);
const errorMessage = computed(() => (page.props.errors as any)?.message);

// State filter
const search = ref(props.filters.search || '');
const isActiveFilter = ref(props.filters.is_active ?? '');
const bsreStatusFilter = ref(props.filters.bsre_status || '');

// Watcher untuk Filter & Pencarian
let searchTimeout: any = null;
watch([search, isActiveFilter, bsreStatusFilter], ([newSearch, newActive, newBsre]) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('admin.users.index'), {
            search: newSearch,
            is_active: newActive,
            bsre_status: newBsre
        }, { preserveState: true, replace: true });
    }, 500);
});

const form = useForm({});

const toggleStatus = (userId: number) => {
    form.patch(route('admin.users.toggle', userId), {
        preserveScroll: true,
    });
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
};

const userStatuses = ref<Record<number, string>>({});
const isChecking = ref<Record<number, boolean>>({});

const checkStatus = async (userId: number) => {
    isChecking.value[userId] = true;
    try {
        const response = await axios.post(route('users.check_bsre', userId));
        userStatuses.value[userId] = response.data.status;
    } catch (error: any) {
        userStatuses.value[userId] = error.response?.data?.status || 'ERROR_KONEKSI';
    } finally {
        isChecking.value[userId] = false;
    }
};

const autoCheckAll = async () => {
    for (const user of props.users.data) {
        if (!userStatuses.value[user.id] && user.nik) {
            await checkStatus(user.id);
            await new Promise(resolve => setTimeout(resolve, 200)); 
        }
    }
};

onMounted(() => {
    autoCheckAll();
});

watch(() => props.users.data, () => {
    autoCheckAll();
});

const getStatusBadge = (status: string) => {
    switch (status) {
        case 'ISSUE': return 'bg-green-100 text-green-700';
        case 'NEW':
        case 'RENEW':
        case 'WAITING_FOR_VERIFICATION': return 'bg-amber-100 text-amber-700';
        case 'EXPIRED':
        case 'SUSPEND':
        case 'REVOKE': return 'bg-red-100 text-red-700';
        default: return 'bg-slate-100 text-slate-600';
    }
};
</script>

<template>
    <Head title="Manajemen Pegawai" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">Manajemen Akun Pegawai</h2>
        </template>

        <div class="space-y-6">
            <!-- Pesan Flash -->
            <div v-if="flashSuccess"
                class="p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg flex items-center gap-2 shadow-sm">
                <CheckCircle class="w-5 h-5" />
                <span class="font-medium">{{ flashSuccess }}</span>
            </div>

            <div v-if="errorMessage"
                class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg flex items-center gap-2 shadow-sm">
                <XCircle class="w-5 h-5" />
                <span class="font-medium">{{ errorMessage }}</span>
            </div>

            <div class="mb-6 flex flex-col sm:flex-row gap-4 justify-between bg-white p-4 rounded-xl shadow-sm border border-slate-100">
                <!-- Pencarian -->
                <div class="w-full sm:w-1/3">
                    <input type="text" v-model="search" placeholder="Cari nama, email, NIK..." class="w-full rounded-lg border-slate-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <!-- Filter -->
                <div class="flex gap-2 w-full sm:w-auto">
                    <select v-model="isActiveFilter" class="rounded-lg border-slate-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Akun</option>
                        <option value="1">Akun Aktif</option>
                        <option value="0">Akun Nonaktif</option>
                    </select>

                    <select v-model="bsreStatusFilter" class="rounded-lg border-slate-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Status TTE</option>
                        <option value="ISSUE">ISSUE (Aktif)</option>
                        <option value="RENEW">RENEW (Perpanjangan)</option>
                        <option value="NEW">NEW (Baru)</option>
                        <option value="EXPIRED">EXPIRED</option>
                        <option value="NOT_REGISTERED">NOT REGISTERED</option>
                        <option value="WAITING_FOR_VERIFICATION">WAITING FOR VERIFICATION</option>
                        <option value="NO_CERTIFICATE">NO CERTIFICATE</option>
                        <option value="SUSPEND">SUSPEND</option>
                        <option value="REVOKE">REVOKE</option>
                    </select>
                </div>
            </div>

            <!-- Tabel Pengguna -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden pb-4">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Pegawai</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">NIK</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Peran</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Status Akses</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Tgl Daftar</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status TTE</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            <!-- PERBAIKAN 3: Loop menggunakan users.data -->
                            <tr v-for="user in users.data" :key="user.id" class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 shrink-0 rounded-full bg-slate-100 flex items-center justify-center border border-slate-200 text-slate-500 font-bold">
                                            {{ user.name.charAt(0) }}
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-semibold text-slate-900">{{ user.name }}</div>
                                            <div class="text-sm text-slate-500">{{ user.email }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 font-medium">
                                    {{ user.nik }}
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span v-if="user.role === 'admin'" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        <ShieldCheck class="w-3.5 h-3.5" /> Admin
                                    </span>
                                    <span v-else class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        <UserIcon class="w-3.5 h-3.5" /> User
                                    </span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span v-if="user.is_active" class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                        Aktif
                                    </span>
                                    <span v-else class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800 border border-yellow-200">
                                        Menunggu Persetujuan
                                    </span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                    {{ formatDate(user.created_at) }}
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span v-if="userStatuses[user.id] || user.bsre_status" :class="getStatusBadge(userStatuses[user.id] || user.bsre_status || '')" class="px-2 py-1 text-xs font-bold rounded-full">
                                            {{ userStatuses[user.id] || user.bsre_status }}
                                        </span>
                                        <span v-else class="px-2 py-1 text-xs font-medium rounded-full bg-slate-100 text-slate-500">
                                            Belum Dicek
                                        </span>

                                        <svg v-if="isChecking[user.id]" class="animate-spin h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        
                                        <button v-else-if="!isChecking[user.id] && (userStatuses[user.id] === 'ERROR_KONEKSI')" @click="checkStatus(user.id)" class="text-[10px] text-blue-600 hover:underline">
                                            Coba Lagi
                                        </button>
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button @click="toggleStatus(user.id)"
                                        :disabled="form.processing || user.id === $page.props.auth.user.id"
                                        class="px-4 py-1.5 rounded-lg font-semibold text-xs transition-colors disabled:opacity-50"
                                        :class="user.is_active ? 'bg-red-50 text-red-700 hover:bg-red-100 border border-red-200' : 'bg-blue-600 text-white hover:bg-blue-700'">
                                        {{ user.is_active ? 'Nonaktifkan' : 'Setujui & Aktifkan' }}
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div v-if="users.links.length > 3" class="mt-6 flex justify-center">
                    <div class="flex flex-wrap gap-1">
                        <template v-for="(link, k) in users.links" :key="k">
                            <div v-if="link.url === null" class="px-3 py-1.5 text-sm text-slate-400 border border-slate-200 rounded-lg bg-slate-50" v-html="link.label"></div>
                            <Link v-else :href="link.url" :class="link.active ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-50'" class="px-3 py-1.5 text-sm border rounded-lg transition-colors" v-html="link.label" preserve-scroll />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>