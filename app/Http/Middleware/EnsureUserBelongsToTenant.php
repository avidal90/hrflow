<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserBelongsToTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return $next($request);
        }

        $currentTenantId = (string) (tenant()?->getTenantKey() ?? '');

        abort_unless($user->tenant_id !== null, 403);

        if ((string) $user->tenant_id !== $currentTenantId) {
            return redirect()->route('portal.dashboard', ['tenant' => $user->tenant_id]);
        }

        return $next($request);
    }
}
