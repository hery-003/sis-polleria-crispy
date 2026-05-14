<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    logs: Object,
    actions: Array,
    users: Array,
    filters: Object,
});

const form = ref({
    action: props.filters.action || '',
    user_id: props.filters.user_id || '',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
});

const applyFilters = () => {
    router.get(route('audit-logs.index'), {
        ...form.value,
    }, { preserveState: true });
};

const clearFilters = () => {
    form.value = { action: '', user_id: '', date_from: '', date_to: '' };
    router.get(route('audit-logs.index'));
};

const getActionBadge = (action) => {
    const map = {
        'order_created': 'bg-green-100 text-green-800',
        'order_cancelled': 'bg-red-100 text-red-800',
        'order_paid': 'bg-blue-100 text-blue-800',
        'status_changed': 'bg-yellow-100 text-yellow-800',
        'user_created': 'bg-purple-100 text-purple-800',
        'product_created': 'bg-indigo-100 text-indigo-800',
        'product_updated': 'bg-indigo-100 text-indigo-800',
        'product_deleted': 'bg-gray-100 text-gray-800',
    };
    return map[action] || 'bg-gray-100 text-gray-800';
};

const formatAction = (action) => {
    return action.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString('es-PE', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatValues = (values) => {
    if (!values || typeof values !== 'object') return '-';
    return Object.entries(values)
        .map(([key, val]) => `${key}: ${JSON.stringify(val)}`)
        .join(', ');
};
</script>

<template>
    <Head title="Auditoria - Historial" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-black text-text tracking-tight uppercase">
                    Auditoria - Historial de Acciones
                </h2>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Filtros -->
            <div class="bg-white p-4 rounded-2xl shadow-xl">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase">Accion</label>
                        <select v-model="form.action" class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">Todas</option>
                            <option v-for="action in actions" :key="action" :value="action">
                                {{ formatAction(action) }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase">Usuario</label>
                        <select v-model="form.user_id" class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">Todos</option>
                            <option v-for="user in users" :key="user.id" :value="user.id">
                                {{ user.name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase">Desde</label>
                        <input type="date" v-model="form.date_from" class="w-full border rounded-lg px-3 py-2 text-sm" />
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase">Hasta</label>
                        <input type="date" v-model="form.date_to" class="w-full border rounded-lg px-3 py-2 text-sm" />
                    </div>
                </div>
                <div class="flex gap-2">
                    <button @click="applyFilters"
                        class="bg-primary text-white px-6 py-2 rounded-lg font-bold hover:bg-orange-700 transition-colors text-sm">
                        Filtrar
                    </button>
                    <button @click="clearFilters"
                        class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg font-bold hover:bg-gray-300 transition-colors text-sm">
                        Limpiar
                    </button>
                </div>
            </div>

            <!-- Tabla de logs -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Fecha</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Usuario</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Accion</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Modelo</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Detalles</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">IP</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="log in logs.data" :key="log.id" class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">
                                    {{ formatDate(log.created_at) }}
                                </td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-800">
                                    {{ log.user?.name || 'Sistema' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded-full text-xs font-bold"
                                          :class="getActionBadge(log.action)">
                                        {{ formatAction(log.action) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    {{ log.model_type }} #{{ log.model_id }}
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500 max-w-xs truncate" :title="formatValues(log.new_values)">
                                    {{ formatValues(log.new_values) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 font-mono">
                                    {{ log.ip_address }}
                                </td>
                            </tr>
                            <tr v-if="logs.data.length === 0">
                                <td colspan="6" class="px-4 py-12">
                                    <EmptyState icon="📋" title="Sin registros" message="No hay registros de auditoría" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="logs.links.length > 0" class="px-4 py-3 border-t bg-gray-50 flex justify-between items-center">
                    <span class="text-sm text-gray-600">
                        Mostrando {{ logs.from || 0 }} - {{ logs.to || 0 }} de {{ logs.total }} registros
                    </span>
                    <div class="flex gap-1">
                        <Link v-for="(link, index) in logs.links"
                            :key="index"
                            :href="link.url || '#'"
                            v-text="link.label"
                            class="px-3 py-1 text-sm rounded-lg transition-colors"
                            :class="{
                                'bg-primary text-white font-bold': link.active,
                                'text-gray-600 hover:bg-gray-200': !link.active && link.url,
                                'text-gray-300 cursor-not-allowed': !link.url,
                            }"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
