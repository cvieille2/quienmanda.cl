<?php

namespace Database\Seeders;

use App\Enums\Currency;
use App\Enums\PaymentGateway;
use App\Enums\SupportTransactionStatus;
use App\Enums\SupportTransactionType;
use App\Models\Profile;
use App\Models\RankingPeriod;
use App\Models\SupportTransaction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Transacciones DEMO de tipo 'real' ya approvals para que el ranking (Fase 2) se vea poblado al desarrollar.
 * NO es parte del seed canónico de producción: ejecutar solo local/demo (`php artisan db:seed --class=DemoTransactionsSeeder`).
 * Respeta límites oficiales: monto por tx entre $1.000 y $500.000; supporter_count = COUNT DISTINCT payer_reference_hash (C-07).
 */
class DemoTransactionsSeeder extends Seeder
{
    public function run(): void
    {
        $period = RankingPeriod::where('status', 'active')->first()
            ?? RankingPeriod::first();

        if (! $period) {
            $this->command?->warn('No hay periodo activo. Corré InitialPeriodSeeder primero.');
            return;
        }

        $profiles = Profile::where('status', 'active')->orderBy('id')->get();
        if ($profiles->isEmpty()) {
            $this->command?->warn('No hay perfiles activos. Corré TestProfilesSeeder primero.');
            return;
        }

        // Distribución: el primer perfil lidera, el resto escalones decrecientes.
        $base = [43000, 39000, 22000, 18200, 12900, 9400, 7100, 5600, 3900, 2200, 1500, 1000];
        $now = now();

        foreach ($profiles as $i => $profile) {
            $total = $base[$i] ?? 1000;
            $remaining = $total;
            $payerIndex = 0;

            // Genera "tranches" para simular supporters distintos y montos por perfil.
            while ($remaining > 0) {
                $tranche = min(rand(1000, 5000), $remaining);
                $remaining -= $tranche;

                $payerRef = hash('sha256', 'demo_payer_' . $i . '_' . $payerIndex);

                SupportTransaction::create([
                    'ranking_period_id'       => $period->id,
                    'profile_id'              => $profile->id,
                    'amount_clp'              => $tranche,
                    'currency'                => Currency::CLP,
                    'type'                    => SupportTransactionType::Real,
                    'status'                  => SupportTransactionStatus::Approved,
                    'provider_transaction_id' => 'demo_' . Str::ulid(),
                    'external_reference'      => (string) Str::ulid(),
                    'payment_gateway'         => PaymentGateway::MercadoPago,
                    'gateway_status'          => 'approved',
                    'payer_reference_hash'    => $payerRef,
                    'is_payer_identity_resolved' => true,
                    'payer_age_declared_18'   => true,
                    'is_anonymous'            => true,
                    'checkout_created_at'     => $now,
                    'provider_approved_at'    => $now,
                    'webhook_received_at'     => $now,
                    'ranking_qualified_at'    => $now->copy()->subMinutes($i * 10 + $payerIndex),
                ]);

                $payerIndex++;
            }
        }
    }
}
