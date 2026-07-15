<script setup>
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue';
import axios from 'axios';
import { useCartStore } from '@/stores/cart';
import { useToast } from '@/plugins/toast';
import { usePOSStore } from '@/composables/usePOSStore';
import SecondaryButton from '@/components/SecondaryButton.vue';
import Card from '@/components/Card.vue';
import Modal from '@/components/Modal.vue';
import EmptyState from '@/components/EmptyState.vue';
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
const receivedAmount = ref('');
const changeAmount = computed(() => {
    const received = parseFloat(receivedAmount.value) || 0;
    const total = cartStore.total;
    return received >= total ? received - total : 0;
});
const showCancelConfirm = ref(false);
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
const showMobileCart = ref(false);
const showMobileVariants = ref(false);

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

const searchClient = debounce(async () => {
    if (clientSearch.value.length < 2) {
        clientResults.value = [];
        return;
    }
    try {
        const response = await axios.get(route('clients.search', { q: clientSearch.value }));
        clientResults.value = response.data;
    } catch (e) {
        toast.error('Error al buscar clientes');
    }
}, 300);

const selectClient = async (client) => {
    selectedClient.value = client;
    clientSearch.value = client.name;
    clientResults.value = [];

    const response = await fetch(route('clients.orders', client.id));
    clientHistory.value = await response.json();
};

const selectProduct = (product) => {
    playClick();
    selectedProduct.value = product;
    showMobileVariants.value = true;
};

const addToCart = (product, variant) => {
    playClick();
    cartStore.addItem(product, variant);

    addedVariantId.value = variant.id;
    cartBouncing.value = true;
    setTimeout(() => {
        addedVariantId.value = null;
        cartBouncing.value = false;
    }, 500);

    showMobileVariants.value = false;
};

const selectedMetodoQR = computed(() => {
    const metodo = props.metodosPago.find(m => m.slug === paymentMethod.value)
    return metodo?.qr_image ? '/storage/' + metodo.qr_image : null
})

const metodosConQR = computed(() => {
    return new Set(props.metodosPago.filter(m => m.qr_image).map(m => m.slug))
})

const getMetodoColor = (slug) => {
    const qrMethods = metodosConQR.value
    const colors = {
        'cash': 'bg-green-600 border-green-600 text-white',
        'card': 'bg-blue-600 border-blue-600 text-white',
        'yape': 'bg-purple-600 border-purple-600 text-white',
        'plin': 'bg-teal-600 border-teal-600 text-white',
    };
    if (colors[slug]) return colors[slug]
    if (qrMethods.has(slug)) return 'bg-purple-600 border-purple-600 text-white'
    return 'bg-white border-gray-200 text-gray-400';
};

