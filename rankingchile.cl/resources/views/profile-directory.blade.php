@extends('layouts.app')

@section('title', 'Perfiles y creadores de Chile | Quién Manda')

@push('meta')
    <meta name="description" content="Explora los perfiles, creadores, proyectos y marcas que compiten en el ranking de Quién Manda. Entra a una ficha, revisa su posición y apóyala para subir." />
    <link rel="canonical" href="{{ url('/perfil/') }}" />
    <meta property="og:title" content="Perfiles y creadores de Chile | Quién Manda" />
    <meta property="og:description" content="Descubre quién manda, qué posición ocupa cada perfil y cuánto falta para superarlo." />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ url('/perfil/') }}" />
@endpush

@section('body')
<div class="min-h-screen" x-data="profileDirectory(@js($profiles))">
    @php $activePage = 'profiles'; @endphp
    @include('partials.site-header', ['headerSearchProfiles' => $headerSearchProfiles])

    <main class="mx-auto max-w-6xl px-4 pb-28 pt-8 sm:pt-12">
        <header class="mx-auto max-w-3xl text-center">
            <p class="text-xs font-black uppercase tracking-[0.28em] text-[#F53003]">{{ $periodLabel }}</p>
            <h1 class="mt-3 text-4xl font-black tracking-tight text-[#07182D] sm:text-5xl">Perfiles que compiten por mandar</h1>
            <p class="mx-auto mt-4 max-w-2xl text-base leading-7 text-gray-600 sm:text-lg">
                Cada ficha muestra la posición, el apoyo acumulado y la pelea disponible. Comparte un perfil o entra a su landing para impulsarlo y ayudarlo a subir.
            </p>
        </header>

        @if (count($profiles) > 0)
            <div class="mx-auto mt-8 max-w-xl">
                <label for="profile-directory-search" class="sr-only">Buscar perfiles</label>
                <input id="profile-directory-search" type="search" x-model="query" placeholder="Busca por nombre, categoría o slug..."
                       class="w-full rounded-2xl border-2 border-gray-200 bg-white px-5 py-4 text-base outline-none transition focus:border-[#F53003]" />
            </div>

            <section class="mt-8" aria-labelledby="profile-directory-heading">
                <div class="flex items-end justify-between gap-4">
                    <h2 id="profile-directory-heading" class="text-xl font-black text-[#07182D]">Todos los perfiles</h2>
                    <span class="text-sm font-semibold text-gray-500">{{ number_format($rankingTotal) }} publicados</span>
                </div>

                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <template x-for="profile in filteredProfiles" :key="profile.slug">
                        <a :href="'/perfil/' + profile.slug" class="group rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-[#F53003] hover:shadow-md">
                            <div class="flex items-start justify-between gap-3">
                                <span class="inline-flex h-10 min-w-10 items-center justify-center rounded-full bg-[#EEF2F7] px-3 text-lg font-black text-[#07182D]" x-text="'#' + profile.position"></span>
                                <span class="text-xs font-bold uppercase tracking-wide text-[#F53003]">Ver ficha →</span>
                            </div>
                            <h3 class="mt-5 truncate text-lg font-black text-[#07182D] group-hover:text-[#F53003]" x-text="profile.name"></h3>
                            <p class="mt-1 truncate text-sm text-gray-500" x-text="profile.category || 'Perfil público'"></p>
                            <div class="mt-5 flex items-end justify-between border-t border-gray-100 pt-4">
                                <div>
                                    <p class="text-xs text-gray-400">Apoyo acumulado</p>
                                    <p class="text-xl font-black text-[#07182D]" x-text="money(profile.total_real_clp)"></p>
                                </div>
                                <p class="text-right text-xs font-bold text-gray-500" x-show="profile.position > 1">Roba el #<span x-text="profile.position - 1"></span></p>
                                <p class="text-right text-xs font-bold text-[#F8B803]" x-show="profile.position === 1">Defiende el #1</p>
                            </div>
                        </a>
                    </template>
                </div>

                <div x-show="filteredProfiles.length === 0" x-cloak class="mt-6 rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-10 text-center text-gray-500">
                    No encontramos un perfil con esa búsqueda.
                </div>
            </section>
        @else
            <section class="mx-auto mt-10 max-w-2xl rounded-3xl border border-dashed border-gray-300 bg-white px-6 py-12 text-center">
                <div class="text-5xl">👑</div>
                <h2 class="mt-4 text-xl font-black text-[#07182D]">Todavía no hay perfiles publicados</h2>
                <p class="mt-2 text-sm text-gray-600">Sé el primero en crear una ficha pública para compartirla y empezar la competencia.</p>
                <a href="{{ route('entrar.index') }}" class="mt-6 inline-flex rounded-xl bg-[#07182D] px-5 py-3 text-sm font-black text-white">Crear mi perfil →</a>
            </section>
        @endif

        <div class="mt-10 text-center">
            <a href="{{ route('home') }}" class="text-sm font-bold text-[#F53003] underline underline-offset-2">← Volver al ranking</a>
        </div>
    </main>

    @include('partials.site-footer')
</div>

@push('schema')
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'CollectionPage',
    'name' => 'Perfiles y creadores de Chile',
    'description' => 'Directorio de perfiles que compiten en Quién Manda.',
    'url' => url('/perfil/'),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@push('scripts')
<script>
function profileDirectory(profiles) {
    return {
        profiles: profiles || [],
        query: '',
        get filteredProfiles() {
            const query = this.query.trim().toLowerCase();
            if (!query) return this.profiles;

            return this.profiles.filter((profile) => [profile.name, profile.category, profile.slug]
                .filter(Boolean)
                .some((value) => String(value).toLowerCase().includes(query)));
        },
        money(value) {
            return '$' + Number(value || 0).toLocaleString('es-CL');
        },
    };
}
</script>
@endpush
@endsection
