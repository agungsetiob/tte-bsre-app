<script setup lang="ts">
import axios from 'axios';
import { Head, useForm, usePage, router, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    ShieldCheck,
    User as UserIcon,
    CheckCircle2,
    XCircle,
    Search,
    Filter,
    ChevronDown,
    ShieldAlert,
    RefreshCw,
    Clock4,
    Ban,
    UserCheck,
    Loader2,
    AlertCircle,
} from 'lucide-vue-next';
import { computed, ref, watch, onMounted } from 'vue';

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

// ============ FILTER STATE ============
const search = ref(props.filters.search || '');
const isActiveFilter = ref(props.filters.is_active ?? '');
const bsreStatusFilter = ref(props.filters.bsre_status || '');

let searchTimeout: any = null;
watch(
    [search, isActiveFilter, bsreStatusFilter],
    ([newSearch, newActive, newBsre]) => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            router.get(
                route('admin.users.index'),
                {
                    search: newSearch || undefined,
                    is_active: newActive || undefined,
                    bsre_status: newBsre || undefined,
                },
                { preserveState: true, replace: true, preserveScroll: true }
            );
        }, 500);
    }
);

// ============ TOGGLE STATUS ============
const form = useForm({});
const toggleStatus = (userId: number) => {
    form.patch(route('admin.users.toggle', userId), {
        preserveScroll: true,
    });
};

// ============ BSrE AUTO CHECK ============
const userStatuses = ref<Record<number, string>>({});
const isChecking = ref<Record<number, boolean>>({});

const checkStatus = async (userId: number) => {
    isChecking.value[userId] = true;
    try {
        const response = await axios.post(route('users.check_bsre', userId));
        userStatuses.value[userId] = response.data.status;
    } catch (error: any) {
        userStatuses.value[userId] =
            error.response?.data?.status || 'ERROR_KONEKSI';
    } finally {
        isChecking.value[userId] = false;
    }
};

const autoCheckAll = async () => {
    for (const user of props.users.data) {
        if (!userStatuses.value[user.id] && user.nik) {
            await checkStatus(user.id);
            await new Promise((resolve) => setTimeout(resolve, 200));
        }
    }
};

onMounted(() => {
    autoCheckAll();
});

watch(
    () => props.users.data,
    () => {
        autoCheckAll();
    }
);

// ============ HELPERS ============
const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
};

type StatusMeta = {
    label: string;
    cls: string;
    dot: string;
    Icon: any;
};

const bsreStatusMap: Record<string, StatusMeta> = {
    ISSUE: {
        label: 'Aktif',
        cls: 'bg-[#EAF7F3] text-[#0B7563] border-[#BCE5DA]',
        dot: 'bg-[#0B8570]',
        Icon: ShieldCheck,
    },
    NEW: {
        label: 'Baru',
        cls: 'bg-amber-50 text-amber-700 border-amber-200',
        dot: 'bg-amber-500',
        Icon: AlertCircle,
    },
    RENEW: {
        label: 'Perpanjangan',
        cls: 'bg-amber-50 text-amber-700 border-amber-200',
        dot: 'bg-amber-500',
        Icon: RefreshCw,
    },
    WAITING_FOR_VERIFICATION: {
        label: 'Menunggu Verifikasi',
        cls: 'bg-amber-50 text-amber-700 border-amber-200',
        dot: 'bg-amber-500',
        Icon: Clock4,
    },
    EXPIRED: {
        label: 'Kadaluarsa',
        cls: 'bg-rose-50 text-rose-700 border-rose-200',
        dot: 'bg-rose-500',
        Icon: ShieldAlert,
    },
    SUSPEND: {
        label: 'Suspend',
        cls: 'bg-rose-50 text-rose-700 border-rose-200',
        dot: 'bg-rose-500',
        Icon: Ban,
    },
    REVOKE: {
        label: 'Dicabut',
        cls: 'bg-rose-50 text-rose-700 border-rose-200',
        dot: 'bg-rose-500',
        Icon: XCircle,
    },
    NOT_REGISTERED: {
        label: 'Belum Terdaftar',
        cls: 'bg-slate-50 text-slate-700 border-slate-200',
        dot: 'bg-slate-400',
        Icon: AlertCircle,
    },
    NO_CERTIFICATE: {
        label: 'Tanpa Sertifikat',
        cls: 'bg-slate-50 text-slate-700 border-slate-200',
        dot: 'bg-slate-400',
        Icon: AlertCircle,
    },
    ERROR_KONEKSI: {
        label: 'Gagal Cek',
        cls: 'bg-rose-50 text-rose-700 border-rose-200',
        dot: 'bg-rose-500',
        Icon: AlertCircle,
    },
};

