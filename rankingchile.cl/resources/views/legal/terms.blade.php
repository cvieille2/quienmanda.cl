@extends('layouts.app')
@section('title', $title)
@section('body')
<div class="min-h-screen">
    @php $activePage = 'terms'; @endphp
    @include('partials.site-header')

    <main class="mx-auto max-w-3xl px-4 py-10">
        <h1 class="text-3xl font-extrabold tracking-tight">Términos y Condiciones</h1>
        <p class="mt-2 text-sm text-gray-500">Versión {{ $termsVersion }} · Vigente desde {{ \Carbon\Carbon::parse($effectiveDate)->format('d/m/Y') }}</p>

        <div class="mt-8 prose prose-sm max-w-none text-gray-700 space-y-6">
            <section>
                <h2 class="text-lg font-bold">1. Aceptación de los términos</h2>
                <p>Al acceder y utilizar quienmanda.cl (en adelante, "el Sitio"), usted acepta estos Términos y Condiciones en su totalidad. Si no está de acuerdo, por favor no utilice el Sitio.</p>
            </section>

            <section>
                <h2 class="text-lg font-bold">2. Descripción del servicio</h2>
                <p>Quién Manda es una plataforma de ranking público que permite a los usuarios participar mediante pagos promocionales para impulsar la visibilidad de perfiles dentro del ranking. El servicio de visibilidad es independiente de los perfiles listados.</p>
            </section>

            <section>
                <h2 class="text-lg font-bold">3. Naturaleza del servicio</h2>
                <p>El pago corresponde a un <strong>servicio de visibilidad promocional</strong> dentro de Quién Manda. No constituye una donación, inversión, aporte solidario ni transferencia de dinero a los perfiles promocionados.</p>
            </section>

            <section>
                <h2 class="text-lg font-bold">4. No garantías</h2>
                <p>Quién Manda no garantiza resultados específicos incluyendo, pero no limitado a: cantidad de visitas, clics, seguidores, ventas, clientes, ingresos o permanencia en una posición del ranking.</p>
            </section>

            <section>
                <h2 class="text-lg font-bold">5. Pagos</h2>
                <p>Los pagos se procesan exclusivamente a través de Mercado Pago. Quién Manda no almacena datos de tarjetas de crédito ni cuentas bancarias. El monto mínimo y máximo de cada pago se indica en el Sitio.</p>
            </section>

            <section>
                <h2 class="text-lg font-bold">6. Períodos del ranking</h2>
                <p>El ranking opera en períodos definidos. Las reglas, límites y configuración de cada período quedan congelados una vez que el período está activo. Los pagos se asignan al período correspondiente según las reglas del sistema.</p>
            </section>

            <section>
                <h2 class="text-lg font-bold">7. Posiciones y proyecciones</h2>
                <p>Las posiciones mostradas son proyecciones basadas en el estado actual del ranking. La posición final puede variar si las condiciones cambian antes de la confirmación del pago. Una posición obtenida no es permanente.</p>
            </section>

            <section>
                <h2 class="text-lg font-bold">8. Perfiles</h2>
                <p>La aparición de un perfil en Quién Manda no implica relación comercial, afiliación o endoso. Algunos perfiles pueden ser creados con información pública. Los titulares pueden solicitar correcciones escribiendo a {{ $contactEmail }}.</p>
            </section>

            <section>
                <h2 class="text-lg font-bold">9. Conducta prohibida</h2>
                <p>Está prohibido utilizar bots, automatizaciones abusivas, fraude de pagos o técnicas destinadas a manipular artificialmente el ranking. Quién Manda podrá invalidar actividad fraudulenta.</p>
            </section>

            <section>
                <h2 class="text-lg font-bold">10. Moderación</h2>
                <p>Quién Manda se reserva el derecho de revisar, suspender o eliminar perfiles y actividad cuando existan indicios de fraude, abuso, suplantación, contenido ilegal o incumplimiento de estos términos.</p>
            </section>

            <section>
                <h2 class="text-lg font-bold">11. Limitación de responsabilidad</h2>
                <p>En ningún caso Quién Manda será responsable por daños indirectos, incidentales, especiales o consecuentes derivados del uso del Sitio o de los pagos realizados.</p>
            </section>

            <section>
                <h2 class="text-lg font-bold">12. Cambios en los términos</h2>
                <p>Nos reservamos el derecho de modificar estos términos en cualquier momento. Los cambios se publicarán en esta página con la fecha de actualización correspondiente. El uso continuado del Sitio después de los cambios constituye aceptación.</p>
            </section>

            <section>
                <h2 class="text-lg font-bold">13. Contacto</h2>
                <p>Para consultas sobre estos términos: <a href="mailto:{{ $contactEmail }}" class="text-[#F53003] underline">{{ $contactEmail }}</a></p>
            </section>
        </div>

        <div class="mt-8 text-center">
            <a href="{{ route('rules') }}" class="text-sm font-bold text-[#F53003] underline">📋 Ver reglas del ranking →</a>
        </div>
    </main>

    @include('partials.site-footer')
</div>
@endsection
