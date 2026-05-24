# Estructura Avesnativaschilenas
## Sesion: 2026-05-22 | Tarea: T-036

## Objetivo
Clasificar el research `Keyword Stats 2026-05-22 at 13_13_12.html` por intencion de busqueda y convertir el mapa de competencia en una ruta accionable por agente.

## Resumen del dataset
- Total filas analizadas: 4617
- Comercial: 1698
- Informacional: 632
- Navegacional: 3
- Mixto/ambigua: 2284

## Criterio de clasificacion
- **Comercial**: compra, precio, venta, marca, producto, comparativa, guia de compra, segunda mano, tienda
- **Informacional**: como, que es, cuidados, alimentacion, habitat, caracteristicas, especies, significado, historia
- **Navegacional**: marcas, sitios o plataformas concretas como Mercadolibre, Amazon, redes o dominios
- **Mixto**: keywords con ambiguedad entre busqueda de informacion y compra, o con entidad + producto/contexto

## Ejemplos por intencion

### Comercial
- `accesorios habitrail`
- `aviario para pajaros`
- `bebederos para loros`
- `cacatua alba comprar`
- `codorniz viva precio`

### Informacional
- `alimentacion de loro`
- `cuidados de codorniz`
- `como cruzar canarios`
- `curiosidades de aves`
- `especies de cacatuas`

### Navegacional
- `jaulas para ninfas mercadolibre`
- `juguetes para loros mercadolibre`
- `jaula grande para pajaros mercadolibre`

### Mixto
- `agapornis variedades`
- `aguilas en la biblia`
- `alimento para perico`
- `10 condores chilenos`
- `actividades con aves`

## Enfoque de trabajo
1. Separar las keywords por intencion real.
2. Detectar que intenciones tienen hueco de contenido en la competencia.
3. Priorizar las keyword comerciales y las mixtas con clara orientacion a producto.
4. Transformar las informacionales en contenido de autoridad con CTA suaves.
5. Identificar navegacionales como señales de marca/marketplace y decidir si conviene competir o no.

## Tareas formales

| ID | Tarea | Agente ideal | Entregable | Criterio de cierre |
|---|---|---|---|---|
| T-036-ST-01 | Mapear keywords comerciales y oportunidades monetizables | `agente-consultor-seo-monetizacion` | Lista priorizada de keywords comerciales, SERP, competencia, huecos y candidatos a pagina de dinero | Queda claro que consultas atacan directo a compra |
| T-036-ST-02 | Traducir las keywords informacionales y mixtas a angulos de contenido | `agente-copywriter-conversion` | Propuesta de titulos, H2 y CTA por intencion | Cada grupo informacional tiene un angulo util y accionable |
| T-036-ST-03 | Priorizar por potencial de negocio e impacto SEO | `agente-estratega-negocio-digital` | Matriz de prioridad por intencion, dificultad e impacto | La ruta deja claro que crear primero y que dejar fuera |
| T-036-ST-04 | Auditar la SERP y el formato ganador por intencion | `agente-analista-web-cro` | Analisis de competencia: formato, UX, CTA, contenido, tabla, FAQ, rich results | Cada intencion tiene lectura de oportunidad clara |
| T-036-ST-05 | Diseñar patrones visuales por tipo de intencion | `agente-disenador-ui-web` | Propuesta visual para paginas comerciales, informacionales y mixtas | Cada tipo de pagina tiene layout recomendado |
| T-036-ST-06 | Definir malla de enlazado por intencion | `agente-especialista-enlazado-interno` | Mapa de enlaces internos que mueva de info a dinero y de dinero a autoridad | La malla empuja autoridad y conversion |
| T-036-ST-07 | Preparar template SEO/WordPress por intencion | `agente-tecnico-wordpress-automatizacion` | Reglas de plantilla, schema, meta y bloques por tipo de pagina | Queda claro como implementar cada intencion en WP |
| T-036-ST-08 | Coordinar, consolidar y cerrar la ejecucion | `agente-proyecto-avesnativaschilenas` | Resumen ejecutivo, memoria y siguiente paso | El pipeline queda trazado y medible |

## Reglas de ejecucion
- Comercial primero si hay producto y precio.
- Informacional solo si puede alimentar un hub o captar autoridad.
- Navegacional solo si aporta defensa de marca o analisis de marketplace.
- Mixto se define con la SERP: si gana producto, va a comercial; si gana explicacion, va a informacional.

## Cobertura real del sitio
Si una keyword ya tiene post o pagina en `avesnativachilenas.cl`, se marca aqui con su URL. Si no existe, queda como gap para crear o actualizar contenido.

| Intencion | Termino | Existe en el sitio | URL |
|---|---|---|---|
| Comercial | accesorios para aves | SI | `/jaulas/accesorios-para-jaulas/` |
| Comercial | aviario para pajaros | SI | `/jaulas/aviarios/` |
| Comercial | bebederos para loros | SI | `/tienda/comederos/` |
| Comercial | canarios en voladera | SI | `/jaulas/voladeras/` |
| Comercial | jaula para guacamayo | SI | `/jaulas/para-guacamayos/` |
| Informacional | alimentacion de loro | SI | `/cuidados/tipo-de-alimentacion-de-las-aves/` |
| Informacional | cuidados de codorniz | SI/Parcial | `/cuidados/` y `/jaulas/para-codornices/` |
| Informacional | especies de cacatuas | SI/Parcial | `/jaulas/para-cacatuas/` |
| Mixto | aguilas en la biblia | SI | `/aguilas/caracteristicas-de-las-aguilas-segun-la-biblia/` |
| Mixto | aves de puerto montt | SI | `/ubicacion/aves-en-puerto-montt/` |
| Navegacional | jaulas para ninfas mercadolibre | NO | - |
| Navegacional | juguetes para loros mercadolibre | NO | - |

### Regla operativa para el inventario
1. Termino con URL existente: se cita la pagina/post en la arquitectura.
2. Termino sin URL: se marca como gap y se asigna a creacion/actualizacion.
3. Termino ambiguo: se decide por la SERP y se anota el destino final.

## Resultado esperado
- Mapa de intencion por keyword.
- Oportunidades reales por tipo de SERP.
- Plan de contenido priorizado por negocio.
- Base para crear o actualizar contenido con foco en monetizacion.

## Siguiente paso
T-037: inventario completo de cobertura por keyword, con columna de existencia real en sitio, URL y tipo de accion.

## Anexo operativo
- `output/plan/T-037-cobertura-completa.csv`
- `output/plan/T-037-cobertura-completa.md`

### Resumen del anexo
- Total keywords: 4617
- SI: 3367
- SI/Parcial: 818
- NO: 432
