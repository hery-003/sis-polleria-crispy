<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

#[Fillable('user_id', 'action', 'model_type', 'model_id', 'old_values', 'new_values', 'ip_address', 'user_agent')]
class AuditLog extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function log($action, $modelType = null, $modelId = null, $oldValues = null, $newValues = null, ?int $userId = null)
    {
        $log = self::create([
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()?->ip() ?? null,
            'user_agent' => request()?->userAgent() ?? null,
        ]);

        Cache::tags(['audit_logs'])->flush();

        return $log;
    }
}
