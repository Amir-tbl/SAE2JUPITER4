<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Tout le monde a le droit de tenter de se connecter.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Règles de validation du formulaire de connexion.
     *
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string',
        ];
    }

    /**
     * Vérifie que la requête n'est pas bloquée par le rate limiter,
     * puis renvoie une erreur de validation si trop de tentatives.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        // 5 essais autorisés avant blocage
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => "Trop de tentatives de connexion. Réessayez dans $seconds secondes.",
        ]);
    }

    /**
     * Clé unique du rate limiter : combinaison de l'email et de l'IP.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('email')).'|'.$this->ip());
    }
}
