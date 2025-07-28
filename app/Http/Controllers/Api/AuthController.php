<?php

namespace App\Http\Controllers\Api;

use App\Contracts\AuthServiceInterface;
use App\Http\Requests\Api\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AuthController extends BaseApiController
{
    public function __construct(
        private readonly AuthServiceInterface $authService
    ) {}

    /**
     * Login user and create token
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = $this->authService->authenticate($request->pn, $request->password);
        
        if (!$user) {
            return $this->errorResponse('Invalid credentials.', 'ERR_INVALID_CREDENTIALS', 401);
        }

        $tokenData = $this->authService->createToken($user, $request->device_name);
        $role = $this->authService->transformRoleForMobile($user->auth->role);

        return $this->successResponse([
            'token' => $tokenData['token'],
            'user' => [
                'userId' => $user->user_id,
                'name' => $user->name,
                'pn' => $user->pn,
                'role' => $role
            ]
        ]);
    }

    /**
     * Refresh token
     */
    public function refresh(Request $request): JsonResponse
    {
        $request->validate([
            'device_name' => 'required|string'
        ]);

        $user = $request->user();
        $tokenData = $this->authService->refreshToken($user, $request->device_name);

        return $this->successResponse($tokenData);
    }

    /**
     * Logout user
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->authService->revokeCurrentToken($user);

        return $this->successResponse([
            'message' => 'Logout successful.',
            'errorCode' => null
        ]);
    }

    /**
     * Register for push notifications
     */
    public function registerPush(Request $request): JsonResponse
    {
        $request->validate([
            'device_token' => 'required|string',
            'platform' => 'required|string|in:ios,android'
        ]);

        $user = $request->user();
        $success = $this->authService->registerPushNotification($user, $request->validated());

        if (!$success) {
            return $this->errorResponse('Failed to register push notification.', 'ERR_PUSH_REGISTRATION_FAILED', 500);
        }

        return $this->successResponse([
            'message' => 'Push notification registration successful.',
            'errorCode' => null
        ]);
    }
}
