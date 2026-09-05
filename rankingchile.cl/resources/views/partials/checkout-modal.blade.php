<template x-teleport="body">
<div x-show="modal.open" x-cloak class="fixed inset-0 z-50 flex items-end sm:items-center justify-center" aria-modal="true">
    <div class="absolute inset-0 bg-black/60" @click="modal.open = false"></div>

    <div class="relative bg-white w-full sm:max-w-md rounded-t-3xl sm:rounded-3xl shadow-2xl p-6 max-h-[92vh] overflow-y-auto pb-[max(1.25rem,env(safe-area-inset-bottom))]">

        {{-- PASO 1: MONTO --}}
        <div x-show="modal.step === 1">
            <div class="flex items-center justify-between">
                <button class="text-gray-400 text-2xl" @click="modal.open = false">✕</button>
                <span class="text-xs text-gray-400">Pago seguro</span>
            </div>

            <div class="mt-4 flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center text-xl">👤</div>
                <div>
                    <div class="font-black">#<span x-text="modal.profile.rank"></span> <span x-text="modal.profile.name"></span></div>
                    <div class="text-xs text-gray-500" x-text="'Actual: ' + moneyDisplay(modal.profile.amount)"></div>
                </div>
            </div>

            <p class="mt-5 text-sm text-gray-600">
                🔥 Para que <span class="font-bold" x-text="modal.profile.name"></span> supere al de arriba faltan exactamente:
            </p>
            <div class="my-4 text-center text-3xl font-black text-[#F53003]" x-text="moneyDisplay(suggestedAmount)"></div>
            <p class="text-center text-xs text-gray-400" x-text="'(para el puesto #1)'"></p>

            <div class="mt-5">
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">¿Cuánto apoyas? (mín $1.000 · máx $500.000)</label>
                <div class="mt-2 grid grid-cols-4 gap-2">
                    <template x-for="q in modal.quickAmounts" :key="q">
                        <button @click="setQuick(q)"
                            class="py-3 rounded-xl border font-bold text-sm"
                            :class="modal.amount === q ? 'bg-[#1B1B18] text-white border-[#1B1B18]' : 'bg-white border-gray-300 hover:border-gray-400'"
                            x-text="'$' + q.toLocaleString('es-CL')"></button>
                    </template>
                    <input type="number" inputmode="numeric" min="1000" max="500000" placeholder="Otro"
                        @input="setCustomAmount($event)"
                        class="py-3 rounded-xl border border-gray-300 text-center font-bold text-sm w-full focus:border-[#F53003] focus:outline-none" />
                </div>
            </div>

            <div x-show="modal.amount > 500000" class="mt-2 text-xs text-[#F53003]">⚠️ Máx $500.000 por apoyo. Haz varios.</div>
            <div x-show="modal.amount > 0 && modal.amount < 1000 && modal.amount !== 1000" class="mt-2 text-xs text-[#F53003]">El mínimo es $1.000.</div>
            <div x-show="error" class="mt-3 text-sm text-[#F53003]" x-text="error"></div>

            <button @click="goPay()" :disabled="!modal.amount"
                class="mt-5 w-full bg-[#F53003] disabled:bg-gray-300 disabled:text-gray-500 hover:bg-[#c22a02] text-white font-black py-4 rounded-2xl active:scale-[0.98] transition"
                x-text="'👑 QUITARLE LA CORONA · ' + moneyDisplay(modal.amount)"></button>
            <p class="mt-3 text-center text-xs text-gray-400">Pago rápido. Puedes quedar en anónimo.</p>
        </div>

        {{-- PASO 2: DATOS + PAGO --}}
        <div x-show="modal.step === 2">
            <div class="flex items-center justify-between">
                <button class="text-gray-400 text-2xl" @click="modal.step = 1">←</button>
                <span class="text-xs text-gray-400">Monto: <span class="font-bold" x-text="moneyDisplay(modal.amount)"></span></span>
            </div>

            <label class="block mt-5 text-sm font-semibold">Tu email <span class="text-[#F53003]">*</span></label>
            <input type="email" x-model="fm.email" placeholder="tucorreo@ejemplo.cl" autocomplete="email" inputmode="email"
                class="mt-1 w-full border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:border-[#F53003]" />
            <p class="text-xs text-gray-400 mt-1">*Usado para tu comprobante, no para crear cuenta ni spam.</p>

            <label class="block mt-4 text-sm font-semibold">Tu nombre (opcional)</label>
            <input type="text" x-model="fm.name" placeholder="ej: Vale por Chile 🇨🇱" class="mt-1 w-full border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:border-[#F53003]" />
            <p class="text-xs text-gray-400 mt-1">(Si lo dejas vacío: "Apoyo anónimo")</p>

            <p class="mt-4 text-xs text-gray-500 leading-relaxed">
                *Este monto lo llevaría al #1 según el ranking actual. La posición definitiva se determina cuando el pago sea confirmado.
            </p>

            <label class="mt-4 flex items-start gap-2 cursor-pointer">
                <input type="checkbox" x-model="fm.age18" class="mt-0.5">
                <span class="text-sm">Soy mayor de 18 años</span>
            </label>

            <div x-show="error" class="mt-3 text-sm text-[#F53003]" x-text="error"></div>

            <button @click="submitPayment()" :disabled="submitting"
                class="mt-5 w-full bg-[#009EE3] hover:bg-[#007ab3] disabled:bg-gray-300 disabled:text-gray-500 text-white font-black py-4 rounded-2xl active:scale-[0.98] transition flex items-center justify-center gap-2">
                <span x-show="!submitting">PAGAR CON MERCADO PAGO 💳</span>
                <span x-show="submitting" class="inline-block">⏳ Confirmando...</span>
            </button>
        </div>

        {{-- PASO 3: PROCESANDO / CONFIRMACIÓN --}}
        <div x-show="modal.step === 3" class="text-center py-10">
            <template x-if="modal.confirmed">
                <div>
                    <div class="text-6xl">✅</div>
                    <h3 class="mt-4 text-xl font-extrabold">PAGO RECIBIDO</h3>
                    <p class="mt-2 text-sm text-gray-600" x-text="'Tu apoyo se reflejó en el ranking. ¡Gracias!'"></p>
                    <button @click="modal.open = false" class="mt-6 w-full bg-[#1B1B18] text-white font-black py-4 rounded-2xl">CONTINUAR 🎉</button>
                </div>
            </template>
        </div>
    </div>
</div>
</template>
