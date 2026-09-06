@extends('layouts.app')

@section('title', 'Categorías — ¿Quién Manda en Chile?')

@section('body')
<div class="min-h-screen">

    @php $activePage = 'categories'; @endphp
    @include('partials.site-header')

    <main class="mx-auto max-w-5xl px-4 pb-16">

        {{-- HERO --}}
        <section class="pt-8 pb-2 text-center">
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">Categorías</h1>
            <p class="text-gray-500 mt-2 max-w-xl mx-auto">
                Cada categoría tiene su propio ranking. Elige una para ver quién la lidera.
            </p>
        </section>

        {{-- MÁS ACTIVAS --}}
        @if (count($hotCategories) > 0)
            <section class="mt-6">
                <div class="flex items-center gap-2 mb-4">
                    <span class="text-xl">🔥</span>
                    <h2 class="text-lg font-extrabold">Más activas</h2>
                    <span class="text-xs text-gray-400 ml-1">— donde más se está apoyando ahora</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    @foreach ($hotCategories as $i => $cat)
                        @php $leader = $cat['top3'][0] ?? null; @endphp
                        <a href="#cat-{{ $cat['slug'] }}"
                           class="block rounded-2xl border p-4 transition hover:shadow-lg
                                  {{ $i === 0 ? 'bg-gradient-to-br from-[#FFF8E1] to-white border-[#F8B803]' : 'bg-white border-gray-200' }}">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-xs font-black px-2 py-0.5 rounded-full
                                    {{ $i === 0 ? 'bg-[#F53003] text-white' : ($i === 1 ? 'bg-[#F8B803] text-[#1B1B18]' : 'bg-gray-200 text-gray-600') }}">
                                    #{{ $i + 1 }}
                                </span>
                                @if ($i === 0)
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#F53003]">热门 hottest</span>
                                @endif
                            </div>
                            <h3 class="font-extrabold text-base">{{ $cat['name'] }}</h3>
                            <div class="text-xs text-gray-500 mt-0.5">
                                {{ $cat['count'] }} perfil{{ $cat['count'] !== 1 ? 'es' : '' }}
                            </div>
                            @if ($leader)
                                <div class="mt-3 pt-3 border-t border-gray-100">
                                    <div class="text-[10px] uppercase tracking-wider text-gray-400 font-bold">Lidera</div>
                                    <div class="font-bold text-sm mt-0.5 truncate">{{ $leader['display_name'] }}</div>
                                    <div class="font-black text-[#F53003]">{{ money_clp($leader['total_real_clp']) }}</div>
                                </div>
                            @endif
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- TODAS LAS CATEGORÍAS --}}
        <section class="mt-10">
            <h2 class="text-lg font-extrabold mb-4">Todas las categorías</h2>

            <div class="space-y-6">
                @forelse ($categories as $cat)
                    <div id="cat-{{ $cat['slug'] }}" class="rounded-2xl border border-gray-200 bg-white overflow-hidden">
                        {{-- Header de categoría --}}
                        <div class="px-5 py-4 flex items-center justify-between bg-gray-50 border-b border-gray-100">
                            <div>
                                <h3 class="font-extrabold text-lg">{{ $cat['name'] }}</h3>
                                <div class="text-xs text-gray-500">
                                    {{ $cat['count'] }} perfil{{ $cat['count'] !== 1 ? 'es' : '' }} · Total {{ money_clp($cat['total']) }}
                                </div>
                            </div>
                            <div class="text-2xl font-black text-[#F8B803]">
                                {{ money_clp($cat['total']) }}
                            </div>
                        </div>

                        {{-- Top 3 --}}
                        <div class="divide-y divide-gray-100">
                            @foreach ($cat['top3'] as $j => $profile)
                                @php
                                    $pos = $j + 1;
                                    $isLeader = $j === 0;
                                @endphp
                                <a href="{{ route('profile.show', $profile['slug']) }}"
                                   class="flex items-center gap-4 px-5 py-3 transition
                                          {{ $isLeader ? 'bg-[#FFF8E1]' : 'hover:bg-gray-50' }}">
                                    {{-- Posición --}}
                                    <span class="w-8 text-center font-black text-lg
                                        {{ $isLeader ? 'text-[#F8B803]' : 'text-gray-400' }}">
                                        {{ $pos }}
                                    </span>

                                    {{-- Badge verificación --}}
                                    <span class="rounded-full w-6 h-6 flex items-center justify-center text-[10px] text-white font-bold flex-shrink-0
                                        {{ $profile['verification_status'] === 'verified' ? 'bg-[#0ea5e9]' : ($profile['verification_status'] === 'pending' ? 'bg-orange-500' : 'bg-gray-300') }}">
                                        {{ $profile['verification_status'] === 'verified' ? '✓' : ($profile['verification_status'] === 'pending' ? '…' : '') }}
                                    </span>

                                    {{-- Nombre --}}
                                    <div class="flex-1 min-w-0">
                                        <div class="font-bold truncate">
                                            {{ $profile['display_name'] }}
                                            @if ($profile['is_community_created'])
                                                <span class="text-xs text-gray-400 ml-1">👥</span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Monto --}}
                                    <div class="text-right flex-shrink-0">
                                        <div class="font-black {{ $isLeader ? 'text-[#F53003]' : '' }}">
                                            {{ money_clp($profile['total_real_clp']) }}
                                        </div>
                                        <div class="text-[10px] text-gray-400">
                                            {{ number_format($profile['supporter_count']) }} impulso{{ $profile['supporter_count'] !== 1 ? 's' : '' }}
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>

                        {{-- Si hay más de 3, mostrar link --}}
                        @if ($cat['count'] > 3)
                            <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 text-center">
                                <span class="text-xs text-gray-400">
                                    +{{ $cat['count'] - 3 }} perfil{{ ($cat['count'] - 3) !== 1 ? 'es' : '' }} más en esta categoría
                                </span>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-16 text-gray-400">
                        <div class="text-4xl mb-3">🎵</div>
                        <p class="font-bold">Aún no hay categorías con perfiles</p>
                        <p class="text-sm mt-1">Cuando se aprueben perfiles, aparecerán aquí.</p>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- CTA --}}
        <section class="mt-10 text-center">
            <a href="{{ route('home') }}"
               class="inline-block bg-[#1B1B18] text-white font-bold px-8 py-4 rounded-2xl hover:bg-[#2a2a2a] transition">
                👑 Ver el ranking completo
            </a>
        </section>

        @include('partials.site-footer')
    </main>
</div>
@endsection
