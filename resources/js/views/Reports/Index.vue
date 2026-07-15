<script setup>
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Bar, Doughnut, Line } from 'vue-chartjs';
import { ref, computed } from 'vue'
import EmptyState from '@/components/EmptyState.vue';
import Card from '@/components/Card.vue';
import { useToast } from '@/plugins/toast';

const toast = useToast();

const props = defineProps({
    stats: Object,
    filters: Object,
    orders: Array,
    comparison: Object,
});

const startDate = ref(props.filters.start_date);
const endDate = ref(props.filters.end_date);
const compareStart = ref(props.filters.compare_start || '');
const compareEnd = ref(props.filters.compare_end || '');
const showComparison = ref(!!props.filters.compare_start);

const pct = (current, prev) => {
    if (!prev || prev == 0) return 100;
    return (((current - prev) / prev) * 100).toFixed(1);
};

const presets = [
    { label: 'Hoy', days: 0 },
    { label: 'Esta Semana', days: 'week' },
    { label: 'Este Mes', days: 'month' },
    { label: 'Este Año', days: 'year' },
];

const fmt = (d) => d.toISOString().split('T')[0];

const setPreset = (preset) => {
    const now = new Date();
    if (preset.days === 0) {
        startDate.value = fmt(now);
        endDate.value = fmt(now);
    } else if (preset.days === 'week') {
        const monday = new Date(now);
        monday.setDate(now.getDate() - (now.getDay() || 7) + 1);
        startDate.value = fmt(monday);
        endDate.value = fmt(now);
    } else if (preset.days === 'month') {
        startDate.value = fmt(new Date(now.getFullYear(), now.getMonth(), 1));
        endDate.value = fmt(now);
    } else if (preset.days === 'year') {
        startDate.value = fmt(new Date(now.getFullYear(), 0, 1));
        endDate.value = fmt(now);
    }
    applyFilter();
};

const applyFilter = () => {
    if (new Date(endDate.value) < new Date(startDate.value)) {
        toast.error('La fecha final no puede ser anterior a la inicial');
        return;
    }
    router.get(route('reports.index'), {
        start_date: startDate.value,
        end_date: endDate.value,
        compare_start: compareStart.value || null,
        compare_end: compareEnd.value || null,
    }, { preserveState: true });
};

const dayLabels = props.stats.salesByDay?.map(d => d.date) || [];
const dayData = props.stats.salesByDay?.map(d => d.total) || [];

const lineChartData = {
    labels: dayLabels.length > 0 ? dayLabels : ['Sin datos'],
    datasets: [{
        label: 'Ventas por Día (Bs.)',
        backgroundColor: '#F37021',
        borderColor: '#F37021',
        data: dayData.length > 0 ? dayData : [0],
        tension: 0.4,
        fill: true
    }]
};

const productLabels = props.stats.topProducts?.map(p => p.product?.name || 'N/A') || [];
const productData = props.stats.topProducts?.map(p => p.total_qty) || [];

const barChartData = {
    labels: productLabels.length > 0 ? productLabels : ['Sin datos'],
    datasets: [{
        label: 'Unidades Vendidas',
        backgroundColor: '#4B230D',
        data: productData.length > 0 ? productData : [0]
    }]
};

const paymentLabels = props.stats.salesByPayment?.map(p => (p.payment_method || 'N/A').toUpperCase()) || [];
const paymentData = props.stats.salesByPayment?.map(p => p.total) || [];

const donutChartData = {
    labels: paymentLabels.length > 0 ? paymentLabels : ['Sin datos'],
    datasets: [{
        backgroundColor: ['#16a34a', '#2563eb', '#9333ea', '#ca8a04'],
        data: paymentData.length > 0 ? paymentData : [1]
    }]
};

const userLabels = props.stats.salesByUser?.map(u => u.user?.name || 'N/A') || [];
const userData = props.stats.salesByUser?.map(u => u.total) || [];

