<?php

use App\Livewire\Forms\ReservationForm;
use App\Models\Reservation;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    public ?Reservation $reservation = null;
    // details | reschedule | cancel
    public string $mode = 'details';
    public ?string $selectedDay = null;
    public ?string $selectedHour = null;
    public ?string $notice = null;
    public ReservationForm $form;

    #[On('editReservation')]
    public function open($uuid)
    {
        $this->close();
        // admins edit any reservation (admin-employee-details); everyone else is scoped
        // to their own reservations, so a foreign uuid ends in 404
        $user = auth()->user();
        $query = $user->role === 'admin' ? Reservation::query() : $user->reservations();

        // abort() instead of findOrFail(): Livewire tests rethrow ModelNotFoundException instead of answering 404
        $this->reservation = $query->with(['user', 'service.category', 'employee.user', 'employee.availability'])->find($uuid) ?? abort(404);
    }

    #[Computed]
    public function workingDays()
    {
        return $this->reservation->employee->availability->pluck('day_of_week')->unique()->values()->all();
    }

    public function startReschedule()
    {
        $this->authorize('update', $this->reservation);
        $this->reset('selectedDay', 'selectedHour', 'notice');
        $this->resetValidation();
        $this->mode = 'reschedule';
    }

    public function startCancel()
    {
        $this->authorize('cancel', $this->reservation);
        $this->reset('notice');
        $this->mode = 'cancel';
    }

    public function back()
    {
        $this->reset('mode', 'selectedDay', 'selectedHour');
        $this->resetValidation();
    }

    #[On('daySelected')]
    public function setDate($date)
    {
        if ($this->mode !== 'reschedule') {
            return;
        }

        $this->selectedDay = $date;
        $this->reset('selectedHour');
    }

    #[On('hourSelected')]
    public function setHour($time)
    {
        if ($this->mode !== 'reschedule') {
            return;
        }

        $this->selectedHour = $time;
    }

    public function reschedule()
    {
        $this->authorize('update', $this->reservation);

        $this->form->fill([
            'service_id' => $this->reservation->service_id,
            'employee_id' => $this->reservation->employee_id,
            'date' => $this->selectedDay,
            'hour' => $this->selectedHour,
            'reservation_id' => $this->reservation->uuid,
        ]);
        $this->form->validate();
        $this->form->reschedule($this->reservation);

        $this->finish('Termin rezerwacji został zmieniony.');
    }

    public function cancelReservation()
    {
        $this->authorize('cancel', $this->reservation);

        $this->reservation->update(['status' => 'cancelled']);

        $this->finish('Rezerwacja została anulowana.');
    }

    public function confirm()
    {
        $this->authorize('confirm', $this->reservation);

        $this->reservation->update(['status' => 'confirmed']);

        $this->finish('Rezerwacja została potwierdzona.');
    }

    // only completed visits count as income in the admin stats and reports
    public function complete()
    {
        $this->authorize('complete', $this->reservation);

        $this->reservation->update(['status' => 'completed']);

        $this->finish('Wizyta została oznaczona jako zakończona.');
    }

    protected function finish(string $notice)
    {
        $this->reservation->refresh();
        $this->back();
        $this->notice = $notice;
        $this->dispatch('reservationUpdated');
    }

    public function close()
    {
        $this->reset();
        $this->resetValidation();
    }
};
?>

