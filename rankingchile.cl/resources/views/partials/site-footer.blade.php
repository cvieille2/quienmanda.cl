<footer class="mt-10 border-t border-gray-200 pt-6 pb-8 text-center text-xs text-gray-400">
    <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-1">
        <a href="{{ route('home') }}" class="hover:text-gray-600 transition">Inicio</a>
        <a href="{{ route('categories') }}" class="hover:text-gray-600 transition">Categorías</a>
        <a href="{{ route('rules') }}" class="hover:text-gray-600 transition">Reglas</a>
        <a href="{{ route('legal.terms') }}" class="hover:text-gray-600 transition">Términos y Condiciones</a>
        <a href="{{ route('legal.privacy') }}" class="hover:text-gray-600 transition">Política de Privacidad</a>
        <a href="mailto:{{ config('legal.contact.email') }}" class="hover:text-gray-600 transition">Contacto</a>
    </div>
    <p class="mt-3">⏳ Cierre semanal: domingo 23:59 (America/Santiago)</p>
    @isset($period)
        <p class="mt-1">Ciclo {{ $period->code }} · {{ $period->starts_at->timezone('America/Santiago')->format('d/m') }} – {{ $period->ends_at->timezone('America/Santiago')->format('d/m') }}</p>
    @endisset
    <p class="mt-2 text-[10px] text-gray-300">© {{ date('Y') }} quienmanda.cl — Plataforma independiente, no afiliada a los perfiles listados.</p>
</footer>
