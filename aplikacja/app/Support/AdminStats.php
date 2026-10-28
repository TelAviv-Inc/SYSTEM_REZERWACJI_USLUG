<?php

namespace App\Support;

use App\Models\Reservation;
use Illuminate\Support\Carbon;

class AdminStats
{
    // weeks => label of the period select, shared by every weekly chart
    public const PERIODS = [4 => '4 tygodnie', 12 => '12 tygodni', 52 => '52 tygodnie'];

    /**
     * The period if it's one of PERIODS, 12 weeks otherwise.
     */
    public static function period(int $weeks): int
    {
        return array_key_exists($weeks, self::PERIODS) ? $weeks : 12;
    }

    /**
     * Number of non-cancelled reservations per week, for the last $weeks weeks
     * (the current week included). Weeks without reservations are returned as 0.
     * Pass $employeeId to count only that employee's reservations.
     *
     * @return array{labels: list<string>, values: list<int>}
     */
    public static function weeklyReservations(int $weeks = 12, ?string $employeeId = null): array
    {
        $from = now()->startOfWeek(Carbon::MONDAY)->subWeeks($weeks - 1);
        $to = now()->endOfWeek(Carbon::SUNDAY);

        // whereDate, because the date cast can store "Y-m-d 00:00:00" and a plain
        // string comparison would then drop the last day of the range
        $counts = Reservation::where('status', '!=', 'cancelled')
            ->when($employeeId, fn ($q) => $q->where('employee_id', $employeeId))
            ->whereDate('reservation_date', '>=', $from)
            ->whereDate('reservation_date', '<=', $to)
            ->pluck('reservation_date')
            ->countBy(fn (Carbon $date) => $date->startOfWeek(Carbon::MONDAY)->toDateString());

        $labels = [];
        $values = [];

        for ($i = 0; $i < $weeks; $i++) {
            $week = $from->copy()->addWeeks($i);
            $labels[] = $week->format('d.m');
            $values[] = $counts[$week->toDateString()] ?? 0;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * Income from completed reservations per week (sum of the price stored on the
     * reservation), for the last $weeks weeks (the current week included).
     * Weeks without income are returned as 0.0.
     *
     * @return array{labels: list<string>, values: list<float>}
     */
    public static function weeklyIncome(int $weeks = 12): array
    {
        $from = now()->startOfWeek(Carbon::MONDAY)->subWeeks($weeks - 1);
        $to = now()->endOfWeek(Carbon::SUNDAY);

        $income = Reservation::where('status', 'completed')
            ->whereDate('reservation_date', '>=', $from)
            ->whereDate('reservation_date', '<=', $to)
            ->get(['reservation_date', 'price'])
            ->groupBy(fn (Reservation $r) => $r->reservation_date->startOfWeek(Carbon::MONDAY)->toDateString())
            ->map(fn ($group) => (float) $group->sum('price'));

        $labels = [];
        $values = [];

        for ($i = 0; $i < $weeks; $i++) {
            $week = $from->copy()->addWeeks($i);
            $labels[] = $week->format('d.m');
            $values[] = $income[$week->toDateString()] ?? 0.0;
        }

        return ['labels' => $labels, 'values' => $values];
    }
}
