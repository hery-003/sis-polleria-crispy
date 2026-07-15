<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Attributes\Authorize;
use Illuminate\Routing\Attributes\Middleware;
use App\Models\Order;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    #[Middleware(['auth', 'role:admin'])]
    #[Authorize('manage-reports')]
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

        $comparison = null;
        if ($request->compare_start && $request->compare_end) {
            $compareStart = Carbon::parse($request->compare_start);
            $compareEnd = Carbon::parse($request->compare_end);
            $comparison = $this->reportService->getComparison(
                $startDate, $endDate,
                $compareStart, $compareEnd
            );
        }

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
                'salesByUser' => $this->reportService->getSalesByUser($startDate, $endDate),
            ],
            'orders' => $orders,
            'filters' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'compare_start' => $request->compare_start,
                'compare_end' => $request->compare_end,
            ],
            'comparison' => $comparison,
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

        $fileName = "reporte_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}.csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ];

        return response()->stream(function () use ($startDate, $endDate) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, ['Número', 'Fecha', 'Cliente', 'Total', 'Estado', 'Tipo', 'Método']);

            Order::with(['user'])
                ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
                ->orderBy('created_at', 'desc')
                ->chunk(200, function ($orders) use ($file) {
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
                });

            fclose($file);
        }, 200, $headers);
    }

    public function exportExcel(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::today();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::today();

        $orders = Order::with(['user', 'items.product', 'items.variant'])
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->orderBy('created_at', 'desc')
            ->get();

        return Excel::download(
            new class($orders) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
                protected $orders;
                public function __construct($orders) { $this->orders = $orders; }
                public function collection() {
                    return $this->orders->map(fn($o) => [
                        $o->order_number,
                        $o->created_at->format('Y-m-d H:i'),
                        $o->user?->name ?? 'N/A',
                        $o->type,
                        $o->payment_method,
                        $o->status,
                        $o->total_amount,
                    ]);
                }
                public function headings(): array {
                    return ['Pedido', 'Fecha', 'Cajero', 'Tipo', 'Método', 'Estado', 'Total'];
                }
            },
            "reporte_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}.xlsx"
        );
    }
}
