<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style>
    body { font-family: 'Courier New', monospace; font-size: 10px; width: 80mm; margin: 0 auto; text-transform: uppercase; }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .font-bold { font-weight: bold; }
    .border-dashed { border-top: 1px dashed #000; }
    .mb-2 { margin-bottom: 10px; }
    .mb-4 { margin-bottom: 20px; }
    .mt-6 { margin-top: 30px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 4px 0; }
    th { border-bottom: 1px dashed #000; font-size: 9px; }
    .logo { max-width: 80px; max-height: 80px; }
</style>
</head>
<body>
    <div class="text-center mb-4">
        <h1 style="font-size: 14px;">{{ config('app.name') }}</h1>
        <p style="font-size: 9px;">"Crujiente y Sabor Real!"</p>
        @if($order->metodoPago?->qr_image)
            <img src="{{ storage_path('app/public/'.$order->metodoPago->qr_image) }}" style="width:60px;height:60px;margin-top:5px;" alt="QR" />
        @endif
    </div>

    <div class="border-dashed mb-2" style="padding: 5px 0;">
        <div><span>Ticket:</span> <span class="font-bold">{{ $order->order_number }}</span></div>
        <div><span>Fecha:</span> <span>{{ $order->created_at->format('d/m/Y H:i') }}</span></div>
        <div><span>Cajero:</span> <span>{{ $order->user?->name ?? 'N/A' }}</span></div>
        @if($order->mesa)
            <div><span>Mesa:</span> <span class="font-bold">{{ $order->mesa->name ?? $order->mesa->number }}</span></div>
        @endif
        <div><span>Tipo:</span> <span class="font-bold">{{ $order->type === 'dine_in' ? 'MESA' : ($order->type === 'take_out' ? 'LLEVAR' : 'DELIVERY') }}</span></div>
    </div>

    <table class="mb-2">
        <thead>
            <tr>
                <th style="text-align:left;">Cant</th>
                <th style="text-align:left;">Producto</th>
                <th style="text-align:right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>{{ $item->quantity }}</td>
                <td>
                    {{ $item->product->name }}
                    @if($item->variant)
                        <div style="font-size:9px;">{{ $item->variant->name }}</div>
                    @endif
                </td>
                <td style="text-align:right;">Bs. {{ number_format($item->price * $item->quantity, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="border-dashed" style="padding-top: 5px;">
        <div style="display:flex;justify-content:space-between;font-weight:bold;font-size:14px;">
            <span>TOTAL:</span>
            <span>Bs. {{ number_format($order->total_amount, 2) }}</span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:9px;margin-top:3px;">
            <span>Pago:</span>
            <span>{{ strtoupper($order->payment_method ?? 'N/A') }}</span>
        </div>
        @if($order->received_amount)
            <div style="display:flex;justify-content:space-between;font-size:9px;">
                <span>Recibido:</span>
                <span>Bs. {{ number_format($order->received_amount, 2) }}</span>
            </div>
        @endif
        @if($order->change)
            <div style="display:flex;justify-content:space-between;font-size:9px;">
                <span>Cambio:</span>
                <span>Bs. {{ number_format($order->change, 2) }}</span>
            </div>
        @endif
    </div>

    <div class="text-center mt-6" style="font-size:9px;">
        <p>Gracias por su preferencia!</p>
        <p style="font-style:italic;">Vuelva pronto por el mejor sabor</p>
    </div>
</body>
</html>
