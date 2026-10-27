<?php

use App\Models\Employee;
use App\Models\EmployeeAvailability;
use App\Models\Service;
use App\Support\AdminStats;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    public ?Employee $employee = null;
    public int $weeks = 12;
    // how many upcoming reservations are listed, grows with showMore()
    public int $limit = 10;

    public string $serviceToAdd = '';

    // working hours form; editingAvailability is the uuid of the range being changed, null adds a new one
    public ?string $editingAvailability = null;
    public int $day = 1;
    public string $startTime = '09:00';
    public string $endTime = '17:00';

    // reservation-edit dispatches this after a reschedule or cancel
    protected $listeners = ['reservationUpdated' => '$refresh'];

    #[On('detailsEmployee')]
    public function open($uuid)
    {
        $this->close();
        $employee = Employee::with(['user', 'services.category'])->findOrFail($uuid);
        $this->authorize('view', $employee);
        $this->employee = $employee;
    }

    public function updatedWeeks(): void
    {
        if (!in_array($this->weeks, [4, 12, 52], true)) {
            $this->weeks = 12;
        }
    }

    public function showMore()
    {
        $this->limit += 10;
    }

    #[Computed]
    public function upcomingCount(): int
    {
        return $this->employee->reservations()->upcoming()->count();
    }

    #[Computed]
    public function upcomingReservations()
    {
        return $this->employee
            ->reservations()
            ->upcoming()
            ->with(['user:uuid,name,surname,email', 'service:uuid,name'])
            ->orderBy('reservation_date')
            ->orderBy('start_time')
            ->limit($this->limit)
            ->get();
    }

    #[Computed]
    public function bookings(): array
    {
        return AdminStats::weeklyReservations($this->weeks, $this->employee->uuid);
    }

    #[Computed]
    public function days(): array
    {
        return [1 => 'Poniedziałek', 2 => 'Wtorek', 3 => 'Środa', 4 => 'Czwartek', 5 => 'Piątek', 6 => 'Sobota', 7 => 'Niedziela'];
    }

    // active services the employee doesn't provide yet, for the "add service" select
    #[Computed]
    public function availableServices()
    {
        return Service::with('category:uuid,name')
            ->where('active', 1)
            ->whereNotIn('uuid', $this->employee->services->modelKeys())
            ->orderBy('name')
            ->get(['uuid', 'category_id', 'name'])
            ->groupBy(fn($service) => $service->category?->name ?? 'Bez kategorii')
            ->sortKeys();
    }

    // service uuid => upcoming visits, so removing a service warns about the visits that stay booked
    #[Computed]
    public function upcomingByService(): array
    {
        return $this->employee->reservations()->upcoming()->toBase()->selectRaw('service_id, count(*) as total')->groupBy('service_id')->pluck('total', 'service_id')->map(fn($total) => (int) $total)->all();
    }

    #[Computed]
    public function schedule()
    {
        return $this->employee->availability()->whereNotNull('day_of_week')->get()->groupBy('day_of_week');
    }

    // upcoming visits that no single working range covers any more - they stay booked, the admin has to move them
    #[Computed]
    public function reservationsOutsideHours(): int
    {
        $ranges = $this->employee
            ->availability()
            ->whereNotNull('day_of_week')
            ->toBase()
            ->get(['day_of_week', 'start_time', 'end_time']);
        $time = fn($value) => Carbon::parse($value)->format('H:i:s');

        return $this->employee
            ->reservations()
            ->upcoming()
            ->toBase()
            ->get(['reservation_date', 'start_time', 'end_time'])
            ->reject(function ($reservation) use ($ranges, $time) {
                $day = Carbon::parse($reservation->reservation_date)->isoWeekday();

                return $ranges->contains(fn($range) => (int) $range->day_of_week === $day && $time($range->start_time) <= $time($reservation->start_time) && $time($range->end_time) >= $time($reservation->end_time));
            })
            ->count();
    }

    public function addService()
    {
        $this->authorize('update', $this->employee);

        $this->validate(
            [
                'serviceToAdd' => ['required', Rule::exists('services', 'uuid')->where('active', 1), Rule::notIn($this->employee->services->modelKeys())],
            ],
            [
                'serviceToAdd.required' => 'Wybierz usługę',
                'serviceToAdd.exists' => 'Wybrana usługa nie istnieje',
                'serviceToAdd.not_in' => 'Pracownik ma już tę usługę',
            ],
        );

        $this->employee->services()->attach($this->serviceToAdd, ['uuid' => (string) Str::uuid()]);
        $this->reset('serviceToAdd');
        $this->servicesChanged();
    }

    public function removeService(string $uuid)
    {
        $this->authorize('update', $this->employee);

        // visits already booked for this service are left alone
        $this->employee->services()->detach($uuid);
        $this->servicesChanged();
    }

    protected function servicesChanged()
    {
        $this->employee->load('services.category');
        unset($this->availableServices);
        // the employees list shows the services too
        $this->dispatch('employeeUpdated');
    }

    public function editAvailability(string $uuid)
    {
        $this->authorize('update', $this->employee);
        $availability = $this->employee->availability()->findOrFail($uuid);

        $this->editingAvailability = $availability->uuid;
        $this->day = $availability->day_of_week;
        $this->startTime = $availability->start_time->format('H:i');
        $this->endTime = $availability->end_time->format('H:i');
        $this->resetValidation();
    }

    public function cancelAvailabilityEdit()
    {
        $this->reset('editingAvailability', 'day', 'startTime', 'endTime');
        $this->resetValidation();
    }

    public function saveAvailability()
    {
        $this->authorize('update', $this->employee);

        $this->validate(
            [
                'day' => ['required', 'integer', 'between:1,7'],
                'startTime' => ['required', 'date_format:H:i'],
                'endTime' => ['required', 'date_format:H:i', 'after:startTime'],
            ],
            [
                'day.*' => 'Wybierz dzień tygodnia',
                'startTime.*' => 'Podaj godzinę rozpoczęcia',
                'endTime.date_format' => 'Podaj godzinę zakończenia',
                'endTime.required' => 'Podaj godzinę zakończenia',
                'endTime.after' => 'Koniec pracy musi być po jej rozpoczęciu',
            ],
        );

        // stored as H:i:s, the format ReservationForm compares the reservation times against
        $start = "{$this->startTime}:00";
        $end = "{$this->endTime}:00";

        $overlaps = $this->employee->availability()->where('day_of_week', $this->day)->where('start_time', '<', $end)->where('end_time', '>', $start)->when($this->editingAvailability, fn($q) => $q->whereKeyNot($this->editingAvailability))->exists();

        if ($overlaps) {
            throw ValidationException::withMessages(['startTime' => 'Te godziny nachodzą na inny przedział w tym dniu']);
        }

        $values = ['day_of_week' => $this->day, 'start_time' => $start, 'end_time' => $end];

        if ($this->editingAvailability) {
            $this->employee->availability()->findOrFail($this->editingAvailability)->update($values);
        } else {
            EmployeeAvailability::forceCreate($values + ['uuid' => (string) Str::uuid(), 'employee_id' => $this->employee->uuid]);
        }

        $this->cancelAvailabilityEdit();
    }

    public function removeAvailability(string $uuid)
    {
        $this->authorize('update', $this->employee);

        $this->employee->availability()->whereKey($uuid)->delete();

        if ($this->editingAvailability === $uuid) {
            $this->cancelAvailabilityEdit();
        }
    }

    public function close()
    {
        $this->reset();
        $this->resetValidation();
    }
};
?>

