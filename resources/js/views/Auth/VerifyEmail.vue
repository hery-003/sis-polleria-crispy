<script setup>
import { computed } from 'vue';
import GuestLayout from '@/layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: { type: String },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>
        <Head title="Verificar Correo" />

        <div class="space-y-5">
            <div class="text-center">
                <h2 class="text-2xl font-black text-gray-900">Verificar Correo</h2>
                <p class="text-sm text-gray-500 mt-1">Gracias por registrarte! Verifica tu correo para continuar</p>
            </div>

            <div v-if="verificationLinkSent"
                class="p-3 bg-green-50 border border-green-200 rounded-xl text-sm font-medium text-green-700 text-center">
                Se ha enviado un nuevo enlace de verificación a tu correo.
            </div>

            <div class="bg-gray-50 rounded-xl p-5 text-center">
                <p class="text-sm text-gray-600 font-medium">
                    Antes de continuar, revisa tu bandeja de entrada y haz clic en el enlace de verificación.
                </p>
                <p class="text-xs text-gray-400 font-medium mt-2">
                    Si no recibiste el correo, haz clic en el botón para reenviarlo.
                </p>
            </div>

            <button
                @click="submit"
                :disabled="form.processing"
                class="w-full py-2.5 bg-primary hover:bg-orange-700 text-sm font-bold text-white rounded-xl transition-all disabled:opacity-60 disabled:cursor-not-allowed active:scale-[0.98] flex items-center justify-center gap-2 shadow-sm"
            >
                <svg v-if="form.processing" class="animate-spin h-4 w-4" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                </svg>
                {{ form.processing ? 'Enviando...' : 'Reenviar Correo de Verificación' }}
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
                <Link :href="route('logout')" method="post" as="button"
                    class="font-semibold text-gray-500 hover:text-danger transition-colors">
                    Cerrar Sesión
                </Link>
            </p>
        </div>
    </GuestLayout>
</template>
