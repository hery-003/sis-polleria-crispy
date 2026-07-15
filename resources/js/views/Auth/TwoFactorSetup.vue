<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'
import Card from '@/components/Card.vue'

const props = defineProps({
    qrCodeUrl: String,
    secret: String,
    enabled: Boolean,
})

const form = useForm({ code: '' })
const disableForm = useForm({})

const confirm = () => {
    form.post(route('two-factor.confirm'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    })
}

const disable = () => {
    disableForm.post(route('two-factor.disable'), {
        preserveScroll: true,
    })
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="pb-6">
            <div class="-mx-3 sm:-mx-6 -mt-3 sm:-mt-6 bg-gradient-to-r from-primary via-orange-500 to-orange-600 text-white p-6 sm:p-8 shadow-2xl relative overflow-hidden mb-8">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-32 -mt-32 blur-3xl"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-black/10 rounded-full -ml-24 -mb-24 blur-2xl"></div>
                <div class="relative z-10">
                    <div class="flex items-center gap-4">
                        <Link :href="route('profile.edit')"
                            class="w-10 h-10 flex items-center justify-center bg-white/20 rounded-xl hover:bg-white/30 transition-all backdrop-blur-sm">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </Link>
                        <div>
                            <h1 class="text-3xl font-black italic uppercase tracking-tight">Autenticación en Dos Pasos</h1>
                            <p class="text-orange-100 mt-1 font-bold text-sm">Configura 2FA para tu cuenta</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-2xl mx-auto">
                <Card v-if="!enabled" padding="p-8">
                    <div class="text-center mb-8">
                        <div class="w-16 h-16 mx-auto bg-primary/10 rounded-2xl flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-black text-gray-900 uppercase">Activar 2FA</h3>
                        <p class="text-sm text-gray-500 mt-2 font-medium">Escanea el código QR con Google Authenticator</p>
                    </div>

                    <div class="flex justify-center mb-8">
                        <div class="bg-gray-50 rounded-2xl p-6 border-2 border-dashed border-gray-200">
                            <img :src="qrCodeUrl" alt="QR Code" class="w-48 h-48" />
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-5 mb-8">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-2">O ingresa manualmente:</p>
                        <code class="block text-sm font-mono bg-white p-3 rounded-xl border border-gray-200 text-center select-all font-bold">{{ secret }}</code>
                    </div>

                    <div class="mb-6">
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-widest mb-2">Código de verificación</label>
                        <input v-model="form.code" type="text" maxlength="6" placeholder="000000"
                            class="w-full text-center text-2xl tracking-[0.5em] px-4 py-3.5 bg-white border border-gray-200 rounded-xl font-bold focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all" />
                        <p v-if="form.errors.code" class="text-red-600 text-xs mt-1 font-medium">{{ form.errors.code }}</p>
                    </div>

                    <button @click="confirm" :disabled="form.processing"
                        class="w-full py-2.5 bg-primary hover:bg-orange-700 text-sm font-bold text-white rounded-xl transition-all disabled:opacity-60 disabled:cursor-not-allowed active:scale-[0.98] flex items-center justify-center gap-2 shadow-sm">
                        <svg v-if="form.processing" class="animate-spin h-4 w-4" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                        </svg>
                        {{ form.processing ? 'Activando...' : 'Confirmar y Activar' }}
                    </button>
                </Card>

                <Card v-else padding="p-8">
                    <div class="text-center space-y-5">
                        <div class="w-16 h-16 mx-auto bg-green-100 rounded-2xl flex items-center justify-center">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-black text-green-600 uppercase">2FA Activado</h3>
                        <p class="text-sm text-gray-500 font-medium">Tu cuenta está protegida con autenticación en dos pasos.</p>
                        <button @click="disable" :disabled="disableForm.processing"
                            class="px-8 py-2.5 bg-red-50 text-red-600 rounded-xl font-bold text-sm uppercase hover:bg-red-100 transition-all active:scale-95 flex items-center justify-center gap-2 mx-auto">
                            <svg v-if="disableForm.processing" class="animate-spin h-4 w-4" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                            </svg>
                            Desactivar 2FA
                        </button>
                    </div>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
