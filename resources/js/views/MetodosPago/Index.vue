<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/components/PrimaryButton.vue'
import DangerButton from '@/components/DangerButton.vue'
import SecondaryButton from '@/components/SecondaryButton.vue'
import Modal from '@/components/Modal.vue'
import Card from '@/components/Card.vue'
import EmptyState from '@/components/EmptyState.vue'
import { useToast } from '@/plugins/toast'

const toast = useToast()
const props = defineProps({
    metodos: Array
})

const showCreate = ref(false)
const showEdit = ref(false)
const showDeleteConfirm = ref(false)
const deletingId = ref(null)
const form = ref({ id: null, name: '', slug: '', is_active: true, qr_image: null })
const errors = ref({})
const qrPreview = ref(null)
const qrFile = ref(null)
const removeQR = ref(false)

const onQRFileChange = (e) => {
    const file = e.target.files[0]
    if (file) {
        qrFile.value = file
        removeQR.value = false
        const reader = new FileReader()
        reader.onload = (ev) => { qrPreview.value = ev.target.result }
        reader.readAsDataURL(file)
    }
}

const clearQRFile = () => {
    qrFile.value = null
    qrPreview.value = null
    removeQR.value = true
}

const openCreate = () => {
    form.value = { id: null, name: '', slug: '', is_active: true, qr_image: null }
    qrFile.value = null
    qrPreview.value = null
    removeQR.value = false
    errors.value = {}
    showCreate.value = true
}

const openEdit = (metodo) => {
    form.value = { ...metodo }
    qrFile.value = null
    qrPreview.value = null
    removeQR.value = false
    errors.value = {}
    showEdit.value = true
}

const submitCreate = () => {
    const payload = new FormData()
    payload.append('name', form.value.name)
    payload.append('slug', form.value.slug)
    payload.append('is_active', form.value.is_active ? '1' : '0')
    if (qrFile.value) payload.append('qr_image', qrFile.value)

    router.post(route('metodos-pago.store'), payload, {
        headers: { 'Content-Type': 'multipart/form-data' },
        onError: (e) => { errors.value = e; return false },
        onSuccess: () => { showCreate.value = false }
    })
}

const submitEdit = () => {
    const payload = new FormData()
    payload.append('_method', 'PUT')
    payload.append('name', form.value.name)
    payload.append('slug', form.value.slug)
    payload.append('is_active', form.value.is_active ? '1' : '0')
    if (qrFile.value) payload.append('qr_image', qrFile.value)
    if (removeQR.value) payload.append('remove_qr', '1')

    router.post(route('metodos-pago.update', form.value.id), payload, {
        headers: { 'Content-Type': 'multipart/form-data' },
        onError: (e) => { errors.value = e; return false },
        onSuccess: () => { showEdit.value = false }
    })
}

const confirmDelete = (id) => {
    deletingId.value = id
    showDeleteConfirm.value = true
}

const destroy = () => {
    router.delete(route('metodos-pago.destroy', deletingId.value), {
        onSuccess: () => { showDeleteConfirm.value = false; deletingId.value = null }
    })
}

const generateSlug = () => {
    if (form.value.name) {
        form.value.slug = form.value.name.toLowerCase()
            .replace(/\s+/g, '-')
            .replace(/[^\w\-]+/g, '')
    }
}

const activeCount = props.metodos.filter(m => m.is_active).length
const inactiveCount = props.metodos.filter(m => !m.is_active).length
</script>

