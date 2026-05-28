<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ReportService;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class GenerateDailyReport extends Command
{
    protected $signature = 'app:generate-daily-report {date?}';
    protected $description = 'Genera un reporte PDF con el resumen diario de ventas';

    public function handle(ReportService $reportService)
    {
        $dateStr = $this->argument('date') ?: now()->toDateString();
        $date = Carbon::parse($dateStr);
        
        $this->info("Generando reporte para la fecha: {$dateStr}...");

        try {
            $reportService->clearCache();
            $summary = $reportService->getDailySummary($date);
            $stats = $reportService->getStats($date->copy()->startOfDay(), $date->copy()->endOfDay());

            $data = [
                'date' => $dateStr,
                'summary' => $summary,
                'stats' => $stats,
                'generated_at' => now()->toDateTimeString(),
            ];

            $pdf = Pdf::loadView('reports.daily_pdf', $data);
            
            $filename = "reports/daily_report_{$dateStr}.pdf";
            Storage::disk('local')->put($filename, $pdf->output());

            $this->info("Reporte generado exitosamente: {$filename}");
            Log::info("Daily report generated for {$dateStr}");

        } catch (\Exception $e) {
            $this->error("Error al generar el reporte: " . $e->getMessage());
            Log::error("Failed to generate daily report: " . $e->getMessage());
        }
    }
}
