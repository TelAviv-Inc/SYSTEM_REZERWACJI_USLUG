<?php

use Livewire\Component;
use App\Models\Employee;
use App\Models\User;
use App\Support\Phone;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;

new class extends Component {
    public ?User $user = null;
    public ?string $selectedRole = null;
    public string $email = '';
    public string $phone = '';
    // details | disable
    public string $mode = 'details';
    public ?string $notice = null;

    #[On('editUser')]
    public function open($uuid)
    {
        $this->close();
        $this->user = User::with('employee')->findOrFail($uuid);
        $this->authorize('update', $this->user);
        $this->selectedRole = $this->user->role;
        $this->email = $this->user->email;
        $this->phone = (string) $this->user->phone;
    }

    // same rules as ProfileDataForm, minus the password - the admin changes someone else's data
    protected function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user)],
            'phone' => ['required', 'string', 'regex:/^(\+48)?\d{9}$/'],
        ];
    }

    protected function messages(): array
    {
        return [
            'email.unique' => 'Ten adres e-mail jest już zajęty',
            'phone.regex' => 'Podaj poprawny numer telefonu',
        ];
    }

    public function saveContact()
    {
        $this->authorize('update', $this->user);
        $this->phone = Phone::normalize($this->phone);
        $this->validate();

        $this->user->fill($this->only(['email', 'phone']));
        if (!$this->user->isDirty()) {
            return;
        }

        try {
            $this->user->save();
        } catch (UniqueConstraintViolationException) {
            // someone took this email between validate() and save()
            throw ValidationException::withMessages(['email' => $this->messages()['email.unique']]);
        }

        $this->finish('Dane kontaktowe zostały zapisane.');
    }

    #[Computed]
    public function roles()
    {
        return User::ROLES;
    }

    // visits booked with this user as the employee; they stay when the profile is deactivated
    #[Computed]
    public function upcomingAsEmployee(): int
    {
        return $this->user->employee?->reservations()->upcoming()->count() ?? 0;
    }

    public function startDisable()
    {
        $this->authorize('deactivateUser', $this->user);
        $this->reset('notice');
        $this->mode = 'disable';
    }

    public function back()
    {
        $this->reset('mode');
    }

    public function disable()
    {
        $this->authorize('deactivateUser', $this->user);

        DB::transaction(function () {
            $this->user->update(['active' => false]);
            // an account that can't log in can't be booked either
            $this->user->employee?->update(['active' => 0]);
        });

        $this->finish('Konto zostało dezaktywowane.');
    }

    public function enable()
    {
        $this->authorize('activateUser', $this->user);

        DB::transaction(function () {
            $this->user->update(['active' => true]);

            if ($this->user->role === 'employee') {
                $this->user->employee?->update(['active' => 1]);
            }
        });

        $this->finish('Konto zostało aktywowane.');
    }

    public function changeRole()
    {
        if (!array_key_exists($this->selectedRole, $this->roles) || $this->selectedRole === $this->user->role) {
            return;
        }
        $this->authorize('changeRole', $this->user);

        DB::transaction(function () {
            $wasEmployee = $this->user->role === 'employee';
            $this->user->update(['role' => $this->selectedRole]);

            if ($this->selectedRole === 'employee') {
                // employees.user_id is unique, so a returning employee gets their old row back;
                // a deactivated account stays unbookable until it is activated again
                Employee::updateOrCreate(['user_id' => $this->user->uuid], ['active' => $this->user->active ? 1 : 0]);
            } elseif ($wasEmployee) {
                // deactivate instead of delete, so past reservations keep their employee
                $this->user->employee?->update(['active' => 0]);
            }
        });

        $this->finish('Rola została zmieniona.');
    }

    protected function finish(string $notice)
    {
        $this->user->refresh()->load('employee');
        $this->back();
        $this->notice = $notice;
        $this->dispatch('userUpdated');
    }

    public function close()
    {
        $this->reset();
        $this->resetValidation();
    }
};
?>

