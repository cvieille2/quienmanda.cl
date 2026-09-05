# entregables TASK-002 — quienmanda.cl (FASE 1 / Backend + Pagos)

Código real listo para copiarse al esqueleto Laravel del proyecto.
Estado: Laravel 13 instalado y migrado en la raíz de `rankingchile.cl/`. Tests verdes (SQLite). Docs de dominio movidos a `output/quienmanda/fase0/`.

El esqueleto se instala una vez con `composer create-project laravel/laravel` (Laravel 13 primario / 12 fallback, PHP 8.3) y luego los archivos de esta carpeta se sobrescriben en las rutas indicadas.

> **Contexto repo:** `rankingchile.cl/` es la carpeta del proyecto dentro del repo `multiplicar-dinero`.
> El CI/CD (`.github/workflows/deploy.yml` en la raíz del repo) apunta el deploy de la app a `/public_html/` de BanaHosting.
> **Handoff para el programador:** `output/handoff-programador-laravel.md`.
> Schema canónico y reglas de dominio: `output/quienmanda/fase0/`.

## Mapa de copiado (desde `src/` a la raíz del esqueleto Laravel)

### Migraciones → `database/migrations/` (rebaseline v3.1.0, 13 tablas)
| Archivo | Tabla | Orden |
|---|---|---|
| `2026_01_01_000001_create_ranking_periods_table.php` | ranking_periods | 1 |
| `2026_01_01_000002_create_profiles_table.php` | profiles | 2 |
| `2026_01_01_000003_create_support_transactions_table.php` | support_transactions (fuente de verdad, FKs RESTRICT) | 3 |
| `2026_01_01_000004_create_payment_gateway_events_table.php` | payment_gateway_events (append-only) | 4 |
| `2026_01_01_000005_create_share_events_table.php` | share_events | 5 |
| `2026_01_01_000006_create_ranking_snapshots_table.php` | ranking_snapshots (inmutable + delta recalc) | 6 |
| `2026_01_01_000007_create_audit_logs_table.php` | audit_logs | 7 |
| `2026_01_01_000008_create_feature_flags_table.php` | feature_flags | 8 |
| `2026_01_01_000009_create_profile_submissions_table.php` | profile_submissions | 9 |
| `2026_01_01_000010_create_profile_reports_table.php` | profile_reports | 10 |
| `2026_01_01_000011_create_profile_claims_table.php` | profile_claims | 11 |
| `2026_01_01_000012_create_users_table.php` | users | 12 |
| `2026_01_01_000013_create_ranking_settings_table.php` | ranking_settings | 13 |

> **No existen** `analytics_events` ni `moderation_logs` (D-046/D-047). No crear tablas `rankings`.

### Enums → `app/Enums/` (RankingPeriodStatus, RankingPeriodType, SupportTransaction*, Profile*, User*, VerificationStatus, etc.)

### Modelos → `app/Models/` (13)
`AuditLog`, `FeatureFlag`, `PaymentGatewayEvent`, `Profile`, `ProfileClaim`, `ProfileReport`, `ProfileSubmission`, `RankingPeriod`, `RankingSetting`, `RankingSnapshot`, `ShareEvent`, `SupportTransaction`, `User`.

### Servicios → `app/Services/`
- `Payments/PaymentGatewayInterface.php` y `Payments/MercadoPagoGateway.php` → `app/Services/Payments/`
- `RankingPeriodService.php`, `RankingSnapshotService.php`, `DeltaRecalculateService.php`, `RankingService.php`,
  `SupportTransactionService.php`, `PaymentApprovalService.php`, `ShareService.php`, `PaymentLimitsService.php`,
  `AuditService.php`, `FeatureFlagsService.php`, `RankingSettingsService.php`, `ModerationService.php` → `app/Services/`

### Otros
| Archivo | Destino |
|---|---|
| `app/Http/Controllers/PaymentController.php` | `app/Http/Controllers/` |
| `app/Http/Middleware/NoCacheLiveRanking.php` | `app/Http/Middleware/` |
| `app/Providers/GatewayServiceProvider.php` | `app/Providers/` |
| `app/Support/helpers.php` | `app/Support/helpers.php` + cargar en `composer.json` (`"files": ["app/Support/helpers.php"]`) |
| `config/mercadopago.php` | `config/mercadopago.php` |
| `routes/api.php` | `routes/api.php` |

## Config `.env` (en el hosting, NUNCA viaja por FTP)
```dotenv
MP_ACCESS_TOKEN=...
MP_PUBLIC_KEY=...
MP_ENV=sandbox
MP_NOTIFICATION_URL=https://www.quienmanda.cl/api/webhooks/mercadopago
MP_WEBHOOK_TOPICS=payment,merchant_order
QUEUE_CONNECTION=database
CACHE_STORE=file
```

## Pendientes de decisión para desbloquear estas piezas
- **DECISION_REQUIRED-003 (TASK-001, user / B3):** confirmar el topic real del webhook de Mercado Pago en sandbox (`payment` vs `merchant_order` vs `orders`). El código es configurable vía `MP_WEBHOOK_TOPICS`. Si resulta `merchant_order`, implementar la resolución de `payment_ids[]` por `GET /merchant_orders/{id}` en `PaymentController::webhook`.
- **Credenciales MP sandbox + acceso BanaHosting** (TASK-001, user / B8).

## Orden de instalación
1. `composer create-project laravel/laravel quienmanda` (Laravel 13/12, PHP 8.3).
2. Copiar estos archivos según el mapa.
3. `php artisan migrate --force` (probar `migrate:fresh` en local antes).
4. `php artisan db:seed` (feature_flags + periodo inicial + perfiles de prueba).
5. Configurar `config/mercadopago.php`, helpers, queue/cache.
6. Correr tests.