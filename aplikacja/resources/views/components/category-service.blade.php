@props(['name' => "Bez nazwy", 'services' => []])
<div class="bg-white rounded-xl border border-brand-border p-6 shadow-sm mt-12">

    <h3 class="text-base font-bold text-brand-navy border-b border-brand-border pb-3">
        Usługi w kategorii: <span class="text-brand-accent">{{ $name }}</span>
    </h3>
    @foreach ($services as $service)
        <div class="flex flex-row justify-between items-center border-b border-brand-border mt-4">
            <div class="flex flex-col mb-5">
                <h4 class="text-base font-bold text-brand-navy mt-2">{{ $service->name }}</h4>
                <p class="text-sm font-semibold text-brand-muted mt-2">{{ $service->description }}</p>
            </div>

            <div class="flex flex-row gap-2 items-center justify-center">
                <div class="flex flex-col mr-2">
                    <p class="text-base font-bold text-brand-navy mt-2">{{ $service->price }} PLN</p>
                    <p class="text-sm font-semibold text-brand-muted mt-2">{{ $service->duration }} min</p>
                </div>
                <button
                    class="bg-brand-accent text-white text-sm font-semibold rounded-md px-3 py-1.5 hover:shadow-lg hover:scale-105 transition duration-300">Rezerwuj</button>

            </div>


        </div>

    @endforeach
    <!-- Breathing in, I calm body and mind. Breathing out, I smile. - Thich Nhat Hanh -->
</div>