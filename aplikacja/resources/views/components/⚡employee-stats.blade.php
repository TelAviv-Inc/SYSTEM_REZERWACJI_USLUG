<?php

use App\Models\User;
use App\Support\Report;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

new class extends Component {
    // own names in the URL, so they don't clash with the filters of the visits tab
    #[Url(as: 'stats_period')]
    public string $period = 'this_month';

    #[Url(as: 'stats_from')]
    public string $from = '';

    #[Url(as: 'stats_to')]
    public string $to = '';

    // status => [label, color]; the same colors as in the admin report
    public const STATUSES = [
        'completed' => ['Zrealizowane', '#0ca30c'],
        'confirmed' => ['Potwierdzone', '#2563eb'],
        'pending' => ['Oczekujące', '#fab219'],
        'cancelled' => ['Anulowane', '#d03b3b'],
    ];

    public function mount(): void
    {
        // a shared link with its own dates keeps them, everything else starts from the preset
        if ($this->period !== 'custom' || !$this->from || !$this->to) {
            $this->applyPeriod();
        }
    }

    protected function applyPeriod(): void
    {
        if (!array_key_exists($this->period, Report::PERIODS)) {
            $this->period = 'this_month';
        }

        [$from, $to] = Report::presetRange($this->period);

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

    // null for an admin without an employee profile; the route lets them in too
    #[Computed]
    public function employee()
    {
        return auth()->user()->employee;
    }

    // [from, to] or the reason the dates can't be used
    #[Computed]
    public function range(): array|string
    {
        return Report::parseRange($this->from, $this->to);
    }

    // only ever the logged-in employee's own visits
    #[Computed]
    public function report(): ?Report
    {
        if (!$this->employee || !is_array($this->range)) {
            return null;
        }

        return new Report('services', $this->range[0], $this->range[1], employeeId: $this->employee->uuid);
    }

    #[Computed]
    public function summary(): array
    {
        return $this->report->summary();
    }

    // booked time against the weekly schedule; null without working hours in the period
    protected function utilization(Report $report, array $summary): ?float
    {
        $scheduled = $report->scheduledMinutes();

        return $scheduled ? round(($summary['booked_minutes'] / $scheduled) * 100, 1) : null;
    }

    // the tiles at the top, each compared with the period right before
    #[Computed]
    public function kpis(): array
    {
        $now = $this->summary;
        $previous = $this->report->previous();
        $before = $previous->summary();
        $percent = fn($key) => Report::change($now[$key], $before[$key]);

        $scheduled = $this->report->scheduledMinutes();
        $utilization = $this->utilization($this->report, $now);
        $utilizationBefore = $this->utilization($previous, $before);

        return [
            ['label' => 'Przychód', 'icon' => 'fa-solid fa-sack-dollar', 'value' => Report::money($now['income']), 'change' => $percent('income'), 'unit' => '%', 'goodUp' => true, 'hint' => 'z zakończonych wizyt'],
            ['label' => 'Wizyty', 'icon' => 'fa-regular fa-calendar-check', 'value' => $now['reservations'], 'change' => $percent('reservations'), 'unit' => '%', 'goodUp' => true, 'hint' => "zakończone: {$now['completed']}"],
            ['label' => 'Średnia wartość wizyty', 'icon' => 'fa-solid fa-receipt', 'value' => Report::money($now['avg_ticket']), 'change' => $percent('avg_ticket'), 'unit' => '%', 'goodUp' => true, 'hint' => 'przychód / zakończone'],
            ['label' => 'Klienci', 'icon' => 'fa-solid fa-users', 'value' => $now['clients'], 'change' => $percent('clients'), 'unit' => '%', 'goodUp' => true, 'hint' => "nowi: {$now['new_clients']}"],
            // percentage points - a change from 40% to 50% reads as +10 p.p., not +25%
            ['label' => 'Obłożenie grafiku', 'icon' => 'fa-solid fa-gauge-high', 'value' => $utilization === null ? '—' : Report::number($utilization) . '%', 'change' => $utilization !== null && $utilizationBefore !== null ? round($utilization - $utilizationBefore, 1) : null, 'unit' => ' p.p.', 'goodUp' => true, 'hint' => $scheduled ? Report::hours($now['booked_minutes']) . ' z ' . Report::hours($scheduled) : 'brak godzin pracy'],
            ['label' => 'Odsetek anulowań', 'icon' => 'fa-solid fa-ban', 'value' => Report::number($now['cancellation_rate']) . '%', 'change' => $before['total'] ? round($now['cancellation_rate'] - $before['cancellation_rate'], 1) : null, 'unit' => ' p.p.', 'goodUp' => false, 'hint' => "{$now['cancelled']} z {$now['total']}"],
            ['label' => 'Oczekiwany przychód', 'icon' => 'fa-regular fa-hourglass-half', 'value' => Report::money($now['expected']), 'change' => null, 'unit' => '%', 'goodUp' => true, 'hint' => 'oczekujące i potwierdzone'],
            ['label' => 'Utracony przychód', 'icon' => 'fa-solid fa-arrow-trend-down', 'value' => Report::money($now['lost']), 'change' => $percent('lost'), 'unit' => '%', 'goodUp' => false, 'hint' => 'z anulowanych wizyt'],
        ];
    }

    // the employee's services, the busiest first
    #[Computed]
    public function rows()
    {
        return $this->report
            ->rows()
            ->sortBy([['reservations', 'desc'], ['income', 'desc'], fn($a, $b) => strnatcasecmp($a['name'], $b['name'])])
            ->values();
    }

    // clients with the most visits in the period (cancelled ones left out)
    #[Computed]
    public function topClients()
    {
        $visits = $this->report->reservations()->where('status', '!=', 'cancelled')->groupBy('user_id');

        if ($visits->isEmpty()) {
            return collect();
        }

        $users = User::whereIn('uuid', $visits->keys())
            ->get(['uuid', 'name', 'surname', 'email'])
            ->keyBy('uuid');

        return $visits
            ->map(
                fn($group, $userId) => [
                    'key' => $userId,
                    'name' => trim(($users[$userId]->name ?? '') . ' ' . ($users[$userId]->surname ?? '')) ?: 'Klient',
                    'email' => $users[$userId]->email ?? null,
                    'visits' => $group->count(),
                    'income' => round((float) $group->where('status', 'completed')->sum('price'), 2),
                    'last' => $group->max('date'),
                ],
            )
            ->sortBy([['visits', 'desc'], ['income', 'desc']])
            ->take(5)
            ->values();
    }

    // everything since the employee started, not limited by the period
    #[Computed]
    public function allTime(): array
    {
        $reservations = $this->employee->reservations();
        $completed = (clone $reservations)->completed();
        $booked = (clone $reservations)->where('status', '!=', 'cancelled');

        $clients = (clone $booked)->toBase()->selectRaw('user_id, count(*) as visits')->groupBy('user_id')->pluck('visits');

        return [
            'completed' => (clone $completed)->count(),
            'income' => round((float) (clone $completed)->sum('price'), 2),
            'clients' => $clients->count(),
            'returning' => $clients->filter(fn($visits) => $visits > 1)->count(),
            'since' => $this->employee->created_at,
        ];
    }

    #[Computed]
    public function charts(): array
    {
        $timeline = $this->report->timeline();
        $step = ['day' => 'dziennie', 'week' => 'tygodniowo', 'month' => 'miesięcznie'][$timeline['unit']];
        $byStatus = $this->rows->where('total', '>', 0)->sortByDesc('total')->take(10)->values();
        $byIncome = $this->rows->where('income', '>', 0)->sortByDesc('income')->take(10)->values();
        $weekday = $this->report->byWeekday();
        $hour = $this->report->byHour();

        return [
            'reservations' => [
                'title' => "Wizyty ({$step})",
                'config' => ['type' => 'bar', 'labels' => $timeline['labels'], 'datasets' => [['label' => 'Wizyty', 'values' => $timeline['reservations']]]],
            ],
            'income' => [
                'title' => "Przychód ({$step})",
                'config' => ['type' => 'line', 'money' => true, 'labels' => $timeline['labels'], 'datasets' => [['label' => 'Przychód', 'values' => $timeline['income']]]],
            ],
            'statuses' => [
                'title' => 'Statusy wizyt według usług',
                'rows' => $byStatus->count(),
                'config' => [
                    'type' => 'bar',
                    'horizontal' => true,
                    'stacked' => true,
                    'labels' => $byStatus->pluck('name')->all(),
                    'datasets' => collect(self::STATUSES)->map(fn($status, $key) => ['label' => $status[0], 'color' => $status[1], 'values' => $byStatus->pluck($key)->all()])->values()->all(),
                ],
            ],
            'ranking' => [
                'title' => 'Przychód według usług',
                'rows' => $byIncome->count(),
                'config' => ['type' => 'bar', 'horizontal' => true, 'money' => true, 'labels' => $byIncome->pluck('name')->all(), 'datasets' => [['label' => 'Przychód', 'values' => $byIncome->pluck('income')->all()]]],
            ],
            'weekday' => [
                'title' => 'Wizyty według dnia tygodnia',
                'config' => ['type' => 'bar', 'labels' => $weekday['labels'], 'datasets' => [['label' => 'Wizyty', 'values' => $weekday['values']]]],
            ],
            'hour' => [
                'title' => 'Wizyty według godziny rozpoczęcia',
                'config' => ['type' => 'bar', 'labels' => $hour['labels'], 'datasets' => [['label' => 'Wizyty', 'values' => $hour['values']]]],
            ],
        ];
    }
};
?>

<div class="mt-4 flex flex-col gap-6">
    @if (!$this->employee)
        <p class="p-2 text-brand-muted">To konto nie ma profilu pracownika, więc nie ma tu żadnych statystyk.</p>
    @else
        <div class="flex flex-col gap-3 rounded-xl border border-brand-border bg-white p-3 sm:p-4 print:hidden">
            <div class="grid grid-cols-2 gap-2 lg:grid-cols-[1fr_auto_auto_auto]">
                <label class="col-span-2 flex flex-col gap-1 text-xs text-brand-muted lg:col-span-1">
                    Okres
                    <select wire:model.live="period"
                        class="rounded-lg border border-brand-border bg-white p-2 text-sm text-brand-text">
                        @foreach (Report::PERIODS as $key => $label)
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
                <div class="col-span-2 flex lg:col-span-1 lg:items-end">
                    <button type="button" onclick="window.print()" @disabled(!$this->report)
                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg border border-brand-border bg-white px-4 py-2 text-sm font-semibold text-brand-text cursor-pointer transition hover:bg-brand-light-bg disabled:cursor-not-allowed disabled:opacity-50">
                        <i class="fa-solid fa-print"></i> Drukuj
                    </button>
                </div>
            </div>
        </div>

        @if (!$this->report)
            <p class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-medium text-red-700"
                role="alert">
                <i class="fa-solid fa-circle-exclamation"></i> {{ $this->range }}
            </p>
        @else
            <div wire:loading.class="opacity-60" class="flex flex-col gap-6 transition-opacity">
                <div>
                    <h2 class="text-xl font-bold text-brand-navy">Twoje statystyki</h2>
                    <p class="text-sm text-brand-muted">
                        {{ $this->report->from->format('d.m.Y') }} – {{ $this->report->to->format('d.m.Y') }}
                        ({{ $this->report->days() }} dni)
                        · porównanie z {{ $this->report->previous()->from->format('d.m.Y') }} –
                        {{ $this->report->previous()->to->format('d.m.Y') }}
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                    @foreach ($this->kpis as $kpi)
                        <x-report-kpi :kpi="$kpi" />
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
                                <div wire:key="employee-chart-{{ $key }}-{{ md5(json_encode($chart['config'])) }}"
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
                    <h3 class="mb-2 text-base font-semibold text-brand-navy">Usługi</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[40rem] text-left text-sm">
                            <thead>
                                <tr class="border-b border-brand-border text-xs text-brand-muted">
                                    <th class="p-2 font-semibold">Usługa</th>
                                    <th class="p-2 font-semibold text-right">Wizyty</th>
                                    <th class="p-2 font-semibold text-right">Zakończone</th>
                                    <th class="p-2 font-semibold text-right">Anulowane</th>
                                    <th class="p-2 font-semibold text-right">Przychód</th>
                                    <th class="p-2 font-semibold text-right">Udział</th>
                                    <th class="p-2 font-semibold text-right whitespace-nowrap">Śr. wartość</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($this->rows as $row)
                                    <tr wire:key="employee-stats-row-{{ $row['key'] }}"
                                        class="border-b border-brand-border last:border-0 align-top">
                                        <td class="p-2">
                                            <span class="font-semibold text-brand-navy">{{ $row['name'] }}</span>
                                            @if (!$row['active'])
                                                <span
                                                    class="ml-1 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600">nieaktywna</span>
                                            @endif
                                            <span class="block text-xs text-brand-muted">{{ $row['subtitle'] }} ·
                                                {{ Report::money($row['price']) }} · {{ $row['duration'] }} min</span>
                                        </td>
                                        <td class="p-2 text-right tabular-nums">{{ $row['reservations'] }}</td>
                                        <td class="p-2 text-right tabular-nums">{{ $row['completed'] }}</td>
                                        <td class="p-2 text-right tabular-nums whitespace-nowrap">
                                            {{ $row['cancelled'] }}
                                            <span
                                                class="text-xs text-brand-muted">({{ Report::number($row['cancellation_rate']) }}%)</span>
                                        </td>
                                        <td class="p-2 text-right font-semibold tabular-nums whitespace-nowrap">
                                            {{ Report::money($row['income']) }}</td>
                                        <td class="p-2 text-right tabular-nums">{{ Report::number($row['share']) }}%
                                        </td>
                                        <td class="p-2 text-right tabular-nums whitespace-nowrap">
                                            {{ Report::money($row['avg_ticket']) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="p-2 italic text-brand-muted">Nie masz przypisanych
                                            usług.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if ($this->rows->isNotEmpty())
                                <tfoot>
                                    <tr class="border-t-2 border-brand-border font-semibold text-brand-navy">
                                        <td class="p-2">Razem</td>
                                        <td class="p-2 text-right tabular-nums">{{ $this->summary['reservations'] }}
                                        </td>
                                        <td class="p-2 text-right tabular-nums">{{ $this->summary['completed'] }}</td>
                                        <td class="p-2 text-right tabular-nums">{{ $this->summary['cancelled'] }}</td>
                                        <td class="p-2 text-right tabular-nums whitespace-nowrap">
                                            {{ Report::money($this->summary['income']) }}</td>
                                        <td class="p-2 text-right tabular-nums">
                                            {{ $this->summary['income'] > 0 ? '100%' : '0%' }}</td>
                                        <td class="p-2 text-right tabular-nums whitespace-nowrap">
                                            {{ Report::money($this->summary['avg_ticket']) }}</td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                    <p class="mt-2 text-xs text-brand-muted">
                        Przychód liczony z ceny zapisanej w zakończonych wizytach. Wizyty nie obejmują anulowanych.
                    </p>
                </section>

                <div class="grid gap-4 lg:grid-cols-2">
                    <section class="min-w-0 rounded-xl border border-brand-border bg-white p-3 sm:p-4">
                        <h3 class="mb-2 text-base font-semibold text-brand-navy">
                            <i class="fa-solid fa-star text-brand-accent"></i> Najczęstsi klienci w tym okresie
                        </h3>
                        @if ($this->topClients->isEmpty())
                            <p class="text-sm italic text-brand-muted">Brak wizyt w tym okresie.</p>
                        @else
                            <ol class="flex flex-col divide-y divide-brand-border text-sm">
                                @foreach ($this->topClients as $client)
                                    <li wire:key="employee-top-client-{{ $client['key'] }}"
                                        class="flex items-center justify-between gap-3 py-2">
                                        <span class="min-w-0">
                                            <span
                                                class="block font-semibold text-brand-navy break-words">{{ $client['name'] }}</span>
                                            <span class="block text-xs text-brand-muted break-all">
                                                {{ $client['email'] }} · ostatnio
                                                {{ \Illuminate\Support\Carbon::parse($client['last'])->format('d.m.Y') }}
                                            </span>
                                        </span>
                                        <span class="shrink-0 text-right tabular-nums">
                                            <span class="block font-semibold">Wizyty:
                                                {{ $client['visits'] }}</span>
                                            <span
                                                class="block text-xs text-brand-muted">{{ Report::money($client['income']) }}</span>
                                        </span>
                                    </li>
                                @endforeach
                            </ol>
                        @endif
                    </section>

                    <section class="min-w-0 rounded-xl border border-brand-border bg-white p-3 sm:p-4">
                        <h3 class="mb-2 text-base font-semibold text-brand-navy">
                            <i class="fa-solid fa-trophy text-brand-accent"></i> Od początku współpracy
                        </h3>
                        <dl class="grid grid-cols-2 gap-3 text-sm">
                            <div class="rounded-lg bg-brand-light-bg p-3">
                                <dt class="text-xs text-brand-muted">Zakończone wizyty</dt>
                                <dd class="text-lg font-bold text-brand-navy">{{ $this->allTime['completed'] }}</dd>
                            </div>
                            <div class="rounded-lg bg-brand-light-bg p-3">
                                <dt class="text-xs text-brand-muted">Przychód</dt>
                                <dd class="text-lg font-bold text-brand-navy break-words">
                                    {{ Report::money($this->allTime['income']) }}</dd>
                            </div>
                            <div class="rounded-lg bg-brand-light-bg p-3">
                                <dt class="text-xs text-brand-muted">Klienci</dt>
                                <dd class="text-lg font-bold text-brand-navy">{{ $this->allTime['clients'] }}</dd>
                            </div>
                            <div class="rounded-lg bg-brand-light-bg p-3">
                                <dt class="text-xs text-brand-muted">Powracający klienci</dt>
                                <dd class="text-lg font-bold text-brand-navy">
                                    {{ $this->allTime['returning'] }}
                                    @if ($this->allTime['clients'])
                                        <span
                                            class="text-xs font-normal text-brand-muted">({{ Report::number(round(($this->allTime['returning'] / $this->allTime['clients']) * 100, 1)) }}%)</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                        @if ($this->allTime['since'])
                            <p class="mt-2 text-xs text-brand-muted">Pracownik od
                                {{ $this->allTime['since']->format('d.m.Y') }}.</p>
                        @endif
                    </section>
                </div>
            </div>
        @endif
    @endif
</div>
