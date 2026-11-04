<?php

use App\Support\Report;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    // reservation-edit dispatches this after a status change or cancel
    protected $listeners = ['reservationUpdated' => '$refresh'];

    // null for an admin without an employee profile; the route lets them in too
    #[Computed]
    public function employee()
    {
        return auth()
            ->user()
            ->employee?->load(['user', 'services' => fn($q) => $q->with('category:uuid,name')->orderBy('name')]);
    }

    // what a client needs to see this employee in the booking modal (Employee::scopeBookable)
    #[Computed]
    public function bookable(): bool
    {
        return (bool) $this->employee->active && (bool) $this->employee->user?->active;
    }

    #[Computed]
    public function days(): array
    {
        return [1 => 'Poniedziałek', 2 => 'Wtorek', 3 => 'Środa', 4 => 'Czwartek', 5 => 'Piątek', 6 => 'Sobota', 7 => 'Niedziela'];
    }

    #[Computed]
    public function schedule()
    {
        return $this->employee->availability()->whereNotNull('day_of_week')->get()->groupBy('day_of_week');
    }

    #[Computed]
    public function weeklyMinutes(): int
    {
        return (int) $this->schedule->flatten()->sum(fn($range) => $range->start_time->diffInMinutes($range->end_time));
    }

    // one-off working hours for a given date, from today on
    #[Computed]
    public function specialDates()
    {
        return $this->employee->availability()->reorder()->whereNotNull('specific_date')->whereDate('specific_date', '>=', today())->orderBy('specific_date')->orderBy('start_time')->get();
    }

    // today's visits, cancelled ones left out
    #[Computed]
    public function today()
    {
        return $this->employee
            ->reservations()
            ->whereDate('reservation_date', today())
            ->where('status', '!=', 'cancelled')
            ->with(['user:uuid,name,surname,email,phone', 'service:uuid,name'])
            ->orderBy('start_time')
            ->get();
    }

    #[Computed]
    public function nextVisit()
    {
        return $this->employee
            ->reservations()
            ->upcoming()
            ->with(['user:uuid,name,surname', 'service:uuid,name'])
            ->orderBy('reservation_date')
            ->orderBy('start_time')
            ->first();
    }

    #[Computed]
    public function counts(): array
    {
        $reservations = fn() => $this->employee->reservations();

        return [
            'upcoming' => $reservations()->upcoming()->count(),
            'week' => $reservations()
                ->upcoming()
                ->whereDate('reservation_date', '<=', today()->addDays(6))
                ->count(),
            'toConfirm' => $reservations()->pending()->upcoming()->count(),
            'toComplete' => $reservations()->active()->past()->count(),
        ];
    }

    #[Computed]
    public function outsideHours(): int
    {
        return $this->employee->upcomingOutsideHoursCount();
    }
};
?>

