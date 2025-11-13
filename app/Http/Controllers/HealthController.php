<?php

namespace App\Http\Controllers;

use App\Contracts\HealthServiceInterface;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    public function __construct(
        private HealthServiceInterface $healthService
    ) {
    }

    public function status(): JsonResponse
    {
        $status = $this->healthService->getStatus();

        return response()->json($status);
    }
}
