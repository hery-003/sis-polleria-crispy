<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue';
import axios from 'axios';
import { useCartStore } from '@/Stores/cart';
import { useToast } from '@/Plugins/toast';
import { usePOSStore } from '@/Composables/usePOSStore';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Card from '@/Components/Card.vue';
import Fuse from 'fuse.js';

const debounce = (fn, delay) => {
    let timeoutId;
    return (...args) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn(...args), delay);
    };
};

const toast = useToast();
const { soundEnabled, playClick, playSuccess, playError, toggleSound } = usePOSStore();

const props = defineProps({
    categories: Array,
    metodosPago: Array,
    mesas: Array,
});

const page = usePage();
const cartStore = useCartStore();
const selectedCategory = ref(props.categories[0]?.id || null);
const selectedProduct = ref(null);
const paymentMethod = ref('cash');
const confirming = ref(false);
const processingPay = ref(false);
const confirmedOrderId = ref(null);
const search = ref('');
const searchInput = ref(null);
const cartBouncing = ref(false);
const addedVariantId = ref(null);
const selectedMesa = ref(null);
const selectedClient = ref(null);
const clientSearch = ref('');
const clientResults = ref([]);
const clientHistory = ref([]);
const showClientModal = ref(false);
const clientHistoryLoading = ref(false);

watch(() => cartStore.orderType, (newType) => {
    if (newType !== 'dine_in') {
        selectedMesa.value = null;
    }
});

const allProducts = computed(() => {
    const products = [];
    for (const cat of props.categories) {
        if (cat.products) {
            for (const p of cat.products) {
                products.push({ ...p, category_name: cat.name });
            }
        }
    }
    return products;
});

let fuseInstance = null;

const filteredProducts = computed(() => {
    if (!search.value) {
        const category = props.categories.find(c => c.id === selectedCategory.value);
        return category ? category.products : [];
    }

    if (!fuseInstance) {
        fuseInstance = new Fuse(allProducts.value, {
            keys: ['name', 'description', 'category_name'],
            threshold: 0.4,
            distance: 100,
        });
    }

    return fuseInstance.search(search.value).map(r => r.item);
});

const debouncedSearch = debounce(() => {
    fuseInstance = null;
}, 300);

// Búsqueda de clientes
const searchClient = debounce(async () => {
    if (clientSearch.value.length < 2) {
        clientResults.value = [];
        return;
    }
    try {
        const response = await axios.get(route('clients.search', { q: clientSearch.value }));
        clientResults.value = response.data;
    } catch (e) {
        console.error('Error searching clients:', e);
    }
}, 300);

const selectClient = async (client) => {
    selectedClient.value = client;
    clientSearch.value = client.name;
    clientResults.value = [];
    
    // Cargar historial
    clientHistoryLoading.value = true;
    try {
        const response = await fetch(route('clients.orders', client.id));
        clientHistory.value = await response.json();
    } catch (e) {
        console.error('Error loading client orders:', e);
    } finally {
        clientHistoryLoading.value = false;
    }
};

const selectProduct = (product) => {
    playClick();
    selectedProduct.value = product;
};

const addToCart = (product, variant) => {
    playClick();
    cartStore.addItem(product, variant);
    
    // Feedback visual
    addedVariantId.value = variant.id;
    cartBouncing.value = true;
    setTimeout(() => {
        addedVariantId.value = null;
        cartBouncing.value = false;
    }, 500);
};

const getMetodoColor = (slug) => {
    const colors = {
        'cash': 'bg-green-600 border-green-600 text-white',
        'card': 'bg-blue-600 border-blue-600 text-white',
        'yape': 'bg-purple-600 border-purple-600 text-white'
    };
    return colors[slug] || 'bg-white border-gray-200 text-gray-400';
};

const cancelOrder = () => {
    if (cartStore.items.length === 0) return;
    if (confirm('¿Está seguro de cancelar el pedido actual?')) {
        cartStore.clearCart();
        selectedMesa.value = null;
        selectedProduct.value = null;
        toast.warning('Pedido cancelado');
    }
};

