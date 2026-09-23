<div class="bg-white rounded-xl border border-brand-border p-6 shadow-sm mt-12">

    <div class="flex items-center justify-between mb-4">
        <button wire:click="previousWeek" @disabled($weekOffset === 0) class="px-3 py-1.5 rounded-md text-sm font-semibold transition
                {{ $weekOffset === 0
    ? 'text-brand-muted opacity-40 cursor-not-allowed'
    : 'text-brand-accent hover:bg-[#eff6ff]' }}">
            &laquo; Poprzedni tydzień
        </button>

        <span class="text-sm font-bold text-brand-navy">
            {{ $days->first()['date']->format('d.m') }} - {{ $days->last()['date']->format('d.m.Y') }}
        </span>

        <button wire:click="nextWeek"
            class="px-3 py-1.5 rounded-md text-sm font-semibold text-brand-accent hover:bg-[#eff6ff] transition">
            Następny tydzień &raquo;
        </button>
    </div>

    <div class="grid grid-cols-7 gap-2">
        @foreach ($days as $day)
            @php $disabled = !$day['isWorking'] || $day['isPast']; @endphp
            <button wire:click="selectDay('{{ $day['date']->toDateString() }}')" @disabled($disabled)
                class="flex flex-col items-center justify-center rounded-md py-3 text-sm font-semibold transition
                                    {{ $disabled
            ? 'bg-gray-100 text-brand-muted opacity-50 cursor-not-allowed'
            : 'bg-[#eff6ff] text-brand-accent hover:bg-brand-accent hover:text-black cursor-pointer transition duration-500' }}">
                <span>{{ $day['date']->translatedFormat('D') }}</span>
                <span>{{ $day['date']->format('d.m') }}</span>
            </button>
        @endforeach
    </div>

</div>