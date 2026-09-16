<?php
// Project path: app/Http/Middleware/EnsureModuleEnabled.php

namespace App\Http\Middleware;

use App\Services\ModuleAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Usage: Route::middleware('module:purchase_orders')->group(...)
 * Registered as 'module' in bootstrap/app.php (see Sprint 1 setup notes).
 */
class EnsureModuleEnabled
{
    public function __construct(protected ModuleAccessService $modules) {}

    public function handle(Request $request, Closure $next, string $moduleKey): Response
    {
        $outlet = $request->user()?->outlet;

        if (! $outlet || ! $this->modules->isEnabled($outlet, $moduleKey)) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'This module is not available on your current plan.',
                    'module'  => $moduleKey,
                ], 403);
            }

            abort(403, 'This module is not available on your current plan.');
        }

        return $next($request);
    }
}