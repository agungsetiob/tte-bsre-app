<script setup lang="ts">
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div>
        <div class="min-h-screen bg-slate-50 font-sans text-gray-900">
            <!-- Navbar dengan gaya Glassmorphism & Sticky -->
            <nav class="sticky top-0 z-50 w-full border-b border-gray-200 bg-white/80 backdrop-blur-md shadow-sm transition-all duration-300">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between items-center">
                        <div class="flex items-center gap-8">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="route('dashboard')" class="transition-transform hover:scale-105">
                                    <ApplicationLogo class="block h-10 w-auto fill-current text-blue-600" />
                                </Link>
                            </div>

                            <!-- Navigation Links (Desktop) -->
                            <div class="hidden space-x-2 sm:flex sm:items-center">
                                <NavLink
                                    :href="route('dashboard')"
                                    :active="route().current('dashboard')"
                                    class="flex items-center gap-2 px-3 py-2 rounded-md transition-colors"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                    </svg>
                                    Dashboard
                                </NavLink>

                                <NavLink
                                    v-if="route().has('documents.index')"
                                    :href="route('documents.index')"
                                    :active="route().current('documents.*')"
                                    class="flex items-center gap-2 px-3 py-2 rounded-md transition-colors"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Dokumen
                                </NavLink>

                                <NavLink
                                    v-if="route().has('histories.index')"
                                    :href="route('histories.index')"
                                    :active="route().current('histories.*')"
                                    class="flex items-center gap-2 px-3 py-2 rounded-md transition-colors"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Histori
                                </NavLink>
                                <NavLink
                                    v-if="route().has('admin.users.index')"
                                    :href="route('admin.users.index')"
                                    :active="route().current('admin.users.*')"
                                    class="flex items-center gap-2 px-3 py-2 rounded-md transition-colors"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v2a3 3 0 01-3 3H9a3 3 0 01-3-3v-2m12 0h-2.586a1 1 0 00-.707.293l-2.828 2.828a1 1 0 01-.707.293H7a1 1 0 01-1-1v-2a1 1 0 011-1h2.586a1 1 0 00.707-.293       l2.828-2.828a1 1 0 01.707-.293H17a1 1 0 011 1v2a1 1 0 01-1 1z" />
                                    </svg>
                                    Pengguna
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center">
                            <!-- Settings Dropdown -->
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-full bg-white shadow-sm hover:shadow-md transition-shadow cursor-pointer border border-gray-200">
                                            <button
                                                type="button"
                                                class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold text-gray-700 transition duration-150 ease-in-out hover:text-blue-600 focus:outline-none"
                                            >
                                                <!-- Avatar Placeholder -->
                                                <div class="h-6 w-6 rounded-full bg-gradient-to-tr from-blue-500 to-cyan-400 text-white flex items-center justify-center text-xs">
                                                    {{ $page.props.auth.user.name.charAt(0) }}
                                                </div>
                                                {{ $page.props.auth.user.name }}
                                                
                                                <svg class="h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <div class="px-4 py-3 border-b border-gray-100">
                                            <p class="text-sm">Masuk sebagai</p>
                                            <p class="text-sm font-medium text-gray-900 truncate">{{ $page.props.auth.user.email }}</p>
                                        </div>
                                        <DropdownLink :href="route('profile.edit')" class="flex items-center gap-2">
                                            Profil Saya
                                        </DropdownLink>
                                        <DropdownLink :href="route('logout')" method="post" as="button" class="flex items-center gap-2 text-red-600 hover:text-red-700 hover:bg-red-50">
                                            Keluar Aplikasi
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger (Mobile) -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center rounded-md p-2 text-gray-500 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-700 focus:bg-gray-100 focus:outline-none"
                            >
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }" class="sm:hidden border-t border-gray-200 bg-white">
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                            Dashboard
                        </ResponsiveNavLink>
                        <ResponsiveNavLink v-if="route().has('documents.index')" :href="route('documents.index')" :active="route().current('documents.*')">
                            Dokumen
                        </ResponsiveNavLink>
                        <ResponsiveNavLink v-if="route().has('histories.index')" :href="route('histories.index')" :active="route().current('histories.*')">
                            Histori
                        </ResponsiveNavLink>
                        <ResponsiveNavLink v-if="route().has('admin.users.index')" :href="route('admin.users.index')" :active="route().current('admin.users.*')">
                            Pengguna
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="border-t border-gray-200 pb-1 pt-4 bg-gray-50">
                        <div class="px-4 flex items-center gap-3">
                            <div class="h-10 w-10 rounded-full bg-gradient-to-tr from-blue-500 to-cyan-400 text-white flex items-center justify-center font-bold">
                                {{ $page.props.auth.user.name.charAt(0) }}
                            </div>
                            <div>
                                <div class="text-base font-medium text-gray-800">{{ $page.props.auth.user.name }}</div>
                                <div class="text-sm font-medium text-gray-500">{{ $page.props.auth.user.email }}</div>
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">Profil</ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('logout')" method="post" as="button" class="text-red-600">Keluar</ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header class="bg-white shadow-sm border-b border-gray-200 relative z-10" v-if="$slots.header">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="relative z-0">
                <slot />
            </main>
        </div>
    </div>
</template>