<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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
            $this->logDetailedException($e);
        });

        $this->renderable(function (\Spatie\Permission\Exceptions\UnauthorizedException $e, $request) {
            if ($request->expectsJson() || ($request->ajax() && !$request->hasHeader('X-MDI-Partial'))) {
                return response()->json(['message' => __('permission.no_permission.description')], 403);
            }
            return response()->view('admin::pages.no-permission', ['workspaceAccessDenied' => true], 403);
        });
    }

    /**
     * Capture and log rich structured technical details for debugging.
     */
    protected function logDetailedException(Throwable $e): void
    {
        try {
            $request = request();
            $errorId = $this->getOrCreateErrorId();

            $user = null;
            try {
                if (auth()->check()) {
                    $u = auth()->user();
                    $user = [
                        'id' => $u->id ?? null,
                        'name' => $u->name ?? null,
                        'email' => $u->email ?? null,
                    ];
                }
            } catch (Throwable $userEx) {}

            $route = null;
            try {
                if ($request && $request->route()) {
                    $route = [
                        'name' => $request->route()->getName(),
                        'action' => $request->route()->getActionName(),
                    ];
                }
            } catch (Throwable $routeEx) {}

            $sanitizedInput = [];
            try {
                if ($request) {
                    $sanitizedInput = $request->except([
                        'password', 'password_confirmation', '_token', 'current_password'
                    ]);
                }
            } catch (Throwable $inputEx) {}

            Log::error("[{$errorId}] Exception: {$e->getMessage()}", [
                'error_id' => $errorId,
                'exception_class' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile() . ':' . $e->getLine(),
                'status_code' => $this->isHttpException($e) ? $e->getStatusCode() : 500,
                'url' => $request ? $request->fullUrl() : 'CLI',
                'method' => $request ? $request->method() : 'CLI',
                'route' => $route,
                'user' => $user ?: 'Guest',
                'ip' => $request ? $request->ip() : null,
                'is_mdi' => $request ? $request->hasHeader('X-MDI-Partial') : false,
                'input' => $sanitizedInput,
                'trace' => collect($e->getTrace())->take(10)->map(function ($frame) {
                    return [
                        'file' => $frame['file'] ?? null,
                        'line' => $frame['line'] ?? null,
                        'call' => ($frame['class'] ?? '') . ($frame['type'] ?? '') . ($frame['function'] ?? ''),
                    ];
                })->toArray(),
            ]);
        } catch (Throwable $logEx) {
            // Failsafe to avoid recursive crash during logging
        }
    }

    /**
     * Get or create a unique error reference ID for the current request.
     */
    protected function getOrCreateErrorId(): string
    {
        try {
            $request = request();
            if ($request && $request->attributes->has('pos_error_id')) {
                return $request->attributes->get('pos_error_id');
            }
            $errorId = 'ERR-' . strtoupper(substr(uniqid(), -6));
            if ($request) {
                $request->attributes->set('pos_error_id', $errorId);
            }
            return $errorId;
        } catch (Throwable $e) {
            return 'ERR-' . strtoupper(substr(uniqid(), -6));
        }
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $e)
    {
        // Allow Laravel to handle native redirect/validation/auth exceptions normally
        if ($e instanceof \Illuminate\Validation\ValidationException ||
            $e instanceof \Illuminate\Auth\AuthenticationException ||
            $e instanceof \Illuminate\Http\Exceptions\HttpResponseException) {
            return parent::render($request, $e);
        }

        // Allow Spatie UnauthorizedException to be handled by its registered callback
        if ($e instanceof \Spatie\Permission\Exceptions\UnauthorizedException) {
            return parent::render($request, $e);
        }

        $e = $this->prepareException($e);
        $errorId = $this->getOrCreateErrorId();

        // Check if this request is targeted at the admin workspace
        $isAdminRequest = $request->is('admin') ||
                          $request->is('admin/*') ||
                          $request->hasHeader('X-MDI-Partial') ||
                          $request->hasHeader('X-MDI-Form') ||
                          ($request->route() && Str::startsWith($request->route()->getName() ?? '', 'admin-'));

        if ($isAdminRequest) {
            $statusCode = $this->isHttpException($e) ? $e->getStatusCode() : 500;

            // Prepare developer debugging info (only exposed when app.debug is true)
            $debugInfo = config('app.debug') ? [
                'error_id' => $errorId,
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'trace' => collect($e->getTrace())->take(12)->map(function ($frame) {
                    return [
                        'file' => $frame['file'] ?? null,
                        'line' => $frame['line'] ?? null,
                        'function' => ($frame['class'] ?? '') . ($frame['type'] ?? '') . ($frame['function'] ?? ''),
                    ];
                })->toArray(),
            ] : null;

            // 1. JSON / API / Non-MDI AJAX requests
            if ($request->expectsJson() || ($request->ajax() && !$request->hasHeader('X-MDI-Partial') && !$request->hasHeader('X-MDI-Form'))) {
                return response()->json([
                    'success' => false,
                    'error_id' => $errorId,
                    'message' => config('app.debug') ? $e->getMessage() : __('global.workspace.load_error_desc'),
                    'debug' => $debugInfo,
                ], $statusCode)->withHeaders([
                    'X-POS-Error' => '1',
                    'X-POS-Error-ID' => $errorId,
                ]);
            }

            // Derive current tab key
            $cleanPath = trim(parse_url($request->fullUrl(), PHP_URL_PATH) ?? '', '/');
            $tabKey = preg_replace('/[^a-zA-Z0-9]/', '-', $cleanPath) ?: 'home';

            // 2. MDI AJAX Partial Fetch (Tab Content Navigation)
            if ($request->hasHeader('X-MDI-Partial')) {
                try {
                    return response()->view('admin::pages.error-pane', [
                        'errorId' => $errorId,
                        'statusCode' => $statusCode,
                        'debugInfo' => $debugInfo,
                        'exception' => $e,
                        'tabKey' => $tabKey,
                        'isPartial' => true,
                    ], $statusCode)->withHeaders([
                        'X-POS-Error' => '1',
                        'X-POS-Error-ID' => $errorId,
                        'X-POS-Error-Message' => config('app.debug') ? substr($e->getMessage(), 0, 150) : 'Server Error',
                    ]);
                } catch (Throwable $partialEx) {
                    // Fall back to parent renderer if custom partial fails
                    return parent::render($request, $e);
                }
            }

            // 3. Full Page Browser Request (Browser Refresh on Active Tab / Direct URL Access)
            try {
                return response()->view('admin::pages.error', [
                    'errorId' => $errorId,
                    'statusCode' => $statusCode,
                    'debugInfo' => $debugInfo,
                    'exception' => $e,
                    'tabKey' => $tabKey,
                    'isPartial' => false,
                ], $statusCode)->withHeaders([
                    'X-POS-Error' => '1',
                    'X-POS-Error-ID' => $errorId,
                    'X-POS-Error-Message' => config('app.debug') ? substr($e->getMessage(), 0, 150) : 'Server Error',
                ]);
            } catch (Throwable $renderEx) {
                Log::warning("Failed to render custom admin error page: " . $renderEx->getMessage(), [
                    'exception' => get_class($renderEx),
                    'file' => $renderEx->getFile() . ':' . $renderEx->getLine(),
                ]);
                // Fall back to default handler if rendering custom error page fails
                return parent::render($request, $e);
            }
        }

        return parent::render($request, $e);
    }
}
