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
        ? 'inline-flex items-center px-3 py-2 rounded-xl bg-white text-primary text-sm font-black leading-5 shadow-lg shadow-black/10 transition duration-150 ease-in-out'
        : 'inline-flex items-center px-3 py-2 rounded-xl text-sm font-bold leading-5 text-white/90 hover:text-white hover:bg-white/10 transition duration-150 ease-in-out',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
