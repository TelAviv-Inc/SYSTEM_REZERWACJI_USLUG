<?php

namespace App\Support;

use App\Models\Employee;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Admin report for one period, grouped by category, service or employee.
 *
 * Income is the price stored on completed reservations; pending and confirmed
 * ones count as expected income, cancelled ones as lost income. Reservation
 * counts leave the cancelled ones out unless the field says otherwise.
 */
class Report
{
    public const TYPES = [
        'categories' => 'Kategorie',
        'services' => 'Usługi',
        'employees' => 'Pracownicy',
    ];

    public const STATUSES = ['completed', 'confirmed', 'pending', 'cancelled'];

    // reports group reservations of services without a category under this key
    protected const NO_CATEGORY = 'none';

    protected ?Collection $reservations = null;

    protected ?Collection $rows = null;

    protected ?Collection $serviceNames = null;

    protected ?Collection $employeeNames = null;

    public function __construct(
        public readonly string $type,
        public readonly Carbon $from,
        public readonly Carbon $to,
        public readonly ?string $categoryId = null,
    ) {}

    public function days(): int
    {
        return (int) $this->from->diffInDays($this->to) + 1;
    }

    // the same number of days right before this period, for the "vs previous period" deltas
    public function previous(): self
    {
        $to = $this->from->copy()->subDay();

        return new self($this->type, $to->copy()->subDays($this->days() - 1), $to, $this->categoryId);
    }

    /**
     * Reservations in the period (cancelled included), one plain object each.
     */
    public function reservations(): Collection
    {
        return $this->reservations ??= DB::table('reservations')
            ->leftJoin('services', 'services.uuid', '=', 'reservations.service_id')
            ->whereDate('reservations.reservation_date', '>=', $this->from->toDateString())
            ->whereDate('reservations.reservation_date', '<=', $this->to->toDateString())
            ->when($this->categoryId, fn ($q) => $q->where('services.category_id', $this->categoryId))
            ->get([
                'reservations.user_id',
                'reservations.employee_id',
                'reservations.service_id',
                'reservations.reservation_date',
                'reservations.start_time',
                'reservations.end_time',
                'reservations.status',
                'reservations.price',
                'services.category_id',
            ])
            ->map(function ($r) {
                // the date cast can store "Y-m-d 00:00:00", so only the date part is used
                $r->date = substr($r->reservation_date, 0, 10);
                $r->hour = (int) substr($r->start_time, 0, 2);
                $r->minutes = max(0, (int) Carbon::parse($r->start_time)->diffInMinutes(Carbon::parse($r->end_time)));
                $r->price = (float) $r->price;
                $r->category_id ??= self::NO_CATEGORY;

                return $r;
            });
    }

    /**
     * Totals for the whole period.
     */
    public function summary(): array
    {
        return $this->stats($this->reservations()) + [
            'new_clients' => $this->newClients(),
        ];
    }

    /**
     * Counts and money for any set of reservations: the summary and every table row use it.
     */
    protected function stats(Collection $reservations): array
    {
        $byStatus = $reservations->groupBy('status');
        $booked = $reservations->where('status', '!=', 'cancelled');
        $count = fn (string $status) => $byStatus->get($status)?->count() ?? 0;
        $sum = fn (string ...$statuses) => (float) collect($statuses)->sum(fn ($s) => $byStatus->get($s)?->sum('price') ?? 0);

        $total = $reservations->count();
        $completed = $count('completed');
        $income = round($sum('completed'), 2);

        return [
            'total' => $total,
            'reservations' => $booked->count(),
            'completed' => $completed,
            'confirmed' => $count('confirmed'),
            'pending' => $count('pending'),
            'cancelled' => $count('cancelled'),
            'income' => $income,
            'expected' => round($sum('pending', 'confirmed'), 2),
            'lost' => round($sum('cancelled'), 2),
            'avg_ticket' => $completed ? round($income / $completed, 2) : 0.0,
            'cancellation_rate' => $total ? round($count('cancelled') / $total * 100, 1) : 0.0,
            'clients' => $booked->pluck('user_id')->unique()->count(),
            'booked_minutes' => (int) $booked->sum('minutes'),
        ];
    }

