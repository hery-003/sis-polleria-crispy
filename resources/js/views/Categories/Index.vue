<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/components/PrimaryButton.vue'
import DangerButton from '@/components/DangerButton.vue'
import SecondaryButton from '@/components/SecondaryButton.vue'
import Modal from '@/components/Modal.vue'
import Card from '@/components/Card.vue'
import EmptyState from '@/components/EmptyState.vue'

const props = defineProps({
    categories: Array,
    trashed: Array,
})

const showCreate = ref(false)
const showEdit = ref(false)
const showDeleteConfirm = ref(false)
const showRestoreConfirm = ref(false)
const deletingId = ref(null)
const restoringId = ref(null)
const form = ref({ id: null, name: '', slug: '', sort_order: 0, is_active: true })
const errors = ref({})

const stats = computed(() => ({
    total: props.categories.length,
    active: props.categories.filter(c => c.is_active).length,
    inactive: props.categories.filter(c => !c.is_active).length,
}))

const openCreate = () => {
    form.value = { id: null, name: '', slug: '', sort_order: 0, is_active: true }
    errors.value = {}
    showCreate.value = true
}

const openEdit = (cat) => {
    form.value = { ...cat }
    errors.value = {}
    showEdit.value = true
}

const generateSlug = (name) => {
    return name.toLowerCase()
        .replace(/[^a-z0-9áéíóúñ\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .trim()
}

const onNameInput = () => {
    if (!form.value.id) {
        form.value.slug = generateSlug(form.value.name)
    }
}

const submitCreate = () => {
    router.post(route('categories.store'), form.value, {
        onError: (e) => { errors.value = e; return false },
        onSuccess: () => { showCreate.value = false }
    })
}

const submitEdit = () => {
    router.put(route('categories.update', form.value.id), form.value, {
        onError: (e) => { errors.value = e; return false },
        onSuccess: () => { showEdit.value = false }
    })
}

const confirmDelete = (id) => {
    deletingId.value = id
    showDeleteConfirm.value = true
}

const confirmRestore = (id) => {
    restoringId.value = id
    showRestoreConfirm.value = true
}

const destroy = () => {
    router.delete(route('categories.destroy', deletingId.value), {
        onSuccess: () => { showDeleteConfirm.value = false; deletingId.value = null }
    })
}

const restore = () => {
    router.patch(route('categories.restore', restoringId.value), {
        onSuccess: () => { showRestoreConfirm.value = false; restoringId.value = null }
    })
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="pb-6">
            <div class="-mx-3 sm:-mx-6 -mt-3 sm:-mt-6 bg-gradient-to-r from-primary via-orange-500 to-orange-600 text-white p-6 sm:p-8 shadow-2xl relative overflow-hidden mb-8">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-32 -mt-32 blur-3xl"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-black/10 rounded-full -ml-24 -mb-24 blur-2xl"></div>
                <div class="relative z-10 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h1 class="text-3xl font-black italic uppercase tracking-tight">Categorías</h1>
                        <p class="text-orange-100 mt-1 font-bold text-sm">Gestiona las categorías del menú</p>
                    </div>
                    <PrimaryButton @click="openCreate" class="!bg-white !text-primary hover:!bg-orange-50 !shadow-xl">
                        + Nueva Categoría
                    </PrimaryButton>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4 mb-8">
                <Card hover class="border-l-8 border-primary">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total</p>
                    <p class="text-3xl font-black text-text mt-1 font-mono">{{ stats.total }}</p>
                </Card>
                <Card hover class="border-l-8 border-green-500">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Activas</p>
                    <p class="text-3xl font-black text-green-600 mt-1 font-mono">{{ stats.active }}</p>
                </Card>
                <Card hover class="border-l-8 border-gray-400">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Inactivas</p>
                    <p class="text-3xl font-black text-gray-500 mt-1 font-mono">{{ stats.inactive }}</p>
                </Card>
            </div>

            <Card no-padding>
                <div v-if="categories.length === 0" class="py-16">
                    <EmptyState icon="📂" title="Sin categorías" message="Crea tu primera categoría para empezar a organizar productos.">
                        <PrimaryButton @click="openCreate" class="mt-4">Crear Categoría</PrimaryButton>
                    </EmptyState>
                </div>
                <div v-else class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div v-for="cat in categories" :key="cat.id" :class="[
                        'relative rounded-2xl border-2 p-5 transition-all duration-200 group',
                        cat.is_active
                            ? 'border-primary/20 bg-white hover:border-primary hover:shadow-xl hover:-translate-y-1'
                            : 'border-gray-200 bg-gray-50 opacity-60'
                    ]">
                        <div class="flex items-start justify-between mb-3">
                            <div :class="[
                                'w-12 h-12 rounded-xl flex items-center justify-center text-xl shadow-sm',
                                cat.is_active ? 'bg-primary/10 text-primary' : 'bg-gray-200 text-gray-400'
                            ]">
                                📂
                            </div>
                            <span v-if="!cat.is_active"
                                class="px-2.5 py-1 text-[9px] font-black bg-gray-200 text-gray-500 rounded-full uppercase tracking-wider">Inactiva</span>
                            <span v-else
                                class="px-2.5 py-1 text-[9px] font-black bg-green-100 text-green-700 rounded-full uppercase tracking-wider">Activa</span>
                        </div>
                        <h3 class="text-lg font-black text-text uppercase leading-tight">{{ cat.name }}</h3>
                        <p class="text-xs text-gray-400 font-mono mt-0.5">/{{ cat.slug }}</p>
                        <div class="flex items-center gap-3 mt-3 text-xs font-bold text-gray-500">
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                {{ cat.products_count }} producto(s)
                            </span>
                            <span v-if="cat.sort_order" class="flex items-center gap-1 ml-auto">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                Orden {{ cat.sort_order }}
                            </span>
                        </div>
                        <div class="mt-4 pt-4 border-t border-gray-100 flex gap-2">
                            <SecondaryButton @click="openEdit(cat)" class="flex-1 !py-2 !text-[10px]">
                                Editar
                            </SecondaryButton>
                            <DangerButton @click="confirmDelete(cat.id)" class="!py-2 !px-3">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </DangerButton>
                        </div>
                    </div>
                </div>
            </Card>

            <div v-if="trashed?.length" class="mt-6">
                <details class="group">
                    <summary class="flex items-center gap-2 cursor-pointer text-sm font-bold text-gray-500 hover:text-gray-700 mb-3 p-3 bg-white rounded-xl border border-gray-100 shadow-sm">
                        <svg class="w-4 h-4 group-open:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        <span>🗑️ Categorías eliminadas ({{ trashed.length }})</span>
                    </summary>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div v-for="cat in trashed" :key="cat.id"
                            class="border-2 border-dashed border-gray-200 rounded-2xl p-5 bg-gray-50/50">
                            <div class="flex items-start justify-between mb-3">
                                <div class="w-10 h-10 rounded-xl bg-gray-200 flex items-center justify-center text-lg">🗑️</div>
                            </div>
                            <h4 class="font-bold text-gray-500 line-through">{{ cat.name }}</h4>
                            <p class="text-xs text-gray-400 mt-1">{{ cat.products_count }} producto(s)</p>
                            <button @click="confirmRestore(cat.id)"
                                class="mt-3 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-xl text-xs font-bold transition-all active:scale-95 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Restaurar
                            </button>
                        </div>
                    </div>
                </details>
            </div>
        </div>

        <Modal :show="showCreate" @close="showCreate = false">
            <form @submit.prevent="submitCreate" class="p-6 space-y-5">
                <div class="flex items-center gap-3 pb-4 border-b border-gray-100">
                    <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-lg">📂</div>
                    <div>
                        <h3 class="text-lg font-black text-text uppercase">Nueva Categoría</h3>
                        <p class="text-xs text-gray-400 font-bold">Agrega una nueva categoría al menú</p>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1.5">Nombre</label>
                    <input v-model="form.name" @input="onNameInput" type="text" placeholder="Ej: Pollo Broster"
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl font-bold focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all" />
                    <p v-if="errors.name" class="text-danger text-xs mt-1 font-bold">{{ errors.name }}</p>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1.5">Slug</label>
                    <input v-model="form.slug" type="text" placeholder="Ej: pollo-broster"
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl font-bold focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all" />
                    <p v-if="errors.slug" class="text-danger text-xs mt-1 font-bold">{{ errors.slug }}</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1.5">Orden</label>
                        <input v-model.number="form.sort_order" type="number" min="0" placeholder="0"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl font-bold focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all" />
                    </div>
                    <div class="flex items-end pb-3">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" v-model="form.is_active" class="w-5 h-5 rounded border-gray-300 text-primary focus:ring-primary" />
                            <span class="text-sm font-bold text-text">Activa</span>
                        </label>
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <SecondaryButton type="button" @click="showCreate = false" class="flex-1">Cancelar</SecondaryButton>
                    <PrimaryButton type="submit" class="flex-1">Crear Categoría</PrimaryButton>
                </div>
            </form>
        </Modal>

        <Modal :show="showEdit" @close="showEdit = false">
            <form @submit.prevent="submitEdit" class="p-6 space-y-5">
                <div class="flex items-center gap-3 pb-4 border-b border-gray-100">
                    <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-lg">✏️</div>
                    <div>
                        <h3 class="text-lg font-black text-text uppercase">Editar Categoría</h3>
                        <p class="text-xs text-gray-400 font-bold">Modifica los datos de la categoría</p>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1.5">Nombre</label>
                    <input v-model="form.name" type="text"
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl font-bold focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all" />
                    <p v-if="errors.name" class="text-danger text-xs mt-1 font-bold">{{ errors.name }}</p>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1.5">Slug</label>
                    <input v-model="form.slug" type="text"
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl font-bold focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all" />
                    <p v-if="errors.slug" class="text-danger text-xs mt-1 font-bold">{{ errors.slug }}</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1.5">Orden</label>
                        <input v-model.number="form.sort_order" type="number" min="0"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl font-bold focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all" />
                    </div>
                    <div class="flex items-end pb-3">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" v-model="form.is_active" class="w-5 h-5 rounded border-gray-300 text-primary focus:ring-primary" />
                            <span class="text-sm font-bold text-text">Activa</span>
                        </label>
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <SecondaryButton type="button" @click="showEdit = false" class="flex-1">Cancelar</SecondaryButton>
                    <PrimaryButton type="submit" class="flex-1">Guardar Cambios</PrimaryButton>
                </div>
            </form>
        </Modal>

        <Modal :show="showDeleteConfirm" @close="showDeleteConfirm = false">
            <div class="p-8 text-center">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-5">
                    <svg class="w-8 h-8 text-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
                <h3 class="text-xl font-black text-text mb-2">Eliminar Categoría</h3>
                <p class="text-sm text-gray-500 mb-6">¿Estás seguro? Esta acción no se puede deshacer.</p>
                <div class="flex gap-3">
                    <SecondaryButton @click="showDeleteConfirm = false" class="flex-1">Cancelar</SecondaryButton>
                    <DangerButton @click="destroy" class="flex-1">Eliminar</DangerButton>
                </div>
            </div>
        </Modal>

        <Modal :show="showRestoreConfirm" @close="showRestoreConfirm = false">
            <div class="p-8 text-center">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-5">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </div>
                <h3 class="text-xl font-black text-text mb-2">Restaurar Categoría</h3>
                <p class="text-sm text-gray-500 mb-6">¿Deseas restaurar esta categoría?</p>
                <div class="flex gap-3">
                    <SecondaryButton @click="showRestoreConfirm = false" class="flex-1">Cancelar</SecondaryButton>
                    <button @click="restore"
                        class="flex-1 py-3 px-4 bg-green-600 hover:bg-green-700 text-white rounded-xl font-bold transition-all active:scale-95">
                        Restaurar
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
