<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Api\BaseApiController;

class TestController extends BaseApiController
{
    public function ping(): JsonResponse
    {
        return response()->json(['status' => 'ok', 'time' => now()]);
    }
    
    public function assignments(): JsonResponse
    {
        try {
            // Simple count without relationships
            $count = \App\Models\DeviceAssignment::count();
            
            return response()->json([
                'status' => 'ok',
                'total_assignments' => $count
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'ERR_TEST_FAILED', 500);
        }
    }
}
