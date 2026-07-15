<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/components/PrimaryButton.vue'
import DangerButton from '@/components/DangerButton.vue'
import SecondaryButton from '@/components/SecondaryButton.vue'
import Modal from '@/components/Modal.vue'
import { useToast } from '@/plugins/toast'

const toast = useToast()
const props = defineProps({
    mesas: Array
})

const showCreate = ref(false)
const showEdit = ref(false)
const showDeleteConfirm = ref(false)
const deletingId = ref(null)
const form = ref({ id: null, name: '', number: '', is_active: true, reserved_at: '' })
const errors = ref({})

const statusConfig = {
    available: { label: 'Libre', bg: 'bg-green-500', badge: 'bg-green-100 text-green-800', icon: '🟢', border: 'border-green-400', shadow: 'shadow-green-100' },
    occupied: { label: 'Ocupada', bg: 'bg-danger', badge: 'bg-red-100 text-red-800', icon: '🔴', border: 'border-danger', shadow: 'shadow-red-100' },
    reserved: { label: 'Reservada', bg: 'bg-blue-500', badge: 'bg-blue-100 text-blue-800', icon: '🔵', border: 'border-blue-400', shadow: 'shadow-blue-100' },
    inactive: { label: 'Inactiva', bg: 'bg-gray-400', badge: 'bg-gray-200 text-gray-600', icon: '⚪', border: 'border-gray-300', shadow: 'shadow-gray-100' },
}

const getStatus = (mesa) => mesa.status || 'inactive'

const openCreate = () => {
    form.value = { id: null, name: '', number: '', is_active: true }
    errors.value = {}
    showCreate.value = true
}

