<script setup>
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import Card from '@/components/Card.vue';
import EmptyState from '@/components/EmptyState.vue';

const props = defineProps({
    orders: Object,
    stats: Object,
    users: Array,
    filters: Object,
});

const form = ref({
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
    user_id: props.filters.user_id || '',
});

const applyFilters = () => {
    router.get(route('cancellations.index'), {
        ...form.value,
    }, { preserveState: true });
};

const clearFilters = () => {
    form.value = { date_from: '', date_to: '', user_id: '' };
    router.get(route('cancellations.index'));
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
</script>

<template>
    <Head title="Cancelaciones" />
    <AuthenticatedLayout>
        <div class="pb-6">
            <div class="-mx-3 sm:-mx-6 -mt-3 sm:-mt-6 bg-gradient-to-r from-primary via-orange-500 to-orange-600 text-white p-6 sm:p-8 shadow-2xl relative overflow-hidden mb-8">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-32 -mt-32 blur-3xl"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-black/10 rounded-full -ml-24 -mb-24 blur-2xl"></div>
                <div class="relative z-10">
                    <h1 class="text-3xl font-black italic uppercase tracking-tight">Cancelaciones</h1>
                    <p class="text-orange-100 mt-1 font-bold text-sm">Historial de pedidos cancelados</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <Card hover class="border-l-8 border-red-500">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Cancelados</p>
                    <p class="text-3xl font-black text-red-600 mt-1 font-mono">{{ stats.total_cancelled }}</p>
                </Card>
                <Card hover class="border-l-8 border-orange-500">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Reembolsado</p>
                    <p class="text-3xl font-black text-orange-600 mt-1 font-mono">Bs. {{ parseFloat(stats.total_refunded).toFixed(2) }}</p>
                </Card>
                <Card hover class="border-l-8 border-yellow-500">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Hoy</p>
                    <p class="text-3xl font-black text-yellow-600 mt-1 font-mono">{{ stats.today_cancelled }}</p>
                </Card>
                <Card hover class="border-l-8 border-purple-500">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Top Motivo</p>
                    <p class="text-base font-black text-purple-600 mt-1 truncate">{{ stats.top_reasons?.[0]?.cancellation_reason || 'N/A' }}</p>
                </Card>
            </div>

            <Card v-if="stats.top_reasons?.length > 0" padding="p-6" class="mb-8">
                <h3 class="text-sm font-black text-text uppercase tracking-wider mb-4">Motivos más frecuentes</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                    <div v-for="reason in stats.top_reasons" :key="reason.cancellation_reason"
                        class="p-3 rounded-xl bg-red-50 border border-red-100">
                        <p class="text-xs font-bold text-gray-700 truncate">{{ reason.cancellation_reason }}</p>
                        <p class="text-lg font-black text-danger mt-1">{{ reason.count }} <span class="text-[10px] font-normal text-gray-500">veces</span></p>
                    </div>
                </div>
            </Card>

            <Card padding="p-4 sm:p-6" class="mb-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Usuario</label>
                        <select v-model="form.user_id"
                            class="mt-1 w-full border-2 border-gray-200 rounded-lg px-3 py-2 text-sm font-bold focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none transition-all">
                            <option value="">Todos</option>
                            <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
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
                    <div class="flex gap-2 items-end">
                        <button @click="applyFilters"
                            class="bg-primary text-white px-5 py-2.5 rounded-lg font-bold text-sm hover:bg-orange-600 transition-all active:scale-95 shadow-lg shadow-orange-200">
                            Filtrar
                        </button>
                        <button @click="clearFilters"
                            class="bg-gray-100 text-gray-700 px-5 py-2.5 rounded-lg font-bold text-sm hover:bg-gray-200 transition-all active:scale-95">
                            Limpiar
                        </button>
                    </div>
                </div>
            </Card>

            <Card noPadding>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Fecha</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Pedido</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Cajero</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Motivo</th>
                                <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Total</th>
                                <th class="px-6 py-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Reembolso</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="order in orders.data" :key="order.id" class="hover:bg-red-50/30 transition-colors">
                                <td class="px-6 py-4 text-sm font-bold text-gray-600 whitespace-nowrap">{{ formatDate(order.created_at) }}</td>
                                <td class="px-6 py-4 text-sm font-black text-gray-800">{{ order.order_number }}</td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-600">{{ order.user?.name || 'N/A' }}</td>
                                <td class="px-6 py-4">
                                    <span class="bg-red-100 text-red-800 px-3 py-1 rounded-lg text-[10px] font-black uppercase">{{ order.cancellation_reason }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm font-black text-text text-right font-mono">Bs. {{ order.total_amount }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-[10px] font-black"
                                        :class="order.payment_status === 'refunded' ? 'bg-orange-100 text-orange-800' : 'bg-gray-100 text-gray-600'">
                                        <span class="w-1.5 h-1.5 rounded-full" :class="order.payment_status === 'refunded' ? 'bg-orange-500' : 'bg-gray-400'"></span>
                                        {{ order.payment_status === 'refunded' ? 'Reembolsado' : 'Sin reembolso' }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="orders.data.length === 0">
                                <td colspan="6" class="px-6 py-12">
                                    <EmptyState icon="✅" title="Sin cancelaciones" message="No hay pedidos cancelados en este período" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="orders.links?.length > 0" class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-between items-center">
                    <span class="text-sm text-gray-600 font-bold">Mostrando {{ orders.from || 0 }} - {{ orders.to || 0 }} de {{ orders.total }} registros</span>
                    <div class="flex gap-1">
                        <Link v-for="(link, index) in orders.links" :key="index"
                            :href="link.url || '#'" v-text="link.label"
                            class="px-3 py-1.5 text-sm rounded-lg font-bold transition-all"
                            :class="{ 'bg-primary text-white shadow-lg shadow-orange-200': link.active, 'text-gray-600 hover:bg-gray-200': !link.active && link.url, 'text-gray-300 cursor-not-allowed': !link.url }" />
                    </div>
                </div>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
