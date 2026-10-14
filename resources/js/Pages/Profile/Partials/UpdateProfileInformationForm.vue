<script setup lang="ts">
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    User,
    IdCard,
    Mail,
    UploadCloud,
    CheckCircle2,
    AlertCircle,
    ImageIcon,
    X,
    ShieldCheck,
} from 'lucide-vue-next';

defineProps<{
    mustVerifyEmail?: Boolean;
    status?: String;
}>();

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
    specimen: null as File | null,
    nik: user.nik || '',
});

const fileName = ref<string | null>(null);
const previewUrl = ref<string | null>(null);

const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        form.specimen = target.files[0];
        fileName.value = target.files[0].name;

        // generate preview
        if (previewUrl.value) URL.revokeObjectURL(previewUrl.value);
        previewUrl.value = URL.createObjectURL(target.files[0]);
    }
};

const clearFile = () => {
    form.specimen = null;
    fileName.value = null;
    if (previewUrl.value) URL.revokeObjectURL(previewUrl.value);
    previewUrl.value = null;

    const input = document.getElementById('specimen') as HTMLInputElement | null;
    if (input) input.value = '';
};

const currentSpecimen = computed(() =>
    previewUrl.value
        ? previewUrl.value
        : user.specimen_path
          ? '/storage/' + user.specimen_path
          : null
);
</script>

