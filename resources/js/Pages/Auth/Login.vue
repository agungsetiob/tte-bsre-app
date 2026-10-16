<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    Mail,
    Lock,
    ShieldCheck,
    ArrowRight,
    ScanLine,
    AlertCircle,
    Eye,
    EyeOff,
    Loader,
} from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <Head title="Masuk — HELODOCS" />

    <div class="grid min-h-screen bg-[#F8FCFB] font-sans text-[#0F2A2E] lg:grid-cols-[1.1fr_1fr]">
        <!-- ============ LEFT : BRAND PANEL ============ -->
        <aside
            class="relative hidden overflow-hidden bg-gradient-to-br from-[#0F2A2E] via-[#0B7563] to-[#0B8570] lg:block"
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

            <!-- logo center horizontal & vertical -->
            <div
                class="absolute inset-0 z-10 flex items-center justify-center px-8 xl:px-14"
            >
                <img
                    src="/logo.png"
                    alt="HELODOCS"
                    class="h-16 w-auto brightness-0 invert xl:h-20"
                />
            </div>

            <!-- bottom : BSrE badge -->
            <div
                class="absolute inset-x-10 bottom-10 z-20 xl:bottom-14 xl:inset-x-14"
            >
                <div class="flex items-center justify-center p-4 backdrop-blur-sm">
                    <div class="shrink-0 rounded-xl bg-white/95 p-2.5">
                        <img
                            src="/bsre-logo.png"
                            alt="Balai Sertifikasi Elektronik (BSrE) — BSSN"
                            class="h-9 w-auto"
                        />
                    </div>
                </div>

                <p class="mt-6 text-center text-[11px] text-white">
                    &copy; {{ new Date().getFullYear() }} RSUD dr. H. Andi Abdurrahman Noor
                </p>
            </div>
        </aside>

        <!-- ============ RIGHT : FORM PANEL ============ -->
        <main class="relative flex items-center justify-center px-5 py-10 sm:px-8 lg:px-10">
            <!-- mobile logo -->
            <div class="absolute inset-x-0 top-0 flex items-center justify-center py-5 lg:hidden">
                <img src="/logo.png" alt="HELODOCS" class="h-10 w-auto" />
            </div>

            <div class="w-full max-w-md">
                <!-- Heading -->
                <div class="mb-8 mt-16 lg:mt-0">
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full border border-[#BCE5DA] bg-[#EAF7F3] px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-[#0B7563]"
                    >
                        <ShieldCheck class="h-3.5 w-3.5" />
                        Ruang Pengelola
                    </span>

                    <h1
                        class="font-display mt-4 text-3xl font-extrabold leading-tight tracking-tight text-[#0F2A2E] sm:text-[2rem]"
                    >
                        Selamat datang
                    </h1>

                    <p class="mt-2 text-sm text-[#4A6572]">
                        Masuk untuk mengelola, menandatangani, dan memverifikasi dokumen
                        rumah sakit.
                    </p>
                </div>

                <!-- Status message -->
                <div
                    v-if="status"
                    class="mb-5 flex items-start gap-2.5 rounded-xl border border-[#BCE5DA] bg-[#EAF7F3] p-3 text-sm font-medium text-[#0B7563]"
                >
                    <ShieldCheck class="mt-0.5 h-4 w-4 shrink-0" />
                    {{ status }}
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Email -->
                    <div>
                        <label
                            for="email"
                            class="text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                        >
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
                                autofocus
                                autocomplete="username"
                                placeholder="nama@rsud.go.id"
                                class="block w-full rounded-xl border border-[#D9E6E3] bg-white py-2.5 pl-10 pr-3 text-sm text-[#0F2A2E] placeholder:text-[#A8BCC2] transition-colors focus:border-[#0B8570] focus:outline-none focus:ring-4 focus:ring-[#0B8570]/10"
                            />
                        </div>

                        <p v-if="form.errors.email" class="mt-1.5 flex items-center gap-1 text-xs text-rose-600">
                            <AlertCircle class="h-3 w-3" />
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Password -->
                    <div>
                        <div class="flex items-center justify-between">
                            <label
                                for="password"
                                class="text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                            >
                                Password
                            </label>

                            <Link
                                v-if="canResetPassword"
                                :href="route('password.request')"
                                class="text-xs font-semibold text-[#0B7563] hover:text-[#086B5B] hover:underline"
                            >
                                Lupa password?
                            </Link>
                        </div>

                        <div class="relative mt-2">
                            <Lock
                                class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-[#6A8593]"
                            />
                            <input
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                v-model="form.password"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••"
                                class="block w-full rounded-xl border border-[#D9E6E3] bg-white py-2.5 pl-10 pr-11 text-sm text-[#0F2A2E] placeholder:text-[#A8BCC2] transition-colors focus:border-[#0B8570] focus:outline-none focus:ring-4 focus:ring-[#0B8570]/10"
                            />

                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-2 top-1/2 grid h-8 w-8 -translate-y-1/2 place-items-center rounded-lg text-[#6A8593] transition-colors hover:bg-[#F2FBF8] hover:text-[#0B7563]"
                                :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'"
                            >
                                <Eye v-if="!showPassword" class="h-4 w-4" />
                                <EyeOff v-else class="h-4 w-4" />
                            </button>
                        </div>

                        <p v-if="form.errors.password" class="mt-1.5 flex items-center gap-1 text-xs text-rose-600">
                            <AlertCircle class="h-3 w-3" />
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Remember -->
                    <div class="flex items-center justify-between">
                        <label class="inline-flex cursor-pointer select-none items-center gap-2.5">
                            <input
                                type="checkbox"
                                v-model="form.remember"
                                class="h-4 w-4 cursor-pointer rounded border-[#D9E6E3] text-[#0B8570] focus:ring-2 focus:ring-[#0B8570]/20"
                            />
                            <span class="text-sm text-[#4A6572]">Ingat saya</span>
                        </label>
                    </div>

                    <!-- Submit -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="group flex w-full items-center justify-center gap-2 rounded-xl bg-[#0B8570] px-5 py-3 text-sm font-semibold text-white shadow-[0_18px_40px_-24px_rgba(11,133,112,.8)] transition-all hover:-translate-y-0.5 hover:bg-[#086B5B] disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0"
                    >
                        <Loader v-if="form.processing" class="h-4 w-4 animate-spin" />
                        <span>
                            {{ form.processing ? 'Memproses…' : 'Masuk' }}
                        </span>
                        <ArrowRight
                            v-if="!form.processing"
                            class="h-4 w-4 transition-transform group-hover:translate-x-0.5"
                        />
                    </button>

                    <!-- Verifikasi publik -->
                    <Link
                        :href="route('verification.public')"
                        class="flex items-center justify-center gap-2 rounded-xl border border-[#D9E6E3] bg-white px-5 py-2.5 text-sm font-semibold text-[#4A6572] transition-colors hover:border-[#BCE5DA] hover:bg-[#F8FCFB] hover:text-[#0B7563]"
                    >
                        <ScanLine class="h-4 w-4" />
                        Cek keaslian dokumen
                    </Link>
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
                        <p class="flex items-center gap-1 text-[10px] font-bold uppercase tracking-[0.14em] text-[#0B7563]">
                            <ShieldCheck class="h-3 w-3" />
                            Terintegrasi BSrE
                        </p>
                        <p class="truncate text-[11px] text-[#6A8593]">
                            Balai Sertifikasi Elektronik — BSSN
                        </p>
                    </div>
                </div>

                <!-- Footer (mobile) -->
                <p class="mt-6 text-center text-[11px] text-[#A8BCC2] lg:hidden">
                    &copy; {{ new Date().getFullYear() }} RSUD dr. H. Andi Abdurrahman Noor
                </p>
            </div>
        </main>
    </div>
</template>