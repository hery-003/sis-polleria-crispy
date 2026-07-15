<script setup>
import GuestLayout from '@/layouts/GuestLayout.vue';
import InputError from '@/components/InputError.vue';
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    email: { type: String, required: true },
    token: { type: String, required: true },
});

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Restablecer Contraseña" />

        <div class="space-y-5">
            <div class="text-center">
                <h2 class="text-2xl font-black text-gray-900">Restablecer Contraseña</h2>
                <p class="text-sm text-gray-500 mt-1">Ingresa tu nueva contraseña</p>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">Correo Electrónico</label>
                    <input
                        id="email"
                        type="email"
                        v-model="form.email"
                        required
                        autocomplete="username"
                        class="block w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 placeholder-gray-400 outline-none transition-all focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/10"
                        placeholder="correo@ejemplo.com"
                    />
                    <InputError class="mt-1" :message="form.errors.email" />
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-1.5">Nueva Contraseña</label>
                    <div class="relative">
                        <input
                            id="password"
                            :type="showPassword ? 'text' : 'password'"
                            v-model="form.password"
                            required
                            autocomplete="new-password"
                            class="block w-full px-4 py-2.5 pr-11 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 placeholder-gray-400 outline-none transition-all focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/10"
                            placeholder="Mínimo 8 caracteres"
                        />
                        <button type="button" @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors">
                            <svg v-if="showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                            <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                    <InputError class="mt-1" :message="form.errors.password" />
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-1.5">Confirmar Contraseña</label>
                    <div class="relative">
                        <input
                            id="password_confirmation"
                            :type="showPasswordConfirmation ? 'text' : 'password'"
                            v-model="form.password_confirmation"
                            required
                            autocomplete="new-password"
                            class="block w-full px-4 py-2.5 pr-11 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 placeholder-gray-400 outline-none transition-all focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/10"
                            placeholder="Repite la contraseña"
                        />
                        <button type="button" @click="showPasswordConfirmation = !showPasswordConfirmation"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors">
                            <svg v-if="showPasswordConfirmation" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                            <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                    <InputError class="mt-1" :message="form.errors.password_confirmation" />
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
                    {{ form.processing ? 'Restableciendo...' : 'Restablecer Contraseña' }}
                </button>
            </form>
        </div>
    </GuestLayout>
</template>