const confirmAndPay = () => {
    if (cartStore.items.length === 0) return;

    if (cartStore.orderType === 'dine_in' && !selectedMesa.value) {
        toast.error('Seleccione una mesa para pedidos en mesa');
        return;
    }

    confirming.value = true;

    const items = cartStore.items.map(item => ({
        product_id: item.product_id,
        variant_id: item.variant_id,
        price: item.price,
        quantity: item.quantity
    }));

    const total = items.reduce((acc, item) => acc + (item.price * item.quantity), 0);

    router.post(route('pos.store'), {
        items: items,
        total: total,
        metodo_pago_id: props.metodosPago.find(m => m.slug === paymentMethod.value)?.id || null,
        payment_method: paymentMethod.value,
        type: cartStore.orderType,
        notes: cartStore.notes,
        mesa_id: selectedMesa.value || null,
        client_id: selectedClient.value?.id || null,
        auto_pay: true,
    }, {
        onSuccess: (page) => {
            playSuccess();
            if (page.props?.flash?.last_order_id) {
                window.open(route('orders.print', page.props.flash.last_order_id), '_blank');
            }
            cartStore.clearCart();
            selectedProduct.value = null;
            selectedMesa.value = null;
            selectedClient.value = null;
            clientSearch.value = '';
            clientHistory.value = [];
            toast.success('¡Pedido confirmado y cobrado!');
        },
        onError: () => {
            playError();
            toast.error('Error al procesar el pedido');
        },
        onFinish: () => {
            confirming.value = false;
        }
    });
};

