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
    protected function paginatedResponse($paginator, array $meta = []): JsonResponse
    {
        return response()->json([
            'data' => $paginator->items(),
            'meta' => array_merge([
                'currentPage' => $paginator->currentPage(),
                'lastPage' => $paginator->lastPage(),
                'total' => $paginator->total(),
                'perPage' => $paginator->perPage(),
            ], $meta)
        ]);
    }
}
