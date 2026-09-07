@extends('layouts.app')

@section('title', $title)

@section('body')
    <div class="mx-auto max-w-2xl px-4 py-16 text-center">
        <h1 class="text-3xl font-black text-[#1B1B18]">{{ $title }}</h1>
        <p class="mt-3 text-gray-600">{{ $description }}</p>
        <a href="{{ route('entrar.index') }}" class="mt-6 inline-flex rounded-2xl bg-[#F53003] px-5 py-3 font-black text-white">Ir a Entrar</a>
    </div>
@endsection