<div>
    @if ($reservation)

        <x-modal id="reservation-edit-title" :title="match ($mode) {
            'reschedule' => 'Zmiana terminu',
            'cancel' => 'Anulowanie rezerwacji',
            default => 'Szczegóły rezerwacji',
        }">
            <x-slot:subtitle>
                <p class="text-sm mt-1 break-words">
                    <span class="font-semibold text-brand-accent">{{ $reservation->service->name }}</span>
                    @if ($reservation->service->category)
                        <span class="text-brand-muted">· {{ $reservation->service->category->name }}</span>
                    @endif
                </p>
            </x-slot:subtitle>

            {{-- Summary stays visible in every step, so the client always sees what they are changing. --}}
            <dl class="grid grid-cols-2 gap-x-4 gap-y-3 rounded-xl bg-brand-light-bg p-3 sm:p-4 text-sm">
                <div class="col-span-2 sm:col-span-1">
                    <dt class="text-brand-muted"><i class="fa-regular fa-calendar mr-1"></i> Termin</dt>
                    <dd class="font-semibold text-brand-navy">{{ $reservation->full_date_time }}</dd>
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <dt class="text-brand-muted"><i class="fa-regular fa-user mr-1"></i> Pracownik</dt>
                    <dd class="font-semibold text-brand-navy">
                        {{ $reservation->employee->user?->name }} {{ $reservation->employee->user?->surname }}
                    </dd>
                </div>
                @if ($reservation->user_id !== auth()->id())
                    <div class="col-span-2">
                        <dt class="text-brand-muted"><i class="fa-regular fa-address-card mr-1"></i> Klient</dt>
                        <dd class="font-semibold text-brand-navy break-words">
                            {{ $reservation->user?->name }} {{ $reservation->user?->surname }}
                            <span class="font-normal text-brand-muted">· {{ $reservation->user?->email }}</span>
                        </dd>
                    </div>
                @endif
                <div>
                    <dt class="text-brand-muted">Cena</dt>
                    <dd class="font-semibold text-brand-navy">
                        {{ number_format($reservation->price, 2, ',', ' ') }} zł
                    </dd>
                </div>
                <div>
                    <dt class="text-brand-muted">Status</dt>
                    <dd><x-reservation-status :status="$reservation->status" /></dd>
                </div>
            </dl>

            @if ($notice)
                <p
                    class="mt-4 rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-sm font-medium text-green-700">
                    <i class="fa-solid fa-circle-check"></i> {{ $notice }}
                </p>
            @endif

            @if ($mode === 'details')
                @canany(['update', 'cancel', 'confirm', 'complete'], $reservation)
                    <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:flex-wrap sm:justify-end">
                        @can('cancel', $reservation)
                            <button type="button" wire:click="startCancel"
                                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg border border-red-200 bg-white text-red-600 font-semibold cursor-pointer transition hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500/50">
                                <i class="fa-regular fa-trash-can"></i> Anuluj rezerwację
                            </button>
                        @endcan
                        @can('complete', $reservation)
                            <button type="button" wire:click="complete" wire:loading.attr="disabled"
                                wire:confirm="Oznaczyć wizytę jako zakończoną? Tej operacji nie można cofnąć."
                                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg bg-green-600 text-white font-semibold shadow-md cursor-pointer transition hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500/50 disabled:opacity-60">
                                <i class="fa-solid fa-flag-checkered"></i> Zakończ wizytę
                            </button>
                        @endcan
                        @can('confirm', $reservation)
                            <button type="button" wire:click="confirm" wire:loading.attr="disabled"
                                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg bg-green-600 text-white font-semibold shadow-md cursor-pointer transition hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500/50 disabled:opacity-60">
                                <i class="fa-solid fa-check"></i> Potwierdź
                            </button>
                        @endcan
                        @can('update', $reservation)
                            <button type="button" wire:click="startReschedule"
                                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg bg-brand-accent text-white font-semibold shadow-md cursor-pointer transition duration-200 hover:brightness-110 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-brand-accent focus:ring-offset-2">
                                <i class="fa-regular fa-calendar"></i> Zmień termin
                            </button>
                        @endcan
                    </div>
                @else
                    <p class="mt-4 text-sm text-brand-muted italic">Tej rezerwacji nie można już zmienić ani anulować.
                    </p>
                @endcanany
            @elseif ($mode === 'reschedule')
                <livewire:employee-week-calendar :working-days="$this->workingDays" :key="'reschedule-week-' . $reservation->uuid" />

                @if ($selectedDay)
                    @livewire(
                        'employee-hour-calendar',
                        [
                            'selectedDay' => $selectedDay,
                            'selectedEmployee' => $reservation->employee,
                            'duration' => $reservation->service->duration,
                            'ignoreReservation' => $reservation->uuid,
                        ],
                        key('reschedule-hours-' . $reservation->uuid . '-' . $selectedDay)
                    )
                @endif

                @error('form.date')
                    <p class="mt-3 text-sm text-red-600">{{ $message }}</p>
                @enderror
                @error('form.hour')
                    <p class="mt-3 text-sm text-red-600">{{ $message }}</p>
                @enderror

                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <button type="button" wire:click="back"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg border border-brand-border bg-white text-brand-text font-semibold cursor-pointer transition hover:bg-brand-light-bg">
                        Wróć
                    </button>
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-3">
                        @if ($selectedHour)
                            <p class="text-sm text-brand-muted">
                                Nowy termin:
                                <span class="font-semibold text-brand-navy">
                                    {{ \Carbon\Carbon::parse($selectedDay)->format('d.m.Y') }} {{ $selectedHour }}
                                </span>
                            </p>
                        @endif
                        <button type="button" wire:click="reschedule" wire:loading.attr="disabled"
                            @disabled(!$selectedHour)
                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg bg-green-600 text-white font-semibold shadow-md cursor-pointer transition hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500/50 disabled:opacity-50 disabled:cursor-not-allowed">
                            Zapisz nowy termin
                        </button>
                    </div>
                </div>
            @elseif ($mode === 'cancel')
                <div class="mt-4 flex gap-3 rounded-xl border border-red-200 bg-red-50 p-3 sm:p-4 text-sm text-red-800">
                    <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
                    <p>
                        Czy na pewno chcesz anulować tę rezerwację? Termin zostanie zwolniony dla innych klientów,
                        a tej operacji nie można cofnąć.
                    </p>
                </div>

                <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <button type="button" wire:click="back"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg border border-brand-border bg-white text-brand-text font-semibold cursor-pointer transition hover:bg-brand-light-bg">
                        Wróć
                    </button>
                    <button type="button" wire:click="cancelReservation" wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg bg-red-600 text-white font-semibold shadow-md cursor-pointer transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500/50 disabled:opacity-60">
                        <i class="fa-regular fa-trash-can"></i> Tak, anuluj rezerwację
                    </button>
                </div>
            @endif
        </x-modal>
    @endif
</div>
