<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AboutController extends BaseController
{
    /**
     * Affiche la page À Propos.
     */
    public function about(): View|RedirectResponse|Redirector
    {

        // On retourne simplement la vue
        return view('about');
    }
}
