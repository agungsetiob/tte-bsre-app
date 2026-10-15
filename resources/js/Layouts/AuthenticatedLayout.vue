<script setup lang="ts">
import { ref, computed } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import FlashToast from '@/Components/FlashToast.vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    LayoutDashboard,
    FileText,
    History,
    Users,
    Menu,
    X,
    ChevronDown,
    LogOut,
    UserCircle2,
} from 'lucide-vue-next';

const page = usePage();

const sidebarOpen = ref(false);

const user = computed(() => (page.props.auth as any)?.user ?? { name: '', email: '' });
const userInitial = computed(() => user.value.name?.charAt(0)?.toUpperCase() ?? '?');

const sectionTitle = computed(() => {
    const url = page.url || '';
    if (url.startsWith('/admin/users')) return 'Manajemen Pengguna';
    if (url.startsWith('/documents')) return 'Manajemen Dokumen';
    if (url.startsWith('/histories')) return 'Histori';
    if (url.startsWith('/profile')) return 'Profil';
    return 'Dashboard';
});

const userRole = computed(
    () => (page.props.auth as any)?.user?.role
);

const canAccessMenu = (item: any) => {
    const hasRoute =
        !item.requires || route().has(item.requires);

    const hasRole =
        !item.roles || item.roles.includes(userRole.value);

    return hasRoute && hasRole;
};

const navItems = [
    { label: 'Dashboard', route: 'dashboard', pattern: 'dashboard', icon: LayoutDashboard },
    { label: 'Dokumen', route: 'documents.index', pattern: 'documents.*', icon: FileText, requires: 'documents.index' },
    { label: 'Histori', route: 'histories.index', pattern: 'histories.*', icon: History, requires: 'histories.index' },
    { label: 'Pengguna', route: 'admin.users.index', pattern: 'admin.users.*', icon: Users, requires: 'admin.users.index', roles: ['admin'] },
] as const;

const closeSidebar = () => {
    sidebarOpen.value = false;
};
</script>

