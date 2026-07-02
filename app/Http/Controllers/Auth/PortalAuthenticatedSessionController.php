<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PortalAuthenticatedSessionController extends Controller
{
    /**
     * @return array{tenant: int|string}
     */
    private function tenantRouteParameters(?User $user = null): array
    {
        $tenantId = tenant()?->getTenantKey() ?? $user?->tenant_id;

        return [
            'tenant' => $tenantId,
        ];
    }

    public function create(Request $request): View|RedirectResponse
    {
        if ($request->user() instanceof User) {
            return $this->redirectAuthenticatedUser($request);
        }

        return view('auth.login', $this->loginViewData($request));
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        if (! Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ], (bool) ($credentials['remember'] ?? false))) {
            throw ValidationException::withMessages([
                'email' => __('Credenciales incorrectas.'),
            ]);
        }

        $request->session()->regenerate();

        return $this->redirectAfterLoginAttempt($request);
    }

    public function redirectToPortalLogin(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tenant' => ['required', 'string', Rule::exists('tenants', 'id')],
        ], [
            'tenant.required' => __('Indica el codigo de tu empresa para abrir el portal.'),
            'tenant.exists' => __('No hemos podido abrir ese portal. Revisa el codigo de empresa.'),
        ]);

        return to_route('portal.login', [
            'tenant' => $validated['tenant'],
        ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $this->logoutSession($request);

        if ($this->isPortalRequest($request)) {
            return to_route('portal.login', $this->tenantRouteParameters());
        }

        return to_route('login');
    }

    private function redirectAuthenticatedUser(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return to_route('login');
        }

        if ($this->isPortalRequest($request) && $user->tenant_id !== null) {
            return to_route('portal.dashboard', $this->tenantRouteParameters($user));
        }

        if ($user->canAccessAdministration()) {
            return redirect('/admin');
        }

        if ($user->tenant_id !== null) {
            return to_route('portal.dashboard', $this->tenantRouteParameters($user));
        }

        return to_route('login');
    }

    /**
     * @throws ValidationException
     */
    private function redirectAfterLoginAttempt(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return to_route('login');
        }

        if ($this->isPortalRequest($request) && $user->tenant_id !== null) {
            return to_route('portal.dashboard', $this->tenantRouteParameters($user));
        }

        if ($user->canAccessAdministration()) {
            return redirect('/admin');
        }

        if ($user->tenant_id !== null) {
            $this->logoutSession($request);

            throw ValidationException::withMessages([
                'email' => __('Este acceso es solo para administracion. Usa el portal de tu empresa para fichar y gestionar solicitudes.'),
            ]);
        }

        $this->logoutSession($request);

        throw ValidationException::withMessages([
            'email' => __('Credenciales incorrectas.'),
        ]);
    }

    /**
     * @return array{title: string, heading: string, description: string, form_action: string, footer_html: string}
     */
    private function loginViewData(Request $request): array
    {
        if ($this->isPortalRequest($request)) {
            $tenantName = tenant()?->name ?? 'tu empresa';

            return [
                'title' => 'Portal del empleado | HRFlow',
                'heading' => 'Accede al portal de '.$tenantName,
                'description' => 'Entra para fichar, consultar tu calendario y gestionar solicitudes. Si tu rol tambien tiene permisos internos, veras el acceso a administracion dentro del portal.',
                'form_action' => route('portal.login.store', $this->tenantRouteParameters()),
                'footer_html' => __('Si necesitas el backoffice, usa el acceso de administracion desde :link.', [
                    'link' => '<a href="'.route('login').'" class="font-semibold text-slate-700 hover:text-slate-900">la zona interna</a>',
                ]),
            ];
        }

        return [
            'title' => 'Administracion | HRFlow',
            'heading' => 'Acceso administrativo',
            'description' => 'Este acceso esta reservado al backoffice en Filament para RRHH, responsables y perfiles administrativos.',
            'form_action' => route('login.store'),
            'footer_html' => __('Si vienes a fichar o a pedir vacaciones, usa :link.', [
                'link' => '<a href="'.route('public.access').'" class="font-semibold text-slate-700 hover:text-slate-900">el portal de tu empresa</a>',
            ]),
        ];
    }

    private function isPortalRequest(Request $request): bool
    {
        return $request->routeIs('portal.*');
    }

    private function logoutSession(Request $request): void
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
