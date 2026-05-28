<script setup>
defineProps({
    type: { type: String, default: 'card' },
    count: { type: Number, default: 4 },
    cols: { type: Number, default: 4 },
});
</script>

<template>
    <!-- Card skeleton -->
    <div v-if="type === 'card'" class="grid gap-6" :style="{ gridTemplateColumns: `repeat(${cols}, minmax(0, 1fr))` }">
        <div v-for="i in count" :key="i" class="bg-white rounded-2xl border border-gray-100 p-6 animate-pulse">
            <div class="h-4 bg-gray-200 rounded w-1/3 mb-4"></div>
            <div class="h-8 bg-gray-200 rounded w-1/2"></div>
        </div>
    </div>

    <!-- Table skeleton -->
    <div v-else-if="type === 'table'" class="bg-white rounded-2xl border border-gray-100 overflow-hidden animate-pulse">
        <div class="p-6 border-b border-gray-100">
            <div class="h-5 bg-gray-200 rounded w-1/4"></div>
        </div>
        <div class="divide-y divide-gray-50">
            <div v-for="i in count" :key="i" class="p-6 flex gap-6">
                <div class="h-4 bg-gray-200 rounded w-1/6"></div>
                <div class="h-4 bg-gray-200 rounded w-1/4"></div>
                <div class="h-4 bg-gray-200 rounded w-1/6"></div>
                <div class="h-4 bg-gray-200 rounded w-1/6"></div>
            </div>
        </div>
    </div>

    <!-- List skeleton -->
    <div v-else-if="type === 'list'" class="space-y-3 animate-pulse">
        <div v-for="i in count" :key="i" class="flex gap-4 p-4 bg-white rounded-xl border border-gray-100">
            <div class="w-12 h-12 bg-gray-200 rounded-lg"></div>
            <div class="flex-1 space-y-2">
                <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                <div class="h-3 bg-gray-200 rounded w-1/2"></div>
            </div>
            <div class="h-8 w-20 bg-gray-200 rounded-lg"></div>
        </div>
    </div>
</template>
