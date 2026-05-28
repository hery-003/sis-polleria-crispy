<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import EmptyState from '@/Components/EmptyState.vue';

const props = defineProps({
    orders: Array
});

const orders = ref(JSON.parse(JSON.stringify(props.orders)));

let audioContext = null;

const initAudio = () => {
    if (!audioContext) {
        audioContext = new (window.AudioContext || window.webkitAudioContext)();
    }
    if (audioContext.state === 'suspended') {
        audioContext.resume();
    }
};

const playNewOrderSound = () => {
    try {
        initAudio();
        const frequencies = [523.25, 659.25, 783.99, 1046.50];

        frequencies.forEach((freq, i) => {
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);

            oscillator.frequency.value = freq;
            oscillator.type = 'sine';

            const startTime = audioContext.currentTime + i * 0.15;
            gainNode.gain.setValueAtTime(0.1, startTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, startTime + 0.2);

            oscillator.start(startTime);
            oscillator.stop(startTime + 0.2);
        });
    } catch (e) {
        console.warn('Audio not available:', e);
    }
};

onMounted(() => {
    // Escuchar eventos de pedidos en tiempo real
    window.Echo?.private('orders')
        ?.listen('.order.created', (e) => {
            orders.value.unshift(e);
            playNewOrderSound();
            // Notificación visual
            if (Notification.permission === 'granted') {
                new Notification('Nuevo Pedido', {
                    body: `Pedido ${e.order_number} - ${e.type}`,
                    icon: '/favicon.ico'
                });
            }
        })
        ?.listen('.order.updated', (e) => {
            const index = orders.value.findIndex(o => o.id === e.id);
            if (index !== -1) {
                orders.value[index] = { ...orders.value[index], ...e };
            } else {
                orders.value.unshift(e);
            }
        });

    // Solicitar permiso para notificaciones
    if (Notification.permission === 'default') {
        Notification.requestPermission();
    }
});

onUnmounted(() => {
    window.Echo?.leaveChannel('orders');
});

const updateStatus = (order, newStatus) => {
    router.patch(route('kitchen.updateStatus', order.id), {
        status: newStatus
    });
};

const getStatusColor = (status) => {
    switch (status) {
        case 'pending': return 'bg-red-100 text-red-800 border-red-200';
        case 'cooking': return 'bg-orange-100 text-orange-800 border-orange-200';
        case 'ready': return 'bg-secondary/20 text-secondary border-secondary/30';
        default: return 'bg-gray-100 text-gray-800 border-gray-200';
    }
};

const getStatusLabel = (status) => {
    switch (status) {
        case 'pending': return 'PENDIENTE';
        case 'cooking': return 'PREPARANDO';
        case 'ready': return 'LISTO';
        default: return status.toUpperCase();
    }
};

const getTimeElapsed = (createdAt) => {
    const start = new Date(createdAt);
    const now = new Date();
    const diff = Math.floor((now - start) / 60000);
    return `${diff} min`;
};
</script>

<template>
    <Head title="Monitor de Cocina" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-black text-gray-800 tracking-tight uppercase">
                    👨‍🍳 Monitor de Cocina
                </h2>
                <div class="flex gap-4">
                    <span class="flex items-center gap-2 text-sm font-bold text-gray-500">
                        <span class="w-3 h-3 bg-red-500 rounded-full animate-pulse"></span>
                        En tiempo real
                    </span>
                </div>
            </div>
        </template>

        <div>
            <EmptyState v-if="orders.length === 0" icon="🥗" title="Sin pedidos" message="No hay pedidos pendientes" />

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <div
                    v-for="order in orders"
                    :key="order.id"
                    :class="[
                        'bg-white rounded-3xl shadow-xl overflow-hidden border-t-8 transition-all',
                        order.status === 'pending' ? 'border-red-500' :
                        order.status === 'cooking' ? 'border-orange-500' : 'border-green-500'
                    ]"
                >
                    <!-- Cabecera de la Comanda -->
                    <div class="p-4 bg-gray-50 border-b flex justify-between items-start">
                        <div>
                            <p class="text-xs font-black text-gray-400 uppercase tracking-widest">{{ order.order_number }}</p>
                            <h3 class="text-lg font-black text-gray-800">{{ order.type === 'dine_in' ? '🏠 MESA' : order.type === 'take_out' ? '🛍️ LLEVAR' : '🚴 DELIVERY' }}</h3>
                        </div>
                        <span class="px-3 py-1 rounded-full text-[10px] font-black border" :class="getStatusColor(order.status)">
                            {{ getStatusLabel(order.status) }}
                        </span>
                    </div>

                    <!-- Tiempo transcurrido -->
                    <div class="px-4 py-2 bg-gray-100 flex justify-between items-center">
                        <span class="text-xs font-bold text-gray-500 uppercase">Tiempo:</span>
                        <span :class="['text-sm font-black', parseInt(getTimeElapsed(order.created_at)) > 15 ? 'text-red-600' : 'text-gray-700']">
                            {{ getTimeElapsed(order.created_at) }}
                        </span>
                    </div>

                    <!-- Items del Pedido -->
                    <div class="p-4 space-y-3 min-h-[150px]">
                        <div v-for="item in order.items" :key="item.id" class="flex gap-3">
                            <span class="w-8 h-8 flex items-center justify-center bg-gray-800 text-white rounded-lg font-black text-sm">
                                {{ item.quantity }}
                            </span>
                            <div class="flex-1">
                                <p class="font-black text-gray-800 uppercase text-sm leading-tight">{{ item.product.name }}</p>
                                <p class="text-[10px] font-bold text-orange-600 uppercase">{{ item.variant.name }}</p>
                                <p v-if="item.notes" class="text-xs italic text-blue-600 mt-1 font-medium">* {{ item.notes }}</p>
                            </div>
                        </div>

                        <div v-if="order.notes" class="mt-4 p-2 bg-yellow-50 border border-yellow-100 rounded-lg text-xs text-yellow-800 font-medium">
                            <strong>NOTAS:</strong> {{ order.notes }}
                        </div>
                    </div>

                    <!-- Acciones -->
                    <div class="p-4 bg-gray-50 border-t mt-auto">
                        <button
                            v-if="order.status === 'pending'"
                            @click="updateStatus(order, 'cooking')"
                            class="w-full py-3 bg-orange-600 hover:bg-orange-700 text-white rounded-xl font-black uppercase text-xs tracking-widest transition-all"
                        >
                            Empezar Preparación
                        </button>
                        <button
                            v-if="order.status === 'cooking'"
                            @click="updateStatus(order, 'ready')"
                            class="w-full py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl font-black uppercase text-xs tracking-widest transition-all"
                        >
                            Listo para Entregar
                        </button>
                        <button
                            v-if="order.status === 'ready'"
                            @click="updateStatus(order, 'completed')"
                            class="w-full py-3 bg-gray-800 hover:bg-gray-900 text-white rounded-xl font-black uppercase text-xs tracking-widest transition-all"
                        >
                            Marcar como Entregado
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
