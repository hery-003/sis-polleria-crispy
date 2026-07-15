<script setup>
import GuestLayout from '@/layouts/GuestLayout.vue';
import InputError from '@/components/InputError.vue';
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const showPassword = ref(false);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onSuccess: () => form.reset('password'),
        onError: () => {},
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Iniciar Sesión" />

        <div class="space-y-8">
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-primary/10 rounded-2xl mb-4 shadow-inner">
                    <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <h2 class="text-3xl font-black text-text uppercase tracking-tight font-poppins">Acceso al Sistema</h2>
                <p class="text-sm text-gray-400 mt-2 font-bold italic">“Crujiente y Sabor Real!”</p>
            </div>

            <div v-if="status" class="p-4 bg-green-500/10 border-2 border-green-500/20 rounded-2xl text-sm font-black text-green-600 text-center animate-bounce-in">
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="space-y-6" novalidate>
                <div class="space-y-1.5">
                    <label for="email" class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] ml-1">Correo Electrónico</label>
                    <div class="relative group">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-primary transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206" />
                            </svg>
                        </div>
                        <input
                            id="email"
                            type="email"
                            v-model="form.email"
                            required
                            autofocus
                            autocomplete="username"
                            class="block w-full pl-12 pr-4 py-4 bg-gray-50 border-2 border-gray-100 rounded-2xl text-sm font-bold text-text placeholder-gray-300 outline-none transition-all focus:bg-white focus:border-primary focus:ring-8 focus:ring-primary/5 shadow-inner"
                            placeholder="usuario@polleria.com"
                        />
                    </div>
                    <InputError class="mt-1 ml-1" :message="form.errors.email" />
                </div>

                <div class="space-y-1.5">
                    <label for="password" class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] ml-1">Contraseña</label>
                    <div class="relative group">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-primary transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input
                            id="password"
                            :type="showPassword ? 'text' : 'password'"
                            v-model="form.password"
                            required
                            autocomplete="current-password"
                            class="block w-full pl-12 pr-12 py-4 bg-gray-50 border-2 border-gray-100 rounded-2xl text-sm font-bold text-text placeholder-gray-300 outline-none transition-all focus:bg-white focus:border-primary focus:ring-8 focus:ring-primary/5 shadow-inner"
                            placeholder="••••••••"
                        />
                        <button type="button" @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-primary transition-colors">
                            <svg v-if="showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268-2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                            <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268-2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                    <InputError class="mt-1 ml-1" :message="form.errors.password" />
                </div>

                <div class="flex items-center justify-between px-1">
                    <label class="flex items-center gap-2 cursor-pointer select-none group">
                        <div class="relative">
                            <input type="checkbox" v-model="form.remember" class="sr-only peer" />
                            <div class="w-5 h-5 rounded-lg border-2 border-gray-200 peer-checked:border-primary peer-checked:bg-primary transition-all group-hover:border-primary/50 flex items-center justify-center">
                                <svg class="w-3 h-3 text-white opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </div>
                        <span class="text-xs font-black text-gray-500 uppercase tracking-widest group-hover:text-text transition-colors">Recordarme</span>
                    </label>
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-[10px] font-black text-primary uppercase tracking-widest hover:text-orange-700 transition-colors"
                    >
                        ¿Olvido Clave?
                    </Link>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full py-4 bg-gradient-to-br from-primary to-orange-700 hover:to-orange-800 text-sm font-black text-white rounded-2xl transition-all shadow-xl shadow-orange-500/20 active:scale-95 flex items-center justify-center gap-3 group uppercase tracking-widest"
                >
                    <svg v-if="form.processing" class="animate-spin h-5 w-5" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                    </svg>
                    <span v-else class="flex items-center gap-2">
                        <span>ENTRAR AL SISTEMA</span>
                        <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </button>

                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-100"></div>
                    </div>
                    <div class="relative flex justify-center">
                        <span class="px-3 bg-white text-xs text-gray-300">o</span>
                    </div>
                </div>

                <p class="text-center text-sm text-gray-500">
                    ¿No tienes cuenta?
                    <Link :href="route('register')" class="font-semibold text-primary hover:text-orange-700 transition-colors">
                        Regístrate
                    </Link>
                </p>
            </form>
        </div>
    </GuestLayout>
</template>
