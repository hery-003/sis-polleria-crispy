<script setup>
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import EmptyState from '@/components/EmptyState.vue';

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

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                <div
                    v-for="order in orders"
                    :key="order.id"
                    :class="[
                        'bg-white rounded-[2.5rem] shadow-2xl overflow-hidden border-t-[12px] transition-all transform hover:scale-[1.02]',
                        order.status === 'pending' ? 'border-danger shadow-red-500/10' :
                        order.status === 'cooking' ? 'border-primary shadow-orange-500/10' : 'border-green-500 shadow-green-500/10'
                    ]"
                >
                    <!-- Cabecera de la Comanda -->
                    <div class="p-6 bg-gray-50/50 border-b flex justify-between items-start">
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1">#{{ order.order_number }}</p>
                            <h3 class="text-xl font-black text-text font-poppins">{{ order.type === 'dine_in' ? '🏠 MESA' : order.type === 'take_out' ? '🛍️ LLEVAR' : '🚴 DELIVERY' }}</h3>
                        </div>
                        <span class="px-4 py-1.5 rounded-full text-[10px] font-black border-2" :class="getStatusColor(order.status)">
                            {{ getStatusLabel(order.status) }}
                        </span>
                    </div>

                    <!-- Tiempo transcurrido -->
                    <div class="px-6 py-3 bg-gray-100/50 flex justify-between items-center border-b border-gray-100">
                        <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Tiempo:</span>
                        <div class="flex items-center gap-2">
                            <span class="relative flex h-2 w-2">
                                <span :class="['animate-ping absolute inline-flex h-full w-full rounded-full opacity-75', parseInt(getTimeElapsed(order.created_at)) > 15 ? 'bg-red-400' : 'bg-green-400']"></span>
                                <span :class="['relative inline-flex rounded-full h-2 w-2', parseInt(getTimeElapsed(order.created_at)) > 15 ? 'bg-red-600' : 'bg-green-500']"></span>
                            </span>
                            <span :class="['text-lg font-black font-mono', parseInt(getTimeElapsed(order.created_at)) > 15 ? 'text-danger' : 'text-text']">
                                {{ getTimeElapsed(order.created_at) }}
                            </span>
                        </div>
                    </div>

                    <!-- Items del Pedido -->
                    <div class="p-6 space-y-5 min-h-[220px]">
                        <div v-for="item in order.items" :key="item.id" class="flex gap-5 items-center bg-gray-50 p-4 rounded-3xl border border-gray-100 group hover:border-primary/30 transition-colors">
                            <span class="w-14 h-14 flex items-center justify-center bg-text text-white rounded-2xl font-black text-3xl shadow-xl shadow-gray-200 flex-shrink-0 group-hover:scale-110 transition-transform">
                                {{ item.quantity }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="font-black text-text uppercase text-base leading-tight font-poppins">{{ item.product.name }}</p>
                                <p class="text-[11px] font-black text-primary uppercase tracking-widest mt-1">{{ item.variant.name }}</p>
                                <p v-if="item.notes" class="text-[11px] italic text-danger mt-2 font-black bg-red-50 px-3 py-1 rounded-xl inline-block border border-red-100">
                                    ⚠️ {{ item.notes }}
                                </p>
                            </div>
                        </div>

                        <div v-if="order.notes" class="mt-4 p-4 bg-secondary/10 border-2 border-dashed border-secondary/30 rounded-3xl text-xs text-text font-bold">
                            <span class="block text-[10px] font-black uppercase mb-1.5 opacity-60">Observaciones Generales:</span>
                            {{ order.notes }}
                        </div>
                    </div>

                    <!-- Acciones -->
                    <div class="p-6 bg-white mt-auto">
                        <button
                            v-if="order.status === 'pending'"
                            @click="updateStatus(order, 'cooking')"
                            class="w-full py-5 bg-gradient-to-br from-primary to-orange-600 hover:to-orange-700 text-white rounded-3xl font-black uppercase text-sm tracking-widest transition-all shadow-xl shadow-orange-500/20 active:scale-95 flex items-center justify-center gap-3"
                        >
                            <span>🔥</span> Empezar Preparación
                        </button>
                        <button
                            v-if="order.status === 'cooking'"
                            @click="updateStatus(order, 'ready')"
                            class="w-full py-5 bg-gradient-to-br from-secondary to-yellow-500 hover:to-yellow-600 text-text rounded-3xl font-black uppercase text-sm tracking-widest transition-all shadow-xl shadow-yellow-500/20 active:scale-95 flex items-center justify-center gap-3"
                        >
                            <span>✅</span> Listo para Entregar
                        </button>
                        <button
                            v-if="order.status === 'ready'"
                            @click="updateStatus(order, 'completed')"
                            class="w-full py-5 bg-gradient-to-br from-text to-gray-900 hover:to-black text-white rounded-3xl font-black uppercase text-sm tracking-widest transition-all shadow-xl shadow-gray-400/20 active:scale-95 flex items-center justify-center gap-3"
                        >
                            <span>📦</span> Marcar como Entregado
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
