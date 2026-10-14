<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import {
    ArrowRight,
    BadgeCheck,
    Building2,
    Clock4,
    FileSignature,
    PenLine,
    QrCode,
    ScanLine,
    ShieldCheck,
} from 'lucide-vue-next';

withDefaults(
    defineProps<{
        canLogin: boolean;
        canRegister: boolean;
        phpVersion: string;
        laravelVersion: string;
    }>(),
    {
        canLogin: true,
        canRegister: true,
        phpVersion: '8.3',
        laravelVersion: '11.x',
    }
);

const scrolled = ref(false);

const steps = [
    { icon: FileSignature, title: 'Unggah', text: 'Unggah dokumen, surat, atau SK rumah sakit dalam satu dasbor.' },
    { icon: PenLine, title: 'Tanda Tangan', text: 'Pimpinan menandatangani secara elektronik dengan sertifikat BSrE.' },
    { icon: BadgeCheck, title: 'Verifikasi Publik', text: 'Penerima mengecek keaslian dokumen scan QR atau upload ke menu verifikasi.' },
];

function onScroll() {
    scrolled.value = window.scrollY > 12;
}

onMounted(() => {
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });

    const io = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-in');
                    io.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.15 }
    );

    document.querySelectorAll('.reveal').forEach((el) => io.observe(el));
});
</script>

