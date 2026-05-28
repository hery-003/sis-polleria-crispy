<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

defineProps({
    fullWidth: { type: Boolean, default: false },
});

const role = usePage().props.auth?.user?.role;
const isAdmin = role === 'admin';
const isCashier = role === 'cashier';
const canPOS = isAdmin || isCashier;
const canProducts = isAdmin || isCashier;
const canClients = isAdmin || isCashier;

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div>
        <div class="min-h-screen bg-background">
            <nav class="bg-white border-b border-gray-200 sticky top-0 z-50 shadow-sm">
                <!-- Primary Navigation Menu -->
                <div class="w-full px-4 sm:px-6 lg:px-8">
                    <div class="flex h-20 justify-between">
                        <div class="flex items-center gap-8">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center gap-3">
                                <Link :href="route('dashboard')">
                                    <ApplicationLogo class="block h-12 w-12 rounded-full object-cover shadow-md" />
                                </Link>
                                <div class="hidden sm:block">
                                    <span class="text-xl font-black text-text uppercase tracking-tight italic">
                                        Pollo Broster
                                    </span>
                                    <span class="text-xl font-black text-primary uppercase tracking-tight italic">
                                        Crispy
                                    </span>
                                </div>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-1 sm:-my-px sm:ms-10 sm:flex items-center">
                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                    Dashboard
                                </NavLink>
                                <NavLink v-if="canPOS" :href="route('pos.index')" :active="route().current('pos.index')">
                                    POS
                                </NavLink>
                                <NavLink :href="route('kitchen.index')" :active="route().current('kitchen.index')">
                                    Cocina
                                </NavLink>
                                <NavLink :href="route('waiter.index')" :active="route().current('waiter.index')">
                                    Mesero
                                </NavLink>
                                <NavLink v-if="canProducts" :href="route('products.index')" :active="route().current('products.index') || route().current('products.create') || route().current('products.edit')">
                                    Productos
                                </NavLink>
                                <NavLink v-if="canProducts" :href="route('products.stock')" :active="route().current('products.stock*')">
                                    Stock
                                </NavLink>
                                <NavLink v-if="canClients" :href="route('clients.index')" :active="route().current('clients.*')">
                                    Clientes
                                </NavLink>
                                <NavLink v-if="isAdmin" :href="route('mesas.index')" :active="route().current('mesas.index')">
                                    Mesas
                                </NavLink>
                                <NavLink v-if="isAdmin" :href="route('cash-register.index')" :active="route().current('cash-register.index')">
                                    Caja
                                </NavLink>
                                <NavLink v-if="isAdmin" :href="route('reports.index')" :active="route().current('reports.index')">
                                    Reportes
                                </NavLink>
                                <NavLink v-if="isAdmin" :href="route('users.index')" :active="route().current('users.*')">
                                    Personal
                                </NavLink>
                                <NavLink v-if="isAdmin" :href="route('audit-logs.index')" :active="route().current('audit-logs.*')">
                                    Auditoría
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center gap-4">
                            <!-- Settings Dropdown -->
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-full">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-full border-2 border-gray-100 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition duration-150 ease-in-out hover:border-primary hover:text-primary focus:outline-none focus:border-primary"
                                            >
                                                <div class="mr-2 flex h-8 w-8 items-center justify-center rounded-full bg-primary/10 text-primary font-bold text-xs uppercase">
                                                    {{ $page.props.auth.user.name.substring(0, 2) }}
                                                </div>
                                                <span class="hidden md:inline">{{ $page.props.auth.user.name }}</span>

                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink :href="route('profile.edit')">
                                            Perfil
                                        </DropdownLink>
                                        <DropdownLink :href="route('logout')" method="post" as="button">
                                            Cerrar Sesión
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none"
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown, }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown, }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown, }"
                    class="sm:hidden bg-white border-t"
                >
                    <div class="space-y-1 pb-3 pt-2 px-4">
                        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                            Dashboard
                        </ResponsiveNavLink>
                        <ResponsiveNavLink v-if="canPOS" :href="route('pos.index')" :active="route().current('pos.index')">
                            POS
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('kitchen.index')" :active="route().current('kitchen.index')">
                            Cocina
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('waiter.index')" :active="route().current('waiter.index')">
                            Mesero
                        </ResponsiveNavLink>
                        <ResponsiveNavLink v-if="canProducts" :href="route('products.index')" :active="route().current('products.*')">
                            Productos
                        </ResponsiveNavLink>
                        <ResponsiveNavLink v-if="canProducts" :href="route('products.stock')" :active="route().current('products.stock*')">
                            Stock
                        </ResponsiveNavLink>
                        <ResponsiveNavLink v-if="canClients" :href="route('clients.index')" :active="route().current('clients.*')">
                            Clientes
                        </ResponsiveNavLink>
                        <ResponsiveNavLink v-if="isAdmin" :href="route('mesas.index')" :active="route().current('mesas.index')">
                            Mesas
                        </ResponsiveNavLink>
                        <ResponsiveNavLink v-if="isAdmin" :href="route('cash-register.index')" :active="route().current('cash-register.index')">
                            Caja
                        </ResponsiveNavLink>
                        <ResponsiveNavLink v-if="isAdmin" :href="route('reports.index')" :active="route().current('reports.index')">
                            Reportes
                        </ResponsiveNavLink>
                        <ResponsiveNavLink v-if="isAdmin" :href="route('users.index')" :active="route().current('users.*')">
                            Personal
                        </ResponsiveNavLink>
                        <ResponsiveNavLink v-if="isAdmin" :href="route('audit-logs.index')" :active="route().current('audit-logs.*')">
                            Auditoría
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="border-t border-gray-200 pb-1 pt-4">
                        <div class="px-4">
                            <div class="text-base font-medium text-gray-800">
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-sm font-medium text-gray-500">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                Perfil
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('logout')" method="post" as="button">
                                Cerrar Sesión
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <main>
                <div v-if="fullWidth" class="bg-background">
                    <slot />
                </div>
                <div v-else class="p-6 max-w-screen-2xl mx-auto bg-background min-h-[calc(100vh-5rem)]">
                    <slot />
                </div>
            </main>
        </div>
        <ToastNotification />
    </div>
</template>
