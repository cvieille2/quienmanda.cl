@extends('layouts.app')

@section('title', $title)

@push('meta')
    <meta name="description" content="{{ $metaDescription }}" />
    <meta name="robots" content="index, follow" />
    <link rel="canonical" href="https://quienmanda.cl/reglas" />
    <meta property="og:title" content="Reglas del Ranking | Quién Manda" />
    <meta property="og:description" content="{{ $metaDescription }}" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://quienmanda.cl/reglas" />
@endpush

@section('body')
<div class="min-h-screen">

    @php $activePage = 'rules'; @endphp
    @include('partials.site-header')

    <main class="mx-auto max-w-3xl px-4 py-10">

        {{-- HERO --}}
        <section class="text-center">
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">Reglas de Quién Manda</h1>
            <p class="mt-3 text-gray-500 max-w-xl mx-auto">Así funciona el ranking, los pagos y la pelea por cada posición.</p>
            <p class="mt-2 text-sm text-gray-600 max-w-xl mx-auto">En Quién Manda, las posiciones cambian según los pagos promocionales confirmados durante cada período. Estas reglas explican exactamente cómo funciona.</p>
        </section>

        {{-- DISCLOSURE BOX --}}
        <div class="mt-8 rounded-2xl bg-[#FFF8E1] border border-[#F8B803]/30 px-5 py-4">
            <div class="text-[10px] uppercase tracking-wider font-bold text-[#F8B803]">Lo más importante</div>
            <p class="mt-1 text-sm text-gray-700 leading-relaxed">
                Pagas por <strong>visibilidad dentro del ranking</strong>. La posición que obtengas puede cambiar después. Quién Manda no garantiza ventas, seguidores, clics ni permanencia en un puesto.
            </p>
        </div>

        {{-- EN SIMPLE: 4 PASOS --}}
        <section class="mt-10">
            <h2 class="text-xl font-extrabold text-center">En simple</h2>
            <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="rounded-2xl bg-white border border-gray-200 p-5">
                    <div class="w-10 h-10 rounded-full bg-[#F53003] text-white font-black flex items-center justify-center text-lg">1</div>
                    <h3 class="mt-3 font-extrabold">Elige un perfil</h3>
                    <p class="mt-1 text-sm text-gray-600">Selecciona a quién quieres impulsar dentro del ranking.</p>
                </div>
                <div class="rounded-2xl bg-white border border-gray-200 p-5">
                    <div class="w-10 h-10 rounded-full bg-[#F53003] text-white font-black flex items-center justify-center text-lg">2</div>
                    <h3 class="mt-3 font-extrabold">Mira cuánto necesita</h3>
                    <p class="mt-1 text-sm text-gray-600">Quién Manda calcula cuánto debes pagar para alcanzar o superar una posición determinada.</p>
                </div>
                <div class="rounded-2xl bg-white border border-gray-200 p-5">
                    <div class="w-10 h-10 rounded-full bg-[#F53003] text-white font-black flex items-center justify-center text-lg">3</div>
                    <h3 class="mt-3 font-extrabold">Paga</h3>
                    <p class="mt-1 text-sm text-gray-600">El cambio sólo se aplica cuando el pago es <strong>confirmado</strong> por el sistema.</p>
                </div>
                <div class="rounded-2xl bg-white border border-gray-200 p-5">
                    <div class="w-10 h-10 rounded-full bg-[#F53003] text-white font-black flex items-center justify-center text-lg">4</div>
                    <h3 class="mt-3 font-extrabold">El ranking cambia</h3>
                    <p class="mt-1 text-sm text-gray-600">El perfil sube según el monto confirmado, pero puede ser superado nuevamente por otros usuarios.</p>
                </div>
            </div>
        </section>

        {{-- REGLAS DETALLADAS --}}
        <section class="mt-12 space-y-8">

            {{-- 1. Ranking basis --}}
            <div id="regla-ranking" class="rounded-2xl border border-gray-200 bg-white p-6">
                <h2 class="text-lg font-extrabold">1. El ranking se mueve con pagos confirmados</h2>
                <div class="mt-3 space-y-2 text-sm text-gray-700 leading-relaxed">
                    <p>Las posiciones de Quién Manda se calculan utilizando únicamente <strong>pagos promocionales válidos y confirmados</strong>.</p>
                    <p>Un intento de pago, una redirección desde Mercado Pago o un pago pendiente <strong>no cambia el ranking</strong>.</p>
                    <p>El ranking se actualiza solamente cuando Quién Manda recibe y valida correctamente la confirmación del proveedor de pagos.</p>
                </div>
            </div>

            {{-- 2. Ranking total --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6">
                <h2 class="text-lg font-extrabold">2. Cada perfil acumula un total</h2>
                <div class="mt-3 space-y-2 text-sm text-gray-700 leading-relaxed">
                    <p>Durante cada período activo, los pagos confirmados asociados a un perfil se acumulan.</p>
                    <p>El total acumulado se utiliza para calcular su posición.</p>
                    <p>Mientras mayor sea el total válido de un perfil, mayor podrá ser su posición dentro del ranking.</p>
                </div>
            </div>

            {{-- 3. Overtake amount --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6">
                <h2 class="text-lg font-extrabold">3. Te mostramos cuánto necesitas para subir</h2>
                <div class="mt-3 space-y-2 text-sm text-gray-700 leading-relaxed">
                    <p>Quién Manda puede mostrar el monto exacto que un perfil necesita para alcanzar una posición determinada.</p>
                    <p>Por ejemplo: <em class="font-bold text-[#F53003]">"Subir al puesto #2 por $19.000"</em>.</p>
                    <p>El cálculo utiliza el estado actual del ranking y las reglas vigentes del período.</p>
                </div>
                {{-- Ejemplo visual --}}
                <div class="mt-4 rounded-xl bg-gray-50 border border-gray-200 px-4 py-3 text-xs space-y-1">
                    <div class="text-[10px] uppercase tracking-wider font-bold text-gray-400">Ejemplo</div>
                    <div class="flex justify-between"><span class="text-gray-500">Tu perfil tiene</span><span class="font-bold">$12.000</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">El #2 tiene</span><span class="font-bold">$30.000</span></div>
                    <div class="flex justify-between border-t border-gray-200 pt-1"><span class="text-gray-500">Necesitas pagar</span><span class="font-black text-[#F53003]">$19.000</span></div>
                </div>
            </div>

            {{-- 4. Projection not guaranteed --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6">
                <h2 class="text-lg font-extrabold">4. La posición mostrada es una proyección</h2>
                <div class="mt-3 space-y-2 text-sm text-gray-700 leading-relaxed">
                    <p>Cuando Quién Manda indica que un pago permitiría alcanzar una posición, esa proyección se basa en el ranking disponible en ese momento.</p>
                    <p>Mientras el pago está siendo procesado, <strong>otro usuario puede modificar el ranking</strong>.</p>
                    <p>Por esa razón, la posición finalmente obtenida puede variar si las condiciones cambian antes de la confirmación.</p>
                </div>
            </div>

            {{-- 5. Can be outbid --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6">
                <h2 class="text-lg font-extrabold">5. Puedes ser superado después</h2>
                <div class="mt-3 space-y-2 text-sm text-gray-700 leading-relaxed">
                    <p>Comprar visibilidad <strong>no significa comprar una posición permanente</strong>.</p>
                    <p>Después de que un perfil sube, otros usuarios pueden realizar nuevos pagos y superarlo.</p>
                    <p>Esto forma parte normal de la dinámica competitiva de Quién Manda.</p>
                </div>
                <div class="mt-3 rounded-xl bg-orange-50 border border-orange-200 px-4 py-3 text-sm text-gray-700">
                    🔥 <strong>Tu pago puede llevar a un perfil al #1, pero otro usuario puede destronarlo después.</strong>
                </div>
            </div>

            {{-- 6. Ties --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6">
                <h2 class="text-lg font-extrabold">6. Cómo funcionan los empates</h2>
                <div class="mt-3 space-y-2 text-sm text-gray-700 leading-relaxed">
                    <p>Si dos perfiles alcanzan el mismo total, Quién Manda aplica la regla de desempate definida para el período.</p>
                    <p>El sistema debe mantener una regla determinística y consistente.</p>
                    <p><strong>La interfaz no debe prometer que igualar un monto garantiza superar al perfil que ya ocupa una posición.</strong></p>
                </div>
                <div class="mt-3 rounded-xl bg-gray-50 border border-gray-200 px-4 py-3 text-sm text-gray-700">
                    💡 <em>Para superar una posición debes alcanzar un monto <strong>superior</strong>, no solamente igualarlo.</em>
                </div>
            </div>

            {{-- 7. Ranking periods --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6">
                <h2 class="text-lg font-extrabold">7. El ranking funciona por períodos</h2>
                <div class="mt-3 space-y-2 text-sm text-gray-700 leading-relaxed">
                    <p>Quién Manda organiza la competencia en <strong>períodos definidos</strong>.</p>
                    <p>Cada período tiene una fecha y hora de inicio y una fecha y hora de cierre.</p>
                    <p>Los pagos se asignan al período correspondiente según las reglas temporales del sistema.</p>
                    <p>Cuando comienza un nuevo período, el ranking puede reiniciarse o utilizar la configuración definida para esa nueva competencia.</p>
                </div>
            </div>

            {{-- 8. Configuration freeze --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6">
                <h2 class="text-lg font-extrabold">8. Las reglas del período no cambian a mitad de camino</h2>
                <div class="mt-3 space-y-2 text-sm text-gray-700 leading-relaxed">
                    <p>Una vez que un período está activo, las reglas importantes utilizadas para calcular el ranking <strong>permanecen congeladas</strong>.</p>
                    <p>Esto incluye límites, reglas de desempate, ventanas de cierre y cualquier configuración que pueda alterar el resultado.</p>
                    <p>El objetivo es evitar que la competencia cambie arbitrariamente mientras está en curso.</p>
                </div>
            </div>

            {{-- 9. Closing --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6">
                <h2 class="text-lg font-extrabold">9. Qué pasa cuando termina el período</h2>
                <div class="mt-3 space-y-2 text-sm text-gray-700 leading-relaxed">
                    <p>Al llegar la hora de cierre, Quién Manda determina las posiciones finales utilizando las operaciones válidas correspondientes al período.</p>
                    <p>El resultado puede guardarse como un registro histórico.</p>
                    <p>La plataforma puede mostrar posteriormente quién terminó primero, las posiciones finales y otros datos públicos del período.</p>
                </div>
            </div>

            {{-- 10. Settlement window --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6">
                <h2 class="text-lg font-extrabold">10. Pagos realizados cerca del cierre</h2>
                <div class="mt-3 space-y-2 text-sm text-gray-700 leading-relaxed">
                    <p>Un pago iniciado antes del cierre <strong>no necesariamente cuenta automáticamente dentro de ese período</strong>.</p>
                    <p>Lo importante es cuándo el pago cumple las condiciones de confirmación y asignación establecidas por el sistema.</p>
                    <p>Quién Manda puede utilizar una pequeña ventana de liquidación para procesar pagos que estaban siendo confirmados al momento del cierre.</p>
                </div>
            </div>

            {{-- 11. Qualified at --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6">
                <h2 class="text-lg font-extrabold">11. La hora válida del ranking es la hora de calificación</h2>
                <div class="mt-3 space-y-2 text-sm text-gray-700 leading-relaxed">
                    <p>Para decidir a qué período pertenece una operación, Quién Manda utiliza la fecha y hora en que el pago cumple las condiciones necesarias para afectar oficialmente el ranking.</p>
                    <p>Esta fecha puede ser distinta de la hora en que el usuario inició el checkout.</p>
                </div>
            </div>

            {{-- 12. Late payments --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6">
                <h2 class="text-lg font-extrabold">12. Pagos confirmados tarde</h2>
                <div class="mt-3 space-y-2 text-sm text-gray-700 leading-relaxed">
                    <p>En determinadas situaciones, un proveedor de pagos puede confirmar una operación con retraso.</p>
                    <p>Si un pago tardío cumple las reglas establecidas para el período correspondiente, Quién Manda podrá revisar el resultado histórico.</p>
                    <p>La plataforma <strong>no borra silenciosamente el resultado original</strong>.</p>
                    <p>Cuando exista una corrección, debe conservarse el registro anterior y generar una revisión trazable.</p>
                </div>
            </div>

            {{-- 13. Payment reversals --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6">
                <h2 class="text-lg font-extrabold">13. Pagos anulados, revertidos o fraudulentos</h2>
                <div class="mt-3 space-y-2 text-sm text-gray-700 leading-relaxed">
                    <p>Si una operación es anulada, devuelta, revertida, considerada fraudulenta o deja de ser válida, Quién Manda puede <strong>excluir ese monto del ranking</strong>.</p>
                    <p>Esto puede provocar cambios en posiciones actuales o revisiones de resultados históricos.</p>
                    <p>Las modificaciones deben quedar registradas para mantener trazabilidad.</p>
                </div>
            </div>

            {{-- 14. Not endorsement --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6">
                <h2 class="text-lg font-extrabold">14. Estar primero no significa ser "mejor"</h2>
                <div class="mt-3 space-y-2 text-sm text-gray-700 leading-relaxed">
                    <p>Quién Manda es un ranking promocional basado en pagos válidos.</p>
                    <p>La posición de un perfil <strong>no representa una evaluación editorial, científica u objetiva</strong> de calidad, fama o popularidad.</p>
                    <p>Un perfil ubicado en el #1 simplemente lidera de acuerdo con las reglas de esta competencia.</p>
                </div>
            </div>

            {{-- 15. What you buy --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6">
                <h2 class="text-lg font-extrabold">15. Qué compra realmente el usuario</h2>
                <div class="mt-3 space-y-2 text-sm text-gray-700 leading-relaxed">
                    <p>El usuario compra <strong>visibilidad y participación promocional</strong> dentro de Quién Manda.</p>
                    <p>El pago puede modificar la posición de un perfil dentro del ranking.</p>
                    <p>No compra seguidores, ventas, clientes, contratos ni resultados comerciales garantizados.</p>
                </div>
            </div>

            {{-- 16. No guarantees --}}
            <div class="rounded-2xl border border-[#F53003]/20 bg-red-50 p-6">
                <h2 class="text-lg font-extrabold text-[#F53003]">16. Lo que no garantizamos</h2>
                <div class="mt-3 grid grid-cols-2 gap-2 text-sm text-gray-700">
                    <div class="flex items-center gap-2"><span class="text-[#F53003]">✕</span> Cantidad de visitas</div>
                    <div class="flex items-center gap-2"><span class="text-[#F53003]">✕</span> Clics</div>
                    <div class="flex items-center gap-2"><span class="text-[#F53003]">✕</span> Seguidores</div>
                    <div class="flex items-center gap-2"><span class="text-[#F53003]">✕</span> Ventas</div>
                    <div class="flex items-center gap-2"><span class="text-[#F53003]">✕</span> Clientes</div>
                    <div class="flex items-center gap-2"><span class="text-[#F53003]">✕</span> Ingresos</div>
                    <div class="flex items-center gap-2"><span class="text-[#F53003]">✕</span> Permanencia en posición</div>
                    <div class="flex items-center gap-2"><span class="text-[#F53003]">✕</span> Mantener el #1 hasta el cierre</div>
                </div>
            </div>

            {{-- 17. Profile independence --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6">
                <h2 class="text-lg font-extrabold">17. Los perfiles pueden no estar asociados a Quién Manda</h2>
                <div class="mt-3 space-y-2 text-sm text-gray-700 leading-relaxed">
                    <p>La aparición de una persona, artista, marca, creador, proyecto o producto <strong>no significa necesariamente que exista una relación comercial o acuerdo</strong> con Quién Manda.</p>
                    <p>Algunos perfiles pueden ser creados utilizando información pública o enviada por usuarios.</p>
                    <p>Los titulares pueden solicitar correcciones, reclamaciones o revisión del perfil mediante los mecanismos disponibles.</p>
                </div>
            </div>

            {{-- 18. Moderation --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6">
                <h2 class="text-lg font-extrabold">18. Podemos moderar perfiles y actividad</h2>
                <div class="mt-3 space-y-2 text-sm text-gray-700 leading-relaxed">
                    <p>Quién Manda puede revisar perfiles, enlaces, pagos y actividad cuando existan indicios de fraude, abuso, suplantación, contenido ilegal o incumplimiento de las reglas.</p>
                    <p>La plataforma podrá suspender temporalmente determinadas operaciones mientras realiza una revisión.</p>
                    <p>La moderación busca proteger la competencia y mantener la integridad del ranking.</p>
                </div>
            </div>

            {{-- 19. Automated abuse --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6">
                <h2 class="text-lg font-extrabold">19. No se permite manipular el sistema</h2>
                <div class="mt-3 space-y-2 text-sm text-gray-700 leading-relaxed">
                    <p>No está permitido utilizar bots, automatizaciones abusivas, fraude de pagos o técnicas destinadas a manipular artificialmente posiciones, clics, impresiones u otras métricas.</p>
                    <p>Quién Manda puede invalidar actividad detectada como fraudulenta o artificial.</p>
                </div>
            </div>

            {{-- 20. Historical results --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6">
                <h2 class="text-lg font-extrabold">20. Resultados históricos</h2>
                <div class="mt-3 space-y-2 text-sm text-gray-700 leading-relaxed">
                    <p>Quién Manda puede conservar resultados históricos de períodos terminados.</p>
                    <p>Estos registros permiten mostrar quién lideró cada competencia y cómo terminó el ranking.</p>
                    <p>Si posteriormente se requiere una corrección válida, la plataforma debe conservar trazabilidad entre el resultado original y su revisión.</p>
                </div>
            </div>

        </section>

        {{-- EJEMPLOS --}}
        <section class="mt-12">
            <h2 class="text-xl font-extrabold text-center">Ejemplos</h2>

            {{-- Ejemplo 1 --}}
            <div class="mt-6 rounded-2xl border border-gray-200 bg-white p-6">
                <h3 class="font-extrabold text-[#F53003]">Ejemplo 1: Subir al #1</h3>
                <div class="mt-3 rounded-xl bg-gray-50 border border-gray-200 px-4 py-3 text-sm space-y-1">
                    <p>· #1 tiene <strong>$25.000</strong></p>
                    <p>· Tu perfil tiene <strong>$10.000</strong></p>
                    <p>· Necesitas superar <strong>$25.000</strong></p>
                </div>
                <p class="mt-3 text-sm text-gray-700">
                    <span class="font-bold text-[#F53003]">→ Subir al #1 por $16.000</span><br>
                    Si el pago se confirma mientras el ranking permanece igual, el perfil pasará a tener <strong>$26.000</strong> y quedará #1.
                </p>
            </div>

            {{-- Ejemplo 2 --}}
            <div class="mt-4 rounded-2xl border border-gray-200 bg-white p-6">
                <h3 class="font-extrabold text-[#F53003]">Ejemplo 2: Otro usuario te supera</h3>
                <div class="mt-3 rounded-xl bg-gray-50 border border-gray-200 px-4 py-3 text-sm space-y-1">
                    <p>· Tu perfil queda #1 con <strong>$26.000</strong></p>
                    <p>· Otro usuario paga $10.000 por el perfil #2</p>
                    <p>· Ese perfil pasa a <strong>$34.000</strong></p>
                </div>
                <p class="mt-3 text-sm text-gray-700">
                    Tu perfil baja al #2. <strong>No existe propiedad permanente sobre una posición.</strong>
                </p>
            </div>

            {{-- Ejemplo 3 --}}
            <div class="mt-4 rounded-2xl border border-gray-200 bg-white p-6">
                <h3 class="font-extrabold text-[#F53003]">Ejemplo 3: Pago pendiente</h3>
                <div class="mt-3 rounded-xl bg-gray-50 border border-gray-200 px-4 py-3 text-sm space-y-1">
                    <p>· Intentas pagar <strong>$20.000</strong></p>
                    <p>· Mercado Pago deja la operación pendiente</p>
                </div>
                <p class="mt-3 text-sm text-gray-700">
                    El ranking <strong>todavía no cambia</strong>. Sólo los pagos confirmados afectan oficialmente las posiciones.
                </p>
            </div>

            {{-- Ejemplo 4 --}}
            <div class="mt-4 rounded-2xl border border-gray-200 bg-white p-6">
                <h3 class="font-extrabold text-[#F53003]">Ejemplo 4: Empate</h3>
                <div class="mt-3 rounded-xl bg-gray-50 border border-gray-200 px-4 py-3 text-sm space-y-1">
                    <p>· #2 tiene <strong>$20.000</strong></p>
                    <p>· Tu perfil tiene <strong>$10.000</strong></p>
                </div>
                <p class="mt-3 text-sm text-gray-700">
                    <span class="line-through text-gray-400">Pagar $10.000 garantiza el #2.</span><br>
                    <strong>Regla correcta:</strong> No necesariamente. Para superar debes cumplir la regla de desempate vigente y normalmente alcanzar un total <strong>superior</strong>.
                </p>
            </div>
        </section>

        {{-- FAQ --}}
        <section class="mt-12">
            <h2 class="text-xl font-extrabold text-center">Preguntas frecuentes</h2>

            <div class="mt-6 space-y-3">
                <details class="rounded-2xl border border-gray-200 bg-white overflow-hidden">
                    <summary class="px-6 py-4 font-bold cursor-pointer hover:bg-gray-50 transition">¿Estoy comprando el puesto #1?</summary>
                    <div class="px-6 pb-4 text-sm text-gray-700 leading-relaxed">
                        No. Estás comprando <strong>visibilidad promocional</strong> que puede llevar al perfil al #1 según el estado del ranking al momento de confirmación. Otros usuarios pueden superarlo después.
                    </div>
                </details>

                <details class="rounded-2xl border border-gray-200 bg-white overflow-hidden">
                    <summary class="px-6 py-4 font-bold cursor-pointer hover:bg-gray-50 transition">¿Qué pasa si pago y alguien me supera inmediatamente?</summary>
                    <div class="px-6 pb-4 text-sm text-gray-700 leading-relaxed">
                        La operación sigue siendo válida. Ser superado posteriormente es parte normal de la competencia.
                    </div>
                </details>

                <details class="rounded-2xl border border-gray-200 bg-white overflow-hidden">
                    <summary class="px-6 py-4 font-bold cursor-pointer hover:bg-gray-50 transition">¿Mi pago se entrega al perfil?</summary>
                    <div class="px-6 pb-4 text-sm text-gray-700 leading-relaxed">
                        No necesariamente. El pago corresponde al <strong>servicio de visibilidad</strong> ofrecido por Quién Manda y no implica una transferencia de dinero al perfil promocionado.
                    </div>
                </details>

                <details class="rounded-2xl border border-gray-200 bg-white overflow-hidden">
                    <summary class="px-6 py-4 font-bold cursor-pointer hover:bg-gray-50 transition">¿Un pago pendiente cuenta?</summary>
                    <div class="px-6 pb-4 text-sm text-gray-700 leading-relaxed">
                        No. Sólo las operaciones oficialmente confirmadas pueden afectar el ranking.
                    </div>
                </details>

                <details class="rounded-2xl border border-gray-200 bg-white overflow-hidden">
                    <summary class="px-6 py-4 font-bold cursor-pointer hover:bg-gray-50 transition">¿Quién Manda garantiza clics o ventas?</summary>
                    <div class="px-6 pb-4 text-sm text-gray-700 leading-relaxed">
                        No. El servicio compra <strong>visibilidad dentro del ranking</strong>, no resultados comerciales garantizados.
                    </div>
                </details>

                <details class="rounded-2xl border border-gray-200 bg-white overflow-hidden">
                    <summary class="px-6 py-4 font-bold cursor-pointer hover:bg-gray-50 transition">¿Puedo pedir que eliminen un perfil?</summary>
                    <div class="px-6 pb-4 text-sm text-gray-700 leading-relaxed">
                        Los titulares o representantes autorizados pueden solicitar revisión escribiendo a <a href="mailto:{{ config('legal.contact.email') }}" class="text-[#F53003] underline">{{ config('legal.contact.email') }}</a>.
                    </div>
                </details>
            </div>
        </section>

        {{-- LEGAL REFERENCE --}}
        <div class="mt-10 rounded-2xl bg-gray-50 border border-gray-200 px-5 py-4 text-center text-sm text-gray-600">
            Estas reglas explican el funcionamiento del ranking. Para las condiciones contractuales completas revisa nuestros
            <a href="{{ route('legal.terms') }}" class="text-[#F53003] font-bold underline">Términos y Condiciones</a>.
        </div>

    </main>

    @include('partials.site-footer')
</div>
@endsection
