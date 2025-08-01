<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

abstract class BaseApiController extends Controller
{
    /**
     * Return success response
     */
    protected function successResponse(array $data, int $status = 200): JsonResponse
    {
        return response()->json(['data' => $data], $status);
    }

    /**
     * Return error response
     */
    protected function errorResponse(string $message, string $errorCode, int $status = 400): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'errorCode' => $errorCode
        ], $status);
    }

    /**
     * Return paginated response
     */
    protected function paginatedResponse($paginator, array $additionalData = []): JsonResponse
    {
        $data = $paginator->items();
        if (isset($additionalData['data'])) {
            $data = $additionalData['data'];
            unset($additionalData['data']);
        }

        return response()->json([
            'data' => $data,
            'meta' => array_merge([
                'currentPage' => $paginator->currentPage(),
                'lastPage' => $paginator->lastPage(),
                'total' => $paginator->total(),
                'perPage' => $paginator->perPage(),
            ], $additionalData)
        ]);
    }
}
