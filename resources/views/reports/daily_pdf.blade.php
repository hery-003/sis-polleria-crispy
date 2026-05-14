<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte Diario - {{ $date }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #f39c12; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { margin: 0; color: #e67e22; }
        .section { margin-bottom: 30px; }
        .section-title { font-weight: bold; background: #f8f9fa; padding: 5px 10px; border-left: 5px solid #e67e22; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-size: 12px; }
        .total-row { font-weight: bold; background-color: #fff9f0; }
        .footer { text-align: center; font-size: 10px; color: #777; margin-top: 50px; }
        .metric-card { display: inline-block; width: 30%; border: 1px solid #eee; padding: 10px; margin: 1%; text-align: center; border-radius: 8px; }
        .metric-value { font-size: 20px; font-weight: bold; color: #2c3e50; }
        .metric-label { font-size: 10px; color: #7f8c8d; text-transform: uppercase; }
    </style>
</head>
<body>
    <div class="header">
        <h1>POLLERIA CRISPY</h1>
        <p>Resumen Operativo Diario</p>
        <strong>Fecha: {{ $date }}</strong>
    </div>

    <div class="section">
        <div class="section-title">KPIs PRINCIPALES</div>
        <div class="metric-card">
            <div class="metric-label">Ventas Totales</div>
            <div class="metric-value">Bs. {{ number_format($summary['total_sales'], 2) }}</div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Pedidos</div>
            <div class="metric-value">{{ $summary['orders_count'] }}</div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Ticket Promedio</div>
            <div class="metric-value">Bs. {{ number_format($summary['avg_ticket'], 2) }}</div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">VENTAS POR MÉTODO DE PAGO</div>
        <table>
            <thead>
                <tr>
                    <th>Método</th>
                    <th>Pedidos</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse(($stats['salesByPayment'] ?? []) as $payment)
                <tr>
                    <td>{{ ucfirst($payment->payment_method) }}</td>
                    <td>{{ $payment->count }}</td>
                    <td>Bs. {{ number_format($payment->total, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="3">Sin datos</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">TOP 5 PRODUCTOS MÁS VENDIDOS</div>
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad Vendida</th>
                </tr>
            </thead>
            <tbody>
                @forelse(($stats['topProducts'] ?? []) as $item)
                <tr>
                    <td>{{ $item->product->name ?? 'Producto eliminado' }}</td>
                    <td>{{ $item->total_qty }} unidades</td>
                </tr>
                @empty
                <tr><td colspan="2">Sin datos</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">DETALLE POR CATEGORÍA</div>
        <table>
            <thead>
                <tr>
                    <th>Categoría</th>
                    <th>Monto Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse(($stats['salesByCategory'] ?? []) as $cat)
                <tr>
                    <td>{{ $cat->name }}</td>
                    <td>Bs. {{ number_format($cat->total, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="2">Sin datos</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">
        Generado automáticamente por SIS-POLLERIA CRISPY el {{ $generated_at ?? 'N/A' }}
    </div>
</body>
</html>
