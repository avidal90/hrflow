<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class PortalDocumentsController extends Controller
{
    public function __invoke(): View
    {
        return view('portal.documentacion');
    }
}
