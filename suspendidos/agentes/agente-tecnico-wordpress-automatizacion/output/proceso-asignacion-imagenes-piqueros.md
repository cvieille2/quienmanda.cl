# Proceso de asignacion de imagenes a los piqueros

Este documento resume el paso a paso usado para dejar imagenes distintas en los posts de la categoria `Piqueros`.

## 1. Se detecto el problema

- Los cuatro posts de piqueros tenian la misma imagen destacada.
- Eso hacia que el cluster visual quedara repetido y poco util para diferenciar cada especie.

## 2. Se identificaron los posts objetivo

- `piquero-blanco`
- `piquero-cafe`
- `piquero-de-patas-coloradas`
- `piquero-peruano`

## 3. Se busco si ya existian imagenes propias en WordPress

- Se reviso la biblioteca de medios con busqueda por nombre.
- No habia imagenes propias para esas especies.

## 4. Se buscaron imagenes reales de apoyo

- Se consulto Wikimedia Commons para encontrar fotos reales de cada especie.
- Se eligieron imagenes distintas y coherentes con cada ave.

## 5. Se evitaron descargas directas pesadas

- La descarga de originales genero rate limit en un caso.
- Se paso a usar thumbnails recomendados por Wikimedia para evitar bloqueos.

## 6. Se preparo cada imagen para WordPress

- Cada imagen se recorto a formato destacado 16:9.
- Se dejo en `1200x675` para una carga razonable y buena visualizacion.
- Se mejoro levemente contraste y nitidez.

## 7. Se subieron las imagenes a WordPress

- Se subieron via REST API a `/wp-json/wp/v2/media`.
- Se completo `title`, `alt_text`, `caption` y `description`.

## 8. Se asigno cada imagen destacada

- Se actualizo `featured_media` en cada post.
- Cada especie quedo con una imagen distinta.

## 9. Se verifico el resultado

- `piquero-blanco` -> media `14158`
- `piquero-cafe` -> media `14159`
- `piquero-de-patas-coloradas` -> media `14160`
- `piquero-peruano` -> media `14161`

## 10. Resultado final

- Los posts ya no comparten la misma destacada.
- El cluster de piqueros quedo visualmente diferenciado y mas util para SEO interno.
