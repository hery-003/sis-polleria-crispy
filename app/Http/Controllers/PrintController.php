<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Attributes\Middleware;
use App\Models\Order;
use App\Services\PrintService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PrintController extends Controller
{
    #[Middleware(['auth', 'role:admin,cashier,waiter', 'throttle:30,1'])]
    public function __construct(
        protected PrintService $printService
    ) {}

    public function receipt(Order $order)
    {
        try {
            $filePath = $this->printService->printOrderReceipt($order);

            return response()->download($filePath, "recibo_{$order->order_number}.txt", [
                'Content-Type' => 'text/plain',
            ]);
        } catch (\Exception $e) {
            Log::error('Error printing receipt: '.$e->getMessage());
            return redirect()->back()->with('error', 'Error al imprimir: '.$e->getMessage());
        }
    }

    public function pdf(Order $order)
    {
        $order->load(['items.product', 'items.variant', 'user', 'mesa', 'metodoPago']);

        $pdf = Pdf::loadView('receipts.ticket', [
            'order' => $order,
        ]);

        return $pdf->download("ticket_{$order->order_number}.pdf");
    }

    public function kitchen(Order $order)
    {
        try {
            $filePath = $this->printService->printKitchenOrder($order);

            return response()->download($filePath, "comanda_{$order->order_number}.txt", [
                'Content-Type' => 'text/plain',
            ]);
        } catch (\Exception $e) {
            Log::error('Error printing kitchen order: '.$e->getMessage());
            return redirect()->back()->with('error', 'Error al imprimir comanda: '.$e->getMessage());
        }
    }

    public function reprint(Order $order)
    {
        try {
            $filePath = $this->printService->printOrderReceipt($order);

            return response()->download($filePath, "reimpresion_{$order->order_number}.txt", [
                'Content-Type' => 'text/plain',
            ]);
        } catch (\Exception $e) {
            Log::error('Error reprinting receipt: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al reimprimir: ' . $e->getMessage());
        }
    }

    public function autoPrint(Order $order, Request $request)
    {
        $type = $request->get('type', 'receipt');

        try {
            if ($type === 'kitchen') {
                $filePath = $this->printService->printKitchenOrder($order);
            } else {
                $filePath = $this->printService->printOrderReceipt($order);
            }

            $timestamp = file_exists($filePath) ? filemtime($filePath) : time();
            $filename = $type === 'kitchen'
                ? 'kitchen/order_'.$order->order_number.'_'.$timestamp.'.txt'
                : 'receipts/order_'.$order->order_number.'_'.$timestamp.'.txt';

            return response()->json([
                'success' => true,
                'file_url' => Storage::disk('public')->url($filename),
                'message' => 'Impresión generada correctamente',
            ]);

        } catch (\Exception $e) {
            Log::error('Error in auto-print: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al imprimir: '.$e->getMessage(),
            ], 500);
        }
    }
}
