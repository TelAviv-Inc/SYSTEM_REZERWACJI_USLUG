<div>

    @if (!empty($selectedService))

        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-none p-4 transition-opacity">

            <div
                class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl border border-brand-border animate-in fade-in zoom-in-95 duration-200">
                <button wire:click="close"
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin=" His" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
                <div class="p-2 bg-brand-light-bg justify-start items-center">
                    <div class="border-b border-brand-border pb-4 mb-4">
                        <h3 class="text-2xl font-bold text-brand-navy">Rezerwacja usługi</h3>
                        <p class="text-sm font-semibold text-brand-accent mt-1">{{ $selectedService->name ?? '' }}</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <p class="text-sm font-semibold text-brand-muted">Wybierz pracownika:</p>

                    <div class="grid grid-cols-1 gap-2 max-h-60 overflow-y-auto pr-1">
                        @forelse($employees as $employee)
                            <label
                                class="flex items-center justify-between p-3 rounded-lg border border-brand-border hover:border-brand-accent cursor-pointer transition-all hover:bg-slate-50">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-8 h-8 rounded-full bg-brand-accent/10 text-brand-accent flex items-center justify-center font-bold text-sm">
                                        {{ substr($employee->user()->value('name') ?? 'P', 0, 1) }}
                                    </div>
                                    <span class="text-sm font-bold text-brand-navy">{{ $employee->user()->value('name')}}</span>
                                </div>
                                <input type="radio" name="employee" value="{{ $employee->uuid }}"
                                    class="text-brand-accent focus:ring-brand-accent">
                            </label>
                        @empty
                            <p class="text-sm text-gray-500 italic">Brak dostępnych pracowników.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>