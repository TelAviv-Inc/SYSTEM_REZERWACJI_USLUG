@props(['category' => [], 'services' => []])
<div class="bg-white rounded-xl border border-brand-border p-4 sm:p-6 shadow-sm mt-6 sm:mt-12">

    <h3 class="text-base font-bold text-brand-navy border-b border-brand-border pb-3">
        Usługi w kategorii: <span class="text-brand-accent">{{ $category->name }}</span>
    </h3>
    @foreach ($services as $service)
        {{-- Stacked on phones (info on top, price + button below); side by side from sm up --}}
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between sm:gap-4 border-b border-brand-border mt-4 pb-4">
            <div class="flex flex-col min-w-0">
                <h4 class="text-base font-bold text-brand-navy break-words">{{ $service->name }}</h4>
                <p class="text-sm font-medium text-brand-muted mt-1 sm:mt-2 sm:font-semibold break-words">
                    {{ $service->description }}
                </p>
            </div>

            <div class="flex flex-row gap-3 items-center justify-between sm:justify-center shrink-0">
                <div class="flex flex-col sm:mr-2">
                    <p class="text-base font-bold text-brand-navy">{{ $service->price }} PLN</p>
                    @if ($service->duration > 60)
                        <p class="text-sm font-semibold text-brand-muted mt-1">
                            {{ Carbon\CarbonInterval::minutes($service->duration)->cascade()->forHumans(['short' => true]) }}
                        </p>
                    @else
                        <p class="text-sm font-semibold text-brand-muted mt-1">{{ $service->duration }} min</p>
                    @endif
                </div>
                <button wire:click="$dispatch('serviceChosen', {serviceID: '{{ $service->uuid }}'})"
                    class="shrink-0 bg-brand-accent text-white text-sm font-semibold rounded-md px-3 py-1.5 sm:px-3 sm:py-1.5 hover:shadow-lg hover:scale-105 transition duration-300">Rezerwuj</button>

            </div>


        </div>

    @endforeach
    <!-- Breathing in, I calm body and mind. Breathing out, I smile. - Thich Nhat Hanh -->
</div>