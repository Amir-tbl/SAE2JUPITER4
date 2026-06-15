<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

// Composant d'affichage des messages d'alerte (succès, erreur, info)
class alert extends Component
{
    // Cree une instance du composant
    public function __construct()
    {
        //
    }

    // Retourne la vue du composant
    public function render(): View|Closure|string
    {
        return view('components.alert');
    }
}
