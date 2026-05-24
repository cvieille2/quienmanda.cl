# Prioridad URLs Infomoteles
## Fecha: 2026-05-17

## Regla base
- La home debe ser prioridad 1 aunque no aparezca en el sitemap.
- Primero se auditan URLs con intención transaccional o local clara.
- Si una URL esta en `uncategorized/`, con typo o duplicada, se marca como problema antes de cualquier optimizacion cosmética.

## Alta prioridad
- `https://infomoteles.cl/` - Home. Falta en sitemap, error grave.
- `https://infomoteles.cl/santiago/moteles-para-parejas-en-santiago-de-chile/` - Intencion alta.
- `https://infomoteles.cl/santiago/encontrar-moteles-cerca-del-aeropuerto-de-santiago/` - Intencion local fuerte.
- `https://infomoteles.cl/concepcion/cerca-del-aeropuerto-de-concepcion/` - Intencion local fuerte.
- `https://infomoteles.cl/concepcion/moteles-cerca-del-terminal-collao/` - Intencion local especifica.
- `https://infomoteles.cl/la-serena/que-moteles-hay-en-la-serena-cerca-del-aeropuerto/` - Long tail rescatable.
- `https://infomoteles.cl/la-cisterna/moteles-en-americo-vespucio-con-gran-avenida-explorando-opciones/` - Zona/comuna prioritaria.
- `https://infomoteles.cl/blog/guia-de-moteles-en-santiago/` - Contenido pilar.
- `https://infomoteles.cl/preguntas-frecuentes/cuanto-cuesta-un-motel-en-chile/` - Evergreen.
- `https://infomoteles.cl/blog/motel-economico-cerca-de-mi-ubicacion/` - Transaccional.
- `https://infomoteles.cl/blog/encontrar-motel-por-3-horas-santiago-centro/` - Transaccional.

## Media prioridad
- `https://infomoteles.cl/maipu/motel-aerotel-express-maipu/`
- `https://infomoteles.cl/providencia/motel-apolo-providencia/`
- `https://infomoteles.cl/san-miguel/motel-camping-san-miguel/`
- `https://infomoteles.cl/santiago/motel-carmen-santiago/`
- `https://infomoteles.cl/recoleta/motel-el-buda-recoleta/`
- `https://infomoteles.cl/quinta-normal/motel-la-casa-blanca-carrascal-quinta-normal/`
- `https://infomoteles.cl/pudahuel/motel-aerotel-tradition-pudahuel/`
- `https://infomoteles.cl/san-bernardo/motel-la-boheme-san-bernardo/`
- `https://infomoteles.cl/vina-del-mar/motel-bahia/`
- `https://infomoteles.cl/valparaiso/motel-boston-valparaiso/`
- `https://infomoteles.cl/temuco/motel-new-tekena/`
- `https://infomoteles.cl/puerto-montt/motel-el-tepual-puerto-montt/`

## Baja prioridad
- `https://infomoteles.cl/vina-del-mar/motel-marina-vina-del-mar/` - Duplicada exacta.
- `https://infomoteles.cl/la-florida/motel-niagara-la-florida-guia/` - Variante debil.
- `https://infomoteles.cl/uncategorized/luz-de-luna-motel-el-monte/` - Categoria basura.
- `https://infomoteles.cl/uncategorized/cabanas-y-motel-los-placeres-placilla/` - Categoria basura.
- `https://infomoteles.cl/uncategorized/motel-krisaos-calle-larga/` - Categoria basura.
- `https://infomoteles.cl/uncategorized/motel-sanit-valentine-pere-las-casas/` - Typo o slug roto.
- `https://infomoteles.cl/concepcion/bulnes/` - Hueca.
- `https://infomoteles.cl/concepcion/caracol/` - Hueca.
- `https://infomoteles.cl/concepcion/bella-luna/` - Genérica.
- `https://infomoteles.cl/concepcion/capricho/` - Genérica.
- `https://infomoteles.cl/concepcion/artis/` - Genérica.
- `https://infomoteles.cl/el-quisco/appart-hotel-barlovento/` - Slug raro.
- `https://infomoteles.cl/penco/bahia-velero-tourism-motel-tome/` - Mezcla de lugares.
- `https://infomoteles.cl/calama/residencia-capri-calama/` - Revisar valor real.
- `https://infomoteles.cl/santiago/bellas-artes-palace/` - Suena a hotel genérico.

## Hallazgos criticos
- Hay 429 URLs y 428 únicas: existe al menos una duplicada exacta.
- La arquitectura parece demasiado cargada de fichas y poco ordenada por clusters.
- `uncategorized/` no debería existir en un sitio serio.
- La home no aparece en el sitemap: eso es una falla grave de cobertura.

## Orden de trabajo
1. Auditar alta prioridad primero.
2. Revisar posts viejos con tráfico detectable antes de crear contenido nuevo.
3. Detectar canibalización entre fichas, regiones y landings.
4. Limpiar bajas prioridades, typos y duplicados.

## Tabla operativa
| URL | Keyword objetivo | Prioridad | Acción |
|---|---|---|---|
| `https://infomoteles.cl/` | moteles en Chile / moteles cerca de mi | Alta | Revisar indexacion, enlazado y foco de home.
| `https://infomoteles.cl/santiago/moteles-para-parejas-en-santiago-de-chile/` | moteles para parejas en Santiago | Alta | Validar title, H1, intención y CTA.
| `https://infomoteles.cl/santiago/encontrar-moteles-cerca-del-aeropuerto-de-santiago/` | moteles cerca del aeropuerto de Santiago | Alta | Revisar contenido, semántica local y enlaces internos.
| `https://infomoteles.cl/concepcion/cerca-del-aeropuerto-de-concepcion/` | moteles cerca del aeropuerto de Concepcion | Alta | Confirmar intención transaccional y profundidad.
| `https://infomoteles.cl/concepcion/moteles-cerca-del-terminal-collao/` | moteles cerca del terminal Collao | Alta | Revisar si compite con otras landings de Concepcion.
| `https://infomoteles.cl/la-serena/que-moteles-hay-en-la-serena-cerca-del-aeropuerto/` | moteles en La Serena cerca del aeropuerto | Alta | Auditar cobertura de consulta long tail.
| `https://infomoteles.cl/la-cisterna/moteles-en-americo-vespucio-con-gran-avenida-explorando-opciones/` | moteles en La Cisterna / Américo Vespucio | Alta | Revisar si la URL responde a una necesidad real o es relleno.
| `https://infomoteles.cl/blog/guia-de-moteles-en-santiago/` | guia de moteles en Santiago | Alta | Ver si es contenido pilar y si puede absorber canibalización.
| `https://infomoteles.cl/preguntas-frecuentes/cuanto-cuesta-un-motel-en-chile/` | cuanto cuesta un motel en Chile | Alta | Optimizar para snippet y conversion.
| `https://infomoteles.cl/blog/motel-economico-cerca-de-mi-ubicacion/` | motel economico cerca de mi ubicacion | Alta | Verificar intención local y estructura de respuestas.
| `https://infomoteles.cl/blog/encontrar-motel-por-3-horas-santiago-centro/` | motel por 3 horas Santiago centro | Alta | Revisar si captura demanda real o está inflada.

## Observacion
- Si una URL alta no tiene keyword clara, el problema es de arquitectura o de contenido, no del reporte.
- Si una keyword aparece en varias URLs, marcar canibalizacion de inmediato.
