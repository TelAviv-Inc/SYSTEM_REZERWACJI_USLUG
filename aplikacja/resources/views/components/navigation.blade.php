<nav x-data="{ mobileOpen: false }" class="relative max-w-7xl mx-auto px-4 sm:px-6 py-3 sm:py-4">
    @auth
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-10 min-w-0">
                <a href="{{ route('dashboard.index') }}" class="min-w-0">
                    <div class="flex items-center gap-2.5 font-bold text-base sm:text-lg">
                        <span
                            class="w-8 h-8 shrink-0 rounded-lg flex items-center justify-center bg-gradient-to-r from-primary to-secondary">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                <path d="M12 12a4 4 0 100-8 4 4 0 000 8zM4 20c0-3.3 3.6-6 8-6s8 2.7 8 6" stroke="#fff"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                        <p class="truncate">System Rezerwacji Usług</p>
                    </div>
                </a>

                {{-- Desktop links (lg and up) --}}
                <div class="hidden lg:flex gap-2.5">
                    @if (auth()->user()->role === 'admin')
                        <a href="{{ route('dashboard.index') }}"
                            class="text-xs font-bold text-[#A07820] bg-[#F7F1E3] uppercase tracking-wider shadow-[#D4AF37] shadow-md p-2 rounded-lg self-start hover:shadow-lg hover:shadow-[#D4AF37]  transition hover:-rotate-3 delay-100 duration-300">Panel
                            administratora</a>

                    @elseif (auth()->user()->role === 'employee')
                        <a href="{{ route('dashboard.index') }}"
                            class="text-xs font-bold text-[#1B4332] bg-[#E8F5E9]/80 uppercase tracking-wider shadow-[#122C21] shadow-md p-2 rounded-lg self-start  transition hover:rotate-3 delay-100 duration-300">Moj
                            Grafik</a>
                    @endif
                    <a href="{{ route('dashboard.index') }}"
                        class="text-xs font-bold text-brand-accent bg-[#eff6ff] uppercase tracking-wider  p-2 rounded-lg self-start">Moje
                        rezerwacje</a>
                </div>
            </div>

            {{-- Desktop user dropdown (lg and up) --}}
            <div class="hidden lg:flex items-center gap-2.5">
                <div class="relative group" x-data="{dropdownOpen: false}" @click.outside="dropdownOpen = false">
                    <button class="flex items-center gap-2 text-gray-700 font-medium cursor-pointer"
                        @click="dropdownOpen = !dropdownOpen">
                        <i class="fas fa-user-circle text-xl"></i>
                        <span>{{ auth()->user()->name }} {{ auth()->user()->surname }}</span>
                        <i class="fas fa-chevron-down ml-1"></i>
                    </button>
                    <div x-show="dropdownOpen" x-transition style="display: none;"
                        class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10 border border-gray-200">
                        <a href="{{ route('dashboard.profile') }}"
                            class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><i
                                class="fas fa-user"></i>Profil</a>
                        <form action="{{ route('logout') }}" method="post">
                            @csrf
                            <button type="submit"
                                class="flex w-full items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Wyloguj
                                sie</button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Hamburger (below lg) --}}
            <button type="button" class="lg:hidden shrink-0 p-2 -mr-2 text-gray-700 rounded-md hover:bg-gray-100"
                @click="mobileOpen = !mobileOpen" :aria-expanded="mobileOpen" aria-label="Menu">
                <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg x-show="mobileOpen" style="display: none;" class="w-6 h-6" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Mobile menu panel (below lg) --}}
        <div x-show="mobileOpen" x-transition style="display: none;" @click.outside="mobileOpen = false"
            class="lg:hidden absolute left-4 right-4 sm:left-6 sm:right-6 top-full z-20 mt-1 rounded-xl border border-gray-200 bg-white p-4 shadow-lg space-y-3">
            <p class="flex items-center gap-2 text-sm font-medium text-gray-700 pb-3 border-b border-gray-200">
                <i class="fas fa-user-circle text-xl"></i>
                <span class="truncate">{{ auth()->user()->name }} {{ auth()->user()->surname }}</span>
            </p>

            @if (auth()->user()->role === 'admin')
                <a href="{{ route('dashboard.index') }}"
                    class="block text-center text-xs font-bold text-[#A07820] bg-[#F7F1E3] uppercase tracking-wider shadow-[#D4AF37] shadow-md p-3 rounded-lg">Panel
                    administratora</a>
            @elseif (auth()->user()->role === 'employee')
                <a href="{{ route('dashboard.index') }}"
                    class="block text-center text-xs font-bold text-[#1B4332] bg-[#E8F5E9]/80 uppercase tracking-wider shadow-[#122C21] shadow-md p-3 rounded-lg">Moj
                    Grafik</a>
            @endif
            <a href="{{ route('dashboard.index') }}"
                class="block text-center text-xs font-bold text-brand-accent bg-[#eff6ff] uppercase tracking-wider p-3 rounded-lg">Moje
                rezerwacje</a>

            <div class="pt-3 border-t border-gray-200">
                <a href="{{ route('dashboard.profile') }}"
                    class="flex items-center gap-2 px-2 py-2 text-sm text-gray-700 rounded-md hover:bg-gray-50"><i
                        class="fas fa-user"></i>Profil</a>
                <form action="{{ route('logout') }}" method="post">
                    @csrf
                    <button type="submit"
                        class="flex w-full items-center gap-2 px-2 py-2 text-sm text-gray-700 rounded-md hover:bg-gray-50">Wyloguj
                        sie</button>
                </form>
            </div>
        </div>
    @endauth

</nav>