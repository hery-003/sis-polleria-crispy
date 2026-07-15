/**
 * Definición de constantes de rutas para evitar hardcoding en los componentes.
 * Utiliza Ziggy para la resolución de URLs.
 */

export const ROUTES = {
    DASHBOARD: 'dashboard',
    POS: 'pos.index',
    KITCHEN: 'kitchen.index',
    WAITER: 'waiter.index',
    PRODUCTS: {
        INDEX: 'products.index',
        CREATE: 'products.create',
        STOCK: 'products.stock',
    },
    CATEGORIES: 'categories.index',
    CLIENTS: 'clients.index',
    MESAS: 'mesas.index',
    CASH: 'cash-register.index',
    REPORTS: 'reports.index',
    USERS: 'users.index',
    AUDIT: 'audit-logs.index',
    SETTINGS: 'settings.index',
};

export const getRoute = (name, params = {}) => route(name, params);
