<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style>
    body { font-family: 'Helvetica', sans-serif; font-size: 12px; }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .font-bold { font-weight: bold; }
    .mb-2 { margin-bottom: 15px; }
    .mb-4 { margin-bottom: 25px; }
    .mt-6 { margin-top: 30px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
    th { background: #f37021; color: white; padding: 8px; text-align: left; }
    td { padding: 8px; border-bottom: 1px solid #ddd; }
    .ok { color: #16a34a; }
    .surplus { color: #ca8a04; }
    .shortage { color: #dc2626; }
    .summary-box { border: 2px solid #f37021; border-radius: 8px; padding: 15px; margin-bottom: 20px; }
</style>
</head>
<body>
    <div class="text-center mb-4">
        <h1>{{ config('app.name') }}</h1>
        <p>Reporte de Cierre de Caja</p>
    </div>

    <div class="summary-box">
        <table>
            <tr>
                <td><strong>Apertura:</strong></td>
                <td>{{ $register->opened_at->format('d/m/Y H:i') }}</td>
                <td><strong>Cajero:</strong></td>
                <td>{{ $register->user?->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Cierre:</strong></td>
                <td>{{ $register->closed_at?->format('d/m/Y H:i') ?? 'N/A' }}</td>
                <td><strong>Duración:</strong></td>
                <td>
                    @if($register->opened_at && $register->closed_at)
                        {{ $register->opened_at->diffInHours($register->closed_at) }}h 
                        {{ $register->opened_at->diff($register->closed_at)->i }}m
                    @else
                        N/A
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <table>
        <tr>
            <th colspan="2">Resumen Financiero</th>
        </tr>
        <tr>
            <td>Saldo Inicial</td>
            <td class="text-right">Bs. {{ number_format($summary['opening_balance'], 2) }}</td>
        </tr>
        <tr>
            <td>Ventas en Efectivo</td>
            <td class="text-right">Bs. {{ number_format($summary['cash_sales'], 2) }}</td>
        </tr>
        <tr>
            <td>Ingresos Extra</td>
            <td class="text-right">Bs. {{ number_format($summary['cash_in'], 2) }}</td>
        </tr>
        <tr>
            <td>Egresos Extra</td>
            <td class="text-right">(Bs. {{ number_format($summary['cash_out'], 2) }})</td>
        </tr>
        <tr style="font-weight: bold; border-top: 2px solid #000;">
            <td>Efectivo Esperado</td>
            <td class="text-right">Bs. {{ number_format($summary['expected_cash'], 2) }}</td>
        </tr>
        <tr style="font-weight: bold;">
            <td>Efectivo Real</td>
            <td class="text-right">Bs. {{ number_format($summary['actual_cash'], 2) }}</td>
        </tr>
        <tr style="font-weight: bold; font-size: 14px;">
            <td>Diferencia</td>
            <td class="text-right {{ $summary['difference_status'] }}">
                {{ $summary['difference'] >= 0 ? '+' : '' }}Bs. {{ number_format($summary['difference'], 2) }}
                ({{ $summary['difference_status'] === 'ok' ? 'CUADRE EXACTO' : ($summary['difference_status'] === 'surplus' ? 'SOBRANTE' : 'FALTANTE') }})
            </td>
        </tr>
    </table>

    <table>
        <tr>
            <th colspan="2">Estadísticas</th>
        </tr>
        <tr>
            <td>Pedidos Cobrados</td>
            <td class="text-right">{{ $summary['total_orders'] }}</td>
        </tr>
        <tr>
            <td>Movimientos Registrados</td>
            <td class="text-right">{{ $summary['movements_count'] }}</td>
        </tr>
    </table>

    @if($register->notes)
    <div style="margin-top: 20px; padding: 10px; background: #f5f5f5; border-radius: 8px;">
        <strong>Observaciones:</strong>
        <p>{{ $register->notes }}</p>
    </div>
    @endif

    <div class="text-center mt-6" style="color: #666; font-size: 10px;">
        <p>Generado el {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
