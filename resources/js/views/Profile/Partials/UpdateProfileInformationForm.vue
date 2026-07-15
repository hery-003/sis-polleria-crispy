<script setup>
import InputError from '@/components/InputError.vue';
import InputLabel from '@/components/InputLabel.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import TextInput from '@/components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <section>
        <form @submit.prevent="form.patch(route('profile.update'))" class="space-y-5">
            <div>
                <InputLabel for="name" value="Nombre Completo" class="text-xs font-black text-gray-500 uppercase tracking-widest" />
                <TextInput
                    id="name"
                    type="text"
                    class="mt-2 block w-full rounded-xl border-gray-200 focus:border-primary focus:ring-primary transition-all"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="Tu nombre completo"
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Correo Electrónico" class="text-xs font-black text-gray-500 uppercase tracking-widest" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-2 block w-full rounded-xl border-gray-200 focus:border-primary focus:ring-primary transition-all"
                    v-model="form.email"
                    required
                    autocomplete="username"
                    placeholder="tu@correo.com"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-sm text-gray-800 bg-yellow-50 p-4 rounded-xl border border-yellow-100">
                    <span class="font-bold">⚠️ Tu correo no está verificado.</span>
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="text-primary font-bold underline hover:no-underline"
                    >
                        Haz clic aquí para reenviar el correo.
                    </Link>
                </p>

                <div v-show="status === 'verification-link-sent'" class="mt-2 text-sm font-bold text-green-600 bg-green-50 p-3 rounded-xl">
                    ✅ Se ha enviado un nuevo enlace de verificación.
                </div>
            </div>

            <div class="flex items-center gap-4 pt-2">
                <PrimaryButton :disabled="form.processing" class="rounded-xl px-6 py-3">
                    <span v-if="form.processing">Guardando...</span>
                    <span v-else>Guardar Cambios</span>
                </PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0 translate-x-2"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-if="form.recentlySuccessful" class="text-sm font-bold text-green-600 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        ¡Guardado con éxito!
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
