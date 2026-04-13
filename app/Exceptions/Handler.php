<?php

namespace App\Exceptions;

use Throwable;
use App\Traits\ApiResponder;
//use Sentry\Laravel\Facade\Sentry;
use Sentry\Laravel\Facade as Sentry;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Support\MessageBag;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;


class Handler extends ExceptionHandler
{
    use ApiResponder;
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

    public function report(Throwable $exception)
	{
		if( $this->shouldReport( $exception ) && app()->bound("sentry") ) { app("sentry")->captureException( $exception ); }
		parent::report($exception);
	}

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
//            if ($this->shouldReport($e)) {
//                Sentry::captureException($e);
//            }
//            parent::report($e);
        });
    }

    public function render($request, Throwable $exception)
{
    if ($exception instanceof MethodNotAllowedHttpException) {
        return response()->json([
            'success' => false,
            'message' => 'The Request method is not allowed'
        ], 405);
    }

    if ($exception instanceof AuthorizationException) {
            return $this->forbidden(__('api.forbidden'));
    }

    // Turn PHP/nginx "payload too large" into an inline validation message
    // on the form that was posted. Without this, oversized uploads would
    // render Laravel's generic 413 error page.
    if ($exception instanceof PostTooLargeException) {
        $locale = app()->getLocale() === 'ar' ? 'ar' : 'en';
        $message = $locale === 'ar'
            ? 'يجب ألا يتجاوز حجم الملف 10 ميجابايت.'
            : 'File size must not exceed 10 MB.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 413);
        }

        return redirect()->back()
            ->withErrors(new MessageBag(['upload' => $message]))
            ->withInput();
    }

    return parent::render($request, $exception);
}
}
