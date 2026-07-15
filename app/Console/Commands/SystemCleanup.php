<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\progress;

#[Signature('app:system-cleanup')]
#[Description('Limpia archivos temporales y logs antiguos del sistema')]
class SystemCleanup extends Command
{
    public function handle()
    {
        if ($this->input->isInteractive()) {
            $proceed = confirm(
                label: '¿Estás seguro de iniciar la limpieza del sistema?',
                default: false
            );
            if (! $proceed) {
                $this->info('Limpieza cancelada.');

                return Command::SUCCESS;
            }
        }

        $this->info('Iniciando limpieza del sistema...');

        // 1. Limpiar recibos temporales de más de 48 horas
        $countFiles = 0;
        foreach (['local', 'public'] as $disk) {
            $files = Storage::disk($disk)->files('receipts');
            foreach ($files as $file) {
                if (Storage::disk($disk)->lastModified($file) < now()->subHours(48)->getTimestamp()) {
                    Storage::disk($disk)->delete($file);
                    $countFiles++;
                }
            }
        }
        $this->info("- Se eliminaron {$countFiles} archivos de recibos antiguos.");

        // 2. Limpiar logs de auditoría de más de 6 meses (por lotes para evitar locks)
        $countLogs = 0;
        $oldLogs = AuditLog::where('created_at', '<', now()->subMonths(6));
        $totalLogs = $oldLogs->count();

        if ($totalLogs > 0) {
            $progress = progress(
                label: "Eliminando {$totalLogs} registros de auditoría antiguos...",
                steps: $totalLogs
            );

            $progress->start();
            $oldLogs->chunkById(100, function ($logs) use (&$countLogs, $progress) {
                foreach ($logs as $log) {
                    $log->delete();
                    $countLogs++;
                    $progress->advance();
                }
            });
            $progress->finish();
        }

        $this->info("- Se eliminaron {$countLogs} registros de auditoría antiguos.");

        // 3. Limpiar caché expirada de la base de datos (si se usa database como driver)
        if (config('cache.default') === 'database') {
            DB::table('cache')->where('expiration', '<', time())->delete();
            $this->info('- Se limpió la tabla de caché de la base de datos.');
        }

        Log::info("System cleanup completed. Files removed: {$countFiles}, Logs removed: {$countLogs}");
        $this->info('Limpieza completada exitosamente.');

        return Command::SUCCESS;
    }
}
