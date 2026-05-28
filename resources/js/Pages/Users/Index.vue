<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    users: Array
});

const showModal = ref(false);

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'waiter',
});

const submit = () => {
    form.post(route('users.store'), {
        onSuccess: () => {
            form.reset();
            showModal.value = false;
        }
    });
};

const deleteUser = (id) => {
    if (confirm('¿Estás seguro de eliminar este usuario?')) {
        useForm({}).delete(route('users.destroy', id));
    }
};

const getRoleLabel = (role) => {
    switch (role) {
        case 'admin': return 'Administrador';
        case 'cashier': return 'Cajero';
        case 'waiter': return 'Mozo / Mesero';
        default: return role;
    }
};
</script>

<template>
    <Head title="Gestión de Usuarios" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-black text-gray-800 tracking-tight uppercase">
                    👥 Personal y Seguridad
                </h2>
                <button
                    @click="showModal = true"
                    class="px-6 py-3 bg-gray-800 text-white rounded-xl font-black uppercase text-xs tracking-widest hover:bg-gray-900 transition-all shadow-lg shadow-gray-100"
                >
                    + Nuevo Usuario
                </button>
            </div>
        </template>

        <div>
            <div class="bg-white rounded-3xl shadow-xl overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b">
                            <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase">Nombre</th>
                            <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase">Email</th>
                            <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase">Rol</th>
                            <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="user in users" :key="user.id" class="border-b hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <p class="font-black text-gray-800 uppercase text-sm">{{ user.name }}</p>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-500">{{ user.email }}</td>
                            <td class="px-6 py-4">
                                <span :class="[
                                    'px-3 py-1 rounded-full text-[10px] font-black border',
                                    user.role === 'admin' ? 'bg-purple-100 text-purple-800 border-purple-200' :
                                    user.role === 'cashier' ? 'bg-blue-100 text-blue-800 border-blue-200' : 'bg-orange-100 text-orange-800 border-orange-200'
                                ]">
                                    {{ getRoleLabel(user.role) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button
                                    @click="deleteUser(user.id)"
                                    class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                    title="Eliminar"
                                >
                                    🗑️
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal para nuevo usuario -->
        <div v-if="showModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click.self="showModal = false">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
                <div class="p-6 border-b bg-gray-50">
                    <h3 class="text-xl font-black text-gray-800 uppercase">Nuevo Personal</h3>
                </div>
                <form @submit.prevent="submit" class="p-6 space-y-4">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Nombre Completo</label>
                        <input v-model="form.name" type="text" class="w-full px-4 py-3 bg-gray-50 border-gray-100 rounded-xl font-bold" required />
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Email</label>
                        <input v-model="form.email" type="email" class="w-full px-4 py-3 bg-gray-50 border-gray-100 rounded-xl font-bold" required />
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Rol</label>
                        <select v-model="form.role" class="w-full px-4 py-3 bg-gray-50 border-gray-100 rounded-xl font-bold text-gray-700">
                            <option value="waiter">Mozo / Mesero</option>
                            <option value="cashier">Cajero</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Contraseña</label>
                            <input v-model="form.password" type="password" class="w-full px-4 py-3 bg-gray-50 border-gray-100 rounded-xl font-bold" required />
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Confirmar</label>
                            <input v-model="form.password_confirmation" type="password" class="w-full px-4 py-3 bg-gray-50 border-gray-100 rounded-xl font-bold" required />
                        </div>
                    </div>
                    <div class="flex gap-4 pt-4">
                        <button
                            type="button"
                            @click="showModal = false"
                            class="flex-1 py-3 bg-gray-100 text-gray-600 rounded-xl font-black uppercase text-xs tracking-widest"
                        >Cancelar</button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex-1 py-3 bg-orange-600 text-white rounded-xl font-black uppercase text-xs tracking-widest shadow-lg shadow-orange-100"
                        >Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
