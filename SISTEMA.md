# Sistema de Agentes — Microagencia Digital

## Meta
Convertir $100.000 CLP en $1.000.000 CLP mensual recurrente usando activos digitales existentes, no inversión financiera.

## Propietario
Cristian Vieille — arquitecto de software, docente, creador de sitios web de nicho y emprendedor digital. Su fortaleza no es el dinero sino su capacidad técnica (SEO, WordPress, automatización, desarrollo, contenido, docencia).

## Activos digitales
| Sitio | Nicho | Prioridad |
|---|---|---|
| infomoteles.cl | Directorio moteles Chile | Alta — monetización B2B directa |
| visitandopuntaarenas.cl | Turismo Punta Arenas | Alta — artículos y fichas |
| turismoencajondelmaipo.cl | Turismo Cajón del Maipo | Media — mismo modelo que VPA |
| avesnativaschilenas.cl | Aves chilenas | Baja — tráfico y autoridad a largo plazo |
| aventurasenelagua.cl | Pesca / aventura | Baja — afiliación Amazon |

## Equipo de agentes (9)
| Agente | Tipo | Output (qué produce, no qué sabe) |
|---|---|---|
| Director de Agencia Digital | Principal | Decisiones ejecutables, no planes. Convierte toda solicitud en tareas con output concreto y medible |
| Project Manager Infomoteles | Proyecto | Coordina ejecución en infomoteles.cl. Su output es "ficha publicada", "title actualizado", "canónica corregida" |
| Consultor SEO y Monetización | Especialista | Listas priorizadas: "estos 5 titles cambia por estos", "estas 3 páginas necesitan canónica", "estos moteles contactar" |
| Copywriter de Conversión | Especialista | Textos listos para copiar: titles, meta descriptions, H1, extractos, páginas completas. Nada de "recomendaciones" |
| Analista Web CRO | Especialista | Diagnósticos con acción: "esta página tiene X problema, el cambio es Y, el impacto esperado es Z" |
| Especialista en Enlazado Interno SEO | Especialista | Malla de enlaces implementable: "de la página A agrega enlace a B con ancla C" |
| Implementador WordPress | Especialista | Conecta a WordPress via REST API, aplica cambios reales (titles, content, imágenes, redirects). Es quien ejecuta |
| Vendedor Ejecutor B2B | Especialista | Produce mensajes de outreach, guiones de llamada, secuencias de follow-up, y trackea pipeline. Su output se envía, no se archiva |
| Analista de Métricas | Especialista | Extrae datos reales de GA4 + SC, compara antes/después, detecta si los cambios funcionaron o no |

### Suspendidos (se reactivan cuando aplique)
| Agente | Motivo |
|---|---|
| PM visitandopuntaarenas.cl, PM turismoencajondelmaipo.cl, PM avesnativaschilenas.cl, PM aventurasenelagua.cl, PM SEO Local | No hay ejecución activa en esos frentes. Su trabajo era documentar, no vender. Se reactivan cuando haya presupuesto o campaña activa |
| Estratega de Negocio Digital | Dice "qué no hacer" pero no ejecuta. Su función la absorbe el Director |
| Asesor Estratégico-Financiero — Roberto Gamboa Aguilar | Valida modelos, pero no genera ingresos directos. Se consulta puntualmente si hay duda financiera |
| Mentor de Ventas B2B | Sus guiones los ejecuta ahora el Vendedor Ejecutor B2B |
| Pauta Digital — Ana Ivars y Patrick Wind | Sin oferta validada ni presupuesto de pauta. Se reactiva cuando haya campaña paga |
| Referente Vilma Núñez | Estrategia de infoproductos para cuando haya tráfico y audiencia. Hoy no aplica |
| Estratega de Lanzamientos — Álex Izquierdo | No hay lanzamiento en curso |
| Diseñador UI Web | Mejoras visuales sin conversión no generan plata. Se reactiva cuando CRO lo requiera |

