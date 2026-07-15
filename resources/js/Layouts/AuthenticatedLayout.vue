<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import ApplicationLogo from '@/components/ApplicationLogo.vue';
import Dropdown from '@/components/Dropdown.vue';
import DropdownLink from '@/components/DropdownLink.vue';
import NavLink from '@/components/NavLink.vue';
import NavDropdown from '@/components/NavDropdown.vue';
import NavDropdownLink from '@/components/NavDropdownLink.vue';
import ResponsiveNavLink from '@/components/ResponsiveNavLink.vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { useThemeStore } from '@/stores/theme';

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
const loading = ref(false);
const theme = useThemeStore();

const removeStart = router.on('start', () => loading.value = true);
const removeFinish = router.on('finish', () => loading.value = false);

onUnmounted(() => {
    removeStart();
    removeFinish();
});
</script>

<template>
    <div>
        <div class="min-h-screen bg-background">
            <!-- Global loading bar -->
            <div v-if="loading" class="fixed top-0 left-0 right-0 z-[100] h-1 bg-primary/30">
                <div class="h-full bg-primary animate-pulse" style="width: 60%; transition: width 0.3s"></div>
            </div>
            <nav class="bg-primary/95 backdrop-blur-md sticky top-0 z-50 shadow-2xl shadow-orange-500/10 border-b border-white/10">
                <!-- Primary Navigation Menu -->
                <div class="w-full px-4 sm:px-6 lg:px-8">
                    <div class="flex h-20 justify-between text-white">
                        <div class="flex items-center gap-2 lg:gap-4">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center gap-2 group">
                                <Link :href="route('dashboard')" class="transition-transform group-hover:scale-110 duration-300">
                                    <ApplicationLogo class="block h-12 w-12 rounded-full object-cover shadow-2xl border-2 border-white/20" />
                                </Link>
                                <div class="hidden sm:block">
                                    <span class="text-xl font-black text-white uppercase tracking-tight italic drop-shadow-sm">
                                        Pollo Broster
                                    </span>
                                    <span class="text-xl font-black text-secondary uppercase tracking-tight italic drop-shadow-sm">
                                        Crispy
                                    </span>
                                </div>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden lg:flex lg:items-center lg:gap-0.5">
                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                    Dashboard
                                </NavLink>
                                <NavLink v-if="canPOS" :href="route('pos.index')" :active="route().current('pos.index')">
                                    POS
                                </NavLink>
                                <NavDropdown label="Operaciones" :active="route().current('kitchen.index') || route().current('waiter.index')">
                                    <NavDropdownLink :href="route('kitchen.index')" :active="route().current('kitchen.index')">
                                        Cocina
                                    </NavDropdownLink>
                                    <NavDropdownLink :href="route('waiter.index')" :active="route().current('waiter.index')">
                                        Mesero
                                    </NavDropdownLink>
                                </NavDropdown>
                                <NavDropdown v-if="canProducts" label="Productos" :active="route().current('products.*') || route().current('categories.*') || route().current('products.stock*') || route().current('clients.*') || route().current('mesas.index')">
                                    <NavDropdownLink :href="route('products.index')" :active="route().current('products.index') || route().current('products.create') || route().current('products.edit')">
                                        Productos
                                    </NavDropdownLink>
                                    <NavDropdownLink :href="route('categories.index')" :active="route().current('categories.*')">
                                        Categorías
                                    </NavDropdownLink>
                                    <NavDropdownLink :href="route('products.stock')" :active="route().current('products.stock*')">
                                        Stock
                                    </NavDropdownLink>
                                    <NavDropdownLink v-if="canClients" :href="route('clients.index')" :active="route().current('clients.*')">
                                        Clientes
                                    </NavDropdownLink>
                                    <NavDropdownLink v-if="isAdmin" :href="route('mesas.index')" :active="route().current('mesas.index')">
                                        Mesas
                                    </NavDropdownLink>
                                </NavDropdown>
                                <NavDropdown v-if="isAdmin" label="Finanzas" :active="route().current('cash-register.index') || route().current('reports.index')">
                                    <NavDropdownLink :href="route('cash-register.index')" :active="route().current('cash-register.index')">
                                        Caja
                                    </NavDropdownLink>
                                    <NavDropdownLink :href="route('reports.index')" :active="route().current('reports.index')">
                                        Reportes
                                    </NavDropdownLink>
                                </NavDropdown>
                                <NavDropdown v-if="isAdmin" label="Admin" :active="route().current('users.*') || route().current('audit-logs.*') || route().current('cancellations.*') || route().current('settings.*')">
                                    <NavDropdownLink :href="route('users.index')" :active="route().current('users.*')">
                                        Personal
                                    </NavDropdownLink>
                                    <NavDropdownLink :href="route('audit-logs.index')" :active="route().current('audit-logs.*')">
                                        Auditoría
                                    </NavDropdownLink>
                                    <NavDropdownLink :href="route('cancellations.index')" :active="route().current('cancellations.*')">
                                        Cancelaciones
                                    </NavDropdownLink>
                                    <NavDropdownLink :href="route('settings.index')" :active="route().current('settings.*')">
                                        Configuración
                                    </NavDropdownLink>
                                </NavDropdown>
                            </div>
                        </div>

                        <div class="hidden md:ms-6 lg:flex lg:items-center gap-4">
                            <!-- Dark Mode Toggle -->
                            <button @click="theme.toggle"
                                class="p-2 rounded-full text-white/80 hover:text-white hover:bg-white/10 transition-all"
                                :title="theme.isDark ? 'Modo claro' : 'Modo oscuro'">
                                <svg v-if="!theme.isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                </svg>
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </button>
                            <!-- Settings Dropdown -->
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-full">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-full border-2 border-white/20 bg-white/10 px-4 py-2 text-sm font-medium text-white transition duration-150 ease-in-out hover:bg-white/20 focus:outline-none"
                                            >
                                                <div class="mr-2 flex h-8 w-8 items-center justify-center rounded-full bg-secondary text-text font-bold text-xs uppercase shadow-inner">
                                                    {{ $page.props.auth.user.name.substring(0, 2) }}
                                                </div>
                                                <span class="hidden md:inline font-bold tracking-tight">{{ $page.props.auth.user.name }}</span>

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
                        <div class="-me-2 flex items-center lg:hidden">
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
                    class="lg:hidden bg-white border-t"
                >
                    <div class="space-y-1 pb-3 pt-2 px-4">
                        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                            Dashboard
                        </ResponsiveNavLink>

                        <div class="border-t border-gray-100 mx-3 mt-2 pt-2">
                            <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider px-3">Operaciones</span>
                        </div>
                        <ResponsiveNavLink v-if="canPOS" :href="route('pos.index')" :active="route().current('pos.index')">
                            POS
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('kitchen.index')" :active="route().current('kitchen.index')">
                            Cocina
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('waiter.index')" :active="route().current('waiter.index')">
                            Mesero
                        </ResponsiveNavLink>

                        <div class="border-t border-gray-100 mx-3 mt-2 pt-2">
                            <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider px-3">Productos</span>
                        </div>
                        <ResponsiveNavLink v-if="canProducts" :href="route('products.index')" :active="route().current('products.*')">
                            Productos
                        </ResponsiveNavLink>
                        <ResponsiveNavLink v-if="canProducts" :href="route('categories.index')" :active="route().current('categories.*')">
                            Categorías
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

                        <div class="border-t border-gray-100 mx-3 mt-2 pt-2">
                            <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider px-3">Finanzas</span>
                        </div>
                        <ResponsiveNavLink v-if="isAdmin" :href="route('cash-register.index')" :active="route().current('cash-register.index')">
                            Caja
                        </ResponsiveNavLink>
                        <ResponsiveNavLink v-if="isAdmin" :href="route('reports.index')" :active="route().current('reports.index')">
                            Reportes
                        </ResponsiveNavLink>

                        <div class="border-t border-gray-100 mx-3 mt-2 pt-2">
                            <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider px-3">Administración</span>
                        </div>
                        <ResponsiveNavLink v-if="isAdmin" :href="route('users.index')" :active="route().current('users.*')">
                            Personal
                        </ResponsiveNavLink>
                        <ResponsiveNavLink v-if="isAdmin" :href="route('audit-logs.index')" :active="route().current('audit-logs.*')">
                            Auditoría
                        </ResponsiveNavLink>
                        <ResponsiveNavLink v-if="isAdmin" :href="route('cancellations.index')" :active="route().current('cancellations.*')">
                            Cancelaciones
                        </ResponsiveNavLink>
                        <ResponsiveNavLink v-if="isAdmin" :href="route('settings.index')" :active="route().current('settings.*')">
                            Configuración
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
                <Transition name="page" mode="out-in">
                    <div :key="fullWidth ? 'full' : 'padded'" :class="fullWidth ? 'bg-background' : 'p-3 sm:p-6 max-w-screen-2xl mx-auto bg-background min-h-[calc(100vh-5rem)]'">
                        <slot />
                    </div>
                </Transition>
            </main>
        </div>
        <ToastNotification />
    </div>
</template>
