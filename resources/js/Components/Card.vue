<script setup>
defineProps({
    padding: { type: String, default: 'p-6' },
    noPadding: { type: Boolean, default: false },
    hover: { type: Boolean, default: false }
})
</script>

<template>
    <div 
        class="bg-white rounded-[2.5rem] shadow-xl shadow-orange-500/5 border border-white transition-all duration-300 overflow-hidden"
        :class="[
            noPadding ? '' : padding,
            hover ? 'hover:shadow-2xl hover:shadow-orange-500/10 hover:-translate-y-2' : ''
        ]"
    >
        <slot />
    </div>
</template>
