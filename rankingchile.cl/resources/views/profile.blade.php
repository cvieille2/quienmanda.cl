@extends('layouts.app')

@section('title', $profile->display_name . ' está #' . ($viewModel?->position() ?? $rank ?? '-') . ' en ¿Quién Manda? 👑')

@push('meta')
    <meta property="og:title" content="{{ $profile->display_name }} está #{{ $viewModel?->position() ?? $rank ?? '-' }} en ¿Quién Manda? 👑" />
    <meta property="og:description" content="Faltan {{ money_clp($viewModel?->overtakeAboveClp() ?? $entry['overtake_above_clp'] ?? 1000) }} para que {{ $profile->display_name }} siga escalando. Impúlsalo y supéralo." />
    <meta property="og:type" content="profile" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{{ $profile->display_name }} está #{{ $viewModel?->position() ?? $rank ?? '-' }} en ¿Quién Manda?" />
    <meta name="twitter:description" content="El ranking que se decide con plata. Impulsa a {{ $profile->display_name }}." />
@endpush

@section('body')
<div class="min-h-screen" x-data="quienmanda({
    paymentsEnabled: {{ $paymentsEnabled ? 'true' : 'false' }},
    checkoutToken: '{{ $checkoutToken }}',
    limits: { min: {{ $limits['min'] }}, max: {{ $limits['max'] }} },
    checkoutPayload: {{ json_encode($viewModel?->checkoutPayload()) }},
    checkoutProjection: {{ json_encode($projection) }},
    ranking: {{ json_encode(array_map(fn($r) => [
        'id' => $r['profile_id'], 'slug' => $r['slug'], 'name' => $r['display_name'],
        'amount' => $r['total_real_clp'], 'rank' => $r['position'], 'toTop' => $r['to_number_one_clp'] ?? 0,
    ], $ranking)) }}
)">

    @php $activePage = 'profile'; @endphp
    @section('headerExtra')
        <div class="flex items-center gap-2" x-show="countdown">
            <span class="text-[#F8B803] font-mono font-bold tabular-nums" x-text="countdown"></span>
            <span class="text-white/60 text-xs leading-tight">para el<br>RESET</span>
        </div>
    @endsection
    @include('partials.site-header')

    <main class="mx-auto max-w-2xl px-4 pb-28 pt-6">

        <div class="bg-white border border-gray-200 rounded-3xl p-6 text-center">
            <div class="w-24 h-24 mx-auto rounded-full bg-gray-200 flex items-center justify-center text-4xl">
                {{ $profile->profile_image_url ? '' : '👤' }}
            </div>

            <div class="mt-3">
                <div class="mt-2 flex items-center justify-center gap-2">
                    <span class="font-black text-gray-400">#{{ $viewModel?->position() ?? $rank ?? '-' }}</span>
                    <h1 class="text-2xl font-extrabold">{{ $profile->display_name }}</h1>
                    @if ($viewModel && $viewModel->verificationBadge())
                        <span
                            title="{{ $viewModel->verificationStatus() === 'verified' ? 'Verificado' : 'Verificación pendiente' }}"
                            class="rounded-full {{ $viewModel->verificationBadgeColor() }} text-white text-xs px-1.5 py-0.5">
                            {{ $viewModel->verificationBadge() }}
                        </span>
                    @endif
                </div>
                @if ($viewModel ? $viewModel->isCommunityCreated() : $profile->is_community_created)
                    <p class="text-xs text-gray-400 mt-1">👥 Perfil de la Comunidad / No Oficial ⚖️</p>
                @endif
                <div class="mt-3 text-3xl font-black text-[#1B1B18]">
                    {{ $viewModel?->totalRealClpFormatted() ?? money_clp($entry['total_real_clp'] ?? 0) }}
                </div>
                <div class="text-xs text-gray-500">{{ $periodRemainingText ?? 'esta semana' }}</div>
            </div>

            @if ($entry)
                <div class="mt-4 text-sm text-gray-600">
                    👥 {{ $viewModel?->supporterCountPluralized() ?? number_format($entry['supporter_count']) . ' personas impulsan' }} a {{ $profile->display_name }} {{ $periodRemainingText ?? 'esta semana' }}
                    <span class="text-gray-400 text-xs">(distintas y validadas)</span>
                </div>
            @endif

            @if ($paymentsEnabled)
                <button
                    @click="openCheckout(@js($profile->id))"
                    class="mt-6 w-full bg-[#F53003] hover:bg-[#c22a02] text-white font-black py-4 rounded-2xl active:scale-[0.98] transition">
                    👑 IMPULSAR A {{ strtoupper($profile->display_name) }}
                </button>
                @if ($entry && ($viewModel?->overtakeAboveClp() ?? $entry['overtake_above_clp'] ?? 0) > 0)
                    <p class="mt-2 text-xs text-gray-500">para superar al puesto de arriba · <span class="font-bold text-[#F53003]">🔥 faltan {{ money_clp($viewModel?->overtakeAboveClp() ?? $entry['overtake_above_clp'] ?? 0) }}</span></p>
                @endif
            @else
                <div class="mt-6 w-full bg-white/10 border border-gray-200 text-gray-500 font-semibold py-4 rounded-2xl">💤 Pagos desactivados</div>
            @endif

            {{-- MODULE community_organizer --}}
            @if ($sharingEnabled)
                <div class="mt-6 rounded-2xl bg-[#1B1B18] text-white p-5 text-left">
                    <h2 class="font-extrabold">🎯 Moviliza a tu comunidad</h2>
                    <p class="text-xs text-white/70 mt-1">Comparte el link de impulso para movilizar a tu grupo.</p>
                    <div class="mt-3 flex gap-2">
                        <input readonly value="{{ url()->current() }}?ref=share_trophy" class="flex-1 bg-white/10 rounded-lg px-3 py-2 text-xs focus:outline-none" onclick="this.select()" />
                        <button @click="copyShare('{{ url()->current() }}?ref=share_trophy')" class="bg-white text-[#1B1B18] rounded-lg px-4 py-2 text-xs font-bold">📋 Copiar</button>
                    </div>
                    <a href="https://wa.me/?text={{ urlencode('🔥 Impulsa a ' . $profile->display_name . ' en quienmanda.cl 👑 ' . url()->current()) }}"
                       target="_blank" class="mt-2 block text-center bg-[#25D366] text-white font-bold py-2 rounded-lg text-sm">📣 COMPARTIR PARA IMPULSAR</a>
                </div>
            @endif

            <a href="{{ route('home') }}" class="mt-6 block text-[#F53003] font-bold text-sm">← Ver ranking completo</a>
        </div>

        <div class="mt-6 text-center">
            <a href="{{ route('rules') }}" class="text-sm font-bold text-[#F53003] underline underline-offset-2 hover:text-[#c22a02] transition">
                📋 Ver reglas del ranking →
            </a>
        </div>

        @include('partials.site-footer')
    </main>

    {{-- STICKY CTA --}}
    @if ($paymentsEnabled && $entry)
        @php
            $stickyAmount = $projection['required_amount']
                ?? $viewModel?->toNumberOneClp()
                ?? $viewModel?->overtakeAboveClp()
                ?? 1000;
        @endphp
        <div class="fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur border-t border-gray-200 shadow-[0_-8px_24px_rgba(0,0,0,0.08)] px-4 pt-3 pb-[max(0.75rem,env(safe-area-inset-bottom))]">
            <div class="mx-auto max-w-2xl">
                <p class="mb-2 text-center text-[10px] font-bold uppercase tracking-[0.24em] text-gray-500">Acción inmediata</p>
                <button
                    @click="openCheckout(@js($profile->id))"
                    class="w-full bg-[#F53003] hover:bg-[#c22a02] text-white font-black text-base sm:text-lg py-5 rounded-2xl shadow-xl active:scale-[0.98] transition">
                    👑 IMPULSAR · {{ money_clp($stickyAmount) }}
                </button>
            </div>
        </div>
    @endif

    @include('partials.checkout-modal', compact('paymentsEnabled'))

