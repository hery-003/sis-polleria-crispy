<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import EmptyState from '@/Components/EmptyState.vue';

const props = defineProps({
    orders: Array
});

const orders = ref([...props.orders]);

onMounted(() => {
    window.Echo?.channel('orders')
        ?.listen('.order.updated', (e) => {
            if (e.type === 'dine_in' && (e.status === 'ready' || e.status === 'completed')) {
                const index = orders.value.findIndex(o => o.id === e.id);
                if (index !== -1) {
                    orders.value[index] = { ...orders.value[index], ...e };
                } else {
                    orders.value.unshift(e);
                }
            }
        });
});

onUnmounted(() => {
    window.Echo?.leaveChannel('orders');
});

const markDelivered = (orderId) => {
    router.post(route('waiter.deliver', orderId));
};

const getTypeIcon = (type) => {
    if (type === 'dine_in') return '🏠';
    if (type === 'take_out') return '🛍️';
    return '🚴';
};

const getTimeElapsed = (updatedAt) => {
    const start = new Date(updatedAt);
    const now = new Date();
    const diff = Math.floor((now - start) / 60000);
    if (diff < 1) return 'Ahora';
    return `${diff} min`;
};

const readyOrders = (orders) => orders.filter(o => o.status === 'ready');
const completedOrders = (orders) => orders.filter(o => o.status === 'completed');
</script>

<template>
    <Head title="Mesero" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-black text-text uppercase tracking-tight">
                    🧑‍🍳 Vista Mesero
                </h2>
                <div class="flex items-center gap-4">
                    <span class="flex items-center gap-2 text-sm font-bold text-gray-500">
                        <span class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></span>
                        Tiempo real
                    </span>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Listos para Servir -->
            <div>
                <div class="flex items-center gap-3 mb-6">
                    <span class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></span>
                    <h3 class="text-xl font-black text-text uppercase">Listos para Servir</h3>
                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-black">
                        {{ readyOrders(orders).length }}
                    </span>
                </div>

                <EmptyState v-if="readyOrders(orders).length === 0" icon="🍽️" title="Sin pedidos listos" message="No hay pedidos listos para servir" />

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <div
                        v-for="order in readyOrders(orders)"
                        :key="order.id"
                        class="bg-white rounded-3xl shadow-xl overflow-hidden border-t-8 border-green-500 hover:shadow-2xl transition-all group"
                    >
                        <div class="p-4 bg-gradient-to-r from-green-50 to-white border-b flex justify-between items-start">
                            <div>
                                <p class="text-xs font-black text-gray-400 uppercase tracking-widest">{{ order.order_number }}</p>
                                <h3 class="text-xl font-black text-gray-800 mt-1">
                                    {{ order.mesa ? `Mesa ${order.mesa.name || order.mesa.number}` : 'Sin mesa' }}
                                </h3>
                                <p class="text-xs font-bold text-gray-500 mt-1">
                                    {{ getTypeIcon(order.type) }} {{ order.type === 'dine_in' ? 'Mesa' : order.type === 'take_out' ? 'Llevar' : 'Delivery' }}
                                </p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-[10px] font-black bg-green-100 text-green-800 border border-green-200">
                                LISTO
                            </span>
                        </div>

                        <div class="px-4 py-2 bg-gray-50 flex justify-between items-center">
                            <span class="text-xs font-bold text-gray-500 uppercase">Esperando:</span>
                            <span class="text-sm font-black text-gray-700">{{ getTimeElapsed(order.updated_at) }}</span>
                        </div>

                        <div class="p-4 space-y-2 min-h-[120px]">
                            <div v-for="item in order.items" :key="item.id" class="flex gap-3 items-start">
                                <span class="w-7 h-7 flex items-center justify-center bg-green-600 text-white rounded-lg font-black text-xs">
                                    {{ item.quantity }}
                                </span>
                                <div class="flex-1">
                                    <p class="font-black text-gray-800 uppercase text-sm leading-tight">{{ item.product.name }}</p>
                                    <p class="text-[10px] font-bold text-orange-600 uppercase">{{ item.variant.name }}</p>
                                    <p v-if="item.notes" class="text-xs italic text-blue-600 mt-1 font-medium">* {{ item.notes }}</p>
                                </div>
                            </div>
                            <div v-if="order.notes" class="mt-3 p-2 bg-yellow-50 border border-yellow-100 rounded-lg text-xs text-yellow-800 font-medium">
                                <strong>NOTA:</strong> {{ order.notes }}
                            </div>
                        </div>

                        <div class="p-4 bg-gray-50 border-t">
                            <button
                                @click="markDelivered(order.id)"
                                class="w-full py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl font-black uppercase text-xs tracking-widest transition-all hover:-translate-y-0.5 shadow-lg"
                            >
                                ✅ Marcar como Entregado
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Entregados Recientemente -->
            <div>
                <div class="flex items-center gap-3 mb-6">
                    <span class="w-3 h-3 bg-blue-500 rounded-full"></span>
                    <h3 class="text-xl font-black text-text uppercase">Entregados</h3>
                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-black">
                        {{ completedOrders(orders).length }}
                    </span>
                </div>

                <EmptyState v-if="completedOrders(orders).length === 0" icon="✅" title="Sin entregas" message="No hay entregas recientes" />

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <div
                        v-for="order in completedOrders(orders)"
                        :key="order.id"
                        class="bg-white rounded-3xl shadow-xl overflow-hidden border-t-8 border-blue-400 opacity-75"
                    >
                        <div class="p-4 bg-gray-50 border-b flex justify-between items-start">
                            <div>
                                <p class="text-xs font-black text-gray-400 uppercase">{{ order.order_number }}</p>
                                <h3 class="text-lg font-black text-gray-700 mt-1">
                                    {{ order.mesa ? `Mesa ${order.mesa.name || order.mesa.number}` : 'Sin mesa' }}
                                </h3>
                                <p class="text-xs text-gray-500 font-medium mt-1">
                                    {{ order.user?.name }}
                                </p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-[10px] font-black bg-blue-100 text-blue-800 border border-blue-200">
                                ENTREGADO
                            </span>
                        </div>
                        <div class="p-4 space-y-1">
                            <div v-for="item in order.items" :key="item.id" class="flex gap-2">
                                <span class="text-xs font-black text-gray-500">{{ item.quantity }}x</span>
                                <span class="text-sm font-medium text-gray-600">{{ item.product.name }}</span>
                            </div>
                            <p class="text-sm font-black text-gray-700 mt-2">
                                Total: Bs. {{ order.total_amount }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
