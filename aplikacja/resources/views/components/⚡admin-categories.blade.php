<?php

use App\Models\ServiceCategory;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public string $search = '';

    // form modal; editing is the uuid of the category being changed, null adds a new one
    public bool $showForm = false;
    public ?string $editing = null;
    public string $name = '';
    public string $description = '';
    public string $icon = 'fa-solid fa-scissors';

    public function mount(): void
    {
        $this->authorize('viewAny', ServiceCategory::class);
    }

    #[Computed]
    public function categories()
    {
        return ServiceCategory::query()
            ->withCount(['services', 'services as active_services_count' => fn($q) => $q->where('active', 1)])
            ->when($this->search, fn($q) => $q->where(fn($q) => $q->where('name', 'like', "%{$this->search}%")->orWhere('description', 'like', "%{$this->search}%")))
            ->orderBy('name')
            ->get();
    }

    // quick picks for the icon field, any Font Awesome class can still be typed in
    #[Computed]
    public function icons(): array
    {
        return ['fa-solid fa-scissors', 'fa-solid fa-palette', 'fa-solid fa-spa', 'fa-solid fa-pump-medical', 'fa-solid fa-spray-can-sparkles', 'fa-solid fa-hand-sparkles', 'fa-solid fa-wand-magic-sparkles', 'fa-solid fa-paintbrush', 'fa-solid fa-face-smile', 'fa-solid fa-leaf', 'fa-solid fa-gem', 'fa-solid fa-star'];
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:64', Rule::unique('service_categories', 'name')->ignore($this->editing, 'uuid')],
            'description' => ['required', 'string', 'max:255'],
            'icon' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9 -]+$/'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Podaj nazwę kategorii',
            'name.max' => 'Nazwa może mieć najwyżej 64 znaki',
            'name.unique' => 'Kategoria o tej nazwie już istnieje',
            'description.required' => 'Podaj opis kategorii',
            'description.max' => 'Opis może mieć najwyżej 255 znaków',
            'icon.required' => 'Wybierz ikonę',
            'icon.max' => 'Klasa ikony jest za długa',
            'icon.regex' => 'Podaj klasy ikony Font Awesome, np. fa-solid fa-scissors',
        ];
    }

    public function create()
    {
        $this->authorize('create', ServiceCategory::class);
        $this->closeForm();
        $this->showForm = true;
    }

    public function edit(string $uuid)
    {
        $category = ServiceCategory::findOrFail($uuid);
        $this->authorize('update', $category);

        $this->closeForm();
        $this->editing = $category->uuid;
        $this->fill($category->only(['name', 'description', 'icon']));
        $this->showForm = true;
    }

    public function save()
    {
        $category = $this->editing ? ServiceCategory::findOrFail($this->editing) : null;
        $this->authorize($category ? 'update' : 'create', $category ?? ServiceCategory::class);

        $this->name = trim($this->name);
        $this->description = trim($this->description);
        $this->icon = trim(preg_replace('/\s+/', ' ', $this->icon));
        $values = $this->validate();

        $category ? $category->update($values) : ServiceCategory::create($values);

        $this->closeForm();
    }

    public function delete(string $uuid)
    {
        $category = ServiceCategory::findOrFail($uuid);
        $this->authorize('delete', $category);

        $category->delete();

        if ($this->editing === $uuid) {
            $this->closeForm();
        }
    }

    public function closeForm()
    {
        $this->reset('showForm', 'editing', 'name', 'description', 'icon');
        $this->resetValidation();
    }
};
?>

