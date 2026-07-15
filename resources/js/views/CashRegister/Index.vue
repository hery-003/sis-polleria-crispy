<script setup>
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import EmptyState from '@/components/EmptyState.vue';

const props = defineProps({
    activeRegister: Object,
    recentMovements: Array,
    closeSummary: Object,
    expectedCash: Object,
});

const currentBalance = computed(() => {
    if (!props.activeRegister) return 0;
    let balance = parseFloat(props.activeRegister.opening_balance) || 0;
    props.recentMovements?.forEach(m => {
        if (m.type === 'in') {
            balance += parseFloat(m.amount);
        } else {
            balance -= parseFloat(m.amount);
        }
    });
    return balance.toFixed(2);
});

const openForm = useForm({
    opening_balance: ''
});

const closeForm = useForm({
    closing_balance: '',
    notes: '',
    cash_register_id: props.activeRegister?.id || ''
});

const movementForm = useForm({
    type: 'in',
    amount: '',
    description: ''
});

const showCloseModal = ref(false);

const computedDifference = computed(() => {
    if (!closeForm.closing_balance || !props.expectedCash?.expected_cash) return null;
    const closing = parseFloat(closeForm.closing_balance);
    const expected = parseFloat(props.expectedCash.expected_cash);
    return closing - expected;
});

const differenceStatus = computed(() => {
    if (!computedDifference.value) return null;
    if (Math.abs(computedDifference.value) < 0.01) return 'ok';
    return computedDifference.value > 0 ? 'surplus' : 'shortage';
});

const submitOpen = () => {
    openForm.post(route('cash-register.open'), {
        onSuccess: () => openForm.reset()
    });
};

const submitClose = () => {
    closeForm.cash_register_id = props.activeRegister.id;
    closeForm.post(route('cash-register.close'), {
        onSuccess: () => {
            closeForm.reset();
            showCloseModal.value = false;
        }
    });
};

const submitMovement = () => {
    movementForm.post(route('cash-register.movement'), {
        onSuccess: () => movementForm.reset()
    });
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString();
};
</script>