<div class="mt-4 flex flex-col gap-6">
    @if (!$this->employee)
        <p class="p-2 text-brand-muted">To konto nie ma profilu pracownika, więc nie ma tu żadnych informacji.</p>
    @else
        @php($user = $this->employee->user)

        {{-- things that stop clients from booking or need the employee's attention --}}
        @if (!$this->bookable)
            <p class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-medium text-red-700"
                role="alert">
                <i class="fa-solid fa-circle-exclamation"></i>
                Twój profil pracownika jest nieaktywny – klienci nie mogą umawiać nowych wizyt. Skontaktuj się z
                administratorem.
            </p>
        @endif
        @if ($this->employee->services->isEmpty() || $this->schedule->isEmpty() || $this->outsideHours)
            <ul
                class="flex flex-col gap-1 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm font-medium text-amber-800">
                @if ($this->employee->services->isEmpty())
                    <li><i class="fa-solid fa-triangle-exclamation"></i> Nie masz przypisanych usług, więc nie
                        pojawiasz się przy rezerwacji.</li>
                @endif
                @if ($this->schedule->isEmpty())
                    <li><i class="fa-solid fa-triangle-exclamation"></i> Nie masz ustalonych godzin pracy, więc
                        klienci nie mogą wybrać terminu.</li>
                @endif
                @if ($this->outsideHours)
                    <li><i class="fa-solid fa-triangle-exclamation"></i> Nadchodzące wizyty poza godzinami pracy:
                        {{ $this->outsideHours }}. Pozostają bez zmian – w razie potrzeby poproś administratora o ich
                        przeniesienie.</li>
                @endif
            </ul>
        @endif

        <div class="grid gap-4 lg:grid-cols-[2fr_3fr]">
            <section class="min-w-0 rounded-xl border border-brand-border bg-white p-3 sm:p-4">
                <div class="flex items-center gap-3">
                    <span
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-brand-accent text-lg font-bold text-white"
                        aria-hidden="true">
                        {{ mb_strtoupper(mb_substr($user?->name ?? 'P', 0, 1) . mb_substr($user?->surname ?? '', 0, 1)) }}
                    </span>
                    <div class="min-w-0">
                        <h2 class="text-lg font-bold text-brand-navy break-words">
                            {{ trim($user?->name . ' ' . $user?->surname) }}</h2>
                        <span
                            @class([
                                'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold',
                                'bg-green-100 text-green-800' => $this->bookable,
                                'bg-red-100 text-red-700' => !$this->bookable,
                            ])>{{ $this->bookable ? 'Dostępny do rezerwacji' : 'Niedostępny do rezerwacji' }}</span>
                    </div>
                </div>

                <dl class="mt-4 grid grid-cols-1 gap-3 text-sm sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                    <div class="min-w-0">
                        <dt class="text-brand-muted"><i class="fa-regular fa-envelope mr-1"></i> E-mail</dt>
                        <dd class="font-semibold text-brand-navy break-all">{{ $user?->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-brand-muted"><i class="fa-solid fa-phone mr-1"></i> Telefon</dt>
                        <dd class="font-semibold text-brand-navy">{{ $user?->phone ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-brand-muted"><i class="fa-regular fa-id-badge mr-1"></i> Pracownik od</dt>
                        <dd class="font-semibold text-brand-navy">
                            {{ $this->employee->created_at?->format('d.m.Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-brand-muted"><i class="fa-regular fa-clock mr-1"></i> Czas pracy w tygodniu
                        </dt>
                        <dd class="font-semibold text-brand-navy">{{ Report::hours($this->weeklyMinutes) }}</dd>
                    </div>
                </dl>

                <a href="{{ route('dashboard.profile') }}"
                    class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-brand-accent hover:underline">
                    <i class="fa-regular fa-pen-to-square"></i> Zmień dane w profilu
                </a>
            </section>

            <section class="min-w-0 rounded-xl border border-brand-border bg-white p-3 sm:p-4">
                <h3 class="mb-3 text-base font-semibold text-brand-navy">
                    <i class="fa-regular fa-calendar text-brand-accent"></i> Twój plan
                </h3>

                <dl class="grid grid-cols-2 gap-3 text-sm sm:grid-cols-4">
                    <div class="rounded-lg bg-brand-light-bg p-3">
                        <dt class="text-xs text-brand-muted">Dziś</dt>
                        <dd class="text-lg font-bold text-brand-navy">{{ $this->today->count() }}</dd>
                    </div>
                    <div class="rounded-lg bg-brand-light-bg p-3">
                        <dt class="text-xs text-brand-muted">Najbliższe 7 dni</dt>
                        <dd class="text-lg font-bold text-brand-navy">{{ $this->counts['week'] }}</dd>
                    </div>
                    <div class="rounded-lg bg-brand-light-bg p-3">
                        <dt class="text-xs text-brand-muted">Do potwierdzenia</dt>
                        <dd class="text-lg font-bold text-brand-navy">
                            @if ($this->counts['toConfirm'])
                                <a href="{{ route('dashboard.employee', ['tab' => 'reservations', 'reservation_status' => 'pending', 'reservations_order' => 'asc']) }}"
                                    class="text-brand-accent hover:underline">{{ $this->counts['toConfirm'] }}</a>
                            @else
                                0
                            @endif
                        </dd>
                    </div>
                    <div class="rounded-lg bg-brand-light-bg p-3">
                        <dt class="text-xs text-brand-muted">Do zakończenia</dt>
                        <dd class="text-lg font-bold text-brand-navy">
                            @if ($this->counts['toComplete'])
                                <a href="{{ route('dashboard.employee', ['tab' => 'reservations', 'reservation_status' => 'to_complete', 'reservations_order' => 'asc']) }}"
                                    class="text-brand-accent hover:underline">{{ $this->counts['toComplete'] }}</a>
                            @else
                                0
                            @endif
                        </dd>
                    </div>
                </dl>

                <p class="mt-3 text-sm text-brand-text">
                    @if ($this->nextVisit)
                        <span class="text-brand-muted">Następna wizyta:</span>
                        <span class="font-semibold text-brand-navy">{{ $this->nextVisit->full_date_time }}</span>
                        · {{ $this->nextVisit->service?->name }}
                        <span class="text-brand-muted">· {{ $this->nextVisit->user?->name }}
                            {{ $this->nextVisit->user?->surname }}</span>
                    @else
                        <span class="italic text-brand-muted">Brak nadchodzących wizyt.</span>
                    @endif
                </p>

                <h4 class="mt-4 mb-2 text-sm font-semibold text-brand-navy">Wizyty dzisiaj
                    ({{ today()->format('d.m.Y') }})</h4>
                @if ($this->today->isEmpty())
                    <p class="text-sm italic text-brand-muted">Nie masz dziś żadnych wizyt.</p>
                @else
                    <ul class="flex flex-col gap-2">
                        @foreach ($this->today as $reservation)
                            <li wire:key="employee-today-{{ $reservation->uuid }}"
                                class="flex flex-col gap-2 rounded-lg border border-brand-border p-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0 text-sm">
                                    <p class="font-semibold text-brand-navy">
                                        {{ $reservation->start_time->format('H:i') }}–{{ $reservation->end_time->format('H:i') }}
                                        · {{ $reservation->service?->name }}
                                    </p>
                                    <p class="break-words text-brand-text">
                                        {{ $reservation->user?->name }} {{ $reservation->user?->surname }}
                                        @if ($reservation->user?->phone)
                                            <a href="tel:{{ $reservation->user->phone }}"
                                                class="text-brand-muted hover:text-brand-accent">·
                                                {{ $reservation->user->phone }}</a>
                                        @endif
                                    </p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <x-reservation-status :status="$reservation->status" />
                                    <button type="button"
                                        wire:click="$dispatch('editReservation', { uuid: '{{ $reservation->uuid }}' })"
                                        class="ml-auto inline-flex items-center justify-center gap-2 rounded-lg border border-brand-border bg-white px-3 py-1.5 text-sm font-semibold text-brand-text cursor-pointer transition hover:bg-brand-light-bg">
                                        <i class="fa-regular fa-pen-to-square"></i> Zarządzaj
                                    </button>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <section class="min-w-0 rounded-xl border border-brand-border bg-white p-3 sm:p-4">
                <h3 class="mb-3 text-base font-semibold text-brand-navy">
                    <i class="fa-solid fa-scissors text-brand-accent"></i> Twoje usługi
                    ({{ $this->employee->services->count() }})
                </h3>
                @if ($this->employee->services->isEmpty())
                    <p class="text-sm italic text-brand-muted">Nie masz przypisanych usług.</p>
                @else
                    <ul class="divide-y divide-brand-border text-sm">
                        @foreach ($this->employee->services as $service)
                            <li wire:key="employee-info-service-{{ $service->uuid }}"
                                class="flex items-start justify-between gap-3 py-2">
                                <span class="min-w-0">
                                    <span class="font-semibold text-brand-navy break-words">{{ $service->name }}</span>
                                    @if (!$service->active)
                                        <span
                                            class="ml-1 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600">wyłączona</span>
                                    @endif
                                    <span
                                        class="block text-xs text-brand-muted">{{ $service->category?->name ?? 'Bez kategorii' }}</span>
                                </span>
                                <span class="shrink-0 text-right tabular-nums">
                                    <span
                                        class="block font-semibold">{{ Report::money((float) $service->price) }}</span>
                                    <span class="block text-xs text-brand-muted">{{ $service->duration }} min</span>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <section class="min-w-0 rounded-xl border border-brand-border bg-white p-3 sm:p-4">
                <h3 class="mb-3 text-base font-semibold text-brand-navy">
                    <i class="fa-regular fa-clock text-brand-accent"></i> Godziny pracy
                </h3>
                <ul class="divide-y divide-brand-border rounded-xl border border-brand-border text-sm">
                    @foreach ($this->days as $dayNumber => $dayName)
                        <li wire:key="employee-info-day-{{ $dayNumber }}" @class([
                            'flex flex-col gap-1 p-3 sm:flex-row sm:items-center',
                            'bg-brand-light-bg' => today()->isoWeekday() === $dayNumber,
                        ])>
                            <span class="font-semibold text-brand-navy sm:w-36">
                                {{ $dayName }}
                                @if (today()->isoWeekday() === $dayNumber)
                                    <span class="text-xs font-normal text-brand-muted">(dziś)</span>
                                @endif
                            </span>
                            @if ($this->schedule->has($dayNumber))
                                <span class="flex flex-wrap gap-2">
                                    @foreach ($this->schedule[$dayNumber] as $range)
                                        <span wire:key="employee-info-range-{{ $range->uuid }}"
                                            class="rounded-full border border-brand-border bg-white px-3 py-0.5 tabular-nums">
                                            {{ $range->start_time->format('H:i') }}–{{ $range->end_time->format('H:i') }}
                                        </span>
                                    @endforeach
                                </span>
                            @else
                                <span class="italic text-brand-muted">Wolne</span>
                            @endif
                        </li>
                    @endforeach
                </ul>

                @if ($this->specialDates->isNotEmpty())
                    <h4 class="mt-4 mb-2 text-sm font-semibold text-brand-navy">Dodatkowe terminy</h4>
                    <ul class="flex flex-wrap gap-2 text-sm">
                        @foreach ($this->specialDates as $range)
                            <li wire:key="employee-info-date-{{ $range->uuid }}"
                                class="rounded-full border border-brand-border bg-white px-3 py-0.5 tabular-nums">
                                {{ $range->specific_date->format('d.m.Y') }},
                                {{ $range->start_time->format('H:i') }}–{{ $range->end_time->format('H:i') }}
                            </li>
                        @endforeach
                    </ul>
                @endif

                <p class="mt-3 text-xs text-brand-muted">Usługi i godziny pracy ustala administrator. Jeśli coś się
                    nie zgadza, skontaktuj się z nim.</p>
            </section>
        </div>

        {{-- confirm, complete and cancel today's visits; the same modal as in the visits tab --}}
        @livewire('reservation-edit')
    @endif
</div>
