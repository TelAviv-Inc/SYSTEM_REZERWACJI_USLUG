<div class="bg-white rounded-xl border border-brand-border p-6 shadow-sm space-y-6">

    <!-- Usługi: Koloryzacja -->
    <div x-show="selectedCategory === 'Koloryzacja'" class="space-y-3">
        <h3 class="text-base font-bold text-brand-navy border-b border-brand-border pb-3 mb-4">
            Usługi w kategorii: <span class="text-brand-accent">Koloryzacja</span>
        </h3>

        <x-category-service 
            title="Farbowanie całych włosów" 
            description="Jednolity kolor na całej długości, w cenie mycie i modelowanie." 
            price="220 PLN" 
            duration="90 min" 
            @click="bookingService = { name: 'Farbowanie całych włosów', price: '220 PLN', duration: '90 min' }; $dispatch('open-modal', 'booking-modal')" 
        />

        <x-category-service 
            title="Rozjaśnianie / Dekoloryzacja" 
            description="Bezpieczne przejście z ciemnych tonów do jasnych." 
            price="300 PLN" 
            duration="120 min" 
            @click="bookingService = { name: 'Rozjaśnianie / Dekoloryzacja', price: '300 PLN', duration: '120 min' }; $dispatch('open-modal', 'booking-modal')" 
        />

        <x-category-service 
            title="Highlights / Refleksy" 
            description="Subtelne rozświetlenie wybranych pasm włosów." 
            price="260 PLN" 
            duration="105 min" 
            @click="bookingService = { name: 'Highlights / Refleksy', price: '260 PLN', duration: '105 min' }; $dispatch('open-modal', 'booking-modal')" 
        />
    </div>

    <!-- Usługi: Ciecie -->
    <div x-show="selectedCategory === 'Ciecie'" class="space-y-3">
        <h3 class="text-base font-bold text-brand-navy border-b border-brand-border pb-3 mb-4">
            Usługi w kategorii: <span class="text-brand-accent">Cięcie</span>
        </h3>

        <x-category-service 
            title="Strzyżenie Damskie" 
            description="Cięcie dopasowane do kształtu twarzy, mycie i stylizacja." 
            price="150 PLN" 
            duration="60 min" 
            @click="bookingService = { name: 'Strzyżenie Damskie', price: '150 PLN', duration: '60 min' }; $dispatch('open-modal', 'booking-modal')" 
        />

        <x-category-service 
            title="Strzyżenie Męskie" 
            description="Cięcie klasyczne lub nowoczesne, stylizacja i pielęgnacja." 
            price="90 PLN" 
            duration="45 min" 
            @click="bookingService = { name: 'Strzyżenie Męskie', price: '90 PLN', duration: '45 min' }; $dispatch('open-modal', 'booking-modal')" 
        />
    </div>

    <!-- Usługi: Zabiegi -->
    <div x-show="selectedCategory === 'Zabiegi'" class="space-y-3">
        <h3 class="text-base font-bold text-brand-navy border-b border-brand-border pb-3 mb-4">
            Usługi w kategorii: <span class="text-brand-accent">Zabiegi</span>
        </h3>

        <x-category-service 
            title="Botoks na włosy" 
            description="Głęboka regeneracja i wygładzenie struktury włosa." 
            price="250 PLN" 
            duration="75 min" 
            @click="bookingService = { name: 'Botoks na włosy', price: '250 PLN', duration: '75 min' }; $dispatch('open-modal', 'booking-modal')" 
        />
    </div>

    <!-- Usługi: Pielegnacja -->
    <div x-show="selectedCategory === 'Pielegnacja'" class="space-y-3">
        <h3 class="text-base font-bold text-brand-navy border-b border-brand-border pb-3 mb-4">
            Usługi w kategorii: <span class="text-brand-accent">Pielęgnacja</span>
        </h3>

        <x-category-service 
            title="Rytuał Nawilżający" 
            description="Intensywna maska odżywcza z sauną na włosy." 
            price="180 PLN" 
            duration="50 min" 
            @click="bookingService = { name: 'Rytuał Nawilżający', price: '180 PLN', duration: '50 min' }; $dispatch('open-modal', 'booking-modal')" 
        />
    </div>

</div>