{{-- Bottom sheet on phones, centered modal from sm up. "close" is the Livewire method that closes it;
     "escape" replaces what the Escape key runs, by default the same method. --}}
@props(['id', 'title', 'close' => 'close', 'width' => 'max-w-lg md:max-w-xl', 'escape' => null])

<div wire:click.self="{{ $close }}" x-on:keydown.escape.window="{{ $escape ?? '$wire.' . $close . '()' }}"
    class="fixed inset-0 z-50 flex items-end sm:items-center justify-center overflow-y-auto bg-black/60 p-0 sm:p-4 transition-opacity">

    <div role="dialog" aria-modal="true" aria-labelledby="{{ $id }}" @class([
        'relative w-full max-h-[95dvh] sm:max-h-[90dvh] overflow-y-auto rounded-t-2xl sm:rounded-2xl bg-white p-4 sm:p-6 shadow-2xl border border-brand-border',
        $width,
    ])>
        <button type="button" wire:click="{{ $close }}" aria-label="Zamknij"
            class="absolute top-2 right-2 sm:top-4 sm:right-4 p-2 text-gray-400 hover:text-gray-600 transition-colors cursor-pointer">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>

        <div class="border-b border-brand-border pb-3 sm:pb-4 mb-4 pr-8">
            <h3 id="{{ $id }}" class="text-xl sm:text-2xl font-bold text-brand-navy">{{ $title }}</h3>
            {{ $subtitle ?? '' }}
        </div>

        {{ $slot }}
    </div>
</div>
