<?php
// Project path: app/Http/Middleware/EnsureSubscriptionActive.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Hard lockout for shop owner / salesman / booker routes.
 *
 * Super admin is never subject to this — its routes should not register
 * this middleware. Applies to both web and API guards; the response shape
 * differs so the mobile app gets a machine-readable reason to show the
 * "subscription inactive" screen instead of a generic error.
 */
class EnsureSubscriptionActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user || ! $user->belongsToOutletSubscription()) {
            return $next($request);
        }

        $outlet = $user->outlet;

        if (! $outlet || ! $outlet->hasActiveSubscription()) {
            Auth::logout();

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Subscription is inactive. Please contact your administrator.',
                    'reason'  => $outlet?->subscription_status ?? 'no_outlet',
                ], 403);
            }

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Your shop\'s subscription is inactive. Please contact the administrator.',
            ]);
        }

        return $next($request);
    }
}