<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionValue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends BaseController
{
    public function index(): View
    {
        $users = User::with('roles')->orderBy('last_name')->get();
        $roles = Role::orderBy('name')->get();
        $departments = Role::where('is_department', true)->orderBy('name')->get();
        $totalUsers = $users->count();
        $activeUsers = $users->filter(fn($u) => $u->roles->isNotEmpty())->count();

        return view('users.index', compact('users', 'roles', 'departments', 'totalUsers', 'activeUsers'));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'login' => 'required|string|unique:users,login',
            'password' => 'required|string|min:4',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $user = User::create([
            'first_name' => ucfirst(strtolower($request->first_name)),
            'last_name' => strtoupper($request->last_name),
            'email' => $request->email,
            'login' => $request->login,
            'password' => $request->password,
        ]);

        if ($request->roles) {
            $user->roles()->sync($request->roles);
        }

        return response()->json(['status' => 'success', 'message' => 'Utilisateur cree.']);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $user->update([
            'first_name' => ucfirst(strtolower($request->first_name)),
            'last_name' => strtoupper($request->last_name),
            'email' => $request->email,
        ]);

        if ($request->has('password') && $request->password) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        $user->roles()->sync($request->roles ?? []);

        return response()->json(['status' => 'success', 'message' => 'Utilisateur mis a jour.']);
    }

    public function modalEdit(int $id): View
    {
        $user = User::with('roles')->findOrFail($id);
        $roles = Role::orderBy('name')->get();
        return view('users.modal-edit', compact('user', 'roles'));
    }

    public function destroy(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return response()->json(['status' => 'error', 'message' => 'Impossible de supprimer votre propre compte.'], 403);
        }

        $user->roles()->detach();
        $user->delete();

        return response()->json(['status' => 'success', 'message' => 'Utilisateur supprime.']);
    }
}