<template>
    <Head title="Métodos de Pago" />
    <AuthenticatedLayout>
        <div class="pb-6">
            <div class="-mx-3 sm:-mx-6 -mt-3 sm:-mt-6 bg-gradient-to-r from-primary via-orange-500 to-orange-600 text-white p-6 sm:p-8 shadow-2xl relative overflow-hidden mb-8">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-32 -mt-32 blur-3xl"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-black/10 rounded-full -ml-24 -mb-24 blur-2xl"></div>
                <div class="relative z-10 flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-black italic uppercase tracking-tight">Métodos de Pago</h1>
                        <p class="text-orange-100 mt-1 font-bold text-sm">Configura las formas de pago disponibles</p>
                    </div>
                    <PrimaryButton @click="openCreate" class="!bg-white !text-primary !shadow-lg">
                        + Nuevo Método
                    </PrimaryButton>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                <Card hover class="border-l-8 border-primary">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Métodos</p>
                    <p class="text-3xl font-black text-text mt-1 font-mono">{{ props.metodos.length }}</p>
                </Card>
                <Card hover class="border-l-8 border-green-500">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Activos</p>
                    <p class="text-3xl font-black text-green-600 mt-1 font-mono">{{ activeCount }}</p>
                </Card>
                <Card hover class="border-l-8 border-red-500">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Inactivos</p>
                    <p class="text-3xl font-black text-red-600 mt-1 font-mono">{{ inactiveCount }}</p>
                </Card>
            </div>

            <Card noPadding>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Nombre</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Slug</th>
                                <th class="px-6 py-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Estado</th>
                                <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="metodo in metodos" :key="metodo.id" class="hover:bg-orange-50/30 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div v-if="metodo.qr_image" class="w-10 h-10 rounded-xl overflow-hidden border border-gray-200 flex-shrink-0">
                                            <img :src="'/storage/' + metodo.qr_image" class="w-full h-full object-cover" alt="QR" />
                                        </div>
                                        <div class="font-bold text-text">{{ metodo.name }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <code class="text-xs bg-gray-100 px-2 py-1 rounded-lg font-bold text-gray-600">{{ metodo.slug }}</code>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider"
                                        :class="metodo.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-500'">
                                        <span class="w-1.5 h-1.5 rounded-full" :class="metodo.is_active ? 'bg-green-500' : 'bg-gray-400'"></span>
                                        {{ metodo.is_active ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex gap-1.5 justify-end">
                                        <SecondaryButton @click="openEdit(metodo)" class="!text-[10px] !py-2 !px-3">Editar</SecondaryButton>
                                        <DangerButton @click="confirmDelete(metodo.id)" class="!text-[10px] !py-2 !px-3">Eliminar</DangerButton>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="metodos.length === 0">
                                <td colspan="4" class="px-6 py-12">
                                    <EmptyState icon="💳" title="Sin métodos" message="No hay métodos de pago registrados." />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </Card>
        </div>

        <Modal :show="showCreate" @close="showCreate = false">
            <div class="p-6 w-full max-w-md mx-auto">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-text uppercase">Nuevo Método de Pago</h3>
                        <p class="text-xs text-gray-500 font-bold">Añade un nuevo método de pago</p>
                    </div>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Nombre</label>
                        <input v-model="form.name" type="text" placeholder="Ej: Yape/Plin" @blur="generateSlug"
                            class="mt-1 w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none font-bold transition-all" />
                        <p v-if="errors.name" class="text-danger text-sm mt-1 font-bold">{{ errors.name }}</p>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Slug</label>
                        <input v-model="form.slug" type="text" placeholder="Ej: yape"
                            class="mt-1 w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none font-bold transition-all" />
                        <p v-if="errors.slug" class="text-danger text-sm mt-1 font-bold">{{ errors.slug }}</p>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Código QR (opcional)</label>
                        <div class="mt-1 flex items-center gap-3">
                            <label class="px-4 py-2.5 bg-gray-100 text-text rounded-xl text-xs font-black cursor-pointer hover:bg-gray-200 transition-all uppercase">
                                Seleccionar imagen
                                <input type="file" accept="image/*" @change="onQRFileChange" class="hidden" />
                            </label>
                            <button v-if="qrPreview || form.qr_image" @click="clearQRFile" class="text-xs text-danger font-bold hover:underline">Eliminar QR</button>
                        </div>
                        <div v-if="qrPreview" class="mt-2 w-24 h-24 rounded-xl overflow-hidden border-2 border-gray-200">
                            <img :src="qrPreview" class="w-full h-full object-cover" alt="QR preview" />
                        </div>
                        <div v-else-if="form.qr_image && !removeQR" class="mt-2 w-24 h-24 rounded-xl overflow-hidden border-2 border-gray-200">
                            <img :src="'/storage/' + form.qr_image" class="w-full h-full object-cover" alt="Current QR" />
                        </div>
                        <p v-if="errors.qr_image" class="text-danger text-sm mt-1 font-bold">{{ errors.qr_image }}</p>
                    </div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" v-model="form.is_active" class="w-5 h-5 rounded-lg border-2 border-gray-200 text-primary focus:ring-primary" />
                        <span class="text-sm font-bold text-text">Activo</span>
                    </label>
                    <div class="flex gap-3 pt-4">
                        <SecondaryButton @click="showCreate = false" class="flex-1">Cancelar</SecondaryButton>
                        <PrimaryButton @click="submitCreate" class="flex-1">Crear</PrimaryButton>
                    </div>
                </div>
            </div>
        </Modal>

        <Modal :show="showEdit" @close="showEdit = false">
            <div class="p-6 w-full max-w-md mx-auto">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 rounded-2xl bg-blue-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-text uppercase">Editar Método de Pago</h3>
                        <p class="text-xs text-gray-500 font-bold">Modifica los datos del método</p>
                    </div>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Nombre</label>
                        <input v-model="form.name" type="text" @blur="generateSlug"
                            class="mt-1 w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none font-bold transition-all" />
                        <p v-if="errors.name" class="text-danger text-sm mt-1 font-bold">{{ errors.name }}</p>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Slug</label>
                        <input v-model="form.slug" type="text"
                            class="mt-1 w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none font-bold transition-all" />
                        <p v-if="errors.slug" class="text-danger text-sm mt-1 font-bold">{{ errors.slug }}</p>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Código QR (opcional)</label>
                        <div class="mt-1 flex items-center gap-3">
                            <label class="px-4 py-2.5 bg-gray-100 text-text rounded-xl text-xs font-black cursor-pointer hover:bg-gray-200 transition-all uppercase">
                                Seleccionar imagen
                                <input type="file" accept="image/*" @change="onQRFileChange" class="hidden" />
                            </label>
                            <button v-if="qrPreview || form.qr_image" @click="clearQRFile" class="text-xs text-danger font-bold hover:underline">Eliminar QR</button>
                        </div>
                        <div v-if="qrPreview" class="mt-2 w-24 h-24 rounded-xl overflow-hidden border-2 border-gray-200">
                            <img :src="qrPreview" class="w-full h-full object-cover" alt="QR preview" />
                        </div>
                        <div v-else-if="form.qr_image && !removeQR" class="mt-2 w-24 h-24 rounded-xl overflow-hidden border-2 border-gray-200">
                            <img :src="'/storage/' + form.qr_image" class="w-full h-full object-cover" alt="Current QR" />
                        </div>
                        <p v-if="errors.qr_image" class="text-danger text-sm mt-1 font-bold">{{ errors.qr_image }}</p>
                    </div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" v-model="form.is_active" class="w-5 h-5 rounded-lg border-2 border-gray-200 text-primary focus:ring-primary" />
                        <span class="text-sm font-bold text-text">Activo</span>
                    </label>
                    <div class="flex gap-3 pt-4">
                        <SecondaryButton @click="showEdit = false" class="flex-1">Cancelar</SecondaryButton>
                        <PrimaryButton @click="submitEdit" class="flex-1">Guardar</PrimaryButton>
                    </div>
                </div>
            </div>
        </Modal>

        <Modal :show="showDeleteConfirm" @close="showDeleteConfirm = false">
            <div class="p-6 text-center">
                <div class="mx-auto w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
                <h3 class="text-xl font-black text-text mb-2">Eliminar Método de Pago</h3>
                <p class="text-sm text-gray-500 mb-6">¿Estás seguro de eliminar este método de pago? Esta acción no se puede deshacer.</p>
                <div class="flex gap-3">
                    <SecondaryButton @click="showDeleteConfirm = false" class="flex-1">Cancelar</SecondaryButton>
                    <DangerButton @click="destroy" class="flex-1">Eliminar</DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
