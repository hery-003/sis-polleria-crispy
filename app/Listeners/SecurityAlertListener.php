<?php

namespace App\Listeners;

use App\Events\SecurityAlert;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Log;

class SecurityAlertListener
{
    public function handle(SecurityAlert $event): void
    {
        AuditLog::log('security_alert', 'Security', null, null, [
            'user_name' => $event->user->name,
            'user_id' => $event->user->id,
            'type' => $event->type,
            'message' => $event->message,
            'data' => $event->data,
        ]);

        Log::critical("Alerta de seguridad: {$event->message}", [
            'user' => $event->user->name,
            'type' => $event->type,
            'data' => $event->data,
        ]);
    }
}