const handleKeydown = (e) => {
    if (e.key === 'F1') {
        e.preventDefault();
        searchInput.value?.focus();
    } else if (e.key === 'F6') {
        e.preventDefault();
        confirmAndPay();
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <Head title="POS - Punto de Venta" />

    <AuthenticatedLayout>
        <div class="flex h-[calc(100vh-8.5rem)] gap-0 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            
            <!-- COLUMNA 1: PRODUCTOS (siempre visible) -->
            <div class="flex-1 flex flex-col min-w-0 border-r border-gray-100">
                <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 bg-gray-50/50">
                    <h1 class="text-sm font-black text-text uppercase tracking-tight">🍗 Productos</h1>
                    <span class="text-[10px] text-gray-400 font-medium">F1: Buscar · F6: Cobrar</span>
                </div>
                <div class="px-5 pt-4 pb-3 space-y-3 border-b border-gray-100">
                    <div class="relative">
                        <input ref="searchInput" v-model="search" @input="debouncedSearch" type="text" 
                            placeholder="Buscar producto... (F1)"
                            class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm" />
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <div class="flex gap-1.5 overflow-x-auto scrollbar-hide">
                        <button v-for="category in categories" :key="category.id"
                            @click="selectedCategory = category.id; selectedProduct = null"
                            :class="[
                                'px-3.5 py-1.5 rounded-lg text-xs font-semibold uppercase tracking-wider whitespace-nowrap transition-all flex-shrink-0',
                                selectedCategory === category.id
                                    ? 'bg-primary text-white'
                                    : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                            ]"
                        >
                            {{ category.name }}
                        </button>
                    </div>
                </div>
                <div class="flex-1 overflow-y-auto p-4 bg-gray-50/50">
                    <div v-if="filteredProducts.length === 0" class="h-full flex flex-col items-center justify-center text-gray-400">
                        <span class="text-6xl mb-4 opacity-30">{{ search ? '🔍' : '🍗' }}</span>
                        <p class="font-black text-sm uppercase tracking-wider text-gray-300">{{ search ? 'Sin resultados' : 'Seleccione una categoría' }}</p>
                        <p class="text-[10px] font-medium text-gray-200 mt-1">{{ search ? 'Intente con otro término' : 'Elija una categoría para ver productos' }}</p>
                    </div>
                    <div v-else class="grid grid-cols-4 gap-3">
                        <div v-for="product in filteredProducts" :key="product.id"
                            @click="selectProduct(product)"
                            :class="[
                                'bg-white rounded-xl border p-3 cursor-pointer transition-all flex flex-col items-center text-center',
                                selectedProduct?.id === product.id
                                    ? 'border-primary ring-2 ring-primary/20 shadow-md'
                                    : 'border-gray-100 hover:border-primary hover:shadow-md'
                            ]"
                        >
                            <div class="w-16 h-16 mb-2 rounded-lg bg-gray-50 flex items-center justify-center text-3xl">
                                <img v-if="product.image_url" :src="product.image_url" :alt="product.name" class="w-full h-full object-contain" loading="lazy">
                                <span v-else>🍗</span>
                            </div>
                            <h3 class="font-semibold text-xs text-gray-800 leading-tight line-clamp-2">{{ product.name }}</h3>
                            <p class="text-[10px] font-semibold text-primary mt-1">Bs. {{ product.variants?.[0]?.price || '0.00' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- COLUMNA 2: VARIANTES -->
            <div class="w-[300px] flex flex-col flex-shrink-0 border-r border-gray-100">
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 bg-gray-50/50">
                    <h2 class="text-sm font-black text-text uppercase tracking-tight">🔄 Variantes</h2>
                </div>
                <div class="flex-1 overflow-y-auto p-4 bg-gray-50/50">
                    <template v-if="selectedProduct">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-100">
                                <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center text-2xl flex-shrink-0">
                                    <img v-if="selectedProduct.image_url" :src="selectedProduct.image_url" :alt="selectedProduct.name" class="w-full h-full object-contain rounded-lg" loading="lazy">
                                    <span v-else>🍗</span>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="font-semibold text-sm text-gray-800 truncate">{{ selectedProduct.name }}</h3>
                                    <p class="text-[10px] text-gray-400">Seleccione una variante</p>
                                </div>
                            </div>
                            <button v-for="variant in selectedProduct.variants" :key="variant.id"
                                @click="addToCart(selectedProduct, variant)"
                                :class="[
                                    'w-full py-3.5 px-4 rounded-lg font-semibold transition-all flex justify-between items-center border',
                                    addedVariantId === variant.id
                                        ? 'bg-green-50 text-green-700 border-green-300 scale-[1.02] shadow-sm'
                                        : 'bg-white text-gray-700 border-gray-100 hover:border-primary hover:bg-orange-50 hover:shadow-sm'
                                ]"
                            >
                                <span class="text-sm">{{ variant.name }}</span>
                                <span class="text-sm font-mono font-semibold text-primary">Bs. {{ variant.price }}</span>
                            </button>
                        </div>
                    </template>
                    <div v-else class="h-full flex flex-col items-center justify-center text-gray-300">
                        <span class="text-5xl mb-3 opacity-20">👆</span>
                        <p class="font-black text-xs uppercase tracking-wider text-gray-200">Sin selección</p>
                        <p class="text-[10px] font-medium text-gray-200/60 mt-1">Seleccione un producto<br>para ver sus variantes</p>
                    </div>
                </div>
            </div>

            <!-- COLUMNA 3: PEDIDO (380px fijo) -->
            <div class="w-[380px] flex flex-col flex-shrink-0">
                
                <!-- Header -->
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 bg-gray-50/50">
                    <h2 class="text-sm font-black text-text uppercase tracking-tight flex items-center gap-2">🛒 Pedido</h2>
                    <div class="flex items-center gap-1.5">
                        <button @click="toggleSound" class="p-1.5 rounded-lg hover:bg-gray-100 transition-colors" :title="soundEnabled ? 'Silenciar' : 'Activar sonido'">
                            <span class="text-sm">{{ soundEnabled ? '🔊' : '🔇' }}</span>
                        </button>
                        <div class="flex items-center gap-1.5 px-2.5 py-1 bg-white rounded-lg border border-gray-100">
                            <div class="w-6 h-6 rounded-full bg-primary/10 flex items-center justify-center text-[8px] font-semibold text-primary">
                                {{ page.props.auth.user.name.substring(0, 2) }}
                            </div>
                            <span class="text-[10px] font-medium text-gray-700">{{ page.props.auth.user.name.split(' ')[0] }}</span>
                        </div>
                    </div>
                </div>

                <!-- Contenido scrolleable -->
                <div class="flex-1 overflow-y-auto px-4 py-3 space-y-4">

                    <!-- Tipo de Pedido -->
                    <div class="flex gap-1 p-0.5 bg-gray-100 rounded-lg">
                        <button @click="cartStore.orderType = 'dine_in'"
                            :class="['flex-1 py-2 text-[10px] font-semibold uppercase tracking-wider rounded-md transition-all', cartStore.orderType === 'dine_in' ? 'bg-white shadow-sm text-primary' : 'text-gray-400 hover:text-gray-600']">
                            🏠 Mesa
                        </button>
                        <button @click="cartStore.orderType = 'take_out'"
                            :class="['flex-1 py-2 text-[10px] font-semibold uppercase tracking-wider rounded-md transition-all', cartStore.orderType === 'take_out' ? 'bg-white shadow-sm text-primary' : 'text-gray-400 hover:text-gray-600']">
                            🛍️ Llevar
                        </button>
                        <button @click="cartStore.orderType = 'delivery'"
                            :class="['flex-1 py-2 text-[10px] font-semibold uppercase tracking-wider rounded-md transition-all', cartStore.orderType === 'delivery' ? 'bg-white shadow-sm text-primary' : 'text-gray-400 hover:text-gray-600']">
                            🚴 Delivery
                        </button>
                    </div>

                    <!-- Mesa -->
                    <div v-if="cartStore.orderType === 'dine_in'" class="space-y-1.5">
                        <label class="text-[9px] font-semibold text-gray-400 uppercase tracking-wider">Mesa</label>
                        <div class="grid grid-cols-5 gap-1">
                            <button v-for="mesa in mesas" :key="mesa.id"
                                @click="selectedMesa = mesa.id"
                                :class="['py-1.5 rounded-md text-xs font-semibold transition-all border', selectedMesa === mesa.id ? 'bg-primary border-primary text-white' : 'bg-white border-gray-100 text-gray-600 hover:border-gray-200']">
                                {{ mesa.number }}
                            </button>
                        </div>
                    </div>

                    <!-- Cliente -->
                    <div class="space-y-1.5">
                        <label class="text-[9px] font-semibold text-gray-400 uppercase tracking-wider">Cliente</label>
                        <div class="relative">
                            <input v-model="clientSearch" @input="searchClient" type="text"
                                placeholder="Buscar..."
                                class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
                            <div v-if="clientResults.length > 0" class="absolute z-20 w-full bg-white border border-gray-100 rounded-lg mt-1 max-h-36 overflow-y-auto shadow-lg">
                                <button v-for="client in clientResults" :key="client.id"
                                    @click="selectClient(client)"
                                    class="w-full text-left px-3 py-2 hover:bg-orange-50 border-b border-gray-50 last:border-0 text-xs">
                                    <div class="font-medium text-gray-800">{{ client.name }}</div>
                                    <div class="text-[9px] text-gray-400">{{ client.phone }}</div>
                                </button>
                            </div>
                            <Transition name="fade">
                                <div v-if="selectedClient" class="mt-2 px-3 py-2 bg-orange-50 rounded-lg border border-orange-100 flex justify-between items-center">
                                    <span class="text-xs font-medium text-gray-800">{{ selectedClient.name }}</span>
                                    <button @click="selectedClient = null; clientSearch = ''; clientHistory = []" class="text-gray-400 hover:text-gray-600 text-xs">✕</button>
                                </div>
                            </Transition>
                        </div>
                    </div>

                    <!-- Carrito -->
                    <div class="space-y-1.5">
                        <label class="text-[9px] font-semibold text-gray-400 uppercase tracking-wider">Productos</label>
                        <div v-if="cartStore.items.length === 0" class="py-8 border-2 border-dashed border-gray-100 rounded-lg flex flex-col items-center text-gray-300">
                            <span class="text-4xl mb-2 opacity-20">🛒</span>
                            <p class="text-xs font-black uppercase tracking-wider text-gray-200">Carrito vacío</p>
                            <p class="text-[9px] font-medium text-gray-200/60 mt-1">Seleccione productos para comenzar</p>
                        </div>
                        <div class="space-y-1.5 max-h-[280px] overflow-y-auto">
                            <TransitionGroup name="cart-item">
                                <div v-for="(item, index) in cartStore.items" :key="item.product_id + '-' + item.variant_id"
                                    class="flex gap-2 bg-white p-2.5 rounded-lg border border-gray-50 hover:border-primary/20 transition-all">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex justify-between items-start gap-1">
                                            <div class="min-w-0">
                                                <h4 class="font-medium text-[11px] text-gray-800 truncate">{{ item.name }}</h4>
                                                <p class="text-[9px] text-primary font-medium">{{ item.variant_name }}</p>
                                            </div>
                                            <span class="text-[11px] font-semibold text-gray-800 font-mono whitespace-nowrap">Bs. {{ (item.price * item.quantity).toFixed(2) }}</span>
                                        </div>
                                        <div class="mt-1.5 flex items-center justify-between">
                                            <div class="flex items-center gap-0.5 bg-gray-50 rounded-md border border-gray-100">
                                                <button @click="cartStore.updateQuantity(index, item.quantity - 1)" class="w-7 h-7 flex items-center justify-center hover:bg-white hover:text-danger rounded-md text-sm transition-all">−</button>
                                                <span class="w-7 text-center font-medium text-xs font-mono">{{ item.quantity }}</span>
                                                <button @click="cartStore.updateQuantity(index, item.quantity + 1)" class="w-7 h-7 flex items-center justify-center hover:bg-white hover:text-green-600 rounded-md text-sm transition-all">+</button>
                                            </div>
                                            <button @click="cartStore.removeItem(index)" class="text-gray-300 hover:text-danger transition-colors p-0.5">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </TransitionGroup>
                        </div>
                    </div>
                </div>

                <!-- Totales y botón de pago (fijo abajo) -->
                <div class="border-t border-gray-100 px-4 py-3 space-y-3">
                    
                    <!-- Método de Pago -->
                    <div class="space-y-1.5">
                        <label class="text-[9px] font-semibold text-gray-400 uppercase tracking-wider">Pago</label>
                        <div class="grid grid-cols-3 gap-1.5">
                            <button v-for="metodo in metodosPago" :key="metodo.id"
                                @click="paymentMethod = metodo.slug"
                                :class="[
                                    'py-2 text-[9px] font-semibold uppercase tracking-wider rounded-lg border transition-all',
                                    paymentMethod === metodo.slug
                                        ? getMetodoColor(metodo.slug)
                                        : 'bg-white border-gray-100 text-gray-400 hover:border-gray-200'
                                ]">
                                {{ metodo.name }}
                            </button>
                        </div>
                    </div>

                    <!-- Totales -->
                    <div class="px-3 py-2.5 bg-gray-50 rounded-lg border border-gray-100 space-y-1.5">
                        <div class="flex justify-between items-center text-gray-400 text-[10px]">
                            <span class="font-medium">Subtotal</span>
                            <span class="font-semibold font-mono">Bs. {{ cartStore.subtotal.toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between items-center pt-1.5 border-t border-dashed border-gray-200">
                            <span class="text-sm font-semibold text-gray-800">Total</span>
                            <span class="text-xl font-bold text-primary font-mono">Bs. {{ cartStore.total.toFixed(2) }}</span>
                        </div>
                    </div>

                    <!-- Botón -->
                    <div class="flex gap-1.5">
                        <button @click="cancelOrder" :disabled="cartStore.items.length === 0"
                            class="px-3 py-3 bg-white hover:bg-red-50 disabled:opacity-30 disabled:grayscale text-danger border border-red-100 rounded-lg transition-all active:scale-95">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                        <button @click="confirmAndPay" :disabled="cartStore.items.length === 0 || confirming"
                            class="flex-1 py-3 bg-primary hover:bg-orange-600 disabled:bg-gray-200 disabled:shadow-none text-white rounded-lg font-semibold text-sm shadow-md transition-all active:scale-95">
                            <span v-if="confirming" class="flex items-center justify-center gap-1.5">
                                <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Procesando...
                            </span>
                            <span v-else>Cobrar (F6)</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.cart-item-enter-active, .cart-item-leave-active {
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.cart-item-enter-from { opacity: 0; transform: translateX(30px) scale(0.9); }
.cart-item-leave-to { opacity: 0; transform: translateX(-30px) scale(0.9); }

.fade-enter-active, .fade-leave-active {
    transition: all 0.3s ease;
}
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(-10px); }
</style>
