<script setup>
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import EmptyState from '@/components/EmptyState.vue';
import Card from '@/components/Card.vue';
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

const selectedLog = ref(null);
const showDetail = ref(false);

const openDetail = (log) => {
    selectedLog.value = log;
    showDetail.value = true;
};
</script>

<template>
    <Head title="Auditoria - Historial" />
    <AuthenticatedLayout>
        <div class="pb-6">
            <div class="-mx-3 sm:-mx-6 -mt-3 sm:-mt-6 bg-gradient-to-r from-primary via-orange-500 to-orange-600 text-white p-6 sm:p-8 shadow-2xl relative overflow-hidden mb-8">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-32 -mt-32 blur-3xl"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-black/10 rounded-full -ml-24 -mb-24 blur-2xl"></div>
                <div class="relative z-10">
                    <h1 class="text-3xl font-black italic uppercase tracking-tight">Auditoría</h1>
                    <p class="text-orange-100 mt-1 font-bold text-sm">Historial de acciones del sistema</p>
                </div>
            </div>

            <Card padding="p-4 sm:p-6" class="mb-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Acción</label>
                        <select v-model="form.action"
                            class="mt-1 w-full border-2 border-gray-200 rounded-lg px-3 py-2 text-sm font-bold focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none transition-all">
                            <option value="">Todas</option>
                            <option v-for="action in actions" :key="action" :value="action">
                                {{ formatAction(action) }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Usuario</label>
                        <select v-model="form.user_id"
                            class="mt-1 w-full border-2 border-gray-200 rounded-lg px-3 py-2 text-sm font-bold focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none transition-all">
                            <option value="">Todos</option>
                            <option v-for="user in users" :key="user.id" :value="user.id">
                                {{ user.name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Desde</label>
                        <input type="date" v-model="form.date_from"
                            class="mt-1 w-full border-2 border-gray-200 rounded-lg px-3 py-2 text-sm font-bold focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none transition-all" />
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Hasta</label>
                        <input type="date" v-model="form.date_to"
                            class="mt-1 w-full border-2 border-gray-200 rounded-lg px-3 py-2 text-sm font-bold focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none transition-all" />
                    </div>
                </div>
                <div class="flex gap-2">
                    <button @click="applyFilters"
                        class="bg-primary text-white px-5 py-2.5 rounded-lg font-bold text-sm hover:bg-orange-600 transition-all active:scale-95 shadow-lg shadow-orange-200">
                        Filtrar
                    </button>
                    <button @click="clearFilters"
                        class="bg-gray-100 text-gray-700 px-5 py-2.5 rounded-lg font-bold text-sm hover:bg-gray-200 transition-all active:scale-95">
                        Limpiar
                    </button>
                </div>
            </Card>

            <Card noPadding>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Fecha</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Usuario</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Acción</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Modelo</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Detalles</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">IP</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="log in logs.data" :key="log.id" class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-sm font-bold text-gray-600 whitespace-nowrap">
                                    {{ formatDate(log.created_at) }}
                                </td>
                                <td class="px-6 py-4 text-sm font-bold text-gray-800">
                                    {{ log.user?.name || 'Sistema' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-lg text-[10px] font-black" :class="getActionBadge(log.action)">
                                        {{ formatAction(log.action) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-600">
                                    {{ log.model_type }} #{{ log.model_id }}
                                </td>
                                <td class="px-6 py-4">
                                    <button @click="openDetail(log)"
                                        class="text-xs text-primary hover:text-orange-700 font-bold truncate max-w-[200px] text-left"
                                        :title="formatValues(log.new_values)">
                                        {{ formatValues(log.new_values) }}
                                    </button>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 font-mono font-bold">
                                    {{ log.ip_address }}
                                </td>
                            </tr>
                            <tr v-if="logs.data.length === 0">
                                <td colspan="6" class="px-6 py-12">
                                    <EmptyState icon="📋" title="Sin registros" message="No hay registros de auditoría" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="logs.links.length > 0" class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-between items-center">
                    <span class="text-sm text-gray-600 font-bold">
                        Mostrando {{ logs.from || 0 }} - {{ logs.to || 0 }} de {{ logs.total }} registros
                    </span>
                    <div class="flex gap-1">
                        <Link v-for="(link, index) in logs.links"
                            :key="index"
                            :href="link.url || '#'"
                            v-text="link.label"
                            class="px-3 py-1.5 text-sm rounded-lg font-bold transition-all"
                            :class="{
                                'bg-primary text-white shadow-lg shadow-orange-200': link.active,
                                'text-gray-600 hover:bg-gray-200': !link.active && link.url,
                                'text-gray-300 cursor-not-allowed': !link.url,
                            }"
                        />
                    </div>
                </div>
            </Card>
        </div>

        <Teleport to="body">
            <div v-if="showDetail && selectedLog" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click.self="showDetail = false">
                <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl max-h-[80vh] overflow-y-auto">
                    <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-lg font-black text-text uppercase">Detalle de Auditoría</h3>
                        <button @click="showDetail = false" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-xl transition-all text-lg">✕</button>
                    </div>
                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Acción</label>
                                <p class="mt-1"><span class="px-3 py-1 rounded-lg text-[10px] font-black" :class="getActionBadge(selectedLog.action)">{{ formatAction(selectedLog.action) }}</span></p>
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Usuario</label>
                                <p class="mt-1 font-bold text-gray-800">{{ selectedLog.user?.name || 'Sistema' }}</p>
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Modelo</label>
                                <p class="mt-1 text-sm font-bold text-gray-700">{{ selectedLog.model_type }} #{{ selectedLog.model_id }}</p>
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Fecha</label>
                                <p class="mt-1 text-sm font-bold text-gray-700">{{ formatDate(selectedLog.created_at) }}</p>
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">IP</label>
                                <p class="mt-1 text-sm font-mono font-bold text-gray-700">{{ selectedLog.ip_address }}</p>
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">User Agent</label>
                                <p class="mt-1 text-xs text-gray-500 truncate">{{ selectedLog.user_agent }}</p>
                            </div>
                        </div>

                        <div v-if="selectedLog.old_values" class="border-t pt-4">
                            <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Valores Anteriores</h4>
                            <div class="bg-red-50 rounded-xl p-4">
                                <table class="w-full text-xs">
                                    <tr v-for="(val, key) in selectedLog.old_values" :key="'old-'+key" class="border-b border-red-100">
                                        <td class="py-1.5 font-bold text-gray-600 w-1/3">{{ key }}</td>
                                        <td class="py-1.5 text-gray-800 font-mono font-bold">{{ typeof val === 'object' ? JSON.stringify(val) : val }}</td>
                                    </tr>
                                    <tr v-if="Object.keys(selectedLog.old_values).length === 0">
                                        <td class="py-2 text-gray-400 font-bold">Sin datos</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div v-if="selectedLog.new_values" class="border-t pt-4">
                            <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Valores Nuevos</h4>
                            <div class="bg-green-50 rounded-xl p-4">
                                <table class="w-full text-xs">
                                    <tr v-for="(val, key) in selectedLog.new_values" :key="'new-'+key" class="border-b border-green-100">
                                        <td class="py-1.5 font-bold text-gray-600 w-1/3">{{ key }}</td>
                                        <td class="py-1.5 text-gray-800 font-mono font-bold">{{ typeof val === 'object' ? JSON.stringify(val) : val }}</td>
                                    </tr>
                                    <tr v-if="Object.keys(selectedLog.new_values).length === 0">
                                        <td class="py-2 text-gray-400 font-bold">Sin datos</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>
