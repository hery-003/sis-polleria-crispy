<script setup>
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Card from '@/components/Card.vue';
import EmptyState from '@/components/EmptyState.vue';

const props = defineProps({
    variants: Array,
});

const edits = ref({});
const saving = ref(false);

const lowStockItems = computed(() =>
    props.variants.filter(v => v.stock !== null && v.stock <= (v.threshold || 5) && v.stock > 0)
);

const outOfStock = computed(() =>
    props.variants.filter(v => v.stock !== null && v.stock === 0)
);

const goodStock = computed(() =>
    props.variants.filter(v => v.stock === null || v.stock > (v.threshold || 5))
);

const itemsToOrder = computed(() =>
    props.variants.filter(v => v.stock !== null && v.stock <= (v.threshold || 5))
);

const setStock = (id, val) => {
    edits.value[id] = Math.max(0, parseInt(val) || 0);
};

const saveAll = () => {
    const updates = Object.entries(edits.value).map(([id, stock]) => ({ id: parseInt(id), stock }));
    if (updates.length === 0) return;

    saving.value = true;
    router.post(route('products.stock.update'), { updates }, {
        onSuccess: () => { edits.value = {}; },
        onFinish: () => { saving.value = false; },
    });
};

const hasEdits = computed(() => Object.keys(edits.value).length > 0);

const stockPercent = (stock, threshold) => {
    if (stock === null || stock === undefined) return 100;
    const max = Math.max(stock, (threshold || 5) * 3);
    return Math.min(100, (stock / max) * 100);
};

const stockColor = (stock, threshold) => {
    if (stock === null || stock === undefined) return 'bg-green-500';
    const t = threshold || 5;
    if (stock === 0) return 'bg-red-500';
    if (stock <= t) return 'bg-orange-500';
    return 'bg-green-500';
};
</script>

