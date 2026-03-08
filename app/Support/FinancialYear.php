<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * FamLedger financial year: start/end dates from config (default Jan 1 – Dec 31).
 */
class FinancialYear
{
    public static function start(?int $year = null): Carbon
    {
        $year ??= (int) now()->format('Y');
        $month = config('famledger.financial_year.start_month', 1);
        $day = config('famledger.financial_year.start_day', 1);

        return Carbon::createFromDate($year, $month, $day)->startOfDay();
    }

    public static function end(?int $year = null): Carbon
    {
        $year ??= (int) now()->format('Y');
        $month = config('famledger.financial_year.end_month', 12);
        $day = config('famledger.financial_year.end_day', 31);

        return Carbon::createFromDate($year, $month, $day)->endOfDay();
    }

    /**
     * Return [start, end] for the given year (default current year).
     */
    public static function range(?int $year = null): array
    {
        $year ??= (int) now()->format('Y');

        return [
            self::start($year),
            self::end($year),
        ];
    }

    /**
     * Label for the financial year (e.g. "2025" for Jan–Dec 2025).
     */
    public static function label(?int $year = null): string
    {
        $year ??= (int) now()->format('Y');
        $startMonth = config('famledger.financial_year.start_month', 1);
        $endMonth = config('famledger.financial_year.end_month', 12);

        if ($startMonth <= $endMonth) {
            return (string) $year;
        }

        return $year . '/' . ($year + 1);
    }
}
