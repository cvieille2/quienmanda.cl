# Memoria — Fase 2 Frontend UX/Cro + Auditoría R1–R5 (2026-09-05)

> Contexto del sistema: `output/quienmanda/README.md` (índice central). Fase 2:
> 🟡 EN_EJECUCIÓN sobre la app Laravel 13 ya instalada y migrada en `rankingchile.cl/`.

## Resumen en 1 línea

Cerré la iteración de Frontend UX y Conversión de Fase 2 aplicando las 5 revisiones
prioritarias (R1–R5) de la auditoría del dueño, y verifiqué los flujos degradados del
ranking (vacíos, sin transacciones, pagos desactivados). 3 commits en `rankingchile.cl`.

---

## FASE_ACTUAL

- **Fase 2 — Frontend, UX y Conversión**: 🟡 EN_EJECUCIÓN (entrega de esta iteración completada y verificada).
- **Fase 0.5 — Technical Gate BanaHosting**: 🟡 EN_EJECUCIÓN (6 ítems de consola pendientes, requieren acceso hosting del dueño).
- **Fase 3+**: ⬜ BLOQUEADA por datos del dueño (B3, B4, B6, B8).

## REVISIONES_RESUELTAS (auditoría del dueño)

| Rev | Hallazgo | Fix | Archivo(s) |
|---|---|---|---|
| **R1** | `goPay()` exigía `fm.age18` en paso 1 pero la casilla vive en paso 2 | Validación de 18+ movida SOLO a `submitPayment()` (envío). Avance monto→datos libre. Validación servidor intacta. | `home.blade.php`, `profile.blade.php`, `checkout-modal.blade.php` |
| **R2** | Paso 3 podía mostrar "PAGO RECIBIDO" sin respuesta confirmada por servidor | Vista de éxito eliminada; paso 3 = "Procesando tu apoyo / no cierres esta página". La acreditación la decide solo el servidor (estado vía `/pagos/{slug}/pendiente`, JSON). | `checkout-modal.blade.php`, `submitPayment()` |
| **R3** | Riesgo de `leader` sin `position` | Controllers pasan `position`; `HomeController`/`ProfileController` mapean `$r['position']` (sin restos de `rank` desde backend). | `HomeController.php`, `ProfileController.php`, vistas |
| **R4** | CTA del líder decía "QUITARLE LA CORONA" (implica rival distinto) | CTA del líder pasa a **"🛡️ DEFENDER LA CORONA"** (hero, fila top-1, sticky). | `home.blade.php`, `checkout-modal.blade.php` |
| **R5** | Límites de monto fijos ($1.000/$500.000) + nombres en atributos/JS | Límites se toman de la configuración **congelada del periodo** (`configuration.minimum_support_clp`/`maximum_support_clp`, defaults 1000/500000) vía `limits` en los controllers. Nombres ya no se inyectan en atributos: `openCheckout(id)` resuelve en `ranking` (solo `@js($id)`, datos escapados). **Extra:** con `payments_enabled=false`, los botones APOYAR/DEFENDER por fila ahora también se ocultan. | `HomeController.php`, `ProfileController.php`, vistas, `openCheckout()` |

## PRUEBAS_Y_RESULTADOS

- `npm run build` → OK (manifest + fonts + `app-*.css/js`).
- `php artisan test` → **4 passed, 15 assertions** (ExampleTest + RankingApiTest).
- **Matriz de estados degradados verificada por HTTP (curl):**
  | Estado | HOME | PROFILE |
  |---|---|---|
  | VACÍO (0 periodos/perfiles/tx, BD temporal) | 200: "El ranking parte esta semana" + "Aún no hay apoyos" | 404 (perfil no existe) ✔ |
  | PERFILES, 0 TX, pagos ON | — | 200: APOYAR A, rank `#3` ✔ |
  | PERFILES + TX, pagos OFF | 200: 1 box "Pagos desactivados", **0 botones de acción** ✔ | — |
  | PERFILES + TX, pagos ON (estado restaurado) | 200: 15 `openCheckout`, 4 DEFENDER ✔ | — |
- Estado real restaurado: `payments_enabled=true`, BD de trabajo intacta.

## COMMITS (rama main, solo dentro de `rankingchile.cl/`)

1. `88019b8` — fix: aplicar auditoria UX R1-R5 al flujo de conversion (+76/-51).
2. `78ef995` — fix: ocultar botones de apoyo por fila cuando pagos estan desactivados.
3. `6f6c33b` — chore: ignorar `fonts-manifest.dev.json` generado por Vite dev.

## BLOQUEADORES (requieren al dueño)

- **B8** — credenciales MP sandbox + acceso BanaHosting (deploy/pruebas reales).
- **B3** — topic real del webhook MP (`orders`/`payment`/`merchant_order`).
- **B6** — lista final de perfiles seed (usando `TestProfilesSeeder` de 12 mientras tanto).
- **B4** — versión de Filament y alcance de `/admin`.
- QA MariaDB real (Fase 0.5) + 6 ítems de consola BanaHosting.

## DECISIONES_DEL_DUENO

- Autorizó avanzar Fase 2 asumiendo riesgo por falta de seed/credenciales.
- Eliminó `.git` anidado de `rankingchile.cl/` (monorepo con trackeo del árbol).
- Autorizó commit de los cambios de `rankingchile.cl/` (3 commits hechos).

## SIGUIENTE_PASO

1. **⚡ Feature nuevo APLICADO (mismo día):** indicador de posición futura en el paso 1 del modal (`Con $X quedaría en #N` + botón `Sube al #1 con $Y`). Commit `fef4c36`. Proyección exacta: `pos = 1 + count(perfiles con monto >= miMonto + apoyo)`, empate = queda detrás. `suggestedAmount` corregido a getter en `profile.blade.php`.
2. (Opcional pedido) Reporte formal en este formato entregado al dueño.
3. Desbloqueo de Fase 3: el dueño entrega B8 + B3 (+ B6/B4).
4. QA MariaDB real + 6 ítems consola BanaHosting (Fase 0.5) con acceso hosting.
</content>
</invoke>