<script setup>
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Error del servidor" />
    <div class="min-h-screen bg-background flex items-center justify-center px-4">
        <div class="text-center">
            <div class="mb-8">
                <span class="text-9xl font-black text-danger">500</span>
            </div>
            <h1 class="text-3xl font-black text-text mb-4">Error del Servidor</h1>
            <p class="text-gray-600 mb-8 text-lg">Ha ocurrido un error inesperado. Intenta de nuevo más tarde.</p>
            <Link
                href="/dashboard"
                class="inline-flex items-center gap-2 bg-primary hover:bg-orange-700 text-white font-black px-8 py-4 rounded-xl transition-all shadow-lg hover:shadow-xl"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver al Dashboard
            </Link>
        </div>
    </div>
</template>
