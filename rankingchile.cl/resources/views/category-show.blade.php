@extends('layouts.app')

@section('title', $category->name . ' — Ranking | Quién Manda')

@push('meta')
    <meta name="description" content="Ranking de {{ $category->name }} en Quién Manda esta semana." />
    <meta name="robots" content="index, follow" />
    <link rel="canonical" href="https://quienmanda.cl/categorias/{{ $category->slug }}" />
    <meta property="og:title" content="{{ $category->name }} — Ranking | Quién Manda" />
    <meta property="og:description" content="Ranking de {{ $category->name }} en Quién Manda esta semana." />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://quienmanda.cl/categorias/{{ $category->slug }}" />
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

    @php $activePage = 'categories'; @endphp
    @include('partials.site-header')
    @include('partials.category-nav', ['categories' => $categories, 'activeCategorySlug' => $activeCategorySlug])

    <main class="mx-auto max-w-6xl px-4 pb-28 pt-6">

        {{-- Category Header --}}
        <div class="text-center mb-8">
            <h1 class="text-3xl font-extrabold tracking-tight text-[#1B1B18]">{{ $category->name }}</h1>
            @if($category->description)
                <p class="mt-2 text-gray-500 max-w-xl mx-auto">{{ $category->description }}</p>
            @endif
            <p class="mt-1 text-sm text-gray-400">{{ count($ranking) }} {{ Str::plural('perfil', count($ranking)) }} en el ranking</p>
        </div>

        @if (count($ranking) > 0)
            <section class="space-y-3 max-w-2xl mx-auto">
                @foreach ($ranking as $i => $r)
                    @php $isLeader = $i === 0; @endphp
                    @if ($i < 3)
                        <a href="{{ route('profile.show', $r['slug']) }}"
                           class="block rounded-2xl border p-4 transition {{ $isLeader ? 'bg-[#FFF8E1] border-[#F8B803] shadow' : 'bg-white border-gray-200 hover:border-gray-300' }}">
                            <div class="flex items-center gap-3">
                                <span class="text-xl font-black {{ $isLeader ? 'text-[#F8B803]' : 'text-gray-400' }} w-8 text-center">{{ $r['position'] }}</span>
                                <div class="flex-1 min-w-0">
                                    <div class="font-bold truncate">{{ $r['display_name'] }}</div>
                                    <div class="text-xs text-gray-500">👥 {{ number_format($r['supporter_count']) }} personas impulsan</div>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="font-black {{ $isLeader ? 'text-[#F53003]' : '' }}">{{ money_clp($r['total_real_clp']) }}</div>
                                </div>
                            </div>
                            @if ($paymentsEnabled)
                                <div class="mt-3">
                                    <button @click.prevent="openCheckout(@js($r['profile_id']))"
                                        class="w-full {{ $isLeader ? 'bg-[#F53003] hover:bg-[#c22a02]' : 'bg-[#1B1B18] hover:bg-[#2a2a26]' }} text-white font-black py-3 rounded-xl active:scale-[0.98] transition text-sm">
                                        {{ $isLeader ? '🛡️ DEFENDER LA CORONA' : 'IMPULSAR' }}
                                    </button>
                                </div>
                            @endif
                        </a>
                    @else
                        <a href="{{ route('profile.show', $r['slug']) }}"
                           class="flex items-center gap-3 bg-white rounded-xl border border-gray-200 px-4 py-3 hover:border-gray-300 transition">
                            <span class="font-black text-gray-400 w-6 text-center">{{ $r['position'] }}</span>
                            <div class="flex-1 font-medium truncate">{{ $r['display_name'] }}</div>
                            @if ($paymentsEnabled)
                                <button @click.prevent="openCheckout(@js($r['profile_id']))"
                                    class="text-xs font-bold text-[#F53003] shrink-0">IMPULSAR</button>
                            @endif
                            <span class="font-bold tabular-nums text-sm shrink-0">{{ money_clp($r['total_real_clp']) }}</span>
                        </a>
                    @endif
                @endforeach
            </section>
        @else
            <section class="text-center py-10 text-gray-500">
                <div class="text-4xl mb-3">🗓️</div>
                <p class="font-bold text-lg text-gray-700">Sin actividad en {{ $category->name }} todavía</p>
                <p class="text-sm mt-1">¡Sé el primero en competir en esta categoría!</p>
            </section>
        @endif

        <div class="mt-6 text-center">
            <a href="{{ route('categories') }}" class="text-sm font-bold text-[#F53003] underline underline-offset-2">← Ver todas las categorías</a>
        </div>

        @include('partials.site-footer')
    </main>

    @include('partials.checkout-modal', compact('paymentsEnabled'))
</div>

@push('scripts')
<script>
    function quienmanda(config) {
        return {
            ...config,
            showFaq: false, periodEnds: 0, countdown: '',
            modal: { open: false, step: 1, profile: null, amount: 0, quickAmounts: [1000, 2000, 5000], confirmed: false },
            receiptData: { periodCode: '', reference: '' },
            fm: { email: '', name: '', age18: false },
            submitting: false, error: null,
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
                for (let i = 0; i < this.ranking.length; i++) { if (this.ranking[i].amount >= target) race++; }
                const rank = race + 1;
                const tied = race > 0 && this.ranking[race - 1].amount === target;
                return { rank, tied };
            },
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
                this.submitting = true; this.error = null;
                try {
                    const res = await fetch('/api/pagos', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: JSON.stringify({ profile_id: this.modal.profile.id, amount_clp: this.modal.amount, supporter_name: this.fm.name.trim() || null, is_anonymous: true, age_declared_18: '1', checkout_token: this.checkoutToken, payer_email: email }),
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
