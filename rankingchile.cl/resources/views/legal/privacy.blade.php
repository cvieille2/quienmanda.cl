@extends('layouts.app')
@section('title', $title)
@section('body')
<div class="min-h-screen">
    @php $activePage = 'privacy'; @endphp
    @include('partials.site-header')

    <main class="mx-auto max-w-3xl px-4 py-10">
        <h1 class="text-3xl font-extrabold tracking-tight">Política de Privacidad</h1>
        <p class="mt-2 text-sm text-gray-500">Última actualización: {{ \Carbon\Carbon::parse($effectiveDate ?? now())->format('d/m/Y') }}</p>

        <div class="mt-8 prose prose-sm max-w-none text-gray-700 space-y-6">
            <section>
                <h2 class="text-lg font-bold">1. Responsable del tratamiento</h2>
                <p>quienmanda.cl (en adelante, "el Sitio") es una plataforma independiente de ranking público. El responsable del tratamiento de datos personales es el operador del Sitio, contactable en <a href="mailto:{{ $contactEmail }}" class="text-[#F53003] underline">{{ $contactEmail }}</a>.</p>
            </section>

            <section>
                <h2 class="text-lg font-bold">2. Datos que recopilamos</h2>
                <ul class="list-disc pl-5 space-y-1">
                    <li><strong>Email del pagador:</strong> requerido para emitir comprobante. Se almacena hasheado (SHA-256) en la base de datos y no se comparte con terceros.</li>
                    <li><strong>Nombre opcional:</strong> si lo proporcionas, se muestra como "Nombre" en el comprobante.</li>
                    <li><strong>Datos de sesión:</strong> identificador de sesión del navegador para prevenir abuso.</li>
                    <li><strong>Información de pago:</strong> procesada exclusivamente por Mercado Pago. No almacenamos datos de tarjeta ni cuentas bancarias.</li>
                    <li><strong>Señales de dispositivo:</strong> información básica del dispositivo para detección de fraude.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-lg font-bold">3. Finalidad del tratamiento</h2>
                <p>Los datos se utilizan exclusivamente para:</p>
                <ul class="list-disc pl-5 space-y-1">
                    <li>Procesar tu pago y emitir comprobante.</li>
                    <li>Prevenir fraude y abuso del sistema.</li>
                    <li>Actualizar el ranking público.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-lg font-bold">4. Base legal</h2>
                <p>El tratamiento se fundamenta en tu consentimiento explícito (aceptación de términos y esta política) y en la ejecución del servicio contratado.</p>
            </section>

            <section>
                <h2 class="text-lg font-bold">5. Compartición de datos</h2>
                <p>No vendemos ni compartimos datos personales con terceros, excepto:</p>
                <ul class="list-disc pl-5 space-y-1">
                    <li><strong>Mercado Pago:</strong> para procesar el pago (ellos tienen su propia política de privacidad).</li>
                    <li><strong>Obligación legal:</strong> si una autoridad competente lo solicita mediante orden judicial.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-lg font-bold">6. Retención de datos</h2>
                <p>Los datos de transacción se conservan por 5 años para cumplimiento tributario y de auditoría. Los datos de sesión se eliminan al cerrar el navegador.</p>
            </section>

            <section>
                <h2 class="text-lg font-bold">7. Tus derechos</h2>
                <p>Conforme a la Ley 19.628 sobre protección de datos personales en Chile, tienes derecho a:</p>
                <ul class="list-disc pl-5 space-y-1">
                    <li>Acceder a tus datos personales.</li>
                    <li>Solicitar rectificación de datos inexactos.</li>
                    <li>Solicitar eliminación de tus datos (sujeto a obligaciones legales de retención).</li>
                    <li>Oponerte al tratamiento de tus datos.</li>
                </ul>
                <p>Para ejercer estos derechos, escríbenos a <a href="mailto:{{ $contactEmail }}" class="text-[#F53003] underline">{{ $contactEmail }}</a>.</p>
            </section>

            <section>
                <h2 class="text-lg font-bold">8. Seguridad</h2>
                <p>Implementamos medidas de seguridad técnicas y organizativas razonables para proteger tus datos, incluyendo cifrado en tránsito (HTTPS) y acceso restringido a la base de datos.</p>
            </section>

            <section>
                <h2 class="text-lg font-bold">9. Cambios en esta política</h2>
                <p>Nos reservamos el derecho de modificar esta política. Los cambios se publicarán en esta página con fecha de actualización. El uso continuado del Sitio después de los cambios constituye aceptación.</p>
            </section>

            <section>
                <h2 class="text-lg font-bold">10. Contacto</h2>
                <p>Para consultas sobre privacidad: <a href="mailto:{{ $contactEmail }}" class="text-[#F53003] underline">{{ $contactEmail }}</a></p>
            </section>
        </div>
    </main>

    @include('partials.site-footer')
</div>
@endsection
