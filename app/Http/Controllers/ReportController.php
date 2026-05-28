<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function index(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::today();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::today();

        $stats = $this->reportService->getStats($startDate, $endDate);

        // Obtener lista detallada de pedidos para la tabla
        $orders = Order::with(['user', 'items.product', 'items.variant'])
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Reports/Index', [
            'stats' => [
                'totalSales' => $stats['totalSales'],
                'ordersCount' => $stats['ordersCount'],
                'cancellations' => $stats['cancellations'],
                'netIncome' => $stats['netIncome'],
                'avgTicket' => $stats['avgTicket'],
                'topProducts' => $stats['topProducts'],
                'salesByPayment' => $stats['salesByPayment'],
                'salesByDay' => $stats['salesByDay'],
                'salesByHour' => $stats['salesByHour'],
                'salesByType' => $stats['salesByType'],
            ],
            'orders' => $orders,
            'filters' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
        ]);
    }

    public function exportPdf(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::today();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::today();

        $stats = $this->reportService->getStats($startDate, $endDate);

        $orders = Order::with(['user', 'items.product', 'items.variant'])
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->orderBy('created_at', 'desc')
            ->get();

        $pdf = Pdf::loadView('reports.pdf', [
            'stats' => $stats,
            'orders' => $orders,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);

        return $pdf->download("reporte_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}.pdf");
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::today();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::today();

        $orders = Order::with(['user', 'items'])
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->orderBy('created_at', 'desc')
            ->get();

        $fileName = "reporte_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}.csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ];

        return response()->stream(function () use ($orders) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Número', 'Fecha', 'Cliente', 'Total', 'Estado', 'Tipo', 'Método']);

            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->order_number,
                    $order->created_at->format('Y-m-d H:i'),
                    $order->user?->name ?? 'N/A',
                    $order->total_amount,
                    $order->status,
                    $order->type,
                    $order->payment_method,
                ]);
            }

            fclose($file);
        }, 200, $headers);
    }
}
