<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponse
{
    /**
     * Return a success response.
     */
    protected function successResponse($data = null, string $message = '', int $status = 200): JsonResponse
    {
        $response = [
            'data' => $data,
            'message' => $message,
        ];

        return response()->json($response, $status);
    }

    /**
     * Return a paginated response.
     */
    protected function paginatedResponse(LengthAwarePaginator $paginator, string $message = ''): JsonResponse
    {
        $response = [
            'data' => $paginator->items(),
            'message' => $message,
            'continuationToken' => $paginator->hasMorePages() ? base64_encode($paginator->currentPage() + 1) : '',
            'nextURL' => $paginator->nextPageUrl() ?? '',
        ];

        return response()->json($response, 200);
    }

    /**
     * Return an error response.
     */
    protected function errorResponse(string $message, int $status = 400, $data = null): JsonResponse
    {
        $response = [
            'data' => $data,
            'message' => $message,
        ];

        return response()->json($response, $status);
    }

    /**
     * Return a not found response.
     */
    protected function notFoundResponse(string $message = 'Resource not found'): JsonResponse
    {
        return $this->errorResponse($message, 404);
    }

    /**
     * Return an unauthorized response.
     */
    protected function unauthorizedResponse(string $message = 'Unauthorized access'): JsonResponse
    {
        return $this->errorResponse($message, 403);
    }

    /**
     * Return a validation error response.
     */
    protected function validationErrorResponse(array $errors, string $message = 'Validation failed'): JsonResponse
    {
        return $this->errorResponse($message, 422, $errors);
    }

    /**
     * Return a not implemented response.
     */
    protected function notImplementedResponse(string $message = 'This endpoint is not yet implemented'): JsonResponse
    {
        return $this->successResponse(null, $message, 200);
    }
}