<template>
    <section>
        <form
            @submit.prevent="form.patch(route('profile.update'))"
            class="space-y-5"
        >
            <!-- ============ Nama ============ -->
            <div>
                <label
                    for="name"
                    class="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                >
                    <User class="h-3.5 w-3.5" />
                    Nama Lengkap
                </label>
                <input
                    id="name"
                    type="text"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                    class="mt-1.5 block w-full rounded-xl border border-[#D9E6E3] bg-[#F8FCFB] px-3.5 py-2.5 text-sm text-[#0F2A2E] placeholder:text-[#A8BCC2] transition-colors focus:border-[#0B8570] focus:outline-none focus:ring-4 focus:ring-[#0B8570]/10"
                />
                <p
                    v-if="form.errors.name"
                    class="mt-1.5 flex items-center gap-1 text-xs text-rose-600"
                >
                    <AlertCircle class="h-3 w-3" />
                    {{ form.errors.name }}
                </p>
            </div>

            <!-- ============ NIK ============ -->
            <div>
                <label
                    for="nik"
                    class="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                >
                    <IdCard class="h-3.5 w-3.5" />
                    NIK
                </label>
                <input
                    id="nik"
                    type="text"
                    v-model="form.nik"
                    required
                    autocomplete="off"
                    class="mt-1.5 block w-full rounded-xl border border-[#D9E6E3] bg-[#F8FCFB] px-3.5 py-2.5 text-sm text-[#0F2A2E] placeholder:text-[#A8BCC2] transition-colors focus:border-[#0B8570] focus:outline-none focus:ring-4 focus:ring-[#0B8570]/10"
                />
                <p
                    v-if="form.errors.nik"
                    class="mt-1.5 flex items-center gap-1 text-xs text-rose-600"
                >
                    <AlertCircle class="h-3 w-3" />
                    {{ form.errors.nik }}
                </p>
            </div>

            <!-- ============ Email ============ -->
            <div>
                <label
                    for="email"
                    class="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                >
                    <Mail class="h-3.5 w-3.5" />
                    Email
                </label>
                <input
                    id="email"
                    type="email"
                    v-model="form.email"
                    required
                    autocomplete="username"
                    class="mt-1.5 block w-full rounded-xl border border-[#D9E6E3] bg-[#F8FCFB] px-3.5 py-2.5 text-sm text-[#0F2A2E] placeholder:text-[#A8BCC2] transition-colors focus:border-[#0B8570] focus:outline-none focus:ring-4 focus:ring-[#0B8570]/10"
                />
                <p
                    v-if="form.errors.email"
                    class="mt-1.5 flex items-center gap-1 text-xs text-rose-600"
                >
                    <AlertCircle class="h-3 w-3" />
                    {{ form.errors.email }}
                </p>
            </div>

            <!-- ============ Spesimen Tanda Tangan ============ -->
            <div>
                <label
                    for="specimen"
                    class="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-[#6A8593]"
                >
                    <ImageIcon class="h-3.5 w-3.5" />
                    Spesimen Tanda Tangan
                    <span class="font-medium normal-case text-[#A8BCC2]">(Opsional)</span>
                </label>

                <div class="mt-2 flex flex-col gap-3 sm:flex-row sm:items-start">
                    <!-- Preview box -->
                    <div
                        class="grid h-24 w-32 shrink-0 place-items-center overflow-hidden rounded-xl border border-dashed border-[#D9E6E3] bg-[#F8FCFB]"
                    >
                        <img
                            v-if="currentSpecimen"
                            :src="currentSpecimen"
                            alt="Spesimen tanda tangan"
                            class="h-full w-full object-contain p-1.5"
                        />
                        <div
                            v-else
                            class="flex flex-col items-center text-center text-[10px] text-[#A8BCC2]"
                        >
                            <ImageIcon class="h-5 w-5" />
                            <span class="mt-1 px-2 leading-tight">
                                Belum ada spesimen
                            </span>
                        </div>
                    </div>

                    <!-- File input area -->
                    <div class="flex-1">
                        <div class="relative">
                            <input
                                id="specimen"
                                type="file"
                                accept="image/png, image/jpeg"
                                @change="handleFileChange"
                                class="peer absolute inset-0 z-10 h-full w-full cursor-pointer opacity-0"
                            />
                            <div
                                class="flex items-center gap-3 rounded-xl border border-[#D9E6E3] bg-white px-3.5 py-3 transition-colors peer-hover:border-[#0B8570] peer-hover:bg-[#F2FBF8]"
                            >
                                <span
                                    class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-[#EAF7F3] text-[#0B7563]"
                                >
                                    <UploadCloud class="h-4 w-4" />
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p
                                        class="truncate text-sm font-semibold text-[#0F2A2E]"
                                    >
                                        {{
                                            fileName ||
                                            (user.specimen_path
                                                ? 'Ganti spesimen saat ini'
                                                : 'Pilih file gambar')
                                        }}
                                    </p>
                                    <p
                                        class="mt-0.5 text-[11px] text-[#6A8593]"
                                    >
                                        Format JPG/PNG, maks. 2MB
                                    </p>
                                </div>
                                <span
                                    v-if="fileName"
                                    class="rounded-md bg-[#EAF7F3] px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-[#0B7563]"
                                >
                                    Baru
                                </span>
                            </div>
                        </div>

                        <!-- Clear -->
                        <button
                            v-if="fileName"
                            type="button"
                            @click="clearFile"
                            class="mt-2 inline-flex items-center gap-1 text-[11px] font-semibold text-[#6A8593] transition-colors hover:text-rose-600"
                        >
                            <X class="h-3 w-3" />
                            Batalkan pilihan file
                        </button>

                        <p
                            v-if="form.errors.specimen"
                            class="mt-1.5 flex items-center gap-1 text-xs text-rose-600"
                        >
                            <AlertCircle class="h-3 w-3" />
                            {{ form.errors.specimen }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- ============ Verifikasi Email ============ -->
            <div
                v-if="
                    mustVerifyEmail && user.email_verified_at === null
                "
                class="rounded-xl border border-amber-200 bg-amber-50 p-3.5"
            >
                <div class="flex items-start gap-2.5">
                    <AlertCircle
                        class="mt-0.5 h-4 w-4 shrink-0 text-amber-600"
                    />
                    <div class="text-xs leading-relaxed text-amber-800">
                        <p class="font-semibold">
                            Email Anda belum diverifikasi.
                        </p>
                        <Link
                            :href="route('verification.send')"
                            method="post"
                            as="button"
                            class="mt-1 inline-block font-bold text-[#0B7563] underline decoration-2 underline-offset-2 transition-colors hover:text-[#086B5B]"
                        >
                            Kirim ulang email verifikasi →
                        </Link>
                    </div>
                </div>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-3 flex items-center gap-2 rounded-lg border border-[#BCE5DA] bg-[#EAF7F3] p-2.5 text-xs font-medium text-[#0B7563]"
                >
                    <ShieldCheck class="h-3.5 w-3.5" />
                    Link verifikasi baru telah dikirim ke email Anda.
                </div>
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
                    <span>
                        {{ form.processing ? 'Menyimpan…' : 'Simpan Perubahan' }}
                    </span>
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
                        Perubahan tersimpan
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>