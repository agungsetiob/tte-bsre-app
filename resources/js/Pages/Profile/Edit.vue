<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
    bsreStatus?: string;
}>();

const getStatusStyle = (status: string | undefined) => {
    switch (status) {
        case 'ISSUE': return 'bg-green-100 text-green-700 border-green-200';
        case 'NEW':
        case 'RENEW':
        case 'WAITING_FOR_VERIFICATION': return 'bg-amber-100 text-amber-700 border-amber-200';
        case 'EXPIRED':
        case 'SUSPEND':
        case 'REVOKE': return 'bg-red-100 text-red-700 border-red-200';
        default: return 'bg-slate-100 text-slate-600 border-slate-200';
    }
};

const getStatusLabel = (status: string | undefined) => {
    switch (status) {
        case 'ISSUE': return 'Aktif - Siap Digunakan';
        case 'NEW': return 'Baru - Belum Aktivasi';
        case 'WAITING_FOR_VERIFICATION': return 'Menunggu Verifikasi';
        case 'RENEW': return 'Proses Pembaruan';
        case 'EXPIRED': return 'Sertifikat Kadaluarsa';
        case 'SUSPEND': return 'Akun Disuspend';
        case 'REVOKE': return 'Sertifikat Dicabut';
        case 'NO_CERTIFICATE': return 'Belum Memiliki Sertifikat';
        case 'NOT_REGISTERED': return 'Belum Terdaftar di BSrE';
        case 'ERROR_KONEKSI': return 'Gagal Cek ke Server BSrE';
        default: return status || 'Tidak Diketahui';
    }
};
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                Profile
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Status Sertifikat TTE (BSrE BSSN)</h3>
                        <p class="mt-1 text-sm text-gray-600">Status keaktifan tanda tangan elektronik Anda saat ini.</p>
                    </div>
                    <div :class="getStatusStyle(bsreStatus)" class="px-4 py-2 border rounded-full font-bold text-sm tracking-wide">
                        {{ bsreStatus }} : {{ getStatusLabel(bsreStatus) }}
                    </div>
                </div>
                <div
                    class="bg-white p-4 shadow sm:rounded-lg sm:p-8"
                >
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>

                <div
                    class="bg-white p-4 shadow sm:rounded-lg sm:p-8"
                >
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <div
                    class="bg-white p-4 shadow sm:rounded-lg sm:p-8"
                >
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
