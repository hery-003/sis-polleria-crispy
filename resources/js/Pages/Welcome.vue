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
        <div class="relative bg-gradient-to-br from-primary to-orange-600 overflow-hidden">
            <div class="absolute top-0 right-0 w-96 h-96 bg-white/5 rounded-full -mr-32 -mt-32"></div>
            <div class="absolute bottom-0 left-0 w-64 h-64 bg-white/5 rounded-full -ml-32 -mb-32"></div>

            <div class="relative max-w-7xl mx-auto px-6 py-8">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-4">
                        <img src="/images/Logo polleria.png" alt="Logo" class="h-16 w-16 rounded-full ring-4 ring-white/30" />
                        <div>
                            <h1 class="text-2xl font-black italic text-white uppercase tracking-tight">Pollo Broster Crispy</h1>
                            <p class="text-orange-100 text-sm font-medium">Sistema de Gestión de Pedidos</p>
                        </div>
                    </div>
                    <nav v-if="canLogin" class="flex items-center gap-4">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="px-6 py-2.5 bg-white text-primary rounded-xl font-bold hover:bg-orange-50 transition-all shadow-lg"
                        >
                            Ir al Dashboard
                        </Link>
                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="px-6 py-2.5 bg-white/15 text-white rounded-xl font-bold hover:bg-white/25 transition-all backdrop-blur-sm"
                            >
                                Iniciar Sesión
                            </Link>
                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="px-6 py-2.5 bg-white text-primary rounded-xl font-bold hover:bg-orange-50 transition-all shadow-lg"
                            >
                                Registrarse
                            </Link>
                        </template>
                    </nav>
                </div>
            </div>

            <!-- Hero Content -->
            <div class="relative max-w-7xl mx-auto px-6 py-24 text-center">
                <div class="max-w-3xl mx-auto">
                    <div class="inline-flex items-center gap-2 bg-white/15 backdrop-blur-sm rounded-full px-6 py-2 mb-8">
                        <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                        <span class="text-white/90 text-sm font-medium">Sistema Operativo</span>
                    </div>
                    <h2 class="text-5xl md:text-7xl font-black italic text-white uppercase leading-tight mb-6">
                        Gestión Inteligente<br />
                        <span class="text-secondary">para tu Pollería</span>
                    </h2>
                    <p class="text-xl text-orange-100 mb-12 max-w-2xl mx-auto leading-relaxed">
                        Control de pedidos, cocina, caja y reportes en un solo lugar.
                        Diseñado para optimizar la operación de tu negocio.
                    </p>
                    <div class="flex flex-wrap justify-center gap-4">
                        <Link
                            :href="route('login')"
                            class="px-8 py-4 bg-white text-primary rounded-2xl font-black italic uppercase text-lg hover:bg-orange-50 transition-all shadow-2xl hover:-translate-y-1"
                        >
                            Comenzar ahora
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="px-8 py-4 bg-white/15 text-white rounded-2xl font-black italic uppercase text-lg hover:bg-white/25 transition-all backdrop-blur-sm hover:-translate-y-1"
                        >
                            Crear cuenta
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="max-w-7xl mx-auto px-6 py-24">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-black italic text-text uppercase">Todo lo que necesitas</h2>
                <p class="text-gray-500 mt-4 text-lg font-medium">Módulos diseñados para cada área de tu negocio</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white rounded-3xl p-8 shadow-xl hover:shadow-2xl transition-all border-t-4 border-primary group">
                    <span class="text-5xl block mb-6 group-hover:scale-110 transition-transform">🛒</span>
                    <h3 class="text-xl font-black italic text-text uppercase mb-3">Punto de Venta</h3>
                    <p class="text-gray-500 leading-relaxed">Registro rápido de pedidos con selección de mesa, tipo de servicio y múltiples métodos de pago.</p>
                </div>

                <div class="bg-white rounded-3xl p-8 shadow-xl hover:shadow-2xl transition-all border-t-4 border-orange-500 group">
                    <span class="text-5xl block mb-6 group-hover:scale-110 transition-transform">👨‍🍳</span>
                    <h3 class="text-xl font-black italic text-text uppercase mb-3">Monitor de Cocina</h3>
                    <p class="text-gray-500 leading-relaxed">Visualización en tiempo real de pedidos entrantes, gestión de estados y alertas de preparación.</p>
                </div>

                <div class="bg-white rounded-3xl p-8 shadow-xl hover:shadow-2xl transition-all border-t-4 border-green-500 group">
                    <span class="text-5xl block mb-6 group-hover:scale-110 transition-transform">💰</span>
                    <h3 class="text-xl font-black italic text-text uppercase mb-3">Control de Caja</h3>
                    <p class="text-gray-500 leading-relaxed">Apertura y cierre de caja, registro de movimientos y conciliación de ingresos diarios.</p>
                </div>

                <div class="bg-white rounded-3xl p-8 shadow-xl hover:shadow-2xl transition-all border-t-4 border-blue-500 group">
                    <span class="text-5xl block mb-6 group-hover:scale-110 transition-transform">📈</span>
                    <h3 class="text-xl font-black italic text-text uppercase mb-3">Reportes</h3>
                    <p class="text-gray-500 leading-relaxed">Estadísticas de ventas, ticket promedio, productos más vendidos y exportación a PDF/CSV.</p>
                </div>

                <div class="bg-white rounded-3xl p-8 shadow-xl hover:shadow-2xl transition-all border-t-4 border-purple-500 group">
                    <span class="text-5xl block mb-6 group-hover:scale-110 transition-transform">🍗</span>
                    <h3 class="text-xl font-black italic text-text uppercase mb-3">Productos</h3>
                    <p class="text-gray-500 leading-relaxed">Gestión de catálogo con categorías, variantes (porciones, enteros) y control de stock.</p>
                </div>

                <div class="bg-white rounded-3xl p-8 shadow-xl hover:shadow-2xl transition-all border-t-4 border-gray-700 group">
                    <span class="text-5xl block mb-6 group-hover:scale-110 transition-transform">👥</span>
                    <h3 class="text-xl font-black italic text-text uppercase mb-3">Personal y Roles</h3>
                    <p class="text-gray-500 leading-relaxed">Administración de usuarios con roles (admin, cajero, mesero) y registro de auditoría.</p>
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
