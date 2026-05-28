<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Bar, Doughnut, Line } from 'vue-chartjs';
import { ref, computed } from 'vue'
import EmptyState from '@/Components/EmptyState.vue';
import { useToast } from '@/Plugins/toast';

const toast = useToast();

const props = defineProps({
    stats: Object,
    filters: Object,
    orders: Array,
});

const startDate = ref(props.filters.start_date);
const endDate = ref(props.filters.end_date);

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
        end_date: endDate.value
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
</script>

<template>
    <Head title="Reportes y Estadísticas" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-black text-text tracking-tight uppercase">
                📊 Reportes y Estadísticas
            </h2>
        </template>

        <div class="space-y-6">
             <!-- Filtros de Fecha -->
              <div class="bg-white p-4 rounded-2xl shadow-xl flex gap-4 items-end flex-wrap">
                  <div class="flex gap-1.5 items-end">
                      <button v-for="p in presets" :key="p.label" @click="setPreset(p)"
                          class="px-3.5 py-2 rounded-lg text-xs font-black uppercase transition-all bg-gray-100 text-gray-600 hover:bg-gray-200 hover:text-gray-800">
                          {{ p.label }}
                      </button>
                  </div>
                  <div>
                      <label class="text-xs font-bold text-gray-500 uppercase">Desde</label>
                      <input type="date" v-model="startDate" class="border rounded-lg px-3 py-2" />
                  </div>
                  <div>
                      <label class="text-xs font-bold text-gray-500 uppercase">Hasta</label>
                      <input type="date" v-model="endDate" class="border rounded-lg px-3 py-2" />
                  </div>
                  <button @click="applyFilter" 
                      class="bg-primary text-white px-6 py-2 rounded-lg font-bold hover:bg-orange-700 transition-colors">
                      Filtrar
                  </button>
                 <div class="ml-auto flex gap-2">
                     <a :href="route('reports.export.pdf', { start_date: startDate, end_date: endDate })" 
                        class="px-4 py-2 bg-danger hover:bg-red-700 text-white rounded-lg font-bold text-xs uppercase transition-all flex items-center gap-2">
                         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                         </svg>
                         PDF
                     </a>
                     <a :href="route('reports.export.csv', { start_date: startDate, end_date: endDate })" 
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-bold text-xs uppercase transition-all flex items-center gap-2">
                         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                         </svg>
                         CSV
                     </a>
                 </div>
             </div>

            <!-- KPIs rápidos -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
                <div class="bg-white p-6 rounded-3xl shadow-xl border-b-8 border-primary">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Ventas Totales</p>
                    <h3 class="text-3xl font-black text-text">Bs. {{ stats.totalSales?.toFixed(2) || '0.00' }}</h3>
                </div>
                <div class="bg-white p-6 rounded-3xl shadow-xl border-b-8 border-blue-600">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Pedidos</p>
                    <h3 class="text-3xl font-black text-text">{{ stats.ordersCount || 0 }}</h3>
                </div>
                <div class="bg-white p-6 rounded-3xl shadow-xl border-b-8 border-success">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Ingreso Neto</p>
                    <h3 class="text-3xl font-black text-success">Bs. {{ stats.netIncome?.toFixed(2) || '0.00' }}</h3>
                </div>
                <div class="bg-white p-6 rounded-3xl shadow-xl border-b-8 border-purple-600">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Ticket Promedio</p>
                    <h3 class="text-3xl font-black text-text">Bs. {{ stats.avgTicket?.toFixed(2) || '0.00' }}</h3>
                </div>
                <div class="bg-white p-6 rounded-3xl shadow-xl border-b-8 border-danger">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Cancelaciones</p>
                    <h3 class="text-3xl font-black text-danger">{{ stats.cancellations || 0 }}</h3>
                </div>
            </div>


        <!-- Gráficos Fila 1 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Ventas por Día -->
                <div class="bg-white p-6 rounded-3xl shadow-xl">
                    <h3 class="text-lg font-black text-text mb-6 uppercase">Ventas por Día</h3>
                    <div class="h-64">
                        <Line :data="lineChartData" :options="chartOptions" />
                    </div>
                </div>

                <!-- Métodos de Pago -->
                <div class="bg-white p-6 rounded-3xl shadow-xl">
                    <h3 class="text-lg font-black text-text mb-6 uppercase">Distribución de Pagos</h3>
                    <div class="h-64 flex justify-center">
                        <Doughnut :data="donutChartData" :options="chartOptions" />
                    </div>
                </div>

                <!-- Top Productos -->
                <div class="lg:col-span-2 bg-white p-6 rounded-3xl shadow-xl">
                    <h3 class="text-lg font-black text-text mb-6 uppercase">Productos Más Vendidos</h3>
                    <div class="h-80">
                        <Bar :data="barChartData" :options="chartOptions" />
                    </div>
                </div>
            </div>

            <!-- Gráficos Fila 2 -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Ventas por Hora -->
                <div class="bg-white p-6 rounded-3xl shadow-xl">
                    <h3 class="text-lg font-black text-text mb-6 uppercase">Ventas por Hora</h3>
                    <div class="h-64">
                        <Bar :data="hourChartData" :options="chartOptions" />
                    </div>
                </div>

                <!-- Ventas por Tipo -->
                <div class="bg-white p-6 rounded-3xl shadow-xl">
                    <h3 class="text-lg font-black text-text mb-6 uppercase">Ventas por Tipo</h3>
                    <div class="h-64 flex justify-center">
                        <Doughnut :data="typeChartData" :options="chartOptions" />
                    </div>
                </div>
            </div>

            <!-- Tabla Detallada de Ventas -->
            <div class="bg-white rounded-3xl shadow-xl overflow-hidden">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-black text-text uppercase">Detalle de Pedidos</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b">
                                <th class="px-6 py-4 text-xs font-black text-gray-500 uppercase">Fecha</th>
                                <th class="px-6 py-4 text-xs font-black text-gray-500 uppercase">Pedido</th>
                                <th class="px-6 py-4 text-xs font-black text-gray-500 uppercase">Tipo</th>
                                <th class="px-6 py-4 text-xs font-black text-gray-500 uppercase">Productos</th>
                                <th class="px-6 py-4 text-xs font-black text-gray-500 uppercase text-right">Total</th>
                                <th class="px-6 py-4 text-xs font-black text-gray-500 uppercase text-center">Estado</th>
                                <th class="px-6 py-4 text-xs font-black text-gray-500 uppercase text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="orders.length === 0">
                                <td colspan="7" class="px-6 py-12">
                                    <EmptyState icon="📊" title="Sin resultados" message="No hay pedidos en el rango seleccionado." />
                                </td>
                            </tr>
                            <tr v-for="order in orders" :key="order.id" class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap">
                                    {{ new Date(order.created_at).toLocaleString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-bold text-gray-800">{{ order.order_number }}</span>
                                    <div class="text-xs text-gray-500">{{ order.user?.name }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-700">
                                    {{ order.type === 'dine_in' ? 'Mesa' : order.type === 'take_out' ? 'Para Llevar' : 'Delivery' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <ul class="list-disc list-inside">
                                        <li v-for="item in order.items" :key="item.id" class="text-xs">
                                            {{ item.quantity }}x {{ item.product?.name }} ({{ item.variant?.name }})
                                        </li>
                                    </ul>
                                </td>
                                <td class="px-6 py-4 text-sm font-bold text-gray-900 text-right">
                                    Bs. {{ order.total_amount }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase"
                                          :class="{
                                              'bg-green-100 text-green-800': order.status === 'completed',
                                              'bg-secondary/20 text-secondary': order.status === 'ready',
                                              'bg-orange-100 text-orange-800': order.status === 'cooking',
                                              'bg-red-100 text-red-800': order.status === 'cancelled',
                                              'bg-gray-100 text-gray-800': order.status === 'pending'
                                          }">
                                        {{ order.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a :href="route('orders.print', order.id)" target="_blank"
                                       class="text-primary hover:text-orange-700 font-bold text-xs uppercase border border-primary px-3 py-1 rounded hover:bg-orange-50 transition-all">
                                        Imprimir
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
