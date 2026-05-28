<script setup>
defineProps({
    padding: { type: String, default: 'p-6' },
    noPadding: { type: Boolean, default: false },
    hover: { type: Boolean, default: false }
})
</script>

<template>
    <div 
        class="bg-white rounded-2xl shadow-xl border border-gray-100 transition-all duration-200 overflow-hidden"
        :class="[
            noPadding ? '' : padding,
            hover ? 'hover:shadow-2xl hover:-translate-y-1' : ''
        ]"
    >
        <slot />
    </div>
</template>
