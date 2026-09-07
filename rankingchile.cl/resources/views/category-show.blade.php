@extends('layouts.app')

@section('title', $category->seoTitleGenerated())

@php
    $seoDesc = $category->seo_description ?: $category->seoDescriptionGenerated();
    $canonicalUrl = 'https://quienmanda.cl/categoria/' . $category->slug;
    $leaderPrice = $leader['total_real_clp'] ?? 0;
@endphp

@push('meta')
    @if($category->is_indexable)
        <meta name="description" content="{{ $seoDesc }}" />
        <meta name="robots" content="index, follow" />
    @else
        <meta name="robots" content="noindex" />
    @endif
    <link rel="canonical" href="{{ $canonicalUrl }}" />
    <meta property="og:title" content="{{ $category->seoTitleGenerated() }}" />
    <meta property="og:description" content="{{ $seoDesc }}" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ $canonicalUrl }}" />
@endpush

@section('body')
<div class="min-h-screen" x-data="quienmanda({
    paymentsEnabled: {{ $paymentsEnabled ? 'true' : 'false' }},
    checkoutToken: '{{ $checkoutToken }}',
    limits: { min: {{ $limits['min'] }}, max: {{ $limits['max'] }} },
    ranking: {{ json_encode(array_map(fn($r) => [
        'id' => $r['profile_id'], 'slug' => $r['slug'], 'name' => $r['display_name'],
        'amount' => $r['total_real_clp'], 'rank' => $r['position'],
        'behind' => $r['behind_clp'] ?? 0, 'overtake' => $r['overtake_above_clp'] ?? 0,
        'toTop' => $r['to_number_one_clp'] ?? 0, 'category' => $r['category'],
    ], $ranking)) }},
    viewModels: {{ json_encode(collect($viewModels)->map(fn($vm) => [
        'profile_id' => $vm->profileId(),
        'slug' => $vm->slug(),
        'name' => $vm->displayName(),
        'total_clp' => $vm->totalRealClp(),
        'total_formatted' => $vm->totalRealClpFormatted(),
        'supporter_count' => $vm->supporterCount(),
        'supporter_text' => $vm->supporterCountPluralized(),
        'position' => $vm->position(),
        'is_leader' => $vm->isLeader(),
        'verification_badge' => $vm->verificationBadge(),
        'verification_color' => $vm->verificationBadgeColor(),
        'is_community' => $vm->isCommunityCreated(),
        'overtake_above_clp' => $vm->overtakeAboveClp(),
        'to_number_one_clp' => $vm->toNumberOneClp(),
        'cta_label' => $vm->ctaLabel(),
        'cta_target' => $vm->ctaTargetPosition(),
        'cta_required' => $vm->ctaRequiredAmount(),
        'cta_required_formatted' => $vm->ctaRequiredAmountFormatted(),
        'has_projection' => $vm->hasProjection(),
    ])->values()->all()) }}
})">

    @php $activePage = 'categories'; @endphp
    @include('partials.site-header')
    @include('partials.category-nav', ['categories' => $categories, 'activeCategorySlug' => $activeCategorySlug])

    <main class="mx-auto max-w-6xl px-4 pb-28 pt-6">

        {{-- BREADCRUMBS --}}
        <nav class="mb-6 text-xs text-gray-400" aria-label="Breadcrumb">
            <ol class="flex items-center gap-1">
                <li><a href="{{ route('home') }}" class="hover:text-[#F53003] transition">Inicio</a></li>
                <li>/</li>
                <li><a href="{{ route('categories') }}" class="hover:text-[#F53003] transition">Categorías</a></li>
                <li>/</li>
                <li class="text-gray-600 font-medium">{{ $category->displayName() }}</li>
            </ol>
        </nav>

        {{-- HERO --}}
        <section class="bg-gradient-to-b from-[#FFFDF7] to-white py-8 sm:py-12 rounded-2xl mb-8" x-data="categoryHero()">
            <div class="mx-auto max-w-2xl px-4 text-center">
                @if($category->icon)
                    <div class="text-4xl mb-3">{{ $category->icon }}</div>
                @endif

                <p class="text-[10px] uppercase tracking-[0.3em] font-bold text-gray-400">{{ $periodRemainingText ?? 'ESTA SEMANA' }}</p>

                <h1 class="mt-3 text-3xl sm:text-4xl font-extrabold tracking-tight text-[#1B1B18]">
                    {{ $category->heroTitleFallback() }}
                </h1>

                @if($category->hero_description)
                    <p class="mt-3 text-lg text-gray-500">{{ $category->hero_description }}</p>
                @else
                    <p class="mt-3 text-lg text-gray-500">{{ $category->heroDescriptionFallback() }}</p>
                @endif

                @if($leader)
                    <div class="mt-4 inline-flex items-center gap-2 bg-white border border-gray-200 rounded-full px-4 py-1.5 text-sm">
                        <span class="font-bold text-gray-700">{{ $leader['display_name'] }}</span>
                        <span class="text-gray-300">·</span>
                        <span class="text-[#F53003] font-black">{{ money_clp($leader['total_real_clp']) }}</span>
                    </div>
                @else
                    <p class="mt-4 text-sm text-gray-400">Sé el primero en mover el ranking</p>
                @endif

                {{-- Source Input --}}
                <div class="mt-6 max-w-lg mx-auto">
                    <div class="relative">
                        <input type="text" x-model="sourceInput"
                               placeholder="Pega tu perfil, canal, web o proyecto"
                               class="w-full border-2 border-gray-200 focus:border-[#F53003] rounded-2xl px-5 py-4 text-base font-medium placeholder:text-gray-400 outline-none transition"
                               @keydown.enter="goToEntrar()" />
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-1 text-gray-400 text-xs">
                            <span>youtube.com/@...</span>
                        </div>
                    </div>
                </div>

                {{-- Position Selector (spec positions: #10, #5, #3, #1) --}}
                @if(count($positionPricing) > 0)
                <div class="mt-5">
                    <p class="text-sm font-semibold text-gray-600 mb-3">¿Hasta dónde quieres subir?</p>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 max-w-lg mx-auto">
                        @foreach($positionPricing as $pp)
                        <button @click="selectPosition({{ $pp['position'] }}, {{ $pp['amount'] }})"
                                class="relative rounded-xl border-2 p-3 text-center transition"
                                :class="selectedPosition === {{ $pp['position'] }}
                                    ? 'border-[#F53003] bg-[#F53003]/5'
                                    : 'border-gray-200 hover:border-gray-300'">
                            <div class="text-xs font-bold text-gray-400">#{{ $pp['position'] }}</div>
                            <div class="mt-1 text-lg font-black"
                                 :class="selectedPosition === {{ $pp['position'] }} ? 'text-[#F53003]' : 'text-[#1B1B18]'">
                                ${{ number_format($pp['amount']) }}
                            </div>
                        </button>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- CTA --}}
                <div class="mt-6 max-w-lg mx-auto">
                    <button @click="goToEntrar()"
                            class="w-full bg-[#1B1B18] hover:bg-[#2a2a26] text-white font-black text-lg py-5 rounded-2xl shadow-xl active:scale-[0.98] transition">
                        <span x-text="ctaLabel"></span>
                    </button>
                </div>

                <p class="mt-3 text-[11px] text-gray-400 max-w-md mx-auto leading-relaxed">
                    Pagas por visibilidad en Quién Manda. Tu posición puede cambiar si otro perfil te supera.
                </p>
            </div>
        </section>

        {{-- STATS PILLS --}}
        @if($stats['active_profiles'] > 0 || $stats['period_amount'] > 0)
        <div class="flex flex-wrap items-center justify-center gap-3 mb-8 text-sm font-medium text-gray-500">
            @if($stats['active_profiles'] > 0)
                <span class="flex items-center gap-1.5 bg-white border border-gray-200 rounded-full px-4 py-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                    {{ number_format($stats['active_profiles']) }} {{ Str::plural('perfil', $stats['active_profiles']) }} compitiendo
                </span>
            @endif
            @if($stats['period_amount'] > 0)
                <span class="bg-white border border-gray-200 rounded-full px-4 py-1.5">${{ number_format($stats['period_amount']) }} invertidos</span>
            @endif
            @if($stats['outbound_clicks'] > 0)
                <span class="bg-white border border-gray-200 rounded-full px-4 py-1.5">{{ number_format($stats['outbound_clicks']) }} clics enviados</span>
            @endif
        </div>
        @endif

        {{-- RANKING --}}
        <section>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-extrabold tracking-tight text-[#1B1B18]">Quién manda esta semana</h2>
                @if($period)
                    <span class="text-xs text-gray-400">{{ $periodRemainingText ?? '' }} <span class="tabular-nums" x-text="countdown"></span></span>
                @endif
            </div>

            @if (count($ranking) > 0)
                <section class="space-y-3 max-w-2xl mx-auto">
                    @foreach ($ranking as $i => $r)
                        @php
                            $vm = $viewModels[$i] ?? null;
                            $isLeader = $i === 0;
                        @endphp
                        @if ($i < 3)
                            <a href="{{ route('profile.show', $r['slug']) }}"
                               class="block rounded-2xl border p-4 transition {{ $isLeader ? 'bg-[#FFF8E1] border-[#F8B803] shadow' : 'bg-white border-gray-200 hover:border-gray-300' }}">
                                <div class="flex items-center gap-3">
                                    <span class="text-xl font-black {{ $isLeader ? 'text-[#F8B803]' : 'text-gray-400' }} w-8 text-center">{{ $vm->position() }}</span>
                                    @if($vm->verificationBadge())
                                        <span class="text-white rounded-md px-1.5 text-xs {{ $vm->verificationBadgeColor() }}">
                                            {{ $vm->verificationBadge() }}
                                        </span>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <div class="font-bold truncate">{{ $vm->displayName() }}</div>
                                        <div class="text-xs text-gray-500">👥 {{ $vm->supporterCountPluralized() }}</div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <div class="font-black {{ $isLeader ? 'text-[#F53003]' : '' }}">{{ $vm->totalRealClpFormatted() }}</div>
                                    </div>
                                </div>
                                @if ($paymentsEnabled)
                                    <div class="mt-3">
                                        <button @click.prevent="openCheckout(@js($r['profile_id']))"
                                            class="w-full {{ $isLeader ? 'bg-[#F53003] hover:bg-[#c22a02]' : 'bg-[#1B1B18] hover:bg-[#2a2a26]' }} text-white font-black py-3 rounded-xl active:scale-[0.98] transition text-sm">
                                            {{ $vm->ctaLabel() }}
                                        </button>
                                    </div>
                                @endif
                            </a>
                        @else
                            <a href="{{ route('profile.show', $r['slug']) }}"
                               class="flex items-center gap-3 bg-white rounded-xl border border-gray-200 px-4 py-3 hover:border-gray-300 transition">
                                <span class="font-black text-gray-400 w-6 text-center">{{ $vm->position() }}</span>
                                @if($vm->verificationBadge())
                                    <span class="rounded-full px-1.5 text-[10px] text-white {{ $vm->verificationBadgeColor() }}">
                                        {{ $vm->verificationBadge() }}
                                    </span>
                                @endif
                                <div class="flex-1 font-medium truncate">{{ $vm->displayName() }}</div>
                                @if ($paymentsEnabled)
                                    <button @click.prevent="openCheckout(@js($r['profile_id']))"
                                        class="text-xs font-bold text-[#F53003] shrink-0">{{ $vm->ctaLabel() }}</button>
                                @endif
                                <span class="font-bold tabular-nums text-sm shrink-0">{{ $vm->totalRealClpFormatted() }}</span>
                            </a>
                        @endif
                    @endforeach
                </section>
            @else
                <section class="text-center py-10 text-gray-500">
                    <div class="text-4xl mb-3">{{ $category->icon ?? '🗓️' }}</div>
                    <p class="font-bold text-lg text-gray-700">Sin actividad en {{ $category->displayName() }} todavía</p>
                    <p class="text-sm mt-1">¡Sé el primero en competir en esta categoría!</p>
                </section>
            @endif
        </section>

        {{-- RECENT ACTIVITY --}}
        @if(count($recentActivity) > 0)
        <section class="mt-10">
            <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-4">Actividad reciente</h3>
            <div class="space-y-2">
                @foreach($recentActivity as $event)
                    <a href="{{ route('profile.show', $event['profile_slug']) }}"
                       class="flex items-center gap-3 bg-white rounded-xl border border-gray-100 px-4 py-3 hover:border-gray-200 transition">
                        <span class="w-2 h-2 rounded-full bg-green-500 shrink-0"></span>
                        <div class="flex-1 min-w-0">
                            <span class="font-medium text-gray-800">{{ $event['profile_name'] }}</span>
                            <span class="text-gray-400">recibió</span>
                            <span class="font-bold text-[#F53003]">{{ money_clp($event['amount_clp']) }}</span>
                        </div>
                        @if($event['supporter_name'])
                            <span class="text-xs text-gray-400 shrink-0">de {{ $event['supporter_name'] }}</span>
                        @endif
                        <time class="text-[11px] text-gray-400 shrink-0" datetime="{{ $event['created_at'] }}">
                            {{ $event['created_at']->diffForHumans() }}
                        </time>
                    </a>
                @endforeach
            </div>
        </section>
        @endif

        {{-- SEO CONTENT + FAQ --}}
        @if($category->seo_content)
        <section class="mt-10">
            <div class="prose prose-sm max-w-2xl mx-auto text-gray-600">
                {!! $category->seo_content !!}
            </div>
        </section>
        @endif

        @php
            $faqItems = [
                ['q' => '¿Cómo funciona el ranking de ' . $category->displayName() . '?', 'a' => 'Quién Manda es un ranking semanal donde las personas invierten para impulsar a sus perfiles favoritos. El que más reciba, más sube. Al final de cada semana, el ranking se reinicia.'],
                ['q' => '¿Cuánto cuesta subir de posición?', 'a' => 'El monto lo decides tú. Mientras más inviertas, más avanzas. Los precios varían según cuánta competencia haya en la categoría.'],
                ['q' => '¿Qué pasa si alguien me supera?', 'a' => 'Puedes volver a invertir en cualquier momento para recuperar tu posición. No hay límite de veces que puedas impulsar a un perfil.'],
            ];
        @endphp

        @if(count($faqItems) > 0)
        <section class="mt-10">
            <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-4">Preguntas frecuentes</h3>
            <div class="space-y-3 max-w-2xl mx-auto">
                @foreach($faqItems as $faq)
                <details class="rounded-2xl border border-gray-200 bg-white overflow-hidden">
                    <summary class="px-6 py-4 font-bold cursor-pointer hover:bg-gray-50 transition">{{ $faq['q'] }}</summary>
                    <div class="px-6 pb-4 text-sm text-gray-700 leading-relaxed">{!! $faq['a'] !!}</div>
                </details>
                @endforeach
            </div>
        </section>
        @endif

        {{-- OTHER CATEGORIES --}}
        @if(count($otherCategories) > 0)
        <section class="mt-10">
            <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-4">Explorar otras categorías</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @foreach($otherCategories as $cat)
                <a href="{{ route('category.show', $cat->slug) }}"
                   class="flex flex-col items-center bg-white border border-gray-200 rounded-2xl p-4 hover:border-[#F53003] hover:bg-[#FFF8E1] transition text-center">
                    @if($cat->icon)
                        <span class="text-2xl mb-2">{{ $cat->icon }}</span>
                    @endif
                    <span class="font-bold text-sm text-gray-700">{{ $cat->displayName() }}</span>
                </a>
                @endforeach
            </div>
        </section>
        @endif

        {{-- LINK BACK --}}
        <div class="mt-8 text-center">
            <a href="{{ route('categories') }}" class="text-sm font-bold text-[#F53003] underline underline-offset-2">← Ver todas las categorías</a>
        </div>

    </main>

    @include('partials.site-footer')
    @include('partials.checkout-modal', compact('paymentsEnabled'))
</div>

{{-- SCHEMA.ORG --}}
@push('schema')
@php
    $breadcrumbSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Inicio', 'item' => 'https://quienmanda.cl/'],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Categorías', 'item' => 'https://quienmanda.cl/categorias'],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $category->displayName()],
        ],
    ];
