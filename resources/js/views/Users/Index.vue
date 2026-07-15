<script setup>
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Modal from '@/components/Modal.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import SecondaryButton from '@/components/SecondaryButton.vue';
import Card from '@/components/Card.vue';

const props = defineProps({
    users: Object,
    trashedUsers: Array,
});

const showModal = ref(false);
const editModal = ref(false);
const editingUser = ref(null);
const showTrashed = ref(false);

const showDeleteConfirm = ref(false);
const showToggleConfirm = ref(false);
const deletingUserId = ref(null);
const toggleUser = ref(null);
const toggleMessage = ref('');

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'waiter',
});

const editForm = useForm({
    name: '',
    email: '',
    role: 'waiter',
    password: '',
    password_confirmation: '',
});

const activeUsers = computed(() => props.users.data.filter(u => u.is_active).length);
const inactiveUsers = computed(() => props.users.data.filter(u => !u.is_active).length);

const submit = () => {
    form.post(route('users.store'), {
        onSuccess: () => {
            form.reset();
            showModal.value = false;
        }
    });
};

const openEditModal = (user) => {
    editingUser.value = user;
    editForm.name = user.name;
    editForm.email = user.email;
    editForm.role = user.role;
    editForm.password = '';
    editForm.password_confirmation = '';
    editModal.value = true;
};

const updateUser = () => {
    editForm.put(route('users.update', editingUser.value.id), {
        onSuccess: () => {
            editForm.reset();
            editModal.value = false;
            editingUser.value = null;
        }
    });
};

const confirmDelete = (id) => {
    deletingUserId.value = id;
    showDeleteConfirm.value = true;
};

const deleteUser = () => {
    if (deletingUserId.value) {
        useForm({}).delete(route('users.destroy', deletingUserId.value));
        showDeleteConfirm.value = false;
        deletingUserId.value = null;
    }
};

const confirmToggle = (user) => {
    toggleUser.value = user;
    toggleMessage.value = user.is_active
        ? 'Este usuario no podrá iniciar sesión en el sistema.'
        : 'Este usuario podrá acceder al sistema nuevamente.';
    showToggleConfirm.value = true;
};

const executeToggle = () => {
    if (toggleUser.value) {
        router.patch(route('users.toggle-active', toggleUser.value.id));
        showToggleConfirm.value = false;
        toggleUser.value = null;
    }
};

const restoreUser = (id) => {
    router.patch(route('users.restore', id));
};

const getRoleLabel = (role) => {
    switch (role) {
        case 'admin': return 'Administrador';
        case 'cashier': return 'Cajero';
        case 'waiter': return 'Mozo / Mesero';
        default: return role;
    }
};

const roleClass = (role) => {
    const map = {
        admin: 'bg-purple-100 text-purple-800 border-purple-200',
        cashier: 'bg-blue-100 text-blue-800 border-blue-200',
        waiter: 'bg-orange-100 text-orange-800 border-orange-200',
    };
    return map[role] || 'bg-gray-100 text-gray-800 border-gray-200';
};
</script>