<template>
    <Head title="Stock Masivo" />
    <AuthenticatedLayout>
        <div class="pb-24">
            <div class="-mx-3 sm:-mx-6 -mt-3 sm:-mt-6 bg-gradient-to-r from-primary via-orange-500 to-orange-600 text-white p-6 sm:p-8 shadow-2xl relative overflow-hidden mb-8">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-32 -mt-32 blur-3xl"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-black/10 rounded-full -ml-24 -mb-24 blur-2xl"></div>
                <div class="relative z-10">
                    <h1 class="text-3xl font-black italic uppercase tracking-tight">Gestión de Stock</h1>
                    <p class="text-orange-100 mt-1 font-bold text-sm">Controla el inventario de todas las variantes</p>
                </div>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <Card hover class="border-l-8 border-green-500">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Stock OK</p>
                    <p class="text-3xl font-black text-green-600 mt-1 font-mono">{{ goodStock.length }}</p>
                </Card>
                <Card hover class="border-l-8 border-orange-500">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Stock Bajo</p>
                    <p class="text-3xl font-black text-orange-600 mt-1 font-mono">{{ lowStockItems.length }}</p>
                </Card>
                <Card hover class="border-l-8 border-red-500">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Sin Stock</p>
                    <p class="text-3xl font-black text-red-600 mt-1 font-mono">{{ outOfStock.length }}</p>
                </Card>
                <Card hover class="border-l-8 border-primary">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Variantes</p>
                    <p class="text-3xl font-black text-text mt-1 font-mono">{{ props.variants.length }}</p>
                </Card>
            </div>

            <div class="bg-white rounded-3xl shadow-xl overflow-hidden border-l-8 border-orange-500 mb-8">
                <div class="p-6 bg-gradient-to-r from-orange-50 to-white">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-lg font-black text-text uppercase flex items-center gap-2">
                                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Sugerencia de Compra
                            </h3>
                            <p class="text-sm text-gray-500 mt-1">Variantes con stock por debajo del umbral mínimo</p>
                        </div>
                        <span class="px-3 py-1.5 bg-orange-100 text-orange-800 rounded-lg text-xs font-black">
                            {{ itemsToOrder.length }} para pedir
                        </span>
                    </div>
                </div>

                <EmptyState v-if="itemsToOrder.length === 0"
                    icon="✅" title="Stock suficiente"
                    message="Todos los productos tienen stock por encima del umbral mínimo." />

                <div v-else class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Producto</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Variante</th>
                                <th class="px-6 py-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Stock</th>
                                <th class="px-6 py-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Umbral</th>
                                <th class="px-6 py-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Sugerido</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="v in itemsToOrder" :key="v.id" class="hover:bg-orange-50 transition-colors">
                                <td class="px-6 py-4">
                                    <p class="font-bold text-text text-sm">{{ v.product }}</p>
                                    <p class="text-[10px] text-gray-400 font-bold">{{ v.category }}</p>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-600">{{ v.variant }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black"
                                        :class="v.stock === 0 ? 'bg-red-100 text-red-800' : 'bg-orange-100 text-orange-800'">
                                        <span class="w-1.5 h-1.5 rounded-full" :class="v.stock === 0 ? 'bg-red-500' : 'bg-orange-500'"></span>
                                        {{ v.stock }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center text-sm font-bold text-gray-500">{{ v.threshold || 5 }}</td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button @click="setStock(v.id, (edits[v.id] ?? v.stock) + 5)"
                                            class="w-9 h-9 flex items-center justify-center bg-primary/10 text-primary rounded-lg font-black text-sm hover:bg-primary hover:text-white transition-all active:scale-90">
                                            +5
                                        </button>
                                        <button @click="setStock(v.id, (edits[v.id] ?? v.stock) + 10)"
                                            class="w-9 h-9 flex items-center justify-center bg-primary/10 text-primary rounded-lg font-black text-sm hover:bg-primary hover:text-white transition-all active:scale-90">
                                            +10
                                        </button>
                                        <button @click="setStock(v.id, (edits[v.id] ?? v.stock) + 20)"
                                            class="w-9 h-9 flex items-center justify-center bg-primary/10 text-primary rounded-lg font-black text-sm hover:bg-primary hover:text-white transition-all active:scale-90">
                                            +20
                                        </button>
                                        <input type="number"
                                            :value="edits[v.id] ?? v.stock"
                                            @input="setStock(v.id, $event.target.value)"
                                            class="w-16 text-center py-1.5 border-2 rounded-lg font-bold text-sm transition-all"
                                            :class="edits[v.id] !== undefined ? 'border-primary bg-orange-50' : 'border-gray-200'" />
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-xl overflow-hidden border-l-8 border-blue-500">
                <div class="p-6 bg-gradient-to-r from-blue-50 to-white">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div>
                            <h3 class="text-lg font-black text-text uppercase flex items-center gap-2">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                Todos los Stocks
                            </h3>
                            <p class="text-sm text-gray-500 mt-1">Edita los valores directamente</p>
                        </div>
                        <div class="flex gap-3 text-xs font-black">
                            <span class="flex items-center gap-1.5 text-green-700 bg-green-50 px-3 py-1.5 rounded-lg">
                                <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                {{ goodStock.length }} ok
                            </span>
                            <span class="flex items-center gap-1.5 text-orange-700 bg-orange-50 px-3 py-1.5 rounded-lg">
                                <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                                {{ lowStockItems.length }} bajo
                            </span>
                            <span class="flex items-center gap-1.5 text-red-700 bg-red-50 px-3 py-1.5 rounded-lg">
                                <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                {{ outOfStock.length }} sin stock
                            </span>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto max-h-[600px] overflow-y-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b sticky top-0 z-10">
                            <tr>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Producto</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Variante</th>
                                <th class="px-6 py-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Stock</th>
                                <th class="px-6 py-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Umbral</th>
                                <th class="px-6 py-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest" style="width: 180px;">Nuevo Stock</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="v in props.variants" :key="v.id" :class="[
                                'transition-colors',
                                v.stock === 0 ? 'bg-red-50/50' :
                                v.stock !== null && v.stock <= (v.threshold || 5) ? 'bg-orange-50/50' : 'hover:bg-gray-50'
                            ]">
                                <td class="px-6 py-4">
                                    <p class="font-bold text-text text-sm">{{ v.product }}</p>
                                    <p class="text-[10px] text-gray-400 font-bold">{{ v.category }}</p>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-600">{{ v.variant }}</td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex flex-col items-center gap-1.5">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black"
                                            :class="v.stock === null || v.stock === undefined ? 'bg-gray-100 text-gray-600' :
                                                v.stock === 0 ? 'bg-red-100 text-red-800' :
                                                v.stock <= (v.threshold || 5) ? 'bg-orange-100 text-orange-800' :
                                                'bg-green-100 text-green-800'">
                                            <span class="w-1.5 h-1.5 rounded-full"
                                                :class="v.stock === null || v.stock === undefined ? 'bg-gray-500' :
                                                    v.stock === 0 ? 'bg-red-500' :
                                                    v.stock <= (v.threshold || 5) ? 'bg-orange-500' : 'bg-green-500'">
                                            </span>
                                            {{ v.stock !== null && v.stock !== undefined ? v.stock : '∞' }}
                                        </span>
                                        <div v-if="v.stock !== null && v.stock !== undefined" class="w-20 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full transition-all duration-300" :class="stockColor(v.stock, v.threshold)"
                                                :style="{ width: stockPercent(v.stock, v.threshold) + '%' }"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center text-sm font-bold text-gray-500">{{ v.threshold || 5 }}</td>
                                <td class="px-6 py-4 text-center">
                                    <input type="number"
                                        :value="edits[v.id] ?? (v.stock ?? 0)"
                                        @input="setStock(v.id, $event.target.value)"
                                        class="w-24 text-center py-2 border-2 rounded-lg font-bold text-sm transition-all"
                                        :class="edits[v.id] !== undefined ? 'border-primary bg-orange-50 ring-4 ring-primary/10' : 'border-gray-200'" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <Transition name="fade">
            <div v-if="hasEdits" class="fixed bottom-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-md border-t border-gray-200 shadow-2xl p-4">
                <div class="max-w-7xl mx-auto flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
                        <span class="text-sm font-bold text-text">
                            {{ Object.keys(edits).length }} cambio(s) pendiente(s)
                        </span>
                    </div>
                    <div class="flex gap-3">
                        <button @click="edits = {}"
                            class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-bold text-sm transition-all active:scale-95">
                            Cancelar
                        </button>
                        <button @click="saveAll" :disabled="saving"
                            class="px-8 py-3 bg-primary hover:bg-orange-600 disabled:bg-gray-300 text-white rounded-xl font-bold text-sm transition-all active:scale-95 shadow-lg shadow-orange-200 flex items-center gap-2">
                            <svg v-if="saving" class="animate-spin h-4 w-4" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                            </svg>
                            {{ saving ? 'Guardando...' : `Guardar ${Object.keys(edits).length} cambio(s)` }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </AuthenticatedLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: all 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
    transform: translateY(20px);
}
</style>
