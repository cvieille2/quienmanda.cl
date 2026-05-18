# BRIEF DE ADAPTACION — Fichas de Motel en InfoMoteles.cl

**Fecha**: 2026-05-16
**Emite**: Director de Agencia Digital
**Base**: `input/como-debe-ser-ficha-motel.txt`
**Destinatarios**: SEO, Tecnico, Disenador, Copywriter, CRO, PM Infomoteles, Enlazado Interno

---

## DECISION DEL DIRECTOR

Se adapta el diseno y estructura de las fichas de motel siguiendo las recomendaciones del analisis competitivo. Las fichas dejaran de ser "entradas de blog/directorio" y pasaran a ser **landings transaccionales locales** con datos verificables, schema correcto y estructura profesional.

---

## CAMBIOS POR AGENTE

### 1. CONSULTOR SEO — Estructura de URL y SEO on-page

**Instrucciones obligatorias para cada ficha**:

| Elemento | Formato |
|---|---|
| URL | `/ciudad/nombre-motel/` ej: `/punta-arenas/motel-borde-rio/` |
| Title | `Motel [Nombre] en [Ciudad]` |
| Meta description | 145-160 caracteres con propuesta de valor y dato util |
| H1 | `Motel [Nombre] en [Ciudad]` |
| Extracto | Obligatorio en cada ficha; texto breve, unico y orientado al hero |
| H2 | 1. Descripcion, 2. Datos de ubicacion y contacto, 3. Precios y tarifas, 4. Servicios y amenidades, 5. Opiniones reales y mi opinion, 6. Preguntas frecuentes |
| Intro above the fold | Resumen corto, precio desde, horario, barrio/comuna, telefono/WhatsApp, servicios clave, fecha de verificacion |
| Keyword primaria | `[nombre motel] + [ciudad]` |
| Keywords secundarias | `motel en [ciudad]`, `motel con [servicio] en [ciudad]`, `motel por horas en [ciudad]` |

**Estructura de URL**: cambiar de URL plana a `/[region]/[ciudad]/[nombre-motel]/` con breadcrumbs.

---

### 2. TECNICO WORDPRESS — Nuevo CPT y template de ficha

**Custom Post Type**: `ficha_motel` con los siguientes campos personalizados obligatorios:

| Campo | Tipo | Ejemplo |
|---|---|---|
| nombre_motel | Texto | Motel Borde Rio |
| ciudad | Texto | Punta Arenas |
| comuna | Texto | Punta Arenas |
| direccion | Texto | Mejicana 1374 |
| telefono | Texto | +56 61 224 3874 |
| whatsapp | Texto | +569XXXXXXXX |
| url_oficial | URL | https://motelborderio.cl/ |
| geo_lat | Decimal | -53.1630 |
| geo_lng | Decimal | -70.9090 |
| precio_desde | Numero | 14000 |
| horario | Texto | 24 horas |
| servicios | Array (checkboxes) | Jacuzzi, WiFi, TV, Estacionamiento, Pago tarjeta |
| tarifas | JSON (tabla) | habitacion, tiempo, precio, incluye |
| fecha_verificacion | Fecha | 2026-05-16 |
| fuente_dato | Texto | sitio oficial / telefono / formulario |

**Regla obligatoria de contenido**:
- Toda ficha de motel debe tener `extracto` cargado. Ese texto se usa en el hero, en el meta description y como resumen editorial inicial.
- No se publica ninguna ficha sin extracto.
- El extracto debe ser unico por ficha, breve y util para SEO local.

**JSON-LD**: Implementar `@graph` con `WebPage`, `BreadcrumbList`, `Motel` en el servidor (SSR), no via JS.

**Estructura HTML de la ficha** (template PHP del CPT):
```
<article class="ficha-motel">
  <header class="hero">
    <nav breadcrumb: Chile > Ciudad > Motel>
    <h1>Motel [Nombre] en [Ciudad]</h1>
    <p class="resumen">...con datos verificados...</p>
    <div class="badges"> precio, horario, servicios top </div>
    <div class="ctas"> Ver tarifas, Llamar, WhatsApp, Como llegar </div>
    <p class="freshness">Ultima verificacion: [fecha]</p>
  </header>
   <section id="galeria">6-12 imagenes, hero no lazy</section>
   <section id="descripcion">resumen editorial unico</section>
   <section id="ubicacion-contacto">
     <h2>Datos de ubicacion y contacto</h2>
     <p>Texto corto y directo sobre como llegar, confirmar datos y contactar al motel.</p>
     [ubicacionMejorada]
   </section>
   <section id="tarifas">tabla de precios</section>
   <section id="servicios">lista de amenities</section>
   <section id="resenas">opiniones reales y opinion editorial propia</section>
   <section id="faq">FAQ en HTML simple basada en consultas reales y sin canibalizar la categoria</section>
 </article>
```

