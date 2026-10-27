<?php

use App\Models\Employee;
use App\Support\AdminStats;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    public ?Employee $employee = null;
    public int $weeks = 12;
    // how many upcoming reservations are listed, grows with showMore()
    public int $limit = 10;

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

    public function close()
    {
        $this->reset();
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
                                <li wire:key="employee-service-{{ $service->uuid }}"
                                    class="rounded-full border border-brand-border bg-white px-3 py-1 text-sm text-brand-text">
                                    {{ $service->name }}
                                    @if ($service->category)
                                        <span class="text-brand-muted">· {{ $service->category->name }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
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
                                        <p class="font-semibold text-brand-navy">{{ $reservation->full_date_time }}</p>
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