const userChartData = {
    labels: userLabels.length > 0 ? userLabels : ['Sin datos'],
    datasets: [{
        label: 'Ventas por Usuario (Bs.)',
        backgroundColor: ['#F37021', '#2563eb', '#16a34a', '#9333ea', '#ca8a04', '#e31e24'],
        data: userData.length > 0 ? userData : [1]
    }]
};

const hourLabels = props.stats.salesByHour?.map(h => `${h.hour}:00`) || [];
const hourData = props.stats.salesByHour?.map(h => h.total) || [];

const hourChartData = {
    labels: hourLabels.length > 0 ? hourLabels : ['Sin datos'],
    datasets: [{
        label: 'Ventas por Hora (Bs.)',
        backgroundColor: '#FFC20E',
        borderColor: '#FFC20E',
        data: hourData.length > 0 ? hourData : [0],
        borderRadius: 8,
    }]
};

const typeLabels = props.stats.salesByType?.map(t => {
    const map = { dine_in: 'Mesa', take_out: 'Para Llevar', delivery: 'Delivery' };
    return map[t.type] || t.type;
}) || [];
const typeData = props.stats.salesByType?.map(t => t.total) || [];

const typeChartData = {
    labels: typeLabels.length > 0 ? typeLabels : ['Sin datos'],
    datasets: [{
        backgroundColor: ['#F37021', '#2563eb', '#16a34a'],
        data: typeData.length > 0 ? typeData : [1]
    }]
};

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false
};

const cardClass = (idx) => {
    const colors = ['border-primary', 'border-blue-600', 'border-green-600', 'border-purple-600', 'border-red-600'];
    return colors[idx % colors.length];
};

