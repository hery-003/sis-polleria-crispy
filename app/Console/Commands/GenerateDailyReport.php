<?php

namespace App\Console\Commands;

use App\Mail\DailyReportMail;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

use function Laravel\Prompts\confirm;

#[Signature('app:generate-daily-report {date?} {email?}')]
#[Description('Genera un reporte PDF con el resumen diario de ventas y opcionalmente lo envía por correo')]
class GenerateDailyReport extends Command
{
    public function handle(ReportService $reportService)
    {
        $dateStr = $this->argument('date') ?: now()->toDateString();
        $date = Carbon::parse($dateStr);

        if ($this->input->isInteractive() && ! $this->argument('date')) {
            $dateStr = \Laravel\Prompts\text(
                label: 'Fecha del reporte (YYYY-MM-DD)',
                default: now()->toDateString(),
                required: true
            );
            $date = Carbon::parse($dateStr);
        }

        if (! $this->argument('date') && $dateStr !== now()->toDateString()) {
            $proceed = confirm(
                label: "Generar reporte para la fecha: {$dateStr}?",
                default: true
            );
            if (! $proceed) {
                $this->info('Operación cancelada.');

                return Command::SUCCESS;
            }
        }

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

            $email = $this->argument('email');
            if ($email) {
                Mail::to($email)->send(new DailyReportMail($dateStr, $stats, $filename));
                $this->info("Reporte enviado a: {$email}");
                Log::info("Daily report emailed to {$email} for {$dateStr}");
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error al generar el reporte: '.$e->getMessage());
            Log::error('Failed to generate daily report: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
