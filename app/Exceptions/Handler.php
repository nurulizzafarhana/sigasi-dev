<?php

namespace App\Exceptions;

use App\Http\Helpers\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
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

    protected function unauthenticated($request, AuthenticationException $ex){

        if( $request->is('api/*') ) {
            return ApiResponse::unauthorized();
        }

        return redirect('/login');
    }

    function render($request, Throwable $exception)
    {
        if ($exception instanceof HttpExceptionInterface) {
            if ($exception->getStatusCode() == 403) {
                if( $request->is('api/*') ) {
                    return ApiResponse::forbidden();
                }
            }
        }
        return parent::render($request, $exception);
    }
}