<div>
    @if ($user)

        <x-modal id="admin-users-edit-title" :title="$mode === 'disable' ? 'Dezaktywacja konta' : 'Edycja użytkownika'">
            <x-slot:subtitle>
                <p class="text-sm mt-1 break-words">
                    <span class="font-semibold text-brand-accent">{{ $user->name }} {{ $user->surname }}</span>
                    <span class="text-brand-muted">· {{ $user->email }}</span>
                </p>
            </x-slot:subtitle>

            {{-- Summary stays visible in every step, so the admin always sees who they are changing. --}}
            <dl class="grid grid-cols-2 gap-x-4 gap-y-3 rounded-xl bg-brand-light-bg p-3 sm:p-4 text-sm">
                <div class="col-span-2 sm:col-span-1">
                    <dt class="text-brand-muted"><i class="fa-solid fa-phone mr-1"></i> Telefon</dt>
                    <dd class="font-semibold text-brand-navy">{{ $user->phone ?: '—' }}</dd>
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <dt class="text-brand-muted"><i class="fa-regular fa-calendar mr-1"></i> Konto od</dt>
                    <dd class="font-semibold text-brand-navy">{{ $user->created_at?->format('d.m.Y') }}</dd>
                </div>
                <div>
                    <dt class="text-brand-muted">Rola</dt>
                    <dd class="font-semibold text-brand-navy">{{ $this->roles[$user->role] ?? $user->role }}</dd>
                </div>
                <div>
                    <dt class="text-brand-muted">Status</dt>
                    <dd>
                        <span @class([
                            'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold',
                            'bg-green-100 text-green-800' => $user->active,
                            'bg-red-100 text-red-700' => !$user->active,
                        ])>{{ $user->active ? 'Aktywny' : 'Nieaktywny' }}</span>
                    </dd>
                </div>
                @if ($user->employee)
                    <div class="col-span-2">
                        <dt class="text-brand-muted"><i class="fa-regular fa-id-badge mr-1"></i> Profil pracownika
                        </dt>
                        <dd class="font-semibold text-brand-navy">
                            {{ $user->employee->active ? 'Aktywny' : 'Nieaktywny' }}
                        </dd>
                    </div>
                @endif
            </dl>

            @if ($notice)
                <p
                    class="mt-4 rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-sm font-medium text-green-700">
                    <i class="fa-solid fa-circle-check"></i> {{ $notice }}
                </p>
            @endif

            @if ($mode === 'details')
                <form wire:submit="saveContact" class="mt-6">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label for="admin-users-edit-email"
                                class="block text-sm font-semibold text-brand-navy mb-1">E-mail</label>
                            <input id="admin-users-edit-email" type="email" wire:model="email" autocomplete="off"
                                class="w-full rounded-lg border border-brand-border p-2.5">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="admin-users-edit-phone"
                                class="block text-sm font-semibold text-brand-navy mb-1">Telefon</label>
                            <input id="admin-users-edit-phone" type="tel" wire:model="phone" autocomplete="off"
                                class="w-full rounded-lg border border-brand-border p-2.5">
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="mt-3 flex justify-end">
                        <button type="submit" wire:loading.attr="disabled"
                            class="inline-flex w-full items-center justify-center gap-2 px-5 py-2.5 rounded-lg bg-brand-accent text-white font-semibold shadow-md cursor-pointer transition duration-200 hover:brightness-110 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-brand-accent focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed sm:w-auto">
                            Zapisz dane
                        </button>
                    </div>
                </form>

                @can('changeRole', $user)
                    <div class="mt-6">
                        <label for="admin-users-edit-role"
                            class="block text-sm font-semibold text-brand-navy mb-1">Rola</label>
                        <div class="flex flex-col gap-2 sm:flex-row">
                            <select id="admin-users-edit-role" wire:model.live="selectedRole"
                                class="w-full rounded-lg border border-brand-border p-2.5 sm:flex-1">
                                @foreach ($this->roles as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <button type="button" wire:click="changeRole" wire:loading.attr="disabled"
                                @disabled($selectedRole === $user->role)
                                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg bg-brand-accent text-white font-semibold shadow-md cursor-pointer transition duration-200 hover:brightness-110 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-brand-accent focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                Zapisz rolę
                            </button>
                        </div>
                        @if ($user->role === 'employee' && $selectedRole !== 'employee')
                            <p class="mt-2 text-sm text-amber-700">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                                Profil pracownika zostanie dezaktywowany i zniknie z listy przy rezerwacji.
                                @if ($this->upcomingAsEmployee)
                                    Nadchodzące wizyty u tego pracownika ({{ $this->upcomingAsEmployee }}) pozostaną
                                    bez zmian – przenieś je lub anuluj w zakładce Rezerwacje.
                                @endif
                            </p>
                        @endif
                    </div>
                @else
                    <p class="mt-4 text-sm text-brand-muted italic">Nie możesz zmienić roli własnego konta.</p>
                @endcan

                @can('deactivateUser', $user)
                    <div class="mt-6 flex justify-end border-t border-brand-border pt-4">
                        <button type="button" wire:click="startDisable"
                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg border border-red-200 bg-white text-red-600 font-semibold cursor-pointer transition hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500/50">
                            <i class="fa-solid fa-user-slash"></i> Dezaktywuj konto
                        </button>
                    </div>
                @endcan

                @can('activateUser', $user)
                    <div class="mt-6 flex justify-end border-t border-brand-border pt-4">
                        <button type="button" wire:click="enable"
                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg border border-green-200 bg-white text-green-600 font-semibold cursor-pointer transition hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-green-500/50">
                            <i class="fa-solid fa-user-check"></i> Aktywuj konto
                        </button>
                    </div>
                @endcan
            @elseif ($mode === 'disable')
                <div class="mt-4 flex gap-3 rounded-xl border border-red-200 bg-red-50 p-3 sm:p-4 text-sm text-red-800">
                    <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
                    <p>
                        Czy na pewno chcesz dezaktywować to konto? Użytkownik zostanie wylogowany
                        i nie będzie mógł się zalogować.
                        @if ($user->employee?->active)
                            Profil pracownika tego konta zniknie z listy przy rezerwacji.
                            @if ($this->upcomingAsEmployee)
                                Nadchodzące wizyty ({{ $this->upcomingAsEmployee }}) pozostaną bez zmian.
                            @endif
                        @endif
                    </p>
                </div>

                <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <button type="button" wire:click="back"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg border border-brand-border bg-white text-brand-text font-semibold cursor-pointer transition hover:bg-brand-light-bg">
                        Wróć
                    </button>
                    <button type="button" wire:click="disable" wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg bg-red-600 text-white font-semibold shadow-md cursor-pointer transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500/50 disabled:opacity-60">
                        <i class="fa-solid fa-user-slash"></i> Tak, dezaktywuj
                    </button>
                </div>
            @endif
        </x-modal>
    @endif
</div>
