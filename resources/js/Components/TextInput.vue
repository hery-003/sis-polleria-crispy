<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="w-full px-5 py-3.5 bg-white border-2 border-gray-100 rounded-2xl font-bold text-sm text-text focus:ring-4 focus:ring-primary/5 focus:border-primary transition-all placeholder:text-gray-300"
        v-model="model"
        ref="input"
    />
</template>