<template>

    <Head title="RSUD dr. H. Andi Abdurrahman Noor">
        <meta name="description"
            content="Layanan tanda tangan dan verifikasi dokumen elektronik rumah sakit, terintegrasi Balai Sertifikasi Elektronik (BSrE) BSSN." />
    </Head>

    <header class="fixed inset-x-0 top-0 z-[100]">
        <div class="transition-all duration-300" :class="scrolled
            ? 'border-b border-[#D6E3DF] bg-white/90 shadow-[0_12px_30px_-22px_rgba(15,42,46,.45)] backdrop-blur-md'
            : 'border-b border-transparent bg-[#F8FCFB]/85 backdrop-blur-md'">
            <div class="mx-auto flex w-full max-w-[1180px] items-center justify-between gap-4 px-5 py-2 md:px-8">
                <img src="/logo.png" alt="HELODOCS — Hospital Electronic Document Sign" class="h-12 w-auto md:h-16" />

                <nav>
                    <Link v-if="canLogin" :href="route('login')"
                        class="group inline-flex items-center gap-1.5 rounded-lg bg-[#0B8570] px-4 py-2 text-sm font-semibold text-white transition-all hover:bg-[#086B5B] hover:shadow-[0_0_0_3px_rgba(11,133,112,.22)]">
                        Masuk
                        <ArrowRight class="h-4 w-4 transition-transform group-hover:translate-x-0.5" />
                    </Link>
                </nav>
            </div>
        </div>
    </header>

    <div class="page-shell min-h-screen overflow-x-hidden text-[#0F2A2E]">
        <!-- HERO -->
        <section class="mx-auto w-full max-w-[1180px] px-5 pb-14 pt-[92px] md:px-8 md:pb-20 md:pt-[108px]">
            <div class="grid items-center gap-10 lg:grid-cols-[1.05fr_.95fr] lg:gap-14">
                <!-- kiri -->
                <div class="rise">
                    <span
                        class="inline-flex items-center gap-2 rounded-full border border-[#BCE5DA] bg-[#EAF7F3] px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-[#0B7563]">
                        <ShieldCheck class="h-3.5 w-3.5" />
                        Terintegrasi BSrE — BSSN
                    </span>

                    <h1
                        class="font-display mt-5 text-[2.1rem] font-extrabold leading-[1.08] tracking-tight text-[#0F2A2E] sm:text-5xl lg:text-[3.35rem]">
                        Tanda tangan & verifikasi
                        <span class="text-[#0B8570]">dokumen elektronik</span>
                        rumah sakit.
                    </h1>

                    <p class="mt-5 max-w-xl text-[15px] leading-relaxed text-[#4A6572]">
                        Satu pintu untuk mengesahkan SK, dokumen medis, dan surat dengan sertifikat elektronik yang sah —
                        lalu memastikan keasliannya dalam hitungan detik oleh pihak penerima.
                    </p>

                    <div class="mt-7 flex flex-wrap items-center gap-3">
                        <Link :href="route('verification.public')"
                            class="group inline-flex items-center gap-2 rounded-xl bg-[#0B8570] px-5 py-3 text-sm font-semibold text-white shadow-[0_18px_40px_-24px_rgba(11,133,112,.8)] transition-all hover:-translate-y-0.5 hover:bg-[#086B5B]">
                            <ScanLine class="h-4 w-4" />
                            Cek Keaslian Dokumen
                        </Link>
                        <Link :href="route('login')"
                            class="inline-flex items-center gap-2 rounded-xl border border-[#B9D3CD] bg-white px-5 py-3 text-sm font-semibold text-[#0F2A2E] transition-all hover:border-[#0B8570] hover:bg-[#F2FBF8]">
                            Ruang Pengelola
                            <ArrowRight class="h-4 w-4" />
                        </Link>
                    </div>

                    <!-- rail statistik -->
                    <div class="reveal mt-9 flex flex-wrap items-center gap-x-8 gap-y-4 border-t border-[#D9E6E3] pt-6">
                        <div class="flex items-center gap-2.5">
                            <Building2 class="h-4 w-4 text-[#E29A2D]" />
                            <span class="text-xs leading-tight text-[#4A6572]">Dokumen rumah sakit<br
                                    class="hidden sm:block" /> tersahkan harian</span>
                        </div>
                        <div class="hidden h-8 w-px bg-[#D9E6E3] sm:block" />
                        <div class="flex items-center gap-2.5">
                            <Clock4 class="h-4 w-4 text-[#E29A2D]" />
                            <span class="text-xs leading-tight text-[#4A6572]">Verifikasi langsung<br
                                    class="hidden sm:block" /> dengan scan QR</span>
                        </div>
                        <div class="hidden h-8 w-px bg-[#D9E6E3] sm:block" />
                        <div class="flex items-center gap-2.5">
                            <QrCode class="h-4 w-4 text-[#E29A2D]" />
                            <span class="text-xs leading-tight text-[#4A6572]">Kode unik per<br
                                    class="hidden sm:block" /> dokumen final</span>
                        </div>
                    </div>
                </div>

                <!-- kanan: mock kartu dokumen -->
                <div class="rise-slow relative">
                    <div
                        class="paper-card relative mx-auto max-w-md bg-white p-6 text-[#0F2A2E] shadow-[0_45px_90px_-48px_rgba(15,42,46,.35)] ring-1 ring-[#E0EAE7] sm:p-7">
                        <div class="flex items-start justify-between gap-4 border-b border-[#E0EAE7] pb-4">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#0B8570]">Nomor:
                                    B/028/156/RSUD-BNM/V/2026</p>
                                <p class="font-display mt-1 text-lg font-bold leading-snug">Penetapan Layanan Rawat
                                    Jalan</p>
                            </div>
                            <FileSignature class="h-6 w-6 shrink-0 text-[#0B8570]" />
                        </div>

                        <div class="mt-4 space-y-2.5">
                            <div class="h-2.5 w-full rounded-full bg-[#E9F0EE]" />
                            <div class="h-2.5 w-[88%] rounded-full bg-[#E9F0EE]" />
                            <div class="h-2.5 w-[72%] rounded-full bg-[#E9F0EE]" />
                        </div>

                        <div class="mt-6 flex items-end justify-between gap-4">
                            <div class="min-w-0">
                                <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-[#6A8593]">
                                    Mengetahui,</p>
                                <p class="truncate text-sm font-bold">Direktur RSUD</p>
                                <svg viewBox="0 0 160 34" class="mt-1 h-8 w-36 text-[#0B8570]" fill="none"
                                    aria-hidden="true">
                                    <path class="sign-line"
                                        d="M4 24c10-14 16 4 26-6s14 8 24-2 12 9 22-1 16 7 26-3 20 5 28-1"
                                        stroke="currentColor" stroke-width="2.2" stroke-linecap="round" />
                                </svg>
                            </div>

                            <div
                                class="seal-badge relative grid h-[74px] w-[74px] shrink-0 place-items-center rounded-full border-2 border-[#0B8570] bg-[#ECF8F5]">
                                <div class="text-center leading-tight">
                                    <ShieldCheck class="mx-auto h-5 w-5 text-[#0B7563]" />
                                    <span
                                        class="mt-0.5 block text-[8px] font-bold uppercase tracking-wide text-[#0B7563]">Sah</span>
                                </div>
                                <span class="halo absolute inset-0 rounded-full"></span>
                            </div>
                        </div>
                    </div>

                    <div
                        class="float-chip absolute -left-2 bottom-2 hidden items-center gap-2 rounded-xl border border-[#BCE5DA] bg-white px-3 py-2 text-xs font-semibold text-[#0B7563] shadow-lg sm:flex lg:-left-6">
                        <BadgeCheck class="h-4 w-4 text-[#0B8570]" />
                        Terverifikasi BSrE
                    </div>
                </div>
            </div>
        </section>

        <!-- PROSES -->
        <section class="border-y border-[#E0EAE7] bg-[#FFFFFF]/70">
            <div class="mx-auto w-full max-w-[1180px] px-5 py-12 md:px-8 md:py-16">

                <ol class="relative grid gap-6 md:grid-cols-3">
                    <span
                        class="pointer-events-none absolute left-6 right-6 top-8 hidden h-px bg-gradient-to-r from-[#D9E6E3] via-[#0B8570]/35 to-transparent md:block"
                        aria-hidden="true" />
                    <li v-for="(step, i) in steps" :key="step.title"
                        class="reveal relative rounded-xl border border-[#DCE8E5] bg-white p-5 shadow-[0_18px_40px_-30px_rgba(15,42,46,.35)] transition-all duration-300 hover:-translate-y-1 hover:border-[#0B8570]/60 hover:shadow-[0_28px_60px_-38px_rgba(11,133,112,.45)]">
                        <div class="flex items-center gap-3">
                            <span class="grid h-9 w-9 place-items-center rounded-lg bg-[#EAF7F3] text-[#0B7563]">
                                <component :is="step.icon" class="h-[18px] w-[18px]" />
                            </span>
                            <span class="font-display text-xs font-bold tracking-[0.2em] text-[#E29A2D]">0{{ i + 1
                                }}</span>
                        </div>
                        <h3 class="font-display mt-4 text-lg font-bold text-[#0F2A2E]">{{ step.title }}</h3>
                        <p class="mt-1.5 text-sm leading-relaxed text-[#4A6572]">{{ step.text }}</p>
                    </li>
                </ol>
            </div>
        </section>

        <!-- FOOTER -->
        <footer class="mx-auto w-full max-w-[1180px] px-5 py-10 md:px-8">
            <div
                class="reveal flex flex-col gap-6 border-t border-[#D9E6E3] pt-8 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <img src="/bsre-logo.png" alt="Balai Sertifikasi Elektronik (BSrE) — BSSN"
                        class="h-14 w-auto md:h-16" />
                    <p class="max-w-md text-xs leading-relaxed text-[#4A6572]">
                        Layanan ini beroperasi terintegrasi dengan <span class="font-semibold text-[#0F2A2E]">Balai
                            Sertifikasi Elektronik (BSrE)</span>, Badan Siber dan Sandi Negara, sebagai penerbit
                        sertifikat elektronik bagi instansi pemerintah.
                    </p>
                </div>

                <p class="text-xs text-[#6A8593] md:text-right">
                    &copy; 2026 RSUD dr. H. Andi Abdurrahman Noor
                    <span class="mx-1 text-[#A8BCC2]">|</span>
                    Laravel {{ laravelVersion }} &bull; PHP {{ phpVersion }}
                </p>
            </div>
        </footer>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');

.page-shell {
    position: relative;
    background: linear-gradient(180deg, #F8FCFB 0%, #F1F7F5 100%);
    font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
}

/* lapisan halus untuk memberi kedalaman pada latar terang */
.page-shell::before {
    content: '';
    position: absolute;
    inset: 0;
    pointer-events: none;
    background-image:
        radial-gradient(circle at 14% 10%, rgba(11, 133, 112, 0.10), transparent 38%),
        radial-gradient(circle at 86% 18%, rgba(226, 154, 45, 0.10), transparent 42%),
        repeating-linear-gradient(90deg, rgba(15, 42, 46, 0.04) 0 1px, transparent 1px 96px),
        repeating-linear-gradient(0deg, rgba(15, 42, 46, 0.03) 0 1px, transparent 1px 96px);
    mask-image: linear-gradient(180deg, rgba(0, 0, 0, 0.65), transparent 66%);
}

.page-shell>* {
    position: relative;
}

.font-display {
    font-family: 'Sora', 'Plus Jakarta Sans', sans-serif;
}

.paper-card {
    clip-path: polygon(0 0, calc(100% - 22px) 0, 100% 22px, 100% 100%, 0 100%);
}

/* animasi */
@keyframes riseIn {
    from {
        opacity: 0;
        transform: translateY(18px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes floatChip {

    0%,
    100% {
        transform: translateY(0);
    }

    50% {
        transform: translateY(-6px);
    }
}

@keyframes haloPulse {
    0% {
        transform: scale(0.9);
        opacity: 0.6;
    }

    70% {
        transform: scale(1.25);
        opacity: 0;
    }

    100% {
        transform: scale(1.25);
        opacity: 0;
    }
}

@keyframes stampIn {
    0% {
        transform: scale(1.35) rotate(-6deg);
        opacity: 0;
    }

    60% {
        transform: scale(0.96) rotate(1deg);
        opacity: 1;
    }

    100% {
        transform: scale(1) rotate(0deg);
        opacity: 1;
    }
}

@keyframes drawSign {
    to {
        stroke-dashoffset: 0;
    }
}

.rise {
    animation: riseIn 0.7s cubic-bezier(0.2, 0.8, 0.25, 1) both;
}

.rise-slow {
    animation: riseIn 0.7s 0.14s cubic-bezier(0.2, 0.8, 0.25, 1) both;
}

.seal-badge {
    animation: stampIn 0.75s 0.4s cubic-bezier(0.2, 0.9, 0.25, 1.15) both;
}

.halo {
    box-shadow: 0 0 0 2px rgba(11, 133, 112, 0.45);
    animation: haloPulse 2.8s ease-out infinite;
}

.sign-line {
    stroke-dasharray: 220;
    stroke-dashoffset: 220;
    animation: drawSign 1.1s ease-out 0.9s forwards;
}

.float-chip {
    animation: floatChip 5s ease-in-out infinite;
}

.reveal {
    opacity: 0;
    transform: translateY(22px);
    transition: opacity 0.6s ease, transform 0.6s cubic-bezier(0.2, 0.8, 0.25, 1);
}

.reveal.is-in {
    opacity: 1;
    transform: translateY(0);
}

@media (prefers-reduced-motion: reduce) {

    .rise,
    .rise-slow,
    .seal-badge,
    .sign-line,
    .halo,
    .float-chip {
        animation: none;
        opacity: 1;
        transform: none;
    }

    .reveal {
        opacity: 1;
        transform: none;
    }
}
</style>