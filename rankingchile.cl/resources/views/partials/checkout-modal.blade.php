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

            <div x-show="modal.amount >= limits.min && modal.amount <= limits.max" class="mt-3">
                <div class="flex items-center justify-between text-xs text-gray-500 rounded-xl bg-gray-50 border border-gray-200 px-3 py-2">
                    <span>Con <span class="font-bold" x-text="moneyDisplay(modal.amount)"></span> quedaría en</span>
                    <span class="font-black text-lg" :class="projection.rank === 1 ? 'text-[#F8B803]' : 'text-[#1B1B18]'">
                        #<span x-text="projection.rank"></span>
                        <span class="text-xs font-normal text-gray-500" x-show="projection.tied" x-text="'(empata en el Nº' + (projection.rank - 1) + ')'"></span>
                    </span>
                </div>
                <button @click="setQuick(projectedToTop)" x-show="projection.rank > 1 && projectedToTop <= limits.max"
                    class="mt-2 w-full text-[#F53003] text-xs font-bold underline underline-offset-2">
                    ⬆️ Sube al #1 con <span x-text="moneyDisplay(projectedToTop)"></span> (dale, más!)
                </button>
            </div>

            <div x-show="modal.amount > limits.max" class="mt-2 text-xs text-[#F53003]" x-text="'⚠️ Máx ' + moneyDisplay(limits.max) + ' por apoyo. Haz varios.'"></div>
            <div x-show="modal.amount > 0 && modal.amount < limits.min" class="mt-2 text-xs text-[#F53003]" x-text="'El mínimo es ' + moneyDisplay(limits.min) + '.'"></div>
            <div x-show="error" class="mt-3 text-sm text-[#F53003]" x-text="error"></div>

            <button @click="goPay()" :disabled="!modal.amount"
                class="mt-5 w-full bg-[#F53003] disabled:bg-gray-300 disabled:text-gray-500 hover:bg-[#c22a02] text-white font-black py-4 rounded-2xl active:scale-[0.98] transition"
                x-text="(modal.profile.rank === 1 ? '🛡️ DEFENDER LA CORONA · ' : '⭐ APOYAR AHORA · ') + moneyDisplay(modal.amount)"></button>
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

        {{-- PASO 3: PROCESANDO (acreditación SÓLO la confirma el servidor/estado) --}}
        <div x-show="modal.step === 3" class="text-center py-10">
            <div class="text-6xl">⏳</div>
            <h3 class="mt-4 text-xl font-extrabold">Procesando tu apoyo</h3>
            <p class="mt-2 text-sm text-gray-600">
                Estamos confirmando tu pago. <span class="font-semibold">No cierres esta página.</span>
                El pago queda registrado cuando el servidor lo confirme.
            </p>
            <button @click="modal.open = false" class="mt-6 w-full bg-[#1B1B18] text-white font-black py-4 rounded-2xl">VOLVER AL RANKING</button>
        </div>
    </div>
</div>
</template>
