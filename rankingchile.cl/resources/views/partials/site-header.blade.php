@php
    $headerStats = $headerStats ?? ['active_profiles' => 0, 'period_amount' => 0, 'outbound_clicks' => 0];
    $formatAmount = fn(int $v) => $v >= 1_000_000 ? '$' . number_format($v / 1_000_000, 1) . 'M' : ($v >= 1_000 ? '$' . number_format($v / 1_000) . 'k' : '$' . number_format($v));
@endphp
<header class="sticky top-0 z-40 bg-[#FFFDF7] border-b border-gray-200" x-data="{ mobileOpen: false }">
    {{-- Desktop --}}
    <div class="mx-auto max-w-6xl px-4 hidden md:flex items-center justify-between gap-4" style="height:72px">
        {{-- Brand --}}
        <a href="{{ route('home') }}" class="flex items-center gap-2 font-extrabold tracking-tight shrink-0 text-[#1B1B18]">
            <span class="text-xl">👑</span>
            <span class="text-lg">QUIÉN MANDA</span>
        </a>

        {{-- Live Stats Pill --}}
        @if($headerStats['active_profiles'] > 0 || $headerStats['period_amount'] > 0)
        <div class="flex items-center gap-3 bg-white border border-gray-200 rounded-full px-4 py-1.5 text-xs font-medium text-gray-600 shrink-0">
            @if($headerStats['active_profiles'] > 0)
            <span class="flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                <span>{{ number_format($headerStats['active_profiles']) }} compitiendo</span>
            </span>
            @endif
            @if($headerStats['period_amount'] > 0)
            <span class="text-gray-300">|</span>
            <span>{{ $formatAmount($headerStats['period_amount']) }} esta semana</span>
            @endif
            @if($headerStats['outbound_clicks'] > 0)
            <span class="text-gray-300">|</span>
            <span>{{ number_format($headerStats['outbound_clicks']) }} clics enviados</span>
            @endif
        </div>
        @endif

        {{-- Navigation --}}
        <nav class="flex items-center gap-1 text-sm font-semibold ml-auto">
            <a href="{{ route('home') }}"
               class="px-3 py-1.5 rounded-lg transition {{ ($activePage ?? '') === 'home' ? 'text-[#F53003] bg-[#F53003]/5' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                Ranking
            </a>
            <a href="{{ route('categories') }}"
               class="px-3 py-1.5 rounded-lg transition {{ ($activePage ?? '') === 'categories' ? 'text-[#F53003] bg-[#F53003]/5' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                Categorías
            </a>
            <a href="{{ route('rules') }}"
               class="px-3 py-1.5 rounded-lg transition {{ ($activePage ?? '') === 'rules' ? 'text-[#F53003] bg-[#F53003]/5' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                Reglas
            </a>
        </nav>

        {{-- Primary CTA --}}
        <a href="{{ route('entrar.index') }}"
           class="shrink-0 bg-[#1B1B18] hover:bg-[#2a2a26] text-white text-sm font-black px-5 py-2.5 rounded-xl transition active:scale-[0.97]">
            ENTRAR Y SUBIR
        </a>
    </div>

    {{-- Mobile --}}
    <div class="flex md:hidden items-center justify-between px-4" style="height:62px">
        <a href="{{ route('home') }}" class="flex items-center gap-1.5 font-extrabold text-[#1B1B18]">
            <span class="text-lg">👑</span>
            <span class="text-base">QUIÉN MANDA</span>
        </a>
        <div class="flex items-center gap-2">
            <a href="{{ route('entrar.index') }}"
               class="bg-[#1B1B18] text-white text-xs font-black px-3 py-2 rounded-lg">
                ENTRAR
            </a>
            <button @click="mobileOpen = !mobileOpen" class="p-2 text-gray-600" aria-label="Menú">
                <svg x-show="!mobileOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <svg x-show="mobileOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>

    {{-- Mobile Stats Strip --}}
    @if($headerStats['active_profiles'] > 0 || $headerStats['period_amount'] > 0)
    <div class="md:hidden flex items-center justify-center gap-3 bg-white border-t border-gray-100 px-4 py-1.5 text-[11px] font-medium text-gray-500">
        @if($headerStats['active_profiles'] > 0)
        <span class="flex items-center gap-1">
            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
            {{ number_format($headerStats['active_profiles']) }} compitiendo
        </span>
        @endif
        @if($headerStats['period_amount'] > 0)
        <span>{{ $formatAmount($headerStats['period_amount']) }} esta semana</span>
        @endif
    </div>
    @endif

    {{-- Mobile Drawer --}}
    <div x-show="mobileOpen" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="md:hidden bg-white border-t border-gray-100 shadow-lg px-4 py-4 space-y-1"
         @click.away="mobileOpen = false">
        <a href="{{ route('home') }}" class="block px-3 py-2.5 rounded-lg text-sm font-semibold {{ ($activePage ?? '') === 'home' ? 'text-[#F53003] bg-[#F53003]/5' : 'text-gray-700 hover:bg-gray-50' }}">Ranking</a>
        <a href="{{ route('categories') }}" class="block px-3 py-2.5 rounded-lg text-sm font-semibold {{ ($activePage ?? '') === 'categories' ? 'text-[#F53003] bg-[#F53003]/5' : 'text-gray-700 hover:bg-gray-50' }}">Categorías</a>
        <a href="{{ route('rules') }}" class="block px-3 py-2.5 rounded-lg text-sm font-semibold {{ ($activePage ?? '') === 'rules' ? 'text-[#F53003] bg-[#F53003]/5' : 'text-gray-700 hover:bg-gray-50' }}">Reglas</a>
        <div class="border-t border-gray-100 my-2"></div>
        <a href="{{ route('entrar.index') }}" class="block px-3 py-2.5 rounded-lg text-sm font-black text-white bg-[#1B1B18] text-center">ENTRAR Y SUBIR</a>
    </div>
</header>
