<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\AuditLog;

class SystemCleanup extends Command
{
    protected $signature = 'app:system-cleanup';
    protected $description = 'Limpia archivos temporales y logs antiguos del sistema';

    public function handle()
    {
        $this->info("Iniciando limpieza del sistema...");

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
        AuditLog::where('created_at', '<', now()->subMonths(6))
            ->chunkById(100, function ($logs) use (&$countLogs) {
                foreach ($logs as $log) {
                    $log->delete();
                    $countLogs++;
                }
            });
        $this->info("- Se eliminaron {$countLogs} registros de auditoría antiguos.");

        // 3. Limpiar caché expirada de la base de datos (si se usa database como driver)
        if (config('cache.default') === 'database') {
            \Illuminate\Support\Facades\DB::table('cache')->where('expiration', '<', time())->delete();
            $this->info("- Se limpió la tabla de caché de la base de datos.");
        }

        Log::info("System cleanup completed. Files removed: {$countFiles}, Logs removed: {$countLogs}");
        $this->info("Limpieza completada exitosamente.");
    }
}
