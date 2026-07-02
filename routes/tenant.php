<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\PortalAuthenticatedSessionController;
use App\Http\Controllers\Portal\PortalCalendarController;
use App\Http\Controllers\Portal\PortalCalendarEventsController;
use App\Http\Controllers\Portal\PortalDashboardController;
use App\Http\Controllers\Portal\PortalRequestsController;
use App\Http\Controllers\Portal\PortalTimeTrackingController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByPath;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByPath::class,
])->prefix('/portal/{tenant}')->group(function () {
    Route::get('/login', [PortalAuthenticatedSessionController::class, 'create'])->name('portal.login');
    Route::post('/login', [PortalAuthenticatedSessionController::class, 'store'])->name('portal.login.store');

    Route::middleware('auth')->group(function (): void {
        Route::post('/logout', [PortalAuthenticatedSessionController::class, 'destroy'])->name('logout');

        Route::get('/dashboard', PortalDashboardController::class)->name('portal.dashboard');

        Route::get('/calendario', PortalCalendarController::class)->name('portal.calendar.index');
        Route::get('/calendario/eventos', PortalCalendarEventsController::class)->name('portal.calendar.events');

        Route::get('/control-horario', PortalTimeTrackingController::class)->name('portal.time-tracking.index');
        Route::get('/solicitudes', PortalRequestsController::class)->name('portal.requests.index');
        Route::view('/documentacion', 'portal.placeholder', [
            'description' => 'Este espacio mostrara el expediente documental del empleado con acceso protegido.',
            'eyebrow' => 'Documentacion',
            'title' => 'Documentacion',
        ])->name('portal.documents.index');
    });
});
