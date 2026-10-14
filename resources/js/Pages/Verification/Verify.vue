<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import {
    ShieldCheck,
    ShieldX,
    ShieldAlert,
    Loader2,
    Download,
    FileText,
    User,
    Calendar,
    Clock4,
    BadgeCheck,
    AlertCircle,
    Info,
    Search,
    ArrowLeft,
} from 'lucide-vue-next';

const props = defineProps<{
    document: any;
}>();

const verificationResult = ref<any>(null);
const isLoading = ref(true);
const isError = ref(false);

onMounted(async () => {
    try {
        const response = await axios.post(
            route('verify.check_async', props.document.verification_code)
        );
        verificationResult.value = response.data;
    } catch (error) {
        console.error(error);
        isError.value = true;
    } finally {
        isLoading.value = false;
    }
});

const formatDate = (dateString: string) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

// ============ STATE META ============
type StateMeta = {
    bg: string;
    Icon: any;
    title: string;
    subtitle: string;
};

const stateMeta = computed<StateMeta>(() => {
    if (isLoading.value) {
        return {
            bg: 'from-[#0F2A2E] via-[#0B7563] to-[#0B8570]',
            Icon: Loader2,
            title: 'Memverifikasi Dokumen…',
            subtitle: 'Menghubungi server BSrE BSSN',
        };
    }

    if (isError.value) {
        return {
            bg: 'from-amber-500 to-amber-600',
            Icon: ShieldAlert,
            title: 'Pengecekan Tertunda',
            subtitle:
                'Gagal terhubung ke server BSrE. Menampilkan data dari sistem lokal.',
        };
    }

    const conclusion = verificationResult.value?.conclusion;
    const description = verificationResult.value?.description;

    if (conclusion === 'VALID') {
        return {
            bg: 'from-[#0B7563] to-[#0B8570]',
            Icon: ShieldCheck,
            title: description || 'Dokumen Sah',
            subtitle:
                'Tanda tangan elektronik terverifikasi oleh BSrE BSSN.',
        };
    }

    return {
        bg: 'from-rose-500 to-rose-600',
        Icon: ShieldX,
        title: description || 'Dokumen Tidak Valid',
        subtitle: 'Tanda tangan elektronik tidak dapat diverifikasi.',
    };
});

const isValid = computed(() => verificationResult.value?.conclusion === 'VALID');
</script>

