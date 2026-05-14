<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    clients: Object
});

const showModal = ref(false);
const editingClient = ref(null);
const form = ref({
    name: '',
    phone: '',
    email: '',
    document_number: '',
    address: ''
});

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

const deleteClient = (client) => {
    if (confirm(`¿Seguro de eliminar a ${client.name}?`)) {
        router.delete(route('clients.destroy', client.id));
    }
};
</script>

<template>
    <Head title="Clientes" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-black text-gray-800 tracking-tight uppercase">👥 Clientes</h2>
                <PrimaryButton @click="openModal()" class="text-sm">
                    + Nuevo Cliente
                </PrimaryButton>
            </div>
        </template>

        <div>
            <div class="bg-white rounded-3xl shadow-xl overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase">Nombre</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase">Teléfono</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase">Documento</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase">Puntos</th>
                            <th class="px-6 py-4 text-right text-xs font-black text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="client in clients.data" :key="client.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-bold text-gray-800">{{ client.name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ client.phone || '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ client.document_number || '-' }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-black">
                                    {{ client.points }} pts
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <SecondaryButton @click="openModal(client)" class="text-xs py-1 px-3">
                                    Editar
                                </SecondaryButton>
                                <button @click="deleteClient(client)" class="text-xs py-1 px-3 bg-danger text-white rounded-lg hover:bg-red-700 font-bold transition-all">
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Modal -->
            <div v-if="showModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click.self="showModal = false">
                <div class="bg-white rounded-3xl p-8 max-w-md w-full shadow-2xl">
                    <h3 class="text-2xl font-black mb-6">{{ editingClient ? 'Editar' : 'Nuevo' }} Cliente</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="text-xs font-black text-gray-500 uppercase">Nombre</label>
                            <input v-model="form.name" type="text" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label class="text-xs font-black text-gray-500 uppercase">Teléfono</label>
                            <input v-model="form.phone" type="text" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label class="text-xs font-black text-gray-500 uppercase">Email</label>
                            <input v-model="form.email" type="email" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label class="text-xs font-black text-gray-500 uppercase">CI/NIT</label>
                            <input v-model="form.document_number" type="text" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label class="text-xs font-black text-gray-500 uppercase">Dirección</label>
                            <textarea v-model="form.address" rows="2" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary"></textarea>
                        </div>
                    </div>
                    <div class="flex gap-3 mt-6">
                        <SecondaryButton @click="showModal = false" class="flex-1">Cancelar</SecondaryButton>
                        <PrimaryButton @click="saveClient" class="flex-1">{{ editingClient ? 'Actualizar' : 'Guardar' }}</PrimaryButton>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
