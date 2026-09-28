<div>

    @if (!empty($selectedService))

        {{-- Overlay: bottom sheet on phones, centered modal from sm up. Overlay itself scrolls if content is taller than
        the screen. --}}
        <div
            class="fixed inset-0 z-50 flex items-end sm:items-center justify-center overflow-y-auto bg-black/60 backdrop-blur-none p-0 sm:p-4 transition-opacity">

            <div
                class="relative w-full max-w-lg md:max-w-xl max-h-[95dvh] sm:max-h-[90dvh] overflow-y-auto rounded-t-2xl sm:rounded-2xl bg-white p-4 sm:p-6 shadow-2xl border border-brand-border animate-in fade-in zoom-in-95 duration-200">
                <button wire:click="close" aria-label="Zamknij"
                    class="absolute top-2 right-2 sm:top-4 sm:right-4 p-2 text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
                <div class="p-1 sm:p-2 bg-white justify-start items-center">
                    <div class="border-b border-brand-border pb-3 sm:pb-4 mb-3 sm:mb-4 pr-8">
                        <h3 class="text-xl sm:text-2xl font-bold text-brand-navy">Rezerwacja usługi</h3>
                        <p class="text-sm font-semibold text-brand-accent mt-1 break-words">
                            {{ $selectedService->name ?? '' }}
                        </p>
                    </div>
                </div>

                <div class="space-y-4">
                    <p class="text-sm font-semibold text-brand-muted">Wybierz pracownika:</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-48 sm:max-h-60 overflow-y-auto pr-1">
                        @forelse($employees as $employee)
                            <label wire:click="selectEmployee('{{ $employee->uuid }}')"
                                class="flex items-center justify-between gap-2 p-3 min-h-[44px] rounded-lg border border-brand-border hover:border-brand-accent cursor-pointer transition-all hover:bg-slate-50">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div
                                        class="w-8 h-8 shrink-0 rounded-full bg-brand-accent/10 text-brand-accent flex items-center justify-center font-bold text-sm">
                                        {{ substr($employee->user()->value('name') ?? 'P', 0, 1) }}
                                    </div>
                                    <span
                                        class="text-sm font-bold text-brand-navy truncate">{{ $employee->user()->value('name')}}</span>
                                </div>
                                <input type="radio" name="employee" value="{{ $employee->uuid }}"
                                    class="shrink-0 text-brand-accent focus:ring-brand-accent">
                            </label>
                        @empty
                            <p class="text-sm text-gray-500 italic sm:col-span-2">Brak dostępnych pracowników.</p>
                        @endforelse
                    </div>
                    @if ($selectedEmployee)
                        <livewire:employee-week-calendar :key="$selectedEmployee->uuid" />
                    @endif
                    @if ($selectedEmployee && $selectedDay)
                        @livewire('employee-hour-calendar', ['selectedDay' => $selectedDay, 'selectedEmployee' => $selectedEmployee, 'serviceTime' => $selectedService->duration], key($selectedEmployee->uuid . '-' . $selectedDay))
                    @endif
                    @if ($selectedEmployee && $selectedDay && $selectedHour)
                        <div class="flex items-end justify-end">
                            <button
                                class="px-1 py-2 bg-green-500  rounded-lg text-white font-xl font-semibold tracking-wide md:px-2 md:py-3">Zarezerwuj</button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>