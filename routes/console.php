<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Reporte diario automático
Schedule::command('app:generate-daily-report')->dailyAt('23:59');

// Limpieza semanal del sistema
Schedule::command('app:system-cleanup')->weeklyOn(0, '03:00');
