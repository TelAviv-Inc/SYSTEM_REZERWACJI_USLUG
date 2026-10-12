<?php

use Livewire\Component;

new class extends Component {
    public string $password = '';

    public function deactivate()
    {
        $user = auth()->user();

        if (in_array($user->role, ['employee', 'admin']))
            return;

        $user->fill(['active' => false]);
        $user->save();

        Auth::guard('web')->logout();
        session()->invalidate();
        session()->regenerateToken();

        $this->redirect('/');
    }




    public function delete()
    {
        $user = Auth::user();
        if (in_array($user->role, ['employee', 'admin']))
            return;

        $this->validate([
            'password' => ['required', 'current_password'],
        ]);

        Auth::guard('web')->logout();
        $user->delete();

        session()->invalidate();
        session()->regenerateToken();

        $this->redirect('/');
    }

};
?>

<div class="mt-2">
    <div class="rounded-lg bg-red-300 border border-red-400 px-3 py-2">
        <div class="flex flex-col gap-1.5 ">
            <h2 class="text-lg font-bold text-white">Strefa niebezpieczna</h2>
            <p class="text-sm italic text-white">Usunięcie konta jest nieodwracalne — stracisz historię
                rezerwacji.</p>
        </div>
        @if (in_array(auth()->user()->role, ['employee', 'admin']))
            <div class="flex flex-col gap-1.5 mt-3">
                <h2 class="text-xl font-bold text-red-800">Nie mozesz podjac tych dzialan, Twoje konto jest zarzadzane przez
                    administratora.</h2>
            </div>
        @else
            <div class="flex flex-col gap-1.5 mt-3">

                <label for="delete_password" class="text-sm font-medium text-red-800">Hasło (wymagane do usunięcia
                    konta)</label>
                <input type="password" id="delete_password" wire:model="password" autocomplete="current-password"
                    class="w-full sm:w-80 rounded-lg border border-red-300 bg-white px-3 py-2.5 text-brand-text focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent">
                @error('password')
                    <p class="text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex flex-col gap-3 mt-3 pb-2 sm:flex-row">

                <button type="button" wire:click="deactivate" wire:confirm="Czy na pewno chcesz dezaktywować konto?"
                    wire:loading.attr="disabled" wire:target="deactivate"
                    class="inline-flex items-center cursor-pointer justify-center gap-2 w-full sm:w-auto px-5 py-2.5 rounded-lg bg-white border border-red-600 text-red-600 font-semibold shadow-md transition duration-200 hover:bg-red-50 hover:shadow-lg hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed disabled:hover:translate-y-0">
                    <i class="fa-solid fa-spinner fa-spin" wire:loading wire:target="deactivate"></i>
                    <i class="fa-solid fa-user-slash" wire:loading.remove wire:target="deactivate"></i>
                    Dezaktywuj konto
                </button>

                <button type="button" wire:click="delete"
                    wire:confirm="Czy na pewno chcesz usunąć konto? Tej operacji nie można cofnąć."
                    wire:loading.attr="disabled" wire:target="delete"
                    class="inline-flex items-center cursor-pointer justify-center gap-2 w-full sm:w-auto px-5 py-2.5 rounded-lg bg-red-600 text-white font-semibold shadow-md transition duration-200 hover:bg-red-700 hover:shadow-lg hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed disabled:hover:translate-y-0">
                    <i class="fa-solid fa-spinner fa-spin" wire:loading wire:target="delete"></i>
                    <i class="fa-solid fa-trash" wire:loading.remove wire:target="delete"></i>
                    Usuń konto
                </button>
            </div>
        @endif

    </div>
    {{-- Let all your things have their places; let each part of your business have its time. - Benjamin Franklin
    --}}
</div>