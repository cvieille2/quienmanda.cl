# FASE 1 — Backend, Dominio y Pagos (quienmanda.cl)

**Estado:** TASK-002 direct-write **COMPLETADO**. Laravel 13 + payload instalado y migrado en local. Tests verdes (4 passed, SQLite). Fase 0 APROBADA. Fase 1 AUTORIZADA.

## ¿Dónde vive qué?

| Qué | Dónde |
|---|---|
| **Código vivo (fuente de verdad)** | `rankingchile.cl/` — app Laravel 13, 13 migraciones canónicas, modelos, enums, servicios, controllers, routes. |
| **Payload original + entrega** | `rankingchile.cl/src/` (por archivar) y `output/quienmanda/fase1/` |
| **Handoff para el programador** | `output/handoff-programador-laravel.md` |
| **Docs de dominio (ERD, schema, specs)** | `output/quienmanda/fase0/` y `output/quienmanda/producto/` |

## Estado actual

- [x] Esqueleto Laravel 13 instalado en `rankingchile.cl/`
- [x] Payload TASK-002 aplicado (migraciones, modelos, enums, servicios, helpers, config, routes)
- [x] `php artisan migrate:fresh` en SQLite
- [x] `php artisan test` → 4 passed
- [ ] QA en MariaDB (BanaHosting real) antes de deploy
- [ ] commit de `rankingchile.cl/` en monorepo (pendiente OK del dueño)
- [ ] Credenciales MP sandbox (B8) + topic webhook (B3) + seed perfiles (B6)

## Bloqueos que dependen del dueño

| # | Bloqueo |
|---|---|
| **B8** | Credenciales MP sandbox + acceso BanaHosting |
| **B3** | Topic real del webhook MP (`payment`/`merchant_order`/`orders`) |
| **B6** | Lista seed de perfiles (10–15) o placeholders |
| **B4** | Versión de Filament y alcance de `/admin` |

## Regla de proceso

- Una sola fuente de verdad; las carpetas de fase son índices que apuntan a ella.
- Al cerrar cada gate: `git add` + `commit`.
