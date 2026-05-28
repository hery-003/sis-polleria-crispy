<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
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
        onSuccess: () => {
            form.reset('password');
        },
        onError: (errors) => {
            console.error('Login errors:', errors);
        },
    });
};

const validateEmail = () => {
    if (form.email && !form.email.includes('@')) {
        form.errors.email = 'Ingrese un correo válido';
    } else {
        delete form.errors.email;
    }
};

const validatePassword = () => {
    if (form.password && form.password.length < 8) {
        form.errors.password = 'Mínimo 8 caracteres';
    } else {
        delete form.errors.password;
    }
};
</script>

<template>
    <GuestLayout>
        <Head title="Iniciar Sesión" />

        <div class="animate-fade-in-down">
            <div v-if="status" class="mb-4 text-sm font-medium text-green-600 text-center">
            {{ status }}
            </div>

            <form @submit.prevent="submit" class="space-y-4 bg-white p-8 rounded-3xl shadow-xl border border-gray-100">
            <div>
                <InputLabel for="email" value="Correo Electrónico" class="text-text" />
                <div class="mt-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <TextInput
                        id="email"
                        type="email"
                        class="pl-10 block w-full"
                        v-model="form.email"
                        @input="validateEmail"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="correo@ejemplo.com"
                    />
                </div>
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Contraseña" class="text-text" />
                <div class="mt-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <TextInput
                        id="password"
                        :type="showPassword ? 'text' : 'password'"
                        class="pl-10 pr-10 block w-full"
                        v-model="form.password"
                        @input="validatePassword"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
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
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center">
                    <input type="checkbox" v-model="form.remember" class="rounded border-gray-300 text-primary focus:ring-primary" />
                    <span class="ml-2 text-sm text-gray-600">Recordarme</span>
                </label>
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm text-primary hover:underline"
                >
                    ¿Olvidaste tu contraseña?
                </Link>
            </div>

            <div>
                <PrimaryButton
                    type="submit"
                    class="w-full justify-center py-3 text-lg"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Iniciar Sesión
                </PrimaryButton>
            </div>

            <div class="text-center mt-4">
                <span class="text-sm text-gray-600">¿No tienes cuenta? </span>
                <Link
                    :href="route('register')"
                    class="text-sm text-primary font-bold hover:underline"
                >
                    Regístrate aquí
                </Link>
            </div>
        </form>
        </div>
    </GuestLayout>
</template>

<style scoped>
@keyframes fade-in-down {
    0% {
        opacity: 0;
        transform: translateY(-20px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}
.animate-fade-in-down {
    animation: fade-in-down 0.5s ease-out;
}
</style>
