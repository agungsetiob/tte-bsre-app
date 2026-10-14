<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    User,
    IdCard,
    Mail,
    Lock,
    ShieldCheck,
    ArrowRight,
    Eye,
    EyeOff,
    AlertCircle,
    BadgeCheck,
    FileSignature,
    QrCode,
    CheckCircle2,
} from 'lucide-vue-next';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    nik: '',
});

const showPassword = ref(false);
const showConfirm = ref(false);

const submit = () => {
    form.post(route('register'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};

// NIK hanya boleh angka
const handleNikInput = (e: Event) => {
    const target = e.target as HTMLInputElement;
    form.nik = target.value.replace(/\D/g, '').slice(0, 16);
};

// Password strength
const passwordStrength = computed(() => {
    const p = form.password;
    if (!p) return { score: 0, label: '', cls: '', barCls: '' };

    let score = 0;
    if (p.length >= 8) score++;
    if (/[a-z]/.test(p) && /[A-Z]/.test(p)) score++;
    if (/\d/.test(p)) score++;
    if (/[^A-Za-z0-9]/.test(p)) score++;

    const map: Record<number, { label: string; cls: string; barCls: string }> = {
        1: { label: 'Lemah', cls: 'text-rose-600', barCls: 'bg-rose-500' },
        2: { label: 'Cukup', cls: 'text-amber-600', barCls: 'bg-amber-500' },
        3: { label: 'Kuat', cls: 'text-[#0B7563]', barCls: 'bg-[#0B8570]' },
        4: { label: 'Sangat Kuat', cls: 'text-[#0B7563]', barCls: 'bg-[#0B8570]' },
    };

    return { score, ...(map[score] || map[1]) };
});

const confirmMatches = computed(
    () =>
        form.password_confirmation.length > 0 &&
        form.password === form.password_confirmation
);
</script>

<template>
    <Head title="Registrasi — HELODOCS" />

    <div class="grid min-h-screen bg-[#F8FCFB] font-sans text-[#0F2A2E] lg:grid-cols-[1fr_1.1fr]">
        <!-- ============ LEFT : FORM PANEL ============ -->
        <main class="relative flex items-center justify-center px-5 py-10 sm:px-8 lg:px-10">
            <!-- mobile logo -->
            <div class="absolute inset-x-0 top-0 flex items-center justify-center py-5 lg:hidden">
                <img src="/logo.png" alt="HELODOCS" class="h-10 w-auto" />
            </div>

            <div class="w-full max-w-md">
                <!-- Heading -->
                <div class="mb-7 mt-16 lg:mt-0">
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full border border-[#BCE5DA] bg-[#EAF7F3] px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-[#0B7563]"
                    >
                        <User class="h-3.5 w-3.5" />
                        Pendaftaran Pegawai
                    </span>
                    <h1
                        class="font-display mt-4 text-3xl font-extrabold leading-tight tracking-tight text-[#0F2A2E] sm:text-[2rem]"
                    >
                        Buat akun baru.
                    </h1>
                    <p class="mt-2 text-sm text-[#4A6572]">
                        Daftarkan diri Anda untuk mulai mengelola dan menandatangani
                        dokumen rumah sakit secara elektronik.
                    </p>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Nama -->
                    <div>
                        <label
                            for="name"
                            class="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                        >
                            <User class="h-3.5 w-3.5" />
                            Nama Lengkap
                        </label>
                        <div class="relative mt-2">
                            <User
                                class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-[#6A8593]"
                            />
                            <input
                                id="name"
                                type="text"
                                v-model="form.name"
                                required
                                autofocus
                                autocomplete="name"
                                placeholder="Nama sesuai identitas"
                                class="block w-full rounded-xl border border-[#D9E6E3] bg-white py-2.5 pl-10 pr-3 text-sm text-[#0F2A2E] placeholder:text-[#A8BCC2] transition-colors focus:border-[#0B8570] focus:outline-none focus:ring-4 focus:ring-[#0B8570]/10"
                            />
                        </div>
                        <p
                            v-if="form.errors.name"
                            class="mt-1.5 flex items-center gap-1 text-xs text-rose-600"
                        >
                            <AlertCircle class="h-3 w-3" />
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- NIK -->
                    <div>
                        <label
                            for="nik"
                            class="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                        >
                            <IdCard class="h-3.5 w-3.5" />
                            NIK
                            <span
                                class="font-medium normal-case text-[#A8BCC2]"
                            >(Nomor Induk Kependudukan)</span>
                        </label>
                        <div class="relative mt-2">
                            <IdCard
                                class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-[#6A8593]"
                            />
                            <input
                                id="nik"
                                type="text"
                                inputmode="numeric"
                                :value="form.nik"
                                @input="handleNikInput"
                                required
                                maxlength="16"
                                autocomplete="off"
                                placeholder="16 digit angka"
                                class="block w-full rounded-xl border border-[#D9E6E3] bg-white py-2.5 pl-10 pr-3 font-mono text-sm tracking-wider text-[#0F2A2E] placeholder:font-sans placeholder:tracking-normal placeholder:text-[#A8BCC2] transition-colors focus:border-[#0B8570] focus:outline-none focus:ring-4 focus:ring-[#0B8570]/10"
                            />
                        </div>
                        <div class="mt-1.5 flex items-center justify-between">
                            <p
                                v-if="form.errors.nik"
                                class="flex items-center gap-1 text-xs text-rose-600"
                            >
                                <AlertCircle class="h-3 w-3" />
                                {{ form.errors.nik }}
                            </p>
                            <p
                                v-else
                                class="text-[11px] text-[#A8BCC2]"
                            >
                                Hanya angka, maksimal 16 digit
                            </p>
                            <p
                                class="text-[11px] font-semibold"
                                :class="
                                    form.nik.length === 16
                                        ? 'text-[#0B7563]'
                                        : 'text-[#A8BCC2]'
                                "
                            >
                                {{ form.nik.length }}/16
                            </p>
                        </div>
                    </div>

                    <!-- Email -->
                    <div>
                        <label
                            for="email"
                            class="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                        >
                            <Mail class="h-3.5 w-3.5" />
                            Email
                        </label>
                        <div class="relative mt-2">
                            <Mail
                                class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-[#6A8593]"
                            />
                            <input
                                id="email"
                                type="email"
                                v-model="form.email"
                                required
                                autocomplete="username"
                                placeholder="nama@rsud.go.id"
                                class="block w-full rounded-xl border border-[#D9E6E3] bg-white py-2.5 pl-10 pr-3 text-sm text-[#0F2A2E] placeholder:text-[#A8BCC2] transition-colors focus:border-[#0B8570] focus:outline-none focus:ring-4 focus:ring-[#0B8570]/10"
                            />
                        </div>
                        <p
                            v-if="form.errors.email"
                            class="mt-1.5 flex items-center gap-1 text-xs text-rose-600"
                        >
                            <AlertCircle class="h-3 w-3" />
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label
                            for="password"
                            class="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                        >
                            <Lock class="h-3.5 w-3.5" />
                            Password
                        </label>
                        <div class="relative mt-2">
                            <Lock
                                class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-[#6A8593]"
                            />
                            <input
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                v-model="form.password"
                                required
                                autocomplete="new-password"
                                placeholder="Minimal 8 karakter"
                                class="block w-full rounded-xl border border-[#D9E6E3] bg-white py-2.5 pl-10 pr-11 text-sm text-[#0F2A2E] placeholder:text-[#A8BCC2] transition-colors focus:border-[#0B8570] focus:outline-none focus:ring-4 focus:ring-[#0B8570]/10"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-2 top-1/2 grid h-8 w-8 -translate-y-1/2 place-items-center rounded-lg text-[#6A8593] transition-colors hover:bg-[#F2FBF8] hover:text-[#0B7563]"
                                :aria-label="
                                    showPassword
                                        ? 'Sembunyikan password'
                                        : 'Tampilkan password'
                                "
                            >
                                <Eye v-if="!showPassword" class="h-4 w-4" />
                                <EyeOff v-else class="h-4 w-4" />
                            </button>
                        </div>

                        <!-- Strength meter -->
                        <Transition
                            enter-active-class="transition ease-out duration-200"
                            enter-from-class="opacity-0 -translate-y-1"
                            enter-to-class="opacity-100 translate-y-0"
                        >
                            <div v-if="form.password" class="mt-2">
                                <div class="flex gap-1">
                                    <span
                                        v-for="i in 4"
                                        :key="i"
                                        class="h-1 flex-1 rounded-full transition-colors"
                                        :class="
                                            i <= passwordStrength.score
                                                ? passwordStrength.barCls
                                                : 'bg-[#E9F0EE]'
                                        "
                                    />
                                </div>
                                <p
                                    class="mt-1.5 text-[11px] font-semibold"
                                    :class="passwordStrength.cls"
                                >
                                    Kekuatan: {{ passwordStrength.label }}
                                </p>
                            </div>
                        </Transition>

                        <p
                            v-if="form.errors.password"
                            class="mt-1.5 flex items-center gap-1 text-xs text-rose-600"
                        >
                            <AlertCircle class="h-3 w-3" />
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label
                            for="password_confirmation"
                            class="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                        >
                            <ShieldCheck class="h-3.5 w-3.5" />
                            Konfirmasi Password
                        </label>
                        <div class="relative mt-2">
                            <ShieldCheck
                                class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-[#6A8593]"
                            />
                            <input
                                id="password_confirmation"
                                :type="showConfirm ? 'text' : 'password'"
                                v-model="form.password_confirmation"
                                required
                                autocomplete="new-password"
                                placeholder="Ulangi password"
                                class="block w-full rounded-xl border border-[#D9E6E3] bg-white py-2.5 pl-10 pr-11 text-sm text-[#0F2A2E] placeholder:text-[#A8BCC2] transition-colors focus:border-[#0B8570] focus:outline-none focus:ring-4 focus:ring-[#0B8570]/10"
                            />
                            <button
                                type="button"
                                @click="showConfirm = !showConfirm"
                                class="absolute right-2 top-1/2 grid h-8 w-8 -translate-y-1/2 place-items-center rounded-lg text-[#6A8593] transition-colors hover:bg-[#F2FBF8] hover:text-[#0B7563]"
                                :aria-label="
                                    showConfirm
                                        ? 'Sembunyikan password'
                                        : 'Tampilkan password'
                                "
                            >
                                <Eye v-if="!showConfirm" class="h-4 w-4" />
                                <EyeOff v-else class="h-4 w-4" />
                            </button>
                        </div>

                        <Transition
                            enter-active-class="transition ease-out duration-200"
                            enter-from-class="opacity-0 -translate-y-1"
                            enter-to-class="opacity-100 translate-y-0"
                        >
                            <p
                                v-if="form.password_confirmation"
                                class="mt-1.5 flex items-center gap-1 text-[11px] font-semibold"
                                :class="
                                    confirmMatches
                                        ? 'text-[#0B7563]'
                                        : 'text-rose-600'
                                "
                            >
                                <CheckCircle2
                                    v-if="confirmMatches"
                                    class="h-3 w-3"
                                />
                                <AlertCircle v-else class="h-3 w-3" />
                                {{
                                    confirmMatches
                                        ? 'Password cocok'
                                        : 'Password belum cocok'
                                }}
                            </p>
                        </Transition>

                        <p
                            v-if="form.errors.password_confirmation"
                            class="mt-1.5 flex items-center gap-1 text-xs text-rose-600"
                        >
                            <AlertCircle class="h-3 w-3" />
                            {{ form.errors.password_confirmation }}
                        </p>
                    </div>

                    <!-- Submit -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="group flex w-full items-center justify-center gap-2 rounded-xl bg-[#0B8570] px-5 py-3 text-sm font-semibold text-white shadow-[0_18px_40px_-24px_rgba(11,133,112,.8)] transition-all hover:-translate-y-0.5 hover:bg-[#086B5B] disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0"
                    >
                        <span>
                            {{ form.processing ? 'Mendaftarkan…' : 'Daftar Sekarang' }}
                        </span>
                        <ArrowRight
                            class="h-4 w-4 transition-transform group-hover:translate-x-0.5"
                        />
                    </button>

                    <!-- Login link -->
                    <div class="flex items-center justify-center gap-1.5 text-sm">
                        <span class="text-[#4A6572]">Sudah punya akun?</span>
                        <Link
                            :href="route('login')"
                            class="font-semibold text-[#0B7563] transition-colors hover:text-[#086B5B] hover:underline"
                        >
                            Masuk di sini
                        </Link>
                    </div>
                </form>

                <!-- BSrE badge (mobile) -->
                <div
                    class="mt-10 flex items-center gap-3 rounded-2xl border border-[#DCE8E5] bg-white p-3.5 lg:hidden"
                >
                    <img
                        src="/bsre-logo.png"
                        alt="BSrE BSSN"
                        class="h-9 w-auto shrink-0"
                    />
                    <div class="min-w-0">
                        <p
                            class="flex items-center gap-1 text-[10px] font-bold uppercase tracking-[0.14em] text-[#0B7563]"
                        >
                            <ShieldCheck class="h-3 w-3" />
                            Terintegrasi BSrE
                        </p>
                        <p class="truncate text-[11px] text-[#6A8593]">
                            Balai Sertifikasi Elektronik — BSSN
                        </p>
                    </div>
                </div>
            </div>
        </main>

        <!-- ============ RIGHT : BRAND PANEL ============ -->
        <aside
            class="relative hidden overflow-hidden bg-gradient-to-br from-[#0F2A2E] via-[#0B7563] to-[#0B8570] p-10 lg:flex lg:flex-col lg:justify-between xl:p-14"
        >
            <!-- decorative overlay -->
            <div
                class="pointer-events-none absolute inset-0 opacity-[0.35]"
                style="
                    background-image:
                        radial-gradient(circle at 20% 20%, rgba(255, 255, 255, 0.18), transparent 45%),
                        radial-gradient(circle at 80% 80%, rgba(226, 154, 45, 0.18), transparent 50%),
                        repeating-linear-gradient(90deg, rgba(255, 255, 255, 0.06) 0 1px, transparent 1px 88px),
                        repeating-linear-gradient(0deg, rgba(255, 255, 255, 0.05) 0 1px, transparent 1px 88px);
                "
            />
            <div
                class="pointer-events-none absolute -right-24 top-1/3 h-96 w-96 rounded-full bg-[#0B8570] opacity-40 blur-3xl"
            />

            <!-- top -->
            <div class="relative z-10 text-right">
                <p
                    class="text-[11px] font-bold uppercase tracking-[0.16em] text-white/60"
                >
                    Sudah punya akun?
                </p>
                <Link
                    :href="route('login')"
                    class="mt-2 inline-flex items-center gap-1.5 rounded-xl border border-white/20 bg-white/10 px-4 py-2 text-sm font-semibold text-white backdrop-blur transition-colors hover:bg-white/20"
                >
                    Masuk ke Akun
                    <ArrowRight class="h-4 w-4" />
                </Link>
            </div>

            <!-- middle : value props -->
            <div class="relative z-10 space-y-5">
                <div class="flex items-start gap-3.5">
                    <span
                        class="mt-0.5 grid h-10 w-10 shrink-0 place-items-center rounded-xl border border-white/15 bg-white/10 text-white backdrop-blur"
                    >
                        <FileSignature class="h-5 w-5" />
                    </span>
                    <div>
                        <p class="font-display text-sm font-bold text-white">
                            Kelola dokumen digital
                        </p>
                        <p class="mt-0.5 text-xs leading-relaxed text-white/65">
                            Unggah, kelola, dan pantau seluruh dokumen rumah sakit
                            dalam satu dasbor.
                        </p>
                    </div>
                </div>

                <div class="flex items-start gap-3.5">
                    <span
                        class="mt-0.5 grid h-10 w-10 shrink-0 place-items-center rounded-xl border border-white/15 bg-white/10 text-white backdrop-blur"
                    >
                        <BadgeCheck class="h-5 w-5" />
                    </span>
                    <div>
                        <p class="font-display text-sm font-bold text-white">
                            Tanda tangan sah
                        </p>
                        <p class="mt-0.5 text-xs leading-relaxed text-white/65">
                            Sertifikat elektronik BSrE BSSN yang diakui secara
                            hukum di Indonesia.
                        </p>
                    </div>
                </div>

                <div class="flex items-start gap-3.5">
                    <span
                        class="mt-0.5 grid h-10 w-10 shrink-0 place-items-center rounded-xl border border-white/15 bg-white/10 text-white backdrop-blur"
                    >
                        <QrCode class="h-5 w-5" />
                    </span>
                    <div>
                        <p class="font-display text-sm font-bold text-white">
                            Verifikasi publik
                        </p>
                        <p class="mt-0.5 text-xs leading-relaxed text-white/65">
                            Penerima dapat mengecek keaslian dokumen lewat scan QR
                            dalam hitungan detik.
                        </p>
                    </div>
                </div>
            </div>

            <!-- bottom : BSrE -->
            <div class="relative z-10">
                <div
                    class="flex items-center gap-4 rounded-2xl border border-white/12 bg-white/[0.06] p-4 backdrop-blur-sm"
                >
                    <div class="shrink-0 rounded-xl bg-white/95 p-2.5">
                        <img
                            src="/bsre-logo.png"
                            alt="Balai Sertifikasi Elektronik (BSrE) — BSSN"
                            class="h-9 w-auto"
                        />
                    </div>
                    <div class="min-w-0">
                        <p
                            class="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-[0.16em] text-white/90"
                        >
                            <ShieldCheck class="h-3.5 w-3.5 text-[#E29A2D]" />
                            Terintegrasi Resmi
                        </p>
                        <p class="mt-0.5 text-xs leading-relaxed text-white/60">
                            Balai Sertifikasi Elektronik — BSSN
                        </p>
                    </div>
                </div>

                <p class="mt-6 text-[11px] text-white/40">
                    &copy; {{ new Date().getFullYear() }} RSUD dr. H. Andi Abdurrahman Noor
                </p>
            </div>
        </aside>
    </div>
</template>