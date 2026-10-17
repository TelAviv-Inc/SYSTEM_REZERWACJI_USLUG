{{-- Submit button that shows a spinner while the form is sending (the form disables it in onsubmit) --}}
@props(['loading'])

<button type="submit"
    class="group mt-2 flex w-full items-center justify-center gap-2 rounded-lg bg-brand-accent px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition duration-300 hover:-translate-y-0.5 hover:bg-brand-blue hover:shadow-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-accent/50 focus-visible:ring-offset-2 disabled:cursor-wait disabled:opacity-75 disabled:hover:translate-y-0 disabled:hover:bg-brand-accent disabled:hover:shadow-sm">
    <span class="group-disabled:hidden">{{ $slot }}</span>
    <i class="fas fa-arrow-right text-xs transition-transform duration-300 group-hover:translate-x-1 group-disabled:hidden"></i>
    <span class="hidden group-disabled:inline"><i class="fas fa-spinner fa-spin mr-2"></i>{{ $loading }}</span>
</button>
