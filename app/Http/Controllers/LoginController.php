<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\User;

class LoginController extends Controller
{
    // Affiche le formulaire de connexion
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }

        return view('login');
    }

    // Traite la tentative de connexion
    public function login(LoginRequest $request)
    {
        // Bloque la requete si trop de tentatives echouees (anti-bruteforce)
        $request->ensureIsNotRateLimited();

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            // Echec : on incremente le compteur de tentatives pour cette cle
            RateLimiter::hit($request->throttleKey());

            return back()->withErrors([
                'email' => 'Les identifiants fournis sont incorrects.',
            ])->withInput($request->only('email'));
        }

        // Succes : on remet le compteur a zero
        RateLimiter::clear($request->throttleKey());

        $user->getPermissions(true);
        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    // Deconnecte l'utilisateur
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
