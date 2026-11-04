<?php

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Livewire\Attributes\Computed;
use App\Models\Reservation;
use App\Models\ServiceCategory;

new class extends Component {
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'reservation_status')]
    public string $status = '';

    #[Url]
    public string $service = '';

    #[Url]
    public string $category = '';

    #[Url(as: 'reservations_from')]
    public string $from = '';

    #[Url(as: 'reservations_to')]
    public string $to = '';

    #[Url(as: 'reservations_order')]
    public string $direction = 'desc';

    // reservation-edit dispatches this after a status change or cancel
    protected $listeners = ['reservationUpdated' => '$refresh'];

    // null for an admin without an employee profile; the route lets them in too
    #[Computed]
    public function employee()
    {
        return auth()->user()->employee;
    }

    #[Computed]
    public function services()
    {
        return $this->employee->services()->orderBy('name')->get();
    }

    #[Computed]
    public function categories()
    {
        return ServiceCategory::whereIn('uuid', $this->services->pluck('category_id'))
            ->orderBy('name')
            ->get();
    }

    // a filter date as Y-m-d, or null when the field is empty or not a real date
    protected function date(string $value): ?string
    {
        // the round trip rejects overflowing dates like 2026-02-30
        $date = \DateTime::createFromFormat('!Y-m-d', $value);

        return $date && $date->format('Y-m-d') === $value ? $value : null;
    }

    #[Computed]
    public function dateError(): ?string
    {
        $from = $this->date($this->from);
        $to = $this->date($this->to);

        return $from && $to && $from > $to ? 'Data początkowa nie może być późniejsza niż końcowa.' : null;
    }

    // open visits of this employee that have started; only they or an admin can close them
    #[Computed]
    public function toCompleteCount(): int
    {
        return $this->employee->reservations()->active()->past()->count();
    }

    #[Computed]
    public function reservations()
    {
        $direction = $this->direction === 'asc' ? 'asc' : 'desc';

        return $this->employee
            ->reservations()
            ->with(['user:uuid,name,surname,email', 'service:uuid,name,category_id', 'service.category:uuid,name'])
            ->when($this->search, fn($q) => $q->whereHas('user', fn($q) => $q->whereAny(['name', 'surname', 'email', 'phone'], 'like', "%{$this->search}%")))
            ->when($this->status === 'to_complete', fn($q) => $q->active()->past())
            ->when(array_key_exists($this->status, Reservation::STATUSES), fn($q) => $q->where('status', $this->status))
            ->when($this->service, fn($q) => $q->where('service_id', $this->service))
            ->when($this->category, fn($q) => $q->whereHas('service', fn($q) => $q->where('category_id', $this->category)))
            ->when($this->date($this->from), fn($q, $from) => $q->whereDate('reservation_date', '>=', $from))
            ->when($this->date($this->to), fn($q, $to) => $q->whereDate('reservation_date', '<=', $to))
            ->orderBy('reservation_date', $direction)
            ->orderBy('start_time', $direction)
            ->paginate(10, pageName: 'reservations_page');
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'status', 'service', 'category', 'from', 'to'])) {
            $this->resetPage('reservations_page');
        }
    }

    public function updatedCategory(): void
    {
        $this->service = '';
    }

    public function toggleDirection(): void
    {
        $this->direction = $this->direction === 'asc' ? 'desc' : 'asc';
    }

    public function showToComplete(): void
    {
        $this->reset('search', 'service', 'category', 'from', 'to');
        $this->status = 'to_complete';
        $this->direction = 'asc';
        $this->resetPage('reservations_page');
    }
};
?>