const openEdit = (mesa) => {
    const reserved_at = mesa.reserved_at ? mesa.reserved_at.substring(0, 16) : ''
    form.value = { id: mesa.id, name: mesa.name, number: mesa.number, is_active: mesa.is_active, reserved_at }
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

const confirmDelete = (id) => {
    deletingId.value = id
    showDeleteConfirm.value = true
}

const destroy = () => {
    router.delete(route('mesas.destroy', deletingId.value), {
        onSuccess: () => { showDeleteConfirm.value = false; deletingId.value = null }
    })
}

const stats = computed(() => {
    const total = props.mesas.length
    const available = props.mesas.filter(m => getStatus(m) === 'available').length
    const occupied = props.mesas.filter(m => getStatus(m) === 'occupied').length
    const reserved = props.mesas.filter(m => getStatus(m) === 'reserved').length
    const inactive = props.mesas.filter(m => getStatus(m) === 'inactive').length
    return { total, available, occupied, reserved, inactive }
})
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center flex-wrap gap-2">
                <h2 class="font-semibold text-xl text-text">Mapa de Mesas</h2>
                <PrimaryButton @click="openCreate">Nueva Mesa</PrimaryButton>
            </div>
        </template>

        <!-- Stats bar -->
            <div class="grid grid-cols-5 gap-3 mb-6">
                <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-green-500">
                    <p class="text-xs text-gray-500 uppercase font-bold">Libres</p>
                    <p class="text-2xl font-black text-green-600">{{ stats.available }}</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-danger">
                    <p class="text-xs text-gray-500 uppercase font-bold">Ocupadas</p>
                    <p class="text-2xl font-black text-danger">{{ stats.occupied }}</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-blue-500">
                    <p class="text-xs text-gray-500 uppercase font-bold">Reservadas</p>
                    <p class="text-2xl font-black text-blue-600">{{ stats.reserved }}</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-gray-400">
                    <p class="text-xs text-gray-500 uppercase font-bold">Inactivas</p>
                    <p class="text-2xl font-black text-gray-500">{{ stats.inactive }}</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-primary">
                    <p class="text-xs text-gray-500 uppercase font-bold">Total</p>
                    <p class="text-2xl font-black text-primary">{{ stats.total }}</p>
                </div>
            </div>

        <!-- Mapa visual de mesas -->
        <div class="bg-white rounded-2xl shadow-xl p-6">
            <div class="flex items-center gap-4 mb-6 text-xs font-semibold">
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-green-500 inline-block"></span> Libre</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-red-500 inline-block"></span> Ocupada</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-blue-500 inline-block"></span> Reservada</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-gray-300 inline-block"></span> Inactiva</span>
            </div>
            <div v-if="mesas.length === 0" class="text-center py-12 text-gray-400">
                <p class="text-5xl mb-3">🪑</p>
                <p class="font-bold">No hay mesas registradas</p>
                <p class="text-sm">Cree mesas para comenzar</p>
            </div>
            <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                <div v-for="mesa in mesas" :key="mesa.id"
                    @click="openEdit(mesa)"
                    :class="[
                        'relative rounded-2xl border-2 p-4 text-center cursor-pointer transition-all duration-200 hover:scale-105 hover:shadow-lg',
                        statusConfig[getStatus(mesa)].border,
                        getStatus(mesa) === 'available' ? 'bg-green-50' :
                        getStatus(mesa) === 'occupied' ? 'bg-red-50' :
                        getStatus(mesa) === 'reserved' ? 'bg-blue-50' : 'bg-gray-50 opacity-70'
                    ]"
                >
                    <div :class="[
                        'absolute top-2 right-2 w-3 h-3 rounded-full border-2 border-white shadow-sm',
                        statusConfig[getStatus(mesa)].bg
                    ]"></div>

                    <div class="text-4xl font-black mb-1" :class="{
                        'text-green-700': getStatus(mesa) === 'available',
                        'text-red-700': getStatus(mesa) === 'occupied',
                        'text-blue-700': getStatus(mesa) === 'reserved',
                        'text-gray-400': getStatus(mesa) === 'inactive'
                    }">{{ mesa.number }}</div>

                    <div class="font-bold text-sm text-text truncate">{{ mesa.name }}</div>
                    <div v-if="mesa.capacity" class="text-[10px] text-gray-400 mt-0.5">Cap. {{ mesa.capacity }} pers.</div>

                    <div class="mt-2">
                        <span class="inline-block px-2 py-0.5 text-[10px] font-bold rounded-full"
                            :class="statusConfig[getStatus(mesa)].badge">
                            {{ statusConfig[getStatus(mesa)].label }}
                        </span>
                    </div>

                    <div class="flex gap-1 mt-3 justify-center">
                        <SecondaryButton @click.stop="openEdit(mesa)" class="text-[10px] px-2 py-1">Editar</SecondaryButton>
                        <DangerButton @click.stop="confirmDelete(mesa.id)" class="text-[10px] px-2 py-1">Eliminar</DangerButton>
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
                    <div>
                        <label class="block text-sm font-medium text-text mb-1">Capacidad (personas)</label>
                        <input v-model="form.capacity" type="number" placeholder="Ej: 4"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text mb-1">Reservar hasta</label>
                        <input v-model="form.reserved_at" type="datetime-local"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary" />
                        <p v-if="errors.reserved_at" class="text-danger text-sm mt-1">{{ errors.reserved_at }}</p>
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

        <!-- Modal Eliminar -->
        <Modal :show="showDeleteConfirm" @close="showDeleteConfirm = false">
            <div class="p-6 bg-white rounded-2xl w-full max-w-sm mx-auto animate-scale-in text-center">
                <div class="text-5xl mb-4">🗑️</div>
                <h3 class="text-xl font-bold text-text mb-2">Eliminar Mesa</h3>
                <p class="text-gray-500 mb-6">¿Estás seguro de eliminar esta mesa? Esta acción no se puede deshacer.</p>
                <div class="flex gap-3">
                    <SecondaryButton @click="showDeleteConfirm = false" class="flex-1">Cancelar</SecondaryButton>
                    <DangerButton @click="destroy" class="flex-1">Eliminar</DangerButton>
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
                    <div>
                        <label class="block text-sm font-medium text-text mb-1">Capacidad (personas)</label>
                        <input v-model="form.capacity" type="number"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text mb-1">Reservar hasta</label>
                        <input v-model="form.reserved_at" type="datetime-local"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary" />
                        <p v-if="errors.reserved_at" class="text-danger text-sm mt-1">{{ errors.reserved_at }}</p>
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
