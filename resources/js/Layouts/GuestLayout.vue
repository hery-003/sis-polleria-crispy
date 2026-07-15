<script setup>
import ApplicationLogo from '@/components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="relative flex min-h-screen flex-col items-center justify-center bg-[#FFF4D1] pt-6 sm:pt-0 w-full overflow-hidden px-4">
        <!-- Fondos dinámicos -->
        <div class="absolute top-0 right-0 w-[45rem] h-[45rem] bg-primary/10 rounded-full -mr-64 -mt-64 blur-3xl animate-pulse"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-secondary/20 rounded-full -ml-48 -mb-48 blur-3xl"></div>
        <div class="absolute inset-0 bg-[radial-gradient(#F37021_1px,transparent_1px)] bg-[length:32px_32px] opacity-[0.05]"></div>

        <div class="relative z-10 w-full max-w-[28rem] mx-auto">
            <div class="text-center mb-10 transform hover:scale-105 transition-transform duration-500">
                <Link href="/" class="inline-block group">
                    <div class="relative">
                        <div class="absolute inset-0 bg-white blur-2xl opacity-50 rounded-full"></div>
                        <ApplicationLogo class="relative h-32 w-32 mx-auto drop-shadow-[0_20px_30px_rgba(243,112,33,0.3)] ring-8 ring-white/50 rounded-full" />
                        <div class="absolute -bottom-2 -right-2 w-10 h-10 bg-gradient-to-br from-green-400 to-green-600 rounded-full border-4 border-white flex items-center justify-center shadow-2xl animate-bounce-in">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    </div>
                </Link>
                <div class="mt-8">
                    <h1 class="text-4xl font-black text-text uppercase tracking-tighter font-poppins italic leading-none">
                        Pollo Broster<br />
                        <span class="text-primary drop-shadow-sm">Crispy</span>
                    </h1>
                    <div class="inline-block h-1 w-12 bg-secondary rounded-full mt-4"></div>
                </div>
            </div>

            <div class="w-full overflow-hidden bg-white px-10 py-12 shadow-[0_30px_60px_-15px_rgba(75,35,13,0.15)] rounded-[3rem] border border-white relative">
                <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-primary via-orange-500 to-secondary"></div>
                <slot />
            </div>

            <div class="text-center mt-10 space-y-2">
                <p class="text-[10px] font-black text-text/40 uppercase tracking-[0.3em]">
                    Premium POS System v2.0
                </p>
                <p class="text-[10px] font-bold text-text/30 uppercase tracking-widest">
                    &copy; {{ new Date().getFullYear() }} Pollo Broster Crispy · Bolivia
                </p>
            </div>
        </div>
    </div>
</template>