## Jerarquía
```
Cristian (propietario)
  └── Director de Agencia Digital (decide, no planifica)
        ├── Project Manager Infomoteles (coordina ejecución)
        ├── Consultor SEO y Monetización (encuentra qué hacer)
        ├── Copywriter de Conversión (produce texto final)
        ├── Analista Web CRO (mide y recomienda cambios)
        ├── Especialista en Enlazado Interno SEO (diseña malla)
        ├── Implementador WordPress (ejecuta cambios reales)
        ├── Vendedor Ejecutor B2B (vende, no teoriza)
        └── Analista de Métricas (compara resultados)
```

## Regla central
Ningún especialista ni Project Manager entrega la decisión final. Solo el Director decide.

## Regla de monetizacion
Siempre que una accion, oferta o propuesta busque monetizar, primero se debe definir el buyer persona y la etapa exacta del customer journey en la que esta (descubrimiento, consideracion, decision, retencion o recomendacion). Sin eso, no se prioriza la accion.

## Regla de caja
La meta de $1.000.000 CLP/mes solo se considera en ruta si la tarea puede demostrar un camino corto a venta, setup cobrado o ingreso recurrente. SEO, contenido o diseño que no acerquen a caja quedan en segundo plano.

## Regla de feedback
Todo feedback dirigido al Director debe incluir al menos una opcion de mejora o una posible nueva rama de negocio, aunque el pedido original sea solo de revision o aprobacion.

## Regla de validacion critica
El Director no aprueba propuestas a la primera. Toda propuesta debe demostrar con evidencia que es valida; si mezcla hecho con hipotesis, si no separa estados, o si no trae criterio de cierre por URL/pagina, se rechaza y se devuelve para rehacer.

## Regla de problemas (flujo)
Cada agente debe reportar los problemas reales que encontro al ejecutar (no solo teoria). Esto es obligatorio en cada entregable/feedback.

Formato minimo:
- Problema (1 linea)
- Evidencia (error exacto, URL, captura textual, status code)
- Impacto (que impidio o degrado)
- Mitigacion (que intento y resultado)
- Siguiente accion recomendada (con un criterio de "listo" / "no listo")

## Regla de anuncios
En fichas de motel, los bloques de AdSense solo pueden ir en zonas de baja friccion: despues de la descripcion inicial y/o despues de ubicacion. Nunca pegados al hero, a los botones de contacto, ni entre el CTA principal y la informacion clave.


## Personas de referencia
- Roberto Gamboa Aguilar → asesor estratégico-financiero
- Vilma Núñez → estrategia de pauta, infoproductos, arquitectura de conversión y monetización multicapa
- Ana Ivars → Meta Ads y optimización de embudos de venta
- Patrick Wind → algoritmos, escalado y formación en pauta
- Álex Izquierdo → lanzamientos de productos digitales y formación de media buyers

## Flujo general (execution-first)
1. Cristian entrega solicitud, sitio, idea o problema
2. Director identifica objetivo y convoca los agentes mínimos necesarios
3. Cada agente entrega output **directamente ejecutable**: texto final, código, datos, mensaje listo para enviar
4. Director decide qué se implementa y qué no
5. Implementador WordPress aplica los cambios o Vendedor Ejecutor envía los mensajes
6. Analista de Métricas mide resultado con datos reales (no estimaciones)
7. Si no hay mejora medible en 7 días, se descarta y se prueba otra cosa

### Regla de output obligatorio
Ningún agente entrega "recomendaciones", "sugerencias" o "estrategias". Todo output debe ser:
- **Copia/pega** (titles, metas, contenido)
- **Ejecutable** (código, script, comando curl)
- **Enviable** (mensaje de WhatsApp, email, guión de llamada)
- **Medible** (métrica antes/después con fuente)

Si un agente no puede producir output en ese formato, no se le convoca para esa tarea.

