<script setup>
import { Head } from '@inertiajs/vue3';
import { onMounted } from 'vue';

const props = defineProps({
    order: Object
});

onMounted(() => {
    setTimeout(() => {
        window.print();
    }, 500);
});

const downloadPDF = () => {
    window.open(route('orders.pdf', props.order.id), '_blank');
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString();
};
</script>

<template>
    <Head title="Imprimir Ticket" />

    <div class="no-print text-center p-4">
        <button @click="downloadPDF" class="px-6 py-3 bg-primary text-white rounded-xl font-bold text-sm hover:bg-orange-600 transition-all shadow-md">
            Descargar PDF
        </button>
        <button @click="window.print()" class="px-6 py-3 ml-2 bg-gray-200 text-gray-700 rounded-xl font-bold text-sm hover:bg-gray-300 transition-all">
            Imprimir
        </button>
        <p class="text-xs text-gray-400 mt-2">Esta sección no aparecerá en el ticket impreso</p>
    </div>
    <div class="ticket-container bg-white p-4 font-mono text-sm uppercase">
        <div class="text-center mb-4">
            <img src="/images/Logo polleria.png" alt="Pollo Broster Crispy" class="mx-auto w-32 h-32 object-contain mb-2" />
            <h1 class="font-black text-lg">POLLO BROSTER CRISPY</h1>
            <p>"Crujiente y Sabor Real"</p>
            <p class="text-xs mt-1">Av. Principal #123 - Santa Cruz</p>
            <p class="text-xs">NIT: 1023456789</p>
        </div>

        <div class="border-t border-b border-dashed py-2 my-2 space-y-1">
            <div class="flex justify-between">
                <span>Ticket:</span>
                <span class="font-bold">{{ order.order_number }}</span>
            </div>
            <div class="flex justify-between">
                <span>Fecha:</span>
                <span class="text-[10px]">{{ formatDate(order.created_at) }}</span>
            </div>
            <div class="flex justify-between">
                <span>Cajero:</span>
                <span class="text-[10px]">{{ order.user?.name || 'N/A' }}</span>
            </div>
            <div v-if="order.mesa" class="flex justify-between">
                <span>Mesa:</span>
                <span class="font-bold">{{ order.mesa.name || order.mesa.number }}</span>
            </div>
            <div class="flex justify-between">
                <span>Tipo:</span>
                <span class="font-bold">{{ order.type === 'dine_in' ? 'MESA' : order.type === 'take_out' ? 'LLEVAR' : 'DELIVERY' }}</span>
            </div>
        </div>

        <table class="w-full mb-2">
            <thead>
                <tr class="border-b border-dashed">
                    <th class="text-left py-1">Cant</th>
                    <th class="text-left py-1">Producto</th>
                    <th class="text-right py-1">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in order.items" :key="item.id">
                    <td class="py-1">{{ item.quantity }}</td>
                    <td class="py-1">
                        {{ item.product.name }}
                        <div class="text-[10px]">{{ item.variant.name }}</div>
                    </td>
                    <td class="py-1 text-right">Bs. {{ (item.price * item.quantity).toFixed(2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="border-t border-dashed pt-2 space-y-1">
            <div class="flex justify-between font-black text-lg">
                <span>TOTAL:</span>
                <span>Bs. {{ parseFloat(order.total_amount || 0).toFixed(2) }}</span>
            </div>
            <div class="flex justify-between text-xs">
                <span>Pago:</span>
                <span>{{ (order.payment_method || '').toUpperCase() || 'N/A' }}</span>
            </div>
        </div>

        <div v-if="order.metodo_pago?.qr_image" class="flex justify-center mt-4">
            <img :src="'/storage/' + order.metodo_pago.qr_image" class="w-24 h-24 object-contain" alt="QR" />
        </div>

        <div class="text-center mt-6 text-[10px]">
            <p>Gracias por su preferencia!</p>
            <p class="mt-1 italic">Vuelva pronto por el mejor sabor</p>
        </div>
    </div>
</template>

<style scoped>
@media print {
    body * {
        visibility: hidden;
    }
    .ticket-container, .ticket-container * {
        visibility: visible;
    }
    .ticket-container {
        position: absolute;
        left: 0;
        top: 0;
        width: 80mm;
    }
    .no-print {
        display: none !important;
    }
    @page {
        margin: 0;
        size: auto;
    }
}

.ticket-container {
    max-width: 300px;
    margin: 0 auto;
}
</style>