<template>
    <Head title="Gestión de Usuarios" />
    <AuthenticatedLayout>
        <div class="pb-6">
            <div class="-mx-3 sm:-mx-6 -mt-3 sm:-mt-6 bg-gradient-to-r from-primary via-orange-500 to-orange-600 text-white p-6 sm:p-8 shadow-2xl relative overflow-hidden mb-8">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-32 -mt-32 blur-3xl"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-black/10 rounded-full -ml-24 -mb-24 blur-2xl"></div>
                <div class="relative z-10 flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-black italic uppercase tracking-tight">Personal y Seguridad</h1>
                        <p class="text-orange-100 mt-1 font-bold text-sm">Gestiona los usuarios del sistema</p>
                    </div>
                    <PrimaryButton @click="showModal = true" class="!bg-white !text-primary !shadow-lg">
                        + Nuevo Usuario
                    </PrimaryButton>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                <Card hover class="border-l-8 border-primary">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Usuarios</p>
                    <p class="text-3xl font-black text-text mt-1 font-mono">{{ props.users.data.length }}</p>
                </Card>
                <Card hover class="border-l-8 border-green-500">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Activos</p>
                    <p class="text-3xl font-black text-green-600 mt-1 font-mono">{{ activeUsers }}</p>
                </Card>
                <Card hover class="border-l-8 border-red-500">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Inactivos</p>
                    <p class="text-3xl font-black text-red-600 mt-1 font-mono">{{ inactiveUsers }}</p>
                </Card>
            </div>

            <Card noPadding class="mb-8">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Nombre</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Email</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Rol</th>
                                <th class="px-6 py-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Estado</th>
                                <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50 transition-colors" :class="{ 'opacity-60': !user.is_active }">
                                <td class="px-6 py-4">
                                    <p class="font-black text-text uppercase text-sm" :class="{ 'line-through': !user.is_active }">{{ user.name }}</p>
                                </td>
                                <td class="px-6 py-4 text-sm font-bold text-gray-500">{{ user.email }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-lg text-[10px] font-black border" :class="roleClass(user.role)">
                                        {{ getRoleLabel(user.role) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-[10px] font-black border"
                                        :class="user.is_active ? 'bg-green-100 text-green-800 border-green-200' : 'bg-red-100 text-red-800 border-red-200'">
                                        <span class="w-1.5 h-1.5 rounded-full" :class="user.is_active ? 'bg-green-500' : 'bg-red-500'"></span>
                                        {{ user.is_active ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex gap-1 justify-end">
                                        <button @click="confirmToggle(user)"
                                            class="p-2 text-gray-600 hover:bg-gray-100 rounded-xl transition-all active:scale-90"
                                            :title="user.is_active ? 'Desactivar' : 'Activar'">
                                            <svg v-if="user.is_active" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                            </svg>
                                            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                        <button @click="openEditModal(user)"
                                            class="p-2 text-blue-600 hover:bg-blue-50 rounded-xl transition-all active:scale-90"
                                            title="Editar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button @click="confirmDelete(user.id)"
                                            class="p-2 text-red-600 hover:bg-red-50 rounded-xl transition-all active:scale-90"
                                            title="Eliminar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </Card>

            <Card v-if="trashedUsers.length > 0" noPadding class="overflow-hidden">
                <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                    <button @click="showTrashed = !showTrashed" class="flex items-center gap-2 text-sm font-black text-gray-500 hover:text-gray-700 transition-colors">
                        <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-90': showTrashed }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        Usuarios eliminados ({{ trashedUsers.length }})
                    </button>
                </div>
                <div v-if="showTrashed" class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Nombre</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Email</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Rol</th>
                                <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="user in trashedUsers" :key="user.id" class="hover:bg-gray-50 transition-colors opacity-60">
                                <td class="px-6 py-4">
                                    <p class="font-black text-gray-500 uppercase text-sm line-through">{{ user.name }}</p>
                                </td>
                                <td class="px-6 py-4 text-sm font-bold text-gray-400">{{ user.email }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-lg text-[10px] font-black bg-gray-200 text-gray-500">
                                        {{ getRoleLabel(user.role) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button @click="restoreUser(user.id)"
                                        class="p-2 text-green-600 hover:bg-green-50 rounded-xl transition-all active:scale-90"
                                        title="Restaurar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </Card>
        </div>

        <Modal :show="editModal" @close="editModal = false" max-width="md">
            <form @submit.prevent="updateUser" class="p-6 space-y-4">
                <div class="flex items-center gap-4 mb-2">
                    <div class="w-12 h-12 rounded-2xl bg-blue-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-text uppercase">Editar Personal</h3>
                        <p class="text-xs text-gray-500 font-bold">Modifica los datos del usuario</p>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Nombre Completo</label>
                    <input v-model="editForm.name" type="text" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none font-bold transition-all" required />
                    <p v-if="editForm.errors.name" class="mt-1 text-xs text-red-600 font-bold">{{ editForm.errors.name }}</p>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Email</label>
                    <input v-model="editForm.email" type="email" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none font-bold transition-all" required />
                    <p v-if="editForm.errors.email" class="mt-1 text-xs text-red-600 font-bold">{{ editForm.errors.email }}</p>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Rol</label>
                    <select v-model="editForm.role" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none font-bold transition-all text-gray-700">
                        <option value="waiter">Mozo / Mesero</option>
                        <option value="cashier">Cajero</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Nueva Contraseña</label>
                        <input v-model="editForm.password" type="password" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none font-bold transition-all" placeholder="Opcional" />
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Confirmar</label>
                        <input v-model="editForm.password_confirmation" type="password" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none font-bold transition-all" placeholder="Opcional" />
                    </div>
                </div>
                <div class="flex gap-4 pt-4">
                    <SecondaryButton type="button" @click="editModal = false" class="flex-1">Cancelar</SecondaryButton>
                    <PrimaryButton type="submit" :disabled="editForm.processing" class="flex-1 shadow-lg shadow-orange-200">Actualizar</PrimaryButton>
                </div>
            </form>
        </Modal>

        <Modal :show="showModal" @close="showModal = false" max-width="md">
            <form @submit.prevent="submit" class="p-6 space-y-4">
                <div class="flex items-center gap-4 mb-2">
                    <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-text uppercase">Nuevo Personal</h3>
                        <p class="text-xs text-gray-500 font-bold">Registra un nuevo usuario en el sistema</p>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Nombre Completo</label>
                    <input v-model="form.name" type="text" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none font-bold transition-all" required />
                    <p v-if="form.errors.name" class="mt-1 text-xs text-red-600 font-bold">{{ form.errors.name }}</p>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Email</label>
                    <input v-model="form.email" type="email" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none font-bold transition-all" required />
                    <p v-if="form.errors.email" class="mt-1 text-xs text-red-600 font-bold">{{ form.errors.email }}</p>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Rol</label>
                    <select v-model="form.role" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none font-bold transition-all text-gray-700">
                        <option value="waiter">Mozo / Mesero</option>
                        <option value="cashier">Cajero</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Contraseña</label>
                        <input v-model="form.password" type="password" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none font-bold transition-all" required />
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Confirmar</label>
                        <input v-model="form.password_confirmation" type="password" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none font-bold transition-all" required />
                    </div>
                </div>
                <div class="flex gap-4 pt-4">
                    <SecondaryButton type="button" @click="showModal = false" class="flex-1">Cancelar</SecondaryButton>
                    <PrimaryButton type="submit" :disabled="form.processing" class="flex-1 shadow-lg shadow-orange-200">Guardar</PrimaryButton>
                </div>
            </form>
        </Modal>

        <Modal :show="showDeleteConfirm" @close="showDeleteConfirm = false" max-width="sm">
            <div class="p-6 text-center">
                <div class="mx-auto w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
                <h3 class="text-xl font-black text-gray-800 mb-2">Eliminar Usuario</h3>
                <p class="text-sm text-gray-500 mb-6">¿Estás seguro de eliminar este usuario? Esta acción no se puede deshacer.</p>
                <div class="flex gap-3">
                    <SecondaryButton @click="showDeleteConfirm = false" class="flex-1">Cancelar</SecondaryButton>
                    <button @click="deleteUser" class="flex-1 py-3 px-4 bg-danger text-white rounded-xl font-bold hover:bg-red-700 transition-all active:scale-95">Eliminar</button>
                </div>
            </div>
        </Modal>

        <Modal :show="showToggleConfirm" @close="showToggleConfirm = false" max-width="sm">
            <div class="p-6 text-center">
                <div class="mx-auto w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
                <h3 class="text-xl font-black text-gray-800 mb-2">{{ toggleUser?.is_active ? 'Desactivar' : 'Activar' }} Usuario</h3>
                <p class="text-sm text-gray-500 mb-6">{{ toggleMessage }}</p>
                <div class="flex gap-3">
                    <SecondaryButton @click="showToggleConfirm = false" class="flex-1">Cancelar</SecondaryButton>
                    <button @click="executeToggle" class="flex-1 py-3 px-4 bg-gray-800 text-white rounded-xl font-bold hover:bg-gray-900 transition-all active:scale-95">
                        {{ toggleUser?.is_active ? 'Desactivar' : 'Activar' }}
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
