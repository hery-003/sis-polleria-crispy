<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\PrintService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class PrintController extends Controller
{
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
            return redirect()->back()->with('error', 'Error al imprimir: ' . $e->getMessage());
        }
    }

    public function kitchen(Order $order)
    {
        try {
            $filePath = $this->printService->printKitchenOrder($order);

            return response()->download($filePath, "comanda_{$order->order_number}.txt", [
                'Content-Type' => 'text/plain',
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al imprimir comanda: ' . $e->getMessage());
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

            $filename = $type === 'kitchen'
                ? 'kitchen/order_' . $order->order_number . '_' . filemtime($filePath) . '.txt'
                : 'receipts/order_' . $order->order_number . '_' . filemtime($filePath) . '.txt';
            return response()->json([
                'success' => true,
                'file_url' => Storage::disk('public')->url($filename),
                'message' => 'Impresión generada correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al imprimir: ' . $e->getMessage()
            ], 500);
        }
    }
}
