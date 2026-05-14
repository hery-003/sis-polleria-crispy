<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="space-y-6">
        <div class="bg-danger/5 border-2 border-danger/20 rounded-2xl p-6">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-danger/10 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="font-bold text-danger uppercase text-sm">Eliminar mi cuenta</h3>
                    <p class="text-sm text-gray-600 mt-1">
                        Una vez que elimines tu cuenta, todos sus recursos y datos serán eliminados permanentemente. Esta acción no se puede deshacer.
                    </p>
                    <DangerButton @click="confirmUserDeletion" class="mt-4 rounded-xl px-5 py-2.5 text-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Eliminar Cuenta Permanentemente
                    </DangerButton>
                </div>
            </div>
        </div>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-8 bg-white rounded-3xl max-w-md mx-auto">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 rounded-full bg-danger/10 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-black text-text uppercase">¿Estás completamente seguro?</h2>
                    <p class="text-sm text-gray-500 mt-2">
                        Esta acción eliminará permanentemente tu cuenta y todos los datos asociados. No hay vuelta atrás.
                    </p>
                </div>

                <div class="bg-gray-50 rounded-2xl p-4 mb-6">
                    <InputLabel for="password" value="Ingresa tu contraseña para confirmar" class="text-xs font-black text-gray-500 uppercase tracking-widest" />
                    <TextInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="mt-2 block w-full rounded-xl border-gray-200 focus:border-danger focus:ring-danger transition-all"
                        placeholder="Tu contraseña actual"
                        @keyup.enter="deleteUser"
                    />
                    <InputError :message="form.errors.password" class="mt-2" />
                </div>

                <div class="flex gap-3">
                    <SecondaryButton @click="closeModal" class="flex-1 rounded-xl py-3">
                        Cancelar
                    </SecondaryButton>
                    <DangerButton
                        class="flex-1 rounded-xl py-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        <span v-if="form.processing">Eliminando...</span>
                        <span v-else>Sí, Eliminar</span>
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </section>
</template>
