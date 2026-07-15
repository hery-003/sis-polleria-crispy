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
const bgColors = {
    primary: 'bg-orange-50/50',
    blue: 'bg-blue-50/50',
    purple: 'bg-purple-50/50',
    green: 'bg-green-50/50',
    danger: 'bg-red-50/50',
    gray: 'bg-gray-50/50'
}

const iconBgColors = {
    primary: 'bg-primary/10 text-primary',
    blue: 'bg-blue-600/10 text-blue-600',
    purple: 'bg-purple-600/10 text-purple-600',
    green: 'bg-green-600/10 text-green-600',
    danger: 'bg-danger/10 text-danger',
    gray: 'bg-gray-500/10 text-gray-500'
}
</script>

<template>
    <Card :hover="true" :class="['border-l-8', borderColors[color], bgColors[color]]" padding="p-6">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ label }}</p>
                <h3 class="text-3xl font-black mt-2 tracking-tight" :class="textColors[color]">{{ value }}</h3>
            </div>
            <div :class="['w-12 h-12 rounded-2xl flex items-center justify-center text-2xl shadow-sm', iconBgColors[color]]">
                {{ icon }}
            </div>
        </div>
        <div v-if="trend" class="mt-4 flex items-center gap-1.5">
            <div :class="['px-2 py-0.5 rounded-md text-[10px] font-black flex items-center gap-0.5', trendUp ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700']">
                {{ trendUp ? '↑' : '↓' }} {{ trend }}
            </div>
            <span class="text-[10px] text-gray-400 font-black uppercase tracking-tight">vs periodo anterior</span>
        </div>
    </Card>
</template>
