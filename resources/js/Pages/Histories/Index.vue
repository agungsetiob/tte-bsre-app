<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps<{
    histories: any; // Menggunakan any untuk simplifikasi paginasi Inertia
}>();

const form = useForm({});

const deleteHistory = (id: number) => {
    if (confirm('Yakin ingin menghapus histori ini?')) {
        form.delete(route('histories.destroy', id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Histori TTE" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Histori Tanda Tangan Elektronik</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dokumen</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan API</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Opsi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="history in histories.data" :key="history.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ new Date(history.created_at).toLocaleString('id-ID') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ history.document?.title || 'Dokumen Dihapus' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ history.action }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span :class="history.status === 'success' ? 'text-green-600 font-bold' : 'text-red-600 font-bold'">
                                        {{ history.status.toUpperCase() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" :title="history.response_message">
                                    {{ history.response_message }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button @click="deleteHistory(history.id)" class="text-red-600 hover:text-red-900">
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div v-if="histories.data.length === 0" class="text-center py-4 text-gray-500">
                        Belum ada histori TTE.
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>