<script setup>
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import SecondaryButton from '@/components/SecondaryButton.vue';
import Modal from '@/components/Modal.vue';
import Card from '@/components/Card.vue';
import EmptyState from '@/components/EmptyState.vue';

const props = defineProps({
    clients: Object
});

const showModal = ref(false);
const showDeleteConfirm = ref(false);
const deletingClient = ref(null);
const editingClient = ref(null);
const form = ref({
    name: '',
    phone: '',
    email: '',
    document_number: '',
    address: ''
});

const totalClients = computed(() => props.clients.data?.length || 0);
const clientsWithPhone = computed(() => props.clients.data?.filter(c => c.phone).length || 0);
const clientsWithEmail = computed(() => props.clients.data?.filter(c => c.email).length || 0);

const openModal = (client = null) => {
    if (client) {
        editingClient.value = client;
        form.value = { ...client };
    } else {
        editingClient.value = null;
        form.value = { name: '', phone: '', email: '', document_number: '', address: '' };
    }
    showModal.value = true;
};

const saveClient = () => {
    if (editingClient.value) {
        router.put(route('clients.update', editingClient.value.id), form.value);
    } else {
        router.post(route('clients.store'), form.value);
    }
    showModal.value = false;
};

const confirmDelete = (client) => {
    deletingClient.value = client;
    showDeleteConfirm.value = true;
};

const deleteClient = () => {
    if (deletingClient.value) {
        router.delete(route('clients.destroy', deletingClient.value.id));
        showDeleteConfirm.value = false;
        deletingClient.value = null;
    }
};
</script>

<template>
    <Head title="Clientes" />
    <AuthenticatedLayout>
        <div class="pb-6">
            <div class="-mx-3 sm:-mx-6 -mt-3 sm:-mt-6 bg-gradient-to-br from-primary via-orange-500 to-orange-700 text-white p-8 sm:p-12 shadow-[0_20px_50px_rgba(243,112,33,0.2)] relative overflow-hidden mb-10">
                <div class="absolute top-0 right-0 w-96 h-96 bg-white/10 rounded-full -mr-48 -mt-48 blur-3xl animate-pulse"></div>
                <div class="absolute bottom-0 left-0 w-64 h-64 bg-black/10 rounded-full -ml-32 -mb-32 blur-2xl"></div>
                <div class="relative z-10 flex flex-col md:flex-row justify-between items-center gap-6">
                    <div>
                        <h1 class="text-4xl font-black italic uppercase tracking-tighter font-poppins">Gestión de Clientes</h1>
                        <p class="text-orange-100 mt-2 font-bold text-lg opacity-80">Administra y fideliza a tus clientes</p>
                    </div>
                    <PrimaryButton @click="openModal()" class="!bg-white !text-primary !shadow-2xl !px-8">
                        <span class="mr-2">👤+</span> Nuevo Cliente
                    </PrimaryButton>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-10">
                <Card hover class="border-l-8 border-primary">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Total Registrados</p>
                    <p class="text-4xl font-black text-text mt-2 font-mono">{{ totalClients }}</p>
                </Card>
                <Card hover class="border-l-8 border-green-500">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Con Teléfono</p>
                    <p class="text-4xl font-black text-green-600 mt-2 font-mono">{{ clientsWithPhone }}</p>
                </Card>
                <Card hover class="border-l-8 border-blue-500">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Con Email</p>
                    <p class="text-4xl font-black text-blue-600 mt-2 font-mono">{{ clientsWithEmail }}</p>
                </Card>
            </div>

            <Card noPadding>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50/50 border-b border-gray-100">
                            <tr>
                                <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Nombre del Cliente</th>
                                <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Contacto</th>
                                <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Documento</th>
                                <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Fidelización</th>
                                <th class="px-8 py-5 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Gestión</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="client in clients.data" :key="client.id" class="hover:bg-orange-50/30 transition-colors group">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-black text-xs">
                                            {{ client.name.substring(0, 2).toUpperCase() }}
                                        </div>
                                        <div class="font-black text-text uppercase text-sm tracking-tight">{{ client.name }}</div>
                                    </div>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="text-sm font-bold text-gray-600">{{ client.phone || 'S/N' }}</div>
                                    <div class="text-[10px] text-gray-400">{{ client.email || 'Sin email' }}</div>
                                </td>
                                <td class="px-8 py-5 text-sm font-bold text-gray-400 font-mono">{{ client.document_number || '-' }}</td>
                                <td class="px-8 py-5">
                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-lg text-[10px] font-black border border-yellow-200">
                                        ⭐ {{ client.points }} Puntos
                                    </span>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <div class="flex gap-2 justify-end opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button @click="openModal(client)" class="p-2.5 bg-gray-50 hover:bg-primary hover:text-white rounded-xl text-gray-400 transition-all border border-gray-100">
                                            ✏️
                                        </button>
                                        <button @click="confirmDelete(client)" class="p-2.5 bg-gray-50 hover:bg-danger hover:text-white rounded-xl text-gray-400 transition-all border border-gray-100">
                                            🗑️
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="!clients.data?.length" class="py-12">
                    <EmptyState icon="users" title="Sin clientes" message="No hay clientes registrados aún. Crea tu primer cliente para empezar." />
                </div>
            </Card>
        </div>

        <Modal :show="showModal" @close="showModal = false" max-width="md">
            <div class="p-6">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-text uppercase">{{ editingClient ? 'Editar' : 'Nuevo' }} Cliente</h3>
                        <p class="text-xs text-gray-500 font-bold">{{ editingClient ? 'Actualiza los datos del cliente' : 'Registra un nuevo cliente' }}</p>
                    </div>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Nombre</label>
                        <input v-model="form.name" type="text" class="mt-1 w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none font-bold transition-all">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Teléfono</label>
                        <input v-model="form.phone" type="text" class="mt-1 w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none font-bold transition-all">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Email</label>
                        <input v-model="form.email" type="email" class="mt-1 w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none font-bold transition-all">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">CI/NIT</label>
                        <input v-model="form.document_number" type="text" class="mt-1 w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none font-bold transition-all">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Dirección</label>
                        <textarea v-model="form.address" rows="2" class="mt-1 w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none font-bold transition-all resize-none"></textarea>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <SecondaryButton @click="showModal = false" class="flex-1">Cancelar</SecondaryButton>
                    <PrimaryButton @click="saveClient" class="flex-1">{{ editingClient ? 'Actualizar' : 'Guardar' }}</PrimaryButton>
                </div>
            </div>
        </Modal>

        <Modal :show="showDeleteConfirm" @close="showDeleteConfirm = false" max-width="sm">
            <div class="p-6 text-center">
                <div class="mx-auto w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
                <h3 class="text-xl font-black text-gray-800 mb-2">Eliminar Cliente</h3>
                <p class="text-sm text-gray-500 mb-6">¿Seguro de eliminar a <strong>{{ deletingClient?.name }}</strong>? Esta acción no se puede deshacer.</p>
                <div class="flex gap-3">
                    <SecondaryButton @click="showDeleteConfirm = false" class="flex-1">Cancelar</SecondaryButton>
                    <button @click="deleteClient" class="flex-1 py-3 px-4 bg-danger text-white rounded-xl font-bold hover:bg-red-700 transition-all active:scale-95">Eliminar</button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
