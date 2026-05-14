<script setup>
defineProps({
    icon: { type: String, default: '📦' },
    title: { type: String, default: 'Sin datos' },
    message: { type: String, default: 'No hay registros disponibles' },
    action: { type: String, default: '' },
});
</script>

<template>
    <div class="flex flex-col items-center justify-center py-16 text-center">
        <span class="text-6xl mb-4 opacity-40 grayscale">{{ icon }}</span>
        <h3 class="text-lg font-black text-gray-400 uppercase tracking-wider">{{ title }}</h3>
        <p class="text-sm text-gray-300 font-medium mt-1">{{ message }}</p>
        <button v-if="action" class="mt-6 px-6 py-2.5 bg-primary text-white rounded-xl font-semibold text-sm hover:bg-orange-600 transition-all">
            {{ action }}
        </button>
    </div>
</template>
