{{-- COMPROBANTE DE PAGO — Se muestra después de un pago aprobado --}}
<div class="bg-white border border-gray-200 rounded-3xl overflow-hidden">
    {{-- Header del comprobante --}}
    <div class="bg-[#1B1B18] text-white px-6 py-4 text-center">
        <div class="text-2xl mb-1">🧾</div>
        <h2 class="font-extrabold text-lg">Comprobante de Impulso</h2>
        <div class="text-xs text-white/60 mt-1">{{ $receipt['receipt_id'] }}</div>
    </div>

    {{-- Monto --}}
    <div class="px-6 py-5 text-center border-b border-gray-100">
        <div class="text-xs text-gray-400 uppercase tracking-wider font-bold">Monto Impulsado</div>
        <div class="text-4xl font-black text-[#F53003] mt-1">{{ $receipt['amount_formatted'] }}</div>
        <div class="text-xs text-gray-500 mt-1">
            a <span class="font-bold">{{ $receipt['profile_name'] }}</span>
        </div>
    </div>

    {{-- Detalles --}}
    <div class="px-6 py-4 space-y-3 text-sm">
        <div class="flex justify-between">
            <span class="text-gray-500">Perfil</span>
            <a href="{{ route('profile.show', $receipt['profile_slug']) }}" class="font-bold text-[#F53003] hover:underline">
                {{ $receipt['profile_name'] }}
            </a>
        </div>
        @if ($receipt['final_position'])
            <div class="flex justify-between">
                <span class="text-gray-500">Posición final</span>
                <span class="font-black text-[#1B1B18]">#{{ $receipt['final_position'] }}</span>
            </div>
        @endif
        <div class="flex justify-between">
            <span class="text-gray-500">Impulsante</span>
            <span class="font-medium">{{ $receipt['supporter_name'] }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-500">Período</span>
            <span class="font-medium">{{ $receipt['period_code'] }}</span>
        </div>
        @if ($receipt['period_dates'])
            <div class="flex justify-between">
                <span class="text-gray-500">Ciclo</span>
                <span class="font-medium text-xs">{{ $receipt['period_dates'] }}</span>
            </div>
        @endif
        <div class="flex justify-between">
            <span class="text-gray-500">Fecha confirmación</span>
            <span class="font-medium">{{ $receipt['approved_at'] }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-500">Referencia</span>
            <span class="font-mono text-xs text-gray-400">{{ $receipt['reference'] }}</span>
        </div>
    </div>

    {{-- Descripción --}}
    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
        <p class="text-xs text-gray-600 leading-relaxed">{{ $receipt['description'] }}</p>
    </div>

    {{-- Footer --}}
    <div class="px-6 py-3 text-center text-[10px] text-gray-400 border-t border-gray-100">
        <p>Este comprobante confirma tu impulso. No constituye factura electrónica.</p>
        <p class="mt-0.5">quienmanda.cl · Ranking de perfiles públicos de Chile</p>
    </div>
</div>
