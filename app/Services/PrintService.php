<?php

namespace App\Services;

use App\Models\Order;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PrintService
{
    protected $printer;
    protected $connector;

    public function __construct()
    {
        $this->connector = null;
    }

    protected function getConnector(?string $filePath = null)
    {
        $printerType = config('printing.driver', 'file');
        
        switch ($printerType) {
            case 'network':
                $ip = config('printing.network.ip', '127.0.0.1');
                $port = config('printing.network.port', 9100);
                return new NetworkPrintConnector($ip, $port);
                
            case 'file':
            default:
                $path = $filePath ?? Storage::disk('local')->path('receipts/print_' . uniqid() . '.txt');
                return new FilePrintConnector($path);
        }
    }

    public function printOrderReceipt(Order $order): string
    {
        $filename = "receipts/order_{$order->order_number}_" . time() . ".txt";
        $fullPath = Storage::disk('public')->path($filename);

        try {
            Storage::disk('public')->makeDirectory('receipts');
            $this->connector = $this->getConnector($fullPath);
            $this->printer = new Printer($this->connector);

            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->setTextSize(2, 2);
            $this->printer->text("POLLO BROSTER CRISPY\n");
            $this->printer->setTextSize(1, 1);
            $this->printer->text("Crujiente y Sabor Real!\n");
            $this->printer->text("================================\n");

            $this->printer->setJustification(Printer::JUSTIFY_LEFT);
            $this->printer->text("Orden: " . $order->order_number . "\n");
            $this->printer->text("Fecha: " . $order->created_at->format('d/m/Y H:i:s') . "\n");
            $this->printer->text("Cajero: " . ($order->user->name ?? 'N/A') . "\n");

            if ($order->mesa) {
                $this->printer->text("Mesa: " . $order->mesa->number . "\n");
            }

            $this->printer->text("Tipo: " . $this->getOrderTypeText($order->type) . "\n");
            $this->printer->text("--------------------------------\n");

            foreach ($order->items as $item) {
                $productName = $item->product->name ?? 'Producto';
                $variantName = $item->variant->name ?? '';
                $name = $variantName ? "$productName ($variantName)" : $productName;

                // Truncate if too long
                if (strlen($name) > 30) {
                    $name = substr($name, 0, 27) . '...';
                }

                $this->printer->text(sprintf(
                    "%-30s x%d\n",
                    $name,
                    $item->quantity
                ));
                $this->printer->text(sprintf(
                    "%30s Bs. %.2f\n",
                    "",
                    $item->subtotal
                ));
            }

            $this->printer->text("--------------------------------\n");
            $this->printer->setJustification(Printer::JUSTIFY_RIGHT);
            $this->printer->text("TOTAL: Bs. " . number_format($order->total_amount, 2) . "\n");

            $this->printer->setJustification(Printer::JUSTIFY_LEFT);
            $this->printer->text("Método: " . $this->getPaymentMethodText($order) . "\n");
            $this->printer->text("Estado: " . $this->getStatusText($order->status) . "\n");

            if ($order->notes) {
                $this->printer->text("--------------------------------\n");
                $this->printer->text("Notas: " . $order->notes . "\n");
            }

            $this->printer->text("================================\n");
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->text("¡Gracias por su compra!\n");
            $this->printer->text("Vuelva pronto\n");
            $this->printer->feed(3);
            $this->printer->cut();

            $this->printer->close();

            return $fullPath;

        } catch (\Exception $e) {
            if ($this->printer) {
                $this->printer->close();
            }
            Log::error('Print error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function printKitchenOrder(Order $order): string
    {
        $filename = "kitchen/order_{$order->order_number}_" . time() . ".txt";
        $fullPath = Storage::disk('public')->path($filename);

        try {
            Storage::disk('public')->makeDirectory('kitchen');
            $this->connector = $this->getConnector($fullPath);
            $this->printer = new Printer($this->connector);

            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->setTextSize(2, 2);
            $this->printer->text("COMANDA #" . $order->order_number . "\n");
            $this->printer->setTextSize(1, 1);
            $this->printer->text("================================\n");

            $this->printer->setJustification(Printer::JUSTIFY_LEFT);
            $this->printer->text("Hora: " . $order->created_at->format('H:i:s') . "\n");

            if ($order->mesa) {
                $this->printer->text("MESA: " . $order->mesa->number . "\n");
            }

            $this->printer->text("Tipo: " . $this->getOrderTypeText($order->type) . "\n");
            $this->printer->text("--------------------------------\n");
            $this->printer->setTextSize(1, 2);

            foreach ($order->items as $item) {
                $productName = $item->product->name ?? 'Producto';
                $variantName = $item->variant->name ?? '';
                $name = $variantName ? "$productName ($variantName)" : $productName;

                $this->printer->text("[$item->quantity] $name\n");
            }

            $this->printer->setTextSize(1, 1);

            if ($order->notes) {
                $this->printer->text("--------------------------------\n");
                $this->printer->text("NOTAS: " . $order->notes . "\n");
            }

            $this->printer->text("================================\n");
            $this->printer->feed(3);
            $this->printer->cut();

            $this->printer->close();

            return $fullPath;

        } catch (\Exception $e) {
            if ($this->printer) {
                $this->printer->close();
            }
            Log::error('Kitchen print error: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function getOrderTypeText(string $type): string
    {
        return match($type) {
            'dine_in' => 'En Mesa',
            'take_out' => 'Para Llevar',
            'delivery' => 'Delivery',
            default => ucfirst($type),
        };
    }

    protected function getPaymentMethodText(Order $order): string
    {
        if ($order->metodoPago) {
            return $order->metodoPago->name;
        }

        return match($order->payment_method) {
            'cash' => 'Efectivo',
            'card' => 'Tarjeta',
            'yape' => 'Yape',
            default => ucfirst($order->payment_method ?? 'N/A'),
        };
    }

    protected function getStatusText(string $status): string
    {
        return match($status) {
            'pending' => 'Pendiente',
            'cooking' => 'En Cocina',
            'ready' => 'Listo',
            'completed' => 'Entregado',
            'cancelled' => 'Cancelado',
            default => ucfirst($status),
        };
    }
}
