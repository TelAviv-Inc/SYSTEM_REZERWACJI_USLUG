<nav class="flex items-center justify-between px-6 py-4 max-w-7xl mx-auto">
    @auth
        <div class="flex justify-between gap-10">
            <div class="flex items-center gap-2.5 font-bold text-lg">
                <span class="w-8 h-8 rounded-lg flex items-center justify-center bg-brand-accent">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                        <path d="M12 12a4 4 0 100-8 4 4 0 000 8zM4 20c0-3.3 3.6-6 8-6s8 2.7 8 6" stroke="#fff"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </span>
                <p>System Rezerwacji Usług</p>
            </div>
            <div class="flex gap-2.5">
                @if (auth()->user()->role === 'admin')
                    <a href="{{ route('dashboard') }}"
                        class="text-xs font-bold text-[#A07820] bg-[#F7F1E3] uppercase tracking-wider shadow-[#D4AF37] shadow-md p-2 rounded-lg self-start hover:shadow-lg hover:shadow-[#D4AF37]  transition hover:-rotate-3 delay-100 duration-300">Panel
                        administratora</a>

                @elseif (auth()->user()->role === 'employee')
                    <a href="{{ route('dashboard') }}"
                        class="text-xs font-bold text-brand-accent bg-[#eff6ff] uppercase tracking-wider p-2 rounded-lg self-start">Panel
                        administratora</a>
                @endif
                <a href="{{ route('dashboard') }}"
                    class="text-xs font-bold text-brand-accent bg-[#eff6ff] uppercase tracking-wider  p-2 rounded-lg self-start">Moje
                    rezerwacje</a>
            </div>

        </div>


        <div class="flex items-center gap-2.5">
            <!-- User Dropdown -->
            <div class="relative group">
                <button class="flex items-center gap-2 text-gray-700 font-medium cursor-pointer">
                    <i class="fas fa-user-circle text-xl"></i>
                    <span>{{ auth()->user()->name }} {{ auth()->user()->surname }}</span>
                    <i class="fas fa-chevron-down ml-1"></i>
                </button>
                <div
                    class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 hidden group-hover:block z-10 border border-gray-200">
                    <a href="#" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><i
                            class="fas fa-user"></i>Profil</a>
                    <form action="{{ route('logout') }}" method="post">
                        @csrf
                        <button type="submit"
                            class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Wyloguj
                            sie</button>
                    </form>
                </div>
            </div>
        </div>
    @endauth

</nav>