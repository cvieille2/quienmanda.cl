# rankingchile.cl

Plataforma de ranking de perfiles públicos de Magallanes con sistema de donaciones y verificación. Laravel 13 + PHP 8.3 + Vite 8 + Tailwind 4 + Alpine.js.

## Requisitos

- PHP 8.3+
- Node.js 20+ (para Vite)
- SQLite (desarrollo) / MariaDB (producción)

## Instalación rápida

```bash
git clone <repo-url> && cd multiplicar-dinero/rankingchile.cl
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh --seed
npm install && npm run build
php artisan test          # 124 tests, 488 assertions
php artisan serve         # http://localhost:8000
```

## Arquitectura

```
app/
├── Enums/                  # 17 Backed Enums (estados, tipos, roles, monedas, gateways)
├── Models/                 # 13 modelos Eloquent (con state machine en RankingPeriod)
├── Services/               # 13 servicios de negocio (SRP, sin lógica en controllers)
│   └── Payments/           # PaymentGatewayInterface + MercadoPagoGateway
├── Http/Controllers/       # 7 controllers delgados (solo request → servicio → response)
├── Http/Middleware/         # NoCacheLiveRanking
├── Providers/              # GatewayServiceProvider (binding de interfaces)
├── Support/                # helpers.php (audit, analytics)
└── Contracts/              # Interfaces para dependencias sustituibles

database/
├── migrations/             # 16 migraciones (CREATE TABLE puro, sin ALTERs)
├── seeders/                # 6 seeders (feature flags, periodo, settings, perfiles demo)
└── factories/              # Factories para testing

routes/
├── web.php                 # Home, perfiles, pagos, checkout local (dev)
└── api.php                 # Webhook MercadoPago, start payment, ranking API

tests/
├── Unit/                   # EnumsTest, ModelsTest, ServicesTest, ExampleTest
└── Feature/                # LocalMercadoPagoFlowTest, RankingApiTest, ExampleTest
```

## Tablas (16)

| Tabla | Descripción |
|-------|-------------|
| `ranking_periods` | Períodos de ranking (weekly/monthly/etc) con state machine |
| `profiles` | Perfiles públicos con badge, verificación y categoría |
| `support_transactions` | Donaciones (real/promotional) con tracking de gateway |
| `payment_gateway_events` | Log append-only de eventos de MercadoPago |
| `share_events` | Eventos de compartir con UTM tracking |
| `ranking_snapshots` | Snapshot inmutable del ranking al cerrar período |
| `audit_logs` | Auditoría de acciones del sistema |
| `feature_flags` | Feature flags con cache de 5s |
| `profile_submissions` | Envíos de la comunidad para aprobación |
| `profile_reports` | Reportes de contenido |
| `profile_claims` | Reclamos de propiedad de perfil |
| `users` | Usuarios administradores |
| `ranking_settings` | Configuración default singleton |
| `sessions` | Sesiones de Laravel |
| `cache` / `cache_locks` | Cache de Laravel |
| `jobs` / `job_batches` / `failed_jobs` | Cola de Laravel |

## Enums (17)

`Currency`, `PaymentGateway`, `PaymentGatewayDriver`, `PaymentGatewayConfirmationStatus`, `GatewayProcessingResult`, `ProfileStatus`, `ProfileType`, `ProfileSubmissionStatus`, `ProfileReportStatus`, `ProfileClaimStatus`, `VerificationStatus`, `SupportTransactionStatus`, `SupportTransactionType`, `RankingPeriodType`, `RankingPeriodStatus`, `UserRole`, `UserStatus`

## State Machine — RankingPeriod

```
draft → scheduled → active → closed_pending → closed → snapshotted
                                                                    ↘ cancelled
```

## Servicios principales

| Servicio | Responsabilidad |
|----------|-----------------|
| `RankingPeriodService` | Crear, transicionar y cerrar períodos |
| `RankingService` | Calcular ranking activo |
| `RankingSnapshotService` | Crear snapshots inmutables |
| `RankingSettingsService` | Configuración default singleton |
| `SupportTransactionService` | Validar montos y crear transacciones |
| `PaymentApprovalService` | Aprobar pagos via webhook/return |
| `PaymentLimitsService` | Límites de monto por período |
| `ShareService` | Generar eventos de compartir |
| `ModerationService` | Aprobar/rechazar submissions y claims |
| `AuditService` | Registrar eventos de auditoría |
| `FeatureFlagsService` | Gestionar feature flags |
| `DeltaRecalculateService` | Recálculo delta del ranking |

## API Endpoints

| Método | Ruta | Descripción | Rate Limit |
|--------|------|-------------|------------|
| GET | `/` | Homepage | — |
| GET | `/perfil/{slug}` | Perfil público | — |
| POST | `/api/pagos` | Iniciar donación | 10/min |
| GET | `/api/pagos/return` | Return del checkout | — |
| GET | `/pagos/resultado` | Página resultado | — |
| GET | `/pagos/{slug}/pendiente` | Estado pendiente | — |
| GET | `/api/ranking/current` | Ranking actual | 180/min |
| POST | `/api/webhooks/mercadopago` | Webhook MP | — |

### Checkout local (solo dev/test)

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/local/mercadopago/checkout/{token}` | Simular checkout |
| POST | `/local/mercadopago/checkout/{token}/approve` | Aprobar pago |
| POST | `/local/mercadopago/checkout/{token}/reject` | Rechazar pago |

## Variables de entorno (.env)

```dotenv
# App
APP_NAME=rankingchile
APP_URL=https://rankingchile.cl

# Base de datos (producción: MariaDB)
DB_CONNECTION=sqlite

# MercadoPago
MP_DRIVER=mercadopago          # o "local" para tests
MP_WEBHOOK_SECRET=
MP_NOTIFICATION_URL=https://rankingchile.cl/api/webhooks/mercadopago
MP_LOCAL_CACHE_TTL_HOURS=24

# Cola y cache
QUEUE_CONNECTION=database
CACHE_STORE=database
```

## Testing

```bash
php artisan test                    # Todos los tests (124)
php artisan test --filter=Unit      # Solo unit
php artisan test --filter=Feature   # Solo feature
```

Tests unitarios cubren: enums (17), modelos (13), servicios (10+), helpers, gateway local.
Tests feature cubren: flujo completo de checkout, ranking API, homepage.

## Despliegue

El proyecto usa deploy FTP via GitHub Actions a BanaHosting:

```bash
scripts/remote-deploy.sh           # Script de deploy
```

Ver `.github/workflows/deploy.yml` en la raíz del repo para la configuración CI/CD.

## Convenciones

- **No magic values**: estados, tipos, roles → Enums tipados
- **SRP estricto**: controllers delgados, servicios con una responsabilidad, repositories solo persistencia
- **State machine**: no cambiar status arbitrariamente, usar servicios dedicados
- **Contracts**: infraestructura detrás de interfaces (PaymentGatewayInterface, etc.)
- **Tests**: todo código nuevo con coverage mínimo de tests unitarios

## Licencia

Proyecto privado — todos los derechos reservados.
