<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

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
            <div class="-mx-6 -mt-6 bg-gradient-to-r from-primary to-orange-600 text-white pb-32 pt-8 px-6 mb-8">
                <div class="max-w-5xl mx-auto">
                    <h1 class="text-3xl font-black italic uppercase tracking-tight">Mi Perfil</h1>
                    <p class="text-orange-100 mt-1 font-medium">Gestiona tu información personal y seguridad</p>
                </div>
            </div>

            <!-- Contenido Principal -->
            <div class="max-w-5xl mx-auto px-6 -mt-20 pb-12">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Sidebar: Info del Usuario -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-3xl shadow-xl overflow-hidden sticky top-24">
                            <!-- Avatar y Datos -->
                            <div class="bg-gradient-to-b from-primary/10 to-white p-8 text-center">
                                <div class="relative inline-block">
                                    <div class="w-28 h-28 rounded-full bg-gradient-to-br from-primary to-orange-600 flex items-center justify-center text-white text-3xl font-black shadow-lg mx-auto">
                                        {{ initials }}
                                    </div>
                                    <div class="absolute bottom-0 right-0 w-8 h-8 bg-green-500 rounded-full border-4 border-white flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                </div>
                                <h2 class="text-xl font-black text-text mt-4 uppercase">{{ user.name }}</h2>
                                <p class="text-sm text-gray-500 font-medium">{{ user.email }}</p>
                                <span class="inline-block mt-3 px-4 py-1 rounded-full text-xs font-black uppercase tracking-wider" :class="roleInfo.color">
                                    {{ roleInfo.label }}
                                </span>
                            </div>

                            <!-- Estadísticas Rápidas -->
                            <div class="p-6 border-t border-gray-100">
                                <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Miembros desde</h3>
                                <p class="text-sm font-bold text-text">
                                    {{ user.created_at ? new Date(user.created_at).toLocaleDateString('es-PE', { year: 'numeric', month: 'long', day: 'numeric' }) : 'N/A' }}
                                </p>
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
