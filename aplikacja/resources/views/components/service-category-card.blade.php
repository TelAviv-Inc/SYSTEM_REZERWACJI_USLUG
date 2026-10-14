@props(['name' => 'Domyslna nazwa', 'description' => "Brak opisu", 'icon' => "no-icon"])

<!-- resources/views/components/service-category-card.blade.php -->
<button type="button" wire:click="$dispatch('categorySelected', { categoryName: @js($name) })"
    class="group relative flex flex-col h-full w-full text-left bg-white rounded-xl border border-brand-border p-5 sm:p-6 shadow-sm transition duration-300 hover:-translate-y-0.5 hover:border-brand-accent/40 hover:shadow-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-accent/50 cursor-pointer overflow-hidden">
    {{-- Accent bar that slides in on hover --}}
    <span
        class="absolute inset-x-0 top-0 h-1 bg-brand-accent origin-left scale-x-0 transition-transform duration-300 group-hover:scale-x-100"></span>

    <div
        class="flex items-center justify-center size-12 sm:size-14 rounded-lg bg-brand-light-bg text-brand-accent text-xl sm:text-2xl transition-colors duration-300 group-hover:bg-brand-accent group-hover:text-white">
        <i class="{{ $icon }}"></i>
    </div>

    <h3 class="mt-4 text-base sm:text-lg font-bold text-brand-navy">{{ $name }}</h3>
    <p class="mt-1.5 text-sm font-medium text-brand-muted line-clamp-2 sm:line-clamp-3">
        {{ $description }}
    </p>

    <div class="mt-auto pt-5">
        <span
            class="flex items-center justify-between border-t border-brand-border pt-4 text-sm font-semibold text-brand-accent">
            Zobacz usługi
            <i class="fa-solid fa-arrow-right text-xs transition-transform duration-300 group-hover:translate-x-1"></i>
        </span>
    </div>
</button>
