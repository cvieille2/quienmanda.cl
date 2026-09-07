@php
    $searchProfiles = collect($headerSearchProfiles ?? [])->values()->all();
    $isHome = request()->routeIs('home');
    $isCategories = request()->routeIs('categories*', 'category.show*');
    $isHowItWorks = $isHome && request()->getRequestUri() === '/#como-funciona';
    $user = auth()->user();
@endphp

<header
    class="sticky top-0 z-[1000] border-b border-white/8 bg-[#07182D] text-white shadow-[0_4px_18px_rgba(0,0,0,0.10)]"
    x-data="headerShell(@js($searchProfiles))"
    @keydown.escape.window="closePanels()"
>
    <div class="mx-auto flex h-[64px] max-w-[1280px] items-center justify-between gap-3 px-4 sm:px-6 lg:h-[72px] lg:px-6">
        {{-- Brand --}}
        <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2 font-extrabold tracking-tight text-white">
            <span aria-hidden="true" class="inline-flex h-8 w-8 items-center justify-center rounded-full text-[#FFC21C]">👑</span>
            <span class="text-[19px] font-black lg:text-[22px]">Quién Manda</span>
        </a>

        {{-- Desktop navigation --}}
        <nav aria-label="Navegación principal" class="hidden items-center gap-1 lg:flex">
            <a href="{{ route('home') }}" aria-current="{{ $isHome ? 'page' : 'false' }}" class="relative rounded-xl px-3 py-2 text-sm font-semibold text-white/85 transition hover:text-[#FFC21C]">
                Ranking
                <span @class(['absolute inset-x-3 -bottom-1 h-0.5 rounded-full bg-[#FFC21C]', 'opacity-100' => $isHome, 'opacity-0' => ! $isHome])></span>
            </a>
            <a href="{{ route('categories') }}" aria-current="{{ $isCategories ? 'page' : 'false' }}" class="relative rounded-xl px-3 py-2 text-sm font-semibold text-white/85 transition hover:text-[#FFC21C]">
                Categorías
                <span @class(['absolute inset-x-3 -bottom-1 h-0.5 rounded-full bg-[#FFC21C]', 'opacity-100' => $isCategories, 'opacity-0' => ! $isCategories])></span>
            </a>
            <a href="{{ route('home') }}#como-funciona" aria-current="{{ $isHowItWorks ? 'page' : 'false' }}" class="relative rounded-xl px-3 py-2 text-sm font-semibold text-white/85 transition hover:text-[#FFC21C]">
                Cómo funciona
                <span @class(['absolute inset-x-3 -bottom-1 h-0.5 rounded-full bg-[#FFC21C]', 'opacity-100' => $isHowItWorks, 'opacity-0' => ! $isHowItWorks])></span>
            </a>
        </nav>

        {{-- Desktop search + account actions --}}
        <div class="hidden items-center gap-2 lg:flex">
            <button type="button" @click="openSearch()" class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-white transition hover:border-[#FFC21C] hover:text-[#FFC21C]" aria-label="Buscar perfiles">
                <svg aria-hidden="true" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"/>
                </svg>
            </button>

            @guest
                <a href="{{ route('login') }}" class="rounded-xl px-3 py-2 text-sm font-semibold text-white/90 transition hover:text-[#FFC21C]">
                    Iniciar sesión
                </a>
                <a href="{{ route('entrar.index') }}" class="rounded-xl bg-[#FFC21C] px-5 py-3 text-sm font-black text-[#07182D] transition hover:brightness-95 hover:-translate-y-px">
                    Súmate
                </a>
            @else
                <div class="relative" x-data="{ open: false }" @click.away="open = false">
                    <button type="button" @click="open = !open" class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm font-bold text-white transition hover:border-[#FFC21C]">
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-[#FFC21C] text-sm font-black text-[#07182D]" aria-hidden="true">{{ mb_substr($user->name ?? 'U', 0, 1) }}</span>
                        <span class="max-w-[10rem] truncate">{{ $user->name ?? 'Mi cuenta' }}</span>
                    </button>

                    <div x-show="open" x-cloak x-transition class="absolute right-0 mt-2 w-56 overflow-hidden rounded-2xl border border-white/10 bg-[#0B203A] shadow-2xl">
                        <a href="/mi-perfil" class="block px-4 py-3 text-sm font-semibold text-white/90 hover:bg-white/5">Mi perfil</a>
                        <a href="/mis-perfiles" class="block px-4 py-3 text-sm font-semibold text-white/90 hover:bg-white/5">Mis perfiles</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full px-4 py-3 text-left text-sm font-semibold text-white/90 hover:bg-white/5">Cerrar sesión</button>
                        </form>
                    </div>
                </div>
            @endguest
        </div>

        {{-- Mobile actions --}}
        <div class="flex items-center gap-1 lg:hidden">
            <button type="button" @click="openSearch()" class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-white" aria-label="Buscar perfiles">
                <svg aria-hidden="true" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"/>
                </svg>
            </button>
            <button type="button" @click="mobileOpen = !mobileOpen" class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-white" aria-label="Abrir menú" :aria-expanded="mobileOpen.toString()">
                <svg x-show="!mobileOpen" aria-hidden="true" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg x-show="mobileOpen" aria-hidden="true" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Mobile drawer --}}
    <div x-show="mobileOpen" x-cloak class="fixed inset-0 z-[1001] lg:hidden">
        <div class="absolute inset-0 bg-black/50" @click="closePanels()"></div>
        <aside x-ref="mobileDrawer" class="absolute right-0 top-0 h-full w-[min(88vw,360px)] bg-[#07182D] px-4 py-5 shadow-2xl" @keydown.tab.prevent="trapFocus($event, 'mobileDrawer')">
            <div class="flex items-center justify-between">
                <span class="text-sm font-black uppercase tracking-[0.22em] text-[#FFC21C]">Menú</span>
                <button type="button" @click="closePanels()" class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-white" aria-label="Cerrar menú">
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <nav aria-label="Navegación principal" class="mt-6 space-y-2">
                <a href="{{ route('home') }}" class="block rounded-2xl px-4 py-3 text-sm font-semibold text-white/90 hover:bg-white/5">Ranking</a>
                <a href="{{ route('categories') }}" class="block rounded-2xl px-4 py-3 text-sm font-semibold text-white/90 hover:bg-white/5">Categorías</a>
                <a href="{{ route('home') }}#como-funciona" class="block rounded-2xl px-4 py-3 text-sm font-semibold text-white/90 hover:bg-white/5">Cómo funciona</a>
                <button type="button" @click="openSearch(); closePanels()" class="block w-full rounded-2xl px-4 py-3 text-left text-sm font-semibold text-white/90 hover:bg-white/5">Buscar</button>
            </nav>

            <div class="mt-6 border-t border-white/10 pt-4">
                @guest
                    <a href="{{ route('login') }}" class="block rounded-2xl px-4 py-3 text-sm font-semibold text-white/90 hover:bg-white/5">Iniciar sesión</a>
                    <a href="{{ route('entrar.index') }}" class="mt-2 block rounded-2xl bg-[#FFC21C] px-4 py-3 text-center text-sm font-black text-[#07182D]">Súmate</a>
                @else
                    <p class="px-4 text-xs font-bold uppercase tracking-[0.22em] text-white/50">Cuenta</p>
                    <div class="mt-2 space-y-1">
                        <a href="/mi-perfil" class="block rounded-2xl px-4 py-3 text-sm font-semibold text-white/90 hover:bg-white/5">Mi perfil</a>
                        <a href="/mis-perfiles" class="block rounded-2xl px-4 py-3 text-sm font-semibold text-white/90 hover:bg-white/5">Mis perfiles</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full rounded-2xl px-4 py-3 text-left text-sm font-semibold text-white/90 hover:bg-white/5">Cerrar sesión</button>
                        </form>
                    </div>
                @endguest
            </div>
        </aside>
    </div>

    {{-- Search overlay --}}
    <div x-show="searchOpen" x-cloak class="fixed inset-0 z-[1002]" aria-modal="true" role="dialog">
        <div class="absolute inset-0 bg-black/55" @click="closePanels()"></div>
        <div class="absolute inset-x-4 top-16 mx-auto max-w-2xl rounded-3xl border border-white/10 bg-[#07182D] p-4 shadow-2xl sm:top-20 sm:p-5" x-ref="searchPanel" @keydown.tab.prevent="trapFocus($event, 'searchPanel')">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/5 text-[#FFC21C]" aria-hidden="true">🔎</div>
                <div class="flex-1">
                    <input
                        x-ref="searchInput"
                        x-model="searchQuery"
                        type="search"
                        placeholder="Buscar perfil..."
                        class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white outline-none placeholder:text-white/40 focus:border-[#FFC21C]"
                        @keydown.enter.prevent="goFirstResult()"
                    />
                    <p class="mt-1 text-xs text-white/45">Escribe un nombre y navega con teclado.</p>
                </div>
                <button type="button" @click="closePanels()" class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-white" aria-label="Cerrar búsqueda">✕</button>
            </div>

            <div class="mt-4 max-h-[50vh] overflow-auto space-y-2 pr-1">
                <template x-if="filteredProfiles.length">
                    <template x-for="profile in filteredProfiles" :key="profile.slug">
                        <button type="button" @click="goToProfile(profile.slug)" class="flex w-full items-center justify-between rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-left hover:border-[#FFC21C]">
                            <span>
                                <span class="block font-semibold text-white" x-text="profile.name"></span>
                                <span class="block text-xs text-white/45" x-text="profile.category || 'Perfil'"></span>
                            </span>
                            <span class="text-[#FFC21C]" aria-hidden="true">→</span>
                        </button>
                    </template>
                </template>
                <div x-show="!filteredProfiles.length" class="rounded-2xl border border-dashed border-white/10 px-4 py-6 text-center text-sm text-white/55">
                    No hay resultados.
                </div>
            </div>
        </div>
    </div>
</header>

@push('scripts')
<script>
function headerShell(searchProfiles) {
    return {
        mobileOpen: false,
        searchOpen: false,
        searchQuery: '',
        searchProfiles: searchProfiles || [],
        get filteredProfiles() {
            const q = this.searchQuery.trim().toLowerCase();
            if (!q) return this.searchProfiles.slice(0, 8);
            return this.searchProfiles.filter((profile) => {
                return [profile.name, profile.slug, profile.category]
                    .filter(Boolean)
                    .some((field) => String(field).toLowerCase().includes(q));
            }).slice(0, 8);
        },
        openSearch() {
            this.searchOpen = true;
            this.mobileOpen = false;
            this.$nextTick(() => {
                this.$refs.searchInput?.focus();
            });
        },
        closePanels() {
            this.mobileOpen = false;
            this.searchOpen = false;
            this.searchQuery = '';
        },
        goToProfile(slug) {
            window.location.href = `/perfil/${slug}`;
        },
        goFirstResult() {
            const first = this.filteredProfiles[0];
            if (first) this.goToProfile(first.slug);
        },
        trapFocus(event, refName) {
            const root = this.$refs[refName];
            if (!root) return;
            const focusable = Array.from(root.querySelectorAll('a, button, input, [tabindex]:not([tabindex="-1"])'));
            if (!focusable.length) return;
            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
                return;
            }
            if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        },
    };
}
</script>
@endpush