const confirmCancelOrder = () => {
    cartStore.clearCart();
    selectedMesa.value = null;
    selectedProduct.value = null;
    showCancelConfirm.value = false;
    toast.warning('Pedido cancelado');
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
    const received = parseFloat(receivedAmount.value) || 0;

    router.post(route('pos.store'), {
        items: items,
        total: total,
        received_amount: received > 0 ? received : null,
        change: received > total ? received - total : 0,
        metodo_pago_id: props.metodosPago.find(m => m.slug === paymentMethod.value)?.id || null,
        payment_method: paymentMethod.value,
        type: cartStore.orderType,
        notes: cartStore.notes,
        mesa_id: selectedMesa.value || null,
        client_id: selectedClient.value?.id || null,
        auto_pay: true,
    }, {
        onSuccess: (page) => {
            const error = page.props?.flash?.error;
            if (error) {
                playError();
                toast.error(error);
                return;
            }
            playSuccess();
            const lastOrderId = page.props?.flash?.last_order_id;
            if (lastOrderId) {
                window.open(route('orders.print', lastOrderId), '_blank');
            }
            cartStore.clearCart();
            confirmedOrderId.value = lastOrderId;
            selectedProduct.value = null;
            selectedMesa.value = null;
            selectedClient.value = null;
            clientSearch.value = '';
            clientHistory.value = [];
            showMobileCart.value = false;
            toast.success('Pedido confirmado y cobrado!');
        },
        onError: (errors) => {
            playError();
            const messages = Object.values(errors || {});
            toast.error(messages.length ? messages.join('. ') : 'Error al procesar el pedido');
            console.error('Order errors:', errors);
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

    <AuthenticatedLayout fullWidth>
        <div class="flex flex-col lg:flex-row h-auto lg:h-[calc(100vh-5rem)] gap-0 bg-background rounded-none lg:rounded-2xl shadow-sm border-0 lg:border border-gray-100 overflow-hidden lg:pb-0 pb-24">
            
            <!-- COLUMNA 1: PRODUCTOS -->
            <div class="flex-1 flex flex-col min-w-0 lg:border-r border-gray-100 max-h-[50vh] lg:max-h-full">
                <div class="flex items-center justify-between px-3 lg:px-5 py-3 border-b border-gray-100 bg-background">
                    <h1 class="text-sm font-black text-text uppercase tracking-tight">Productos</h1>
                    <span class="hidden lg:inline text-[10px] text-gray-400 font-medium">F1: Buscar · F6: Cobrar</span>
                    <button @click="showMobileCart = true" class="lg:hidden relative p-2 rounded-xl bg-primary/10 text-primary">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z" />
                        </svg>
                        <span v-if="cartStore.totalItems > 0" class="absolute -top-1 -right-1 w-5 h-5 bg-danger text-white text-[9px] font-black rounded-full flex items-center justify-center">{{ cartStore.totalItems }}</span>
                    </button>
                </div>
                <div class="px-3 lg:px-6 pt-4 pb-3 space-y-3 border-b border-gray-100 bg-white">
                    <div class="relative group">
                        <input ref="searchInput" v-model="search" @input="debouncedSearch" type="text" 
                            placeholder="Buscar producto... (F1)"
                            class="w-full pl-12 pr-4 py-3 bg-gray-50 border-2 border-gray-100 rounded-2xl font-bold focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-sm shadow-inner" />
                        <svg class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400 group-focus-within:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <div class="flex gap-2 overflow-x-auto scrollbar-hide pb-1">
                        <button v-for="category in categories" :key="category.id"
                            @click="selectedCategory = category.id; selectedProduct = null"
                            :class="[
                                'px-5 py-2.5 rounded-2xl text-xs font-black uppercase tracking-widest whitespace-nowrap transition-all flex-shrink-0 border-2 active:scale-95',
                                selectedCategory === category.id
                                    ? 'bg-primary border-primary text-white shadow-lg shadow-orange-500/20 -translate-y-0.5'
                                    : 'bg-white border-gray-100 text-gray-500 hover:border-primary/30 hover:bg-orange-50'
                            ]"
                        >
                            {{ category.name }}
                        </button>
                    </div>
                </div>
                <div class="flex-1 overflow-y-auto p-4 lg:p-6 bg-background/30">
                    <div v-if="filteredProducts.length === 0" class="h-full flex items-center justify-center">
                        <EmptyState :icon="search ? '🔍' : '🍗'" :title="search ? 'Sin resultados' : 'Seleccione una categor\u00eda'" :message="search ? 'Intente con otro t\u00e9rmino' : 'Elija una categor\u00eda para ver productos'" />
                    </div>
                    <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-3 xl:grid-cols-4 gap-3 lg:gap-4">
                        <div v-for="product in filteredProducts" :key="product.id"
                            @click="selectProduct(product)"
                            :class="[
                                'bg-white rounded-[2rem] border-2 p-3 lg:p-4 cursor-pointer transition-all flex flex-col items-center text-center group active:scale-95',
                                selectedProduct?.id === product.id
                                    ? 'border-primary ring-4 ring-primary/10 shadow-2xl shadow-orange-500/10'
                                    : 'border-white shadow-xl shadow-orange-500/5 hover:border-primary/20 hover:shadow-2xl hover:shadow-orange-500/10 hover:-translate-y-1'
                            ]"
                        >
                            <div class="w-16 h-16 lg:w-24 lg:h-24 mb-3 lg:mb-4 rounded-[1.5rem] bg-gray-50 flex items-center justify-center text-3xl lg:text-4xl shadow-inner group-hover:scale-110 transition-transform duration-300 overflow-hidden">
                                <img v-if="product.image_url" :src="product.image_thumbnail_url || product.image_url" :alt="product.name" class="w-full h-full object-contain p-2" loading="lazy">
                                <span v-else>🍗</span>
                            </div>
                            <h3 class="font-black text-[10px] lg:text-xs text-text uppercase tracking-tight leading-tight line-clamp-2 h-8 font-poppins">{{ product.name }}</h3>
                            <div class="mt-3 bg-primary/10 px-3 py-1 rounded-full">
                                <p class="text-[10px] lg:text-[11px] font-black text-primary font-mono">Bs. {{ product.variants?.[0]?.price || '0.00' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- COLUMNA 2: VARIANTES (visible en desktop) -->
            <div class="hidden lg:flex w-[300px] flex-col flex-shrink-0 border-r border-gray-100">
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 bg-background">
                    <h2 class="text-sm font-black text-text uppercase tracking-tight">Variantes</h2>
                </div>
                <div class="flex-1 overflow-y-auto p-4 bg-background/50">
                    <template v-if="selectedProduct">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-100">
                                <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center text-2xl flex-shrink-0">
                                    <img v-if="selectedProduct.image_url" :src="selectedProduct.image_url" :alt="selectedProduct.name" class="w-full h-full object-contain rounded-xl" loading="lazy">
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
                                    'w-full py-5 px-5 rounded-2xl font-black transition-all flex flex-col items-center justify-center gap-1 border-2 text-center group',
                                    addedVariantId === variant.id
                                        ? 'bg-green-50 text-green-700 border-green-300 scale-[1.05] shadow-lg ring-4 ring-green-100'
                                        : 'bg-white text-text border-gray-100 hover:border-primary hover:bg-orange-50 hover:shadow-xl hover:-translate-y-1'
                                ]"
                            >
                                <span class="text-sm uppercase tracking-tight group-hover:scale-110 transition-transform">{{ variant.name }}</span>
                                <span class="text-lg font-mono text-primary group-hover:scale-110 transition-transform">Bs. {{ variant.price }}</span>
                            </button>
                        </div>
                    </template>
                    <div v-else class="h-full flex items-center justify-center">
                        <EmptyState icon="👆" title="Sin selecci\u00f3n" message="Seleccione un producto para ver sus variantes" />
                    </div>
                </div>
            </div>

            <!-- COLUMNA 3: PEDIDO (visible en desktop) -->
            <div class="hidden lg:flex w-[380px] flex-col flex-shrink-0">
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 bg-background">
                    <h2 class="text-sm font-black text-text uppercase tracking-tight flex items-center gap-2">Pedido</h2>
                    <div class="flex items-center gap-1.5">
                        <button @click="toggleSound" class="p-1.5 rounded-xl hover:bg-gray-100 transition-colors" :title="soundEnabled ? 'Silenciar' : 'Activar sonido'">
                            <span class="text-sm">{{ soundEnabled ? '🔊' : '🔇' }}</span>
                        </button>
                        <div class="flex items-center gap-1.5 px-2.5 py-1 bg-white rounded-xl border border-gray-100">
                            <div class="w-6 h-6 rounded-full bg-primary/10 flex items-center justify-center text-[8px] font-semibold text-primary">
                                {{ page.props.auth.user.name.substring(0, 2) }}
                            </div>
                            <span class="text-[10px] font-medium text-gray-700">{{ page.props.auth.user.name.split(' ')[0] }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto px-4 py-3 space-y-4">
                    <div class="flex gap-1 p-0.5 bg-gray-100 rounded-xl">
                        <button @click="cartStore.orderType = 'dine_in'"
                            :class="['flex-1 py-2 text-[10px] font-semibold uppercase tracking-wider rounded-lg transition-all', cartStore.orderType === 'dine_in' ? 'bg-white shadow-sm text-primary' : 'text-gray-400 hover:text-gray-600']">
                            Mesa
                        </button>
                        <button @click="cartStore.orderType = 'take_out'"
                            :class="['flex-1 py-2 text-[10px] font-semibold uppercase tracking-wider rounded-lg transition-all', cartStore.orderType === 'take_out' ? 'bg-white shadow-sm text-primary' : 'text-gray-400 hover:text-gray-600']">
                            Llevar
                        </button>
                        <button @click="cartStore.orderType = 'delivery'"
                            :class="['flex-1 py-2 text-[10px] font-semibold uppercase tracking-wider rounded-lg transition-all', cartStore.orderType === 'delivery' ? 'bg-white shadow-sm text-primary' : 'text-gray-400 hover:text-gray-600']">
                            Delivery
                        </button>
                    </div>

                    <div v-if="cartStore.orderType === 'dine_in'" class="space-y-1.5">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Mesa</label>
                        <div class="grid grid-cols-5 gap-1">
                            <button v-for="mesa in mesas" :key="mesa.id"
                                @click="selectedMesa = mesa.id"
                                :class="['py-1.5 rounded-lg text-xs font-semibold transition-all border', selectedMesa === mesa.id ? 'bg-primary border-primary text-white' : 'bg-white border-gray-100 text-gray-600 hover:border-gray-200']">
                                {{ mesa.number }}
                            </button>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Cliente</label>
                        <div class="relative">
                            <input v-model="clientSearch" @input="searchClient" type="text"
                                placeholder="Buscar..."
                                class="w-full px-3 py-2 bg-gray-50 border-2 border-gray-100 rounded-xl text-xs font-bold focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all" />
                            <div v-if="clientResults.length > 0" class="absolute z-20 w-full bg-white border border-gray-100 rounded-xl mt-1 max-h-36 overflow-y-auto shadow-lg">
                                <button v-for="client in clientResults" :key="client.id"
                                    @click="selectClient(client)"
                                    class="w-full text-left px-3 py-2 hover:bg-orange-50 border-b border-gray-50 last:border-0 text-xs">
                                    <div class="font-medium text-gray-800">{{ client.name }}</div>
                                    <div class="text-[9px] text-gray-400">{{ client.phone }}</div>
                                </button>
                            </div>
                            <Transition name="fade">
                                <div v-if="selectedClient" class="mt-2 px-3 py-2 bg-orange-50 rounded-xl border border-orange-100 flex justify-between items-center">
                                    <span class="text-xs font-medium text-gray-800">{{ selectedClient.name }}</span>
                                    <button @click="selectedClient = null; clientSearch = ''; clientHistory = []" class="text-gray-400 hover:text-gray-600 text-xs">✕</button>
                                </div>
                            </Transition>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Productos</label>
                        <div v-if="cartStore.items.length === 0" class="border-2 border-dashed border-gray-100 rounded-xl py-6">
                            <EmptyState icon="🛒" title="Carrito vac\u00edo" message="Seleccione productos para comenzar" />
                        </div>
                        <div class="space-y-1.5 max-h-[280px] overflow-y-auto">
                            <TransitionGroup name="cart-item">
                                <div v-for="(item, index) in cartStore.items" :key="item.product_id + '-' + item.variant_id"
                                    class="flex gap-2 bg-white p-2.5 rounded-2xl border border-gray-50 hover:border-primary/20 hover:shadow-sm transition-all">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex justify-between items-start gap-1">
                                            <div class="min-w-0">
                                                <h4 class="font-medium text-[11px] text-gray-800 truncate">{{ item.name }}</h4>
                                                <p class="text-[9px] text-primary font-medium">{{ item.variant_name }}</p>
                                            </div>
                                            <span class="text-[11px] font-semibold text-gray-800 font-mono whitespace-nowrap">Bs. {{ (item.price * item.quantity).toFixed(2) }}</span>
                                        </div>
                                        <div class="mt-1.5 flex items-center justify-between">
                                            <div class="flex items-center gap-0.5 bg-gray-50 rounded-lg border border-gray-100">
                                                <button @click="cartStore.updateQuantity(index, item.quantity - 1)" class="w-7 h-7 flex items-center justify-center hover:bg-white hover:text-danger rounded-lg text-sm transition-all">−</button>
                                                <span class="w-7 text-center font-medium text-xs font-mono">{{ item.quantity }}</span>
                                                <button @click="cartStore.updateQuantity(index, item.quantity + 1)" class="w-7 h-7 flex items-center justify-center hover:bg-white hover:text-green-600 rounded-lg text-sm transition-all">+</button>
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

                <div class="border-t border-gray-100 px-4 py-3 space-y-3">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Pago</label>
                        <div class="grid grid-cols-4 gap-1.5">
                            <button v-for="metodo in metodosPago" :key="metodo.id"
                                @click="paymentMethod = metodo.slug; receivedAmount = ''"
                                :class="[
                                    'relative py-2 text-[9px] font-semibold uppercase tracking-wider rounded-xl border transition-all',
                                    paymentMethod === metodo.slug
                                        ? getMetodoColor(metodo.slug)
                                        : 'bg-white border-gray-100 text-gray-400 hover:border-gray-200'
                                ]">
                                <span v-if="metodosConQR.has(metodo.slug)" class="absolute -top-1 -right-1 w-3.5 h-3.5 bg-purple-500 rounded-full flex items-center justify-center">
                                    <svg class="w-2 h-2 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4v-4h-4v4zm-8 4h4v-4H4v4zm0-8h4V4H4v4z"/>
                                    </svg>
                                </span>
                                {{ metodo.name }}
                            </button>
                        </div>
                    </div>

                    <div v-if="paymentMethod === 'cash'" class="space-y-1.5">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Monto recibido</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-gray-400">Bs.</span>
                            <input v-model="receivedAmount" type="number" step="0.01" min="0" placeholder="0.00"
                                class="w-full pl-9 pr-3 py-2 bg-gray-50 border-2 border-gray-100 rounded-xl text-sm font-mono font-bold focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all" />
                        </div>
                        <div v-if="changeAmount > 0" class="flex justify-between items-center px-3 py-2 bg-green-50 rounded-xl border border-green-200">
                            <span class="text-[10px] font-semibold text-green-700 uppercase tracking-wider">Cambio</span>
                            <span class="text-sm font-bold text-green-700 font-mono">Bs. {{ changeAmount.toFixed(2) }}</span>
                        </div>
                    </div>

                    <div v-else-if="selectedMetodoQR" class="flex flex-col items-center py-3 px-4 bg-purple-50 rounded-xl border-2 border-purple-100 space-y-2">
                        <div class="flex items-center gap-1.5 text-[10px] font-semibold text-purple-700 uppercase tracking-wider">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4v-4h-4v4zm-8 4h4v-4H4v4zm0-8h4V4H4v4z"/>
                            </svg>
                            Pago con QR
                        </div>
                        <img :src="selectedMetodoQR" class="w-36 h-36 rounded-xl border-2 border-white shadow-md object-cover bg-white" alt="QR de pago" />
                        <p class="text-[9px] text-purple-600 font-medium">Escanea el código QR con tu app bancaria</p>
                    </div>

                    <div class="px-3 py-2.5 bg-background rounded-xl border border-yellow-200 space-y-1.5">
                        <div class="flex justify-between items-center text-gray-400 text-[10px]">
                            <span class="font-medium">Subtotal</span>
                            <span class="font-semibold font-mono">Bs. {{ cartStore.subtotal.toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between items-center pt-1.5 border-t border-dashed border-yellow-300">
                            <span class="text-sm font-semibold text-text">Total</span>
                            <span class="text-xl font-bold text-primary font-mono">Bs. {{ cartStore.total.toFixed(2) }}</span>
                        </div>
                    </div>

                    <div class="flex gap-1.5">
                        <button @click="showCancelConfirm = true" :disabled="cartStore.items.length === 0"
                            class="px-3 py-3 bg-white hover:bg-red-50 disabled:opacity-30 disabled:grayscale text-danger border border-red-100 rounded-xl transition-all active:scale-95">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                        <button @click="confirmAndPay" :disabled="cartStore.items.length === 0 || confirming"
                            class="flex-1 py-3 bg-primary hover:bg-orange-600 disabled:bg-gray-200 disabled:shadow-none text-white rounded-xl font-semibold text-sm shadow-md transition-all active:scale-95">
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

        <!-- Mobile: Variant selector bottom sheet -->
        <Teleport to="body">
            <div v-if="selectedProduct && showMobileVariants" class="fixed inset-0 z-50 lg:hidden" @click.self="showMobileVariants = false">
                <div class="absolute inset-0 bg-black/50" @click="showMobileVariants = false"></div>
                <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-3xl max-h-[70vh] overflow-y-auto p-5 shadow-2xl">
                    <div class="flex items-center justify-between mb-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center text-2xl flex-shrink-0 overflow-hidden">
                                <img v-if="selectedProduct.image_url" :src="selectedProduct.image_url" class="w-full h-full object-contain" loading="lazy">
                                <span v-else>🍗</span>
                            </div>
                            <div>
                                <h3 class="font-bold text-sm text-gray-800">{{ selectedProduct.name }}</h3>
                                <p class="text-[10px] text-gray-400">Seleccione una variante</p>
                            </div>
                        </div>
                        <button @click="showMobileVariants = false" class="w-8 h-8 flex items-center justify-center bg-gray-100 rounded-full text-gray-500">✕</button>
                    </div>
                    <div class="space-y-2">
                        <button v-for="variant in selectedProduct.variants" :key="variant.id"
                            @click="addToCart(selectedProduct, variant)"
                            :class="[
                                'w-full py-4 px-4 rounded-xl font-semibold transition-all flex justify-between items-center border-2',
                                addedVariantId === variant.id
                                    ? 'bg-green-50 text-green-700 border-green-300 scale-[1.02] shadow-sm'
                                    : 'bg-gray-50 text-gray-700 border-gray-100 active:border-primary'
                            ]"
                        >
                            <span class="text-sm">{{ variant.name }}</span>
                            <span class="text-sm font-mono font-bold text-primary">Bs. {{ variant.price }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Mobile: Cart full screen -->
        <Teleport to="body">
            <div v-if="showMobileCart" class="fixed inset-0 z-50 lg:hidden bg-white flex flex-col">
                <div class="flex items-center justify-between px-4 py-4 border-b border-gray-100 bg-white sticky top-0">
                    <button @click="showMobileCart = false" class="text-gray-500 text-lg">✕</button>
                    <h2 class="text-sm font-black text-text uppercase">Pedido</h2>
                    <div class="flex items-center gap-2">
                        <button @click="toggleSound" class="p-1.5 rounded-xl hover:bg-gray-100 transition-colors">
                            <span class="text-sm">{{ soundEnabled ? '🔊' : '🔇' }}</span>
                        </button>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto px-4 py-3 space-y-4">
                    <div class="flex gap-1 p-0.5 bg-gray-100 rounded-xl">
                        <button @click="cartStore.orderType = 'dine_in'"
                            :class="['flex-1 py-2 text-[10px] font-semibold uppercase tracking-wider rounded-lg transition-all', cartStore.orderType === 'dine_in' ? 'bg-white shadow-sm text-primary' : 'text-gray-400 hover:text-gray-600']">
                            Mesa
                        </button>
                        <button @click="cartStore.orderType = 'take_out'"
                            :class="['flex-1 py-2 text-[10px] font-semibold uppercase tracking-wider rounded-lg transition-all', cartStore.orderType === 'take_out' ? 'bg-white shadow-sm text-primary' : 'text-gray-400 hover:text-gray-600']">
                            Llevar
                        </button>
                        <button @click="cartStore.orderType = 'delivery'"
                            :class="['flex-1 py-2 text-[10px] font-semibold uppercase tracking-wider rounded-lg transition-all', cartStore.orderType === 'delivery' ? 'bg-white shadow-sm text-primary' : 'text-gray-400 hover:text-gray-600']">
                            Delivery
                        </button>
                    </div>

                    <div v-if="cartStore.orderType === 'dine_in'" class="space-y-1.5">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Mesa</label>
                        <div class="grid grid-cols-5 gap-1">
                            <button v-for="mesa in mesas" :key="mesa.id"
                                @click="selectedMesa = mesa.id"
                                :class="['py-2 rounded-lg text-xs font-semibold transition-all border', selectedMesa === mesa.id ? 'bg-primary border-primary text-white' : 'bg-white border-gray-100 text-gray-600 hover:border-gray-200']">
                                {{ mesa.number }}
                            </button>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Cliente</label>
                        <div class="relative">
                            <input v-model="clientSearch" @input="searchClient" type="text"
                                placeholder="Buscar..."
                                class="w-full px-3 py-2 bg-gray-50 border-2 border-gray-100 rounded-xl text-xs font-bold focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all" />
                            <div v-if="clientResults.length > 0" class="absolute z-20 w-full bg-white border border-gray-100 rounded-xl mt-1 max-h-36 overflow-y-auto shadow-lg">
                                <button v-for="client in clientResults" :key="client.id"
                                    @click="selectClient(client)"
                                    class="w-full text-left px-3 py-2 hover:bg-orange-50 border-b border-gray-50 last:border-0 text-xs">
                                    <div class="font-medium text-gray-800">{{ client.name }}</div>
                                    <div class="text-[9px] text-gray-400">{{ client.phone }}</div>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Items</label>
                        <div v-if="cartStore.items.length === 0" class="border-2 border-dashed border-gray-100 rounded-xl py-6">
                            <EmptyState icon="🛒" title="Carrito vac\u00edo" message="Seleccione productos para comenzar" />
                        </div>
                        <div class="space-y-2">
                            <div v-for="(item, index) in cartStore.items" :key="item.product_id + '-' + item.variant_id"
                                class="flex gap-2 bg-white p-3 rounded-xl border border-gray-100">
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-start gap-1">
                                        <div class="min-w-0">
                                            <h4 class="font-semibold text-xs text-gray-800 truncate">{{ item.name }}</h4>
                                            <p class="text-[10px] text-primary">{{ item.variant_name }}</p>
                                        </div>
                                        <span class="text-xs font-semibold text-gray-800 font-mono whitespace-nowrap">Bs. {{ (item.price * item.quantity).toFixed(2) }}</span>
                                    </div>
                                    <div class="mt-2 flex items-center justify-between">
                                        <div class="flex items-center gap-0.5 bg-gray-50 rounded-xl border border-gray-100">
                                            <button @click="cartStore.updateQuantity(index, item.quantity - 1)" class="w-8 h-8 flex items-center justify-center hover:text-danger rounded-xl text-sm transition-all">−</button>
                                            <span class="w-8 text-center font-medium text-sm font-mono">{{ item.quantity }}</span>
                                            <button @click="cartStore.updateQuantity(index, item.quantity + 1)" class="w-8 h-8 flex items-center justify-center hover:text-green-600 rounded-xl text-sm transition-all">+</button>
                                        </div>
                                        <button @click="cartStore.removeItem(index)" class="text-gray-300 hover:text-danger transition-colors p-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-100 px-4 py-3 space-y-3 bg-background sticky bottom-0 shadow-2xl">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Pago</label>
                        <div class="grid grid-cols-4 gap-1.5">
                            <button v-for="metodo in metodosPago" :key="metodo.id"
                                @click="paymentMethod = metodo.slug; receivedAmount = ''"
                                :class="[
                                    'relative py-2 text-[9px] font-semibold uppercase tracking-wider rounded-xl border transition-all',
                                    paymentMethod === metodo.slug
                                        ? getMetodoColor(metodo.slug)
                                        : 'bg-white border-gray-100 text-gray-400 hover:border-gray-200'
                                ]">
                                <span v-if="metodosConQR.has(metodo.slug)" class="absolute -top-1 -right-1 w-3.5 h-3.5 bg-purple-500 rounded-full flex items-center justify-center">
                                    <svg class="w-2 h-2 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4v-4h-4v4zm-8 4h4v-4H4v4zm0-8h4V4H4v4z"/>
                                    </svg>
                                </span>
                                {{ metodo.name }}
                            </button>
                        </div>
                    </div>

                    <div v-if="paymentMethod === 'cash'" class="space-y-1.5">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Monto recibido</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-gray-400">Bs.</span>
                            <input v-model="receivedAmount" type="number" step="0.01" min="0" placeholder="0.00"
                                class="w-full pl-9 pr-3 py-2 bg-gray-50 border-2 border-gray-100 rounded-xl text-sm font-mono font-bold focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all" />
                        </div>
                        <div v-if="changeAmount > 0" class="flex justify-between items-center px-3 py-2 bg-green-50 rounded-xl border border-green-200">
                            <span class="text-[10px] font-semibold text-green-700 uppercase tracking-wider">Cambio</span>
                            <span class="text-sm font-bold text-green-700 font-mono">Bs. {{ changeAmount.toFixed(2) }}</span>
                        </div>
                    </div>

                    <div v-else-if="selectedMetodoQR" class="flex flex-col items-center py-3 px-4 bg-purple-50 rounded-xl border-2 border-purple-100 space-y-2">
                        <div class="flex items-center gap-1.5 text-[10px] font-semibold text-purple-700 uppercase tracking-wider">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4v-4h-4v4zm-8 4h4v-4H4v4zm0-8h4V4H4v4z"/>
                            </svg>
                            Pago con QR
                        </div>
                        <img :src="selectedMetodoQR" class="w-36 h-36 rounded-xl border-2 border-white shadow-md object-cover bg-white" alt="QR de pago" />
                        <p class="text-[9px] text-purple-600 font-medium">Escanea el código QR con tu app bancaria</p>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-sm font-semibold text-text">Total</span>
                        <span class="text-2xl font-bold text-primary font-mono">Bs. {{ cartStore.total.toFixed(2) }}</span>
                    </div>

                    <div class="flex gap-2">
                        <button @click="showCancelConfirm = true" :disabled="cartStore.items.length === 0"
                            class="px-4 py-3 bg-white hover:bg-red-50 disabled:opacity-30 text-danger border border-red-100 rounded-xl transition-all active:scale-95">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                        <button @click="confirmAndPay" :disabled="cartStore.items.length === 0 || confirming"
                            class="flex-1 py-3 bg-primary hover:bg-orange-600 disabled:bg-gray-200 text-white rounded-xl font-bold text-sm shadow-lg transition-all active:scale-95">
                            <span v-if="confirming" class="flex items-center justify-center gap-1.5">
                                <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Procesando...
                            </span>
                            <span v-else>Pagar Ahora</span>
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Order confirmed banner -->
        <Transition name="fade">
            <div v-if="confirmedOrderId" class="fixed top-24 right-4 z-50 bg-green-600 text-white rounded-2xl shadow-2xl p-5 max-w-sm">
                <div class="flex items-start gap-3">
                    <span class="text-2xl">✅</span>
                    <div>
                        <h3 class="font-bold text-sm">Pedido registrado</h3>
                        <p class="text-green-100 text-xs mt-0.5">ID: #{{ confirmedOrderId }}</p>
                        <div class="flex gap-2 mt-3">
                            <a :href="route('orders.print', confirmedOrderId)" target="_blank"
                                class="text-xs px-3 py-1.5 bg-white text-green-700 rounded-xl font-bold hover:bg-green-50 transition-all">
                                Ver Ticket
                            </a>
                            <a :href="route('orders.reprint', confirmedOrderId)" target="_blank"
                                class="text-xs px-3 py-1.5 bg-green-500 text-white rounded-xl font-bold hover:bg-green-400 transition-all">
                                Reimprimir
                            </a>
                            <button @click="confirmedOrderId = null"
                                class="text-xs px-2 py-1.5 text-green-100 hover:text-white transition-all">
                                ✕
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Mobile: Fixed bottom bar -->
        <div class="lg:hidden fixed bottom-0 left-0 right-0 z-30 bg-white border-t border-gray-200 px-4 py-2 shadow-2xl flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z" />
                    </svg>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ cartStore.totalItems }} items</p>
                    <p class="text-sm font-black text-text">Bs. {{ cartStore.total.toFixed(2) }}</p>
                </div>
            </div>
            <button @click="showMobileCart = true"
                class="px-6 py-3 bg-primary text-white rounded-xl font-bold text-sm shadow-lg active:scale-95 transition-all"
                :disabled="cartStore.items.length === 0">
                Ver Pedido
            </button>
        </div>
        <!-- Cancel Confirm Modal -->
        <Modal :show="showCancelConfirm" @close="showCancelConfirm = false">
            <div class="p-8 text-center">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-5">
                    <svg class="w-8 h-8 text-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
                <h3 class="text-xl font-black text-text mb-2">Cancelar Pedido</h3>
                <p class="text-sm text-gray-500 mb-6">¿Estás seguro de cancelar el pedido actual? Se perderán todos los productos agregados.</p>
                <div class="flex gap-3">
                    <SecondaryButton @click="showCancelConfirm = false" class="flex-1">Volver</SecondaryButton>
                    <SecondaryButton @click="confirmCancelOrder" class="flex-1 !bg-danger !text-white !border-danger hover:!bg-red-700">Sí, Cancelar</SecondaryButton>
                </div>
            </div>
        </Modal>
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
