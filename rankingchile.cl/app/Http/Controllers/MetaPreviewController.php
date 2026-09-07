<?php

namespace App\Http\Controllers;

use App\Services\MetaPreviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MetaPreviewController extends Controller
{
    public function __construct(private MetaPreviewService $service) {}

    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'url' => ['required', 'string', 'max:500'],
        ]);

        return response()->json($this->service->preview($data['url']));
    }
}