## Ofertas base
| Producto | Precio |
|---|---|
| Auditoría express sitio web | $49.990 a $79.990 |
| Ficha destacada básica | $29.990 mensual |
| Ficha destacada premium | $59.990 mensual |
| Setup ficha destacada + artículo | $120.000 |
| Artículo patrocinado SEO | $80.000 a $150.000 |
| Diagnóstico SEO + mejora rápida (5-7d) | $149.000 a $250.000 |
| Plan SEO local mensual | $250.000 a $500.000 |
| Optimización WordPress | $150.000 a $350.000 |

## Metas 30-45 días
| Combinación | Ingreso |
|---|---|
| 4 clientes SEO x $250.000 | $1.000.000 |
| 2 clientes SEO premium x $500.000 | $1.000.000 |
| 5 fichas premium ($299.950) + 2 artículos ($240.000) + 2 SEO ($500.000) | $1.039.950 |
| 10 fichas básicas ($299.900) + 2 artículos ($240.000) + 1 SEO ($250.000) | $1.089.900 |

## Plan 30 días
| Semana | Acción |
|---|---|
| 1 | Armar oferta irresistible con sitios propios como prueba |
| 2 | Contactar 100 negocios con sitio débil (120 contactos → 12 respuestas → 2 cierres) |
| 3 | Entregar rápido (PDF + video Loom), vender continuidad |
| 4 | Cerrar 4 clientes de $250.000 o 2 de $500.000 |

## Nichos prioritarios (orden)
1. Turismo local (con visitandopuntaarenas como prueba)
2. Moteles (con infomoteles como plataforma)
3. Profesionales de salud (por experiencia hospitalaria de Cristian)
4. Negocios con WordPress abandonado

## Presupuesto $100.000
| Rubro | Monto |
|---|---|
| Landing simple de servicios | $0 - $15.000 |
| Canva Pro / piezas comerciales | $10.000 - $15.000 |
| Hosting, dominio, correo | $15.000 - $20.000 |
| Base de datos de prospectos | $0 - $20.000 |
| Pauta local de prueba | $30.000 - $50.000 |
| Herramientas SEO | $20.000 - $30.000 |

## Estandar de fichas de motel
Toda ficha de motel en infomoteles.cl debe seguir el modelo en `input/como-debe-ser-ficha-motel.txt`. Ningun agente puede crear, modificar o revisar una ficha sin respetar ese modelo (orden visual, campos obligatorios, formato WhatsApp, checklist de calidad).
Ademas, el `extracto` es obligatorio en cada ficha: debe existir, ser unico por URL y usarse como resumen principal del hero y del meta description.

### Flujo de creacion de fichas
1. **Investigacion**: antes de escribir, buscar al motel en internet (web, Google Maps, redes, directorios). Si el motel tiene pagina web, revisar todas sus URLs relevantes para extraer informacion de servicios, habitaciones, jacuzzis, tematica, tarifas, contacto y cualquier seccion importante. Si existen estadisticas o datos cuantitativos, incluirlos.
2. Si el motel esta a menos de 20 kilometros de una ciudad distinta a su categoria principal, el primer parrafo debe incluir un enlace interno a esa ciudad y una frase que indique que tambien lo visitan parejas desde esa ciudad.
3. **Redaccion**: completar campos solo con datos verificados. NO inventar ni poner datos por poner.
4. **Entrega a SEO Local**: la ficha completa se entrega al agente SEO Local para que revise el sitio real del negocio en internet y proponga mejoras de optimizacion local.
5. **Revision**: el agente SEO Local verifica consistencia, corrige discrepancias y sugiere ajustes.
6. **Publicacion**: solo despues de la revision SEO se sube al sitio web.

### Presentacion de precios y servicios
- Si se conocen precios o servicios, se deben presentar con tablas o estructuras utiles, no como texto relleno.
- Tarifas preferidas: `Habitacion`, `Tiempo`, `Precio`, `Incluye`.
- Servicios preferidos: tabla o lista util con elementos que ayuden a decidir una salida con la pareja.
- El copy debe priorizar privacidad, comodidad, acceso y diferencia real.
- Si no hay datos suficientes, no se inventan: se indica que deben confirmarse.

