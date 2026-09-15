@props(['name' => 'Domyslna nazwa', 'description' => "Brak opisu", 'icon' => "no-icon"])

<!-- resources/views/components/service-category-card.blade.php -->
<div
    class="flex flex-col h-full bg-white rounded-md overflow-hidden shadow-md hover:shadow-lg transition-shadow duration-300">
    <div class="rounded-sm flex items-center justify-center bg-brand-accent  text-white text-5xl py-3">
        <i class="{{$icon}}"></i>

    </div>
    <h3 class="text-lg font-semibold text-gray-900 text-center mt-4 mb-2">{{$name}}</h3>
    <p class="text-gray-600 text-sm text-center px-8 pb-4">{{ $description }}</p>
    <div
        class="mt-auto w-full flex justify-center items-center text-brand-accent bg-[#eff6ff] uppercase tracking-wider py-3 px-4">
        <a href="{{ route('dashboard.categories.show', $name) }}" class="text-md font-bold">Zobacz Uslugi</a>
    </div>
</div>