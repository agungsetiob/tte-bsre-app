<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';
import { 
    FileDigit, 
    ClipboardList,
    Copy, 
    Check,
    SendHorizontal
} from 'lucide-vue-next';

const props = defineProps<{
    classifications: Array<{ id: number; code: string; name: string }>;
    letterTypes: Array<{ id: number; code: string; name: string }>;
    history: { data: Array<any>; links: Array<any> };
    userDepartment: string;
}>();

const form = useForm({
    classification_id: '',
    letter_type_id: '',
    purpose: '',
    letter_date: new Date().toISOString().split('T')[0],
});

const submit = () => {
    form.post(route('letter-numbers.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        }
    });
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
};

const formatLetterDate = (date: string) => {
    if (!date) return "-";

    return new Date(date).toLocaleDateString("id-ID", {
        day: "numeric",
        month: "long",
        year: "numeric",
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
    <Head title="Buku Agenda Surat Keluar" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="font-display text-xl font-bold leading-tight text-[#0F2A2E]">
                    SIPESAT
                </h2>
                <p class="mt-1 text-xs text-[#6A8593]">
                    Sistem Penomoran Surat Otomatis
                </p>
            </div>
        </template>

        <div class="mx-auto w-full max-w-8xl space-y-6 px-5 py-8 text-[#0F2A2E] md:px-8">

            <!-- GRID LAYOUT -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- KOLOM KIRI: FORM GENERATOR -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="rounded-2xl border border-[#DCE8E5] bg-white p-6 shadow-[0_18px_40px_-32px_rgba(15,42,46,.35)]">
                        <div class="flex items-center gap-3 mb-5 border-b border-[#EEF4F2] pb-4">
                            <div class="grid h-10 w-10 place-items-center rounded-xl bg-[#F2FBF8] text-[#0B8570]">
                                <FileDigit class="h-5 w-5" />
                            </div>
                            <div>
                                <h3 class="font-display text-base font-bold text-[#0F2A2E]">Ambil Nomor Baru</h3>
                                <p class="text-xs text-[#6A8593]">Unit: <span class="font-semibold text-[#0B8570]">{{ userDepartment }}</span></p>
                            </div>
                        </div>

                        <form @submit.prevent="submit" class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-[#6A8593]">Tanggal Surat</label>
                                <input v-model="form.letter_date" type="date" required class="mt-1.5 block w-full rounded-xl border border-[#D9E6E3] bg-[#F8FCFB] px-3 py-2.5 text-sm text-[#0F2A2E] focus:border-[#0B8570] focus:outline-none focus:ring-0" />
                                <p v-if="form.errors.letter_date" class="mt-1 text-xs text-rose-600">{{ form.errors.letter_date }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-[#6A8593]">Perihal</label>
                                <textarea v-model="form.purpose" required rows="2" placeholder="Contoh: Undangan Rapat Koordinasi Bulanan..." class="mt-1.5 block w-full rounded-xl border border-[#D9E6E3] bg-[#F8FCFB] px-3 py-2.5 text-sm text-[#0F2A2E] placeholder:text-[#A8BCC2] focus:border-[#0B8570] focus:outline-none focus:ring-0"></textarea>
                                <p v-if="form.errors.purpose" class="mt-1 text-xs text-rose-600">{{ form.errors.purpose }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-[#6A8593]">Klasifikasi Keamanan</label>
                                <select v-model="form.classification_id" required class="mt-1.5 block w-full rounded-xl border border-[#D9E6E3] bg-[#F8FCFB] px-3 py-2.5 text-sm text-[#0F2A2E] focus:border-[#0B8570] focus:outline-none focus:ring-0">
                                    <option value="" disabled>-- Pilih Klasifikasi --</option>
                                    <option v-for="cls in classifications" :key="cls.id" :value="cls.id">
                                        [{{ cls.code }}] {{ cls.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.classification_id" class="mt-1 text-xs text-rose-600">{{ form.errors.classification_id }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-[#6A8593]">Jenis Arsip Surat</label>
                                <select v-model="form.letter_type_id" required class="mt-1.5 block w-full rounded-xl border border-[#D9E6E3] bg-[#F8FCFB] px-3 py-2.5 text-sm text-[#0F2A2E] focus:border-[#0B8570] focus:outline-none focus:ring-0">
                                    <option value="" disabled>-- Pilih Jenis Surat --</option>
                                    <option v-for="type in letterTypes" :key="type.id" :value="type.id">
                                        [{{ type.code }}] {{ type.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.letter_type_id" class="mt-1 text-xs text-rose-600">{{ form.errors.letter_type_id }}</p>
                            </div>

                            <button type="submit" :disabled="form.processing" class="w-full mt-2 inline-flex justify-center items-center gap-2 rounded-xl bg-[#0B8570] px-4 py-3 text-sm font-semibold text-white shadow-[0_18px_40px_-24px_rgba(11,133,112,.8)] transition-all hover:-translate-y-0.5 hover:bg-[#086B5B] disabled:opacity-50">
                                {{ form.processing ? 'Memproses...' : 'Generate Nomor' }}
                                <SendHorizontal class="h-4 w-4" />
                            </button>
                        </form>
                    </div>
                </div>

                <!-- KOLOM KANAN: TABEL RIWAYAT -->
                <div class="lg:col-span-2">
                    <div class="rounded-2xl border border-[#DCE8E5] bg-white shadow-[0_18px_40px_-32px_rgba(15,42,46,.35)] overflow-hidden">
                        <div class="px-6 py-4 border-b border-[#EEF4F2] flex items-center gap-2 bg-[#F8FCFB]">
                            <ClipboardList class="h-5 w-5 text-[#6A8593]" />
                            <h3 class="font-display text-sm font-bold text-[#0F2A2E]">Riwayat Pengambilan Nomor Saya</h3>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead class="bg-white text-[#0F2A2E]">
                                    <tr class="border-b border-[#E9F0EE]">
                                        <th class="px-6 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-[#6A8593]">Tanggal</th>
                                        <th class="px-6 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-[#6A8593]">Perihal</th>
                                        <th class="px-6 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-[#6A8593]">Nomor Surat</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#EEF4F2]">
                                    <tr v-if="history.data.length === 0">
                                        <td colspan="3" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center">
                                                <FileDigit class="h-8 w-8 text-[#A8BCC2] mb-2" />
                                                <p class="text-sm font-bold text-[#0F2A2E]">Belum ada riwayat</p>
                                                <p class="text-xs text-[#6A8593]">Anda belum pernah mengambil nomor surat.</p>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-for="item in history.data" :key="item.id" class="transition-colors hover:bg-[#F8FCFB]">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <p class="text-sm font-bold text-slate-800">{{ formatLetterDate(item.letter_date) }}</p>
                                            <p class="text-[10px] text-slate-500">Dibuat: {{ formatDate(item.created_at) }}</p>
                                        </td>
                                        <td class="px-6 py-4">
                                            <p class="text-sm font-semibold text-[#0F2A2E]">{{ item.purpose }}</p>
                                            <div class="flex gap-2 mt-1">
                                                <span class="inline-flex rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-medium text-slate-600">{{ item.classification?.name }}</span>
                                                <span class="inline-flex rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-medium text-slate-600">{{ item.letter_type?.name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-bold font-mono text-[#0B8570] bg-[#EAF7F3] px-2 py-1 rounded border border-[#BCE5DA]">
                                                    {{ item.full_number }}
                                                </span>
                                                <button 
                                                    @click="copyToClipboard(item.full_number, item.id)"
                                                    class="p-1.5 rounded-lg text-[#6A8593] hover:text-[#0B8570] hover:bg-[#EAF7F3] transition-colors"
                                                    :title="copiedId === item.id ? 'Tersalin!' : 'Salin Nomor'"
                                                >
                                                    <Check v-if="copiedId === item.id" class="h-4 w-4 text-[#0B8570]" />
                                                    <Copy v-else class="h-4 w-4" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="history.links && history.links.length > 3" class="px-6 py-4 border-t border-[#EEF4F2] bg-[#F8FCFB] flex justify-center">
                            <div class="inline-flex items-center gap-1 rounded-xl border border-[#DCE8E5] bg-white p-1">
                                <template v-for="(link, k) in history.links" :key="k">
                                    <div v-if="link.url === null" class="cursor-not-allowed rounded-lg px-3 py-1.5 text-xs font-semibold text-[#A8BCC2]" v-html="link.label" />
                                    <Link v-else :href="link.url" preserve-scroll :class="link.active ? 'bg-[#0B8570] text-white' : 'text-[#4A6572] hover:bg-[#F2FBF8] hover:text-[#0B7563]'" class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-colors" v-html="link.label" />
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>