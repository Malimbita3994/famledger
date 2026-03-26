<?php

namespace App\Services;

use App\Models\Wallet;
use Illuminate\Support\Collection;

/**
 * Pessimistic locking for wallet rows to reduce race conditions on concurrent debits/credits.
 */
final class WalletBalanceGuard
{
    /**
     * Lock wallets in stable (ascending id) order to avoid deadlocks, then return keyed by id.
     *
     * @param  array<int>  $walletIds
     */
    public static function lockWalletsForUpdate(array $walletIds): Collection
    {
        $ids = array_values(array_unique(array_map('intval', $walletIds)));
        sort($ids);

        if ($ids === []) {
            return collect();
        }

        return Wallet::query()
            ->whereIn('id', $ids)
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');
    }
}
