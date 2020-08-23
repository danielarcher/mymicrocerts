<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        #AuthorizationException::class,
        HttpException::class,
        #ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param Throwable $exception
     *
     * @return void
     * @throws Exception
     */
    public function report(Throwable $exception)
    {
        if ($exception instanceof ValidationException) {
            Log::error($exception->getMessage(), $exception->validator->errors()->toArray());
        }
        if ($this->shouldReport($exception)){
            Log::error($exception->getMessage());
        }
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param Request   $request
     * @param Throwable $exception
     *
     * @return Response|JsonResponse
     * @throws Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof NotFoundHttpException) {
            return response()->json([
                'errors' => [
                    [
                        'description' => 'Resource not found',
                        'code'        => $exception->getStatusCode(),
                    ]
                ]
            ], Response::HTTP_NOT_FOUND);
        }

        if ($exception instanceof TooManyRequestsHttpException) {
            return response()->json([
                'errors' => [
                    [
                        'description' => 'Rate limit exception',
                        'code'        => $exception->getStatusCode(),
                    ]
                ]
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        if ($exception instanceof ValidationException) {
            foreach ($exception->validator->errors()->toArray() as $field => $error) {
                $errors[] = [
                    'param'       => $field,
                    'description' => $error[0],
                    'code'        => 0,
                ];
            }

            return response()->json([
                'errors' => $errors ?? []
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($exception instanceof UnauthorizedException) {
            return response()->json([
                'errors' => [
                    [
                        'description' => 'Invalid Login',
                        'code'        => $exception->getCode(),
                    ]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        return parent::render($request, $exception);
    }
}
