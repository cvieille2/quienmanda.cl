@extends('layouts.app')

@section('title', 'MercadoPago local — simulación')

@section('body')
<main class="min-h-screen bg-[#f8f7f2] px-4 py-8">
    <section class="mx-auto max-w-md rounded-3xl bg-white p-6 shadow border border-gray-200">
        <div class="text-center">
            <div class="text-5xl">💳</div>
            <p class="mt-2 text-xs font-bold uppercase tracking-widest text-[#009EE3]">Simulación local MercadoPago</p>
            <h1 class="mt-2 text-2xl font-black text-[#1B1B18]">Confirmar impulso</h1>
        </div>

        <dl class="mt-6 space-y-3 text-sm">
            <div class="flex justify-between gap-4 border-b border-gray-100 pb-2">
                <dt class="text-gray-500">Perfil</dt>
                <dd class="font-bold text-right">{{ $profile->display_name }}</dd>
            </div>
            <div class="flex justify-between gap-4 border-b border-gray-100 pb-2">
                <dt class="text-gray-500">Monto</dt>
                <dd class="font-black text-[#F53003]">{{ money_clp($transaction->amount_clp) }}</dd>
            </div>
            <div class="border-b border-gray-100 pb-2">
                <dt class="text-gray-500">External reference</dt>
                <dd class="mt-1 break-all font-mono text-xs">{{ $transaction->external_reference }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Token local</dt>
                <dd class="mt-1 break-all font-mono text-xs">{{ $token }}</dd>
            </div>
        </dl>

        <div class="mt-6 grid grid-cols-1 gap-3">
            <form method="POST" action="{{ route('local.mercadopago.approve', ['token' => $token]) }}">
                @csrf
                <button class="w-full rounded-2xl bg-[#16a34a] py-4 font-black text-white active:scale-[0.98] transition">
                    ✅ Aprobar impulso simulado
                </button>
            </form>

            <form method="POST" action="{{ route('local.mercadopago.reject', ['token' => $token]) }}">
                @csrf
                <button class="w-full rounded-2xl bg-[#F53003] py-4 font-black text-white active:scale-[0.98] transition">
                    ❌ Rechazar impulso simulado
                </button>
            </form>
        </div>

        <p class="mt-5 rounded-2xl bg-gray-50 p-3 text-xs text-gray-500">
            Este checkout no cobra dinero. Al aprobar/rechazar dispara el mismo webhook local que usará MercadoPago real.
        </p>
    </section>
</main>
@endsection
