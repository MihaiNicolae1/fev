<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        AuthorizationException::class,
        AuthenticationException::class,
        ValidationException::class,
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
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
     *
     * @return void
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param Throwable $e
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws Throwable
     */
    public function render($request, Throwable $e)
    {
        // Always return JSON for API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->handleApiException($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * Handle API exceptions with clean JSON responses.
     *
     * @param \Illuminate\Http\Request $request
     * @param Throwable $e
     * @return \Illuminate\Http\JsonResponse
     */
    protected function handleApiException($request, Throwable $e)
    {
        // Model not found (e.g., Record::findOrFail())
        if ($e instanceof ModelNotFoundException) {
            $modelName = class_basename($e->getModel());
            return response()->json([
                'success' => false,
                'message' => "{$modelName} not found.",
                'errors' => ['resource' => ["{$modelName} with the specified ID does not exist."]],
            ], 404);
        }

        // Route not found
        if ($e instanceof NotFoundHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Resource not found.',
                'errors' => ['resource' => ['The requested resource does not exist.']],
            ], 404);
        }

        // Validation errors
        if ($e instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        }

        // Authentication errors
        if ($e instanceof AuthenticationException) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'errors' => ['authentication' => ['You must be logged in to access this resource.']],
            ], 401);
        }

        // Authorization errors (Policy denials)
        if ($e instanceof AuthorizationException || $e instanceof AccessDeniedHttpException) {
            $message = $e->getMessage() ?: 'You do not have permission to perform this action.';
            return response()->json([
                'success' => false,
                'message' => 'Forbidden.',
                'errors' => ['authorization' => [$message]],
            ], 403);
        }

        // Other HTTP exceptions
        if ($e instanceof HttpException) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'An error occurred.',
                'errors' => ['server' => [$e->getMessage()]],
            ], $e->getStatusCode());
        }

        // Generic server errors (hide details in production)
        $message = config('app.debug') ? $e->getMessage() : 'An unexpected error occurred.';
        $response = [
            'success' => false,
            'message' => 'Server Error.',
            'errors' => ['server' => [$message]],
        ];

        // Include stack trace only in debug mode
        if (config('app.debug')) {
            $response['exception'] = get_class($e);
            $response['file'] = $e->getFile();
            $response['line'] = $e->getLine();
            $response['trace'] = collect($e->getTrace())->take(10)->toArray();
        }

        return response()->json($response, 500);
    }
}
