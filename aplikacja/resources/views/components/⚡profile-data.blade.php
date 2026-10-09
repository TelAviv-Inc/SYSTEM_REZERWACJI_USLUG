<?php

use Livewire\Component;
use App\Livewire\Forms\ProfileDataForm;
new class extends Component {
    public ProfileDataForm $form;

    public function mount()
    {
        $this->form->setUser(auth()->user());
    }

    public function save()
    {
        if (!$this->form->update()) {
            session()->flash('info', 'brak zmian');
            return;
        }
        $this->dispatch('profile-updated');
        session()->flash('status', 'Dane zostaly zapisane');
    }
};
?>


<div class="mt-2">
    <div class="rounded-lg bg-white border border-brand-border px-3 py-2">
        <div class="flex flex-col gap-1.5 ">
            <h2 class="text-lg font-bold text-brand-text">Dane osobiste</h2>
            <p class="text-sm italic text-brand-muted">Te dane widzą pracownicy przy rezerwacji.</p>
        </div>

        <form wire:submit="save" class="grid grid-cols-1 gap-4 mt-3 pb-2 sm:grid-cols-2">
            <div class="flex flex-col gap-1.5">
                <label for="email" class="text-sm font-medium text-brand-muted">Adres e-mail</label>
                <input type="email" name="email" id="email" wire:model.blur="form.email" autocomplete="email"
                    class="w-full rounded-lg border border-brand-border bg-brand-bg px-3 py-2.5 text-brand-text font-semibold placeholder:text-brand-muted focus:outline-none focus:ring-2 focus:ring-brand-accent focus:border-transparent transition duration-200">
                @error('form.email')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="phone" class="text-sm font-medium text-brand-muted">Numer telefonu</label>
                <input type="tel" name="phone" id="phone" wire:model.blur="form.phone" autocomplete="tel"
                    inputmode="tel" pattern="(\+48)?[0-9]{9}" maxlength="12"
                    class="w-full rounded-lg border border-brand-border bg-brand-bg px-3 py-2.5 text-brand-text font-semibold placeholder:text-brand-muted focus:outline-none focus:ring-2 focus:ring-brand-accent focus:border-transparent transition duration-200">

                @error('form.phone')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            @if ($form->email !== auth()->user()->email)
                <div class="flex flex-col gap-1.5" wire:key="current-password">
                    <label for="current_password" class="text-sm font-medium text-brand-muted">Aktualne haslo</label>
                    <input type="password" name="current_password" id="current_password"
                        wire:model.blur="form.current_password" minlength="8"
                        class="w-full rounded-lg border border-brand-border bg-brand-bg px-3 py-2.5 text-brand-text font-semibold placeholder:text-brand-muted focus:outline-none focus:ring-2 focus:ring-brand-accent focus:border-transparent transition duration-200">
                    @error('form.current_password')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            @endif
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

</div>