</div>

@push('scripts')
<script>
    function quienmanda(config) {
        return {
            ...config,
            countdown: '',
            modal: { open: false, step: 1, profile: null, amount: 0, quickAmounts: [], confirmed: false }, receiptData: { periodCode: '', reference: '' },
            fm: { email: '', name: '', age18: false, termsAccepted: false },
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
            moneyDisplay(v) { return v == null ? '$0' : '$' + Number(v).toLocaleString('es-CL'); },
            async copyShare(url) {
                try { await navigator.clipboard.writeText(url); } catch (e) {}
            },
            openCheckout(id) {
                const p = this.ranking.find(r => r.id === id) || {};
                const min = this.limits.min;
                const proj = this.checkoutProjection;
                const hasProjection = proj && proj.required_amount > 0;
                const needTop = p.toTop || min;
                const suggested = hasProjection
                    ? proj.required_amount
                    : ((needTop > 0 && needTop <= this.limits.max) ? needTop : min);
                this.modal = { open: true, step: 1, confirmed: false,
                    profile: {
                        id, slug: p.slug || '', name: p.name || '', rank: p.rank || null,
                        amount: p.amount || 0, toTop: p.toTop || min,
                        has_projection: hasProjection,
                        projected_rank: hasProjection ? (proj.target_position || null) : null,
                        projected_to_top: hasProjection ? proj.required_amount : min,
                        cta_required: hasProjection ? proj.required_amount : 0,
                    },
                    amount: suggested, quickAmounts: [min, min * 2, min * 5] };
                this.fm = { email: '', name: '', age18: false, termsAccepted: false }; this.error = null;
            },
            setCustomAmount(ev) { const v = parseInt(ev.target.value) || 0; this.modal.amount = Math.max(0, v); this.setQuick(null); },
            setQuick(val) { this.modal.amount = val; },
            get suggestedAmount() {
                if (!this.modal.profile) return this.limits.min;
                const need = this.modal.profile.toTop;
                return (need > 0 && need <= this.limits.max) ? need : this.limits.min;
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
