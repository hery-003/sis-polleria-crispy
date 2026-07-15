<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditObserver
{
    public function created(Model $model): void
    {
        AuditLog::log(
            $this->actionName($model, 'created'),
            class_basename($model),
            $model->id,
            null,
            $model->toArray()
        );
    }

    public function updated(Model $model): void
    {
        if (empty($model->getChanges())) return;

        $old = collect($model->getChanges())->mapWithKeys(
            fn ($val, $key) => [$key => $model->getOriginal($key)]
        )->toArray();

        AuditLog::log(
            $this->actionName($model, 'updated'),
            class_basename($model),
            $model->id,
            $old,
            $model->fresh()->toArray()
        );
    }

    public function deleted(Model $model): void
    {
        AuditLog::log(
            $this->actionName($model, 'deleted'),
            class_basename($model),
            $model->id,
            $model->toArray(),
            null
        );
    }

    protected function actionName(Model $model, string $event): string
    {
        $name = str(class_basename($model))->snake()->lower();
        return "{$name}_{$event}";
    }
}
