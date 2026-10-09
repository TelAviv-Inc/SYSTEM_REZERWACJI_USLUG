@props(['_slots' => []])

<div class="mt-4 sm:mt-6 p-3 sm:p-4 bg-white rounded-xl border border-brand-border">
    <h3 class="text-sm sm:text-base font-bold text-brand-navy mb-3 sm:mb-4">
        Dostępne godziny na {{ \Carbon\Carbon::parse($selectedDay)->format('d.m.Y') }}
    </h3>

    @if(empty($_slots))
        <p class="text-sm text-brand-muted">Brak dostępnych godzin w tym dniu.</p>
    @else
        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-2">
            @foreach($_slots as $slot)
                <button type="button" wire:click="$dispatch('hourSelected', { time: '{{ $slot['time'] }}' })"
                    @disabled(!$slot['isAvailable']) class="min-h-[33px] py-0.5 px-1 sm:px-3 sm:min-h-[44px] rounded-md text-sm font-semibold transition {{
                    !$slot['isAvailable']
                    ? 'bg-gray-100 text-gray-400 line-through cursor-not-allowed'
                    : 'bg-[#eff6ff] text-brand-accent hover:bg-brand-accent hover:text-black cursor-pointer'
                                                                                                            }}">
                    {{ $slot['time'] }}
                </button>
            @endforeach
        </div>
    @endif
</div>