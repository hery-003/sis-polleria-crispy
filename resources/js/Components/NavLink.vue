<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ? 'inline-flex items-center px-4 py-2 rounded-xl bg-primary/10 text-primary text-sm font-bold leading-5 focus:outline-none transition duration-150 ease-in-out'
        : 'inline-flex items-center px-4 py-2 rounded-xl text-sm font-medium leading-5 text-gray-500 hover:text-text hover:bg-gray-100 focus:outline-none focus:text-text focus:bg-gray-100 transition duration-150 ease-in-out',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
