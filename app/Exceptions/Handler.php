<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        // Check if request is for API
        if ($request->is('api/*')) {
            return $this->handleApiException($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * Handle API exceptions with standardized response format
     */
    protected function handleApiException(Request $request, Throwable $e)
    {
        if ($e instanceof ValidationException) {
            return response()->json([
                'message' => 'Invalid data provided.',
                'errorCode' => 'ERR_VALIDATION_FAILED',
                'errors' => $e->errors()
            ], 422);
        }

        if ($e instanceof AuthenticationException) {
            return response()->json([
                'message' => 'Unauthorized access.',
                'errorCode' => 'ERR_UNAUTHORIZED'
            ], 401);
        }

        if ($e instanceof NotFoundHttpException) {
            return response()->json([
                'message' => 'Resource not found.',
                'errorCode' => 'ERR_RESOURCE_NOT_FOUND'
            ], 404);
        }

        if ($e instanceof MethodNotAllowedHttpException) {
            return response()->json([
                'message' => 'Method not allowed.',
                'errorCode' => 'ERR_METHOD_NOT_ALLOWED'
            ], 405);
        }

        if ($e instanceof TooManyRequestsHttpException) {
            return response()->json([
                'message' => 'Too many requests.',
                'errorCode' => 'ERR_RATE_LIMIT_EXCEEDED'
            ], 429);
        }

        // For any other exception in production, return generic error
        if (config('app.debug')) {
            return response()->json([
                'message' => $e->getMessage(),
                'errorCode' => 'ERR_INTERNAL_SERVER_ERROR',
                'trace' => $e->getTraceAsString()
            ], 500);
        }

        return response()->json([
            'message' => 'Internal server error.',
            'errorCode' => 'ERR_INTERNAL_SERVER_ERROR'
        ], 500);
    }
}
