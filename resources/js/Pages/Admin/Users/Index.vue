<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ShieldCheck, User as UserIcon, CheckCircle, XCircle } from 'lucide-vue-next';
import { computed } from 'vue';

defineProps<{
    users: Array<{
        id: number;
        name: string;
        email: string;
        nik: string;
        role: string;
        is_active: boolean;
        created_at: string;
    }>;
}>();

const page = usePage();
const flashSuccess = computed(() => (page.props.flash as any)?.success);
const errorMessage = computed(() => (page.props.errors as any)?.message);

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
</script>

<template>
    <Head title="Manajemen Pegawai" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">Manajemen Akun Pegawai</h2>
        </template>

        <div class="space-y-6">
            <!-- Pesan Flash -->
            <div v-if="flashSuccess" class="p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg flex items-center gap-2 shadow-sm">
                <CheckCircle class="w-5 h-5" />
                <span class="font-medium">{{ flashSuccess }}</span>
            </div>
            
            <div v-if="errorMessage" class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg flex items-center gap-2 shadow-sm">
                <XCircle class="w-5 h-5" />
                <span class="font-medium">{{ errorMessage }}</span>
            </div>

            <!-- Tabel Pengguna -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Pegawai</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">NIK</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Peran</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Status Akses</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Tgl Daftar</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            <tr v-for="user in users" :key="user.id" class="hover:bg-slate-50 transition-colors">
                                <!-- Info Profil -->
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
                                
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button 
                                        @click="toggleStatus(user.id)" 
                                        :disabled="form.processing || user.id === $page.props.auth.user.id"
                                        class="px-4 py-1.5 rounded-lg font-semibold text-xs transition-colors disabled:opacity-50"
                                        :class="user.is_active ? 'bg-red-50 text-red-700 hover:bg-red-100 border border-red-200' : 'bg-blue-600 text-white hover:bg-blue-700'"
                                    >
                                        {{ user.is_active ? 'Nonaktifkan' : 'Setujui & Aktifkan' }}
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>