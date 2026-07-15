<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/components/PrimaryButton.vue'
import SecondaryButton from '@/components/SecondaryButton.vue'
import Card from '@/components/Card.vue'

const props = defineProps({
    settings: Object,
})

const form = ref({
    session_lifetime: props.settings.session_lifetime || '120',
    app_name: props.settings.app_name || 'POLLO BROSTER CRISPY',
})

const errors = ref({})

const submit = () => {
    router.put(route('settings.update'), form.value, {
        onError: (e) => { errors.value = e; return false },
        onSuccess: () => {},
    })
}
</script>

<template>
    <Head title="Configuración" />
    <AuthenticatedLayout>
        <div class="pb-6">
            <div class="-mx-3 sm:-mx-6 -mt-3 sm:-mt-6 bg-gradient-to-r from-primary via-orange-500 to-orange-600 text-white p-6 sm:p-8 shadow-2xl relative overflow-hidden mb-8">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-32 -mt-32 blur-3xl"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-black/10 rounded-full -ml-24 -mb-24 blur-2xl"></div>
                <div class="relative z-10">
                    <h1 class="text-3xl font-black italic uppercase tracking-tight">Configuración del Sistema</h1>
                    <p class="text-orange-100 mt-1 font-bold text-sm">Personaliza los parámetros del negocio</p>
                </div>
            </div>

            <div class="max-w-2xl mx-auto">
                <Card padding="p-8">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center">
                            <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-text uppercase">Parámetros Generales</h3>
                            <p class="text-sm text-gray-500 font-bold mt-1">Configura el nombre del negocio y la sesión</p>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Nombre del Negocio</label>
                            <div class="mt-1 relative">
                                <input v-model="form.app_name" type="text"
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none font-bold transition-all" />
                            </div>
                            <p v-if="errors.app_name" class="text-danger text-sm mt-1 font-bold">{{ errors.app_name }}</p>
                        </div>

                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Tiempo de Sesión (minutos)</label>
                            <div class="mt-1 relative">
                                <input v-model.number="form.session_lifetime" type="number" min="1" max="1440"
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none font-bold transition-all" />
                            </div>
                            <p class="text-xs text-gray-400 font-bold mt-1">Minutos antes de cerrar sesión por inactividad (1–1440)</p>
                            <p v-if="errors.session_lifetime" class="text-danger text-sm mt-1 font-bold">{{ errors.session_lifetime }}</p>
                        </div>

                        <div class="flex gap-3 pt-4 border-t border-gray-100">
                            <SecondaryButton @click="form = { session_lifetime: props.settings.session_lifetime || '120', app_name: props.settings.app_name || 'POLLO BROSTER CRISPY' }">
                                Restablecer
                            </SecondaryButton>
                            <PrimaryButton @click="submit" class="flex-1 shadow-lg shadow-orange-200">Guardar Configuración</PrimaryButton>
                        </div>
                    </div>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
