<?php

use Livewire\Attributes\Url;
use Livewire\Component;

new class extends Component {
    // tab => label; each tab renders the admin-{tab} component
    public const TABS = [
        'reservations' => 'Rezerwacje',
        'employees' => 'Pracownicy',
        'users' => 'Użytkownicy',
        'categories' => 'Kategorie',
        'services' => 'Usługi',
    ];

    // in the URL, so a refresh or a shared link opens the same tab
    #[Url(as: 'tab')]
    public string $option = 'reservations';

    public function mount(): void
    {
        $this->normalizeOption();
    }

    public function updatedOption(): void
    {
        $this->normalizeOption();
    }

    protected function normalizeOption(): void
    {
        if (!array_key_exists($this->option, self::TABS)) {
            $this->option = 'reservations';
        }
    }
};
?>
<div class="flex flex-col mt-4">
    {{-- scrolls sideways on narrow phones instead of wrapping the tabs --}}
    <div class="flex justify-start overflow-x-auto" role="tablist" aria-label="Sekcje panelu">
        @foreach ($this::TABS as $tab => $label)
            <button type="button" role="tab" aria-selected="{{ $option === $tab ? 'true' : 'false' }}"
                wire:click="$set('option', '{{ $tab }}')"
                @class([
                    'shrink-0 whitespace-nowrap font-semibold text-base cursor-pointer border-b-2 p-2 transition',
                    'text-brand-accent border-brand-accent' => $option === $tab,
                    'text-brand-text border-brand-border' => $option !== $tab,
                ])>{{ $label }}</button>
        @endforeach
    </div>

    @livewire('admin-' . $option, key($option))
</div>
