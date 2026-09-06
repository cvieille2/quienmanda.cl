<header class="sticky top-0 z-40 bg-[#1B1B18] text-white border-b border-white/10 w-full">
    <div class="mx-auto max-w-5xl px-4 py-2 flex items-center justify-between gap-3">
        <a href="{{ route('home') }}" class="flex items-center gap-2 font-bold tracking-tight shrink-0">
            <span class="text-lg">👑</span>
            <span class="hidden sm:inline">quienmanda.cl</span>
            <span class="sm:hidden">quienmanda</span>
        </a>
        <nav class="flex items-center gap-3 text-sm font-medium">
            <a href="{{ route('home') }}"
               class="transition {{ ($activePage ?? '') === 'home' ? 'text-[#F8B803]' : 'text-white/70 hover:text-white' }}">
                Ranking
            </a>
            <a href="{{ route('categories') }}"
               class="transition {{ ($activePage ?? '') === 'categories' ? 'text-[#F8B803]' : 'text-white/70 hover:text-white' }}">
                Categorías
            </a>
            <a href="{{ route('rules') }}"
               class="transition {{ ($activePage ?? '') === 'rules' ? 'text-[#F8B803]' : 'text-white/70 hover:text-white' }}">
                Reglas
            </a>
            <a href="{{ route('entrar.index') }}"
               class="transition {{ ($activePage ?? '') === 'entrar' ? 'text-[#F8B803]' : 'text-white/70 hover:text-white' }}">
                Entrar
            </a>
            @isset($headerExtra)
                {{ $headerExtra }}
            @endisset
        </nav>
    </div>
</header>
