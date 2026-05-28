<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller;

class DevLoginController extends Controller
{
    /**
     * Show all users for dev login selection
     */
    public function index()
    {
        if (!app()->isLocal()) {
            abort(404);
        }

        $users = User::with('roles')->get();

        return view('dev-login', ['users' => $users]);
    }

    /**
     * Log in as the specified user
     */
    public function login(Request $request, string $id)
    {
        if (!app()->isLocal()) {
            abort(404);
        }

        $user = User::with('roles')->findOrFail($id);

        // Load permissions cache
        $user->getPermissions(true);

        Auth::login($user);

        return redirect('/dashboard');
    }
}
