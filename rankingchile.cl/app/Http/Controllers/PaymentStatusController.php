<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class PaymentStatusController extends Controller
{
    public function pending(string $slug): JsonResponse
    {
        return response()->json([
            'status' => 'pending_confirmation',
            'slug' => $slug,
        ]);
    }
}