@endphp
<script type="application/ld+json">{{ json_encode($breadcrumbSchema, JSON_UNESCAPED_UNICODE) }}</script>

@if(count($ranking) > 0)
@php
    $itemListSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'ItemList',
        'name' => 'Ranking ' . $category->displayName() . ' — Quién Manda',
        'description' => $seoDesc,
        'url' => $canonicalUrl,
        'numberOfItems' => count($ranking),
        'itemListElement' => array_map(fn ($r) => [
            '@type' => 'ListItem',
            'position' => $r['position'],
            'name' => $r['display_name'],
            'url' => 'https://quienmanda.cl/perfil/' . $r['slug'],
        ], array_slice($ranking, 0, 10)),
    ];
@endphp
<script type="application/ld+json">{{ json_encode($itemListSchema, JSON_UNESCAPED_UNICODE) }}</script>
@endif

@if(count($faqItems) > 0)
@php
    $faqSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => array_map(fn ($faq) => [
            '@type' => 'Question',
            'name' => $faq['q'],
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => strip_tags($faq['a']),
            ],
        ], $faqItems),
    ];
@endphp
<script type="application/ld+json">{{ json_encode($faqSchema, JSON_UNESCAPED_UNICODE) }}</script>
@endif
@endpush

@push('scripts')
<script>
    function categoryHero() {
        return {
            sourceInput: '',
            selectedPosition: {{ $positionPricing[0]['position'] ?? 1 }},
            selectedAmount: {{ $positionPricing[0]['amount'] ?? 1000 }},
            get ctaLabel() {
                if (this.selectedPosition === 1) return 'SUBIR AL #1 POR $' + Number(this.selectedAmount).toLocaleString('es-CL');
                return 'SUBIR AL #' + this.selectedPosition + ' POR $' + Number(this.selectedAmount).toLocaleString('es-CL');
            },
            selectPosition(pos, amount) {
                this.selectedPosition = pos;
                this.selectedAmount = amount;
            },
            goToEntrar() {
                const params = new URLSearchParams();
                if (this.sourceInput.trim()) params.set('source', this.sourceInput.trim());
                params.set('position', this.selectedPosition);
                params.set('category', @js($category->slug));
                window.location.href = '{{ route("entrar.index") }}' + '?' + params.toString();
            }
        };
    }

    function quienmanda(config) {
        return {
            ...config,
            showFaq: false, periodEnds: 0, countdown: '',
            modal: { open: false, step: 1, profile: null, amount: 0, quickAmounts: [], confirmed: false },
            receiptData: { periodCode: '', reference: '' },
            fm: { email: '', name: '', age18: false, termsAccepted: false },
            submitting: false, error: null,
            init() {
                const end = {{ $periodEndsAt ?? 'Date.now()' }};
                let rem = Math.max(0, end - Date.now());
                const tick = () => {
                    rem = Math.max(0, rem - 1000);
                    if (rem <= 0) { this.countdown = '¡RESET!'; return; }
                    const s = Math.floor(rem / 1000);
                    const d = Math.floor(s / 86400), h = Math.floor((s % 86400) / 3600),
                          m = Math.floor((s % 3600) / 60), sec = s % 60;
                    this.countdown = `${d}d ${String(h).padStart(2,'0')}h ${String(m).padStart(2,'0')}m ${String(sec).padStart(2,'0')}s`;
                };
                tick(); setInterval(tick, 1000);
            },
            openCheckout(id) {
                const vm = this.viewModels.find(v => v.profile_id === id) || {};
                const p = this.ranking.find(r => r.id === id) || {};
                const min = this.limits.min;
                const needTop = p.toTop || min;
                const suggested = (needTop > 0 && needTop <= this.limits.max) ? needTop : min;
                this.modal = { open: true, step: 1, confirmed: false,
                    profile: { id, slug: vm.slug || p.slug || '', name: vm.name || p.name || '', rank: vm.position || p.rank || null, amount: vm.total_clp || p.amount || 0, toTop: p.toTop || min },
                    amount: suggested, quickAmounts: [min, min * 2, min * 5] };
                this.fm = { email: '', name: '', age18: false, termsAccepted: false }; this.error = null;
            },
            setQuick(val) { this.modal.amount = val; },
            get suggestedAmount() {
                if (!this.modal.profile) return this.limits.min;
                const need = this.modal.profile.toTop;
                return (need > 0 && need <= this.limits.max) ? need : this.limits.min;
            },
            moneyDisplay(v) { return v == null ? '$0' : '$' + Number(v).toLocaleString('es-CL'); },
            goPay() {
                if (!this.modal.amount) this.modal.amount = this.suggestedAmount;
                if (this.modal.amount < this.limits.min) { this.error = `El mínimo es ${this.moneyDisplay(this.limits.min)}.`; return; }
                if (this.modal.amount > this.limits.max) { this.error = `Máximo ${this.moneyDisplay(this.limits.max)}.`; return; }
                this.modal.step = 2; this.error = null;
            },
            async submitPayment() {
                const email = this.fm.email.trim();
                if (!email || !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) { this.error = 'Ingresa un email válido.'; return; }
                if (!this.fm.age18) { this.error = 'Debes declarar que eres mayor de 18 años.'; return; }
                if (!this.fm.termsAccepted) { this.error = 'Debes aceptar los Términos y Condiciones.'; return; }
                this.submitting = true; this.error = null;
                try {
                    const res = await fetch('/api/pagos', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: JSON.stringify({ profile_id: this.modal.profile.id, target_position: this.modal.profile.rank || 1, supporter_name: this.fm.name.trim() || null, is_anonymous: true, age_declared_18: '1', terms_accepted: '1', checkout_token: this.checkoutToken, payer_email: email }),
                    });
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || 'Error al procesar.');
                    if (data.checkout_url) { window.location.href = data.checkout_url; return; }
                    this.receiptData = { periodCode: data.period_code || '', reference: data.external_reference || '' }; this.modal.step = 3;
                } catch (err) { this.error = err.message; }
                finally { this.submitting = false; }
            },
        };
    }
</script>
@endpush
@endsection
