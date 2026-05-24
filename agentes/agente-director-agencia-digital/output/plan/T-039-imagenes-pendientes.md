# Plan Operativo - Imagenes pendientes Piqueros
## Sesion: 2026-05-23 | Tarea: T-039

## Objetivo
Detectar y cerrar las URLs del cluster `Piqueros` que siguen sin imagen destacada o con inconsistencia entre auditorias, dejando la ejecucion en manos del tecnico WordPress.

## URLs con pendiente real
- `/piqueros/piquero-peruano/`
- `/piqueros/piquero-de-patas-coloradas/`

## Nota operativa
- `piquero-blanco` y `piquero-cafe` ya aparecen con imagen asignada en el proceso previo.
- Antes de tocar nada, el tecnico debe validar `featured_media` real en WordPress por la discrepancia entre documentos.

## Tareas formales

| ID | Tarea | Agente ideal | Entregable | Criterio de cierre |
|---|---|---|---|---|
| T-039-ST-01 | Validar inventario real de imagenes destacadas en Piqueros | `agente-tecnico-wordpress-automatizacion` | Listado final de URLs con estado real de imagen | No hay contradicciones entre auditoria y WordPress |
| T-039-ST-02 | Asignar o confirmar imagen destacada en piquero-peruano | `agente-tecnico-wordpress-automatizacion` | Post actualizado y verificado | La URL queda con imagen destacada valida |
| T-039-ST-03 | Asignar o confirmar imagen destacada en piquero-de-patas-coloradas | `agente-tecnico-wordpress-automatizacion` | Post actualizado y verificado | La URL queda con imagen destacada valida |
| T-039-ST-04 | QA final y cierre operativo | `agente-proyecto-avesnativaschilenas` | Acta de cierre | No quedan posts sin imagen o sin validacion |

## Criterio de exito global
- Dos URLs pendientes resueltas.
- Imagen destacada verificada en WordPress.
- Responsable tecnico y QA documentados.
