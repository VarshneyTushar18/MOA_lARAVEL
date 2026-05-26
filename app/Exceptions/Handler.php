<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
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
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $e)
    {
        if ($e instanceof PostTooLargeException) {
            $maxVideoMb = (int) config('upload_compression.max_video_mb', 200);
            $message = "Upload too large for the server (PHP post/upload limit). "
                ."Videos are limited to {$maxVideoMb} MB in the app; raise post_max_size and upload_max_filesize in php.ini, "
                .'or run: php artisan serve:large';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 413);
            }

            return redirect()
                ->back(302, [], route('console.dashboard'))
                ->withErrors(['file' => $message]);
        }

        return parent::render($request, $e);
    }
}
