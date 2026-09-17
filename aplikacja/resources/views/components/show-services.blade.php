@props(['categories' => [], 'services' => []])
<div class="mt-4">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Service Category Cards -->
        @foreach ($categories as $category)
            <x-service-category-card :name="$category->name" :description="$category->description"
                :icon="$category->icon" />
        @endforeach
    </div>
    
    @if (!empty($selectedCategory))
        <div class="mt-8">
                <x-category-service :name="$selectedCategory" :services="$services" />
        </div>
    @endif
</div>