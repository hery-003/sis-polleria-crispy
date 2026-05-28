<script setup>
import Card from './Card.vue';

defineProps({
    label: String,
    value: [String, Number],
    icon: String,
    color: { type: String, default: 'primary' },
    trend: String,
    trendUp: Boolean
})

const borderColors = {
    primary: 'border-primary',
    blue: 'border-blue-600',
    purple: 'border-purple-500',
    green: 'border-green-500',
    danger: 'border-danger',
    gray: 'border-gray-500'
}

const textColors = {
    primary: 'text-primary',
    blue: 'text-blue-600',
    purple: 'text-purple-600',
    green: 'text-green-600',
    danger: 'text-danger',
    gray: 'text-gray-500'
}
</script>

<template>
    <Card :hover="true" :class="['border-l-8', borderColors[color]]" padding="p-6">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ label }}</p>
                <h3 class="text-3xl font-black mt-2" :class="textColors[color]">{{ value }}</h3>
            </div>
            <span class="text-4xl">{{ icon }}</span>
        </div>
        <div v-if="trend" class="mt-3 flex items-center gap-1">
            <span :class="['text-xs font-bold', trendUp ? 'text-green-500' : 'text-danger']">
                {{ trendUp ? '↑' : '↓' }} {{ trend }}
            </span>
            <span class="text-[10px] text-gray-400 font-bold uppercase">vs. ayer</span>
        </div>
    </Card>
</template>
