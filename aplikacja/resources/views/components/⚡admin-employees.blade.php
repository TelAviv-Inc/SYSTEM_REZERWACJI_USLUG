<?php

use Livewire\Component;
use App\Models\Employee;
use App\Models\ServiceCategory;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
new class extends Component {
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $category = '';

    #[Url]
    public string $service = '';

    public function updated(string $property): void
    {
        // A service from the previous category would hide every employee
        if ($property === 'category') {
            $this->service = '';
        }

        if (in_array($property, ['search', 'category', 'service', 'status'], true)) {
            $this->resetPage();
        }
    }

    #[Computed]
    public function categories()
    {
        return ServiceCategory::with('services:uuid,category_id,name')->orderBy('name')->get();
    }

    #[Computed]
    public function services()
    {
        return $this->categories->when($this->category, fn($c) => $c->where('uuid', $this->category))->flatMap->services;
    }

    #[Computed]
    public function employees()
    {
        return Employee::query()
            ->with(['user:uuid,name,surname,email,phone', 'services:uuid,name'])
            ->withCount('reservations')
            //gdy
            ->when(
                $this->search,
                fn($q) => $q->whereHas(
                    'user',
                    fn($q) => $q
                        ->where('name', 'like', "%{$this->search}%")
                        ->orWhere('surname', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%")
                        ->orWhere('phone', 'like', "%{$this->search}%"),
                ),
            )
            ->when($this->category, fn($q) => $q->whereHas('services', fn($q) => $q->where('category_id', $this->category)))
            ->when($this->service, fn($q) => $q->whereHas('services', fn($q) => $q->where('services.uuid', $this->service)))
            ->when(in_array($this->status, ['active', 'inactive'], true), fn($q) => $q->where('active', $this->status === 'active'))
            ->latest()
            ->paginate(10);
    }
};
?>

<div class="mt-4">
    <div class="mb-3 flex flex-col gap-2 sm:flex-row">
        <input type="text" wire:model.live.debounce.300ms="search"
            placeholder="Szukaj po imieniu, nazwisku, e-mailu lub numerze telefonu"
            class="w-full rounded border border-brand-border p-2">

        <select wire:model.live="category" class="w-full rounded border border-brand-border p-2 sm:w-auto">
            <option value="">Wszystkie kategorie</option>
            @foreach ($this->categories as $cat)
                <option value="{{ $cat->uuid }}">{{ $cat->name }}</option>
            @endforeach
        </select>

        <select wire:model.live="service" class="w-full rounded border border-brand-border p-2 sm:w-auto">
            <option value="">Wszystkie usługi</option>
            @foreach ($this->services as $srv)
                <option value="{{ $srv->uuid }}">{{ $srv->name }}</option>
            @endforeach
        </select>

        <select wire:model.live="status" class="w-full rounded border border-brand-border p-2 sm:w-auto">
            <option value="">Wszystkie statusy</option>
            <option value="active">Aktywny</option>
            <option value="inactive">Nieaktywny</option>
        </select>
    </div>

    {{-- On phones every row is a card with its own labels; from md up it's a normal table --}}
    <table class="block w-full text-left md:table">
        <thead class="hidden md:table-header-group">
            <tr class="border-b border-brand-border">
                <th class="p-2">Imię</th>
                <th class="p-2">E-mail</th>
                <th class="p-2">Status</th>
                <th class="p-2"><span class="sr-only">Akcje</span></th>
            </tr>
        </thead>
        <tbody class="flex flex-col gap-2 md:table-row-group">
            @forelse ($this->employees as $emp)
                <tr wire:key="employee-{{ $emp->uuid }}"
                    class="grid grid-cols-[auto_1fr] gap-x-3 gap-y-1 rounded border border-brand-border p-3 md:table-row md:rounded-none md:border-0 md:border-b md:p-0">
                    <td class="text-xs text-gray-500 md:hidden">Imię</td>
                    <td class="md:table-cell md:p-2">{{ $emp->user->name . ' ' . $emp->user->surname }}</td>

                    <td class="text-xs text-gray-500 md:hidden">E-mail</td>
                    <td class="min-w-0 break-all md:table-cell md:p-2 md:break-normal">{{ $emp->user->email }}</td>

                    <td class="text-xs text-gray-500 md:hidden">Status</td>
                    <td class="md:table-cell md:p-2">{{ $emp->active ? 'Aktywny' : 'Nieaktywny' }}</td>

                    <td class="col-span-2 mt-1 md:mt-0 md:table-cell md:p-2 md:text-right">
                        <button type="button"
                            wire:click="$dispatch('detailsEmployee', { uuid: '{{ $emp->uuid }}' })"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-brand-border bg-white px-3 py-1.5 text-sm font-semibold text-brand-text cursor-pointer transition hover:bg-brand-light-bg md:w-auto">
                            <i class="fa-solid fa-circle-info"></i> Szczegóły
                        </button>
                    </td>
                </tr>
            @empty
                <tr class="block md:table-row">
                    <td colspan="5" class="block p-2 md:table-cell">Brak użytkowników.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-3 cursor-pointer">{{ $this->employees->links() }}</div>

    {{-- @livewire('admin-users-edit') --}}
</div>
