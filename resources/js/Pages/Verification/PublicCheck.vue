<script setup lang="ts">
import { Head, useForm, usePage, Link } from "@inertiajs/vue3";
import { computed, nextTick, onMounted, ref } from "vue";
import {
    ArrowLeft,
    ArrowRight,
    CircleAlert,
    CircleCheck,
    FileSignature,
    FileText,
    Info,
    Loader,
    RotateCcw,
    ShieldCheck,
    Upload,
    X,
} from "lucide-vue-next";

const form = useForm({
    file: null as File | null,
});

const page = usePage();
const scrolled = ref(false);
const dragOver = ref(false);

const pageProps = computed(() => page.props as Record<string, any>);
const result = computed(() => pageProps.value.verificationResult);
const serverFileName = computed(() => String(pageProps.value.fileName || ""));
const chosenFileName = computed(() => form.file?.name || "");
const fileName = computed(
    () => serverFileName.value || chosenFileName.value || "Dokumen.pdf",
);

const serverErrorMessage = computed<string | null>(
    () => (page.props.errors as any)?.message ?? null,
);

const hasFile = computed(() => Boolean(form.file));
const hasResult = computed(() => Boolean(result.value));
const isProcessing = computed(() => Boolean(form.processing));
const conclusionIsValid = computed(() => result.value?.conclusion === "VALID");

const signatureCards = computed<any[]>(() =>
    Array.isArray(result.value?.signatureInformations)
        ? result.value.signatureInformations
        : [],
);

const signatureCountLabel = computed(() =>
    String(result.value?.signatureCount ?? signatureCards.value.length ?? 0),
);

const description = computed(
    () =>
        result.value?.description ||
        "Dokumen telah diperiksa melalui layanan verifikasi.",
);

const statusLabel = computed(() => {
    if (isProcessing.value) return "Memeriksa berkas...";
    if (!hasResult.value) return "Menunggu unggahan berkas";
    return "Hasil verifikasi siap";
});

const dotClasses = computed(() => {
    if (isProcessing.value) return "bg-[#F59E0B]";
    if (!hasResult.value) return "bg-[#94A3B8]";
    return conclusionIsValid.value ? "bg-[#10B981]" : "bg-[#F59E0B]";
});

