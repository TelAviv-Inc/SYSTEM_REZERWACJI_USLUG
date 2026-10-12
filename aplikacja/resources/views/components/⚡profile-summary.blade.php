<?php

use Livewire\Component;
use Livewire\Attributes\On;
new class extends Component {
    // Empty handler: receiving the event is enough to re-render with fresh user data
    #[On('profile-updated')]
    public function refreshSummary() {}
};
?>

<div>
    @php
        $user = auth()->user();
        $username = $user->name . ' ' . $user->surname;
        $roleClasses = match ($user->role) {
            'admin' => 'text-admin-text bg-admin-bg',
            'employee' => 'text-employee-text bg-employee-bg',
            default => 'text-brand-accent bg-client-bg',
        };
    @endphp
    <div class="mt-2 flex gap-4 bg-white p-2 items-center rounded-lg border border-brand-border">
        <p
            class="w-16 h-16 shrink-0 rounded-full {{ $roleClasses }} flex items-center justify-center font-bold text-2xl">
            {{ substr($user->name ?? 'P', 0, 1) }}
        </p>

        <div class="flex flex-col gap-1 justify-start items-start">
            <span class="text-md font-bold text-brand-navy truncate">{{ $username }}</span>
            <span class="text-sm text-brand-muted">{{ $user->email }}</span>
        </div>

    </div>
    <div class="mt-4 grid grid-cols-2 gap-2 md:grid-cols-1">
        <div
            class="flex flex-col rounded-lg border border-brand-border bg-white px-3 py-2 items-start justify-start">
            <p class=" text-sm text-gray-600">Konto zalozone</p>
            <h3 class="text-lg font-bold">{{ $user->created_at->format('d M Y') }}</h3>
        </div>
        <div
            class="flex flex-col rounded-lg border border-brand-border bg-white px-3 py-2 items-start justify-start">
            <p class=" text-sm text-gray-600 md:text-md">Status konta</p>
            <h3 class="text-lg font-bold {{ $user->active ? 'text-green-600' : 'text-red-600' }}">
                &bull; {{ $user->active ? 'Aktywne' : 'Nieczynne' }}
            </h3>
        </div>
        <div
            class="flex flex-col rounded-lg border border-brand-border bg-white px-3 py-2 items-start justify-start">
            <p class=" text-sm text-gray-600 md:text-md">Ostatnia zmiana</p>
            <h3 class="text-lg font-bold">{{ $user->updated_at->format('d M Y') }}</h3>
        </div>
        <div
            class="flex flex-col rounded-lg border border-brand-border bg-white px-3 py-2 items-start justify-start">
            <p class=" text-sm text-gray-600 md:text-md">Rezerwacje</p>
            @if (!$user->reservations()->exists())
                <h3 class="text-base text-gray-500 italic sm:col-span-2">Brak aktywnych rezerwacji</h3>
            @else
                @php
                    $totalReservations = $user->reservations()->count();
                    $pendingReservations = $user->reservations()
                        ->confirmed()
                        ->where(function ($q) {
                            $q->whereDate('reservation_date', '>', today())
                                ->orWhere(function ($q) {
                                    $q->whereDate('reservation_date', today())
                                        ->whereTime('start_time', '>', now()->format('H:i:s'));
                                });
                        })
                        ->count();
                @endphp
                <h3 class="text-lg font-bold">{{ $totalReservations }} łącznie &middot; {{ $pendingReservations }}
                    nadchodzące</h3>
            @endif
        </div>
    </div>
</div>
