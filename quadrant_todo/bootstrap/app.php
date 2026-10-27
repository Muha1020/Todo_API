<?php

use App\Exceptions\ApiException;
use App\Exceptions\ApiExceptions;
use App\Support\ApiResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api/v1',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // the token middleware alias 
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Always answer in JSON
        $exceptions->shouldRenderJsonWhen(fn (Request $request, Throwable $e) => true);

        // Our own exceptions
        $exceptions->render(function (ApiExceptions $e, Request $request) {
            return ApiResponse::error($e->errorcode, $e->getMessage(), $e->status, $e->fields);
        });

        
        $exceptions->render(function (ValidationException $e, Request $request) {
            $fields = collect($e->errors())->map(fn ($messages) => $messages[0])->all();

            return ApiResponse::error('VALIDATION_ERROR', 'The input is not valid.', 400, $fields);
        });

        // Unknown URL
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            return ApiResponse::error('NOT_FOUND', 'The resource was not found.', 404);
        });

        // Last resort: the client gets a generic message
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($e instanceof HttpExceptionInterface) {
                return ApiResponse::error('HTTP_ERROR', 'The request failed.', $e->getStatusCode());
            }

            return ApiResponse::error('SERVER_ERROR', 'Something unexpected happened.', 500);
        });
    })
    ->create();
