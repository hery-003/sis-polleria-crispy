# POLLO BROSTER CRISPY — Sistema POS

**Eslogan:** *"Crujiente y Sabor Real!"*

Sistema POS web para gestión integral de pollería. Desarrollado para **Bolivia** (moneda Bs., CI/NIT, Santa Cruz). Orientado a operaciones rápidas, precisión en pedidos y una experiencia de usuario superior con un diseño visual uniforme.

---

## Stack Tecnológico

| Capa | Tecnología |
|------|-----------|
| Backend | Laravel 12 (PHP 8.2+) |
| Frontend | Vue 3 + Inertia.js + Vite |
| Base de datos | MySQL |
| Tiempo real | Laravel Reverb (WebSockets) — _lazy, deshabilitado por defecto_ |
| Autenticación | Laravel Sanctum (session + API tokens) |
| Estado global | Pinia |
| Estilos | Tailwind CSS 3 |
| Impresión | ESC/POS (tickets térmicos) |
| PDF | DomPDF |
| Testing | Pest PHP — **92 tests, 205 assertions** |

---

## Requisitos

- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8.0+
- npm

---

## Instalación

```bash
# Clonar repositorio
git clone <url>
cd sis-polleria-crispy

# Instalar dependencias PHP
composer install

# Instalar dependencias JS
npm install

# Configurar entorno
cp .env.example .env
# Editar .env con credenciales de base de datos

# Generar clave de aplicación
php artisan key:generate

# Ejecutar migraciones y seeders
php artisan migrate --seed

# Compilar assets
npm run build
```

---

## Inicio Rápido

```bash
# Iniciar servidor de desarrollo
php artisan serve

# En otra terminal, compilar assets en modo dev
npm run dev

# O iniciar todo con un clic (Windows)
START_SYSTEM.bat
```

**Usuarios por defecto (seeder):**

| Rol | Email | Contraseña |
|-----|-------|-----------|
| Admin | `admin@crispy.com` | `password` |
| Cajero | `cajero@crispy.com` | `password` |
| Mesero | `mesero@crispy.com` | `password` |

---

## Comandos Útiles

```bash
# Ejecutar pruebas (92 tests, 205 assertions)
vendor/bin/pest

# Generar reporte diario (manual)
php artisan app:generate-daily-report

# Limpieza del sistema
php artisan app:system-cleanup

# Iniciar Reverb (WebSockets) — solo si VITE_REVERB_ENABLED=true
php artisan reverb:start

# Resetear BD con datos demo
php artisan migrate:fresh --seed
```

---

## Funcionalidades Principales

### POS (Punto de Venta)
- **Layout 3 columnas** — Productos (grid flexible) | Variantes (panel 300px) | Pedido (panel 380px)
- Búsqueda en tiempo real de productos
- Selección de variante (tamaño/precio) siempre visible al seleccionar producto
- Modalidad de pedido: Mesa / Llevar / Delivery
- Selección de cliente con búsqueda (CI/NIT, nombre, teléfono)
- Carrito con controles (+/-) y total fijo en parte inferior
- **Atajo de teclado F6** para cobrar
- Toast de confirmación y sonido opcional
- Impresión ESC/POS automática al confirmar pedido

### Cocina en Tiempo Real
- Display de pedidos con WebSockets (Reverb)
- Máquina de estados: `pending → cooking → ready → completed`
- Badges con color `secondary (#FFC20E)` para estado "ready"
- Notificaciones visuales y sonoras

### Caja Registradora
- Apertura y cierre de caja
- Registro de movimientos (ingresos/egresos)
- Conciliación automática de diferencias
- _Solo visible para rol admin en accesos rápidos_

### Reportes
- Gráficos con Chart.js
- Exportación PDF y CSV
- Presets de fechas (Hoy, Ayer, 7 días, 30 días)
- _Solo visible para rol admin en accesos rápidos_

### Gestión de Productos
- CRUD completo con variantes (tamaños/precios)
- Imágenes por producto
- Control de stock por variante
- Stock bajo con alertas

### Clientes
- Registro con CI/NIT (formato Bolivia)
- Sistema de puntos: 1 punto por cada 10 Bs.
- Historial de pedidos por cliente

### Roles y Permisos
- **Admin** — Acceso completo al sistema
- **Cajero** — POS, Caja, Clientes, Reportes (solo lectura)
- **Mesero** — POS, Cocina (solo ver), Pedidos asignados
- Navbar con links filtrados por rol
- Dashboard con accesos rápidos según rol
- Middleware `CheckRole` con redirect a dashboard + flash error

### Seguridad
- Precio validado contra base de datos (no se acepta precio del cliente)
- `product_id` verificado contra la variante seleccionada
- Máquina de estados para transiciones de orden
- Auditoría completa de acciones

### Componentes UI
- `EmptyState.vue` — Estado vacío reutilizable con icono
- `SkeletonLoader.vue` — Loader con 3 modos: card, table, list
- `Error403.vue` — Página 403 personalizada
- Transiciones uniformes (200-300ms)
- Modales con `backdrop-blur-sm`
- Poppins font centralizado en `app.css`
- `StatCard.vue`, `ToastNotification.vue`, `Card.vue`

---

## Localización Bolivia

| Elemento | Valor |
|----------|-------|
| Moneda | Bs. (Bolivianos) |
| Documento | CI/NIT |
| Ciudad | Santa Cruz |
| Dirección | Av. Principal #123 - Santa Cruz |
| NIT | 1023456789 |
| Locale | `es_ES` (Faker) |

---

## Licencia

MIT
