<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\FamilyMember;
use App\Models\ProjectFunding;
use App\Models\SavingsContribution;
use App\Models\WalletReconciliation;
use Illuminate\Database\Eloquent\Model;

class AuditLogger
{
    /**
     * Log an application-level event (e.g. login, logout, "user did X").
     */
    public static function application(string $action, ?string $description = null, array $properties = []): ?AuditLog
    {
        return AuditLog::logApplication($action, $description, $properties);
    }

    /**
     * Log a database-level change (model created/updated/deleted with old/new values).
     */
    public static function modelChange(Model $model, string $action, ?array $oldValues = null, ?array $newValues = null): ?AuditLog
    {
        $table = $model->getTable();
        $id = $model->getKey();
        $description = self::describeModelChange($model, $action, $oldValues, $newValues);
        $overrides = [
            'subject_type' => get_class($model),
            'subject_id' => $id,
        ];
        if (isset($model->family_id)) {
            $overrides['family_id'] = $model->family_id;
        }

        // Infer family_id for models that don't have a direct family_id column
        if (! isset($overrides['family_id'])) {
            // Wallet reconciliation has explicit family_id
            if ($model instanceof WalletReconciliation && isset($model->family_id)) {
                $overrides['family_id'] = $model->family_id;
            }

            // Family member pivot uses family_id
            if ($model instanceof FamilyMember && isset($model->family_id)) {
                $overrides['family_id'] = $model->family_id;
            }

            // Savings contribution → goal → family
            if ($model instanceof SavingsContribution) {
                $goal = $model->relationLoaded('goal') ? $model->getRelation('goal') : $model->goal()->first();
                if ($goal && isset($goal->family_id)) {
                    $overrides['family_id'] = $goal->family_id;
                }
            }

            // Project funding → project → family
            if ($model instanceof ProjectFunding) {
                $project = $model->relationLoaded('project') ? $model->getRelation('project') : $model->project()->first();
                if ($project && isset($project->family_id)) {
                    $overrides['family_id'] = $project->family_id;
                }
            }
        }

        return AuditLog::logDatabase($table, $id, $action, $oldValues, $newValues, $description, $overrides);
    }

    protected static function describeModelChange(Model $model, string $action, ?array $oldValues, ?array $newValues): string
    {
        $name = class_basename($model);
        $id = $model->getKey();
        if ($action === AuditLog::ACTION_CREATED) {
            return "Created {$name} #{$id}";
        }
        if ($action === AuditLog::ACTION_DELETED) {
            return "Deleted {$name} #{$id}";
        }
        if ($action === AuditLog::ACTION_UPDATED && is_array($newValues) && count($newValues) > 0) {
            $parts = [];
            foreach (array_keys($newValues) as $key) {
                $old = $oldValues[$key] ?? null;
                $new = $newValues[$key] ?? null;
                if ($old != $new) {
                    $parts[] = "{$key}: " . (is_scalar($old) ? $old : json_encode($old)) . " → " . (is_scalar($new) ? $new : json_encode($new));
                }
            }
            return $parts ? "Updated {$name} #{$id}: " . implode('; ', array_slice($parts, 0, 5)) : "Updated {$name} #{$id}";
        }
        return "Updated {$name} #{$id}";
    }
}
