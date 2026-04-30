<?php

use App\Http\Middleware\BindAccountFamilyFromSession;
use App\Http\Middleware\EnsurePasswordChangedIfRequired;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\SyncCurrentFamilySession;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'auth/apple/callback',
        ]);
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'bind.account.family' => BindAccountFamilyFromSession::class,
            'sync.current.family' => SyncCurrentFamilySession::class,
            'must.change.password' => EnsurePasswordChangedIfRequired::class,
            // Spatie permission middlewares
            'permission' => PermissionMiddleware::class,
            'role' => RoleMiddleware::class,
            'roles' => RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() !== 419) {
                return null;
            }
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('Your session has expired. Please refresh the page and try again.'),
                ], 419);
            }

            return null;
        });

        $exceptions->renderable(function (PostTooLargeException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('The request body is too large. Reduce the image size or ask your host to raise PHP post_max_size and upload_max_filesize.'),
                ], 413);
            }

            return redirect()
                ->back(fallback: route('families.goals.index'))
                ->with('error', __('The upload is too large for the server limit (try an image under 4 MB, or increase PHP upload_max_filesize and post_max_size).'));
        });
    })->create();
