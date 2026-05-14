<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Ventas</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h1 { color: #F37021; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #F37021; color: white; padding: 8px; text-align: left; }
        td { padding: 6px; border-bottom: 1px solid #ddd; }
        .kpi { display: inline-block; width: 24%; margin: 10px 0; text-align: center; }
        .kpi-value { font-size: 24px; font-weight: bold; color: #F37021; }
        .kpi-label { font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <h1>Reporte de Ventas</h1>
    <p>Del {{ $startDate->format('d/m/Y') }} al {{ $endDate->format('d/m/Y') }}</p>

    <div>
        <div class="kpi">
            <div class="kpi-value">Bs. {{ number_format($stats['totalSales'] ?? 0, 2) }}</div>
            <div class="kpi-label">Total Ventas</div>
        </div>
        <div class="kpi">
            <div class="kpi-value">{{ $stats['ordersCount'] ?? 0 }}</div>
            <div class="kpi-label">Pedidos</div>
        </div>
        <div class="kpi">
            <div class="kpi-value">{{ $stats['cancellations'] ?? 0 }}</div>
            <div class="kpi-label">Cancelaciones</div>
        </div>
        <div class="kpi">
            <div class="kpi-value">Bs. {{ number_format($stats['avgTicket'] ?? 0, 2) }}</div>
            <div class="kpi-label">Ticket Promedio</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Pedido</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Total</th>
                <th>Estado</th>
                <th>Tipo</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
            <tr>
                <td>{{ $order->order_number }}</td>
                <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $order->user?->name ?? 'N/A' }}</td>
                <td>Bs. {{ number_format($order->total_amount, 2) }}</td>
                <td>{{ $order->status }}</td>
                <td>{{ $order->type }}</td>
            </tr>
            @empty
            <tr><td colspan="6">No hay pedidos en el rango seleccionado</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
