<script setup>
import GuestLayout from '@/layouts/GuestLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'

const form = useForm({ code: '' })

const submit = () => {
    form.post(route('two-factor.challenge'), {
        onSuccess: () => form.reset(),
    })
}
</script>

<template>
    <GuestLayout>
        <Head title="Verificación en Dos Pasos" />
        <div class="space-y-5">
            <div class="text-center">
                <h2 class="text-2xl font-black text-gray-900">Autenticación en Dos Pasos</h2>
                <p class="text-sm text-gray-500 mt-1">Ingresa el código de 6 dígitos de tu app autenticadora</p>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <input v-model="form.code" type="text" maxlength="6" placeholder="000000" autofocus
                        class="w-full text-center text-3xl tracking-[0.5em] px-4 py-4 bg-gray-50 border-2 border-gray-200 rounded-xl font-bold focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all" />
                    <p v-if="form.errors.code" class="text-red-600 text-xs mt-1.5 font-medium text-center">{{ form.errors.code }}</p>
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
                    {{ form.processing ? 'Verificando...' : 'Verificar' }}
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
                    <a :href="route('login')"
                        class="font-semibold text-primary hover:text-orange-700 transition-colors">
                        Volver al Inicio de Sesión
                    </a>
                </p>
            </form>
        </div>
    </GuestLayout>
</template>
