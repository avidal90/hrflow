<?php

use App\Http\Controllers\Auth\PortalAuthenticatedSessionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('public.home');
})->name('public.home');

Route::view('/acceso', 'public.access')->name('public.access');
Route::post('/acceso/portal', [PortalAuthenticatedSessionController::class, 'redirectToPortalLogin'])->name('public.portal.access');

Route::get('/login', [PortalAuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [PortalAuthenticatedSessionController::class, 'store'])->name('login.store');

Route::middleware('auth')->group(function (): void {
    Route::get('/mi-perfil', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/mi-perfil/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
    Route::put('/mi-perfil/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
});
