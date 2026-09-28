<div class="bg-white rounded-xl border border-brand-border p-3 sm:p-6 shadow-sm mt-4 sm:mt-8">

    <div class="flex items-center justify-between gap-2 mb-4">
        <button wire:click="previousWeek" @disabled($weekOffset === 0) aria-label="Poprzedni tydzień" class="shrink-0 px-3 py-2 sm:py-1.5 rounded-md text-sm font-semibold transition
                {{ $weekOffset === 0
    ? 'text-brand-muted opacity-40 cursor-not-allowed'
    : 'text-brand-accent hover:bg-[#eff6ff]' }}">
            &laquo;<span class="hidden sm:inline"> Poprzedni tydzień</span>
        </button>

        <span class="text-xs sm:text-sm font-bold text-brand-navy text-center">
            {{ $days->first()['date']->format('d.m') }} - {{ $days->last()['date']->format('d.m.Y') }}
        </span>

        <button wire:click="nextWeek" @disabled($weekOffset >= 6) aria-label="Poprzedni tydzień" class="shrink-0 px-3 py-2 sm:py-1.5 rounded-md text-sm font-semibold transition
                {{ $weekOffset >= 6
    ? 'text-brand-muted opacity-40 cursor-not-allowed'
    : 'text-brand-accent hover:bg-[#eff6ff]' }}">
            <span class="hidden sm:inline">Następny tydzień </span>&raquo;
        </button>
    </div>

    <div class="grid grid-cols-7 gap-1 sm:gap-2">
        @foreach ($days as $day)
            @php $disabled = !$day['isWorking'] || $day['isPast']; @endphp
            <button wire:key="day-{{ $day['date']->toDateString() }}"
                wire:click="selectDay('{{ $day['date']->toDateString() }}')" @disabled($disabled)
                class="min-w-0 flex flex-col items-center justify-center rounded-md py-2 sm:py-3 text-[11px] sm:text-sm font-semibold transition
                                        {{ $disabled
            ? 'bg-gray-100 text-brand-muted opacity-50 cursor-not-allowed'
            : 'bg-[#eff6ff] text-brand-accent hover:bg-brand-accent hover:text-black cursor-pointer transition duration-500' }}">
                <span>{{ $day['date']->translatedFormat('D') }}</span>
                <span>{{ $day['date']->format('d.m') }}</span>
            </button>
        @endforeach
    </div>

</div>