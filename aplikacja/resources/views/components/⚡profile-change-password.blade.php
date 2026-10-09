<?php

use Livewire\Component;
use App\Livewire\Forms\UpdatePasswordForm;

new class extends Component {
    public UpdatePasswordForm $form;

    public function save()
    {
        $this->form->update();
        session()->flash('status', 'Haslo zostalo zmienione');
    }
};
?>

<div class="mt-2">
    <div class="rounded-lg bg-white border border-brand-border px-3 py-2">
        <div class="flex flex-col gap-1.5 ">
            <h2 class="text-lg font-bold text-brand-text">Zmiana hasła</h2>
            <p class="text-sm italic text-brand-muted">Po zmianie hasła zostaniesz wylogowany na pozostałych
                urządzeniach.</p>
        </div>

        @if (session('status'))
            <p class="mt-3 rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-sm font-medium text-green-700">
                <i class="fa-solid fa-circle-check"></i> {{ session('status') }}
            </p>
        @endif

        <form wire:submit="save" class="grid grid-cols-1 gap-4 mt-3 pb-2 sm:grid-cols-2">
            @csrf
            <div class="flex flex-col gap-1.5">
                <label for="current_password" class="text-sm font-medium text-brand-muted">Aktualne haslo</label>
                <input type="password" name="current_password" id="current_password" minlength="8"
                    wire:model="form.current_password" autocomplete="current-password"
                    class="w-full rounded-lg border border-brand-border bg-brand-bg px-3 py-2.5 text-brand-text font-semibold placeholder:text-brand-muted focus:outline-none focus:ring-2 focus:ring-brand-accent focus:border-transparent transition duration-200">
                @error('form.current_password')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="password" class="text-sm font-medium text-brand-muted">Nowe haslo</label>
                <input type="password" name="password" id="password" minlength="8" wire:model="form.password"
                    autocomplete="new-password"
                    class="w-full rounded-lg border border-brand-border bg-brand-bg px-3 py-2.5 text-brand-text font-semibold placeholder:text-brand-muted focus:outline-none focus:ring-2 focus:ring-brand-accent focus:border-transparent transition duration-200">
                @error('form.password')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
                <label for="password_confirmation" class="text-sm font-medium text-brand-muted">Potwierdź nowe
                    hasło</label>
                <input type="password" name="password_confirmation" id="password_confirmation" minlength="8"
                    wire:model="form.password_confirmation" autocomplete="new-password"
                    class="w-full rounded-lg border border-brand-border bg-brand-bg px-3 py-2.5 text-brand-text font-semibold placeholder:text-brand-muted focus:outline-none focus:ring-2 focus:ring-brand-accent focus:border-transparent transition duration-200">
            </div>
            <div class="flex justify-start sm:col-span-2">
                <button type="submit" wire:loading.attr="disabled" wire:target="save"
                    class="inline-flex items-center justify-center gap-2 w-full sm:w-auto px-5 py-2.5 rounded-lg bg-brand-accent text-white font-semibold shadow-md transition duration-200 hover:brightness-110 hover:shadow-lg hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-brand-accent focus:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed disabled:hover:translate-y-0">
                    <i class="fa-solid fa-spinner fa-spin" wire:loading wire:target="save"></i>
                    <span wire:loading.remove wire:target="save">Zapisz zmiany</span>
                    <span wire:loading wire:target="save">Zapisywanie…</span>
                </button>
            </div>
        </form>

    </div>
    {{-- Waste no more time arguing what a good man should be, be one. - Marcus Aurelius --}}
</div>