const formatDate = (dateString: string) => {
    if (!dateString) return "-";
    return new Date(dateString).toLocaleDateString("id-ID", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
};

function fileSizeLabel() {
    if (!form.file) return "";
    return `${Math.max(1, Math.round(form.file.size / 1024))} KB`;
}

function setFile(files?: FileList | null) {
    form.file = files?.[0] ?? null;
    form.clearErrors();
}

function handleDrop(event: DragEvent) {
    dragOver.value = false;
    setFile(event.dataTransfer?.files);
}

function submitCheck() {
    form.post(route("verification.check"), { preserveScroll: true });
}

function handleScroll() {
    scrolled.value = window.scrollY > 12;
}

onMounted(() => {
    handleScroll();
    window.addEventListener("scroll", handleScroll, { passive: true });
});

const fileInput = ref<HTMLInputElement | null>(null);

function clearFileInput() {
    if (fileInput.value) fileInput.value.value = "";
}

/** Tombol "Reset Pilihan Berkas" */
function resetFileSelection() {
    form.reset();
    form.clearErrors();
    clearFileInput();
}

/** Tombol "Verifikasi Dokumen Lain" */
function verifyAnotherDocument() {
    resetFileSelection();
    window.scrollTo({ top: 0, behavior: "smooth" });
    nextTick(() => fileInput.value?.click());
}
</script>

<template>
    <Head title="Verifikasi Dokumen">
        <meta
            name="description"
            content="Periksa keaslian dokumen bertanda tangan elektronik RSUD melalui layanan BSrE."
        />
    </Head>

    <header class="fixed inset-x-0 top-0 z-50 w-full">
        <div
            :class="
                scrolled
                    ? 'border-b border-[#D9E6E3] bg-[#F8FCFB]/95 shadow-[0_12px_30px_-22px_rgba(15,42,46,.45)] backdrop-blur-md'
                    : 'border-b border-transparent bg-[#F8FCFB]/80 backdrop-blur-md'
            "
        >
            <div
                class="mx-auto flex w-full max-w-[1180px] items-center justify-between gap-4 px-5 py-4 md:px-8"
            >
                <img
                    src="/logo.png"
                    alt="HELODOCS — Hospital Electronic Document Sign"
                    class="h-12 w-auto md:h-14"
                />

                <nav class="flex items-center gap-2">
                    <Link
                        :href="route('welcome')"
                        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium text-[#46626F] transition-colors hover:text-[#0B8570]"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        <span class="hidden sm:inline">Beranda</span>
                    </Link>

                    <Link
                        :href="route('login')"
                        class="group inline-flex items-center gap-1.5 rounded-lg bg-[#0B8570] px-4 py-2 text-sm font-semibold text-white transition-all hover:bg-[#086B5B] hover:shadow-[0_0_0_3px_rgba(11,133,112,.22)]"
                    >
                        <ShieldCheck class="h-4 w-4" />
                        <span class="hidden sm:inline">Login Sistem</span>
                    </Link>
                </nav>
            </div>
        </div>
    </header>

    <div
        class="page-shell min-h-screen overflow-x-hidden pt-[92px] text-[#0F2A2E] md:pt-[108px]"
    >
        <main class="mx-auto w-full max-w-[1180px] px-5 pb-14 md:px-8 md:pb-20">
            <form
                @submit.prevent="submitCheck"
                class="rise rounded-[28px] bg-white p-5 shadow-[0_32px_70px_-52px_rgba(15,42,46,.45)] ring-1 ring-[#E0EAE7] sm:p-7 md:p-8"
            >
                <Transition
                    enter-active-class="transition ease-out duration-200"
                    enter-from-class="opacity-0 -translate-y-1"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition ease-in duration-150"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 -translate-y-1"
                >
                    <div
                        v-if="serverErrorMessage"
                        role="alert"
                        class="mb-6 flex items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700"
                    >
                        <CircleAlert class="mt-0.5 h-5 w-5 shrink-0" />
                        <div class="min-w-0 flex-1">
                            <p
                                class="text-[11px] font-bold uppercase tracking-wider text-rose-600"
                            >
                                Verifikasi Gagal
                            </p>
                            <p class="mt-0.5 font-medium leading-relaxed">
                                {{ serverErrorMessage }}
                            </p>
                        </div>
                        <button
                            type="button"
                            aria-label="Tutup"
                            class="shrink-0 rounded-lg p-1 text-rose-500 transition-colors hover:bg-rose-100 hover:text-rose-700"
                            @click="
                                () => {
                                    (page.props.errors as any).message = null;
                                }
                            "
                        >
                            <X class="h-4 w-4" />
                        </button>
                    </div>
                </Transition>

                <div
                    aria-live="polite"
                    class="flex flex-col gap-4 border-b border-[#E7EEEC] pb-6 md:flex-row md:items-end md:justify-between"
                >
                    <div>
                        <h1
                            class="font-display text-2xl font-bold tracking-tight text-[#0F2A2E] sm:text-3xl"
                        >
                            Verifikasi Keaslian Dokumen TTE
                        </h1>
                        <p class="mt-1 text-sm text-[#4A6572]">
                            Unggah dokumen PDF ber-TTE untuk memeriksa keabsahan
                            tanda tangan secara instan.
                        </p>
                    </div>

                    <div
                        class="inline-flex w-fit items-center gap-2 rounded-full border border-[#DCE8E5] bg-[#F9FCFB] px-3 py-1.5 text-xs font-semibold text-[#4A6572]"
                    >
                        <span
                            :class="dotClasses"
                            class="pulse-dot inline-block h-2 w-2 rounded-full"
                        ></span>
                        {{ statusLabel }}
                    </div>
                </div>

                <div
                    class="grid gap-5 pt-6 lg:grid-cols-[minmax(0,1fr)_290px] lg:gap-7"
                >
                    <label
                        class="drop-zone relative block cursor-pointer rounded-[22px] border-2 border-dashed border-[#C7D9D5] bg-[#FBFEFD] p-5 text-center transition-colors sm:p-7"
                        :class="
                            dragOver
                                ? 'border-[#0B8570]/60 bg-[#F0FBF8]'
                                : 'hover:border-[#0B8570]/60 hover:bg-[#F7FCFB]'
                        "
                        @dragover.prevent="dragOver = true"
                        @dragleave="dragOver = false"
                        @drop.prevent="handleDrop($event)"
                    >
                        <input
                            ref="fileInput"
                            type="file"
                            accept="application/pdf,.pdf"
                            class="sr-only"
                            @change="
                                (event: Event) =>
                                    setFile(
                                        (event.target as HTMLInputElement)
                                            .files,
                                    )
                            "
                        />

                        <Upload class="mx-auto h-8 w-8 text-[#0B8570]" />

                        <p class="mt-3 font-semibold text-[#0F2A2E]">
                            Letakkan berkas PDF di sini atau klik untuk memilih
                        </p>
                        <p class="mt-1 text-sm text-[#6A8593]">
                            Format PDF dengan sertifikat elektronik BSrE
                            disarankan agar hasil baca lebih akurat.
                        </p>

                        <span
                            v-if="chosenFileName"
                            class="mt-4 inline-flex items-center gap-2 rounded-full border border-[#BCE5DA] bg-white px-3 py-1.5 text-xs font-semibold text-[#0B7563]"
                        >
                            <FileText class="h-3.5 w-3.5" />
                            {{ chosenFileName }}
                            <span v-if="fileSizeLabel()" class="text-[#6A8593]"
                                >• {{ fileSizeLabel() }}</span
                            >
                        </span>

                        <ul
                            v-if="Object.keys(form.errors).length"
                            class="mt-4 space-y-1 text-left text-xs text-[#B45309]"
                        >
                            <li v-for="(error, key) in form.errors" :key="key">
                                • {{ error }}
                            </li>
                        </ul>
                    </label>

                    <div
                        class="flex flex-col gap-4 rounded-[22px] border border-[#DCE8E5] bg-[#FBFEFD] p-5 sm:p-6"
                    >
                        <ul
                            class="space-y-3 text-sm leading-relaxed text-[#4A6572]"
                        >
                            <li class="flex gap-3">
                                <FileText
                                    class="mt-0.5 h-4 w-4 shrink-0 text-[#0B8570]"
                                />
                                Pastikan dokumen sudah ditandatangani dengan
                                sertifikat elektronik aktif.
                            </li>
                            <li class="flex gap-3">
                                <ShieldCheck
                                    class="mt-0.5 h-4 w-4 shrink-0 text-[#0B8570]"
                                />
                                Hasil menampilkan status sertifikat, identitas
                                penanda tangan, dan waktu tanda tangan.
                            </li>
                        </ul>

                        <div class="mt-auto space-y-3">
                            <button
                                v-if="!hasResult || hasFile"
                                type="submit"
                                :disabled="isProcessing || !hasFile"
                                class="group inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#0B8570] px-4 py-3 text-sm font-semibold text-white transition-all hover:bg-[#086B5B] disabled:cursor-not-allowed disabled:opacity-55"
                            >
                                <Loader
                                    v-if="isProcessing"
                                    class="h-4 w-4 animate-spin"
                                />
                                {{
                                    isProcessing
                                        ? "Memeriksa ke Server BSrE..."
                                        : "Verifikasi Dokumen"
                                }}
                                <ArrowRight
                                    v-if="!isProcessing"
                                    class="h-4 w-4 transition-transform group-hover:translate-x-0.5"
                                />
                            </button>

                            <button
                                v-else
                                type="button"
                                @click="verifyAnotherDocument"
                                class="group inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#0B8570] px-4 py-3 text-sm font-semibold text-white transition-all hover:bg-[#086B5B]"
                            >
                                <Upload
                                    class="h-4 w-4 transition-transform group-hover:-translate-y-0.5"
                                />
                                Verifikasi Dokumen Lain
                            </button>

                            <button
                                v-if="hasFile"
                                type="button"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-[#B9D3CD] px-4 py-2.5 text-sm font-medium text-[#46626F] transition-colors hover:border-[#0B8570]/60 hover:bg-[#F2FBF8]"
                                @click="resetFileSelection"
                            >
                                <RotateCcw class="h-4 w-4" />
                                Reset Pilihan Berkas
                            </button>

                            <p
                                v-if="!hasFile && !isProcessing"
                                class="text-xs leading-relaxed text-[#6A8593]"
                            >
                                Pilih berkas terlebih dahulu untuk memulai
                                verifikasi.
                            </p>
                        </div>
                    </div>
                </div>
            </form>

            <section v-if="hasResult" class="mt-8 space-y-6">
                <div
                    class="rise rounded-[28px] border p-5 shadow-[0_32px_70px_-52px_rgba(15,42,46,.45)] sm:p-7"
                    :class="
                        conclusionIsValid
                            ? 'border-[#B7E5D8] bg-[#ECFDF5]'
                            : 'border-[#FDE68A] bg-[#FFFBEB]'
                    "
                >
                    <div
                        class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
                    >
                        <div class="flex items-start gap-4">
                            <component
                                :is="
                                    conclusionIsValid
                                        ? CircleCheck
                                        : CircleAlert
                                "
                                class="mt-1 h-8 w-8 shrink-0 text-[#0B8570]"
                            />
                            <div class="min-w-0">
                                <h2
                                    class="font-display text-xl font-bold text-[#0F2A2E]"
                                >
                                    Hasil Verifikasi
                                </h2>
                                <p
                                    class="mt-1 text-sm leading-relaxed text-[#4A6572]"
                                >
                                    {{ description }}
                                </p>
                            </div>
                        </div>

                        <span
                            class="inline-flex w-fit items-center rounded-full px-3 py-1.5 text-xs font-bold"
                            :class="
                                conclusionIsValid
                                    ? 'bg-white text-[#065F46]'
                                    : 'bg-white text-[#92400E]'
                            "
                        >
                            {{ result?.conclusion || "-" }}
                        </span>
                    </div>

                    <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                        <div
                            class="stat-card rounded-2xl bg-white/85 p-4 ring-1 ring-white"
                        >
                            <p
                                class="text-xs font-semibold uppercase tracking-[0.12em] text-[#6A8593]"
                            >
                                Nama Dokumen
                            </p>
                            <p
                                class="mt-1 break-all font-semibold text-[#0F2A2E]"
                            >
                                {{ fileName }}
                            </p>
                        </div>

                        <div
                            class="stat-card rounded-2xl bg-white/85 p-4 ring-1 ring-white"
                        >
                            <p
                                class="text-xs font-semibold uppercase tracking-[0.12em] text-[#6A8593]"
                            >
                                Jumlah Tanda Tangan
                            </p>
                            <p
                                class="mt-1 text-lg font-semibold text-[#0F2A2E]"
                            >
                                {{ signatureCountLabel }}
                            </p>
                        </div>

                        <div
                            class="stat-card rounded-2xl bg-white/85 p-4 ring-1 ring-white sm:col-span-2 xl:col-span-1"
                        >
                            <p
                                class="text-xs font-semibold uppercase tracking-[0.12em] text-[#6A8593]"
                            >
                                Integritas
                            </p>
                            <p
                                class="mt-1 text-lg font-semibold text-[#0F2A2E]"
                            >
                                {{
                                    conclusionIsValid
                                        ? "Baik (Valid)"
                                        : "Perlu Pemeriksaan"
                                }}
                            </p>
                        </div>
                    </div>
                </div>

                <div v-if="signatureCards.length" class="space-y-4">
                    <h3
                        class="font-display inline-block border-b border-[#D9E6E3] pb-2 text-base font-bold text-[#0F2A2E]"
                    >
                        Detail Tanda Tangan
                    </h3>

                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        <article
                            v-for="(sig, idx) in signatureCards"
                            :key="idx"
                            class="detail-card rounded-[22px] border border-[#DCE8E5] bg-white p-5 shadow-[0_18px_40px_-30px_rgba(15,42,46,.35)]"
                        >
                            <div
                                class="flex items-center gap-2 border-b border-[#E7EEEC] pb-3"
                            >
                                <FileSignature class="h-4 w-4 text-[#0B8570]" />
                                <h4 class="font-semibold text-[#0F2A2E]">
                                    Tanda Tangan {{ Number(idx) + 1 }}
                                </h4>
                            </div>

                            <dl class="mt-4 space-y-2.5 text-sm">
                                <div>
                                    <dt
                                        class="text-xs font-semibold uppercase tracking-[0.1em] text-[#6A8593]"
                                    >
                                        Signature Field
                                    </dt>
                                    <dd
                                        class="mt-0.5 inline-block rounded-md bg-[#EFF6F4] px-2 py-0.5 font-mono text-xs text-[#0B7563]"
                                    >
                                        {{ sig.fieldName || "-" }}
                                    </dd>
                                </div>

                                <div>
                                    <dt
                                        class="text-xs font-semibold uppercase tracking-[0.1em] text-[#6A8593]"
                                    >
                                        Penanda Tangan
                                    </dt>
                                    <dd
                                        class="mt-0.5 font-semibold text-[#0F2A2E]"
                                    >
                                        {{ sig.signerName || "-" }}
                                    </dd>
                                </div>

                                <div>
                                    <dt
                                        class="text-xs font-semibold uppercase tracking-[0.1em] text-[#6A8593]"
                                    >
                                        Nama TSA
                                    </dt>
                                    <dd class="mt-0.5 text-[#4A6572]">
                                        {{
                                            sig.timestampInfomation
                                                ?.signerName ||
                                            "Timestamp Authority BSRE BSSN"
                                        }}
                                    </dd>
                                </div>

                                <div>
                                    <dt
                                        class="text-xs font-semibold uppercase tracking-[0.1em] text-[#6A8593]"
                                    >
                                        Validitas Sertifikat
                                    </dt>
                                    <dd class="mt-0.5 text-[#4A6572]">
                                        {{
                                            formatDate(
                                                sig.certificateDetails?.[0]
                                                    ?.notBeforeDate,
                                            )
                                        }}
                                        -
                                        {{
                                            formatDate(
                                                sig.certificateDetails?.[0]
                                                    ?.notAfterDate,
                                            )
                                        }}
                                    </dd>
                                </div>

                                <div>
                                    <dt
                                        class="text-xs font-semibold uppercase tracking-[0.1em] text-[#6A8593]"
                                    >
                                        Ditandatangani Pada
                                    </dt>
                                    <dd class="mt-0.5 text-[#4A6572]">
                                        {{
                                            new Date(
                                                sig.signatureDate,
                                            ).toLocaleString("id-ID", {
                                                year: "numeric",
                                                month: "long",
                                                day: "numeric",
                                                hour: "2-digit",
                                                minute: "2-digit",
                                                second: "2-digit",
                                            })
                                        }}
                                    </dd>
                                </div>
                            </dl>
                        </article>
                    </div>
                </div>

                <div
                    v-else
                    class="rounded-[24px] border border-[#FDE68A] bg-[#FFFBEB] p-5 text-sm text-[#92400E]"
                >
                    <div class="flex items-start gap-3">
                        <Info class="mt-0.5 h-5 w-5 shrink-0" />
                        <p>
                            Dokumen memiliki hasil verifikasi umum, tetapi tidak
                            ada blok tanda tangan yang dapat ditampilkan pada
                            layar ini.
                        </p>
                    </div>
                </div>
            </section>
        </main>

        <footer class="mx-auto w-full max-w-[1180px] px-5 pb-10 md:px-8">
            <div
                class="reveal flex flex-col gap-6 border-t border-[#D9E6E3] pt-8 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4">
                    <img
                        src="/bsre-logo.png"
                        alt="Balai Sertifikasi Elektronik (BSrE) — BSSN"
                        class="h-14 w-auto md:h-16"
                    />
                    <p class="max-w-md text-xs leading-relaxed text-[#4A6572]">
                        Layanan ini beroperasi terintegrasi dengan
                        <span class="font-semibold text-[#0F2A2E]"
                            >Balai Sertifikasi Elektronik (BSrE)</span
                        >, Badan Siber dan Sandi Negara, sebagai penerbit
                        sertifikat elektronik bagi instansi pemerintah.
                    </p>
                </div>

                <p class="text-xs text-[#6A8593] md:text-right">
                    &copy; 2026 RSUD dr. H. Andi Abdurrahman Noor
                </p>
            </div>
        </footer>
    </div>
</template>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap");

.page-shell {
    position: relative;
    background: linear-gradient(180deg, #f8fcfb 0%, #f1f7f5 100%);
    font-family: "Plus Jakarta Sans", system-ui, sans-serif;
}

.page-shell::before {
    content: "";
    position: absolute;
    inset: 0;
    pointer-events: none;
    background-image:
        radial-gradient(
            circle at 14% 10%,
            rgba(11, 133, 112, 0.1),
            transparent 38%
        ),
        radial-gradient(
            circle at 86% 18%,
            rgba(226, 154, 45, 0.1),
            transparent 42%
        ),
        repeating-linear-gradient(
            90deg,
            rgba(15, 42, 46, 0.04) 0 1px,
            transparent 1px 96px
        ),
        repeating-linear-gradient(
            0deg,
            rgba(15, 42, 46, 0.03) 0 1px,
            transparent 1px 96px
        );
    mask-image: linear-gradient(180deg, rgba(0, 0, 0, 0.65), transparent 66%);
}

.page-shell > * {
    position: relative;
}

.font-display {
    font-family: "Sora", "Plus Jakarta Sans", sans-serif;
}

.drop-zone:focus-within {
    border-color: rgba(11, 133, 112, 0.65);
    box-shadow: 0 0 0 4px rgba(11, 133, 112, 0.1);
}

.pulse-dot {
    animation: pulseDot 1.8s ease-in-out infinite;
}

@keyframes riseIn {
    from {
        opacity: 0;
        transform: translateY(18px);
    }
    to {
        opacity: 1;
        transform: none;
    }
}

@keyframes pulseDot {
    0%,
    100% {
        transform: scale(1);
        opacity: 1;
    }
    50% {
        transform: scale(1.25);
        opacity: 0.72;
    }
}

@keyframes fadeUp {
    from {
        opacity: 0;
        transform: translateY(14px);
    }
    to {
        opacity: 1;
        transform: none;
    }
}

.rise {
    animation: riseIn 0.65s cubic-bezier(0.2, 0.8, 0.25, 1) both;
}

.reveal {
    animation: fadeUp 0.7s 0.08s cubic-bezier(0.2, 0.8, 0.25, 1) both;
}

.stat-card {
    animation: fadeUp 0.55s 0.1s cubic-bezier(0.2, 0.8, 0.25, 1) both;
}

.stat-card:nth-child(2) {
    animation-delay: 0.16s;
}

.stat-card:nth-child(3) {
    animation-delay: 0.22s;
}

.detail-card {
    animation: fadeUp 0.55s cubic-bezier(0.2, 0.8, 0.25, 1) both;
}

.detail-card:nth-child(2) {
    animation-delay: 0.07s;
}

.detail-card:nth-child(3) {
    animation-delay: 0.14s;
}

.detail-card:nth-child(4) {
    animation-delay: 0.21s;
}

@media (prefers-reduced-motion: reduce) {
    .rise,
    .reveal,
    .pulse-dot,
    .stat-card,
    .detail-card {
        animation: none !important;
        opacity: 1;
        transform: none;
    }
}
</style>