const getStatusMeta = (status?: string | null): StatusMeta => {
    if (!status)
        return {
            label: 'Belum Dicek',
            cls: 'bg-slate-50 text-slate-600 border-slate-200',
            dot: 'bg-slate-400',
            Icon: Clock4,
        };
    return (
        bsreStatusMap[status] || {
            label: status,
            cls: 'bg-slate-50 text-slate-700 border-slate-200',
            dot: 'bg-slate-400',
            Icon: AlertCircle,
        }
    );
};

const currentUserId = computed(() => (page.props.auth as any)?.user?.id);
</script>

<template>
    <Head title="Manajemen Pegawai" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <p
                    class="text-[11px] font-bold uppercase tracking-[0.16em] text-[#0B7563]"
                >
                    Administrasi
                </p>
                <h2
                    class="font-display mt-0.5 text-xl font-bold leading-tight text-[#0F2A2E]"
                >
                    Manajemen Akun Pegawai
                </h2>
                <p class="mt-0.5 text-xs text-[#6A8593]">
                    Kelola akses, peran, dan status sertifikat TTE pegawai rumah sakit.
                </p>
            </div>
        </template>

        <div
            class="mx-auto w-full max-w-8xl space-y-5 px-5 py-8 text-[#0F2A2E] md:px-8"
        >
            <!-- Flash -->
            <div
                v-if="flashSuccess"
                class="flex items-start gap-3 rounded-2xl border border-[#BCE5DA] bg-[#EAF7F3] p-4 text-sm text-[#0B7563] shadow-[0_18px_40px_-32px_rgba(15,42,46,.35)]"
            >
                <CheckCircle2 class="mt-0.5 h-4 w-4 shrink-0" />
                <span class="font-medium">{{ flashSuccess }}</span>
            </div>
            <div
                v-if="errorMessage"
                class="flex items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700 shadow-[0_18px_40px_-32px_rgba(15,42,46,.35)]"
            >
                <XCircle class="mt-0.5 h-4 w-4 shrink-0" />
                <span class="font-medium">{{ errorMessage }}</span>
            </div>

            <!-- Filter bar -->
            <div
                class="rounded-2xl border border-[#DCE8E5] bg-white p-4 shadow-[0_18px_40px_-32px_rgba(15,42,46,.35)]"
            >
                <div class="flex flex-col gap-3 lg:flex-row">
                    <div class="relative flex-1">
                        <Search
                            class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#6A8593]"
                        />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari nama, email, atau NIK..."
                            class="w-full rounded-xl border border-[#D9E6E3] bg-[#F8FCFB] py-2.5 pl-9 pr-3 text-sm text-[#0F2A2E] placeholder:text-[#A8BCC2] transition-colors focus:border-[#0B8570] focus:outline-none focus:ring-0"
                        />
                    </div>

                    <div class="relative lg:w-52">
                        <UserCheck
                            class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#6A8593]"
                        />
                        <select
                            v-model="isActiveFilter"
                            class="w-full appearance-none rounded-xl border border-[#D9E6E3] bg-[#F8FCFB] py-2.5 pl-9 pr-8 text-sm text-[#0F2A2E] transition-colors focus:border-[#0B8570] focus:outline-none focus:ring-0"
                        >
                            <option value="">Semua Akun</option>
                            <option value="1">Akun Aktif</option>
                            <option value="0">Menunggu Persetujuan</option>
                        </select>
                        <ChevronDown
                            class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#6A8593]"
                        />
                    </div>

                    <div class="relative lg:w-56">
                        <Filter
                            class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#6A8593]"
                        />
                        <select
                            v-model="bsreStatusFilter"
                            class="w-full appearance-none rounded-xl border border-[#D9E6E3] bg-[#F8FCFB] py-2.5 pl-9 pr-8 text-sm text-[#0F2A2E] transition-colors focus:border-[#0B8570] focus:outline-none focus:ring-0"
                        >
                            <option value="">Semua Status TTE</option>
                            <option value="ISSUE">ISSUE — Aktif</option>
                            <option value="RENEW">RENEW — Perpanjangan</option>
                            <option value="NEW">NEW — Baru</option>
                            <option value="EXPIRED">EXPIRED</option>
                            <option value="NOT_REGISTERED">NOT REGISTERED</option>
                            <option value="WAITING_FOR_VERIFICATION">
                                Menunggu Verifikasi
                            </option>
                            <option value="NO_CERTIFICATE">
                                Tanpa Sertifikat
                            </option>
                            <option value="SUSPEND">SUSPEND</option>
                            <option value="REVOKE">REVOKE</option>
                        </select>
                        <ChevronDown
                            class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#6A8593]"
                        />
                    </div>
                </div>
            </div>

            <!-- Table card -->
            <div
                class="overflow-hidden rounded-2xl border border-[#DCE8E5] bg-white shadow-[0_18px_40px_-32px_rgba(15,42,46,.35)]"
            >
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-[#E9F0EE] bg-[#F8FCFB]">
                                <th
                                    class="px-6 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                                >
                                    Pegawai
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                                >
                                    NIK
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                                >
                                    Peran
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                                >
                                    Status Akses
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                                >
                                    Terdaftar
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                                >
                                    Status TTE
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                                >
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#EEF4F2]">
                            <!-- Empty -->
                            <tr v-if="users.data.length === 0">
                                <td colspan="7" class="px-6 py-16 text-center">
                                    <div
                                        class="flex flex-col items-center gap-3"
                                    >
                                        <div
                                            class="grid h-14 w-14 place-items-center rounded-2xl bg-[#EAF7F3]"
                                        >
                                            <UserIcon
                                                class="h-7 w-7 text-[#0B7563]"
                                            />
                                        </div>
                                        <div>
                                            <p
                                                class="font-display text-sm font-bold text-[#0F2A2E]"
                                            >
                                                Tidak ada pegawai ditemukan
                                            </p>
                                            <p
                                                class="mt-1 text-xs text-[#6A8593]"
                                            >
                                                Sesuaikan filter atau kata kunci
                                                pencarian Anda.
                                            </p>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <!-- Rows -->
                            <tr
                                v-for="user in users.data"
                                :key="user.id"
                                class="transition-colors hover:bg-[#F8FCFB]"
                            >
                                <!-- Pegawai -->
                                <td class="px-6 py-4 align-top">
                                    <div class="flex items-start gap-3">
                                        <span
                                            class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-gradient-to-br from-[#0B8570] to-[#0B7563] text-sm font-bold text-white"
                                        >
                                            {{
                                                user.name
                                                    ?.charAt(0)
                                                    ?.toUpperCase()
                                            }}
                                        </span>
                                        <div class="min-w-0">
                                            <p
                                                class="truncate text-sm font-semibold text-[#0F2A2E]"
                                                :title="user.name"
                                            >
                                                {{ user.name }}
                                            </p>
                                            <p
                                                class="mt-0.5 truncate text-[11px] text-[#6A8593]"
                                                :title="user.email"
                                            >
                                                {{ user.email }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <!-- NIK -->
                                <td
                                    class="whitespace-nowrap px-6 py-4 align-top font-mono text-xs text-[#4A6572]"
                                >
                                    {{ user.nik || '—' }}
                                </td>

                                <!-- Peran -->
                                <td class="whitespace-nowrap px-6 py-4 align-top">
                                    <span
                                        v-if="user.role === 'admin'"
                                        class="inline-flex items-center gap-1 rounded-full border border-[#BCE5DA] bg-[#EAF7F3] px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider text-[#0B7563]"
                                    >
                                        <ShieldCheck class="h-3 w-3" />
                                        Admin
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center gap-1 rounded-full border border-[#E9F0EE] bg-[#F8FCFB] px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider text-[#4A6572]"
                                    >
                                        <UserIcon class="h-3 w-3" />
                                        User
                                    </span>
                                </td>

                                <!-- Status Akses -->
                                <td class="whitespace-nowrap px-6 py-4 align-top">
                                    <span
                                        v-if="user.is_active"
                                        class="inline-flex items-center gap-1.5 rounded-full border border-[#BCE5DA] bg-[#EAF7F3] px-2.5 py-1 text-[11px] font-semibold text-[#0B7563]"
                                    >
                                        <span
                                            class="h-1.5 w-1.5 rounded-full bg-[#0B8570]"
                                        />
                                        Aktif
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-[11px] font-semibold text-amber-700"
                                    >
                                        <span
                                            class="h-1.5 w-1.5 rounded-full bg-amber-500"
                                        />
                                        Menunggu Persetujuan
                                    </span>
                                </td>

                                <!-- Terdaftar -->
                                <td
                                    class="whitespace-nowrap px-6 py-4 align-top text-xs text-[#6A8593]"
                                >
                                    {{ formatDate(user.created_at) }}
                                </td>

                                <!-- Status TTE -->
                                <td class="whitespace-nowrap px-6 py-4 align-top">
                                    <div class="flex items-center gap-2">
                                        <!-- Spinner -->
                                        <span
                                            v-if="isChecking[user.id]"
                                            class="inline-flex items-center gap-1.5 rounded-full border border-[#E9F0EE] bg-[#F8FCFB] px-2.5 py-1 text-[11px] font-semibold text-[#6A8593]"
                                        >
                                            <Loader2 class="h-3 w-3 animate-spin" />
                                            Mengecek…
                                        </span>

                                        <!-- Hasil -->
                                        <template v-else>
                                            <span
                                                :class="
                                                    getStatusMeta(
                                                        userStatuses[user.id] ||
                                                            user.bsre_status
                                                    ).cls
                                                "
                                                class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[11px] font-semibold"
                                            >
                                                <span
                                                    class="h-1.5 w-1.5 rounded-full"
                                                    :class="
                                                        getStatusMeta(
                                                            userStatuses[
                                                                user.id
                                                            ] || user.bsre_status
                                                        ).dot
                                                    "
                                                />
                                                {{
                                                    getStatusMeta(
                                                        userStatuses[user.id] ||
                                                            user.bsre_status
                                                    ).label
                                                }}
                                            </span>

                                            <!-- Retry -->
                                            <button
                                                v-if="
                                                    userStatuses[user.id] ===
                                                    'ERROR_KONEKSI'
                                                "
                                                type="button"
                                                @click="checkStatus(user.id)"
                                                class="inline-flex items-center gap-1 rounded-lg border border-[#E9F0EE] bg-white px-2 py-0.5 text-[10px] font-semibold text-[#6A8593] transition-colors hover:border-[#BCE5DA] hover:bg-[#F2FBF8] hover:text-[#0B7563]"
                                            >
                                                <RefreshCw class="h-2.5 w-2.5" />
                                                Coba Lagi
                                            </button>
                                        </template>
                                    </div>
                                </td>

                                <!-- Aksi -->
                                <td
                                    class="whitespace-nowrap px-6 py-4 text-right align-top"
                                >
                                    <button
                                        type="button"
                                        @click="toggleStatus(user.id)"
                                        :disabled="
                                            form.processing ||
                                            user.id === currentUserId
                                        "
                                        :class="
                                            user.is_active
                                                ? 'border border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100'
                                                : 'bg-[#0B8570] text-white shadow-[0_18px_40px_-24px_rgba(11,133,112,.8)] hover:bg-[#086B5B]'
                                        "
                                        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition-all hover:-translate-y-0.5 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:translate-y-0"
                                        :title="
                                            user.id === currentUserId
                                                ? 'Tidak dapat mengubah status akun sendiri'
                                                : ''
                                        "
                                    >
                                        <Ban
                                            v-if="user.is_active"
                                            class="h-3.5 w-3.5"
                                        />
                                        <UserCheck v-else class="h-3.5 w-3.5" />
                                        {{
                                            user.is_active
                                                ? 'Nonaktifkan'
                                                : 'Setujui & Aktifkan'
                                        }}
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div
                v-if="users.links && users.links.length > 3"
                class="flex justify-center pt-2"
            >
                <div
                    class="inline-flex items-center gap-1 rounded-xl border border-[#DCE8E5] bg-white p-1 shadow-[0_18px_40px_-32px_rgba(15,42,46,.35)]"
                >
                    <template v-for="(link, k) in users.links" :key="k">
                        <div
                            v-if="link.url === null"
                            class="cursor-not-allowed rounded-lg px-3 py-1.5 text-xs font-semibold text-[#A8BCC2]"
                            v-html="link.label"
                        />
                        <Link
                            v-else
                            :href="link.url"
                            preserve-scroll
                            :class="
                                link.active
                                    ? 'bg-[#0B8570] text-white'
                                    : 'text-[#4A6572] hover:bg-[#F2FBF8] hover:text-[#0B7563]'
                            "
                            class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-colors"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>