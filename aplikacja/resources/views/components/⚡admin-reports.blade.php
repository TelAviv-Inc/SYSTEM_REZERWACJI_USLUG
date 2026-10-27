<?php

use App\Models\ServiceCategory;
use App\Support\Report;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

new class extends Component {
    #[Url]
    public string $type = 'services';

    #[Url]
    public string $period = 'this_month';

    #[Url]
    public string $from = '';

    #[Url]
    public string $to = '';

    #[Url]
    public string $category = '';

    #[Url]
    public string $sort = 'income';

    #[Url]
    public string $direction = 'desc';

    // status => [label, color]; the status colors are the same in every chart and legend
    public const STATUSES = [
        'completed' => ['Zrealizowane', '#0ca30c'],
        'confirmed' => ['Potwierdzone', '#2563eb'],
        'pending' => ['Oczekujące', '#fab219'],
        'cancelled' => ['Anulowane', '#d03b3b'],
    ];

    public function mount(): void
    {
        $this->authorize('view-reports');

        if (!array_key_exists($this->type, Report::TYPES)) {
            $this->type = 'services';
        }

        // a shared link with its own dates keeps them, everything else starts from the preset
        if ($this->period !== 'custom' || !$this->from || !$this->to) {
            $this->applyPeriod();
        }

        $this->normalizeSort();
    }

    #[Computed]
    public function periods(): array
    {
        return [
            'this_month' => 'Bieżący miesiąc',
            'last_month' => 'Poprzedni miesiąc',
            'last_30' => 'Ostatnie 30 dni',
            'last_90' => 'Ostatnie 90 dni',
            'this_year' => 'Bieżący rok',
            'last_year' => 'Poprzedni rok',
            'custom' => 'Własny zakres',
        ];
    }

    protected function applyPeriod(): void
    {
        if (!array_key_exists($this->period, $this->periods)) {
            $this->period = 'this_month';
        }

        [$from, $to] = match ($this->period) {
            'last_month' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
            'last_30' => [today()->subDays(29), today()],
            'last_90' => [today()->subDays(89), today()],
            'this_year' => [now()->startOfYear(), now()->endOfYear()],
            'last_year' => [now()->subYear()->startOfYear(), now()->subYear()->endOfYear()],
            // custom without dates (an old link) falls back to this month
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };

        $this->from = $from->toDateString();
        $this->to = $to->toDateString();
    }

    public function updatedPeriod(): void
    {
        if ($this->period !== 'custom') {
            $this->applyPeriod();
        }
    }

    public function updatedFrom(): void
    {
        $this->period = 'custom';
    }

    public function updatedTo(): void
    {
        $this->period = 'custom';
    }

    public function updatedType(): void
    {
        if (!array_key_exists($this->type, Report::TYPES)) {
            $this->type = 'services';
        }

        $this->normalizeSort();
    }

    #[Computed]
    public function columns(): array
    {
        return [
            'name' => 'Nazwa',
            'reservations' => 'Rezerwacje',
            'completed' => 'Zrealizowane',
            'cancellation_rate' => 'Anulowane',
            'income' => 'Przychód',
            'share' => 'Udział',
            'avg_ticket' => 'Śr. wartość',
            'expected' => 'Oczekiwany',
            'clients' => 'Klienci',
        ] + ($this->type === 'employees' ? ['utilization' => 'Obłożenie'] : []);
    }

    protected function normalizeSort(): void
    {
        if (!array_key_exists($this->sort, $this->columns)) {
            $this->sort = 'income';
        }

        if (!in_array($this->direction, ['asc', 'desc'], true)) {
            $this->direction = 'desc';
        }
    }

    public function sortBy(string $column): void
    {
        if (!array_key_exists($column, $this->columns)) {
            return;
        }

        // names read best A-Z, numbers biggest first
        $this->direction = $this->sort === $column ? ($this->direction === 'asc' ? 'desc' : 'asc') : ($column === 'name' ? 'asc' : 'desc');
        $this->sort = $column;
    }

    #[Computed]
    public function categories()
    {
        return ServiceCategory::orderBy('name')->get(['uuid', 'name']);
    }

    // [from, to] or the reason the dates can't be used
    #[Computed]
    public function range(): array|string
    {
        $parse = function (string $value): ?Carbon {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) || !checkdate((int) substr($value, 5, 2), (int) substr($value, 8, 2), (int) substr($value, 0, 4))) {
                return null;
            }

            return Carbon::createFromFormat('!Y-m-d', $value);
        };

        $from = $parse($this->from);
        $to = $parse($this->to);

        return match (true) {
            !$from || !$to => 'Podaj poprawne daty początku i końca okresu.',
            $from->gt($to) => 'Data początkowa nie może być późniejsza niż końcowa.',
            $from->diffInDays($to) > 3 * 366 => 'Raport może obejmować najwyżej 3 lata.',
            default => [$from, $to],
        };
    }

    #[Computed]
    public function report(): ?Report
    {
        if (!is_array($this->range)) {
            return null;
        }

        $category = $this->categories->contains('uuid', $this->category) ? $this->category : null;

        return new Report($this->type, $this->range[0], $this->range[1], $category);
    }

    #[Computed]
    public function summary(): array
    {
        return $this->report->summary();
    }

    // the eight tiles at the top, each compared with the period right before
    #[Computed]
    public function kpis(): array
    {
        $now = $this->summary;
        $before = $this->report->previous()->summary();
        $percent = fn($key) => Report::change($now[$key], $before[$key]);

        return [
            ['label' => 'Przychód', 'icon' => 'fa-solid fa-sack-dollar', 'value' => Report::money($now['income']), 'change' => $percent('income'), 'unit' => '%', 'goodUp' => true, 'hint' => 'z zakończonych wizyt'],
            ['label' => 'Rezerwacje', 'icon' => 'fa-regular fa-calendar-check', 'value' => $now['reservations'], 'change' => $percent('reservations'), 'unit' => '%', 'goodUp' => true, 'hint' => 'bez anulowanych'],
            ['label' => 'Średnia wartość wizyty', 'icon' => 'fa-solid fa-receipt', 'value' => Report::money($now['avg_ticket']), 'change' => $percent('avg_ticket'), 'unit' => '%', 'goodUp' => true, 'hint' => 'przychód / zakończone'],
            // percentage points - a change from 10% to 15% reads as +5 p.p., not +50%
            ['label' => 'Odsetek anulowań', 'icon' => 'fa-solid fa-ban', 'value' => Report::number($now['cancellation_rate']) . '%', 'change' => $before['total'] ? round($now['cancellation_rate'] - $before['cancellation_rate'], 1) : null, 'unit' => ' p.p.', 'goodUp' => false, 'hint' => "{$now['cancelled']} z {$now['total']}"],
            ['label' => 'Klienci', 'icon' => 'fa-solid fa-users', 'value' => $now['clients'], 'change' => $percent('clients'), 'unit' => '%', 'goodUp' => true, 'hint' => "nowi: {$now['new_clients']}"],
            ['label' => 'Oczekiwany przychód', 'icon' => 'fa-regular fa-hourglass-half', 'value' => Report::money($now['expected']), 'change' => null, 'unit' => '%', 'goodUp' => true, 'hint' => 'oczekujące i potwierdzone'],
            ['label' => 'Utracony przychód', 'icon' => 'fa-solid fa-arrow-trend-down', 'value' => Report::money($now['lost']), 'change' => $percent('lost'), 'unit' => '%', 'goodUp' => false, 'hint' => 'z anulowanych wizyt'],
            ['label' => 'Zarezerwowany czas', 'icon' => 'fa-regular fa-clock', 'value' => Report::hours($now['booked_minutes']), 'change' => $percent('booked_minutes'), 'unit' => '%', 'goodUp' => true, 'hint' => 'suma czasu wizyt'],
        ];
    }

    #[Computed]
    public function rows()
    {
        $rows = $this->report->rows();

        // rows without a value (an employee without a schedule has no utilization) always go last
        $sorted = $this->sort === 'name' ? $rows->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE, $this->direction === 'desc') : $rows->sortBy(fn($row) => $row[$this->sort] ?? ($this->direction === 'desc' ? -INF : INF), SORT_REGULAR, $this->direction === 'desc');

        return $sorted->values();
    }

    #[Computed]
    public function charts(): array
    {
        $timeline = $this->report->timeline();
        $rows = $this->report->rows();
        $noun = ['categories' => 'kategorii', 'services' => 'usług', 'employees' => 'pracowników'][$this->type];
        $step = ['day' => 'dziennie', 'week' => 'tygodniowo', 'month' => 'miesięcznie'][$timeline['unit']];

        $byIncome = $rows->where('income', '>', 0)->sortByDesc('income')->take(10)->values();
        $byStatus = $rows->where('total', '>', 0)->sortByDesc('total')->take(10)->values();

        return [
            'reservations' => [
                'title' => "Rezerwacje ({$step})",
                'config' => ['type' => 'bar', 'labels' => $timeline['labels'], 'datasets' => [['label' => 'Rezerwacje', 'values' => $timeline['reservations']]]],
            ],
            'income' => [
                'title' => "Przychód ({$step})",
                'config' => ['type' => 'line', 'money' => true, 'labels' => $timeline['labels'], 'datasets' => [['label' => 'Przychód', 'values' => $timeline['income']]]],
            ],
            'ranking' => [
                'title' => "Przychód według {$noun}" . ($rows->where('income', '>', 0)->count() > 10 ? ' (top 10)' : ''),
                'rows' => $byIncome->count(),
                'config' => ['type' => 'bar', 'horizontal' => true, 'money' => true, 'labels' => $byIncome->pluck('name')->all(), 'datasets' => [['label' => 'Przychód', 'values' => $byIncome->pluck('income')->all()]]],
            ],
            'statuses' => [
                'title' => "Statusy rezerwacji według {$noun}" . ($rows->where('total', '>', 0)->count() > 10 ? ' (top 10)' : ''),
                'rows' => $byStatus->count(),
                'config' => [
                    'type' => 'bar',
                    'horizontal' => true,
                    'stacked' => true,
                    'labels' => $byStatus->pluck('name')->all(),
                    'datasets' => collect(self::STATUSES)->map(fn($status, $key) => ['label' => $status[0], 'color' => $status[1], 'values' => $byStatus->pluck($key)->all()])->values()->all(),
                ],
            ],
            'weekday' => [
                'title' => 'Rezerwacje według dnia tygodnia',
                'config' => ['type' => 'bar', 'labels' => $this->report->byWeekday()['labels'], 'datasets' => [['label' => 'Rezerwacje', 'values' => $this->report->byWeekday()['values']]]],
            ],
            'hour' => [
                'title' => 'Rezerwacje według godziny rozpoczęcia',
                'config' => ['type' => 'bar', 'labels' => $this->report->byHour()['labels'], 'datasets' => [['label' => 'Rezerwacje', 'values' => $this->report->byHour()['values']]]],
            ],
        ];
    }

    public function export()
    {
        $this->authorize('view-reports');

        if (!$this->report) {
            return;
        }

        $report = $this->report;
        $summary = $this->summary;
        $rows = $this->rows;
        $columns = $this->csvColumns();
        $category = $this->categories->firstWhere('uuid', $report->categoryId)?->name ?? 'Wszystkie';
        $filename = sprintf('raport-%s-%s_%s.csv', Str::slug(Report::TYPES[$this->type]), $report->from->toDateString(), $report->to->toDateString());

        return response()->streamDownload(
            function () use ($report, $summary, $rows, $columns, $category) {
                $out = fopen('php://output', 'w');
                // BOM, so Excel opens the Polish letters correctly; ";" is what it expects with a Polish locale
                fwrite($out, "\xEF\xBB\xBF");
                $put = fn(array $line) => fputcsv($out, array_map(fn($v) => $this->csvValue($v), $line), ';');

                $put(['Raport', Report::TYPES[$report->type]]);
                $put(['Okres', $report->from->format('d.m.Y') . ' – ' . $report->to->format('d.m.Y')]);
                $put(['Kategoria', $category]);
                $put(['Wygenerowano', now()->format('d.m.Y H:i')]);
                $put([]);
                $put(['Przychód', $summary['income'], 'Oczekiwany', $summary['expected'], 'Utracony', $summary['lost']]);
                $put(['Rezerwacje', $summary['reservations'], 'Anulowane', $summary['cancelled'], 'Klienci', $summary['clients']]);
                $put([]);
                $put(array_values($columns));

                foreach ($rows as $row) {
                    $put(array_map(fn($key) => $row[$key] ?? '', array_keys($columns)));
                }

                fclose($out);
            },
            $filename,
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }

    protected function csvColumns(): array
    {
        $extra = match ($this->type) {
            'categories' => ['services_count' => 'Liczba usług', 'top' => 'Najpopularniejsza usługa'],
            'services' => ['subtitle' => 'Kategoria', 'price' => 'Cena', 'duration' => 'Czas (min)', 'employees_count' => 'Pracownicy', 'top' => 'Najczęściej wykonuje'],
            'employees' => ['subtitle' => 'E-mail', 'services_count' => 'Liczba usług', 'booked_minutes' => 'Zarezerwowane minuty', 'available_minutes' => 'Dostępne minuty', 'utilization' => 'Obłożenie %', 'top' => 'Najczęstsza usługa'],
        };

        return ['name' => 'Nazwa'] +
            $extra + [
                'total' => 'Wszystkie rezerwacje',
                'reservations' => 'Rezerwacje (bez anulowanych)',
                'completed' => 'Zrealizowane',
                'confirmed' => 'Potwierdzone',
                'pending' => 'Oczekujące',
                'cancelled' => 'Anulowane',
                'cancellation_rate' => 'Odsetek anulowań %',
                'income' => 'Przychód',
                'share' => 'Udział w przychodzie %',
                'avg_ticket' => 'Średnia wartość wizyty',
                'expected' => 'Oczekiwany przychód',
                'lost' => 'Utracony przychód',
                'clients' => 'Klienci',
            ];
    }

    protected function csvValue($value): string
    {
        if (is_float($value)) {
            return number_format($value, 2, ',', '');
        }

        $value = (string) $value;

        // a name like "=SUM(...)" must not run as a formula in Excel
        return preg_match('/^[=+\-@\t\r]/', $value) ? "'{$value}" : $value;
    }
};
?>

