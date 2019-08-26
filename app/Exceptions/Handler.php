<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param Exception $exception
     * @return void
     * @throws Exception
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param Request $request
     * @param Exception $exception
     * @return Response|JsonResponse
     */
    public function render($request, Exception $exception)
    {
        if (env('APP_DEBUG')) {
            return parent::render($request, $exception);
        }

        $status = Response::HTTP_INTERNAL_SERVER_ERROR;

        if ($exception instanceof MethodNotAllowedHttpException) {
            $status = Response::HTTP_METHOD_NOT_ALLOWED;
            $exception = new MethodNotAllowedHttpException([],
                'HTTP_METHOD_NOT_ALLOWED', $exception);
        }
        elseif ($exception instanceof NotFoundHttpException) {
            $status = Response::HTTP_NOT_FOUND;
            $exception = new NotFoundHttpException('HTTP_NOT_FOUND', $exception);
        }
        elseif ($exception instanceof AuthorizationException) {
            $status = Response::HTTP_FORBIDDEN;
            $exception = new    AuthorizationException('HTTP_FORBIDDEN', $status);
        }
        elseif ($exception instanceof \Dotenv\Exception\ValidationException && $exception->getResponse()) {
            $status = Response::HTTP_BAD_REQUEST;
            $exception = new \Dotenv\Exception\ValidationException('HTTP_BAD_REQUEST',
                $status, $exception);
        }

        return response()->json([
            'success' => false,
            'message' => $exception->getMessage()],
            $status);
    }
}
