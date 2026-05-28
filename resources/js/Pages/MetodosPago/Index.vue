<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import DangerButton from '@/Components/DangerButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import Modal from '@/Components/Modal.vue'
import { useToast } from '@/Plugins/toast'

const toast = useToast()
const props = defineProps({
    metodos: Array
})

const showCreate = ref(false)
const showEdit = ref(false)
const form = ref({ id: null, name: '', slug: '', is_active: true })
const errors = ref({})

const openCreate = () => {
    form.value = { id: null, name: '', slug: '', is_active: true }
    errors.value = {}
    showCreate.value = true
}

const openEdit = (metodo) => {
    form.value = { ...metodo }
    errors.value = {}
    showEdit.value = true
}

const submitCreate = () => {
    router.post(route('metodos-pago.store'), form.value, {
        onError: (e) => { errors.value = e; return false },
        onSuccess: () => { showCreate.value = false }
    })
}

const submitEdit = () => {
    router.put(route('metodos-pago.update', form.value.id), form.value, {
        onError: (e) => { errors.value = e; return false },
        onSuccess: () => { showEdit.value = false }
    })
}

const destroy = (id) => {
    if (confirm('¿Eliminar este método de pago?')) {
        router.delete(route('metodos-pago.destroy', id))
    }
}

const generateSlug = () => {
    if (form.value.name) {
        form.value.slug = form.value.name.toLowerCase()
            .replace(/\s+/g, '-')
            .replace(/[^\w\-]+/g, '')
    }
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-text">Métodos de Pago</h2>
                <PrimaryButton @click="openCreate">Nuevo Método</PrimaryButton>
            </div>
        </template>

        <div>
            <div class="bg-white overflow-hidden shadow-xl rounded-2xl">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b-2 border-primary/20">
                                        <th class="text-left py-3 px-4 font-semibold text-text">Nombre</th>
                                        <th class="text-left py-3 px-4 font-semibold text-text">Slug</th>
                                        <th class="text-center py-3 px-4 font-semibold text-text">Estado</th>
                                        <th class="text-right py-3 px-4 font-semibold text-text">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="metodo in metodos" :key="metodo.id"
                                        class="border-b border-gray-100 hover:bg-orange-50/50 transition-colors duration-150">
                                        <td class="py-3 px-4">
                                            <div class="font-medium text-text">{{ metodo.name }}</div>
                                        </td>
                                        <td class="py-3 px-4">
                                            <code class="text-sm bg-gray-100 px-2 py-1 rounded">{{ metodo.slug }}</code>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full"
                                                :class="metodo.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-600'">
                                                {{ metodo.is_active ? 'Activo' : 'Inactivo' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <div class="flex gap-2 justify-end">
                                                <SecondaryButton @click="openEdit(metodo)" class="text-xs px-3 py-1">Editar</SecondaryButton>
                                                <DangerButton @click="destroy(metodo.id)" class="text-xs px-3 py-1">Eliminar</DangerButton>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Crear -->
        <Modal :show="showCreate" @close="showCreate = false">
            <div class="p-6 bg-white rounded-2xl w-full max-w-md mx-auto animate-scale-in">
                <h3 class="text-xl font-bold text-text mb-4">Nuevo Método de Pago</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-text mb-1">Nombre</label>
                        <input v-model="form.name" type="text" placeholder="Ej: Yape/Plin" @blur="generateSlug"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary" />
                        <p v-if="errors.name" class="text-danger text-sm mt-1">{{ errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text mb-1">Slug</label>
                        <input v-model="form.slug" type="text" placeholder="Ej: yape"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary" />
                        <p v-if="errors.slug" class="text-danger text-sm mt-1">{{ errors.slug }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" v-model="form.is_active" id="is_active" class="rounded" />
                        <label for="is_active" class="text-sm text-text">Activo</label>
                    </div>
                    <div class="flex gap-3 pt-4">
                        <SecondaryButton @click="showCreate = false" class="flex-1">Cancelar</SecondaryButton>
                        <PrimaryButton @click="submitCreate" class="flex-1">Crear</PrimaryButton>
                    </div>
                </div>
            </div>
        </Modal>

        <!-- Modal Editar -->
        <Modal :show="showEdit" @close="showEdit = false">
            <div class="p-6 bg-white rounded-2xl w-full max-w-md mx-auto animate-scale-in">
                <h3 class="text-xl font-bold text-text mb-4">Editar Método de Pago</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-text mb-1">Nombre</label>
                        <input v-model="form.name" type="text" @blur="generateSlug"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary" />
                        <p v-if="errors.name" class="text-danger text-sm mt-1">{{ errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text mb-1">Slug</label>
                        <input v-model="form.slug" type="text"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary" />
                        <p v-if="errors.slug" class="text-danger text-sm mt-1">{{ errors.slug }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" v-model="form.is_active" id="edit_is_active" class="rounded" />
                        <label for="edit_is_active" class="text-sm text-text">Activo</label>
                    </div>
                    <div class="flex gap-3 pt-4">
                        <SecondaryButton @click="showEdit = false" class="flex-1">Cancelar</SecondaryButton>
                        <PrimaryButton @click="submitEdit" class="flex-1">Guardar</PrimaryButton>
                    </div>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
