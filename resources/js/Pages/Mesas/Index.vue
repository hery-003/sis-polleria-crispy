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
    mesas: Array
})

const showCreate = ref(false)
const showEdit = ref(false)
const form = ref({ id: null, name: '', number: '', is_active: true })
const errors = ref({})

const openCreate = () => {
    form.value = { id: null, name: '', number: '', is_active: true }
    errors.value = {}
    showCreate.value = true
}

const openEdit = (mesa) => {
    form.value = { ...mesa }
    errors.value = {}
    showEdit.value = true
}

const submitCreate = () => {
    router.post(route('mesas.store'), form.value, {
        onError: (e) => { errors.value = e; return false },
        onSuccess: () => { showCreate.value = false }
    })
}

const submitEdit = () => {
    router.put(route('mesas.update', form.value.id), form.value, {
        onError: (e) => { errors.value = e; return false },
        onSuccess: () => { showEdit.value = false }
    })
}

const destroy = (id) => {
    if (confirm('¿Eliminar esta mesa?')) {
        router.delete(route('mesas.destroy', id))
    }
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-text">Gestión de Mesas</h2>
                <PrimaryButton @click="openCreate">Nueva Mesa</PrimaryButton>
            </div>
        </template>

        <div>
            <div class="bg-white overflow-hidden shadow-xl rounded-2xl">
                    <div class="p-6">
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            <div v-for="mesa in mesas" :key="mesa.id"
                                class="border-2 rounded-2xl p-6 text-center transition-all duration-200 hover:shadow-lg"
                                :class="mesa.is_active ? 'border-primary bg-orange-50' : 'border-gray-200 bg-gray-50 opacity-60'">
                                <div class="text-4xl mb-3">🪑</div>
                                <div class="font-bold text-lg text-text">{{ mesa.name }}</div>
                                <div class="text-sm text-gray-500">Nº {{ mesa.number }}</div>
                                <div class="mt-2">
                                    <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full"
                                        :class="mesa.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-600'">
                                        {{ mesa.is_active ? 'Activa' : 'Inactiva' }}
                                    </span>
                                </div>
                                <div class="flex gap-2 mt-4 justify-center">
                                    <SecondaryButton @click="openEdit(mesa)" class="text-xs px-3 py-1">Editar</SecondaryButton>
                                    <DangerButton @click="destroy(mesa.id)" class="text-xs px-3 py-1">Eliminar</DangerButton>
                                </div>
                            </div>
                        </div>
                </div>
            </div>
        </div>

        <!-- Modal Crear -->
        <Modal :show="showCreate" @close="showCreate = false">
            <div class="p-6 bg-white rounded-2xl w-full max-w-md mx-auto animate-scale-in">
                <h3 class="text-xl font-bold text-text mb-4">Nueva Mesa</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-text mb-1">Nombre</label>
                        <input v-model="form.name" type="text" placeholder="Ej: Mesa Principal"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary" />
                        <p v-if="errors.name" class="text-danger text-sm mt-1">{{ errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text mb-1">Número</label>
                        <input v-model="form.number" type="number" placeholder="Ej: 1"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary" />
                        <p v-if="errors.number" class="text-danger text-sm mt-1">{{ errors.number }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" v-model="form.is_active" id="is_active" class="rounded" />
                        <label for="is_active" class="text-sm text-text">Activa</label>
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
                <h3 class="text-xl font-bold text-text mb-4">Editar Mesa</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-text mb-1">Nombre</label>
                        <input v-model="form.name" type="text"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary" />
                        <p v-if="errors.name" class="text-danger text-sm mt-1">{{ errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text mb-1">Número</label>
                        <input v-model="form.number" type="number"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary" />
                        <p v-if="errors.number" class="text-danger text-sm mt-1">{{ errors.number }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" v-model="form.is_active" id="edit_is_active" class="rounded" />
                        <label for="edit_is_active" class="text-sm text-text">Activa</label>
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
