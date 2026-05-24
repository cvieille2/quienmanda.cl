# Backlog por Bloqueo - infomoteles.cl

## Criterio

- **Bloqueante**: si no se resuelve, impide medir bien, consolida mal la senal o deja el trabajo posterior sin base estable.
- **No bloqueante**: se puede ejecutar en paralelo sin esperar una dependencia critica.

## Tareas bloqueantes

| ID | Tarea | Motivo del bloqueo | Depende de |
|---|---|---|---|
| T-008-ST-34 | Unificar canonicas y variantes con slash y anclas | Si no se corrige, la senal se reparte entre versiones y el resto de optimizaciones pierde efecto. | Ninguna |
| T-008-ST-36 | Cerrar instrumentacion de conversion en GA4 | Sin eventos clave no se puede validar si los cambios mejoran conversion real. | Ninguna |
| T-008-ST-37 | Rehacer `/para-moteles/` como landing comercial | La oferta B2B no tiene base lista para convertir. | Ninguna |
| T-008-ST-39 | Redisenar malla entre fichas, hubs y paginas comerciales | Sin malla limpia, la autoridad interna no empuja a las piezas de mayor valor. | T-008-ST-34 |
| T-008-ST-40 | Priorizar fichas con traccion y revenue comprobado | Necesita canonicas y medicion limpia para elegir bien el patron ganador. | T-008-ST-34, T-008-ST-36 |

## Tareas no bloqueantes

| ID | Tarea | Por que puede ir en paralelo |
|---|---|---|
| T-008-ST-35 | Subir CTR en fichas con muchas impresiones y pocos clics | Puede arrancar ya con cambios de title/meta y snippets. |
| T-008-ST-38 | Rehacer la home como entrada comercial unica | Se puede redactar y maquetar en paralelo a la limpieza tecnica. |
| T-008-ST-41 | Re-medicion despues de ajustes SEO y CRO | Se ejecuta cuando los cambios esten arriba, pero no bloquea el trabajo previo. |
| T-008-ST-12 | Coordinar publicacion y verificacion de datos | Puede avanzar mientras se corrige la base tecnica. |
| T-008-ST-14 | Compilar metricas de la semana y reportar resultado | Es seguimiento, no dependencia. |
| T-008-ST-21 | Definir hubs de ciudad y enlaces desde posts antiguos | Puede prepararse como propuesta antes de aplicar cambios finales. |
| T-008-ST-30 | Proponer 15 enlaces contextuales hacia fichas premium | Es trabajo de mapa y no requiere despliegue inmediato. |
| T-008-ST-33 | Validacion visual tras WebP | Puede revisarse de forma paralela como control de calidad. |

## Orden sugerido

1. Cerrar T-008-ST-34 y T-008-ST-36.
2. Publicar T-008-ST-37.
3. Aplicar T-008-ST-39 y T-008-ST-40.
4. Ejecutar T-008-ST-35 y T-008-ST-38 en paralelo.
5. Medir con T-008-ST-41 y revisar si aparecieron nuevas tareas o ajustes.

## Tareas nuevas a revisar despues de cerrar bloqueantes

- Reauditar CTR de `Los Sauces`, `La Giralda`, `Yugos`, `Montavord` y `Los Gatitos`.
- Revisar si la home ya merece nueva iteracion de copy despues de publicar `/para-moteles/`.
- Confirmar si la malla interna ya empuja suficiente a `Serrano Antofagasta`, `Diamante Curico`, `Caudal Talcahuano` y `Euro Calama`.
- Detectar si surge una nueva ficha candidata con mejor revenue que las actuales.

## Observacion

Cuando las bloqueantes esten listas, hay que reabrir el backlog con nueva medicion y solo entonces decidir si salen piezas nuevas o si se reordena el foco.
