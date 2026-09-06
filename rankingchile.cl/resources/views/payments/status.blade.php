@extends('layouts.app')

@section('title', 'Estado de la visibilidad')

@section('body')
<main class="min-h-screen bg-[#f8f7f2] px-4 py-8">
    <section class="mx-auto max-w-md rounded-3xl bg-white p-6 text-center shadow border border-gray-200">
        @if ($status === \App\Enums\SupportTransactionStatus::Approved)
            <div class="text-6xl">👑</div>
            <h1 class="mt-4 text-2xl font-black text-[#1B1B18]">Impulso confirmado</h1>
            <p class="mt-2 text-gray-600">El ranking ya fue actualizado con tu impulso.</p>
        @elseif ($status === \App\Enums\SupportTransactionStatus::Failed)
            <div class="text-6xl">❌</div>
            <h1 class="mt-4 text-2xl font-black text-[#1B1B18]">Pago rechazado</h1>
            <p class="mt-2 text-gray-600">No se sumó al ranking. Puedes volver a intentar el flujo.</p>
        @else
            <div class="text-6xl">⏳</div>
            <h1 class="mt-4 text-2xl font-black text-[#1B1B18]">Procesando tu impulso</h1>
            <p class="mt-2 text-gray-600">El servidor aún no confirma el pago.</p>
        @endif

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

        <a href="{{ route('home') }}" class="mt-6 block rounded-2xl bg-[#1B1B18] py-4 font-black text-white">
            Ver ranking actualizado
        </a>

        <a href="{{ route('profile.show', ['slug' => $slug]) }}" class="mt-3 block text-sm font-bold text-[#F53003]">
            Volver al perfil
        </a>
    </section>
</main>

@include('partials.site-footer')

@endsection