    // clients whose first booking (in the filtered category, if any) falls in this period
    protected function newClients(): int
    {
        return DB::table('reservations')
            ->leftJoin('services', 'services.uuid', '=', 'reservations.service_id')
            ->where('reservations.status', '!=', 'cancelled')
            ->when($this->categoryId, fn ($q) => $q->where('services.category_id', $this->categoryId))
            ->groupBy('reservations.user_id')
            ->selectRaw('min(reservations.reservation_date) as first_date')
            ->get()
            ->filter(fn ($r) => ($date = substr($r->first_date, 0, 10)) >= $this->from->toDateString() && $date <= $this->to->toDateString())
            ->count();
    }

    /**
     * One row per category, service or employee - also the ones without reservations,
     * so unused services and idle employees show up in the report.
     */
    public function rows(): Collection
    {
        if ($this->rows) {
            return $this->rows;
        }

        $key = ['categories' => 'category_id', 'services' => 'service_id', 'employees' => 'employee_id'][$this->type];
        $grouped = $this->reservations()->groupBy($key);
        $income = $this->reservations()->where('status', 'completed')->sum('price');

        $rows = $this->entities()->map(function (array $entity) use ($grouped, $income) {
            $reservations = $grouped->get($entity['key'], collect());
            $stats = $this->stats($reservations);

            return $entity + $stats + [
                'share' => $income > 0 ? round($stats['income'] / $income * 100, 1) : 0.0,
                'top' => $this->topName($reservations),
            ];
        });

        if ($this->type === 'employees') {
            $rows = $rows->map(fn (array $row) => $row + [
                'utilization' => $row['available_minutes'] ? round($row['booked_minutes'] / $row['available_minutes'] * 100, 1) : null,
            ]);
        }

        return $this->rows = $rows->values();
    }

    protected function entities(): Collection
    {
        return match ($this->type) {
            'categories' => $this->categoryEntities(),
            'services' => $this->serviceEntities(),
            'employees' => $this->employeeEntities(),
        };
    }

    protected function categoryEntities(): Collection
    {
        $rows = ServiceCategory::query()
            ->withCount('services')
            ->when($this->categoryId, fn ($q) => $q->whereKey($this->categoryId))
            ->orderBy('name')
            ->get()
            ->map(fn (ServiceCategory $c) => [
                'key' => $c->uuid,
                'name' => $c->name,
                'subtitle' => null,
                'active' => true,
                'services_count' => $c->services_count,
            ]);

        // services left without a category still earn money
        if ($this->reservations()->contains('category_id', self::NO_CATEGORY)) {
            $rows->push(['key' => self::NO_CATEGORY, 'name' => 'Bez kategorii', 'subtitle' => null, 'active' => true, 'services_count' => Service::whereNull('category_id')->count()]);
        }

        return $rows;
    }

    protected function serviceEntities(): Collection
    {
        return Service::query()
            ->with('category:uuid,name')
            ->withCount('employees')
            ->when($this->categoryId, fn ($q) => $q->where('category_id', $this->categoryId))
            ->orderBy('name')
            ->get()
            ->map(fn (Service $s) => [
                'key' => $s->uuid,
                'name' => $s->name,
                'subtitle' => $s->category?->name ?? 'Bez kategorii',
                'active' => (bool) $s->active,
                'price' => (float) $s->price,
                'duration' => (int) $s->duration,
                'employees_count' => $s->employees_count,
            ]);
    }

