<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class PortalRequestsController extends Controller
{
    public function __invoke(): View
    {
        return view('portal.solicitudes');
    }
}
