<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import StatCard from '@/Components/StatCard.vue';
import Card from '@/Components/Card.vue';
import SkeletonLoader from '@/Components/SkeletonLoader.vue';
import EmptyState from '@/Components/EmptyState.vue';

const props = defineProps({
    todaySales: [Number, String],
    activeOrders: [Number, String],
    cancellations: [Number, String],
    avgTicket: [Number, String],
    cashTotal: [Number, String],
    totalOrdersToday: [Number, String],
    activeRegister: Object,
    recentOrders: Array,
    lowStock: Array,
});

const toNum = (val) => typeof val === 'string' ? parseFloat(val) : (val ?? 0);

const role = usePage().props.auth?.user?.role;
const isAdmin = role === 'admin';
const isCashier = role === 'cashier';

const loading = ref(false);
const currentTime = ref(new Date());
const recentOrders = ref(JSON.parse(JSON.stringify(props.recentOrders)));
const stockAlerts = ref([]);
let interval = null;
let refreshInterval = null;

onMounted(() => {
    interval = setInterval(() => {
        currentTime.value = new Date();
    }, 1000);

    refreshInterval = setInterval(() => {
        loading.value = true;
        router.reload({
            only: ['todaySales', 'activeOrders', 'cancellations', 'avgTicket', 'cashTotal', 'totalOrdersToday', 'activeRegister'],
            onFinish: () => { loading.value = false; },
        });
    }, 30000);

    window.Echo?.private('orders')
        .listen('.order.created', (e) => {
            recentOrders.value.unshift(e);
            if (recentOrders.value.length > 10) recentOrders.value.pop();
        })
        .listen('.order.updated', (e) => {
            const index = recentOrders.value.findIndex(o => o.id === e.id);
            if (index !== -1) {
                recentOrders.value[index] = { ...recentOrders.value[index], ...e };
            }
        });

    window.Echo?.private('inventory')
        .listen('.stock.low', (e) => {
            const exists = stockAlerts.value.find(a => a.product_name === e.product_name && a.variant_name === e.variant_name);
            if (exists) {
                exists.stock = e.stock;
            } else {
                stockAlerts.value.unshift(e);
                if (stockAlerts.value.length > 10) stockAlerts.value.pop();
            }
        });
});

onUnmounted(() => {
    clearInterval(interval);
    clearInterval(refreshInterval);
    window.Echo?.leaveChannel('orders');
    window.Echo?.leaveChannel('inventory');
});

const formatTime = (date) => {
    return date.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
};

