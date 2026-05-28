<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-background pt-6 sm:justify-center sm:pt-0 w-full"
    >
        <div class="text-center">
            <Link href="/">
                <ApplicationLogo class="h-36 w-36 mx-auto drop-shadow-lg" />
            </Link>
            <h1 class="mt-4 text-2xl font-black text-text uppercase tracking-tight">
                Pollo Broster <span class="text-primary">Crispy</span>
            </h1>
            <p class="text-sm font-medium text-gray-500 mt-1 italic">“Crujiente y Sabor Real!”</p>
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-white px-6 py-8 shadow-xl sm:max-w-md sm:rounded-2xl border-t-4 border-primary"
        >
            <slot />
        </div>
    </div>
</template>
