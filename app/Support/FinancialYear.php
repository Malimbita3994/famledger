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
     *
     * Note: If the fiscal year spans two calendar years (e.g. July–June), this is not the
     * correct current period—use {@see currentPeriod()} instead.
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
     * The financial year period that contains "now" (respects split FY e.g. July–June).
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    public static function currentPeriod(): array
    {
        $now = Carbon::now();
        $sm = config('famledger.financial_year.start_month', 1);
        $sd = config('famledger.financial_year.start_day', 1);
        $em = config('famledger.financial_year.end_month', 12);
        $ed = config('famledger.financial_year.end_day', 31);

        if ($sm <= $em) {
            $y = (int) $now->format('Y');
            $start = Carbon::createFromDate($y, $sm, $sd)->startOfDay();
            $end = Carbon::createFromDate($y, $em, $ed)->endOfDay();
            if ($now->lt($start)) {
                $y -= 1;
                $start = Carbon::createFromDate($y, $sm, $sd)->startOfDay();
                $end = Carbon::createFromDate($y, $em, $ed)->endOfDay();
            }

            return [$start, $end];
        }

        // Split FY (e.g. July 1 – June 30): end month is in the year after start.
        $y = (int) $now->format('Y');
        $startThisYear = Carbon::createFromDate($y, $sm, $sd)->startOfDay();
        if ($now->gte($startThisYear)) {
            $start = $startThisYear;
            $end = Carbon::createFromDate($y + 1, $em, $ed)->endOfDay();
        } else {
            $start = Carbon::createFromDate($y - 1, $sm, $sd)->startOfDay();
            $end = Carbon::createFromDate($y, $em, $ed)->endOfDay();
        }

        return [$start, $end];
    }

    /**
     * Human label for the {@see currentPeriod()} (e.g. "2026" or "2025/2026").
     */
    public static function currentLabel(): string
    {
        [$start, $end] = self::currentPeriod();
        $sm = config('famledger.financial_year.start_month', 1);
        $em = config('famledger.financial_year.end_month', 12);
        if ($sm <= $em) {
            return (string) $start->year;
        }

        return $start->year.'/'.$end->year;
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

        return $year.'/'.($year + 1);
    }
}