<template>
    <Head :title="'Verifikasi Dokumen: ' + document.title">
        <meta
            name="description"
            content="Halaman verifikasi keaslian dokumen elektronik RSUD dr. H. Andi Abdurrahman Noor, terintegrasi dengan BSrE BSSN."
        />
    </Head>

    <div
        class="relative min-h-screen overflow-hidden bg-gradient-to-b from-[#F8FCFB] to-[#F1F7F5] py-8 font-sans text-[#0F2A2E] sm:py-12"
    >
        <!-- decorative bg -->
        <div
            class="pointer-events-none absolute inset-0 opacity-60"
            style="
                background-image:
                    radial-gradient(circle at 20% 10%, rgba(11, 133, 112, 0.10), transparent 40%),
                    radial-gradient(circle at 85% 20%, rgba(226, 154, 45, 0.10), transparent 45%);
                mask-image: linear-gradient(180deg, rgba(0, 0, 0, 0.7), transparent 70%);
            "
        />

        <div class="relative mx-auto w-full max-w-2xl px-4 sm:px-6">
            <!-- ============ TOP BAR ============ -->
            <div class="mb-6 flex items-center justify-between gap-3">
                <img
                    src="/logo.png"
                    alt="HELODOCS"
                    class="h-10 w-auto sm:h-11"
                />
                <a
                    href="/"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-[#E9F0EE] bg-white/80 px-3 py-1.5 text-xs font-semibold text-[#4A6572] backdrop-blur transition-colors hover:border-[#BCE5DA] hover:bg-[#F2FBF8] hover:text-[#0B7563]"
                >
                    <ArrowLeft class="h-3.5 w-3.5" />
                    Beranda
                </a>
            </div>

            <!-- ============ MAIN CARD ============ -->
            <div
                class="overflow-hidden rounded-3xl border border-[#DCE8E5] bg-white shadow-[0_45px_90px_-48px_rgba(15,42,46,.35)]"
            >
                <!-- HERO STATE -->
                <div
                    :class="`bg-gradient-to-br ${stateMeta.bg}`"
                    class="relative overflow-hidden px-6 py-8 text-center text-white transition-colors duration-500 sm:px-8 sm:py-10"
                >
                    <!-- glow -->
                    <div
                        class="pointer-events-none absolute -right-16 -top-16 h-48 w-48 rounded-full bg-white opacity-10 blur-3xl"
                    />
                    <div
                        class="pointer-events-none absolute -bottom-20 -left-10 h-40 w-40 rounded-full bg-white opacity-[0.08] blur-3xl"
                    />

                    <!-- Icon badge -->
                    <div class="relative mx-auto mb-4 inline-flex">
                        <span
                            class="grid h-20 w-20 place-items-center rounded-full border border-white/25 bg-white/15 backdrop-blur"
                        >
                            <component
                                :is="stateMeta.Icon"
                                class="h-9 w-9"
                                :class="
                                    isLoading ? 'animate-spin' : ''
                                "
                            />
                        </span>
                        <!-- pulse ring -->
                        <span
                            v-if="!isLoading"
                            class="absolute inset-0 animate-ping rounded-full border border-white/20"
                            style="animation-duration: 2.5s"
                        />
                    </div>

                    <h1
                        class="font-display relative text-2xl font-extrabold leading-tight sm:text-3xl"
                    >
                        {{ stateMeta.title }}
                    </h1>
                    <p
                        class="relative mx-auto mt-2 max-w-md text-sm leading-relaxed text-white/80"
                    >
                        {{ stateMeta.subtitle }}
                    </p>

                    <!-- Kode verifikasi -->
                    <div
                        class="relative mt-5 inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1.5 backdrop-blur"
                    >
                        <BadgeCheck class="h-3.5 w-3.5" />
                        <span
                            class="text-[11px] font-bold uppercase tracking-[0.16em]"
                        >
                            {{ document.verification_code }}
                        </span>
                    </div>
                </div>

                <!-- ============ BODY ============ -->
                <div class="space-y-6 p-6 sm:p-8">
                    <!-- Info dokumen -->
                    <div>
                        <p
                            class="text-[11px] font-bold uppercase tracking-[0.16em] text-[#6A8593]"
                        >
                            Informasi Dokumen
                        </p>

                        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="flex items-start gap-3">
                                <span
                                    class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-[#EAF7F3] text-[#0B7563]"
                                >
                                    <FileText class="h-4 w-4" />
                                </span>
                                <div class="min-w-0">
                                    <p class="text-[11px] text-[#6A8593]">
                                        Judul Dokumen
                                    </p>
                                    <p
                                        class="mt-0.5 text-sm font-semibold leading-snug text-[#0F2A2E]"
                                    >
                                        {{ document.title }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <span
                                    class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-[#EEF4F2] text-[#4A6572]"
                                >
                                    <User class="h-4 w-4" />
                                </span>
                                <div class="min-w-0">
                                    <p class="text-[11px] text-[#6A8593]">
                                        Dibuat Oleh
                                    </p>
                                    <p
                                        class="mt-0.5 truncate text-sm font-semibold text-[#0F2A2E]"
                                    >
                                        {{ document.owner?.name || 'Sistem' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============ HASIL TTE ============ -->
                    <div class="border-t border-[#EEF4F2] pt-6">
                        <p
                            class="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-[0.16em] text-[#6A8593]"
                        >
                            <ShieldCheck class="h-3.5 w-3.5" />
                            Detail Tanda Tangan Elektronik
                        </p>

                        <!-- Signatures -->
                        <div
                            v-if="
                                verificationResult?.signatureInformations &&
                                verificationResult.signatureInformations.length > 0
                            "
                            class="mt-4 space-y-3"
                        >
                            <div
                                v-for="(sig, idx) in verificationResult.signatureInformations"
                                :key="idx"
                                class="overflow-hidden rounded-2xl border border-[#DCE8E5] bg-[#F8FCFB]"
                            >
                                <!-- Card header -->
                                <div
                                    class="flex items-center justify-between gap-3 border-b border-[#E9F0EE] bg-white px-4 py-3"
                                >
                                    <div class="flex items-center gap-2.5">
                                        <span
                                            class="grid h-8 w-8 place-items-center rounded-lg bg-[#EAF7F3] text-[#0B7563]"
                                        >
                                            <ShieldCheck class="h-4 w-4" />
                                        </span>
                                        <div>
                                            <p
                                                class="text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                                            >
                                                Penanda Tangan
                                                {{ Number(idx) + 1 }}
                                            </p>
                                            <p
                                                class="text-sm font-bold text-[#0F2A2E]"
                                            >
                                                {{ sig.signerName }}
                                            </p>
                                        </div>
                                    </div>

                                    <span
                                        v-if="isValid"
                                        class="inline-flex shrink-0 items-center gap-1 rounded-full border border-[#BCE5DA] bg-[#EAF7F3] px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-[#0B7563]"
                                    >
                                        <BadgeCheck class="h-3 w-3" />
                                        Valid
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex shrink-0 items-center gap-1 rounded-full border border-rose-200 bg-rose-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-rose-700"
                                    >
                                        <ShieldX class="h-3 w-3" />
                                        Invalid
                                    </span>
                                </div>

                                <!-- Card body -->
                                <div class="space-y-3 p-4 text-xs">
                                    <div class="flex items-start gap-2.5">
                                        <Clock4
                                            class="mt-0.5 h-3.5 w-3.5 shrink-0 text-[#6A8593]"
                                        />
                                        <div class="min-w-0">
                                            <p class="text-[#6A8593]">
                                                Waktu TTE
                                            </p>
                                            <p
                                                class="font-semibold text-[#0F2A2E]"
                                            >
                                                {{
                                                    sig.signatureDate
                                                        ?.replace('T', ' ')
                                                        .substring(0, 19)
                                                }}
                                            </p>
                                        </div>
                                    </div>

                                    <div
                                        v-if="sig.certificateDetails?.length"
                                        class="flex items-start gap-2.5"
                                    >
                                        <Calendar
                                            class="mt-0.5 h-3.5 w-3.5 shrink-0 text-[#6A8593]"
                                        />
                                        <div class="min-w-0">
                                            <p class="text-[#6A8593]">
                                                Masa Aktif Sertifikat
                                            </p>
                                            <p
                                                class="font-semibold text-[#0F2A2E]"
                                            >
                                                {{
                                                    sig.certificateDetails[0].notBeforeDate?.substring(
                                                        0,
                                                        10
                                                    )
                                                }}
                                                <span class="text-[#A8BCC2]">
                                                    →
                                                </span>
                                                {{
                                                    sig.certificateDetails[0].notAfterDate?.substring(
                                                        0,
                                                        10
                                                    )
                                                }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Reason -->
                                    <div
                                        v-if="isValid && sig.reason"
                                        class="flex items-start gap-2.5 rounded-xl border border-[#BCE5DA] bg-[#EAF7F3] p-2.5"
                                    >
                                        <Info
                                            class="mt-0.5 h-3.5 w-3.5 shrink-0 text-[#0B7563]"
                                        />
                                        <div class="min-w-0">
                                            <p
                                                class="text-[10px] font-bold uppercase tracking-wider text-[#0B7563]"
                                            >
                                                Keterangan
                                            </p>
                                            <p
                                                class="mt-0.5 leading-relaxed text-[#0F2A2E]"
                                            >
                                                {{ sig.reason }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- No signature -->
                        <div
                            v-else-if="verificationResult"
                            class="mt-4 flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4"
                        >
                            <AlertCircle
                                class="mt-0.5 h-4 w-4 shrink-0 text-amber-600"
                            />
                            <div>
                                <p
                                    class="text-sm font-bold text-amber-800"
                                >
                                    Belum Ada Tanda Tangan Elektronik
                                </p>
                                <p
                                    class="mt-0.5 text-xs leading-relaxed text-amber-700"
                                >
                                    Dokumen ini belum ditandatangani secara
                                    elektronik.
                                </p>
                            </div>
                        </div>

                        <!-- Skeleton -->
                        <div
                            v-else-if="isLoading"
                            class="mt-4 animate-pulse space-y-3"
                        >
                            <div
                                class="h-24 rounded-2xl border border-[#E9F0EE] bg-[#F8FCFB]"
                            />
                        </div>
                    </div>

                    <!-- ============ DOWNLOAD ============ -->
                    <div
                        v-if="isValid"
                        class="border-t border-[#EEF4F2] pt-6"
                    >
                        <a
                            :href="
                                route(
                                    'verify.download',
                                    document.verification_code
                                )
                            "
                            class="group flex w-full items-center justify-center gap-2 rounded-xl bg-[#0B8570] px-5 py-3 text-sm font-semibold text-white shadow-[0_18px_40px_-24px_rgba(11,133,112,.8)] transition-all hover:-translate-y-0.5 hover:bg-[#086B5B]"
                        >
                            <Download
                                class="h-4 w-4 transition-transform group-hover:translate-y-0.5"
                            />
                            Unduh Dokumen PDF
                        </a>
                    </div>
                </div>

                <!-- ============ FOOTER ============ -->
                <div
                    class="flex flex-col items-center gap-3 border-t border-[#E9F0EE] bg-[#F8FCFB] px-6 py-5 sm:flex-row sm:justify-center"
                >
                    <img
                        src="/bsre-logo.png"
                        alt="BSrE BSSN"
                        class="h-9 w-auto"
                    />
                </div>
            </div>

            <!-- ============ FOOTER TEXT ============ -->
            <p class="mt-6 text-center text-[11px] text-[#A8BCC2]">
                &copy; {{ new Date().getFullYear() }} RSUD dr. H. Andi Abdurrahman Noor
                <span class="mx-1">•</span>
                Halaman verifikasi publik
            </p>

            <!-- ============ VERIFY ANOTHER ============ -->
            <div class="mt-6 flex justify-center">
                <a
                    :href="route('verification.public')"
                    class="inline-flex items-center gap-2 rounded-xl border border-[#D9E6E3] bg-white px-4 py-2.5 text-sm font-semibold text-[#4A6572] transition-colors hover:border-[#BCE5DA] hover:bg-[#F2FBF8] hover:text-[#0B7563]"
                >
                    <Search class="h-4 w-4" />
                    Verifikasi Dokumen Lain
                </a>
            </div>
        </div>
    </div>
</template>