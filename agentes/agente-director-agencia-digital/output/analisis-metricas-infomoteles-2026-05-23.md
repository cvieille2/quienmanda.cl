# Analisis de metricas - infomoteles.cl
## Fecha: 2026-05-23

## Resumen ejecutivo
- Search Console confirma traccion real en fichas locales, no en la home.
- El problema principal no es solo posicion: hay URLs con muchas impresiones y CTR bajo.
- Tambien hay fragmentacion por variantes con `slash` y `#` en varias fichas.
- GA4 muestra que las fichas con mejor traccion tambien generan ingresos, pero el recorrido posterior es irregular y muy dependiente de enlaces internos.

## Datos base
- Pags: 668 clics, 33,749 impresiones, CTR 1.98%, posicion ponderada 14.1.
- Consultas: 414 clics, 15,710 impresiones, CTR 2.64%, posicion ponderada 11.63.
- Top 10 paginas concentran 354 clics, cerca del 53% del total.
- GA4 rutas: 172 vistas, 122 usuarios activos, 1,843 eventos, 0 eventos clave y 481.67 de ingresos reportados. La conversion no esta bien instrumentada si no hay eventos clave.

## Lo que esta funcionando
- `motel-diamante-curico/`: 67 clics, 958 impresiones, 6.99% CTR, pos 1.89.
- `motel-serrano-antofagasta/`: 52 clics, 1,217 impresiones, 4.27% CTR, pos 3.31.
- `motel-caudal-talcahuano/`: 40 clics, 677 impresiones, 5.91% CTR, pos 2.35.
- `motel-y-cabanas-melosas-rauco/`: 38 clics, 558 impresiones, 6.81% CTR, pos 4.26.
- `motel-euro-calama/`: 34 clics, 640 impresiones, 5.31% CTR, pos 3.95.
- En GA4, las rutas con mas vistas e ingresos son:
- `/antofagasta/motel-serrano-antofagasta/` con 34 vistas y 120.06 de ingresos.
- `/curico/motel-diamante/` con 17 vistas y 56.96 de ingresos.
- `/calama/motel-las-cavernas-calama/` con 7 vistas y 37.75 de ingresos.
- `/concepcion/motel-caudal-talcahuano/` con 9 vistas y 34.72 de ingresos.

## Lo que esta perdiendo
| URL o query | Señal | Lectura |
|---|---|---|
| `/copiapo/motel-los-sauces` | 1,263 impresiones, 1.5% CTR | Mucha demanda, snippet flojo. |
| `/la-florida/motel-la-giralda-florida/` | 519 impresiones, 0.58% CTR | Gran oportunidad de title/meta. |
| `/puerto-montt/motel-yugos/` | 599 impresiones, 0.67% CTR | Baja respuesta comercial. |
| `/macul/motel-los-gatitos-macul/` | 586 impresiones, 0.34% CTR | Muy por debajo de su visibilidad. |
| `/` | 34 impresiones, 1 clic, pos 40.41 | La home no cierra ninguna intencion fuerte. |
| `motel yugos`, `motel montavord`, `motel entre rocas`, `motel giralda` | muchas impresiones, casi sin clics | Intencion existe, pero el resultado no convence. |
- `/quinta-normal/motel-el-refugio-de-alsino-quinta-normal/` | 12 vistas, 209s de interaccion media, 0 ingresos | Mucho consumo, cero cierre medible. |
- `/maipu/motel-paso-nevado-maipu/` | 1 vista, 347s de interaccion media | Dato ruidoso, no prioridad.

## Riesgos detectados
- Hay variantes de la misma ficha con y sin `slash`, por ejemplo `motel-los-sauces` y `motel-los-sauces/`.
- Tambien aparecen URLs con `#` como si fueran objetivos separados; eso fragmenta la lectura y ensucia la priorizacion.
- Varias paginas tienen 100+ impresiones y 0 clics: contenido visible, pero sin cierre.
- El recorrido mostrado en la captura de GA4 va desde una ficha fuerte como `Motel Serrano` hacia otras piezas internas como `Directorio de MOTELE...`, `Encuentra los motel...` y `Top Mejores Moteles...`; eso indica navegacion, pero no una ruta de conversion unica.

## Prioridades reales
1. Corregir canonicas y enlaces internos para que una sola URL concentre la senal por ficha.
2. Reescribir titles y metas en las fichas con muchas impresiones y CTR bajo.
3. Empujar las fichas ya cerca del top 3 para ganar clics rapidos.
4. Rehacer la home para una sola promesa comercial y una CTA clara.
5. Ajustar la arquitectura de enlaces internos para que el recorrido post-landing empuje a contacto o reserva, no solo a lectura secundaria.

## Acciones concretas
- Unificar variantes con y sin `slash` en una sola URL canonica.
- Dejar de usar anclas `#` como destino de enlazado interno principal.
- Ajustar snippet de `Los Sauces`, `La Giralda`, `Yugos`, `Montavord` y `Los Gatitos`.
- Priorizar `Serrano Antofagasta`, `Euro Calama`, `Caudal Talcahuano` y `Oasis de los Niches` por estar cerca de top 3.
- Medir de nuevo despues del ajuste para ver si sube CTR antes de crear mas contenido.
- Revisar CTAs y bloques relacionados en fichas que estan generando sesiones pero no eventos clave.
