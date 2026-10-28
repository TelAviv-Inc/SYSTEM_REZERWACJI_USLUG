<?php

use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    // every admin tab has its own URL keys, so filters don't carry over to the next tab
    #[Url(as: 'users_q')]
    public string $search = '';

    #[Url(as: 'users_status')]
    public string $status = '';

    #[Url(as: 'users_role')]
    public string $role = '';

    // admin-users-edit dispatches this after a role change or deactivation
    protected $listeners = ['userUpdated' => '$refresh'];

    // Any filter change goes back to page 1, otherwise you can land on an empty page
    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'status', 'role'], true)) {
            $this->resetPage('users_page');
        }
    }

    #[Computed]
    public function users()
    {
        return User::query()
            ->select(['uuid', 'name', 'surname', 'email', 'role', 'active', 'created_at'])
            ->when(
                $this->search,
                fn($q) => $q->where(
                    fn($q) => $q
                        ->where('name', 'like', "%{$this->search}%")
                        ->orWhere('surname', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%"),
                ),
            )
            ->when(in_array($this->status, ['active', 'inactive'], true), fn($q) => $q->where('active', $this->status === 'active'))
            ->when(array_key_exists($this->role, User::ROLES), fn($q) => $q->where('role', $this->role))
            ->latest()
            ->paginate(10, pageName: 'users_page');
    }
};
?>
<div class="mt-4">
    <div class="mb-3 flex flex-col gap-2 sm:flex-row">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Szukaj po imieniu, nazwisku lub e-mailu"
            class="w-full rounded border border-brand-border p-2">

        <select wire:model.live="role" class="w-full rounded border border-brand-border p-2 sm:w-auto">
            <option value="">Wszystkie role</option>
            @foreach (User::ROLES as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
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
                <th class="p-2">Rola</th>
                <th class="p-2">Status</th>
                <th class="p-2"><span class="sr-only">Akcje</span></th>
            </tr>
        </thead>
        <tbody class="flex flex-col gap-2 md:table-row-group">
            @forelse ($this->users as $user)
                <tr wire:key="user-{{ $user->uuid }}"
                    class="grid grid-cols-[auto_1fr] gap-x-3 gap-y-1 rounded border border-brand-border p-3 md:table-row md:rounded-none md:border-0 md:border-b md:p-0">
                    <td class="text-xs text-gray-500 md:hidden">Imię</td>
                    <td class="md:table-cell md:p-2">{{ $user->name }} {{ $user->surname }}</td>

                    <td class="text-xs text-gray-500 md:hidden">E-mail</td>
                    <td class="min-w-0 break-all md:table-cell md:p-2 md:break-normal">{{ $user->email }}</td>

                    <td class="text-xs text-gray-500 md:hidden">Rola</td>
                    <td class="md:table-cell md:p-2">{{ User::ROLES[$user->role] ?? $user->role }}</td>

                    <td class="text-xs text-gray-500 md:hidden">Status</td>
                    <td class="md:table-cell md:p-2">{{ $user->active ? 'Aktywny' : 'Nieaktywny' }}</td>

                    <td class="col-span-2 mt-1 md:mt-0 md:table-cell md:p-2 md:text-right">
                        <button type="button" wire:click="$dispatch('editUser', { uuid: '{{ $user->uuid }}' })"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-brand-border bg-white px-3 py-1.5 text-sm font-semibold text-brand-text cursor-pointer transition hover:bg-brand-light-bg md:w-auto">
                            <i class="fa-regular fa-pen-to-square"></i> Edytuj
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

    <div class="mt-3 cursor-pointer">{{ $this->users->links() }}</div>

    @livewire('admin-users-edit')
</div>
