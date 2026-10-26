<?php

use Livewire\Component;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;

new class extends Component {
    public ?User $user = null;
    public ?string $selectedRole = null;
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
    }

    #[Computed]
    public function roles()
    {
        return ['client' => 'Klient', 'employee' => 'Pracownik', 'admin' => 'Administrator'];
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
        $this->user->update(['active' => false]);

        $this->finish('Konto zostało dezaktywowane.');
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
                // employees.user_id is unique, so a returning employee gets their old row back
                Employee::updateOrCreate(['user_id' => $this->user->uuid], ['active' => 1]);
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

        {{-- Same shell as reservation-edit: bottom sheet on phones, centered modal from sm up. --}}
        <div wire:click.self="close" x-on:keydown.escape.window="$wire.close()"
            class="fixed inset-0 z-50 flex items-end sm:items-center justify-center overflow-y-auto bg-black/60 p-0 sm:p-4 transition-opacity">

            <div role="dialog" aria-modal="true" aria-labelledby="admin-users-edit-title"
                class="relative w-full max-w-lg md:max-w-xl max-h-[95dvh] sm:max-h-[90dvh] overflow-y-auto rounded-t-2xl sm:rounded-2xl bg-white p-4 sm:p-6 shadow-2xl border border-brand-border">
                <button type="button" wire:click="close" aria-label="Zamknij"
                    class="absolute top-2 right-2 sm:top-4 sm:right-4 p-2 text-gray-400 hover:text-gray-600 transition-colors cursor-pointer">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>

                <div class="border-b border-brand-border pb-3 sm:pb-4 mb-4 pr-8">
                    <h3 id="admin-users-edit-title" class="text-xl sm:text-2xl font-bold text-brand-navy">
                        {{ $mode === 'disable' ? 'Dezaktywacja konta' : 'Edycja użytkownika' }}
                    </h3>
                    <p class="text-sm mt-1 break-words">
                        <span class="font-semibold text-brand-accent">{{ $user->name }} {{ $user->surname }}</span>
                        <span class="text-brand-muted">· {{ $user->email }}</span>
                    </p>
                </div>

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
                @elseif ($mode === 'disable')
                    <div
                        class="mt-4 flex gap-3 rounded-xl border border-red-200 bg-red-50 p-3 sm:p-4 text-sm text-red-800">
                        <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
                        <p>
                            Czy na pewno chcesz dezaktywować to konto? Użytkownik zostanie wylogowany
                            i nie będzie mógł się zalogować.
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

            </div>
        </div>
    @endif
</div>