<div>
    @if ($employee)

        {{-- Same shell as admin-users-edit: bottom sheet on phones, centered modal from sm up.
             Escape is left to the reservation-edit modal while it is open on top of this one. --}}
        <div wire:click.self="close"
            x-on:keydown.escape.window="document.getElementById('reservation-edit-title') || $wire.close()"
            class="fixed inset-0 z-50 flex items-end sm:items-center justify-center overflow-y-auto bg-black/60 p-0 sm:p-4 transition-opacity">

            <div role="dialog" aria-modal="true" aria-labelledby="admin-employee-details-title"
                class="relative w-full max-w-lg md:max-w-3xl max-h-[95dvh] sm:max-h-[90dvh] overflow-y-auto rounded-t-2xl sm:rounded-2xl bg-white p-4 sm:p-6 shadow-2xl border border-brand-border">
                <button type="button" wire:click="close" aria-label="Zamknij"
                    class="absolute top-2 right-2 sm:top-4 sm:right-4 p-2 text-gray-400 hover:text-gray-600 transition-colors cursor-pointer">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>

                <div class="border-b border-brand-border pb-3 sm:pb-4 mb-4 pr-8">
                    <h3 id="admin-employee-details-title" class="text-xl sm:text-2xl font-bold text-brand-navy">
                        Szczegóły pracownika
                    </h3>
                    <p class="text-sm mt-1 break-words">
                        <span class="font-semibold text-brand-accent">{{ $employee->user?->name }}
                            {{ $employee->user?->surname }}</span>
                        <span class="text-brand-muted">· {{ $employee->user?->email }}</span>
                    </p>
                </div>

                <dl class="grid grid-cols-2 gap-x-4 gap-y-3 rounded-xl bg-brand-light-bg p-3 sm:p-4 text-sm">
                    <div class="col-span-2 sm:col-span-1">
                        <dt class="text-brand-muted"><i class="fa-regular fa-id-badge mr-1"></i> Pracownik od</dt>
                        <dd class="font-semibold text-brand-navy">{{ $employee->created_at?->format('d.m.Y') ?? '—' }}
                        </dd>
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <dt class="text-brand-muted"><i class="fa-regular fa-calendar mr-1"></i> Konto od</dt>
                        <dd class="font-semibold text-brand-navy">
                            {{ $employee->user?->created_at?->format('d.m.Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-brand-muted"><i class="fa-solid fa-phone mr-1"></i> Telefon</dt>
                        <dd class="font-semibold text-brand-navy">{{ $employee->user?->phone ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-brand-muted">Status</dt>
                        <dd>
                            <span @class([
                                'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold',
                                'bg-green-100 text-green-800' => $employee->active,
                                'bg-red-100 text-red-700' => !$employee->active,
                            ])>{{ $employee->active ? 'Aktywny' : 'Nieaktywny' }}</span>
                        </dd>
                    </div>
                </dl>

                <section class="mt-6">
                    <h4 class="text-base font-semibold text-brand-navy mb-2">Usługi</h4>
                    @if ($employee->services->isEmpty())
                        <p class="text-sm text-brand-muted italic">Pracownik nie ma przypisanych usług.</p>
                    @else
                        <ul class="flex flex-wrap gap-2">
                            @foreach ($employee->services as $service)
                                @php($booked = $this->upcomingByService[$service->uuid] ?? 0)
                                <li wire:key="employee-service-{{ $service->uuid }}"
                                    class="inline-flex items-center gap-1 rounded-full border border-brand-border bg-white py-1 pl-3 pr-1 text-sm text-brand-text">
                                    <span>
                                        {{ $service->name }}
                                        @if ($service->category)
                                            <span class="text-brand-muted">· {{ $service->category->name }}</span>
                                        @endif
                                    </span>
                                    <button type="button" wire:click="removeService('{{ $service->uuid }}')"
                                        wire:confirm="{{ $booked ? "Pracownik ma {$booked} nadchodzących rezerwacji tej usługi. Zostaną one bez zmian. Usunąć usługę?" : 'Usunąć tę usługę pracownikowi?' }}"
                                        aria-label="Usuń usługę {{ $service->name }}"
                                        class="inline-flex h-6 w-6 items-center justify-center rounded-full text-gray-400 cursor-pointer transition hover:bg-red-50 hover:text-red-600">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    @if ($this->availableServices->isNotEmpty())
                        <form wire:submit="addService" class="mt-3">
                            <label for="admin-employee-details-service" class="sr-only">Dodaj usługę</label>
                            <div class="flex flex-col gap-2 sm:flex-row">
                                <select id="admin-employee-details-service" wire:model="serviceToAdd"
                                    class="w-full rounded-lg border border-brand-border bg-white p-2.5 text-sm sm:flex-1">
                                    <option value="">Wybierz usługę…</option>
                                    @foreach ($this->availableServices as $categoryName => $services)
                                        <optgroup label="{{ $categoryName }}">
                                            @foreach ($services as $service)
                                                <option value="{{ $service->uuid }}">{{ $service->name }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                <button type="submit" wire:loading.attr="disabled"
                                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg bg-brand-accent text-white font-semibold shadow-md cursor-pointer transition duration-200 hover:brightness-110 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-brand-accent focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <i class="fa-solid fa-plus"></i> Dodaj usługę
                                </button>
                            </div>
                            @error('serviceToAdd')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </form>
                    @endif
                </section>

                <section class="mt-6">
                    <h4 class="text-base font-semibold text-brand-navy mb-2">Godziny pracy</h4>

                    @if ($this->reservationsOutsideHours)
                        <p
                            class="mb-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm font-medium text-amber-800">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            Nadchodzące rezerwacje poza godzinami pracy: {{ $this->reservationsOutsideHours }}.
                            Pozostają bez zmian, przenieś je w razie potrzeby.
                        </p>
                    @endif

                    <ul class="divide-y divide-brand-border rounded-xl border border-brand-border text-sm">
                        @foreach ($this->days as $dayNumber => $dayName)
                            <li wire:key="employee-day-{{ $dayNumber }}"
                                class="flex flex-col gap-2 p-3 sm:flex-row sm:items-center">
                                <span class="font-semibold text-brand-navy sm:w-32">{{ $dayName }}</span>
                                @if ($this->schedule->has($dayNumber))
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($this->schedule[$dayNumber] as $availability)
                                            <span wire:key="employee-availability-{{ $availability->uuid }}"
                                                @class([
                                                    'inline-flex items-center gap-1 rounded-full border py-1 pl-3 pr-1',
                                                    'border-brand-accent bg-brand-light-bg' =>
                                                        $editingAvailability === $availability->uuid,
                                                    'border-brand-border bg-white' =>
                                                        $editingAvailability !== $availability->uuid,
                                                ])>
                                                {{ $availability->start_time->format('H:i') }}–{{ $availability->end_time->format('H:i') }}
                                                <button type="button"
                                                    wire:click="editAvailability('{{ $availability->uuid }}')"
                                                    aria-label="Edytuj godziny {{ $dayName }} {{ $availability->start_time->format('H:i') }}"
                                                    class="inline-flex h-6 w-6 items-center justify-center rounded-full text-gray-400 cursor-pointer transition hover:bg-brand-light-bg hover:text-brand-navy">
                                                    <i class="fa-regular fa-pen-to-square"></i>
                                                </button>
                                                <button type="button"
                                                    wire:click="removeAvailability('{{ $availability->uuid }}')"
                                                    wire:confirm="Usunąć ten przedział godzin pracy?"
                                                    aria-label="Usuń godziny {{ $dayName }} {{ $availability->start_time->format('H:i') }}"
                                                    class="inline-flex h-6 w-6 items-center justify-center rounded-full text-gray-400 cursor-pointer transition hover:bg-red-50 hover:text-red-600">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </button>
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-brand-muted italic">Wolne</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>

                    <form wire:submit="saveAvailability" class="mt-3 rounded-xl bg-brand-light-bg p-3 sm:p-4">
                        <p class="mb-2 text-sm font-semibold text-brand-navy">
                            {{ $editingAvailability ? 'Zmień godziny pracy' : 'Dodaj godziny pracy' }}
                        </p>
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                            <div class="col-span-2 sm:col-span-1">
                                <label for="admin-employee-details-day"
                                    class="block text-sm text-brand-muted mb-1">Dzień</label>
                                <select id="admin-employee-details-day" wire:model="day"
                                    class="w-full rounded-lg border border-brand-border bg-white p-2.5 text-sm">
                                    @foreach ($this->days as $dayNumber => $dayName)
                                        <option value="{{ $dayNumber }}">{{ $dayName }}</option>
                                    @endforeach
                                </select>
                                @error('day')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="admin-employee-details-start"
                                    class="block text-sm text-brand-muted mb-1">Od</label>
                                <input id="admin-employee-details-start" type="time" wire:model="startTime"
                                    class="w-full rounded-lg border border-brand-border bg-white p-2.5 text-sm">
                            </div>
                            <div>
                                <label for="admin-employee-details-end"
                                    class="block text-sm text-brand-muted mb-1">Do</label>
                                <input id="admin-employee-details-end" type="time" wire:model="endTime"
                                    class="w-full rounded-lg border border-brand-border bg-white p-2.5 text-sm">
                            </div>
                        </div>
                        @error('startTime')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @error('endTime')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <div class="mt-3 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                            @if ($editingAvailability)
                                <button type="button" wire:click="cancelAvailabilityEdit"
                                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg border border-brand-border bg-white text-brand-text font-semibold cursor-pointer transition hover:bg-brand-light-bg">
                                    Anuluj
                                </button>
                            @endif
                            <button type="submit" wire:loading.attr="disabled"
                                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg bg-brand-accent text-white font-semibold shadow-md cursor-pointer transition duration-200 hover:brightness-110 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-brand-accent focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                {{ $editingAvailability ? 'Zapisz zmiany' : 'Dodaj godziny' }}
                            </button>
                        </div>
                    </form>
                </section>

                <section class="mt-6">
                    <h4 class="text-base font-semibold text-brand-navy mb-2">
                        Nadchodzące rezerwacje ({{ $this->upcomingCount }})
                    </h4>
                    @if ($this->upcomingReservations->isEmpty())
                        <p class="text-sm text-brand-muted italic">Brak nadchodzących rezerwacji.</p>
                    @else
                        <ul class="flex flex-col gap-2">
                            @foreach ($this->upcomingReservations as $reservation)
                                <li wire:key="employee-reservation-{{ $reservation->uuid }}"
                                    class="flex flex-col gap-2 rounded-lg border border-brand-border p-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="min-w-0 text-sm">
                                        <p class="font-semibold text-brand-navy">{{ $reservation->full_date_time }}
                                        </p>
                                        <p class="break-words text-brand-text">
                                            {{ $reservation->service?->name }}
                                            <span class="text-brand-muted">· {{ $reservation->user?->name }}
                                                {{ $reservation->user?->surname }}</span>
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span @class([
                                            'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold',
                                            'bg-amber-100 text-amber-800' => $reservation->status === 'pending',
                                            'bg-green-100 text-green-800' => $reservation->status === 'confirmed',
                                        ])>{{ $reservation->status_label }}</span>
                                        <button type="button"
                                            wire:click="$dispatch('editReservation', { uuid: '{{ $reservation->uuid }}' })"
                                            class="ml-auto inline-flex items-center justify-center gap-2 rounded-lg border border-brand-border bg-white px-3 py-1.5 text-sm font-semibold text-brand-text cursor-pointer transition hover:bg-brand-light-bg">
                                            <i class="fa-regular fa-pen-to-square"></i> Edytuj
                                        </button>
                                    </div>
                                </li>
                            @endforeach
                        </ul>

                        @if ($this->upcomingCount > $limit)
                            <button type="button" wire:click="showMore"
                                class="mt-3 w-full rounded-lg border border-brand-border bg-white px-3 py-1.5 text-sm font-semibold text-brand-text cursor-pointer transition hover:bg-brand-light-bg">
                                Pokaż więcej
                            </button>
                        @endif
                    @endif
                </section>

                <section class="mt-6">
                    <div class="mb-2 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <h4 class="text-base font-semibold text-brand-navy">Rezerwacje w tygodniu</h4>
                        <label
                            class="flex w-full flex-col gap-1 text-sm text-gray-600 sm:w-auto sm:flex-row sm:items-center sm:gap-2">
                            Okres:
                            <select wire:model.live="weeks"
                                class="w-full rounded-lg border border-brand-border bg-white px-3 py-2 text-sm text-brand-text focus:outline-none focus:ring-2 focus:ring-brand-accent sm:w-auto sm:py-1.5">
                                <option value="4">4 tygodnie</option>
                                <option value="12">12 tygodni</option>
                                <option value="52">52 tygodnie</option>
                            </select>
                        </label>
                    </div>

                    {{-- the key changes with the data, so the chart is rebuilt after a cancel or reschedule too --}}
                    <div class="h-64 min-w-0 sm:h-72"
                        wire:key="employee-bookings-{{ $employee->uuid }}-{{ $weeks }}-{{ md5(json_encode($this->bookings['values'])) }}"
                        x-data="statsChart({ type: 'bar', label: 'Ilosc Rezerwacji', ...@js($this->bookings) })">
                        <canvas x-ref="canvas" wire:ignore></canvas>
                    </div>
                </section>

            </div>
        </div>
    @endif

    {{-- opened from the "Edytuj" buttons above; rendered later in the DOM, so it sits on top of this modal --}}
    @livewire('reservation-edit')
</div>
