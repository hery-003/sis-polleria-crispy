<script setup>
import GuestLayout from '@/layouts/GuestLayout.vue';
import InputError from '@/components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: { type: String },
});

const form = useForm({ email: '' });

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Recuperar Contraseña" />

        <div class="space-y-5">
            <div class="text-center">
                <h2 class="text-2xl font-black text-gray-900">Recuperar Contraseña</h2>
                <p class="text-sm text-gray-500 mt-1">Ingresa tu correo y te enviaremos un enlace de restablecimiento</p>
            </div>

            <div v-if="status" class="p-3 bg-green-50 border border-green-200 rounded-xl text-sm font-medium text-green-700 text-center">
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">Correo Electrónico</label>
                    <input
                        id="email"
                        type="email"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="username"
                        class="block w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 placeholder-gray-400 outline-none transition-all focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/10"
                        placeholder="correo@ejemplo.com"
                    />
                    <InputError class="mt-1" :message="form.errors.email" />
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full py-2.5 bg-primary hover:bg-orange-700 text-sm font-bold text-white rounded-xl transition-all disabled:opacity-60 disabled:cursor-not-allowed active:scale-[0.98] flex items-center justify-center gap-2 shadow-sm"
                >
                    <svg v-if="form.processing" class="animate-spin h-4 w-4" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                    </svg>
                    {{ form.processing ? 'Enviando...' : 'Enviar Enlace de Recuperación' }}
                </button>

                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-100"></div>
                    </div>
                    <div class="relative flex justify-center">
                        <span class="px-3 bg-white text-xs text-gray-300">o</span>
                    </div>
                </div>

                <p class="text-center text-sm">
                    <Link :href="route('login')" class="font-semibold text-primary hover:text-orange-700 transition-colors">
                        Volver al Inicio de Sesión
                    </Link>
                </p>
            </form>
        </div>
    </GuestLayout>
</template>
