@props(['name' => 'Domyslna nazwa', 'description' => "Brak opisu", 'icon' => "no-icon"])

<!-- resources/views/components/service-category-card.blade.php -->
<div class="flex-col bg-white rounded-xl overflow-hidden shadow-md hover:shadow-lg transition-shadow duration-300">
    <div class="rounded-sm flex items-center justify-center bg-brand-accent">
        <p>{{ $icon }}</p>
        <i class="{{$icon}} text-white text-5xl py-3"></i>
    </div>
    <h3 class="text-lg font-semibold text-gray-900 text-center mt-4 mb-2">{{$name}}</h3>
    <p class="text-gray-600 text-sm text-center px-8 pb-4">{{ $description }}</p>
</div>