<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
    Lock,
    KeyRound,
    Eye,
    EyeOff,
    CheckCircle2,
    AlertCircle,
    ShieldCheck,
    ShieldAlert,
} from 'lucide-vue-next';

const currentPasswordInput = ref<HTMLInputElement | null>(null);
const passwordInput = ref<HTMLInputElement | null>(null);

const showCurrent = ref(false);
const showNew = ref(false);
const showConfirm = ref(false);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            showCurrent.value = false;
            showNew.value = false;
            showConfirm.value = false;
        },
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value?.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value?.focus();
            }
        },
    });
};

// Password strength meter
const passwordStrength = computed(() => {
    const p = form.password;
    if (!p) return { score: 0, label: '', cls: '', barCls: '' };

    let score = 0;
    if (p.length >= 8) score++;
    if (/[a-z]/.test(p) && /[A-Z]/.test(p)) score++;
    if (/\d/.test(p)) score++;
    if (/[^A-Za-z0-9]/.test(p)) score++;

    const map: Record<number, { label: string; cls: string; barCls: string }> = {
        1: {
            label: 'Lemah',
            cls: 'text-rose-600',
            barCls: 'bg-rose-500',
        },
        2: {
            label: 'Cukup',
            cls: 'text-amber-600',
            barCls: 'bg-amber-500',
        },
        3: {
            label: 'Kuat',
            cls: 'text-[#0B7563]',
            barCls: 'bg-[#0B8570]',
        },
        4: {
            label: 'Sangat Kuat',
            cls: 'text-[#0B7563]',
            barCls: 'bg-[#0B8570]',
        },
    };

    return { score, ...(map[score] || map[1]) };
});

// Confirm match
const confirmMatches = computed(
    () =>
        form.password_confirmation.length > 0 &&
        form.password === form.password_confirmation
);
</script>

<template>
    <section>
        <form @submit.prevent="updatePassword" class="space-y-5">
            <!-- ============ Current Password ============ -->
            <div>
                <label
                    for="current_password"
                    class="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                >
                    <Lock class="h-3.5 w-3.5" />
                    Password Saat Ini
                </label>
                <div class="relative mt-1.5">
                    <input
                        id="current_password"
                        ref="currentPasswordInput"
                        v-model="form.current_password"
                        :type="showCurrent ? 'text' : 'password'"
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="block w-full rounded-xl border border-[#D9E6E3] bg-[#F8FCFB] px-3.5 py-2.5 pr-11 text-sm text-[#0F2A2E] placeholder:text-[#A8BCC2] transition-colors focus:border-[#0B8570] focus:outline-none focus:ring-4 focus:ring-[#0B8570]/10"
                    />
                    <button
                        type="button"
                        @click="showCurrent = !showCurrent"
                        class="absolute right-2 top-1/2 grid h-8 w-8 -translate-y-1/2 place-items-center rounded-lg text-[#6A8593] transition-colors hover:bg-[#F2FBF8] hover:text-[#0B7563]"
                        :aria-label="
                            showCurrent ? 'Sembunyikan password' : 'Tampilkan password'
                        "
                    >
                        <Eye v-if="!showCurrent" class="h-4 w-4" />
                        <EyeOff v-else class="h-4 w-4" />
                    </button>
                </div>
                <p
                    v-if="form.errors.current_password"
                    class="mt-1.5 flex items-center gap-1 text-xs text-rose-600"
                >
                    <AlertCircle class="h-3 w-3" />
                    {{ form.errors.current_password }}
                </p>
            </div>

            <!-- ============ New Password ============ -->
            <div>
                <label
                    for="password"
                    class="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                >
                    <KeyRound class="h-3.5 w-3.5" />
                    Password Baru
                </label>
                <div class="relative mt-1.5">
                    <input
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        :type="showNew ? 'text' : 'password'"
                        autocomplete="new-password"
                        placeholder="••••••••"
                        class="block w-full rounded-xl border border-[#D9E6E3] bg-[#F8FCFB] px-3.5 py-2.5 pr-11 text-sm text-[#0F2A2E] placeholder:text-[#A8BCC2] transition-colors focus:border-[#0B8570] focus:outline-none focus:ring-4 focus:ring-[#0B8570]/10"
                    />
                    <button
                        type="button"
                        @click="showNew = !showNew"
                        class="absolute right-2 top-1/2 grid h-8 w-8 -translate-y-1/2 place-items-center rounded-lg text-[#6A8593] transition-colors hover:bg-[#F2FBF8] hover:text-[#0B7563]"
                        :aria-label="
                            showNew ? 'Sembunyikan password' : 'Tampilkan password'
                        "
                    >
                        <Eye v-if="!showNew" class="h-4 w-4" />
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

            <!-- ============ Confirm ============ -->
            <div>
                <label
                    for="password_confirmation"
                    class="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                >
                    <ShieldCheck class="h-3.5 w-3.5" />
                    Konfirmasi Password Baru
                </label>
                <div class="relative mt-1.5">
                    <input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        :type="showConfirm ? 'text' : 'password'"
                        autocomplete="new-password"
                        placeholder="••••••••"
                        class="block w-full rounded-xl border border-[#D9E6E3] bg-[#F8FCFB] px-3.5 py-2.5 pr-11 text-sm text-[#0F2A2E] placeholder:text-[#A8BCC2] transition-colors focus:border-[#0B8570] focus:outline-none focus:ring-4 focus:ring-[#0B8570]/10"
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
                            confirmMatches ? 'text-[#0B7563]' : 'text-rose-600'
                        "
                    >
                        <CheckCircle2 v-if="confirmMatches" class="h-3 w-3" />
                        <ShieldAlert v-else class="h-3 w-3" />
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

            <!-- ============ Actions ============ -->
            <div
                class="flex flex-wrap items-center gap-3 border-t border-[#EEF4F2] pt-4"
            >
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex items-center gap-2 rounded-xl bg-[#0B8570] px-5 py-2.5 text-sm font-semibold text-white shadow-[0_18px_40px_-24px_rgba(11,133,112,.8)] transition-all hover:-translate-y-0.5 hover:bg-[#086B5B] disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:translate-y-0"
                >
                    {{ form.processing ? 'Menyimpan…' : 'Perbarui Password' }}
                </button>

                <Transition
                    enter-active-class="transition ease-out duration-200"
                    enter-from-class="opacity-0 translate-x-2"
                    leave-active-class="transition ease-in duration-150"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#0B7563]"
                    >
                        <CheckCircle2 class="h-3.5 w-3.5" />
                        Password diperbarui
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>