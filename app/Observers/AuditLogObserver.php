<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Services\AuditLogger;
use Illuminate\Database\Eloquent\Model;

class AuditLogObserver
{
    /**
     * Fields to exclude from audit payload (sensitive or noisy).
     */
    protected array $exclude = ['password', 'remember_token', 'created_at', 'updated_at'];

    public function created(Model $model): void
    {
        $this->audit($model, AuditLog::ACTION_CREATED, null, $this->filterAttributes($model->getAttributes()));
    }

    public function updated(Model $model): void
    {
        $changes = $model->getChanges();
        if (empty($changes)) {
            return;
        }
        $old = [];
        $new = [];
        foreach (array_keys($changes) as $key) {
            if (in_array($key, $this->exclude, true)) {
                continue;
            }
            $old[$key] = $model->getOriginal($key);
            $new[$key] = $changes[$key];
        }
        if (empty($new)) {
            return;
        }
        $this->audit($model, AuditLog::ACTION_UPDATED, $old, $new);
    }

    public function deleted(Model $model): void
    {
        $this->audit($model, AuditLog::ACTION_DELETED, $this->filterAttributes($model->getRawOriginal() ?: $model->getAttributes()), null);
    }

    protected function audit(Model $model, string $action, ?array $oldValues, ?array $newValues): void
    {
        try {
            AuditLogger::modelChange($model, $action, $oldValues, $newValues);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    protected function filterAttributes(array $attrs): array
    {
        return array_diff_key($attrs, array_flip($this->exclude));
    }
}
