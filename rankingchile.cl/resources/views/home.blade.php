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
        'id' => $r['profile_id'],
        'slug' => $r['slug'],
        'name' => $r['display_name'],
        'amount' => $r['total_real_clp'],
        'rank' => $r['position'],
        'behind' => $r['behind_clp'] ?? 0,
        'overtake' => $r['overtake_above_clp'] ?? 0,
        'toTop' => $r['to_number_one_clp'] ?? 0,
        'category' => $r['category'],
    ], $ranking)) }}
)">

    @php $activePage = 'home'; @endphp
    @section('headerExtra')
        <div class="flex items-center gap-2" x-show="periodEnds > 0">
            <span class="text-[#F8B803] font-mono font-bold tabular-nums" x-text="countdown"></span>
            <span class="text-white/60 text-xs leading-tight">para el<br>RESET</span>
        </div>
    @endsection
    @include('partials.site-header')

    <main class="mx-auto max-w-2xl px-4 pb-28">

        {{-- HERO: conflicto + líder --}}
        @if ($leader)
            <section class="pt-6">
                <p class="text-xs uppercase tracking-widest text-gray-500 mb-2">👑 Esta semana manda</p>
                <div class="bg-gradient-to-br from-[#1B1B18] to-[#3a2a00] text-white rounded-3xl p-6 text-center relative overflow-hidden">
                    <div class="text-6xl mb-2">👑</div>
                    <h1 class="text-3xl font-extrabold tracking-tight">{{ $leader['display_name'] }}</h1>
                    <div class="mt-2 text-[#F8B803] text-3xl font-black">{{ money_clp($leader['total_real_clp']) }}</div>
                    <div class="mt-1 text-white/60 text-sm">esta semana</div>
                    <div class="mt-1 text-white/60 text-xs">
                        👥 {{ number_format($leader['supporter_count']) }} personas impulsan
                    </div>
                    @if ($paymentsEnabled)
                        <button
                            @click="openCheckout(@js($leader['profile_id']))"
                            class="mt-5 w-full bg-[#F53003] hover:bg-[#c22a02] text-white font-black text-lg py-4 rounded-2xl tracking-wide shadow-lg active:scale-[0.98] transition">
                            🛡️ DEFENDER LA CORONA
                        </button>
                    @else
                        <div class="mt-5 w-full bg-white/10 text-white/70 font-semibold py-4 rounded-2xl">💤 Pagos desactivados</div>
                    @endif
                </div>
            </section>
        @else
            <section class="pt-6 text-center">
                <div class="text-5xl mb-3">🗓️</div>
                <h1 class="text-2xl font-extrabold">El ranking parte esta semana</h1>
                <p class="text-gray-600 mt-2">Nuevo ciclo comienza el lunes.</p>
            </section>
        @endif

        <h2 class="mt-8 text-center text-2xl font-extrabold tracking-tight">¿QUIÉN MANDA EN CHILE<br>ESTA SEMANA?</h2>
        <p class="text-center text-gray-600 mt-1">El ranking que se decide con plata.</p>

        {{-- TOP RANKING --}}
        @if (count($ranking) > 0)
            <section class="mt-8 space-y-3" x-show="true">
                @foreach ($ranking as $i => $r)
                    @php $isLeader = $i === 0; @endphp
                    @if ($i < 3)
                        <a href="{{ route('profile.show', $r['slug']) }}"
                           class="block rounded-2xl border p-4 transition {{ $isLeader ? 'bg-[#FFF8E1] border-[#F8B803] shadow' : 'bg-white border-gray-200' }}">
                            <div class="flex items-center gap-3">
                                <span class="text-xl font-black {{ $isLeader ? 'text-[#F8B803]' : 'text-gray-400' }} w-8">{{ $r['position'] }}</span>
                                <span class="text-white rounded-md px-1.5 {{ $r['verification_status'] === 'verified' ? 'bg-[#0ea5e9]' : ($r['verification_status'] === 'pending' ? 'bg-orange-500' : 'bg-gray-400') }}">
                                    {{ $r['verification_status'] === 'verified' ? '✓' : ($r['verification_status'] === 'pending' ? '…' : '') }}
                                </span>
                                <div class="flex-1">
                                    <div class="font-bold">{{ $r['display_name'] }}
                                        @if ($r['is_community_created'])<span class="text-xs text-gray-400">👥</span>@endif
                                    </div>
                                     <div class="text-xs text-gray-500">👥 {{ number_format($r['supporter_count']) }} personas impulsan</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-black {{ $isLeader ? 'text-[#F53003]' : '' }}">{{ money_clp($r['total_real_clp']) }}</div>
                                    @if (! $isLeader && $r['overtake_above_clp'] > 0 && $paymentsEnabled)
                                        <button @click.prevent="openCheckout(@js($r['profile_id']))"
                                            class="text-[#F53003] text-xs font-bold underline underline-offset-2">🔥 faltan {{ money_clp($r['overtake_above_clp']) }}</button>
                                    @endif
                                </div>
                            </div>
                            @if ($isLeader && $paymentsEnabled)
                                <div class="mt-3">
                                    <button
                                        @click.prevent="openCheckout(@js($r['profile_id']))"
                                        class="w-full bg-[#F53003] hover:bg-[#c22a02] text-white font-black py-3 rounded-xl active:scale-[0.98] transition">
                                        🛡️ DEFENDER LA CORONA
                                    </button>
                                </div>
                            @endif
                        </a>
                    @else
                        <a href="{{ route('profile.show', $r['slug']) }}"
                           class="flex items-center gap-3 bg-white rounded-xl border border-gray-200 px-4 py-3">
                            <span class="font-black text-gray-400 w-6">{{ $r['position'] }}</span>
                            <span class="rounded-full px-1.5 text-[10px] text-white {{ $r['verification_status'] === 'verified' ? 'bg-[#0ea5e9]' : ($r['verification_status'] === 'pending' ? 'bg-orange-500' : 'bg-gray-300') }}">
                                {{ $r['verification_status'] === 'verified' ? '✓' : ($r['verification_status'] === 'pending' ? '…' : '') }}
                            </span>
                            <div class="flex-1 font-medium">{{ $r['display_name'] }}</div>
                            @if ($paymentsEnabled)
                                <button @click.prevent="openCheckout(@js($r['profile_id']))"
                                        class="text-sm font-bold text-[#F53003]">IMPULSAR</button>
                            @endif
                            <span class="font-bold tabular-nums">{{ money_clp($r['total_real_clp']) }}</span>
                        </a>
                    @endif
                @endforeach
            </section>
        @else
            <section class="mt-8 text-center py-10 text-gray-500">
                <p>Aún no hay impulsos esta semana. ¡Sé el primero!</p>
            </section>
        @endif

        {{-- CÓMO FUNCIONA (REQUERIDO, bajo el fold) --}}
        <section class="mt-10 bg-[#1B1B18] text-white rounded-3xl p-6">
            <h3 class="text-xl font-extrabold mb-4">⚖️ Así funciona / Reglas simples</h3>
            <ol class="space-y-3 text-sm">
                <li class="flex gap-3"><span class="font-black text-[#F8B803]">1.</span> Elige a tu favorito</li>
                <li class="flex gap-3"><span class="font-black text-[#F8B803]">2.</span> Paga $1.000+ e impúlsalo</li>
                <li class="flex gap-3"><span class="font-black text-[#F8B803]">3.</span> Se actualiza el ranking en vivo</li>
                <li class="flex gap-3"><span class="font-black text-[#F8B803]">4.</span> ¿Le quitas la corona? Compártelo 👑</li>
            </ol>
            <div class="mt-5 pt-4 border-t border-white/10 space-y-2 text-xs text-white/70">
                <p>· Monto = solo pagos reales verificados</p>
                <p>· Crédito promocional marcado "Inicio destacado" (nunca como dinero real)</p>
                <p>· Cierre semanal: dom 23:59 (America/Santiago)</p>
                <p>· Perfil oficial / no oficial señalado</p>
            </div>
            <button @click="showFaq = !showFaq" class="mt-4 w-full text-[#F8B803] font-bold text-sm py-2">
                ¿Cómo funciona? ¡más detalle! ▾
            </button>
        </section>

        {{-- FAQ (5 objeciones obligatorias) --}}
        <section x-show="showFaq" x-cloak class="mt-4 bg-white border border-gray-200 rounded-3xl p-6 space-y-4 text-sm">
            <h3 class="font-extrabold text-base">Preguntas frecuentes</h3>
            <details>
                <summary class="font-bold cursor-pointer">¿Esto es real o está manipulado?</summary>
                <p class="mt-1 text-gray-600">Los montos son pagos reales verificados. Cada impulso suma al total público. Los créditos promocionales aparecen marcados como "Inicio destacado".</p>
            </details>
            <details>
                <summary class="font-bold cursor-pointer">¿Mi artista recibe este dinero?</summary>
                <p class="mt-1 text-gray-600">Tu impulso compra visibilidad y promoción del perfil dentro de este ranking. Es una plataforma independiente y no afiliada al artista.</p>
            </details>
            <details>
                <summary class="font-bold cursor-pointer">¿Qué gano yo?</summary>
                <p class="mt-1 text-gray-600">Tu participación hace subir a quien impulsas y queda registrada en el ranking. No hay premio monetario para quien impulsa.</p>
            </details>
            <details>
                <summary class="font-bold cursor-pointer">¿Y si pago y alguien me supera?</summary>
                <p class="mt-1 text-gray-600">Tu impulso siempre suma al total de visibilidad. Ninguna posición futura queda garantizada.</p>
            </details>
            <details>
                <summary class="font-bold cursor-pointer">¿Mi aporte de $1.000 importa?</summary>
                <p class="mt-1 text-gray-600">Cada CLP cuenta en el resultado semanal.</p>
            </details>
        </section>

        {{-- REGLAS COMPLETAS --}}
        <section class="mt-6 text-center">
            <a href="{{ route('rules') }}" class="inline-block text-sm font-bold text-[#F53003] underline underline-offset-2 hover:text-[#c22a02] transition">
                📋 Ver reglas completas →
            </a>
        </section>

        @include('partials.site-footer')
    </main>

    {{-- STICKY CTA INFERIOR (thumb-zone) --}}
    @if ($paymentsEnabled && $leader)
        <div class="fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur border-t border-gray-200 shadow-[0_-8px_24px_rgba(0,0,0,0.08)] px-4 pt-3 pb-[max(0.75rem,env(safe-area-inset-bottom))]">
            <div class="mx-auto max-w-2xl">
                <p class="mb-2 text-center text-[10px] font-bold uppercase tracking-[0.24em] text-gray-500">Acción inmediata</p>
                <button
                    @click="openCheckout(@js($leader['profile_id']))"
                    class="w-full bg-[#F53003] hover:bg-[#c22a02] text-white font-black text-base sm:text-lg py-5 rounded-2xl shadow-xl active:scale-[0.98] transition"
                >
                    🛡️ DEFENDER LA CORONA · {{ $leader ? money_clp($leader['to_number_one_clp'] ?: 1000) : '$1.000' }}
                </button>
            </div>
        </div>
    @endif

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
            modal: { open: false, step: 1, profile: null, amount: 0, quickAmounts: [1000, 2000, 5000], confirmed: false }, receiptData: { periodCode: '', reference: '' },
            fm: { email: '', name: '', age18: false },
            submitting: false,
            error: null,
            init() {
                const now = Date.now();
                const end = {{ $period ? $period->ends_at->timestamp * 1000 : 'Date.now()' }};
                this.periodEnds = Math.max(0, end - now);
                this.tick();
                setInterval(() => this.tick(), 1000);
            },
            tick() {
                this.periodEnds = Math.max(0, this.periodEnds - 1000);
                if (this.periodEnds <= 0) { this.countdown = '¡RESET!'; return; }
                const s = Math.floor(this.periodEnds / 1000);
                const d = Math.floor(s / 86400), h = Math.floor((s % 86400) / 3600),
                      m = Math.floor((s % 3600) / 60), sec = s % 60;
                this.countdown = `${d}d ${String(h).padStart(2,'0')}h ${String(m).padStart(2,'0')}m ${String(sec).padStart(2,'0')}s`;
            },
            openCheckout(id) {
                const p = this.ranking.find(r => r.id === id) || {};
                const min = this.limits.min;
                const needTop = p.toTop || min;
                const suggested = (needTop > 0 && needTop <= this.limits.max) ? needTop : min;
                this.modal = {
                    open: true, step: 1,
                    profile: {
                        id,
                        slug: p.slug || '',
                        name: p.name || '',
                        rank: p.rank || null,
                        amount: p.amount || 0,
                        toTop: p.toTop || min,
                    },
                    amount: suggested,
                    quickAmounts: [min, min * 2, min * 5],
                    confirmed: false,
                };
                this.fm = { email: '', name: '', age18: false };
                this.error = null;
            },
            setCustomAmount(ev) { const v = parseInt(ev.target.value) || 0; this.modal.amount = Math.max(0, v); this.setQuick(null); },
            setQuick(val) { this.modal.amount = val; },
            get suggestedAmount() {
                if (!this.modal.profile) return this.limits.min;
                const need = this.modal.profile.toTop;
                if (need > 0 && need <= this.limits.max) return need;
                return this.limits.min;
            },
            moneyDisplay(v) {
                return v == null ? '$0' : '$' + Number(v).toLocaleString('es-CL');
            },
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
                let rank = 1, ahead = 0;
                for (let i = 0; i < this.ranking.length; i++) {
                    if (this.ranking[i].amount >= target) ahead++;
                }
                rank = ahead + 1;
                const tied = ahead > 0 && this.ranking[ahead - 1].amount === target;
                return { rank, tied };
            },
            goPay() {
                if (!this.modal.amount) this.modal.amount = this.suggestedAmount;
                if (this.modal.amount < this.limits.min) { this.error = `El mínimo es ${this.moneyDisplay(this.limits.min)}.`; return; }
                if (this.modal.amount > this.limits.max) { this.error = `Máximo ${this.moneyDisplay(this.limits.max)} por impulso. Haz varios.`; return; }
                this.modal.step = 2;
                this.error = null;
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
                            profile_id: this.modal.profile.id,
                            amount_clp: this.modal.amount,
                            supporter_name: this.fm.name.trim() || null,
                            is_anonymous: true,
                            age_declared_18: '1',
                            checkout_token: this.checkoutToken,
                            payer_email: email,
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
