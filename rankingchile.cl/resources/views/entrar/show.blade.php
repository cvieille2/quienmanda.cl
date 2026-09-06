@extends('layouts.app')

@section('title', 'Solicitud #'.$submission->id)

@section('body')
<main class="max-w-3xl mx-auto p-6 space-y-6">
    <h1 class="text-3xl font-bold">Solicitud #{{ $submission->id }}</h1>
    <p><strong>Estado:</strong> {{ $submission->status->value }}</p>
    <p><strong>Nombre:</strong> {{ $submission->display_name }}</p>
    <p><strong>Categoría:</strong> {{ $submission->category }}</p>
    <p><strong>Fuente:</strong> {{ $submission->normalized_url }}</p>

    @if($submission->duplicateProfile)
        <div class="p-4 rounded bg-red-50">Ya existe: <a class="underline" href="{{ route('profile.show', $submission->duplicateProfile->slug) }}">{{ $submission->duplicateProfile->display_name }}</a></div>
    @endif

    <div class="flex gap-3 flex-wrap">
        <form method="POST" action="{{ route('entrar.confirmar', $submission) }}">
            @csrf
            <button class="px-4 py-2 rounded bg-black text-white" type="submit">Publicar gratis</button>
        </form>
        <form method="POST" action="{{ route('entrar.checkout', $submission) }}" class="flex gap-2 items-end">
            @csrf
            <input type="hidden" name="age_declared_18" value="1">
            <input type="number" name="amount_clp" min="1" value="1000" class="border rounded p-2 w-32">
            <button class="px-4 py-2 rounded bg-blue-600 text-white" type="submit">Pagar y mover</button>
        </form>
    </div>
</main>
@endsection
