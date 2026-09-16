<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SalesmanMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!in_array(auth()->user()->role, ['salesman', 'admin', 'superadmin'])) {
            abort(403, 'Access denied.');
        }

        if (!auth()->user()->outlet_id) {
            abort(403, 'Your account is not assigned to any outlet. Please contact your admin.');
        }

        if (!auth()->user()->is_active) {
            abort(403, 'Your account has been deactivated.');
        }

        return $next($request);
    }
}