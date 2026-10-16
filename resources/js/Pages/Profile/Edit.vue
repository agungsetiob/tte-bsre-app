<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import DeleteUserForm from "./Partials/DeleteUserForm.vue";
import UpdatePasswordForm from "./Partials/UpdatePasswordForm.vue";
import UpdateProfileInformationForm from "./Partials/UpdateProfileInformationForm.vue";
import { Head } from "@inertiajs/vue3";
import {
    ShieldCheck,
    ShieldAlert,
    ShieldX,
    Clock4,
    AlertCircle,
    BadgeCheck,
    RefreshCw,
} from "lucide-vue-next";

defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
    bsreStatus?: string;
    departments: Array<{ id: number; name: string }>;
}>();

type StatusMeta = {
    label: string;
    cls: string;
    badgeCls: string;
    Icon: any;
    description: string;
};

const statusMap: Record<string, StatusMeta> = {
    ISSUE: {
        label: "Aktif",
        cls: "from-[#0B8570] to-[#0B7563]",
        badgeCls: "bg-white/15 text-white border-white/25",
        Icon: ShieldCheck,
        description:
            "Sertifikat aktif & siap digunakan untuk menandatangani dokumen.",
    },
    NEW: {
        label: "Baru",
        cls: "from-amber-500 to-amber-600",
        badgeCls: "bg-white/15 text-white border-white/25",
        Icon: AlertCircle,
        description: "Sertifikat baru diterbitkan, menunggu proses aktivasi.",
    },
    WAITING_FOR_VERIFICATION: {
        label: "Menunggu Verifikasi",
        cls: "from-amber-500 to-amber-600",
        badgeCls: "bg-white/15 text-white border-white/25",
        Icon: Clock4,
        description: "Sertifikat sedang dalam proses verifikasi oleh BSrE.",
    },
    RENEW: {
        label: "Proses Pembaruan",
        cls: "from-amber-500 to-amber-600",
        badgeCls: "bg-white/15 text-white border-white/25",
        Icon: RefreshCw,
        description: "Sertifikat sedang dalam proses pembaruan.",
    },
    EXPIRED: {
        label: "Kadaluarsa",
        cls: "from-rose-500 to-rose-600",
        badgeCls: "bg-white/15 text-white border-white/25",
        Icon: ShieldAlert,
        description: "Sertifikat sudah kadaluarsa. Segera lakukan pembaruan.",
    },
    SUSPEND: {
        label: "Disuspend",
        cls: "from-rose-500 to-rose-600",
        badgeCls: "bg-white/15 text-white border-white/25",
        Icon: ShieldX,
        description: "Akun Anda sedang disuspend oleh BSrE.",
    },
    REVOKE: {
        label: "Dicabut",
        cls: "from-rose-500 to-rose-600",
        badgeCls: "bg-white/15 text-white border-white/25",
        Icon: ShieldX,
        description: "Sertifikat telah dicabut. Hubungi admin BSrE.",
    },
    NO_CERTIFICATE: {
        label: "Belum Memiliki Sertifikat",
        cls: "from-slate-500 to-slate-600",
        badgeCls: "bg-white/15 text-white border-white/25",
        Icon: AlertCircle,
        description:
            "Anda belum memiliki sertifikat. Silakan ajukan melalui admin.",
    },
    NOT_REGISTERED: {
        label: "Belum Terdaftar",
        cls: "from-slate-500 to-slate-600",
        badgeCls: "bg-white/15 text-white border-white/25",
        Icon: AlertCircle,
        description: "Akun Anda belum terdaftar di sistem BSrE.",
    },
    ERROR_KONEKSI: {
        label: "Koneksi Bermasalah",
        cls: "from-slate-500 to-slate-600",
        badgeCls: "bg-white/15 text-white border-white/25",
        Icon: AlertCircle,
        description: "Gagal menghubungi server BSrE. Coba beberapa saat lagi.",
    },
};

const statusInfo = (s?: string): StatusMeta =>
    statusMap[s ?? ""] || {
        label: "Tidak Diketahui",
        cls: "from-slate-500 to-slate-600",
        badgeCls: "bg-white/15 text-white border-white/25",
        Icon: AlertCircle,
        description: "Status sertifikat tidak dapat ditentukan.",
    };
</script>