    protected function employeeEntities(): Collection
    {
        $withReservations = $this->reservations()->pluck('employee_id')->unique()->all();

        return Employee::query()
            ->with(['user:uuid,name,surname,email', 'availability'])
            ->withCount('services')
            // with a category filter: employees who offer a service in it, or had a visit in it anyway
            ->when($this->categoryId, fn ($q) => $q->where(fn ($q) => $q
                ->whereHas('services', fn ($q) => $q->where('category_id', $this->categoryId))
                ->orWhereIn('uuid', $withReservations)))
            ->get()
            ->map(fn (Employee $e) => [
                'key' => $e->uuid,
                'name' => trim(($e->user?->name ?? '').' '.($e->user?->surname ?? '')) ?: 'Pracownik',
                'subtitle' => $e->user?->email,
                'active' => (bool) $e->active,
                'services_count' => $e->services_count,
                'available_minutes' => $this->availableMinutes($e->availability),
            ])
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE);
    }

    // working minutes in the period from the weekly schedule (day_of_week ranges)
    protected function availableMinutes(Collection $availability): int
    {
        $perWeekday = $availability
            ->whereNotNull('day_of_week')
            ->groupBy('day_of_week')
            ->map(fn ($ranges) => $ranges->sum(fn ($a) => Carbon::parse($a->start_time->format('H:i:s'))->diffInMinutes(Carbon::parse($a->end_time->format('H:i:s')))));

        $minutes = 0;
        for ($day = $this->from->copy(); $day->lte($this->to); $day->addDay()) {
            $minutes += $perWeekday->get($day->isoWeekday(), 0);
        }

        return (int) $minutes;
    }

    // the most booked service (or, in a service report, employee) behind a row
    protected function topName(Collection $reservations): ?string
    {
        $key = $this->type === 'services' ? 'employee_id' : 'service_id';
        $top = $reservations->where('status', '!=', 'cancelled')->countBy($key)->sortDesc()->keys()->first();

        if (! $top) {
            return null;
        }

        return $this->type === 'services'
            ? $this->employeeNames()->get($top)
            : $this->serviceNames()->get($top);
    }

    protected function serviceNames(): Collection
    {
        return $this->serviceNames ??= Service::whereIn('uuid', $this->reservations()->pluck('service_id')->unique())->pluck('name', 'uuid');
    }

    protected function employeeNames(): Collection
    {
        return $this->employeeNames ??= Employee::with('user:uuid,name,surname')
            ->whereIn('uuid', $this->reservations()->pluck('employee_id')->unique())
            ->get()
            ->mapWithKeys(fn (Employee $e) => [$e->uuid => trim($e->user?->name.' '.$e->user?->surname)]);
    }

    /**
     * Booked reservations and income over time. A day per point for up to a month,
     * a week up to half a year, a month beyond that.
     *
     * @return array{unit: string, labels: list<string>, reservations: list<int>, income: list<float>}
     */
    public function timeline(): array
    {
        $unit = match (true) {
            $this->days() <= 31 => 'day',
            $this->days() <= 183 => 'week',
            default => 'month',
        };

        $bucket = fn (string $date) => match ($unit) {
            'day' => $date,
            'week' => Carbon::parse($date)->startOfWeek(Carbon::MONDAY)->toDateString(),
            'month' => substr($date, 0, 7).'-01',
        };

        $grouped = $this->reservations()->groupBy(fn ($r) => $bucket($r->date));

        $labels = $reservations = $income = [];
        $cursor = Carbon::parse($bucket($this->from->toDateString()));

        while ($cursor->lte($this->to)) {
            $group = $grouped->get($cursor->toDateString(), collect());
            $labels[] = $unit === 'month' ? $cursor->translatedFormat('M Y') : $cursor->format('d.m');
            $reservations[] = $group->where('status', '!=', 'cancelled')->count();
            $income[] = round((float) $group->where('status', 'completed')->sum('price'), 2);

            match ($unit) {
                'day' => $cursor->addDay(),
                'week' => $cursor->addWeek(),
                'month' => $cursor->addMonthNoOverflow(),
            };
        }

        return compact('unit', 'labels', 'reservations', 'income');
    }

    /**
     * Booked reservations per day of the week (Monday first).
     */
    public function byWeekday(): array
    {
        $counts = $this->reservations()
            ->where('status', '!=', 'cancelled')
            ->countBy(fn ($r) => Carbon::parse($r->date)->isoWeekday());

        return [
            'labels' => ['Pn', 'Wt', 'Śr', 'Cz', 'Pt', 'So', 'Nd'],
            'values' => array_map(fn ($day) => $counts->get($day, 0), range(1, 7)),
        ];
    }

    /**
     * Booked reservations per starting hour, from 8:00 to 20:00 or wider if visits fall outside.
     */
    public function byHour(): array
    {
        $counts = $this->reservations()->where('status', '!=', 'cancelled')->countBy('hour');
        $hours = range(min(8, $counts->keys()->min() ?? 8), max(20, $counts->keys()->max() ?? 20));

        return [
            'labels' => array_map(fn ($h) => sprintf('%02d:00', $h), $hours),
            'values' => array_map(fn ($h) => $counts->get($h, 0), $hours),
        ];
    }

    /**
     * Short findings for the top of the report.
     *
     * @return list<string>
     */
    public function insights(): array
    {
        $rows = $this->rows();
        $noun = ['categories' => 'kategoria', 'services' => 'usługa', 'employees' => 'pracownik'][$this->type];
        $insights = [];

        $best = $rows->where('income', '>', 0)->sortByDesc('income')->first();
        if ($best) {
            $insights[] = sprintf('Najwyższy przychód: %s – %s (%s%% całości).', $best['name'], self::money($best['income']), self::number($best['share']));
        }

        $busiest = $rows->where('reservations', '>', 0)->sortByDesc('reservations')->first();
        if ($busiest && $busiest['key'] !== ($best['key'] ?? null)) {
            $insights[] = sprintf('Najwięcej rezerwacji: %s (%d).', $busiest['name'], $busiest['reservations']);
        }

        // a rate from two or three visits says nothing, so small rows are skipped
        $cancelling = $rows->where('total', '>=', 5)->where('cancellation_rate', '>=', 20)->sortByDesc('cancellation_rate')->first();
        if ($cancelling) {
            $insights[] = sprintf('Wysoki odsetek anulowań: %s – %s%% rezerwacji.', $cancelling['name'], self::number($cancelling['cancellation_rate']));
        }

        $unused = $rows->where('active', true)->where('total', 0);
        if ($unused->isNotEmpty() && $rows->count() > 1) {
            $insights[] = sprintf(
                'Bez rezerwacji w tym okresie (%d): %s%s.',
                $unused->count(),
                $unused->take(3)->pluck('name')->implode(', '),
                $unused->count() > 3 ? ' i inne' : '',
            );
        }

        if ($this->type === 'employees') {
            $loaded = $rows->whereNotNull('utilization')->where('available_minutes', '>', 0);
            if ($loaded->count() > 1) {
                $high = $loaded->sortByDesc('utilization')->first();
                $low = $loaded->sortBy('utilization')->first();
                $insights[] = sprintf('Obłożenie grafiku: najwyższe %s (%s%%), najniższe %s (%s%%).', $high['name'], self::number($high['utilization']), $low['name'], self::number($low['utilization']));
            }

            $noSchedule = $rows->where('active', true)->where('available_minutes', 0)->count();
            if ($noSchedule) {
                $insights[] = "Aktywni pracownicy bez godzin pracy w tym okresie: {$noSchedule}.";
            }
        }

        $weekday = $this->byWeekday();
        if (array_sum($weekday['values'])) {
            $day = array_search(max($weekday['values']), $weekday['values'], true);
            $hour = $this->byHour();
            $peak = array_search(max($hour['values']), $hour['values'], true);
            $names = ['poniedziałek', 'wtorek', 'środa', 'czwartek', 'piątek', 'sobota', 'niedziela'];
            $insights[] = sprintf('Najbardziej oblegany dzień: %s, najczęstsza godzina rozpoczęcia: %s.', $names[$day], $hour['labels'][$peak]);
        }

        if (! $insights) {
            $insights[] = "Brak rezerwacji w wybranym okresie – nie ma czego podsumować dla żadnej pozycji typu „{$noun}”.";
        }

        return $insights;
    }

    // percent change against the previous period; null when there is nothing to compare with
    public static function change(float|int $current, float|int $previous): ?float
    {
        if ($previous == 0) {
            return null;
        }

        return round(($current - $previous) / abs($previous) * 100, 1);
    }

    // "12,5" or "12" - no trailing ",0"
    public static function number(float|int $value): string
    {
        return rtrim(rtrim(number_format((float) $value, 1, ',', ' '), '0'), ',');
    }

    public static function money(float|int $value): string
    {
        return number_format((float) $value, 2, ',', ' ').' zł';
    }

    public static function hours(int $minutes): string
    {
        return self::number($minutes / 60).' h';
    }
}
