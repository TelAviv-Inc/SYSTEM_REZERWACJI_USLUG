<?php

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $category = '';
    public string $status = '';

    // form modal; editing is the uuid of the service being changed, null adds a new one
    public bool $showForm = false;
    public ?string $editing = null;
    public string $categoryId = '';
    public string $name = '';
    public string $description = '';
    public $duration = 60;
    public $price = '';
    public bool $active = true;

    public function mount(): void
    {
        $this->authorize('viewAny', Service::class);
    }

    // Any filter change goes back to page 1, otherwise you can land on an empty page
    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'category', 'status'], true)) {
            $this->resetPage('services_page');
        }
    }

    #[Computed]
    public function categories()
    {
        return ServiceCategory::orderBy('name')->get(['uuid', 'name']);
    }

    #[Computed]
    public function services()
    {
        return Service::query()
            ->with('category:uuid,name')
            ->withCount(['employees', 'reservations', 'reservations as upcoming_reservations_count' => fn($q) => $q->upcoming()])
            ->when($this->search, fn($q) => $q->where(fn($q) => $q->where('name', 'like', "%{$this->search}%")->orWhere('description', 'like', "%{$this->search}%")))
            ->when($this->category, fn($q) => $q->where('category_id', $this->category))
            ->when(in_array($this->status, ['active', 'inactive'], true), fn($q) => $q->where('active', $this->status === 'active' ? 1 : 0))
            ->orderBy('name')
            ->paginate(10, pageName: 'services_page');
    }

    protected function rules(): array
    {
        return [
            'categoryId' => ['required', Rule::exists('service_categories', 'uuid')],
            'name' => [
                'required',
                'string',
                'max:128',
                // the slug is unique and made from the name, so two names with the same slug can't coexist
                function ($attribute, $value, $fail) {
                    $slug = Str::slug($value);

                    if ($slug === '') {
                        $fail('Nazwa musi zawierać litery lub cyfry');
                    } elseif (Service::where('slug', $slug)->when($this->editing, fn($q) => $q->whereKeyNot($this->editing))->exists()) {
                        $fail('Usługa o tej nazwie już istnieje');
                    }
                },
            ],
            'description' => ['required', 'string', 'max:400'],
            'duration' => ['required', 'integer', 'min:5', 'max:480'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999.99', 'decimal:0,2'],
            'active' => ['boolean'],
        ];
    }

    protected function messages(): array
    {
        return [
            'categoryId.required' => 'Wybierz kategorię',
            'categoryId.exists' => 'Wybrana kategoria nie istnieje',
            'name.required' => 'Podaj nazwę usługi',
            'name.max' => 'Nazwa może mieć najwyżej 128 znaków',
            'description.required' => 'Podaj opis usługi',
            'description.max' => 'Opis może mieć najwyżej 400 znaków',
            'duration.*' => 'Czas trwania to od 5 do 480 minut',
            'price.decimal' => 'Cena może mieć najwyżej dwa miejsca po przecinku',
            'price.*' => 'Podaj cenę od 0 do 99999,99 zł',
        ];
    }

    public function create()
    {
        $this->authorize('create', Service::class);
        $this->closeForm();
        // a new service lands in the filtered category, that's usually where the admin is working
        $this->categoryId = $this->category;
        $this->showForm = true;
    }

    public function edit(string $uuid)
    {
        $service = Service::findOrFail($uuid);
        $this->authorize('update', $service);

        $this->closeForm();
        $this->editing = $service->uuid;
        $this->categoryId = (string) $service->category_id;
        $this->name = $service->name;
        $this->description = $service->description;
        $this->duration = $service->duration;
        $this->price = (string) $service->price;
        $this->active = (bool) $service->active;
        $this->showForm = true;
    }

    public function save()
    {
        $service = $this->editing ? Service::findOrFail($this->editing) : null;
        $this->authorize($service ? 'update' : 'create', $service ?? Service::class);

        $this->name = trim($this->name);
        $this->description = trim($this->description);
        // Polish keyboards type a comma
        $this->price = str_replace(',', '.', trim((string) $this->price));
        $this->validate();

        $values = [
            'category_id' => $this->categoryId,
            'name' => $this->name,
            'description' => $this->description,
            'duration' => (int) $this->duration,
            'price' => $this->price,
            'active' => $this->active ? 1 : 0,
        ];

        if ($service) {
            $service->update($values);
        } else {
            Service::forceCreate($values + ['uuid' => (string) Str::uuid()]);
        }

        $this->closeForm();
    }

    // booked visits stay as they are; an inactive service just can't be booked or given to an employee
    public function toggleActive(string $uuid)
    {
        $service = Service::findOrFail($uuid);
        $this->authorize('update', $service);

        $service->update(['active' => $service->active ? 0 : 1]);
    }

    public function delete(string $uuid)
    {
        $service = Service::findOrFail($uuid);
        $this->authorize('delete', $service);

        // employee_services rows go with it (cascade)
        $service->delete();

        if ($this->editing === $uuid) {
            $this->closeForm();
        }
    }

    public function closeForm()
    {
        $this->reset('showForm', 'editing', 'categoryId', 'name', 'description', 'duration', 'price', 'active');
        $this->resetValidation();
    }
};
?>

