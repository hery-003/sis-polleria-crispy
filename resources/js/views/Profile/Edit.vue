<script setup>
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, usePage, Link } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const page = usePage();
const user = page.props.auth?.user ?? {};
const twoFactorEnabled = !!user.two_factor_confirmed_at;
const initials = (user.name || '?').split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();

const getRoleBadge = (role) => {
    const roles = {
        'admin': { label: 'Administrador', color: 'bg-purple-100 text-purple-800' },
        'cashier': { label: 'Cajero', color: 'bg-green-100 text-green-800' },
        'waiter': { label: 'Mesero', color: 'bg-blue-100 text-blue-800' },
    };
    return roles[role] || { label: 'Usuario', color: 'bg-gray-100 text-gray-800' };
};

const roleInfo = getRoleBadge(user.role);
</script>

<template>
    <Head title="Mi Perfil" />

    <AuthenticatedLayout>
        <div>
            <!-- Header de Perfil -->
            <div class="-mx-3 sm:-mx-6 -mt-3 sm:-mt-6 bg-gradient-to-br from-primary via-orange-500 to-orange-700 text-white pb-40 pt-12 px-8 mb-8 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-96 h-96 bg-white/10 rounded-full -mr-48 -mt-48 blur-3xl animate-pulse"></div>
                <div class="absolute bottom-0 left-0 w-64 h-64 bg-black/10 rounded-full -ml-32 -mb-32 blur-2xl"></div>
                
                <div class="max-w-5xl mx-auto relative z-10 flex flex-col md:flex-row justify-between items-center gap-8">
                    <div class="animate-fade-in-up">
                        <h1 class="text-5xl font-black italic uppercase tracking-tighter font-poppins leading-none">Mi Perfil</h1>
                        <p class="text-orange-100 mt-4 font-bold text-xl opacity-80">Configuración de cuenta y seguridad</p>
                    </div>
                    <div class="hidden md:block animate-fade-in-up" style="animation-delay: 0.1s">
                        <div class="bg-white/20 backdrop-blur-xl px-6 py-3 rounded-[2rem] border border-white/30 shadow-2xl">
                            <span class="text-xs font-black uppercase tracking-widest text-white/90">ID de Usuario: #{{ user.id }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contenido Principal -->
            <div class="max-w-5xl mx-auto px-6 -mt-24 pb-12 relative z-20">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <!-- Sidebar: Info del Usuario -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-[2.5rem] shadow-2xl overflow-hidden sticky top-28 border border-white">
                            <!-- Avatar y Datos -->
                            <div class="bg-gradient-to-b from-orange-50 to-white p-10 text-center">
                                <div class="relative inline-block group">
                                    <div class="w-32 h-32 rounded-full bg-gradient-to-br from-primary to-orange-600 flex items-center justify-center text-white text-4xl font-black shadow-2xl mx-auto group-hover:scale-105 transition-transform duration-500 ring-4 ring-white/50">
                                        {{ initials }}
                                    </div>
                                    <div class="absolute bottom-0 right-0 w-10 h-10 bg-green-500 rounded-full border-4 border-white flex items-center justify-center shadow-lg">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                </div>
                                <h2 class="text-2xl font-black text-text mt-6 uppercase font-poppins tracking-tight">{{ user.name }}</h2>
                                <p class="text-sm text-gray-400 font-bold mt-1">{{ user.email }}</p>
                                <div class="mt-6">
                                    <span class="px-6 py-2 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] shadow-sm border-2" :class="roleInfo.color + ' ' + (user.role === 'admin' ? 'border-purple-200' : user.role === 'cashier' ? 'border-green-200' : 'border-blue-200')">
                                        {{ roleInfo.label }}
                                    </span>
                                </div>
                            </div>

                            <!-- Estadísticas Rápidas -->
                            <div class="p-8 border-t border-gray-50 bg-gray-50/30">
                                <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Trayectoria</h3>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-white border border-gray-100 flex items-center justify-center text-xl shadow-sm">🗓️</div>
                                    <div>
                                        <p class="text-[10px] font-black text-gray-400 uppercase">Miembro desde</p>
                                        <p class="text-sm font-black text-text">
                                            {{ user.created_at ? new Date(user.created_at).toLocaleDateString('es-PE', { year: 'numeric', month: 'short' }) : 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contenido: Formularios -->
                    <div class="lg:col-span-2 space-y-6">
                        
                        <!-- Información del Perfil -->
                        <div class="bg-white rounded-3xl shadow-xl p-8 hover:shadow-2xl transition-shadow">
                            <div class="flex items-start gap-4 mb-6">
                                <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-black text-text uppercase">Información Personal</h3>
                                    <p class="text-sm text-gray-500 font-medium mt-1">Actualiza tu nombre y correo electrónico</p>
                                </div>
                            </div>
                            <UpdateProfileInformationForm
                                :must-verify-email="mustVerifyEmail"
                                :status="status"
                            />
                        </div>

                        <!-- Cambiar Contraseña -->
                        <div class="bg-white rounded-3xl shadow-xl p-8 hover:shadow-2xl transition-shadow">
                            <div class="flex items-start gap-4 mb-6">
                                <div class="w-12 h-12 rounded-2xl bg-blue-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-black text-text uppercase">Seguridad</h3>
                                    <p class="text-sm text-gray-500 font-medium mt-1">Cambia tu contraseña para mantener tu cuenta segura</p>
                                </div>
                            </div>
                            <UpdatePasswordForm />
                        </div>

                        <!-- Autenticación en Dos Pasos -->
                        <div class="bg-white rounded-3xl shadow-xl p-8 hover:shadow-2xl transition-shadow">
                            <div class="flex items-start gap-4 mb-6">
                                <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0"
                                    :class="twoFactorEnabled ? 'bg-green-100' : 'bg-gray-100'">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        :class="twoFactorEnabled ? 'text-green-600' : 'text-gray-400'">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-lg font-black text-text uppercase">Autenticación en Dos Pasos</h3>
                                    <p class="text-sm text-gray-500 font-medium mt-1">
                                        {{ twoFactorEnabled ? '2FA está activo. Añade una capa extra de seguridad.' : 'Protege tu cuenta con verificación adicional.' }}
                                    </p>
                                </div>
                                <Link :href="route('two-factor.setup')"
                                    class="px-4 py-2 rounded-lg text-xs font-bold uppercase transition-all flex items-center gap-2"
                                    :class="twoFactorEnabled ? 'bg-green-50 text-green-700 hover:bg-green-100' : 'bg-primary text-white hover:bg-orange-700'">
                                    {{ twoFactorEnabled ? 'Configurar' : 'Activar' }}
                                </Link>
                            </div>
                        </div>

                        <!-- Eliminar Cuenta (Solo para usuarios no admin o con precaución) -->
                        <div class="bg-white rounded-3xl shadow-xl p-8 border-2 border-danger/20 hover:shadow-2xl transition-shadow">
                            <div class="flex items-start gap-4 mb-6">
                                <div class="w-12 h-12 rounded-2xl bg-danger/10 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-black text-danger uppercase">Zona de Peligro</h3>
                                    <p class="text-sm text-gray-500 font-medium mt-1">Esta acción no se puede deshacer. Todos tus datos serán eliminados permanentemente.</p>
                                </div>
                            </div>
                            <DeleteUserForm />
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
