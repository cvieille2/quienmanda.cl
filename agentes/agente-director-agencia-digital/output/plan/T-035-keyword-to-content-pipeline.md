# Plan Operativo - Keyword-to-Content Pipeline
## Sesion: 2026-05-22 | Tarea: T-035

## Objetivo
Convertir 1764 keywords del archivo `Keyword Stats 2026-05-22` en contenido monetizable para avesnativachilenas.cl con enlaces de afiliado Amazon.

## Contexto
- Se recibio archivo con 1764 keywords, todas con 50 busquedas/mes promedio
- 100% de las keywords son de intencion comercial: busqueda de productos (jaulas, accesorios, aviarios, etc.)
- El sitio ya tiene estructura `/jaulas/`, `/nidos/`, `/tienda/comederos/` pero con baja traccion (20 imp, 2 imp, 14 imp)
- Prioridad: afiliacion Amazon antes que AdSense

## Clusters de Keywords identificados
| # | Cluster | KW | Intencion | Post existente en sitio |
|---|---|---|---|---|
| 1 | Loros, Guacamayos, Cacatuas, Agapornis | 185 | COMPRA | `/loro/`, `/jaulas/para-loros/` |
| 2 | Periquitos, Pericos Australianos | 109 | COMPRA | `/jaulas/` generico |
| 3 | Ninfas / Cockatiel | 34 | COMPRA | `/jaulas/` generico |
| 4 | Canarios | 138 | COMPRA | `/cuidados/`, `/jaulas/` generico |
| 5 | Diamantes Mandarin, Gould, Finches | 9 | COMPRA | No hay post especifico |
| 6 | Cobayas, Cuyes, Conejillos India | 65 | COMPRA | No hay post |
| 7 | Hamster, Ratones, Ratas | 28 | COMPRA | No hay post |
| 8 | Gallinas, Codornices, Gallos Pelea | 239 | COMPRA | `/gallina/` |
| 9 | Palomas, Palomos, Buchones | 60 | COMPRA | `/migratorias/paloma/` |
| 10 | Jaulas Trampa, Captura | 63 | COMPRA | No hay post especifico |
| 11 | Aviarios, Voladeras, Pajareras | 236 | COMPRA | No hay post |
| 12 | Accesorios, Comederos, Juguetes | 53 | COMPRA | `/tienda/comederos/`, `/nidos/` |
| 13 | Alimentos, Salud, Suplementos | 15 | COMPRA | No hay post |
| 14 | Jaulas Decorativas, Vintage, Madera | 39 | COMPRA | `/jaulas/decorativas/` |
| 15 | Marcas: Pedros, 2gr, Vision, Hagen | 110 | COMPRA | `/jaulas/marcas/` |
| 16 | Compra/Venta/Segunda Mano | 271 | COMPRA | No hay post especifico |
| 17 | Otros genericos | 300+ | COMPRA | Varios |

## Proceso por cluster
1. **SEO**: listar keywords del cluster, buscar en sitio si existe contenido (buscar slug/URL), documentar gap
2. **SEO**: identificar productos Amazon.es relevantes por keyword (max 5 por cluster)
3. **Decision Director**: si existe post -> actualizar con KW + afiliados; si no -> crear nuevo post
4. **Copywriter**: redactar/actualizar contenido con enlaces contextuales de afiliado
5. **Tecnico**: publicar en WordPress con SEO on-page optimizado
6. **PM**: QA y cierre

## Reglas de afiliados Amazon
- Todos los posts nuevos/actualizados deben incluir enlaces de afiliado contextuales
- Usar enlace `amzn.to` o directo Amazon.es
- Disclaimer visible: "Como afiliado de Amazon, gano por compras calificadas"
- Priorizar productos con >4 estrellas y buena reputacion
- Enlace natural: "Si buscas una jaula para tu loro, te recomiendo esta..."

## Orden de ejecucion
1. SEO paralelo: clusters 1-17 se estudian simultaneamente (ST-01 a ST-12)
2. Cada SEO entrega: lista KW + match sitio + productos Amazon + gap
3. Director consolida y asigna creacion/actualizacion (ST-13)
4. Copywriter + Tecnico ejecutan por orden de prioridad
5. PM hace QA final

## Prioridad de clusters (impacto monetario estimado)
| Prioridad | Cluster | Potencial mensual estimado |
|---|---|---|
| CRITICA | Aviarios/Voladeras/Pajareras (236 KW) | $50-150 |
| CRITICA | Loros/Guacamayos (185 KW) | $40-120 |
| ALTA | Gallinas/Codornices/Gallos (239 KW) | $30-100 |
| ALTA | Canarios (138 KW) | $25-80 |
| ALTA | Accesorios (53 KW) | $20-60 |
| ALTA | Marcas/Modelos (110 KW) | $20-60 |
| MEDIA | Periquitos (109 KW) | $15-50 |
| MEDIA | Palomas (60 KW) | $10-30 |
| MEDIA | Trampas (63 KW) | $10-30 |
| MEDIA | Cobayas/Cuyes (65 KW) | $10-30 |
| BAJA | Hamster/Ratones (28 KW) | $5-15 |
| BAJA | Diamond/Gould (9 KW) | $3-10 |
| BAJA | Alimentos/Salud (15 KW) | $5-15 |

## Criterio de exito global
- 20+ posts creados o actualizados con contenido orientado a keywords
- Cada post tiene minimo 2 enlaces de afiliado Amazon contextuales
- Las paginas de compra existentes (`/jaulas/`, `/nidos/`, `/tienda/`) reciben optimizacion SEO
- Tracking de impresiones y clics en Search Console para medir impacto a 30 dias