**Bloque reutilizable `ubicacionMejorada`**:
- Debe ir inmediatamente despues del bloque de descripcion.
- Debe incluir una tabla de dos columnas.
- Primera columna: nombre y contacto del motel.
- Segunda columna: ubicacion y mapa de Google Maps.
- El mapa debe ir embebido dentro del bloque y seguir cargando en lazy-load.

**Requisitos tecnicos**:
- Hero image: `fetchpriority="high"`, sin lazy load
- Imagenes secundarias: WebP/AVIF, `loading="lazy"`, width/height definidos
- Las secciones `#tarifas` y `#mapa` deben existir en todas las fichas.
- La FAQ debe ser siempre la ultima seccion.
- Iframes (mapa): lazy-load
- Breadcrumbs con schema BreadcrumbList
- Enlaces internos con `<a href>` real y anchor text descriptivo
- Sitemap XML por ciudad + sitemap index
- Canonical auto por ficha

---

### 3. DISENADOR UI — Template HTML de ficha

Redisenar el bloque de planes como template de ficha completa siguiendo el esqueleto HTML del documento de referencia (`input/como-debe-ser-ficha-motel.txt` lineas 60-153).

**Elementos clave del diseno**:
- Hero con badges de precio/servicios/horario
- CTAS principales: "Ver tarifas", "Llamar", "Enviar WhatsApp", "Como llegar"
- Galeria responsiva (grid 2-3 columnas)
- Tabla de tarifas clara y scaneable
- Seccion de resenas con estrellas
- FAQ en acordeon
- Mobile-first, responsive

**Colores**: Mantener azul corporativo #1a73e8 sobre fondo blanco.

---

### 4. COPYWRITER — Guias de copy por bloque

Cada ficha debe usar copy concreto, verificable y local. NO usar frases genericas como "descubre una experiencia inolvidable".

| Bloque | Objetivo | Ejemplo de copy |
|---|---|---|
| Hero | Decision inmediata | `Motel [Nombre] en [Ciudad], con [servicio_diferencial] y tarifas desde $[precio]. Informacion verificada, mapa y datos clave en una sola pagina.` |
| Descripcion | Diferencial real y contexto local | `Si buscas un motel en [Ciudad] con [amenidad] y acceso desde [referencia_local], este lugar destaca por [diferencial_real].` |
| Servicios | Cobertura semantica | `Entre los servicios mas buscados aqui estan [servicios_top]. Te sugerimos confirmar disponibilidad si tu prioridad es [amenidad_critica].` |
| Tarifas | Conversion | `Las tarifas orientativas parten en $[precio_desde] y cambian segun tipo de habitacion, horario y promociones vigentes.` |
| Galeria | Confianza visual | `Fotos reales del lugar para que evalues privacidad, estilo de habitacion, jacuzzi, estacionamiento y acceso.` |
| Opiniones reales y mi opinion | Confianza y decision | `Opiniones verificadas de usuarios de InfoMoteles.cl sobre limpieza, privacidad, atencion y relacion precio-beneficio, mas la lectura editorial del sitio.` |

**Estructura recomendada para Tarifas y Servicios**:
- Si hay precios conocidos, mostrarlos en una tabla simple: `Habitacion`, `Tiempo`, `Precio`, `Incluye`.
- Si no hay tabla completa, resumir el rango o la referencia publica en formato claro para un visitante que va con su pareja.
- Si hay servicios confirmados, listarlos en tabla o chips por utilidad real: `Jacuzzi`, `Estacionamiento`, `WiFi`, `Aire acondicionado`, `Habitacion con o sin jacuzzi`.
- Evitar listas vacias o adornadas: cada item debe aportar una decision de reserva.
- El tono debe ayudar a alguien que busca donde ir con su pareja: privacidad, comodidad, acceso y diferencia real.

---

### 4.1. EDITOR — Material base para escribir la ficha

Antes de redactar, el editor debe recopilar y ordenar material real de internet. No escribir con relleno ni con frases genericas.

**Fuentes a revisar**:
- Google Maps / ficha publica del negocio.
- Sitio del motel o directorios con datos de contacto. Si existe web oficial, revisar sus URLs clave para extraer servicios, habitaciones, jacuzzis, tematica, tarifas, contacto y secciones importantes.
- Resultados publicos con dirección, teléfono y horario.
- Reseñas visibles o resumen de reputación publica.

