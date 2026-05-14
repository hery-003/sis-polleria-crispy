<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    variants: Array,
});

const edits = ref({});
const saving = ref(false);

const lowStockItems = computed(() =>
    props.variants.filter(v => v.stock <= (v.threshold || 5) && v.stock > 0)
);

const outOfStock = computed(() =>
    props.variants.filter(v => v.stock === 0)
);

const itemsToOrder = computed(() =>
    props.variants.filter(v => v.stock <= (v.threshold || 5))
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
</script>

<template>
    <Head title="Stock Masivo" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-black text-text tracking-tight uppercase">📦 Gestión de Stock</h2>
                <button
                    v-if="hasEdits"
                    @click="saveAll"
                    :disabled="saving"
                    class="px-6 py-3 bg-primary hover:bg-orange-700 disabled:bg-gray-300 text-white rounded-xl font-black text-sm transition-all"
                >
                    {{ saving ? 'Guardando...' : `Guardar ${Object.keys(edits).length} cambios` }}
                </button>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Sugerencia de Compra -->
            <div class="bg-white rounded-3xl shadow-xl overflow-hidden border-l-8 border-orange-500">
                <div class="p-6 bg-gradient-to-r from-orange-50 to-white">
                    <h3 class="text-lg font-black text-text uppercase flex items-center gap-2">
                        🛒 Sugerencia de Compra a Proveedores
                    </h3>
                    <p class="text-sm text-gray-500 mt-1">
                        Productos con stock por debajo del umbral mínimo. Usa los botones <strong>+</strong> para sugerir cantidad a pedir.
                    </p>
                </div>

                <div v-if="itemsToOrder.length === 0" class="p-12 text-center text-gray-400">
                    <span class="text-5xl block mb-3">✅</span>
                    <p class="text-lg font-bold">Todos los productos tienen stock suficiente</p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase">Producto</th>
                                <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase">Variante</th>
                                <th class="px-6 py-4 text-center text-xs font-black text-gray-500 uppercase">Stock Actual</th>
                                <th class="px-6 py-4 text-center text-xs font-black text-gray-500 uppercase">Umbral</th>
                                <th class="px-6 py-4 text-center text-xs font-black text-gray-500 uppercase">Sugerido a Pedir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="v in itemsToOrder" :key="v.id" class="hover:bg-orange-50 transition-colors">
                                <td class="px-6 py-4">
                                    <p class="font-bold text-text">{{ v.product }}</p>
                                    <p class="text-xs text-gray-500">{{ v.category }}</p>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium">{{ v.variant }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 rounded-full text-xs font-black"
                                        :class="v.stock === 0 ? 'bg-red-100 text-red-800' : 'bg-orange-100 text-orange-800'">
                                        {{ v.stock }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-gray-600">{{ v.threshold || 5 }}</td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button @click="setStock(v.id, (edits[v.id] ?? v.stock) + 5)"
                                            class="w-10 h-10 flex items-center justify-center bg-primary text-white rounded-lg font-bold text-lg hover:bg-orange-700 transition-all">
                                            +5
                                        </button>
                                        <button @click="setStock(v.id, (edits[v.id] ?? v.stock) + 10)"
                                            class="w-10 h-10 flex items-center justify-center bg-primary text-white rounded-lg font-bold text-lg hover:bg-orange-700 transition-all">
                                            +10
                                        </button>
                                        <button @click="setStock(v.id, (edits[v.id] ?? v.stock) + 20)"
                                            class="w-10 h-10 flex items-center justify-center bg-primary text-white rounded-lg font-bold text-lg hover:bg-orange-700 transition-all">
                                            +20
                                        </button>
                                        <input type="number" :value="edits[v.id] ?? v.stock"
                                            @input="setStock(v.id, $event.target.value)"
                                            class="w-20 text-center py-2 border-2 border-gray-200 rounded-lg font-bold text-sm" />
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Editor Masivo de Stock -->
            <div class="bg-white rounded-3xl shadow-xl overflow-hidden border-l-8 border-blue-500">
                <div class="p-6 bg-gradient-to-r from-blue-50 to-white flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-black text-text uppercase flex items-center gap-2">
                            📋 Todos los Stocks
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">Edita los valores directamente y guarda los cambios.</p>
                    </div>
                    <div class="flex gap-4 text-sm font-bold">
                        <span class="text-green-600">▲ {{ props.variants.filter(v => v.stock > (v.threshold || 5)).length }} ok</span>
                        <span class="text-orange-600">● {{ lowStockItems.length }} bajo</span>
                        <span class="text-red-600">✕ {{ outOfStock.length }} sin stock</span>
                    </div>
                </div>

                <div class="overflow-x-auto max-h-[600px] overflow-y-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b sticky top-0">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase">Producto</th>
                                <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase">Categoría</th>
                                <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase">Variante</th>
                                <th class="px-6 py-4 text-center text-xs font-black text-gray-500 uppercase">Stock</th>
                                <th class="px-6 py-4 text-center text-xs font-black text-gray-500 uppercase">Umbral</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="v in props.variants" :key="v.id"
                                :class="[
                                    'hover:bg-gray-50 transition-colors',
                                    v.stock === 0 ? 'bg-red-50' : v.stock <= (v.threshold || 5) ? 'bg-orange-50' : ''
                                ]">
                                <td class="px-6 py-4 font-bold text-text">{{ v.product }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ v.category }}</td>
                                <td class="px-6 py-4 text-sm font-medium">{{ v.variant }}</td>
                                <td class="px-6 py-4 text-center">
                                    <input type="number"
                                        :value="edits[v.id] ?? v.stock"
                                        @input="setStock(v.id, $event.target.value)"
                                            class="w-24 text-center py-2 border-2 rounded-lg font-bold text-sm transition-all"
                                        :class="edits[v.id] !== undefined ? 'border-primary bg-orange-50' : 'border-gray-200'" />
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-gray-600">{{ v.threshold || 5 }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