<div class="mt-4">
    @if (!$this->employee)
        <p class="p-2 text-brand-muted">To konto nie ma profilu pracownika, więc nie ma tu żadnych wizyt.</p>
    @else
        @if ($this->toCompleteCount && $status !== 'to_complete')
            <div
                class="mb-3 flex flex-col gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800 sm:flex-row sm:items-center sm:justify-between">
                <p>
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    Wizyty, które już się odbyły i czekają na zakończenie: {{ $this->toCompleteCount }}.
                </p>
                <button type="button" wire:click="showToComplete"
                    class="shrink-0 font-semibold underline cursor-pointer hover:no-underline">Pokaż</button>
            </div>
        @endif

        <div class="mb-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-[2fr_1fr_1fr_1fr_auto_auto]">
            <input type="text" wire:model.live.debounce.300ms="search"
                placeholder="Szukaj klienta po imieniu, nazwisku, e-mailu lub telefonie"
                class="w-full rounded border border-brand-border p-2 sm:col-span-2 lg:col-span-1">

            <select wire:model.live="status" class="w-full rounded border border-brand-border p-2">
                <option value="">Wszystkie statusy</option>
                <option value="to_complete">Do zakończenia</option>
                @foreach (Reservation::STATUSES as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>

            <select wire:model.live="category" class="w-full rounded border border-brand-border p-2">
                <option value="">Wszystkie kategorie</option>
                @foreach ($this->categories as $cat)
                    <option value="{{ $cat->uuid }}">{{ $cat->name }}</option>
                @endforeach
            </select>

            <select wire:model.live="service" class="w-full rounded border border-brand-border p-2">
                <option value="">Wszystkie usługi</option>
                @foreach ($this->services->when($category, fn($services) => $services->where('category_id', $category)) as $svc)
                    <option value="{{ $svc->uuid }}">{{ $svc->name }}</option>
                @endforeach
            </select>

            <label class="flex items-center gap-2 text-sm text-gray-600">
                Od
                <input type="date" wire:model.live="from" aria-label="Data od"
                    class="w-full rounded border border-brand-border p-2 text-brand-text">
            </label>

            <label class="flex items-center gap-2 text-sm text-gray-600">
                Do
                <input type="date" wire:model.live="to" aria-label="Data do"
                    class="w-full rounded border border-brand-border p-2 text-brand-text">
            </label>
        </div>

        @if ($this->dateError)
            <p class="mb-3 text-sm text-red-600">{{ $this->dateError }}</p>
        @else
            {{-- On phones every row is a card with its own labels; from md up it's a normal table --}}
            <table class="block w-full text-left md:table">
                <thead class="hidden md:table-header-group">
                    <tr class="border-b border-brand-border">
                        <th class="p-2">
                            <button type="button" wire:click="toggleDirection"
                                class="inline-flex items-center gap-1 font-bold cursor-pointer hover:text-brand-accent">
                                Termin
                                <i
                                    class="fa-solid text-xs {{ $direction === 'asc' ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                <span
                                    class="sr-only">{{ $direction === 'asc' ? 'od najstarszych' : 'od najnowszych' }}</span>
                            </button>
                        </th>
                        <th class="p-2">Klient</th>
                        <th class="p-2">Usługa</th>
                        <th class="p-2">Cena</th>
                        <th class="p-2">Status</th>
                        <th class="p-2"><span class="sr-only">Akcje</span></th>
                    </tr>
                </thead>
                <tbody class="flex flex-col gap-2 md:table-row-group">
                    @forelse ($this->reservations as $reservation)
                        <tr wire:key="reservation-{{ $reservation->uuid }}"
                            class="grid grid-cols-[auto_1fr] gap-x-3 gap-y-1 rounded border border-brand-border p-3 md:table-row md:rounded-none md:border-0 md:border-b md:p-0">
                            <td class="text-xs text-gray-500 md:hidden">Termin</td>
                            <td class="font-semibold text-brand-navy md:table-cell md:p-2 md:whitespace-nowrap">
                                {{ $reservation->full_date_time }}
                            </td>

                            <td class="text-xs text-gray-500 md:hidden">Klient</td>
                            <td class="min-w-0 break-words md:table-cell md:p-2">
                                {{ $reservation->user?->name }} {{ $reservation->user?->surname }}
                                <span
                                    class="block text-xs text-brand-muted break-all">{{ $reservation->user?->email }}</span>
                            </td>

                            <td class="text-xs text-gray-500 md:hidden">Usługa</td>
                            <td class="min-w-0 break-words md:table-cell md:p-2">
                                {{ $reservation->service?->name }}
                                <span
                                    class="block text-xs text-brand-muted">{{ $reservation->service?->category?->name }}</span>
                            </td>

                            <td class="text-xs text-gray-500 md:hidden">Cena</td>
                            <td class="md:table-cell md:p-2 md:whitespace-nowrap">
                                {{ number_format((float) $reservation->price, 2, ',', ' ') }} zł
                            </td>

                            <td class="text-xs text-gray-500 md:hidden">Status</td>
                            <td class="md:table-cell md:p-2"><x-reservation-status :status="$reservation->status" /></td>

                            <td class="col-span-2 mt-1 md:mt-0 md:table-cell md:p-2 md:text-right">
                                <button type="button"
                                    wire:click="$dispatch('editReservation', { uuid: '{{ $reservation->uuid }}' })"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-brand-border bg-white px-3 py-1.5 text-sm font-semibold text-brand-text cursor-pointer transition hover:bg-brand-light-bg md:w-auto">
                                    <i class="fa-regular fa-pen-to-square"></i> Zarządzaj
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr class="block md:table-row">
                            <td colspan="6" class="block p-2 md:table-cell">Brak wizyt.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-3 cursor-pointer">{{ $this->reservations->links() }}</div>
        @endif

        {{-- confirm, complete and cancel; the same modal admins and clients use --}}
        @livewire('reservation-edit')
    @endif
</div>
