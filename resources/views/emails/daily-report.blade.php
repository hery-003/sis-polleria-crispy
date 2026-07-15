<x-mail::message>
# Reporte Diario - {{ $date }}

**Resumen de ventas del día.**

<x-mail::table>
| Métrica | Valor |
|:--------|:-----:|
| Ventas Totales | S/ {{ number_format($stats['total_sales'] ?? 0, 2) }} |
| Pedidos | {{ $stats['orders_count'] ?? 0 }} |
| Ticket Promedio | S/ {{ number_format($stats['avg_ticket'] ?? 0, 2) }} |
| Cancelaciones | {{ $stats['cancellations'] ?? 0 }} |
</x-mail::table>

El reporte detallado en PDF se adjunta a este correo.

<x-mail::button :url="config('app.url')">
Ir al Sistema
</x-mail::button>

Gracias,<br>
{{ config('app.name') }}
</x-mail::message>