<template>
    <Head title="Profil" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <p
                    class="text-[11px] font-bold uppercase tracking-[0.16em] text-[#0B7563]"
                >
                    Pengaturan Akun
                </p>
                <h2
                    class="font-display mt-0.5 text-xl font-bold leading-tight text-[#0F2A2E]"
                >
                    Profil Saya
                </h2>
                <p class="mt-0.5 text-xs text-[#6A8593]">
                    Kelola informasi pribadi, keamanan akun, dan status
                    sertifikat TTE Anda.
                </p>
            </div>
        </template>

        <div
            class="mx-auto w-full max-w-8xl space-y-5 px-5 py-8 text-[#0F2A2E] md:px-8"
        >
            <!-- ============ CARD STATUS BSrE ============ -->
            <div
                :class="`bg-gradient-to-br ${statusInfo(bsreStatus).cls}`"
                class="relative overflow-hidden rounded-2xl p-6 text-white shadow-[0_24px_60px_-30px_rgba(11,133,112,.55)]"
            >
                <!-- decorative glow -->
                <div
                    class="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full bg-white opacity-10 blur-3xl"
                />
                <div
                    class="pointer-events-none absolute -bottom-20 -left-10 h-48 w-48 rounded-full bg-white opacity-[0.08] blur-3xl"
                />

                <div
                    class="relative flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between"
                >
                    <!-- left -->
                    <div class="flex items-start gap-4">
                        <span
                            class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl border border-white/20 bg-white/10 backdrop-blur"
                        >
                            <component
                                :is="statusInfo(bsreStatus).Icon"
                                class="h-6 w-6"
                            />
                        </span>
                        <div class="min-w-0">
                            <p
                                class="text-[11px] font-bold uppercase tracking-[0.16em] text-white/70"
                            >
                                Status Sertifikat TTE
                            </p>
                            <h3
                                class="font-display mt-1 text-2xl font-extrabold leading-tight"
                            >
                                {{ statusInfo(bsreStatus).label }}
                            </h3>
                            <p
                                class="mt-1.5 max-w-md text-sm leading-relaxed text-white/80"
                            >
                                {{ statusInfo(bsreStatus).description }}
                            </p>
                        </div>
                    </div>

                    <!-- badge kanan -->
                    <div
                        :class="statusInfo(bsreStatus).badgeCls"
                        class="inline-flex shrink-0 items-center gap-1.5 self-start rounded-full border px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider backdrop-blur"
                    >
                        <BadgeCheck class="h-3.5 w-3.5" />
                        {{ bsreStatus || "UNKNOWN" }}
                    </div>
                </div>

                <!-- trust footer -->
                <div
                    class="relative mt-6 flex items-center gap-2.5 border-t border-white/15 pt-4 text-[11px] text-white/70"
                >
                    <ShieldCheck class="h-3.5 w-3.5" />
                    Terintegrasi dengan Balai Sertifikasi Elektronik (BSrE) —
                    BSSN
                </div>
            </div>

            <!-- ============ FORM SECTIONS ============ -->
            <div class="grid grid-cols-1 items-start gap-5 lg:grid-cols-2">
                <!-- 1. Informasi Profil -->
                <div
                    class="overflow-hidden rounded-2xl border border-[#DCE8E5] bg-white shadow-[0_18px_40px_-32px_rgba(15,42,46,.35)]"
                >
                    <div
                        class="flex items-center gap-3 border-b border-[#E9F0EE] bg-[#F8FCFB] px-6 py-4"
                    >
                        <span
                            class="grid h-9 w-9 place-items-center rounded-xl bg-[#EAF7F3] text-[#0B7563]"
                        >
                            <BadgeCheck class="h-4 w-4" />
                        </span>
                        <div>
                            <h3
                                class="font-display text-sm font-bold text-[#0F2A2E]"
                            >
                                Informasi Profil
                            </h3>
                            <p class="text-[11px] text-[#6A8593]">
                                Perbarui nama, NIK, email, dan spesimen tanda
                                tangan.
                            </p>
                        </div>
                    </div>
                    <div class="p-6">
                        <UpdateProfileInformationForm
                            :must-verify-email="mustVerifyEmail"
                            :status="status"
                            :departments="departments"
                        />
                    </div>
                </div>

                <!-- 2. Keamanan Akun -->
                <div
                    class="overflow-hidden rounded-2xl border border-[#DCE8E5] bg-white shadow-[0_18px_40px_-32px_rgba(15,42,46,.35)]"
                >
                    <div
                        class="flex items-center gap-3 border-b border-[#E9F0EE] bg-[#F8FCFB] px-6 py-4"
                    >
                        <span
                            class="grid h-9 w-9 place-items-center rounded-xl bg-amber-50 text-amber-600"
                        >
                            <ShieldCheck class="h-4 w-4" />
                        </span>
                        <div>
                            <h3
                                class="font-display text-sm font-bold text-[#0F2A2E]"
                            >
                                Keamanan Akun
                            </h3>
                            <p class="text-[11px] text-[#6A8593]">
                                Gunakan password yang kuat & unik untuk keamanan
                                akun.
                            </p>
                        </div>
                    </div>
                    <div class="p-6">
                        <UpdatePasswordForm />
                    </div>
                </div>
            </div>

            <!-- ============ DANGER ZONE (full width) ============ -->
            <div
                class="mt-5 overflow-hidden rounded-2xl border border-rose-200 bg-white shadow-[0_18px_40px_-32px_rgba(15,42,46,.35)]"
            >
                <div
                    class="flex items-center gap-3 border-b border-rose-100 bg-rose-50 px-6 py-4"
                >
                    <span
                        class="grid h-9 w-9 place-items-center rounded-xl bg-white text-rose-600"
                    >
                        <ShieldAlert class="h-4 w-4" />
                    </span>
                    <div>
                        <h3
                            class="font-display text-sm font-bold text-rose-700"
                        >
                            Zona Berbahaya
                        </h3>
                        <p class="text-[11px] text-rose-600/80">
                            Tindakan di area ini bersifat permanen dan tidak
                            dapat dibatalkan.
                        </p>
                    </div>
                </div>
                <div class="p-6">
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
