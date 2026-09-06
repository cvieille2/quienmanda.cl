@extends('layouts.app')

@section('title', 'Posición proyectada')

@section('body')
<main class="max-w-3xl mx-auto p-6 space-y-4">
    <h1 class="text-3xl font-bold">Proyección</h1>
    <p><strong>Perfil:</strong> {{ $profile->display_name }}</p>
    <p><strong>Posición proyectada:</strong> {{ $projection['rank'] ?? 'N/D' }}</p>
    <p><strong>Monto proyectado:</strong> {{ $projection['projected_total_clp'] ?? 0 }}</p>
    <p><strong>Para #1:</strong> {{ $projection['to_top_amount'] ?? 0 }}</p>
</main>
@endsection