<template>
    <Head title="Control de Caja" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-black text-gray-800 tracking-tight uppercase">
                💰 Control de Caja
            </h2>
        </template>

        <div class="space-y-6">
            <!-- Si la caja está cerrada -->
            <div v-if="!activeRegister" class="bg-white rounded-3xl shadow-xl p-10 text-center max-w-lg mx-auto border-b-8 border-red-500">
                <span class="text-7xl mb-6 block text-gray-200">🔒</span>
                <h3 class="text-2xl font-black text-gray-800 mb-2 uppercase">La caja está cerrada</h3>
                <p class="text-gray-500 mb-8 font-medium">Debe abrir la caja con un saldo inicial para empezar a vender.</p>
                
                <form @submit.prevent="submitOpen" class="space-y-4">
                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2 text-left">Saldo Inicial (Bs.)</label>
                        <input
                            v-model="openForm.opening_balance"
                            type="number"
                            step="0.01"
                            class="w-full px-6 py-4 bg-gray-50 border-gray-100 rounded-2xl text-2xl font-black text-gray-800 focus:ring-4 focus:ring-orange-100 transition-all"
                            placeholder="0.00"
                            required
                        />
                    </div>
                    <button
                        type="submit"
                        :disabled="openForm.processing"
                        class="w-full py-4 bg-orange-600 hover:bg-orange-700 text-white rounded-2xl font-black uppercase text-sm tracking-widest shadow-lg shadow-orange-100 transition-all active:scale-95 disabled:bg-gray-300"
                    >
                        Abrir Caja Ahora
                    </button>
                </form>
            </div>

            <!-- Si la caja está abierta -->
            <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Resumen y Cierre -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white rounded-3xl shadow-xl p-6 border-b-8 border-green-500">
                        <div class="flex items-center justify-between mb-6">
                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-[10px] font-black uppercase border border-green-200">CAJA ABIERTA</span>
                            <span class="text-xs text-gray-400 font-bold uppercase">{{ formatDate(activeRegister.opened_at) }}</span>
                        </div>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center p-4 bg-gray-50 rounded-2xl">
                                <span class="text-xs font-bold text-gray-500 uppercase">Saldo Inicial</span>
                                <span class="text-xl font-black text-gray-800">Bs. {{ activeRegister.opening_balance }}</span>
                            </div>
                            <div class="flex justify-between items-center p-4 bg-primary/10 rounded-2xl border border-primary/20">
                                <span class="text-xs font-bold text-gray-500 uppercase">Saldo Actual</span>
                                <span class="text-2xl font-black text-primary">Bs. {{ currentBalance }}</span>
                            </div>
                        </div>

                        <div class="mt-8 pt-6 border-t border-dashed">
                            <h4 class="text-sm font-black text-gray-800 mb-4 uppercase">Cerrar Caja</h4>
                            <button
                                @click="showCloseModal = true"
                                class="w-full py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl font-black uppercase text-xs tracking-widest transition-all"
                            >
                                Realizar Arqueo y Cerrar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Movimientos de Caja -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Formulario de Movimiento -->
                    <div class="bg-white rounded-3xl shadow-xl p-6">
                        <h3 class="text-lg font-black text-gray-800 mb-6 uppercase">Registrar Movimiento Extra</h3>
                        <form @submit.prevent="submitMovement" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                            <div class="md:col-span-1">
                                <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Tipo</label>
                                <select v-model="movementForm.type" class="w-full px-4 py-3 bg-gray-50 border-gray-100 rounded-xl font-bold text-gray-700">
                                    <option value="in">Ingreso (+)</option>
                                    <option value="out">Egreso (-)</option>
                                </select>
                            </div>
                            <div class="md:col-span-1">
                                <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Monto</label>
                                <input v-model="movementForm.amount" type="number" step="0.01" class="w-full px-4 py-3 bg-gray-50 border-gray-100 rounded-xl font-bold" placeholder="0.00" required />
                            </div>
                            <div class="md:col-span-1">
                                <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Motivo / Descripción</label>
                                <input v-model="movementForm.description" type="text" class="w-full px-4 py-3 bg-gray-50 border-gray-100 rounded-xl font-bold" placeholder="Ej: Compra de hielo" required />
                            </div>
                            <button
                                type="submit"
                                :disabled="movementForm.processing"
                                class="w-full py-3 bg-gray-800 hover:bg-gray-900 text-white rounded-xl font-black uppercase text-xs tracking-widest"
                            >
                                Registrar
                            </button>
                        </form>
                    </div>

                    <!-- Tabla de Movimientos -->
                    <div class="bg-white rounded-3xl shadow-xl overflow-hidden">
                        <div class="p-6 border-b flex justify-between items-center">
                            <h3 class="text-lg font-black text-gray-800 uppercase">Movimientos de Hoy</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-gray-50 border-b">
                                        <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase">Hora</th>
                                        <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase">Descripción</th>
                                        <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase text-right">Monto</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="recentMovements.length === 0">
                                        <td colspan="3" class="px-6 py-10">
                                            <EmptyState icon="💰" title="Sin movimientos" message="No hay movimientos registrados en esta caja." />
                                        </td>
                                    </tr>
                                    <tr v-for="movement in recentMovements" :key="movement.id" class="border-b hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 text-xs font-bold text-gray-500">{{ formatDate(movement.created_at) }}</td>
                                        <td class="px-6 py-4">
                                            <p class="text-sm font-black text-gray-800 uppercase">{{ movement.description }}</p>
                                            <p class="text-[10px] text-gray-400 font-bold">Por: {{ movement.user.name }}</p>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <span :class="['text-sm font-black', movement.type === 'in' ? 'text-green-600' : 'text-red-600']">
                                                {{ movement.type === 'in' ? '+' : '-' }} Bs. {{ movement.amount }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Resumen Histórico de Cierres -->
                <div v-if="closeSummary && closeSummary.length > 0" class="bg-white rounded-3xl shadow-xl overflow-hidden mt-6">
                    <div class="p-6 border-b">
                        <h3 class="text-lg font-black text-gray-800 uppercase">Histórico de Cierres</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b">
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase">Fecha</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase text-right">Saldo Inicial</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase text-right">Saldo Final</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase text-right">Diferencia</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="summary in closeSummary" :key="summary.id" class="border-b hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ formatDate(summary.closed_at) }}</td>
                                    <td class="px-6 py-4 text-sm font-bold text-gray-800 text-right">Bs. {{ summary.opening_balance }}</td>
                                    <td class="px-6 py-4 text-sm font-bold text-gray-800 text-right">Bs. {{ summary.closing_balance }}</td>
                                    <td class="px-6 py-4 text-sm font-bold text-right"
                                        :class="summary.difference >= 0 ? 'text-green-600' : 'text-red-600'">
                                        {{ summary.difference >= 0 ? '+' : '' }}Bs. {{ summary.difference?.toFixed(2) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Modal de Cierre de Caja -->
        <div v-if="showCloseModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-8 space-y-6">
                <div class="text-center">
                    <span class="text-5xl block mb-4">💵</span>
                    <h3 class="text-xl font-black text-gray-800 uppercase">Cierre de Caja</h3>
                    <p class="text-sm text-gray-500 mt-1">Ingrese el conteo real de efectivo</p>
                </div>

                <form @submit.prevent="submitClose" class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Saldo Final en Efectivo (Bs.)</label>
                        <input
                            v-model="closeForm.closing_balance"
                            type="number"
                            step="0.01"
                            class="w-full px-6 py-4 bg-gray-50 border-2 border-gray-200 rounded-2xl text-2xl font-black text-gray-800 focus:ring-4 focus:ring-orange-100 focus:border-primary transition-all"
                            placeholder="0.00"
                            required
                        />
                    </div>

                    <!-- Diferencia calculada -->
                    <div v-if="closeForm.closing_balance" class="p-4 rounded-2xl"
                         :class="{
                             'bg-green-50 border border-green-200': differenceStatus === 'ok',
                             'bg-yellow-50 border border-yellow-200': differenceStatus === 'surplus',
                             'bg-red-50 border border-red-200': differenceStatus === 'shortage'
                         }">
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-bold uppercase"
                                  :class="{
                                      'text-green-700': differenceStatus === 'ok',
                                      'text-yellow-700': differenceStatus === 'surplus',
                                      'text-red-700': differenceStatus === 'shortage'
                                  }">
                                Diferencia
                            </span>
                            <span class="text-lg font-black"
                                  :class="{
                                      'text-green-700': differenceStatus === 'ok',
                                      'text-yellow-700': differenceStatus === 'surplus',
                                      'text-red-700': differenceStatus === 'shortage'
                                  }">
                                {{ computedDifference >= 0 ? '+' : '' }}Bs. {{ computedDifference?.toFixed(2) }}
                            </span>
                        </div>
                        <p class="text-xs mt-1"
                           :class="{
                               'text-green-600': differenceStatus === 'ok',
                               'text-yellow-600': differenceStatus === 'surplus',
                               'text-red-600': differenceStatus === 'shortage'
                           }">
                            {{ differenceStatus === 'ok' ? '✓ Cuadre exacto' : differenceStatus === 'surplus' ? '⚠ Sobrante' : '⚠ Faltante' }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Observaciones (opcional)</label>
                        <textarea
                            v-model="closeForm.notes"
                            rows="2"
                            class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl font-medium text-gray-700 focus:border-primary transition-all"
                            placeholder="Notas sobre el cierre..."
                        ></textarea>
                    </div>

                    <div class="flex gap-3">
                        <button
                            type="button"
                            @click="showCloseModal = false"
                            class="flex-1 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-xl font-black uppercase text-xs tracking-widest transition-all"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            :disabled="closeForm.processing"
                            class="flex-1 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl font-black uppercase text-xs tracking-widest transition-all disabled:bg-gray-300"
                        >
                            Confirmar Cierre
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