**Material minimo que debe salir de la investigacion**:
- Nombre exacto del motel.
- Ciudad y direccion.
- Telefono y/o WhatsApp si existe.
- Horario o nota de consulta previa.
- Precio o referencia de tarifa si aparece en una fuente confiable.
- 1 dato diferencial real: jacuzzi, estacionamiento, acceso, tipo de habitacion, ubicacion, etc.
- 1 observacion sobre opiniones reales o falta de reseñas visibles.

**Estructura de escritura obligatoria**:
1. Apertura corta: que es el motel y para quien sirve.
2. Si el motel esta a menos de 20 km de otra ciudad relevante, incluir un enlace interno a esa ciudad en el primer parrafo y mencionar que tambien lo visitan parejas desde esa ciudad.
3. Datos de ubicacion y contacto: nombre, telefono, direccion, horario, precio, mapa.
4. Precios: resumir lo encontrado, sin inventar.
5. Servicios: solo los que se puedan sostener con evidencia.
6. Como reservar: accion concreta y simple.
7. Opiniones reales: citar resumen publico, volumen o ausencia de reseñas visibles.

**Reglas de estilo**:
- Escribir con tono util y directo.
- No usar frases de marketing vacias.
- No inventar telefono, direccion, reseñas ni servicios.
- Si un dato no aparece, decir que no fue publicado o que debe confirmarse.
- La FAQ de cada ficha debe salir de consultas reales detectadas en Search Console para esa URL y su categoria padre.
- No usar preguntas genericas si compiten con la intencion principal de la ficha o duplican la categoria.
- La seccion de opiniones debe llevar una tarjeta visual de clientes, un resumen publico si existe y una opinion editorial propia del sitio para EEAT.
- Debajo de la opinion editorial, incluir una tabla corta de pros/contras o servicios fundamentales y especiales, indicando cuales tiene y cuales no.

---

### 5. CRO — Adaptacion de conversion

- Los CTAs principales deben estar siempre above the fold: "Ver tarifas", "Llamar", "WhatsApp"
- NO poner anuncios cerca del CTA principal ni del boton "Llamar"
- AdSense: 1 anchor movil + 2 in-page responsivos + 1 in-feed en relacionados
- Formulario minimo: solo nombre + WhatsApp
- La fecha de verificacion debe ser visible para generar confianza

---

### 6. ENLAZADO INTERNO — Nueva prioridad

Cada ficha debe recibir:
- Enlace desde el hub de ciudad correspondiente
- Enlaces desde fichas relacionadas (misma ciudad)
- Breadcrumbs con anchor text descriptivo
- Las fichas premium deben tener enlaces desde la homepage y categorias principales

Prioridad alta: auditar que las URLs nuevas sigan la estructura `/[ciudad]/[nombre-motel]/`.

---

### 7. PM INFOMOTELES — Pipeline de datos

Cada ficha requiere datos minimos antes de publicarse:
- [ ] Nombre, direccion, telefono verificados (NAP consistente)
- [ ] Ciudad + comuna correctas
- [ ] Precio desde y horario
- [ ] 5+ amenities
- [ ] Fuente del dato (sitio oficial, llamado, formulario)
- [ ] Fecha de verificacion

No publicar fichas con datos incompletos o no verificados.

---

## PIPELINE DE TRABAJO

```
Dia 1-2: SEO redefine URL structure + keywords
Dia 3: Disenador entrega template HTML de ficha
Dia 4: Tecnico implementa CPT + template + JSON-LD
Dia 5: Copywriter escribe 3 fichas piloto
Dia 6: QA tecnico (PSI, schema, indexabilidad)
Dia 7: Publicar 3 fichas piloto + activar medicion
```

---

## ARCHIVOS AFECTADOS

- `agente-tecnico-wordpress-automatizacion/output/build/T-007.md` — Actualizar CPT y shortcode
- `agente-disenador-ui-web/output/build/T-006.md` — Reemplazar bloque de planes por template de ficha
- `agente-consultor-seo-monetizacion/output/plan/T-001.md` — Actualizar estructura URL/keywords
- `agente-copywriter-conversion/output/build/T-004.md` — Actualizar copy segun nuevas guias
- `agente-director-agencia-digital/output/plan/T-008-plan-7-dias.md` — Reflejar nuevo pipeline
- `agente-especialista-enlazado-interno/` — Asignar auditoria de fichas

---

Director,
agente-director-agencia-digital
