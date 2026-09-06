<template x-teleport="body">
<div x-show="modal.open" x-cloak class="fixed inset-0 z-50 flex items-end sm:items-center justify-center" aria-modal="true">
    <div class="absolute inset-0 bg-black/60" @click="modal.open = false"></div>

    <div class="relative bg-white w-full sm:max-w-md rounded-t-3xl sm:rounded-3xl shadow-2xl p-6 max-h-[92vh] overflow-y-auto pb-[max(1.25rem,env(safe-area-inset-bottom))]">

        {{-- PASO 1: MONTO + CLAIM/OUTBID --}}
        <div x-show="modal.step === 1">
            <div class="flex items-center justify-between">
                <button class="text-gray-400 text-2xl" @click="modal.open = false">✕</button>
                <span class="text-xs text-gray-400">Pago seguro</span>
            </div>

            {{-- Perfil --}}
            <div class="mt-4 flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center text-xl">👤</div>
                <div>
                    <div class="font-black">#<span x-text="modal.profile.rank"></span> <span x-text="modal.profile.name"></span></div>
                    <div class="text-xs text-gray-500" x-text="'Actual: ' + moneyDisplay(modal.profile.amount)"></div>
                </div>
            </div>

            {{-- Qué compras --}}
            <div class="mt-4 rounded-xl bg-[#FFF8E1] border border-[#F8B803]/40 px-4 py-3 shadow-sm">
                <div class="text-[10px] uppercase tracking-wider font-bold text-[#F8B803]">¿Qué compras?</div>
                <p class="text-xs text-gray-700 mt-1 leading-relaxed">
                    Tu inversión promocional suma al total de <span class="font-bold" x-text="modal.profile.name"></span> en el ranking semanal.
                    Cada CLP cuenta para subir de puesto y ganar visibilidad.
                </p>
            </div>

            {{-- Monto a pagar --}}
            <label class="mt-4 block text-sm font-semibold">¿Cuánto quieres invertir?</label>
            <div class="mt-2 flex items-center gap-2">
                <span class="text-2xl font-black text-gray-400">$</span>
                <input type="number" x-model.number="modal.amount"
                    :min="limits.min" :max="limits.max" step="100"
                    class="w-full text-3xl font-black text-[#F53003] border-b-2 border-gray-200 focus:border-[#F53003] outline-none py-2 bg-transparent tabular-nums"
                    placeholder="0" inputmode="numeric" />
            </div>

            {{-- Botones rápidos --}}
            <div class="mt-3 flex gap-2">
                <template x-for="q in modal.quickAmounts" :key="q">
                    <button @click="setQuick(q)"
                        class="flex-1 py-2 rounded-xl text-sm font-bold border-2 transition"
                        :class="modal.amount === q ? 'border-[#F53003] bg-[#F53003]/10 text-[#F53003]' : 'border-gray-200 text-gray-600 hover:border-gray-300'"
                        x-text="moneyDisplay(q)">
                    </button>
                </template>
                <button @click="setQuick(suggestedAmount)" x-show="suggestedAmount > 0 && !modal.quickAmounts.includes(suggestedAmount)"
                    class="flex-1 py-2 rounded-xl text-sm font-bold border-2 transition"
                    :class="modal.amount === suggestedAmount ? 'border-[#F53003] bg-[#F53003]/10 text-[#F53003]' : 'border-gray-200 text-gray-600 hover:border-gray-300'">
                    🎯 #1
                </button>
            </div>
            <p class="mt-2 text-xs text-gray-400">
                Mínimo <span x-text="moneyDisplay(limits.min)"></span> · Máximo <span x-text="moneyDisplay(limits.max)"></span>
            </p>

            {{-- Proyección de posición --}}
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

            {{-- Warning: outbid --}}
            <div x-show="modal.profile.rank > 1" class="mt-3 rounded-xl bg-orange-50 border border-orange-200 px-4 py-3">
                <div class="text-[10px] uppercase tracking-wider font-bold text-orange-600">⚠️ ¿Y si me superan?</div>
                <p class="text-xs text-gray-600 mt-1 leading-relaxed">
                    Si otro usuario impulsa con más dinero a un perfil que está debajo,
                    <span class="font-bold" x-text="modal.profile.name"></span> puede bajar de puesto.
                    Tu impulso protege la posición — cada CLP cuenta.
                </p>
            </div>

            {{-- Validaciones --}}
            <div x-show="modal.amount > limits.max" class="mt-2 text-xs text-[#F53003]" x-text="'⚠️ Máx ' + moneyDisplay(limits.max) + ' por impulso. Haz varios.'"></div>
            <div x-show="modal.amount > 0 && modal.amount < limits.min" class="mt-2 text-xs text-[#F53003]" x-text="'El mínimo es ' + moneyDisplay(limits.min) + '.'"></div>
            <div x-show="error" class="mt-3 text-sm text-[#F53003]" x-text="error"></div>

            {{-- CTA --}}
            <button @click="goPay()" :disabled="!modal.amount"
                class="mt-5 w-full bg-[#F53003] disabled:bg-gray-300 disabled:text-gray-500 hover:bg-[#c22a02] text-white font-black py-5 rounded-2xl shadow-xl active:scale-[0.98] transition"
                x-text="(modal.profile.rank === 1 ? '🛡️ DEFENDER LA CORONA · ' : '⭐ IMPULSAR AHORA · ') + moneyDisplay(modal.amount)"></button>
            <p class="mt-3 text-center text-xs text-gray-400">Pago seguro vía Mercado Pago. Recibirás comprobante.</p>
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
            <p class="text-xs text-gray-400 mt-1">(Si lo dejas vacío: "Impulso anónimo")</p>

            {{-- Resumen claim --}}
            <div class="mt-4 rounded-xl bg-gray-50 border border-gray-200 px-4 py-3 text-xs text-gray-600 space-y-1">
                <div class="flex justify-between">
                    <span>Perfil</span>
                    <span class="font-bold" x-text="modal.profile.name"></span>
                </div>
                <div class="flex justify-between">
                    <span>Monto</span>
                    <span class="font-bold text-[#F53003]" x-text="moneyDisplay(modal.amount)"></span>
                </div>
                <div class="flex justify-between">
                    <span>Proyección</span>
                    <span class="font-bold">#<span x-text="projection.rank"></span></span>
                </div>
            </div>

            <p class="mt-3 text-xs text-gray-500 leading-relaxed">
                La posición definitiva se determina cuando el pago sea confirmado por el servidor.
            </p>

            <label class="mt-4 flex items-start gap-2 cursor-pointer">
                <input type="checkbox" x-model="fm.age18" class="mt-0.5">
                <span class="text-sm">Soy mayor de 18 años</span>
            </label>

            <p class="mt-3 text-xs text-gray-400 leading-relaxed">
                Antes de pagar puedes revisar <a href="/reglas" target="_blank" class="text-[#F53003] underline">cómo se calculan las posiciones</a> y qué ocurre si otro perfil te supera.
            </p>

            <div x-show="error" class="mt-3 text-sm text-[#F53003]" x-text="error"></div>

            <button @click="submitPayment()" :disabled="submitting"
                class="mt-5 w-full bg-[#009EE3] hover:bg-[#007ab3] disabled:bg-gray-300 disabled:text-gray-500 text-white font-black py-5 rounded-2xl shadow-xl active:scale-[0.98] transition flex items-center justify-center gap-2">
                <span x-show="!submitting">PAGAR Y APLICAR IMPULSO 💳</span>
                <span x-show="submitting" class="inline-block">⏳ Confirmando...</span>
            </button>
        </div>

        {{-- PASO 3: COMPROBANTE --}}
        <div x-show="modal.step === 3" class="text-center py-6">
            <div class="text-5xl mb-3">✅</div>
            <h3 class="text-xl font-extrabold">¡Impulso confirmado!</h3>
            <p class="mt-2 text-sm text-gray-600">
                Tu pago de <span class="font-bold text-[#F53003]" x-text="moneyDisplay(modal.amount)"></span>
                fue registrado para <span class="font-bold" x-text="modal.profile.name"></span>.
            </p>

            {{-- Comprobante inline --}}
            <div class="mt-4 rounded-xl bg-gray-50 border border-gray-200 px-4 py-3 text-left text-xs space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-500">Monto</span>
                    <span class="font-black text-[#F53003]" x-text="moneyDisplay(modal.amount)"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Perfil</span>
                    <span class="font-bold" x-text="modal.profile.name"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Período</span>
                    <span class="font-medium" x-text="receiptData.periodCode || '—'"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Referencia</span>
                    <span class="font-mono text-gray-400" x-text="receiptData.reference || '—'"></span>
                </div>
            </div>

            <p class="mt-3 text-xs text-gray-400 leading-relaxed">
                📧 Enviamos tu comprobante a <span class="font-medium" x-text="fm.email || 'tu email'"></span>.
                La posición se actualiza cuando el servidor confirme.
            </p>

            <button @click="modal.open = false"
                class="mt-5 w-full bg-[#1B1B18] text-white font-black py-5 rounded-2xl shadow-xl active:scale-[0.98] transition">
                VOLVER AL RANKING
            </button>
            <a :href="modal.profile.slug ? '/perfil/' + modal.profile.slug : '#'"
               class="mt-2 block text-center text-[#F53003] font-bold text-sm py-2">
                Ver perfil de <span x-text="modal.profile.name"></span> →
            </a>
        </div>
    </div>
</div>
</template>