<div class="mt-4">
    <div class="mb-3 flex flex-col gap-2 sm:flex-row">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Szukaj po nazwie lub opisie"
            class="w-full rounded border border-brand-border p-2">

        <select wire:model.live="category" class="w-full rounded border border-brand-border p-2 sm:w-auto">
            <option value="">Wszystkie kategorie</option>
            @foreach ($this->categories as $cat)
                <option value="{{ $cat->uuid }}">{{ $cat->name }}</option>
            @endforeach
        </select>

        <select wire:model.live="status" class="w-full rounded border border-brand-border p-2 sm:w-auto">
            <option value="">Wszystkie statusy</option>
            <option value="active">Aktywna</option>
            <option value="inactive">Nieaktywna</option>
        </select>

        <button type="button" wire:click="create" @disabled($this->categories->isEmpty())
            title="{{ $this->categories->isEmpty() ? 'Najpierw dodaj kategorię' : '' }}"
            class="inline-flex shrink-0 items-center justify-center gap-2 px-4 py-2 rounded-lg bg-brand-accent text-white font-semibold shadow-md cursor-pointer transition duration-200 hover:brightness-110 hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
            <i class="fa-solid fa-plus"></i> Dodaj usługę
        </button>
    </div>

    {{-- On phones every row is a card with its own labels; from md up it's a normal table --}}
    <table class="block w-full text-left md:table">
        <thead class="hidden md:table-header-group">
            <tr class="border-b border-brand-border">
                <th class="p-2">Nazwa</th>
                <th class="p-2">Cena</th>
                <th class="p-2">Czas</th>
                <th class="p-2">Pracownicy</th>
                <th class="p-2">Status</th>
                <th class="p-2"><span class="sr-only">Akcje</span></th>
            </tr>
        </thead>
        <tbody class="flex flex-col gap-2 md:table-row-group">
            @forelse ($this->services as $service)
                <tr wire:key="service-{{ $service->uuid }}"
                    class="grid grid-cols-[auto_1fr] gap-x-3 gap-y-1 rounded border border-brand-border p-3 md:table-row md:rounded-none md:border-0 md:border-b md:p-0">
                    <td class="text-xs text-gray-500 md:hidden">Nazwa</td>
                    <td class="min-w-0 break-words md:table-cell md:p-2">
                        <span class="font-semibold text-brand-navy">{{ $service->name }}</span>
                        <span
                            class="block text-xs text-brand-muted">{{ $service->category?->name ?? 'Bez kategorii' }}</span>
                    </td>

                    <td class="text-xs text-gray-500 md:hidden">Cena</td>
                    <td class="md:table-cell md:p-2 md:whitespace-nowrap">
                        {{ number_format((float) $service->price, 2, ',', ' ') }} zł</td>

                    <td class="text-xs text-gray-500 md:hidden">Czas</td>
                    <td class="md:table-cell md:p-2 md:whitespace-nowrap">{{ $service->duration }} min</td>

                    <td class="text-xs text-gray-500 md:hidden">Pracownicy</td>
                    <td class="md:table-cell md:p-2">{{ $service->employees_count }}</td>

                    <td class="text-xs text-gray-500 md:hidden">Status</td>
                    <td class="md:table-cell md:p-2">
                        <span @class([
                            'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold',
                            'bg-green-100 text-green-800' => $service->active,
                            'bg-red-100 text-red-700' => !$service->active,
                        ])>{{ $service->active ? 'Aktywna' : 'Nieaktywna' }}</span>
                    </td>

                    <td
                        class="col-span-2 mt-1 flex flex-wrap gap-2 md:mt-0 md:table-cell md:p-2 md:text-right md:whitespace-nowrap">
                        <button type="button" wire:click="edit('{{ $service->uuid }}')"
                            class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg border border-brand-border bg-white px-3 py-1.5 text-sm font-semibold text-brand-text cursor-pointer transition hover:bg-brand-light-bg md:flex-none">
                            <i class="fa-regular fa-pen-to-square"></i> Edytuj
                        </button>
                        <button type="button" wire:click="toggleActive('{{ $service->uuid }}')"
                            @if ($service->active && $service->upcoming_reservations_count) wire:confirm="Usługa ma {{ $service->upcoming_reservations_count }} nadchodzących rezerwacji. Zostaną one bez zmian, ale nowych nie będzie można dodać. Dezaktywować usługę?" @endif
                            class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg border border-brand-border bg-white px-3 py-1.5 text-sm font-semibold text-brand-text cursor-pointer transition hover:bg-brand-light-bg md:flex-none">
                            @if ($service->active)
                                <i class="fa-solid fa-toggle-off"></i> Dezaktywuj
                            @else
                                <i class="fa-solid fa-toggle-on"></i> Aktywuj
                            @endif
                        </button>
                        @if ($service->reservations_count)
                            <button type="button" disabled
                                title="Usługa ma rezerwacje, więc można ją tylko dezaktywować."
                                class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg border border-brand-border bg-white px-3 py-1.5 text-sm font-semibold text-gray-300 cursor-not-allowed md:flex-none">
                                <i class="fa-regular fa-trash-can"></i> Usuń
                            </button>
                        @else
                            <button type="button" wire:click="delete('{{ $service->uuid }}')"
                                wire:confirm="Usunąć usługę {{ $service->name }}?{{ $service->employees_count ? " Zostanie odpięta od pracowników: {$service->employees_count}." : '' }}"
                                class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg border border-red-200 bg-white px-3 py-1.5 text-sm font-semibold text-red-600 cursor-pointer transition hover:bg-red-50 md:flex-none">
                                <i class="fa-regular fa-trash-can"></i> Usuń
                            </button>
                        @endif
                    </td>
                </tr>
            @empty
                <tr class="block md:table-row">
                    <td colspan="6" class="block p-2 md:table-cell">Brak usług.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-3 cursor-pointer">{{ $this->services->links() }}</div>

    @if ($showForm)
        <x-modal id="admin-service-form-title" close="closeForm" :title="$editing ? 'Edytuj usługę' : 'Nowa usługa'" width="max-w-lg">
            <x-slot:subtitle>
                @if ($editing)
                    <p class="text-sm mt-1 text-brand-muted">
                        Zmiana ceny i czasu trwania dotyczy tylko nowych rezerwacji.
                    </p>
                @endif
            </x-slot:subtitle>

            <form wire:submit="save" class="flex flex-col gap-4">
                <div>
                    <label for="admin-service-category" class="block text-sm text-brand-muted mb-1">Kategoria</label>
                    <select id="admin-service-category" wire:model="categoryId"
                        class="w-full rounded-lg border border-brand-border bg-white p-2.5 text-sm">
                        <option value="">Wybierz kategorię…</option>
                        @foreach ($this->categories as $cat)
                            <option value="{{ $cat->uuid }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('categoryId')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="admin-service-name" class="block text-sm text-brand-muted mb-1">Nazwa</label>
                    <input id="admin-service-name" type="text" wire:model="name" maxlength="128"
                        class="w-full rounded-lg border border-brand-border bg-white p-2.5 text-sm">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="admin-service-description" class="block text-sm text-brand-muted mb-1">Opis</label>
                    <textarea id="admin-service-description" wire:model="description" rows="3" maxlength="400"
                        class="w-full rounded-lg border border-brand-border bg-white p-2.5 text-sm"></textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="admin-service-price" class="block text-sm text-brand-muted mb-1">Cena
                            (zł)</label>
                        <input id="admin-service-price" type="text" inputmode="decimal" wire:model="price"
                            placeholder="100,00"
                            class="w-full rounded-lg border border-brand-border bg-white p-2.5 text-sm">
                        @error('price')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="admin-service-duration" class="block text-sm text-brand-muted mb-1">Czas
                            trwania
                            (min)</label>
                        <input id="admin-service-duration" type="number" min="5" max="480"
                            step="5" wire:model="duration"
                            class="w-full rounded-lg border border-brand-border bg-white p-2.5 text-sm">
                        @error('duration')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <label class="inline-flex items-center gap-2 text-sm text-brand-text">
                    <input type="checkbox" wire:model="active" class="rounded border-brand-border">
                    Aktywna (klienci mogą ją rezerwować)
                </label>

                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <button type="button" wire:click="closeForm"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg border border-brand-border bg-white text-brand-text font-semibold cursor-pointer transition hover:bg-brand-light-bg">
                        Anuluj
                    </button>
                    <button type="submit" wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg bg-brand-accent text-white font-semibold shadow-md cursor-pointer transition duration-200 hover:brightness-110 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-brand-accent focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        {{ $editing ? 'Zapisz zmiany' : 'Dodaj usługę' }}
                    </button>
                </div>
            </form>
        </x-modal>
    @endif
</div>
