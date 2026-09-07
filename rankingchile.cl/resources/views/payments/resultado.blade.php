@extends('layouts.app')

@section('title', 'Resultado de la visibilidad')

@section('body')
<main class="min-h-screen bg-[#f8f7f2] px-4 py-8">
    <section class="mx-auto max-w-md text-center">
        @php
            $status = $transaction?->status ?? \App\Enums\SupportTransactionStatus::Pending;
        @endphp

        @if ($status === \App\Enums\SupportTransactionStatus::Approved && $receipt)
            {{-- COMPROBANTE COMPLETO --}}
            <div class="mb-4 text-5xl">✅</div>
            <h1 class="text-2xl font-black text-[#1B1B18]">¡Impulso confirmado!</h1>
            <p class="mt-2 text-gray-600 text-sm">
                El ranking ya fue actualizado con tu impulso.
                <span class="font-bold">{{ $receipt['profile_name'] }}</span>
                @if ($receipt['final_position'])
                    quedó en la posición <span class="font-bold">#{{ $receipt['final_position'] }}</span>.
                @else
                    subió 👆.
                @endif
            </p>

            @include('payments.receipt', ['receipt' => $receipt])

        @elseif ($status === \App\Enums\SupportTransactionStatus::Failed)
            <div class="text-6xl">❌</div>
            <h1 class="mt-4 text-2xl font-black text-[#1B1B18]">Pago rechazado</h1>
            <p class="mt-2 text-gray-600">No se sumó al ranking. Puedes volver a intentar el flujo.</p>

            @if ($transaction)
                <div class="mt-5 rounded-2xl bg-gray-50 p-4 text-left text-sm">
                    <div class="flex justify-between gap-4">
                        <span class="text-gray-500">Perfil</span>
                        <span class="font-bold">{{ $transaction->profile->display_name }}</span>
                    </div>
                    <div class="mt-2 flex justify-between gap-4">
                        <span class="text-gray-500">Monto</span>
                        <span class="font-black text-[#F53003]">{{ money_clp($transaction->amount_clp) }}</span>
                    </div>
                </div>
            @endif

        @else
            <div class="text-6xl">⏳</div>
            <h1 class="mt-4 text-2xl font-black text-[#1B1B18]">Procesando tu impulso</h1>
            <p class="mt-2 text-gray-600">
                El pago queda registrado cuando el servidor lo confirme.
                Esta página se puede actualizar en unos segundos.
            </p>

            @if ($transaction)
                <div class="mt-5 rounded-2xl bg-gray-50 p-4 text-left text-sm">
                    <div class="flex justify-between gap-4">
                        <span class="text-gray-500">Perfil</span>
                        <span class="font-bold">{{ $transaction->profile->display_name }}</span>
                    </div>
                    <div class="mt-2 flex justify-between gap-4">
                        <span class="text-gray-500">Monto</span>
                        <span class="font-black text-[#F53003]">{{ money_clp($transaction->amount_clp) }}</span>
                    </div>
                </div>
            @endif
        @endif

        <a href="{{ route('home') }}" class="mt-6 block rounded-2xl bg-[#1B1B18] py-4 font-black text-white">
            Ver ranking actualizado
        </a>

        @if ($slug)
            <a href="{{ route('profile.show', ['slug' => $slug]) }}" class="mt-3 block text-sm font-bold text-[#F53003]">
                Volver al perfil
            </a>
        @endif
    </section>
</main>

@include('partials.site-footer')

@endsection
