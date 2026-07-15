<?php

return [
    'kitchen_orders' => env('CACHE_TTL_KITCHEN_ORDERS', 10),
    'waiter_orders' => env('CACHE_TTL_WAITER_ORDERS', 10),
    'dashboard_data' => [
        env('CACHE_TTL_DASHBOARD_MIN', 30),
        env('CACHE_TTL_DASHBOARD_MAX', 60),
    ],
    'pos_categories' => [
        env('CACHE_TTL_POS_CATEGORIES_MIN', 30),
        env('CACHE_TTL_POS_CATEGORIES_MAX', 60),
    ],
    'pos_metodos_pago' => [
        env('CACHE_TTL_POS_METODOS_MIN', 60),
        env('CACHE_TTL_POS_METODOS_MAX', 120),
    ],
    'pos_mesas' => [
        env('CACHE_TTL_POS_MESAS_MIN', 60),
        env('CACHE_TTL_POS_MESAS_MAX', 120),
    ],
    'reports_stats' => [
        env('CACHE_TTL_REPORTS_STATS_MIN', 10),
        env('CACHE_TTL_REPORTS_STATS_MAX', 20),
    ],
    'daily_summary' => [
        env('CACHE_TTL_DAILY_SUMMARY_MIN', 5),
        env('CACHE_TTL_DAILY_SUMMARY_MAX', 15),
    ],
];
