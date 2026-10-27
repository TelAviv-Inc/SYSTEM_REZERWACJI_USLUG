@props(['categories' => [], 'services' => []])
<div class="mt-4" x-data="{
    scrollToServices() {
        const list = this.$root.querySelector('[data-category-services]');
        if (!list) return;

        // Skip when the list is already on screen (desktop, where the cards fit in one row)
        const top = list.getBoundingClientRect().top;
        if (top >= 0 && top < window.innerHeight * 0.75) return;

        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        list.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
    }
}" x-on:category-services-shown="scrollToServices()">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Service Category Cards -->
        @foreach ($categories as $category)
            <x-service-category-card :name="$category->name" :description="$category->description" :icon="$category->icon" />
        @endforeach
    </div>

    @if (!empty($selectedCategory))
        <div class="mt-8" data-category-services>
            <x-category-service :category="$selectedCategory" :services="$services" />
        </div>
    @endif
</div>