<div class="flex flex-col gap-6">
    {{-- filters in one row above everything they control --}}
    <div class="flex flex-col gap-3 rounded-xl border border-brand-border bg-white p-3 sm:p-4 print:hidden">
        <div class="flex flex-wrap gap-1" role="tablist" aria-label="Rodzaj raportu">
            @foreach (\App\Support\Report::TYPES as $key => $label)
                <button type="button" role="tab" aria-selected="{{ $type === $key ? 'true' : 'false' }}"
                    wire:click="$set('type', '{{ $key }}')"
                    @class([
                        'rounded-lg px-4 py-2 text-sm font-semibold cursor-pointer transition',
                        'bg-brand-accent text-white shadow' => $type === $key,
                        'bg-brand-light-bg text-brand-text hover:bg-brand-border' => $type !== $key,
                    ])>{{ $label }}</button>
            @endforeach
        </div>

        <div class="grid grid-cols-2 gap-2 lg:grid-cols-[1fr_auto_auto_1fr_auto]">
            <label class="col-span-2 flex flex-col gap-1 text-xs text-brand-muted lg:col-span-1">
                Okres
                <select wire:model.live="period"
                    class="rounded-lg border border-brand-border bg-white p-2 text-sm text-brand-text">
                    @foreach ($this->periods as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="flex flex-col gap-1 text-xs text-brand-muted">
                Od
                <input type="date" wire:model.live.debounce.500ms="from"
                    class="rounded-lg border border-brand-border bg-white p-2 text-sm text-brand-text">
            </label>
            <label class="flex flex-col gap-1 text-xs text-brand-muted">
                Do
                <input type="date" wire:model.live.debounce.500ms="to"
                    class="rounded-lg border border-brand-border bg-white p-2 text-sm text-brand-text">
            </label>
            <label class="col-span-2 flex flex-col gap-1 text-xs text-brand-muted lg:col-span-1">
                Kategoria
                <select wire:model.live="category"
                    class="rounded-lg border border-brand-border bg-white p-2 text-sm text-brand-text">
                    <option value="">Wszystkie kategorie</option>
                    @foreach ($this->categories as $cat)
                        <option value="{{ $cat->uuid }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </label>
            <div class="col-span-2 flex gap-2 lg:col-span-1 lg:items-end">
                <button type="button" wire:click="export" @disabled(!$this->report)
                    class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-brand-accent px-4 py-2 text-sm font-semibold text-white shadow cursor-pointer transition hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-50">
                    <i class="fa-solid fa-file-csv"></i> CSV
                </button>
                <button type="button" onclick="window.print()" @disabled(!$this->report)
                    class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg border border-brand-border bg-white px-4 py-2 text-sm font-semibold text-brand-text cursor-pointer transition hover:bg-brand-light-bg disabled:cursor-not-allowed disabled:opacity-50">
                    <i class="fa-solid fa-print"></i> Drukuj
                </button>
            </div>
        </div>
    </div>

    @if (!$this->report)
        <p class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-medium text-red-700" role="alert">
            <i class="fa-solid fa-circle-exclamation"></i> {{ $this->range }}
        </p>
    @else
        <div wire:loading.class="opacity-60" class="flex flex-col gap-6 transition-opacity">
            <div>
                <h2 class="text-xl font-bold text-brand-navy">Raport: {{ \App\Support\Report::TYPES[$type] }}</h2>
                <p class="text-sm text-brand-muted">
                    {{ $this->report->from->format('d.m.Y') }} – {{ $this->report->to->format('d.m.Y') }}
                    ({{ $this->report->days() }} dni)
                    @if ($this->report->categoryId)
                        · kategoria {{ $this->categories->firstWhere('uuid', $this->report->categoryId)?->name }}
                    @endif
                    · porównanie z {{ $this->report->previous()->from->format('d.m.Y') }} –
                    {{ $this->report->previous()->to->format('d.m.Y') }}
                    · wygenerowano {{ now()->format('d.m.Y H:i') }}
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                @foreach ($this->kpis as $kpi)
                    @php($good = $kpi['change'] === null || $kpi['change'] == 0 ? null : $kpi['change'] > 0 === $kpi['goodUp'])
                    <div class="flex min-w-0 flex-col gap-1 rounded-xl border border-brand-border bg-white p-3 sm:p-4">
                        <span class="flex items-center gap-2 text-xs font-medium text-brand-muted">
                            <i class="{{ $kpi['icon'] }}"></i> {{ $kpi['label'] }}
                        </span>
                        <span
                            class="text-lg font-bold text-brand-navy sm:text-2xl break-words">{{ $kpi['value'] }}</span>
                        <span class="flex flex-wrap items-center gap-x-2 text-xs text-brand-muted">
                            @if ($kpi['change'] !== null)
                                <span @class([
                                    'inline-flex items-center gap-1 font-semibold',
                                    'text-green-700' => $good === true,
                                    'text-red-700' => $good === false,
                                    'text-brand-muted' => $good === null,
                                ]) title="Zmiana względem poprzedniego okresu">
                                    <i @class([
                                        'fa-solid',
                                        'fa-arrow-up' => $kpi['change'] > 0,
                                        'fa-arrow-down' => $kpi['change'] < 0,
                                        'fa-equals' => $kpi['change'] == 0,
                                    ])></i>
                                    {{ ($kpi['change'] > 0 ? '+' : '') . \App\Support\Report::number($kpi['change']) . $kpi['unit'] }}
                                </span>
                            @endif
                            <span>{{ $kpi['hint'] }}</span>
                        </span>
                    </div>
                @endforeach
            </div>

            <section class="rounded-xl border border-brand-border bg-white p-3 sm:p-4">
                <h3 class="mb-2 text-base font-semibold text-brand-navy">
                    <i class="fa-regular fa-lightbulb text-brand-accent"></i> Wnioski
                </h3>
                <ul class="flex list-disc flex-col gap-1 pl-5 text-sm text-brand-text">
                    @foreach ($this->report->insights() as $insight)
                        <li>{{ $insight }}</li>
                    @endforeach
                </ul>
            </section>

            <div class="grid gap-4 lg:grid-cols-2">
                @foreach ($this->charts as $key => $chart)
                    <section
                        class="min-w-0 rounded-xl border border-brand-border bg-white p-3 sm:p-4 break-inside-avoid">
                        <h3 class="mb-2 text-sm font-semibold text-brand-navy">{{ $chart['title'] }}</h3>
                        @if (isset($chart['rows']) && $chart['rows'] === 0)
                            <p class="flex h-40 items-center justify-center text-sm italic text-brand-muted">
                                Brak danych w tym okresie.</p>
                        @else
                            {{-- the key changes with the data, so Alpine rebuilds the chart --}}
                            <div wire:key="report-chart-{{ $key }}-{{ md5(json_encode($chart['config'])) }}"
                                x-data="reportChart(@js($chart['config']))" class="relative min-w-0"
                                style="height: {{ isset($chart['rows']) ? max(160, $chart['rows'] * 34 + 60) : 256 }}px">
                                <canvas x-ref="canvas" wire:ignore role="img"
                                    aria-label="{{ $chart['title'] }} – szczegóły w tabeli poniżej"></canvas>
                            </div>
                        @endif
                    </section>
                @endforeach
            </div>

            <section class="min-w-0 rounded-xl border border-brand-border bg-white p-3 sm:p-4">
                <h3 class="mb-2 text-base font-semibold text-brand-navy">Zestawienie</h3>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[56rem] text-left text-sm">
                        <thead>
                            <tr class="border-b border-brand-border text-xs text-brand-muted">
                                @foreach ($this->columns as $key => $label)
                                    <th @class([
                                        'p-2 font-semibold whitespace-nowrap',
                                        'text-right' => $key !== 'name',
                                    ])
                                        aria-sort="{{ $sort === $key ? ($direction === 'asc' ? 'ascending' : 'descending') : 'none' }}">
                                        <button type="button" wire:click="sortBy('{{ $key }}')"
                                            class="inline-flex items-center gap-1 cursor-pointer hover:text-brand-navy">
                                            {{ $label }}
                                            @if ($sort === $key)
                                                <i
                                                    class="fa-solid {{ $direction === 'asc' ? 'fa-arrow-up' : 'fa-arrow-down' }} text-brand-accent"></i>
                                            @endif
                                        </button>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($this->rows as $row)
                                <tr wire:key="report-row-{{ $row['key'] }}"
                                    class="border-b border-brand-border last:border-0 align-top">
                                    <td class="p-2">
                                        <span class="font-semibold text-brand-navy">{{ $row['name'] }}</span>
                                        @if (!$row['active'])
                                            <span
                                                class="ml-1 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600">nieaktywna/y</span>
                                        @endif
                                        <span class="block text-xs text-brand-muted">
                                            @switch($type)
                                                @case('categories')
                                                    Usługi: {{ $row['services_count'] }}
                                                    @if ($row['top'])
                                                        · najpopularniejsza: {{ $row['top'] }}
                                                    @endif
                                                @break

                                                @case('services')
                                                    {{ $row['subtitle'] }} · {{ \App\Support\Report::money($row['price']) }} ·
                                                    {{ $row['duration'] }} min · pracownicy: {{ $row['employees_count'] }}
                                                    @if ($row['top'])
                                                        · najczęściej: {{ $row['top'] }}
                                                    @endif
                                                @break

                                                @case('employees')
                                                    Usługi: {{ $row['services_count'] }}
                                                    @if ($row['top'])
                                                        · najczęściej: {{ $row['top'] }}
                                                    @endif
                                                @break
                                            @endswitch
                                        </span>
                                    </td>
                                    <td class="p-2 text-right tabular-nums">{{ $row['reservations'] }}</td>
                                    <td class="p-2 text-right tabular-nums">{{ $row['completed'] }}</td>
                                    <td class="p-2 text-right tabular-nums whitespace-nowrap">
                                        {{ $row['cancelled'] }}
                                        <span
                                            class="text-xs text-brand-muted">({{ \App\Support\Report::number($row['cancellation_rate']) }}%)</span>
                                    </td>
                                    <td class="p-2 text-right font-semibold tabular-nums whitespace-nowrap">
                                        {{ \App\Support\Report::money($row['income']) }}</td>
                                    <td class="p-2 text-right tabular-nums">
                                        {{ \App\Support\Report::number($row['share']) }}%</td>
                                    <td class="p-2 text-right tabular-nums whitespace-nowrap">
                                        {{ \App\Support\Report::money($row['avg_ticket']) }}</td>
                                    <td class="p-2 text-right tabular-nums whitespace-nowrap">
                                        {{ \App\Support\Report::money($row['expected']) }}</td>
                                    <td class="p-2 text-right tabular-nums">{{ $row['clients'] }}</td>
                                    @if ($type === 'employees')
                                        <td class="p-2 text-right tabular-nums whitespace-nowrap">
                                            @if ($row['utilization'] === null)
                                                <span class="text-xs italic text-brand-muted">brak grafiku</span>
                                            @else
                                                {{ \App\Support\Report::number($row['utilization']) }}%
                                                <span class="block text-xs text-brand-muted">
                                                    {{ \App\Support\Report::hours($row['booked_minutes']) }} /
                                                    {{ \App\Support\Report::hours($row['available_minutes']) }}</span>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ count($this->columns) }}" class="p-2 italic text-brand-muted">Brak
                                            pozycji do pokazania.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if ($this->rows->isNotEmpty())
                                <tfoot>
                                    <tr class="border-t-2 border-brand-border font-semibold text-brand-navy">
                                        <td class="p-2">Razem</td>
                                        <td class="p-2 text-right tabular-nums">{{ $this->summary['reservations'] }}</td>
                                        <td class="p-2 text-right tabular-nums">{{ $this->summary['completed'] }}</td>
                                        <td class="p-2 text-right tabular-nums whitespace-nowrap">
                                            {{ $this->summary['cancelled'] }}
                                            <span
                                                class="text-xs font-normal text-brand-muted">({{ \App\Support\Report::number($this->summary['cancellation_rate']) }}%)</span>
                                        </td>
                                        <td class="p-2 text-right tabular-nums whitespace-nowrap">
                                            {{ \App\Support\Report::money($this->summary['income']) }}</td>
                                        <td class="p-2 text-right tabular-nums">
                                            {{ $this->summary['income'] > 0 ? '100%' : '0%' }}
                                        </td>
                                        <td class="p-2 text-right tabular-nums whitespace-nowrap">
                                            {{ \App\Support\Report::money($this->summary['avg_ticket']) }}</td>
                                        <td class="p-2 text-right tabular-nums whitespace-nowrap">
                                            {{ \App\Support\Report::money($this->summary['expected']) }}</td>
                                        <td class="p-2 text-right tabular-nums">{{ $this->summary['clients'] }}</td>
                                        @if ($type === 'employees')
                                            <td class="p-2"></td>
                                        @endif
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                    <p class="mt-2 text-xs text-brand-muted">
                        Przychód liczony z ceny zapisanej w zakończonych rezerwacjach. Oczekiwany – rezerwacje
                        oczekujące i potwierdzone. Klienci w wierszach mogą się powtarzać, więc suma może być większa
                        niż „Razem”.
                    </p>
                </section>
            </div>
        @endif
    </div>
