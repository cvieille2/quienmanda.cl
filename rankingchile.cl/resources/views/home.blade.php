@extends('layouts.app')

@section('title', '¿Quién Manda en Chile esta semana?')

@push('meta')
    @if ($period && $leader)
        <meta property="og:title" content="¿QUIÉN MANDA EN CHILE ESTA SEMANA?" />
        <meta property="og:description" content="El ranking que se decide con plata. 👑 {{ $leader['display_name'] }} lidera con {{ money_clp($leader['total_real_clp']) }}. ¡Impúlsalo para superarlo!" />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="{{ url('/') }}" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content="¿QUIÉN MANDA EN CHILE ESTA SEMANA?" />
        <meta name="twitter:description" content="El ranking que se decide con plata. {{ $leader['display_name'] }} manda con {{ money_clp($leader['total_real_clp']) }}. Súperalo con inversión promocional." />
    @endif
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
    ], $ranking)) }}
})">

    @php $activePage = 'home'; @endphp
    @include('partials.site-header')

    {{-- CATEGORY NAV --}}
    @include('partials.category-nav', ['categories' => $categories, 'activeCategorySlug' => $activeCategorySlug])

    {{-- MOBILE: Conversion Hero first --}}
    <div class="md:hidden">
        @include('partials.conversion-hero', ['positionPricing' => $positionPricing])
    </div>

    <main class="mx-auto max-w-6xl px-4 pb-28">

        {{-- DESKTOP: Period label --}}
        <div class="hidden md:flex items-center justify-between pt-6 pb-2">
            <div>
                <h2 class="text-2xl font-extrabold tracking-tight text-[#1B1B18]">Ranking semanal</h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    @if($period)
                        Ciclo {{ $period->code }} · {{ $period->starts_at->timezone('America/Santiago')->format('d/m') }} – {{ $period->ends_at->timezone('America/Santiago')->format('d/m') }}
                        <span class="text-gray-400">·</span>
                        <span class="tabular-nums" x-text="countdown"></span>
                    @else
                        Sin período activo
                    @endif
                </p>
            </div>
        </div>

        <div class="lg:grid lg:grid-cols-[1fr_400px] lg:gap-8">

            {{-- LEFT COLUMN: Ranking --}}
            <div>
                @if (count($ranking) > 0)
                    <section class="space-y-3">
                        @foreach ($ranking as $i => $r)
                            @php
                                $isLeader = $i === 0;
                                $isTop3 = $i < 3;
                            @endphp
                            @if ($isTop3)
                                <a href="{{ route('profile.show', $r['slug']) }}"
                                   class="block rounded-2xl border p-4 transition {{ $isLeader ? 'bg-[#FFF8E1] border-[#F8B803] shadow' : 'bg-white border-gray-200 hover:border-gray-300' }}">
                                    <div class="flex items-center gap-3">
                                        <span class="text-xl font-black {{ $isLeader ? 'text-[#F8B803]' : 'text-gray-400' }} w-8 text-center">{{ $r['position'] }}</span>
                                        <span class="text-white rounded-md px-1.5 text-xs {{ $r['verification_status'] === 'verified' ? 'bg-[#0ea5e9]' : ($r['verification_status'] === 'pending' ? 'bg-orange-500' : 'bg-gray-400') }}">
                                            {{ $r['verification_status'] === 'verified' ? '✓' : ($r['verification_status'] === 'pending' ? '…' : '') }}
                                        </span>
                                        <div class="flex-1 min-w-0">
                                            <div class="font-bold truncate">{{ $r['display_name'] }}
                                                @if ($r['is_community_created'])<span class="text-xs text-gray-400">👥</span>@endif
                                            </div>
                                            <div class="text-xs text-gray-500">👥 {{ number_format($r['supporter_count']) }} personas impulsan</div>
                                        </div>
                                        <div class="text-right shrink-0">
                                            <div class="font-black {{ $isLeader ? 'text-[#F53003]' : '' }}">{{ money_clp($r['total_real_clp']) }}</div>
                                        </div>
                                    </div>
                                    <div class="mt-3 flex gap-2">
                                        @if ($isLeader && $paymentsEnabled)
                                            <button @click.prevent="openCheckout(@js($r['profile_id']))"
                                                class="flex-1 bg-[#F53003] hover:bg-[#c22a02] text-white font-black py-3 rounded-xl active:scale-[0.98] transition text-sm">
                                                🛡️ DEFENDER LA CORONA
                                            </button>
                                        @elseif (! $isLeader && $paymentsEnabled)
                                            <button @click.prevent="openCheckout(@js($r['profile_id']))"
                                                class="flex-1 bg-[#1B1B18] hover:bg-[#2a2a26] text-white font-bold py-3 rounded-xl active:scale-[0.98] transition text-sm">
                                                @if($r['overtake_above_clp'] > 0)
                                                    CON ${{ number_format($r['overtake_above_clp']) }} PASA AL #{{ $r['position'] - 1 }}
                                                @else
                                                    IMPULSAR
                                                @endif
                                            </button>
                                        @endif
                                        <a href="{{ route('profile.show', $r['slug']) }}" class="px-4 py-3 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition">Ver</a>
                                    </div>
                                </a>
                            @else
                                <a href="{{ route('profile.show', $r['slug']) }}"
                                   class="flex items-center gap-3 bg-white rounded-xl border border-gray-200 px-4 py-3 hover:border-gray-300 transition">
                                    <span class="font-black text-gray-400 w-6 text-center">{{ $r['position'] }}</span>
                                    <span class="rounded-full px-1.5 text-[10px] text-white {{ $r['verification_status'] === 'verified' ? 'bg-[#0ea5e9]' : ($r['verification_status'] === 'pending' ? 'bg-orange-500' : 'bg-gray-300') }}">
                                        {{ $r['verification_status'] === 'verified' ? '✓' : ($r['verification_status'] === 'pending' ? '…' : '') }}
                                    </span>
                                    <div class="flex-1 font-medium truncate">{{ $r['display_name'] }}</div>
                                    @if ($paymentsEnabled)
                                        <button @click.prevent="openCheckout(@js($r['profile_id']))"
                                            class="text-xs font-bold text-[#F53003] shrink-0">
                                            @if($r['overtake_above_clp'] > 0)
                                                CON ${{ number_format($r['overtake_above_clp']) }} → #{{ $r['position'] - 1 }}
                                            @else
                                                IMPULSAR
                                            @endif
                                        </button>
                                    @endif
                                    <span class="font-bold tabular-nums text-sm shrink-0">{{ money_clp($r['total_real_clp']) }}</span>
                                </a>
                            @endif
                        @endforeach
                    </section>
                @else
                    <section class="text-center py-10 text-gray-500">
                        <div class="text-4xl mb-3">🗓️</div>
                        <p class="font-bold text-lg text-gray-700">La semana acaba de empezar</p>
                        <p class="text-sm mt-1">El primer movimiento puede cambiar todo el ranking.</p>
                    </section>
                @endif
            </div>

            {{-- RIGHT COLUMN: Conversion Hero (desktop) --}}
            <div class="hidden lg:block">
                <div class="sticky top-20">
                    @include('partials.conversion-hero', ['positionPricing' => $positionPricing])

                    {{-- How it works --}}
                    <div class="mt-6 bg-[#1B1B18] text-white rounded-2xl p-5">
                        <h3 class="font-extrabold text-base mb-3">⚖️ Así funciona</h3>
                        <ol class="space-y-2 text-sm">
                            <li class="flex gap-2"><span class="font-black text-[#F8B803]">1.</span> Elige a tu favorito</li>
                            <li class="flex gap-2"><span class="font-black text-[#F8B803]">2.</span> Entra y paga desde $1.000</li>
                            <li class="flex gap-2"><span class="font-black text-[#F8B803]">3.</span> Sube de puesto en el ranking</li>
                            <li class="flex gap-2"><span class="font-black text-[#F8B803]">4.</span> ¿Le quitas la corona? Compártelo 👑</li>
                        </ol>
                        <div class="mt-3 pt-3 border-t border-white/10 text-xs text-white/60 space-y-1">
                            <p>· Cierre semanal: dom 23:59</p>
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
        <section class="mt-10 md:hidden bg-[#1B1B18] text-white rounded-3xl p-6">
            <h3 class="text-xl font-extrabold mb-4">⚖️ Así funciona</h3>
            <ol class="space-y-3 text-sm">
                <li class="flex gap-3"><span class="font-black text-[#F8B803]">1.</span> Elige a tu favorito</li>
                <li class="flex gap-3"><span class="font-black text-[#F8B803]">2.</span> Entra y paga desde $1.000</li>
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
            periodEnds: 0,
            countdown: '',
            modal: { open: false, step: 1, profile: null, amount: 0, quickAmounts: [1000, 2000, 5000], confirmed: false },
            receiptData: { periodCode: '', reference: '' },
            fm: { email: '', name: '', age18: false },
            submitting: false,
            error: null,
            init() {
                const end = {{ $period ? $period->ends_at->timestamp * 1000 : 'Date.now()' }};
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
                const p = this.ranking.find(r => r.id === id) || {};
                const min = this.limits.min;
                const needTop = p.toTop || min;
                const suggested = (needTop > 0 && needTop <= this.limits.max) ? needTop : min;
                this.modal = { open: true, step: 1, confirmed: false,
                    profile: { id, slug: p.slug || '', name: p.name || '', rank: p.rank || null, amount: p.amount || 0, toTop: p.toTop || min },
                    amount: suggested, quickAmounts: [min, min * 2, min * 5] };
                this.fm = { email: '', name: '', age18: false }; this.error = null;
            },
            setQuick(val) { this.modal.amount = val; },
            get suggestedAmount() {
                if (!this.modal.profile) return this.limits.min;
                const need = this.modal.profile.toTop;
                return (need > 0 && need <= this.limits.max) ? need : this.limits.min;
            },
            moneyDisplay(v) { return v == null ? '$0' : '$' + Number(v).toLocaleString('es-CL'); },
            get projectedToTop() {
                if (!this.modal.profile) return this.limits.min;
                const need = this.modal.profile.toTop;
                if (need > 0 && need <= this.limits.max) return need;
                return this.limits.max;
            },
            get projection() {
                const p = this.modal.profile;
                if (!p || !this.modal.amount) return { rank: p ? p.rank : null, tied: false };
                const target = p.amount + this.modal.amount;
                let race = 0;
                for (let i = 0; i < this.ranking.length; i++) {
                    if (this.ranking[i].amount >= target) race++;
                }
                const rank = race + 1;
                const tied = race > 0 && this.ranking[race - 1].amount === target;
                return { rank, tied };
            },
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
                this.submitting = true; this.error = null; this.modal.confirmed = false;
                try {
                    const res = await fetch('/api/pagos', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: JSON.stringify({
                            profile_id: this.modal.profile.id, amount_clp: this.modal.amount,
                            supporter_name: this.fm.name.trim() || null, is_anonymous: true,
                            age_declared_18: '1', checkout_token: this.checkoutToken, payer_email: email,
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
