<script setup>
defineProps({
    value: {
        type: String,
    },
});
</script>

<template>
    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