### FAQ de fichas
- Las preguntas frecuentes deben salir de consultas reales de Search Console para la ficha y para la categoria padre.
- La FAQ debe ayudar al visitante a decidir, no repetir texto generico.
- No canibalizar: las preguntas deben reforzar la intencion de la ficha, no competir con la categoria o con otra URL.
- Se recomiendan 3 a 5 preguntas por ficha, solo si hay busqueda real o valor practico claro.
- Si no hay consultas relevantes, la FAQ se omite o se deja minima.

### Opinion y cierre editorial
- Debajo de la opinion editorial propia debe incluirse una tabla breve de pros/contras o de servicios fundamentales y especiales.
- La tabla debe indicar claramente cuales servicios tiene y cuales no, para ayudar a decidir una visita en pareja.

## Reglas de no dispersión
- No crear sitios nuevos si uno existente puede vender
- No mejorar diseño por gusto si no aumenta conversión
- No automatizar lo que no ahorra tiempo o genera dinero
- No hacer auditorías gratuitas eternas
- No vender barato servicios complejos
- No abrir más de 2 campañas al mismo tiempo
- No contratar herramientas caras antes de validar ventas
- No depender de AdSense como ingreso principal
- No gastar capital en branding
- No lanzar pauta sin oferta validada
- No esperar tener marca, logo y agencia armada para empezar

## Pitch principal
"Te ayudo a transformar tu sitio web en una fuente real de visitas y consultas, partiendo por mejoras simples que Google y tus clientes sí entienden."

## Argumento comercial
"Yo no solo hago SEO. Tengo sitios propios posicionados, monetizados y trabajando en distintos nichos. Puedo ayudarte a hacer algo parecido con tu negocio."

## Prospección (mensaje inicial)
"Hola, soy Cristian. Estuve revisando tu sitio y vi oportunidades simples para que aparezca mejor en Google. No te escribo para venderte una página nueva. ¿Te puedo enviar un diagnóstico breve sin costo?"

## Estructura del proyecto
```
multiplicar-dinero/
├── SISTEMA.md              ← este archivo
├── agentes/
│   ├── agente-director-agencia-digital/     # Decide, coordina
│   ├── agente-pm-infomoteles/               # Gestiona infomoteles.cl
│   ├── agente-consultor-seo-monetizacion/   # Encuentra qué optimizar
│   ├── agente-copywriter-conversion/        # Produce texto final
│   ├── agente-analista-web-cro/             # Mide y recomienda cambios
│   ├── agente-enlazado-interno/             # Diseña malla de enlaces
│   ├── agente-implementador-wordpress/      # Ejecuta cambios reales en WP
│   ├── agente-vendedor-ejecutor/            # Vende, cierra deals
│   └── agente-analista-metricas/            # Compara resultados reales
├── input/
│   ├── enfoque.json
│   ├── enfoque.txt
│   ├── inicio.txt
│   └── sitios.txt
└── suspendidos/                             # Agentes inactivos, se reactivan cuando aplique
    ├── README.md                            # Instrucciones de reactivación
    └── agentes/                             # Sus archivos originales preservados
```

## Memoria de agentes
Cada agente tiene su propia carpeta `memoria/` con:
- `README.md` — propósito de la memoria
- `YYYY-MM-DD.md` — archivo por sesión con decisiones, entregables, riesgos y próximos pasos

## Criterios de priorización (peso 1-5)
| Criterio | Peso |
|---|---|
| Velocidad de ingresos | 5 |
| Ingreso recurrente | 5 |
| Uso de activos existentes | 5 |
| Facilidad de venta | 5 |
| Esfuerzo requerido | 4 |
| Riesgo | 4 |
| Margen | 4 |
