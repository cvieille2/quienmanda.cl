@php
    $positionPricing = $positionPricing ?? [];
    $topAmount = $leader['total_real_clp'] ?? 0;
    $hasActivity = $topAmount > 0;
    $heroProjection = $heroProjection ?? $positionPricing[0] ?? null;
    $limits = $limits ?? ['min' => 1000, 'max' => 1000000];
    $periodLabel = $periodLabel ?? 'Semana';
@endphp
<section class="bg-gradient-to-b from-[#FFFDF7] to-white py-10 sm:py-14" x-data="conversionHero()">
    <div class="mx-auto max-w-2xl px-4 text-center">

        {{-- Eyebrow --}}
        <p class="text-[10px] uppercase tracking-[0.3em] font-bold text-gray-400">{{ $periodLabel }}</p>

        {{-- Headline --}}
        <h1 class="mt-3 text-4xl sm:text-5xl font-extrabold tracking-tight text-[#1B1B18]">
            ¿QUIERES MANDAR?
        </h1>

        {{-- Dynamic Subheadline --}}
        <p class="mt-3 text-lg sm:text-xl font-bold text-gray-500">
            @if($heroProjection)
                Sube al #{{ $heroProjection['position'] }} por <span class="text-[#F53003]">${{ number_format($heroProjection['amount']) }}</span>
            @elseif($hasActivity)
                Sube al #1 por <span class="text-[#F53003]">${{ number_format($topAmount + ($limits['min'] ?? 1000)) }}</span>
            @else
                Sé el primero en mover el ranking
            @endif
        </p>

        {{-- Source Input --}}
        <div class="mt-8 max-w-lg mx-auto">
            <input type="text" x-model="sourceInput"
                   placeholder="Pega tu perfil, canal, web o proyecto"
                   class="w-full border-2 border-gray-200 focus:border-[#F53003] rounded-2xl px-5 py-4 text-base font-medium placeholder:text-gray-400 outline-none transition"
                   @keydown.enter="goToEntrar()" />
            <p class="mt-2 text-xs text-gray-400">youtube.com/@usuario · instagram.com/user · misitio.cl</p>
        </div>

        {{-- Position Selector --}}
        @if(count($positionPricing) > 0)
        <div class="mt-6">
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
        <div class="mt-8 max-w-lg mx-auto">
            <button @click="goToEntrar()"
                    class="w-full bg-[#1B1B18] hover:bg-[#2a2a26] text-white font-black text-lg py-5 rounded-2xl shadow-xl active:scale-[0.98] transition">
                <span x-text="ctaLabel"></span>
            </button>
        </div>

        {{-- Legal microcopy --}}
        <p class="mt-4 text-[11px] text-gray-400 max-w-md mx-auto leading-relaxed">
            Pagas por visibilidad en Quién Manda. Tu posición puede cambiar si otro perfil te supera.
            No garantizamos ventas, seguidores ni permanencia.
        </p>
    </div>
</section>

@push('scripts')
<script>
function conversionHero() {
    return {
        sourceInput: '',
        selectedPosition: {{ $heroProjection['position'] ?? 1 }},
        selectedAmount: {{ $heroProjection['amount'] ?? $limits['min'] ?? 0 }},
        get ctaLabel() {
            const formatted = Number(this.selectedAmount).toLocaleString('es-CL');
            return 'SUBIR AL #' + this.selectedPosition + ' POR $' + formatted;
        },
        selectPosition(pos, amount) {
            this.selectedPosition = pos;
            this.selectedAmount = amount;
        },
        goToEntrar() {
            const params = new URLSearchParams();
            if (this.sourceInput.trim()) params.set('source', this.sourceInput.trim());
            if (this.selectedPosition) params.set('position', this.selectedPosition);
            if (this.selectedAmount) params.set('amount', this.selectedAmount);
            window.location.href = '{{ route("entrar.index") }}' + '?' + params.toString();
        }
    };
}
</script>
@endpush