<template>
    <div class="min-h-screen bg-[#F8FCFB] font-sans text-[#0F2A2E]">
        <FlashToast />
        <!-- Backdrop (mobile) -->
        <Transition
            enter-active-class="ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="sidebarOpen"
                class="fixed inset-0 z-40 bg-[#0F2A2E]/50 backdrop-blur-sm lg:hidden"
                @click="closeSidebar"
            />
        </Transition>

        <!-- ============ SIDEBAR ============ -->
        <aside
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col border-r border-[#E9F0EE] bg-white shadow-[0_0_40px_-24px_rgba(15,42,46,.25)] transition-transform duration-300 lg:z-30"
        >
            <!-- Logo -->
            <div class="flex h-20 shrink-0 items-center justify-between border-b border-[#E9F0EE] px-5">
                <Link :href="route('dashboard')" class="group flex items-center">
                    <img
                        src="/logo.png"
                        alt="HELODOCS — Hospital Electronic Document Sign"
                        class="h-10 w-auto transition-transform duration-300 group-hover:scale-[1.03] md:h-14"
                    />
                </Link>
                <button
                    type="button"
                    class="grid h-8 w-8 place-items-center rounded-lg text-[#6A8593] transition-colors hover:bg-[#F2FBF8] hover:text-[#0B7563] lg:hidden"
                    aria-label="Tutup menu"
                    @click="closeSidebar"
                >
                    <X class="h-4 w-4" />
                </button>
            </div>

            <!-- Nav links -->
            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">

                <template v-for="item in navItems" :key="item.label">
                    <Link
                        v-if="canAccessMenu(item)"
                        :href="route(item.route)"
                        :class="
                            route().current(item.pattern)
                                ? 'bg-[#EAF7F3] text-[#0B7563]'
                                : 'text-[#4A6572] hover:bg-[#F8FCFB] hover:text-[#0F2A2E]'
                        "
                        class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition-all"
                        @click="closeSidebar"
                    >
                        <component
                            :is="item.icon"
                            class="h-[18px] w-[18px] shrink-0"
                            :class="
                                route().current(item.pattern)
                                    ? 'text-[#0B8570]'
                                    : 'text-[#6A8593] group-hover:text-[#0B7563]'
                            "
                        />
                        <span>{{ item.label }}</span>
                    </Link>
                </template>
            </nav>

            <!-- Footer sidebar: BSrE -->
            <div class="shrink-0 space-y-3 border-t border-[#E9F0EE] p-4">
                <div
                    class="rounded-2xl border border-[#DCE8E5] bg-gradient-to-br from-[#F8FCFB] to-[#F1F7F5] p-3.5"
                >
                    <div class="flex items-center gap-3 justify-center">
                        <img
                            src="/bsre-logo.png"
                            alt="Balai Sertifikasi Elektronik (BSrE) — BSSN"
                            class="h-10 w-auto shrink-0"
                        />
                    </div>
                </div>

                <p class="text-center text-[10px] leading-relaxed text-[#A8BCC2]">
                    &copy; {{ new Date().getFullYear() }} RSUD dr. H. Andi Abdurrahman Noor
                </p>
            </div>
        </aside>

        <!-- ============ MAIN ============ -->
        <div class="lg:pl-72">
            <!-- Topbar -->
            <header
                class="sticky top-0 z-20 border-b border-[#E9F0EE] bg-white/85 backdrop-blur-md"
            >
                <div class="flex h-20 items-center gap-3 px-4 sm:px-6 lg:px-8">
                    <!-- Toggle mobile -->
                    <button
                        type="button"
                        class="grid h-9 w-9 place-items-center rounded-lg border border-[#E9F0EE] text-[#4A6572] transition-colors hover:bg-[#F8FCFB] hover:text-[#0F2A2E] lg:hidden"
                        aria-label="Buka menu"
                        @click="sidebarOpen = true"
                    >
                        <Menu class="h-4 w-4" />
                    </button>

                    <!-- Breadcrumb -->
                    <div class="flex min-w-0 flex-1 items-center gap-2 text-sm">
                        <span class="hidden text-[#A8BCC2] sm:inline">Ruang Pengelola</span>
                        <span class="hidden text-[#D9E6E3] sm:inline">/</span>
                        <span class="truncate font-semibold text-[#0F2A2E]">
                            {{ sectionTitle }}
                        </span>
                    </div>

                    <!-- User dropdown -->
                    <Dropdown align="right" width="56">
                        <template #trigger>
                            <button
                                type="button"
                                class="group inline-flex items-center gap-2.5 rounded-full border border-[#E9F0EE] bg-white py-1.5 pl-1.5 pr-3 text-sm font-semibold text-[#0F2A2E] transition-all hover:border-[#BCE5DA] hover:bg-[#F8FCFB]"
                            >
                                <span
                                    class="grid h-7 w-7 place-items-center rounded-full bg-gradient-to-br from-[#0B8570] to-[#0B7563] text-xs font-bold text-white"
                                >
                                    {{ userInitial }}
                                </span>
                                <span class="hidden max-w-[10rem] truncate sm:block">
                                    {{ user.name }}
                                </span>
                                <ChevronDown
                                    class="h-3.5 w-3.5 text-[#6A8593] transition-transform group-hover:translate-y-0.5"
                                />
                            </button>
                        </template>

                        <template #content>
                            <div class="border-b border-[#EEF4F2] px-4 py-3">
                                <p
                                    class="text-[11px] font-semibold uppercase tracking-wider text-[#A8BCC2]"
                                >
                                    Masuk sebagai
                                </p>
                                <p
                                    class="mt-0.5 truncate text-sm font-semibold text-[#0F2A2E]"
                                >
                                    {{ user.name }}
                                </p>
                                <p class="truncate text-xs text-[#6A8593]">
                                    {{ user.email }}
                                </p>
                            </div>
                            <DropdownLink
                                :href="route('profile.edit')"
                                class="flex items-center gap-2"
                            >
                                <UserCircle2 class="h-4 w-4" />
                                Profil Saya
                            </DropdownLink>
                            <DropdownLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="flex items-center gap-2 text-rose-600 hover:bg-rose-50 hover:text-rose-700"
                            >
                                <LogOut class="h-4 w-4" />
                                Keluar Aplikasi
                            </DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </header>

            <!-- Page Heading (slot) -->
            <header
                v-if="$slots.header"
                class="border-b border-[#E9F0EE] bg-white/60"
            >
                <div class="px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1">
                <slot />
            </main>
        </div>
    </div>
</template>