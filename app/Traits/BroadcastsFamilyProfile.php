<?php

namespace App\Traits;

use App\Events\FamilyProfileUpdated;
use App\Services\FamilyFinancialService;
use Illuminate\Database\Eloquent\Model;

trait BroadcastsFamilyProfile
{
    protected static function bootBroadcastsFamilyProfile(): void
    {
        static::created(function (Model $model) {
            static::triggerProfileUpdate($model);
        });
        static::updated(function (Model $model) {
            static::triggerProfileUpdate($model);
        });
        static::deleted(function (Model $model) {
            static::triggerProfileUpdate($model);
        });
    }

    protected static function triggerProfileUpdate(Model $model): void
    {
        $familyId = $model->family_id ?? null;
        if (! $familyId && method_exists($model, 'user') && $model->user) {
            $familyId = $model->user->family_id;
        }

        if (! $familyId) {
            return;
        }

        $service = app(FamilyFinancialService::class);
        $healthIndex = $service->getFamilyHealthIndex($familyId);
        $leaderboard = $service->getContributionLeaderboard($familyId);
        $ledgerTotals = $service->getProfileLedgerSummary($familyId);

        broadcast(new FamilyProfileUpdated($familyId, $healthIndex, $leaderboard, $ledgerTotals));
    }
}