<div class="mt-4">
    <div class="mb-3 flex flex-col gap-2 sm:flex-row">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Szukaj po nazwie lub opisie"
            class="w-full rounded border border-brand-border p-2">

        <button type="button" wire:click="create"
            class="inline-flex shrink-0 items-center justify-center gap-2 px-4 py-2 rounded-lg bg-brand-accent text-white font-semibold shadow-md cursor-pointer transition duration-200 hover:brightness-110 hover:shadow-lg">
            <i class="fa-solid fa-plus"></i> Dodaj kategorię
        </button>
    </div>

    {{-- On phones every row is a card with its own labels; from md up it's a normal table --}}
    <table class="block w-full text-left md:table">
        <thead class="hidden md:table-header-group">
            <tr class="border-b border-brand-border">
                <th class="p-2">Nazwa</th>
                <th class="p-2">Opis</th>
                <th class="p-2">Usługi</th>
                <th class="p-2"><span class="sr-only">Akcje</span></th>
            </tr>
        </thead>
        <tbody class="flex flex-col gap-2 md:table-row-group">
            @forelse ($this->categories as $category)
                <tr wire:key="category-{{ $category->uuid }}"
                    class="grid grid-cols-[auto_1fr] gap-x-3 gap-y-1 rounded border border-brand-border p-3 md:table-row md:rounded-none md:border-0 md:border-b md:p-0">
                    <td class="text-xs text-gray-500 md:hidden">Nazwa</td>
                    <td class="md:table-cell md:p-2">
                        <span class="inline-flex items-center gap-2 font-semibold text-brand-navy">
                            <i class="{{ $category->icon }} w-5 text-center text-brand-accent"></i>
                            {{ $category->name }}
                        </span>
                    </td>

                    <td class="text-xs text-gray-500 md:hidden">Opis</td>
                    <td class="min-w-0 break-words text-sm text-brand-muted md:table-cell md:p-2">
                        {{ $category->description }}</td>

                    <td class="text-xs text-gray-500 md:hidden">Usługi</td>
                    <td class="md:table-cell md:p-2 md:whitespace-nowrap">
                        {{ $category->services_count }}
                        @if ($category->services_count !== $category->active_services_count)
                            <span class="text-xs text-brand-muted">(aktywne:
                                {{ $category->active_services_count }})</span>
                        @endif
                    </td>

                    <td
                        class="col-span-2 mt-1 flex gap-2 md:mt-0 md:table-cell md:p-2 md:text-right md:whitespace-nowrap">
                        <button type="button" wire:click="edit('{{ $category->uuid }}')"
                            class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg border border-brand-border bg-white px-3 py-1.5 text-sm font-semibold text-brand-text cursor-pointer transition hover:bg-brand-light-bg md:flex-none">
                            <i class="fa-regular fa-pen-to-square"></i> Edytuj
                        </button>
                        @if ($category->services_count)
                            <button type="button" disabled
                                title="Kategoria ma usługi. Przenieś je lub usuń, zanim usuniesz kategorię."
                                class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg border border-brand-border bg-white px-3 py-1.5 text-sm font-semibold text-gray-300 cursor-not-allowed md:flex-none">
                                <i class="fa-regular fa-trash-can"></i> Usuń
                            </button>
                        @else
                            <button type="button" wire:click="delete('{{ $category->uuid }}')"
                                wire:confirm="Usunąć kategorię {{ $category->name }}?"
                                class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg border border-red-200 bg-white px-3 py-1.5 text-sm font-semibold text-red-600 cursor-pointer transition hover:bg-red-50 md:flex-none">
                                <i class="fa-regular fa-trash-can"></i> Usuń
                            </button>
                        @endif
                    </td>
                </tr>
            @empty
                <tr class="block md:table-row">
                    <td colspan="4" class="block p-2 md:table-cell">Brak kategorii.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($showForm)
        <x-modal id="admin-category-form-title" close="closeForm" :title="$editing ? 'Edytuj kategorię' : 'Nowa kategoria'" width="max-w-lg">
            <form wire:submit="save" class="flex flex-col gap-4">
                <div>
                    <label for="admin-category-name" class="block text-sm text-brand-muted mb-1">Nazwa</label>
                    <input id="admin-category-name" type="text" wire:model="name" maxlength="64"
                        class="w-full rounded-lg border border-brand-border bg-white p-2.5 text-sm">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="admin-category-description" class="block text-sm text-brand-muted mb-1">Opis</label>
                    <textarea id="admin-category-description" wire:model="description" rows="3" maxlength="255"
                        class="w-full rounded-lg border border-brand-border bg-white p-2.5 text-sm"></textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="admin-category-icon" class="block text-sm text-brand-muted mb-1">Ikona</label>
                    <div class="flex items-center gap-2">
                        <span
                            class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-brand-light-bg text-lg text-brand-accent">
                            <i class="{{ $icon }}"></i>
                        </span>
                        <input id="admin-category-icon" type="text" wire:model.live.debounce.300ms="icon"
                            placeholder="fa-solid fa-scissors"
                            class="w-full rounded-lg border border-brand-border bg-white p-2.5 text-sm">
                    </div>
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        @foreach ($this->icons as $preset)
                            <button type="button" wire:click="$set('icon', '{{ $preset }}')"
                                aria-label="Ikona {{ $preset }}" @class([
                                    'flex size-9 items-center justify-center rounded-lg border cursor-pointer transition',
                                    'border-brand-accent bg-brand-light-bg text-brand-accent' =>
                                        $icon === $preset,
                                    'border-brand-border bg-white text-brand-text hover:bg-brand-light-bg' =>
                                        $icon !== $preset,
                                ])>
                                <i class="{{ $preset }}"></i>
                            </button>
                        @endforeach
                    </div>
                    @error('icon')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <button type="button" wire:click="closeForm"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg border border-brand-border bg-white text-brand-text font-semibold cursor-pointer transition hover:bg-brand-light-bg">
                        Anuluj
                    </button>
                    <button type="submit" wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg bg-brand-accent text-white font-semibold shadow-md cursor-pointer transition duration-200 hover:brightness-110 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-brand-accent focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        {{ $editing ? 'Zapisz zmiany' : 'Dodaj kategorię' }}
                    </button>
                </div>
            </form>
        </x-modal>
    @endif
</div>
