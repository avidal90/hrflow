<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyTenantTimezone
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenantTimezone = tenant()?->timezone;

        if (is_string($tenantTimezone) && in_array($tenantTimezone, timezone_identifiers_list(), true)) {
            config(['app.timezone' => $tenantTimezone]);
            date_default_timezone_set($tenantTimezone);
        }

        return $next($request);
    }
}