const typeMap = { dine_in: 'Mesa', take_out: 'Para Llevar', delivery: 'Delivery' };
const statusClass = (status) => {
    const map = {
        completed: 'bg-green-100 text-green-800',
        ready: 'bg-blue-100 text-blue-800',
        cooking: 'bg-orange-100 text-orange-800',
        cancelled: 'bg-red-100 text-red-800',
        pending: 'bg-gray-100 text-gray-800'
    };
    return map[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="Reportes y Estadísticas" />
    <AuthenticatedLayout>
        <div class="pb-6">
            <div class="-mx-3 sm:-mx-6 -mt-3 sm:-mt-6 bg-gradient-to-br from-primary via-orange-500 to-orange-700 text-white p-8 sm:p-12 shadow-[0_20px_50px_rgba(243,112,33,0.2)] relative overflow-hidden mb-10">
                <div class="absolute top-0 right-0 w-96 h-96 bg-white/10 rounded-full -mr-48 -mt-48 blur-3xl animate-pulse"></div>
                <div class="absolute bottom-0 left-0 w-64 h-64 bg-black/10 rounded-full -ml-32 -mb-32 blur-2xl"></div>
                <div class="relative z-10">
                    <h1 class="text-4xl font-black italic uppercase tracking-tighter font-poppins">Reportes e Inteligencia</h1>
                    <p class="text-orange-100 mt-2 font-bold text-lg opacity-80">Analiza el pulso de Pollería Crispy en tiempo real</p>
                </div>
            </div>

            <Card class="mb-10 !p-0 overflow-visible">
                <div class="p-6 sm:p-8">
                    <div class="flex flex-col lg:flex-row gap-6 items-start lg:items-center">
                        <div class="flex flex-wrap gap-2 p-1.5 bg-gray-50 rounded-2xl border border-gray-100">
                            <button v-for="p in presets" :key="p.label" @click="setPreset(p)"
                                class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all hover:bg-white hover:text-primary hover:shadow-sm"
                                :class="startDate === fmt(new Date()) ? 'bg-primary text-white shadow-lg shadow-orange-500/20' : 'text-gray-500'">
                                {{ p.label }}
                            </button>
                        </div>
                        <div class="flex flex-wrap gap-4 items-end bg-gray-50 p-6 rounded-[2rem] border border-gray-100 shadow-inner">
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 block ml-1">Periodo Inicial</label>
                                <input type="date" v-model="startDate"
                                    class="border-2 border-white rounded-xl px-4 py-2.5 text-sm font-bold focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none transition-all shadow-sm" />
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 block ml-1">Periodo Final</label>
                                <input type="date" v-model="endDate"
                                    class="border-2 border-white rounded-xl px-4 py-2.5 text-sm font-bold focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none transition-all shadow-sm" />
                            </div>
                            <PrimaryButton @click="applyFilter" class="!py-3 !px-8">
                                📊 Filtrar
                            </PrimaryButton>
                        </div>
                        <div class="lg:ml-auto flex gap-2">
                            <button @click="showComparison = !showComparison"
                                class="p-3 rounded-2xl transition-all border-2 flex items-center justify-center group"
                                :class="showComparison ? 'bg-primary border-primary text-white shadow-lg' : 'bg-white border-gray-100 text-gray-400 hover:border-primary/30'">
                                <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </button>
                            <div class="flex gap-1.5 p-1.5 bg-gray-50 rounded-2xl border border-gray-100">
                                <a :href="route('reports.export.pdf', { start_date: startDate, end_date: endDate })" title="Exportar PDF"
                                    class="w-10 h-10 flex items-center justify-center bg-white text-danger border border-red-50 rounded-xl shadow-sm hover:bg-danger hover:text-white transition-all">
                                    PDF
                                </a>
                                <a :href="route('reports.export.excel', { start_date: startDate, end_date: endDate })" title="Exportar Excel"
                                    class="w-10 h-10 flex items-center justify-center bg-white text-green-600 border border-green-50 rounded-xl shadow-sm hover:bg-green-600 hover:text-white transition-all">
                                    XLS
                                </a>
                            </div>
                        </div>
                    </div>

                    <Transition name="fade">
                        <div v-if="showComparison" class="mt-6 p-6 bg-orange-50/50 rounded-[2rem] border-2 border-dashed border-primary/20 flex flex-wrap gap-4 items-end animate-fade-in-up">
                            <div>
                                <label class="text-[10px] font-black text-primary uppercase tracking-widest mb-1.5 block ml-1">Comparar con Desde</label>
                                <input type="date" v-model="compareStart"
                                    class="border-2 border-white rounded-xl px-4 py-2.5 text-sm font-bold focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none transition-all shadow-sm" />
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-primary uppercase tracking-widest mb-1.5 block ml-1">Hasta</label>
                                <input type="date" v-model="compareEnd"
                                    class="border-2 border-white rounded-xl px-4 py-2.5 text-sm font-bold focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none transition-all shadow-sm" />
                            </div>
                            <p class="text-[10px] text-orange-400 font-black uppercase mb-3 ml-2 italic">Se mostrarán porcentajes de crecimiento</p>
                        </div>
                    </Transition>
                </div>
            </Card>

            <div v-if="comparison && comparison.current" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 mb-10">
                <Card v-for="(kpi, idx) in [
                    { label: 'Ventas Totales', value: comparison.current.totalSales, prev: comparison.previous?.totalSales, color: 'primary', icon: '💰' },
                    { label: 'Pedidos Realizados', value: comparison.current.ordersCount, prev: comparison.previous?.ordersCount, color: 'blue-500', icon: '📋' },
                    { label: 'Ingreso Neto', value: comparison.current.netIncome, prev: comparison.previous?.netIncome, color: 'green-500', icon: '📈' },
                    { label: 'Ticket Promedio', value: comparison.current.avgTicket, prev: comparison.previous?.avgTicket, color: 'purple-500', icon: '🎫' },
                    { label: 'Cancelaciones', value: comparison.current.cancellations, prev: comparison.previous?.cancellations, color: 'danger', icon: '⚠️' },
                ]" :key="idx" class="border-l-8 group hover:-translate-y-2" :class="'border-' + kpi.color">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">{{ kpi.label }}</p>
                            <p class="text-3xl font-black text-text font-mono tracking-tighter">
                                {{ kpi.label.includes('Ventas') || kpi.label.includes('Ingreso') || kpi.label.includes('Ticket') ? 'Bs. ' : '' }}{{ kpi.value?.toFixed(2) ?? kpi.value ?? 0 }}
                            </p>
                        </div>
                        <span class="text-3xl grayscale group-hover:grayscale-0 transition-all opacity-20 group-hover:opacity-100">{{ kpi.icon }}</span>
                    </div>
                    <div v-if="kpi.prev !== undefined && kpi.prev !== null" class="flex items-center gap-2 mt-4 pt-4 border-t border-gray-50">
                        <div class="flex flex-col">
                            <span class="text-[9px] text-gray-400 font-black uppercase">Previo</span>
                            <span class="text-xs font-bold text-gray-500 font-mono">{{ kpi.label.includes('Ventas') ? 'Bs. ' : '' }}{{ kpi.prev?.toFixed(2) ?? kpi.prev ?? 0 }}</span>
                        </div>
                        <span class="ml-auto text-xs font-black px-2.5 py-1 rounded-xl shadow-sm" 
                            :class="(kpi.label === 'Cancelaciones' ? (kpi.value <= kpi.prev) : (kpi.value >= kpi.prev)) ? 'bg-green-500 text-white shadow-green-100' : 'bg-danger text-white shadow-red-100'">
                            {{ kpi.value >= kpi.prev ? '▲' : '▼' }} {{ Math.abs(pct(kpi.value, kpi.prev)) }}%
                        </span>
                    </div>
                </Card>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
                <Card v-for="(kpi, idx) in [
                    { label: 'Ventas Totales', value: stats.totalSales, color: 'border-primary', prefix: 'Bs.' },
                    { label: 'Pedidos', value: stats.ordersCount, color: 'border-blue-600' },
                    { label: 'Ingreso Neto', value: stats.netIncome, color: 'border-green-600', prefix: 'Bs.' },
                    { label: 'Ticket Promedio', value: stats.avgTicket, color: 'border-purple-600', prefix: 'Bs.' },
                    { label: 'Cancelaciones', value: stats.cancellations, color: 'border-red-600' },
                ]" :key="'kpi-' + idx" class="border-l-8" :class="kpi.color">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ kpi.label }}</p>
                    <p class="text-2xl font-black text-text mt-1 font-mono">{{ kpi.prefix || '' }} {{ kpi.value?.toFixed(2) ?? kpi.value ?? 0 }}</p>
                </Card>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <Card padding="p-6">
                    <h3 class="text-sm font-black text-text uppercase tracking-wider mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                        </svg>
                        Ventas por Día
                    </h3>
                    <div class="h-64">
                        <Line v-if="dayData.length > 0" :data="lineChartData" :options="chartOptions" />
                        <EmptyState v-else icon="📈" title="Sin datos" message="No hay ventas en el rango seleccionado." />
                    </div>
                </Card>
                <Card padding="p-6">
                    <h3 class="text-sm font-black text-text uppercase tracking-wider mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Distribución de Pagos
                    </h3>
                    <div class="h-64 flex justify-center items-center">
                        <Doughnut v-if="paymentData.length > 0" :data="donutChartData" :options="chartOptions" />
                        <EmptyState v-else icon="💰" title="Sin datos" message="No hay pagos registrados." />
                    </div>
                </Card>
            </div>

            <div class="mb-8">
                <Card padding="p-6">
                    <h3 class="text-sm font-black text-text uppercase tracking-wider mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Productos Más Vendidos
                    </h3>
                    <div class="h-80">
                        <Bar v-if="productData.length > 0" :data="barChartData" :options="chartOptions" />
                        <EmptyState v-else icon="📦" title="Sin datos" message="No hay productos vendidos en el rango." />
                    </div>
                </Card>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <Card padding="p-6">
                    <h3 class="text-sm font-black text-text uppercase tracking-wider mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Ventas por Hora
                    </h3>
                    <div class="h-64">
                        <Bar v-if="hourData.length > 0" :data="hourChartData" :options="chartOptions" />
                        <EmptyState v-else icon="🕐" title="Sin datos" message="No hay ventas en el rango." />
                    </div>
                </Card>
                <Card padding="p-6">
                    <h3 class="text-sm font-black text-text uppercase tracking-wider mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        Ventas por Tipo
                    </h3>
                    <div class="h-64 flex justify-center items-center">
                        <Doughnut v-if="typeData.length > 0" :data="typeChartData" :options="chartOptions" />
                        <EmptyState v-else icon="📋" title="Sin datos" message="No hay ventas por tipo." />
                    </div>
                </Card>
            </div>

            <Card v-if="props.stats.salesByUser?.length > 0" padding="p-6" class="mb-8">
                <h3 class="text-sm font-black text-text uppercase tracking-wider mb-6 flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                    </svg>
                    Ventas por Usuario
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div v-for="sale in props.stats.salesByUser" :key="sale.user_id"
                        class="p-4 rounded-xl bg-gradient-to-br from-orange-50 to-yellow-50 border border-orange-100 hover:shadow-lg hover:shadow-orange-500/5 transition-all">
                        <p class="font-bold text-sm text-text truncate">{{ sale.user?.name || 'N/A' }}</p>
                        <p class="text-2xl font-black text-primary font-mono mt-1">Bs. {{ parseFloat(sale.total).toFixed(2) }}</p>
                        <p class="text-[10px] text-gray-400 font-black mt-1">{{ sale.count }} pedidos</p>
                    </div>
                </div>
            </Card>

            <Card noPadding class="overflow-hidden">
                <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                    <h3 class="text-sm font-black text-text uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Detalle de Pedidos
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Fecha</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Pedido</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Tipo</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Productos</th>
                                <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Total</th>
                                <th class="px-6 py-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Estado</th>
                                <th class="px-6 py-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="orders.length === 0">
                                <td colspan="7" class="px-6 py-12">
                                    <EmptyState icon="📊" title="Sin resultados" message="No hay pedidos en el rango seleccionado." />
                                </td>
                            </tr>
                            <tr v-for="order in orders" :key="order.id" class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-sm font-bold text-gray-600 whitespace-nowrap">
                                    {{ new Date(order.created_at).toLocaleString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-bold text-text">{{ order.order_number }}</p>
                                    <p class="text-[10px] text-gray-400 font-bold">{{ order.user?.name }}</p>
                                </td>
                                <td class="px-6 py-4 text-sm font-bold text-gray-600">{{ typeMap[order.type] || order.type }}</td>
                                <td class="px-6 py-4">
                                    <ul class="space-y-0.5">
                                        <li v-for="item in order.items" :key="item.id" class="text-xs font-medium text-gray-600">
                                            {{ item.quantity }}x {{ item.product?.name }}
                                            <span v-if="item.variant?.name" class="text-gray-400">({{ item.variant.name }})</span>
                                        </li>
                                    </ul>
                                </td>
                                <td class="px-6 py-4 text-sm font-black text-text text-right font-mono">Bs. {{ order.total_amount }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase" :class="statusClass(order.status)">
                                        {{ order.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex gap-1.5 justify-center">
                                        <a :href="route('orders.print', order.id)" target="_blank"
                                            class="text-primary hover:text-white font-bold text-[10px] uppercase border-2 border-primary px-3 py-1.5 rounded-lg hover:bg-primary transition-all">
                                            Ticket
                                        </a>
                                        <a :href="route('orders.reprint', order.id)" target="_blank"
                                            class="text-green-600 hover:text-white font-bold text-[10px] uppercase border-2 border-green-600 px-3 py-1.5 rounded-lg hover:bg-green-600 transition-all">
                                            Reimprimir
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
