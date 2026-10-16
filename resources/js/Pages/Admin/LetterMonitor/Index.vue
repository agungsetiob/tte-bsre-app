<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, watch } from 'vue';
import { 
    Search, 
    Filter, 
    Building2, 
    User, 
    FileDigit, 
    CalendarDays, 
    ClipboardList,
    Copy,
    Check,
    Download
} from 'lucide-vue-next';

const props = defineProps<{
    letters: { data: Array<any>; links: Array<any> };
    filters: { search?: string; department_id?: string };
    departments: Array<{ id: number; name: string }>;
}>();

const search = ref(props.filters.search || '');
const departmentFilter = ref(props.filters.department_id || '');

// Watcher untuk Filter & Search dengan Debounce
let searchTimeout: any = null;
watch([search, departmentFilter], ([newSearch, newDept]) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(
            route('admin.letter_monitor.index'),
            { search: newSearch, department_id: newDept },
            { preserveState: true, replace: true }
        );
    }, 500);
});

const formatLetterDate = (dateString: string) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: '2-digit', month: 'long', year: 'numeric'
    });
};

const formatDateTime = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
};

// Fitur Copy to Clipboard
const copiedId = ref<number | null>(null);
const copyToClipboard = (text: string, id: number) => {
    navigator.clipboard.writeText(text);
    copiedId.value = id;
    setTimeout(() => {
        copiedId.value = null;
    }, 2000);
};
</script>

<template>
    <Head title="Monitor Agenda Surat" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="font-display text-xl font-bold leading-tight text-slate-800">
                    Monitor Agenda Surat
                </h2>
                <p class="mt-1 text-xs text-slate-500">
                    Pantau seluruh riwayat penomoran surat keluar dari semua unit kerja.
                </p>
            </div>
        </template>

        <div class="mx-auto w-full max-w-8xl space-y-6 px-5 py-8 md:px-8">
            
            <!-- FILTER & SEARCH BAR -->
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row">
                    <div class="relative flex-1">
                        <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        <input 
                            v-model="search" 
                            type="text" 
                            placeholder="Cari nomor surat atau perihal..." 
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-9 pr-3 text-sm text-slate-800 placeholder:text-slate-400 focus:border-blue-500 focus:ring-0" 
                        />
                    </div>
                    <div class="relative">
                        <a 
                            :href="route('admin.letter_monitor.export', { search: search, department_id: departmentFilter })"
                            target="_blank"
                            class="inline-flex justify-center w-full items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-emerald-700 hover:-translate-y-0.5"
                        >
                            <Download class="h-4 w-4" /> Ekspor ke Excel
                        </a>
                    </div>
                    <div class="relative sm:w-72">
                        <Filter class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        <select 
                            v-model="departmentFilter" 
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-9 pr-8 text-sm text-slate-800 focus:border-blue-500 focus:ring-0"
                        >
                            <option value="">Semua Unit Kerja</option>
                            <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                {{ dept.name }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- TABEL MONITOR -->
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-2 bg-slate-50">
                    <ClipboardList class="h-5 w-5 text-slate-500" />
                    <h3 class="font-display text-sm font-bold text-slate-800">Buku Agenda Global</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-white text-slate-800">
                            <tr class="border-b border-slate-200">
                                <th class="px-6 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Nomor & Perihal</th>
                                <th class="px-6 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Tanggal</th>
                                <th class="px-6 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Unit Kerja & Peminta</th>
                                <th class="px-6 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Klasifikasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <!-- Empty State -->
                            <tr v-if="letters.data.length === 0">
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <FileDigit class="h-8 w-8 text-slate-300 mb-2" />
                                        <p class="text-sm font-bold text-slate-800">Tidak ada data</p>
                                        <p class="text-xs text-slate-500">Belum ada surat yang diterbitkan atau filter tidak cocok.</p>
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- Rows -->
                            <tr v-for="letter in letters.data" :key="letter.id" class="transition-colors hover:bg-slate-50">
                                <!-- Nomor & Perihal -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-sm font-bold font-mono text-blue-700 bg-blue-50 px-2 py-1 rounded border border-blue-100">
                                            {{ letter.full_number }}
                                        </span>
                                        <button 
                                            @click="copyToClipboard(letter.full_number, letter.id)"
                                            class="p-1 rounded text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                                            :title="copiedId === letter.id ? 'Tersalin!' : 'Salin Nomor'"
                                        >
                                            <Check v-if="copiedId === letter.id" class="h-3.5 w-3.5 text-blue-600" />
                                            <Copy v-else class="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-800 line-clamp-2" :title="letter.purpose">
                                        {{ letter.purpose }}
                                    </p>
                                </td>

                                <!-- Tanggal -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5 text-sm font-bold text-slate-800">
                                        <CalendarDays class="h-4 w-4 text-slate-400" />
                                        {{ formatLetterDate(letter.letter_date) }}
                                    </div>
                                    <div class="mt-1 text-[10px] text-slate-500 pl-5">
                                        Dibuat: {{ formatDateTime(letter.created_at) }}
                                    </div>
                                </td>

                                <!-- Unit Kerja -->
                                <td class="px-6 py-4">
                                    <div class="flex items-start gap-2">
                                        <Building2 class="h-4 w-4 text-slate-400 mt-0.5 shrink-0" />
                                        <div>
                                            <p class="text-sm font-semibold text-slate-800">{{ letter.department?.name }}</p>
                                            <div class="flex items-center gap-1 mt-0.5 text-[11px] text-slate-500">
                                                <User class="h-3 w-3" /> {{ letter.user?.name }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Klasifikasi -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex rounded-lg bg-slate-100 px-2 py-1 text-[11px] font-medium text-slate-600 border border-slate-200">
                                        {{ letter.classification?.name }}
                                    </span>
                                    <div class="mt-1">
                                        <span class="inline-flex rounded-lg bg-slate-100 px-2 py-1 text-[11px] font-medium text-slate-600 border border-slate-200">
                                            {{ letter.letter_type?.name }}
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="letters.links && letters.links.length > 3" class="px-6 py-4 border-t border-[#EEF4F2] bg-[#F8FCFB] flex justify-center">
                    <div class="inline-flex items-center gap-1 rounded-xl border border-[#DCE8E5] bg-white p-1">
                        <template v-for="(link, k) in letters.links" :key="k">
                            <div v-if="link.url === null" class="cursor-not-allowed rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-400" v-html="link.label" />
                            <Link v-else :href="link.url" preserve-scroll :class="link.active ? 'bg-[#0B8570] text-white' : 'text-[#4A6572] hover:bg-[#F2FBF8] hover:text-[#0B7563]'" class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-colors" v-html="link.label" />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>