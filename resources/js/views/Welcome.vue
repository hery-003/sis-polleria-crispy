<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});
</script>

<template>
    <Head title="Bienvenido" />
    <div class="min-h-screen bg-background">
        <!-- Hero Section -->
        <div class="relative bg-gradient-to-br from-primary via-orange-500 to-orange-700 overflow-hidden">
            <div class="absolute top-0 right-0 w-[40rem] h-[40rem] bg-white/10 rounded-full -mr-48 -mt-48 blur-3xl animate-pulse"></div>
            <div class="absolute bottom-0 left-0 w-80 h-80 bg-black/10 rounded-full -ml-40 -mb-40 blur-2xl"></div>

            <div class="relative max-w-7xl mx-auto px-6 py-10">
                <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                    <div class="flex items-center gap-5">
                        <img src="/images/Logo polleria.png" alt="Logo" class="h-20 w-20 rounded-full ring-4 ring-white/30 shadow-2xl" />
                        <div>
                            <h1 class="text-3xl font-black italic text-white uppercase tracking-tighter font-poppins">Pollo Broster Crispy</h1>
                            <p class="text-orange-100 text-sm font-black uppercase tracking-widest opacity-80 italic">Crujiente y Sabor Real!</p>
                        </div>
                    </div>
                    <nav v-if="canLogin" class="flex items-center gap-4">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="px-8 py-3 bg-white text-primary rounded-2xl font-black uppercase text-sm hover:bg-orange-50 transition-all shadow-2xl active:scale-95"
                        >
                            Ir al Panel de Control
                        </Link>
                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="px-8 py-3 bg-white/15 text-white rounded-2xl font-black uppercase text-sm hover:bg-white/25 transition-all backdrop-blur-md border border-white/20 active:scale-95"
                            >
                                Acceso Personal
                            </Link>
                        </template>
                    </nav>
                </div>
            </div>

            <!-- Hero Content -->
            <div class="relative max-w-7xl mx-auto px-6 py-32 text-center md:text-left flex flex-col md:flex-row items-center gap-12">
                <div class="flex-1 animate-fade-in-up">
                    <div class="inline-flex items-center gap-3 bg-white/10 backdrop-blur-md rounded-full px-6 py-2.5 mb-10 border border-white/20">
                        <span class="w-3 h-3 bg-green-400 rounded-full animate-ping"></span>
                        <span class="text-white text-xs font-black uppercase tracking-widest">SISTEMA POS PREMIUM v2.0</span>
                    </div>
                    <h2 class="text-6xl md:text-8xl font-black italic text-white uppercase leading-none mb-8 tracking-tighter font-poppins">
                        SABOR REAL,<br />
                        <span class="text-secondary drop-shadow-2xl">GESTIÓN TOTAL</span>
                    </h2>
                    <p class="text-xl md:text-2xl text-orange-100 mb-12 max-w-xl leading-relaxed font-medium">
                        Controla ventas, cocina, inventario y finanzas con la tecnología más avanzada para pollerías.
                    </p>
                    <div class="flex flex-wrap gap-6 justify-center md:justify-start">
                        <Link
                            :href="route('login')"
                            class="px-10 py-5 bg-white text-primary rounded-3xl font-black italic uppercase text-xl hover:bg-orange-50 transition-all shadow-[0_20px_50px_rgba(255,255,255,0.2)] hover:-translate-y-2 active:scale-95"
                        >
                            Comenzar Operación 🚀
                        </Link>
                    </div>
                </div>
                <div class="hidden lg:block flex-1 animate-scale-in">
                    <div class="relative">
                        <div class="absolute inset-0 bg-secondary blur-[100px] opacity-20 animate-pulse"></div>
                        <img src="/images/Logo polleria.png" alt="Hero" class="relative z-10 w-full max-w-md mx-auto drop-shadow-[0_35px_35px_rgba(0,0,0,0.5)] transform rotate-6 hover:rotate-0 transition-transform duration-700" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="max-w-7xl mx-auto px-6 py-32">
            <div class="flex flex-col md:flex-row justify-between items-end mb-20 gap-8">
                <div class="max-w-2xl">
                    <h2 class="text-5xl font-black italic text-text uppercase tracking-tighter font-poppins">Potencia tu Negocio</h2>
                    <p class="text-gray-500 mt-4 text-xl font-medium">Herramientas diseñadas para maximizar la eficiencia y el sabor.</p>
                </div>
                <div class="w-24 h-1 bg-primary rounded-full hidden md:block mb-4"></div>
            </div>

            <div class="grid md:grid-cols-3 gap-10">
                <div v-for="(feature, idx) in [
                    { icon: '🛒', title: 'Punto de Venta', desc: 'Registro ultra-rápido de pedidos optimizado para pantallas táctiles y tablets.', color: 'primary' },
                    { icon: '👨‍🍳', title: 'Monitor Cocina', desc: 'Comandas digitales inteligentes que organizan el flujo de preparación en tiempo real.', color: 'orange-500' },
                    { icon: '💰', title: 'Control de Caja', desc: 'Gestión total de ingresos, egresos y cierres de caja con auditoría integrada.', color: 'green-500' },
                    { icon: '📈', title: 'Reportes Pro', desc: 'Inteligencia de negocios con KPIs, gráficos comparativos y exportación masiva.', color: 'blue-500' },
                    { icon: '🍗', title: 'Menú Dinámico', desc: 'Control total de productos, variantes y stock con alertas automáticas.', color: 'purple-500' },
                    { icon: '👥', title: 'Gestión Personal', desc: 'Control de roles y auditoría de acciones para un negocio seguro y organizado.', color: 'text' },
                ]" :key="idx" class="bg-white rounded-[2.5rem] p-10 shadow-xl shadow-orange-500/5 hover:shadow-2xl hover:shadow-orange-500/10 transition-all border-t-[8px] group hover:-translate-y-3" :class="'border-' + feature.color">
                    <span class="text-6xl block mb-8 group-hover:scale-125 group-hover:rotate-12 transition-transform duration-500">{{ feature.icon }}</span>
                    <h3 class="text-2xl font-black italic text-text uppercase mb-4 font-poppins tracking-tight">{{ feature.title }}</h3>
                    <p class="text-gray-500 leading-relaxed font-medium">{{ feature.desc }}</p>
                </div>
            </div>
        </div>

        <!-- Stats Section -->
        <div class="bg-gradient-to-r from-primary to-orange-600 py-16">
            <div class="max-w-7xl mx-auto px-6">
                <div class="grid md:grid-cols-4 gap-8 text-center">
                    <div>
                        <div class="text-4xl font-black text-white">100%</div>
                        <div class="text-orange-100 font-medium mt-1">Satisfacción</div>
                    </div>
                    <div>
                        <div class="text-4xl font-black text-white">24/7</div>
                        <div class="text-orange-100 font-medium mt-1">Disponibilidad</div>
                    </div>
                    <div>
                        <div class="text-4xl font-black text-white">Roles</div>
                        <div class="text-orange-100 font-medium mt-1">Admin · Cajero · Mesero</div>
                    </div>
                    <div>
                        <div class="text-4xl font-black text-white">Tiempo Real</div>
                        <div class="text-orange-100 font-medium mt-1">Actualización automática</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-text text-center py-8">
            <div class="flex items-center justify-center gap-2 mb-2">
                <img src="/images/Logo polleria.png" alt="Logo" class="h-8 w-8 rounded-full" />
                <span class="text-white font-black italic">Pollo Broster Crispy</span>
            </div>
            <p class="text-orange-200/60 text-sm">&copy; {{ new Date().getFullYear() }} Todos los derechos reservados</p>
        </footer>
    </div>
</template>
