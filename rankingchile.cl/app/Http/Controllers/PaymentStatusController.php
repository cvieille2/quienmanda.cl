<?php

namespace App\Http\Controllers;

use App\Enums\SupportTransactionStatus;
use App\Models\SupportTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentStatusController extends Controller
{
    public function pending(Request $request, string $slug): JsonResponse|View
    {
        $transaction = $this->resolveTransaction($request);
        $status = $transaction?->status ?? SupportTransactionStatus::Pending;
        $payload = [
            'status' => $status === SupportTransactionStatus::Pending ? 'pending_confirmation' : $status->value,
            'slug' => $slug,
        ];

        if ($request->expectsJson()) {
            return response()->json($payload);
        }

        return view('payments.status', [
            'slug' => $slug,
            'status' => $status,
            'transaction' => $transaction,
        ]);
    }

    private function resolveTransaction(Request $request): ?SupportTransaction
    {
        $paymentId = $request->query('payment_id');

        if (! is_string($paymentId) || $paymentId === '') {
            return null;
        }

        return SupportTransaction::where('provider_transaction_id', $paymentId)->first();
    }
}
