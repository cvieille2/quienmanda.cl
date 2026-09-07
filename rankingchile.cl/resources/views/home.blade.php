@extends('layouts.app')

@section('title', ($periodHeadline ?? '¿Quién manda?') . ' | Quién Manda en Chile')

@push('meta')
    @if ($leader)
        <meta property="og:title" content="{{ mb_strtoupper($periodHeadline ?? '¿QUIÉN MANDA?') }}" />
        <meta property="og:description" content="El ranking que se decide con plata. 👑 {{ $leader['display_name'] }} lidera con {{ money_clp($leader['total_real_clp']) }}. ¡Impúlsalo para superarlo!" />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="{{ url('/') }}" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content="{{ mb_strtoupper($periodHeadline ?? '¿QUIÉN MANDA?') }}" />
        <meta name="twitter:description" content="El ranking que se decide con plata. {{ $leader['display_name'] }} manda con {{ money_clp($leader['total_real_clp']) }}. Súperalo con inversión promocional." />
    @endif
@endpush

@section('body')
<div class="min-h-screen" x-data="quienmanda({
    paymentsEnabled: {{ $paymentsEnabled ? 'true' : 'false' }},
    checkoutToken: '{{ $checkoutToken }}',
    periodLabel: @js($periodLabel),
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

    @php $activePage = 'home'; @endphp
    @include('partials.site-header')

    {{-- MOBILE: Conversion Hero first --}}
    <div class="md:hidden">
        @include('partials.conversion-hero', ['positionPricing' => $positionPricing, 'heroProjection' => $heroProjection, 'heroState' => $heroState, 'limits' => $limits, 'periodLabel' => $periodLabel])
    </div>

    <main class="mx-auto max-w-6xl px-4 pb-28">

        <div class="lg:grid lg:grid-cols-[1fr_400px] lg:gap-8">

            {{-- LEFT COLUMN: Ranking --}}
            <div class="rounded-3xl bg-[#F7F9FC] px-4 py-7 md:px-6 md:py-8">
                <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between mb-5">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.24em] text-gray-400">Ranking público</p>
                        <h2 class="text-2xl font-extrabold tracking-tight text-[#07182D]">{{ $periodHeadline }}</h2>
                        <p class="mt-1 text-sm text-gray-500">El ranking cambia según el apoyo y la actividad de cada perfil.</p>
                    </div>
                    <div class="text-sm font-semibold text-gray-500">
                        @if ($hasCompetitiveActivity)
                            {{ number_format($rankingTotal) }} perfiles
                        @else
                            Nadie compite todavía
                        @endif
                    </div>
                </div>

                @include('partials.ranking-filters', [
                    'categoryFilters' => $categoryFilters,
                    'periodFilters' => $periodFilters,
                ])

                @if (count($ranking) > 0)
                    <div class="mt-6 hidden md:block overflow-hidden rounded-2xl border border-gray-200 bg-white">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50 text-left text-xs font-bold uppercase tracking-[0.2em] text-gray-500">
                                <tr>
                                    <th class="px-4 py-3 w-16">#</th>
                                    <th class="px-4 py-3">Perfil</th>
                                    <th class="px-4 py-3 w-24 text-center">Vistas</th>
                                    <th class="px-4 py-3 w-24 text-center">Clics</th>
                                    <th class="px-4 py-3 w-28 text-right">Apoyo</th>
                                    <th class="px-4 py-3 w-56 text-right">Tu próximo movimiento</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($viewModels as $vm)
                                    <tr class="transition {{ $vm->isLeader() ? 'bg-[#FFF8D9]' : 'bg-white hover:bg-gray-50' }}">
                                        <td class="px-4 py-4 align-middle">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-full {{ $vm->isLeader() ? 'bg-[#FFC21C] text-[#07182D]' : 'bg-[#EEF2F7] text-[#07182D]' }} font-black text-lg">
                                                {{ $vm->position() }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 align-middle">
                                            <a href="{{ route('profile.show', $vm->slug()) }}" class="flex items-center gap-3 group">
                                                <div class="h-12 w-12 overflow-hidden rounded-full bg-[#EEF2F7] flex items-center justify-center text-sm font-black text-[#07182D] shrink-0">
                                                    @if ($vm->profileImageUrl())
                                                        <img src="{{ $vm->profileImageUrl() }}" alt="{{ $vm->displayName() }}" class="h-full w-full object-cover">
                                                    @else
                                                        {{ mb_strtoupper(mb_substr($vm->displayName(), 0, 1)) }}
                                                    @endif
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-black text-[15px] text-[#172033] group-hover:text-[#07182D]">{{ $vm->displayName() }}</span>
                                                        @if ($vm->isLeader())
                                                            <span class="rounded-full bg-[#07182D] px-2 py-0.5 text-[10px] font-bold uppercase tracking-[0.18em] text-white">Corona</span>
                                                        @endif
                                                    </div>
                                                    <div class="mt-0.5 text-xs text-[#788597]">
                                                        @if ($vm->category())
                                                            {{ $vm->category() }} ·
                                                        @endif
                                                        {{ number_format($vm->profileViewsCount()) }} vistas · {{ number_format($vm->destinationClicksCount()) }} clics
                                                    </div>
                                                </div>
                                            </a>
                                        </td>
                                        <td class="px-4 py-4 text-center text-sm font-semibold text-[#172033] align-middle">{{ number_format($vm->profileViewsCount()) }}</td>
                                        <td class="px-4 py-4 text-center text-sm font-semibold text-[#172033] align-middle">{{ number_format($vm->destinationClicksCount()) }}</td>
                                        <td class="px-4 py-4 text-right align-middle">
                                            <div class="text-lg font-black text-[#07182D]">{{ $vm->totalRealClpFormatted() }}</div>
                                            <div class="text-xs text-[#788597]">Apoyo total</div>
                                        </td>
                                        <td class="px-4 py-4 text-right align-middle">
                                            @if ($paymentsEnabled)
                                                <button @click.prevent="openCheckout(@js($vm->profileId()))"
                                                    class="inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-black transition {{ $vm->isLeader() ? 'bg-[#F5303E] text-white hover:bg-[#d82a35]' : 'bg-[#07182D] text-white hover:bg-[#0d2748]' }}">
                                                    {{ $vm->ctaLabel() }}
                                                </button>
                                            @else
                                                <a href="{{ route('profile.show', $vm->slug()) }}"
                                                   class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-black text-[#07182D] hover:border-gray-300 hover:bg-gray-50 transition">
                                                    Ver perfil
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 space-y-3 md:hidden">
                        @foreach ($viewModels as $vm)
                            <article class="rounded-[14px] border border-[#E4EAF1] bg-white p-4 {{ $vm->isLeader() ? 'bg-[#FFF9E2]' : '' }}">
                                <div class="flex items-start gap-3">
                                    <div class="flex h-11 w-11 items-center justify-center rounded-full {{ $vm->isLeader() ? 'bg-[#FFC21C] text-[#07182D]' : 'bg-[#EEF2F7] text-[#07182D]' }} font-black text-lg shrink-0">
                                        {{ $vm->position() }}
                                    </div>
                                    <a href="{{ route('profile.show', $vm->slug()) }}" class="flex-1 min-w-0 flex items-center gap-3">
                                        <div class="h-12 w-12 overflow-hidden rounded-full bg-[#EEF2F7] flex items-center justify-center text-sm font-black text-[#07182D] shrink-0">
                                            @if ($vm->profileImageUrl())
                                                <img src="{{ $vm->profileImageUrl() }}" alt="{{ $vm->displayName() }}" class="h-full w-full object-cover">
                                            @else
                                                {{ mb_strtoupper(mb_substr($vm->displayName(), 0, 1)) }}
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="truncate font-black text-[15px] text-[#172033]">{{ $vm->displayName() }}</span>
                                                @if ($vm->isLeader())
                                                    <span class="rounded-full bg-[#07182D] px-2 py-0.5 text-[10px] font-bold uppercase tracking-[0.18em] text-white">Corona</span>
                                                @endif
                                            </div>
                                            <div class="mt-0.5 text-xs text-[#788597]">
                                                @if ($vm->category())
                                                    {{ $vm->category() }} ·
                                                @endif
                                                {{ number_format($vm->profileViewsCount()) }} vistas · {{ number_format($vm->destinationClicksCount()) }} clics
                                            </div>
                                        </div>
                                    </a>
                                </div>

                                <div class="mt-3 flex items-center justify-between gap-3">
                                    <div>
                                        <div class="text-lg font-black text-[#07182D]">{{ $vm->totalRealClpFormatted() }}</div>
                                        <div class="text-xs text-[#788597]">Apoyo total</div>
                                    </div>
                                    @if ($paymentsEnabled)
                                        <button @click.prevent="openCheckout(@js($vm->profileId()))"
                                            class="rounded-xl px-4 py-2.5 text-sm font-black transition {{ $vm->isLeader() ? 'bg-[#F5303E] text-white' : 'bg-[#07182D] text-white' }}">
                                            {{ $vm->ctaLabel() }}
                                        </button>
                                    @else
                                        <a href="{{ route('profile.show', $vm->slug()) }}"
                                           class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-black text-[#07182D]">
                                            Ver perfil
                                        </a>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>

                    @if($hasMoreProfiles && $loadMoreUrl)
                        <div class="mt-5 text-center">
                            <a href="{{ $loadMoreUrl }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-5 py-3 text-sm font-black text-[#07182D] hover:border-gray-300 hover:bg-gray-50 transition">
                                Ver más perfiles
                            </a>
                        </div>
                    @endif
                @else
                    @if ($currentCategory)
                        <section class="mt-6 rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-10 text-center text-gray-500">
                            <div class="text-4xl mb-3">🗂️</div>
                            <p class="font-bold text-lg text-gray-700">Todavía no hay perfiles en esta categoría</p>
                            <p class="text-sm mt-1">Puedes explorar otras categorías o volver al ranking general.</p>
                            <div class="mt-4">
                                <a href="{{ route('home') }}" class="inline-flex items-center justify-center rounded-xl bg-[#07182D] px-5 py-3 text-sm font-black text-white">Ver todos</a>
                            </div>
                        </section>
                    @else
                        <section class="mt-6 rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-10 text-center text-gray-500">
                            <div class="text-4xl mb-3">👑</div>
                            <p class="font-bold text-lg text-gray-700">EL #1 ESTÁ LIBRE</p>
                            <p class="text-sm mt-1">Ocúpalo antes que otro.</p>
                        </section>
                    @endif
                @endif
            </div>

            {{-- RIGHT COLUMN: Conversion Hero (desktop) --}}
            <div class="hidden lg:block">
                <div class="sticky top-20">
                    @include('partials.conversion-hero', ['positionPricing' => $positionPricing, 'heroProjection' => $heroProjection, 'heroState' => $heroState, 'limits' => $limits, 'periodLabel' => $periodLabel])

                    {{-- How it works --}}
                    <div id="como-funciona" class="mt-6 bg-[#1B1B18] text-white rounded-2xl p-5">
                        <h3 class="font-extrabold text-base mb-3">⚖️ Así funciona</h3>
                        <ol class="space-y-2 text-sm">
                            <li class="flex gap-2"><span class="font-black text-[#F8B803]">1.</span> Elige a tu favorito</li>
                            <li class="flex gap-2"><span class="font-black text-[#F8B803]">2.</span> Entra y paga desde {{ money_clp($limits['min']) }}</li>
                            <li class="flex gap-2"><span class="font-black text-[#F8B803]">3.</span> Sube de puesto en el ranking</li>
                            <li class="flex gap-2"><span class="font-black text-[#F8B803]">4.</span> ¿Le quitas la corona? Compártelo 👑</li>
                        </ol>
                        <div class="mt-3 pt-3 border-t border-white/10 text-xs text-white/60 space-y-1">
                            <p>· Cada CLP cuenta</p>
                        </div>
                    </div>

                    {{-- Rules link --}}
                    <div class="mt-4 text-center">
                        <a href="{{ route('rules') }}" class="text-sm font-bold text-[#F53003] underline underline-offset-2 hover:text-[#c22a02] transition">
                            📋 Ver reglas completas →
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- MOBILE: How it works (below fold) --}}
        <section id="como-funciona" class="mt-10 md:hidden bg-[#1B1B18] text-white rounded-3xl p-6">
            <h3 class="text-xl font-extrabold mb-4">⚖️ Así funciona</h3>
            <ol class="space-y-3 text-sm">
                <li class="flex gap-3"><span class="font-black text-[#F8B803]">1.</span> Elige a tu favorito</li>
                <li class="flex gap-3"><span class="font-black text-[#F8B803]">2.</span> Entra y paga desde {{ money_clp($limits['min']) }}</li>
                <li class="flex gap-3"><span class="font-black text-[#F8B803]">3.</span> Sube de puesto en el ranking</li>
                <li class="flex gap-3"><span class="font-black text-[#F8B803]">4.</span> ¿Le quitas la corona? Compártelo 👑</li>
            </ol>
            <button @click="showFaq = !showFaq" class="mt-4 w-full text-[#F8B803] font-bold text-sm py-2">
                ¿Cómo funciona? ¡más detalle! ▾
            </button>
        </section>

        {{-- FAQ --}}
        <section x-show="showFaq" x-cloak class="mt-4 bg-white border border-gray-200 rounded-3xl p-6 space-y-4 text-sm">
            <h3 class="font-extrabold text-base">Preguntas frecuentes</h3>
            <details>
                <summary class="font-bold cursor-pointer">¿Esto es real o está manipulado?</summary>
                <p class="mt-1 text-gray-600">Los montos son pagos reales verificados. Cada impulso suma al total público.</p>
            </details>
            <details>
                <summary class="font-bold cursor-pointer">¿Mi artista recibe este dinero?</summary>
                <p class="mt-1 text-gray-600">Tu impulso compra visibilidad dentro de este ranking. Plataforma independiente.</p>
            </details>
            <details>
                <summary class="font-bold cursor-pointer">¿Qué gano yo?</summary>
                <p class="mt-1 text-gray-600">Tu participación hace subir a quien impulsas. No hay premio monetario.</p>
            </details>
            <details>
                <summary class="font-bold cursor-pointer">¿Y si pago y alguien me supera?</summary>
                <p class="mt-1 text-gray-600">Ninguna posición futura queda garantizada. Tu impulso siempre suma.</p>
            </details>
        </section>

        {{-- MOBILE: Rules link --}}
        <section class="mt-6 text-center md:hidden">
            <a href="{{ route('rules') }}" class="inline-block text-sm font-bold text-[#F53003] underline underline-offset-2 hover:text-[#c22a02] transition">
                📋 Ver reglas completas →
            </a>
        </section>

        @include('partials.site-footer')
    </main>

    {{-- MODAL DE PAGO --}}
    @include('partials.checkout-modal', compact('paymentsEnabled'))

</div>

@push('scripts')
<script>
    function quienmanda(config) {
        return {
            ...config,
            showFaq: false,
            modal: { open: false, step: 1, profile: null, amount: 0, quickAmounts: [], confirmed: false },
            receiptData: { periodCode: '', reference: '' },
            fm: { email: '', name: '', age18: false, termsAccepted: false },
            submitting: false,
            error: null,
            init() {},
            openCheckout(id) {
                const p = this.ranking.find(r => r.id === id) || {};
                const vm = this.viewModels.find(v => v.profile_id === id) || {};
                const min = this.limits.min;
                const needTop = p.toTop || min;
                const suggested = (needTop > 0 && needTop <= this.limits.max) ? needTop : min;
                this.modal = { open: true, step: 1, confirmed: false,
                    profile: {
                        id, slug: p.slug || '', name: p.name || '', rank: p.rank || null,
                        amount: p.amount || 0, toTop: p.toTop || min,
                        projected_rank: vm.cta_target || null,
                        projected_required: vm.cta_required || 0,
                        projected_required_formatted: vm.cta_required_formatted || '$0',
                        projected_to_top: vm.to_number_one_clp || min,
                        has_projection: vm.has_projection || false,
                    },
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
                if (this.modal.amount > this.limits.max) { this.error = `Máximo ${this.moneyDisplay(this.limits.max)} por impulso. Haz varios.`; return; }
                this.modal.step = 2; this.error = null;
            },
            async submitPayment() {
                const email = this.fm.email.trim();
                if (!email || !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) { this.error = 'Ingresa un email válido.'; return; }
                if (!this.fm.age18) { this.error = 'Debes declarar que eres mayor de 18 años.'; return; }
                if (!this.fm.termsAccepted) { this.error = 'Debes aceptar los Términos y Condiciones.'; return; }
                this.submitting = true; this.error = null; this.modal.confirmed = false;
                try {
                    const res = await fetch('/api/pagos', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: JSON.stringify({
                            profile_id: this.modal.profile.id, target_position: this.modal.profile.rank || 1,
                            supporter_name: this.fm.name.trim() || null, is_anonymous: true,
                            age_declared_18: '1', terms_accepted: '1', checkout_token: this.checkoutToken, payer_email: email,
                        }),
                    });
                    const data = await res.json();
                    if (!res.ok) { throw new Error(data.error === 'checkout_disabled' ? 'Pagos desactivados por ahora.' : (data.message || 'No pudimos procesar tu pago.')); }
                    if (data.checkout_url) { window.location.href = data.checkout_url; return; }
                    this.receiptData = { periodCode: data.period_code || '', reference: data.external_reference || data.receipt_id || '' }; this.modal.step = 3;
                } catch (err) { this.error = err.message; }
                finally { this.submitting = false; }
            },
        };
    }
</script>
@endpush
@endsection