const formatDate = (date) => {
    return date.toLocaleDateString('es-PE', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
};

const getOrderStatusColor = (status) => {
    const colors = {
        'pending': 'bg-red-100 text-red-800',
        'cooking': 'bg-orange-100 text-orange-800',
        'ready': 'bg-green-100 text-green-800',
        'completed': 'bg-blue-100 text-blue-800',
        'cancelled': 'bg-gray-100 text-gray-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const getOrderStatusLabel = (status) => {
    const labels = {
        'pending': 'Pendiente',
        'cooking': 'Preparando',
        'ready': 'Listo',
        'completed': 'Entregado',
        'cancelled': 'Cancelado',
    };
    return labels[status] || status;
};
</script>

<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout>
        <div class="pb-8">
            <!-- Header Mejorado -->
            <div class="-mx-6 -mt-6 bg-gradient-to-r from-primary via-orange-500 to-orange-600 text-white p-8 shadow-2xl relative overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-32 -mt-32 blur-3xl"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-black/10 rounded-full -ml-24 -mb-24 blur-2xl"></div>
                
                <div class="w-full flex flex-col md:flex-row justify-between items-center px-6 relative z-10 gap-6">
                    <div>
                        <h1 class="text-4xl font-black italic uppercase tracking-tight font-poppins">
                            ¡Hola, {{ $page.props.auth.user.name }}! 👋
                        </h1>
                        <p class="text-orange-100 mt-2 font-bold text-lg capitalize flex items-center gap-2">
                            <span>📅</span> {{ formatDate(currentTime) }}
                        </p>
                    </div>
                    <div class="text-center md:text-right">
                        <div class="text-5xl font-black font-mono tracking-tighter drop-shadow-md">
                            {{ formatTime(currentTime) }}
                        </div>
                        <div v-if="activeRegister" class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-md px-4 py-1.5 rounded-full text-sm mt-3 font-black border border-white/30">
                            <span class="w-2 h-2 bg-green-400 rounded-full animate-ping"></span>
                            ✅ CAJA ABIERTA (Bs. {{ toNum(activeRegister.opening_balance).toFixed(2) }})
                        </div>
                        <div v-else class="inline-flex items-center gap-2 bg-red-500/20 backdrop-blur-md px-4 py-1.5 rounded-full text-sm mt-3 font-black border border-red-500/30 text-red-100 animate-pulse">
                            <span>🔒</span> CAJA CERRADA
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-12 mt-8">
                <!-- KPIs Row -->
                <div v-if="loading" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6">
                    <div v-for="i in 5" :key="i" class="bg-white rounded-2xl border border-gray-100 p-6 animate-pulse">
                        <div class="h-4 bg-gray-200 rounded w-1/3 mb-4"></div>
                        <div class="h-8 bg-gray-200 rounded w-1/2"></div>
                    </div>
                </div>
                <div v-else class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6">
                    <StatCard 
                        label="Ventas Hoy" 
                        :value="'Bs. ' + toNum(todaySales).toFixed(2)" 
                        icon="💵" 
                        color="primary"
                    />
                    <StatCard 
                        label="Pedidos Activos" 
                        :value="activeOrders || 0" 
                        icon="🍽️" 
                        color="blue"
                    />
                    <StatCard 
                        label="Ticket Promedio" 
                        :value="'Bs. ' + toNum(avgTicket).toFixed(2)" 
                        icon="🎫" 
                        color="purple"
                    />
                    <StatCard 
                        label="Efectivo Hoy" 
                        :value="'Bs. ' + toNum(cashTotal).toFixed(2)" 
                        icon="💰" 
                        color="green"
                    />
                    <StatCard 
                        label="Cancelaciones" 
                        :value="cancellations || 0" 
                        icon="⚠️" 
                        color="danger"
                    />
                </div>

                <!-- Alertas de Stock Mejoradas -->
                <div v-if="stockAlerts.length > 0">
                    <h2 class="text-2xl font-black text-text mb-6 uppercase flex items-center gap-3 font-poppins tracking-tight">
                        <span class="w-2.5 h-10 bg-danger rounded-full shadow-lg shadow-red-200"></span>
                        Alertas de Inventario
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
                        <Card v-for="alert in stockAlerts" :key="alert.product_name + alert.variant_name"
                            hover class="border-l-8 border-danger">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ alert.product_name }}</p>
                                    <p class="text-base font-black text-text mt-1 leading-tight">{{ alert.variant_name }}</p>
                                </div>
                                <span class="text-3xl animate-bounce">⚠️</span>
                            </div>
                            <div class="mt-4">
                                <div class="flex justify-between items-center mb-1.5">
                                    <span class="text-[10px] font-black text-danger uppercase">Stock Crítico</span>
                                    <span class="text-sm font-black text-danger">{{ alert.stock }} uds.</span>
                                </div>
                                <div class="h-3 bg-gray-100 rounded-full overflow-hidden border border-gray-200">
                                    <div class="h-full bg-danger rounded-full transition-all duration-300" :style="{ width: Math.max(5, (alert.stock / 10) * 100) + '%' }"></div>
                                </div>
                            </div>
                        </Card>
                    </div>
                </div>

                <!-- Accesos Rapidos Modernizados -->
                <div>
                    <h2 class="text-2xl font-black text-text mb-6 uppercase flex items-center gap-3 font-poppins tracking-tight">
                        <span class="w-2.5 h-10 bg-primary rounded-full shadow-lg shadow-orange-200"></span>
                        Accesos Rápidos
                    </h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
                        <Link :href="route('pos.index')" class="group relative overflow-hidden bg-gradient-to-br from-primary to-orange-600 rounded-[2rem] p-8 text-white shadow-xl hover:shadow-2xl transition-all hover:-translate-y-2 flex flex-col items-center text-center">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-white/20 rounded-full -mr-12 -mt-12 group-hover:scale-150 transition-transform duration-300"></div>
                            <span class="text-6xl mb-6 block relative z-10 group-hover:rotate-12 transition-transform">🛒</span>
                            <span class="text-xl font-black uppercase block relative z-10 font-poppins">POS</span>
                            <span class="text-orange-100 mt-2 text-xs font-bold block relative z-10 opacity-80">Ventas rápidas</span>
                        </Link>

                        <Link :href="route('kitchen.index')" class="group relative overflow-hidden bg-gradient-to-br from-secondary to-yellow-500 rounded-[2rem] p-8 text-white shadow-xl hover:shadow-2xl transition-all hover:-translate-y-2 flex flex-col items-center text-center">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-white/20 rounded-full -mr-12 -mt-12 group-hover:scale-150 transition-transform duration-300"></div>
                            <span class="text-6xl mb-6 block relative z-10 group-hover:scale-110 transition-transform">👨‍🍳</span>
                            <span class="text-xl font-black uppercase block relative z-10 font-poppins">Cocina</span>
                            <span class="text-yellow-100 mt-2 text-xs font-bold block relative z-10 opacity-80">Monitor pedidos</span>
                        </Link>

                        <Link v-if="isAdmin" :href="route('cash-register.index')" class="group relative overflow-hidden bg-gradient-to-br from-green-600 to-emerald-700 rounded-[2rem] p-8 text-white shadow-xl hover:shadow-2xl transition-all hover:-translate-y-2 flex flex-col items-center text-center">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-white/20 rounded-full -mr-12 -mt-12 group-hover:scale-150 transition-transform duration-300"></div>
                            <span class="text-6xl mb-6 block relative z-10 group-hover:scale-110 transition-transform">💰</span>
                            <span class="text-xl font-black uppercase block relative z-10 font-poppins">Caja</span>
                            <span class="text-green-100 mt-2 text-xs font-bold block relative z-10 opacity-80">Control diario</span>
                        </Link>

                        <Link v-if="isAdmin" :href="route('reports.index')" class="group relative overflow-hidden bg-gradient-to-br from-blue-600 to-indigo-700 rounded-[2rem] p-8 text-white shadow-xl hover:shadow-2xl transition-all hover:-translate-y-2 flex flex-col items-center text-center">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-white/20 rounded-full -mr-12 -mt-12 group-hover:scale-150 transition-transform duration-300"></div>
                            <span class="text-6xl mb-6 block relative z-10 group-hover:rotate-6 transition-transform">📊</span>
                            <span class="text-xl font-black uppercase block relative z-10 font-poppins">Reportes</span>
                            <span class="text-blue-100 mt-2 text-xs font-bold block relative z-10 opacity-80">Estadísticas</span>
                        </Link>

                        <Link :href="route('waiter.index')" class="group relative overflow-hidden bg-gradient-to-br from-purple-600 to-fuchsia-700 rounded-[2rem] p-8 text-white shadow-xl hover:shadow-2xl transition-all hover:-translate-y-2 flex flex-col items-center text-center">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-white/20 rounded-full -mr-12 -mt-12 group-hover:scale-150 transition-transform duration-300"></div>
                            <span class="text-6xl mb-6 block relative z-10 group-hover:-rotate-6 transition-transform">🚶‍♂️</span>
                            <span class="text-xl font-black uppercase block relative z-10 font-poppins">Mesero</span>
                            <span class="text-purple-100 mt-2 text-xs font-bold block relative z-10 opacity-80">Atención mesas</span>
                        </Link>

                        <Link :href="route('products.index')" class="group relative overflow-hidden bg-gradient-to-br from-orange-400 to-yellow-600 rounded-[2rem] p-8 text-white shadow-xl hover:shadow-2xl transition-all hover:-translate-y-2 flex flex-col items-center text-center">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-white/20 rounded-full -mr-12 -mt-12 group-hover:scale-150 transition-transform duration-300"></div>
                            <span class="text-6xl mb-6 block relative z-10 group-hover:scale-110 transition-transform">🍗</span>
                            <span class="text-xl font-black uppercase block relative z-10 font-poppins">Menú</span>
                            <span class="text-orange-50 mt-2 text-xs font-bold block relative z-10 opacity-80">Productos</span>
                        </Link>
                    </div>
                </div>

                <!-- Pedidos Recientes -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <Card no-padding class="lg:col-span-2">
                        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                            <h2 class="text-xl font-black text-text uppercase font-poppins">Últimos Pedidos</h2>
                            <Link :href="route('reports.index')" class="text-xs font-black text-primary hover:underline uppercase tracking-widest">Ver todos</Link>
                            </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead class="bg-gray-50/50">
                                    <tr>
                                        <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">ID</th>
                                        <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Cliente</th>
                                        <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Tipo</th>
                                        <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Estado</th>
                                        <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    <tr v-for="order in recentOrders" :key="order.id" class="hover:bg-orange-50/30 transition-colors group">
                                        <td class="px-6 py-4 font-mono font-bold text-gray-400 text-sm">#{{ order.id }}</td>
                                        <td class="px-6 py-4">
                                            <div class="font-black text-text">{{ order.client_name || 'Consumidor Final' }}</div>
                                            <div class="text-[10px] text-gray-400 font-bold">{{ new Date(order.created_at).toLocaleTimeString() }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-[10px] font-black uppercase px-2 py-1 rounded-md bg-gray-100 text-gray-600">
                                                {{ order.type === 'dine_in' ? 'Mesa ' + (order.mesa?.number || '') : 'Para Llevar' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span :class="['px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter', getOrderStatusColor(order.status)]">
                                                {{ getOrderStatusLabel(order.status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="font-black text-text">Bs. {{ toNum(order.total_amount).toFixed(2) }}</div>
                                        </td>
                                    </tr>
                                    <tr v-if="recentOrders.length === 0">
                                        <td colspan="5" class="px-6 py-12">
                                            <EmptyState icon="🍽️" title="Sin pedidos" message="No hay pedidos registrados hoy." />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </Card>

                    <Card class="flex flex-col">
                        <h2 class="text-xl font-black text-text uppercase font-poppins mb-6">Resumen de Caja</h2>
                        <div class="flex-1 flex flex-col justify-center items-center text-center space-y-4 p-8">
                            <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center text-4xl mb-2">
                                💰
                            </div>
                            <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Efectivo Disponible</div>
                            <div class="text-5xl font-black text-text">Bs. {{ toNum(cashTotal).toFixed(2) }}</div>
                            <div class="w-full pt-6">
                                <Link :href="route('cash-register.index')" class="block w-full bg-green-600 hover:bg-green-700 text-white font-black py-4 rounded-2xl transition-all shadow-lg shadow-green-100 active:scale-95 uppercase tracking-widest text-sm">
                                    Gestionar Caja
                                </Link>
                            </div>
                        </div>
                    </Card>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

