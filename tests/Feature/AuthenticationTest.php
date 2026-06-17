<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;

uses(RefreshDatabase::class);

test('la page de connexion est accessible a un visiteur', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('un utilisateur deja connecte est redirige vers le dashboard', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/login');

    $response->assertRedirect('/dashboard');
});

test('un utilisateur peut se connecter avec les bons identifiants', function () {
    $user = User::factory()->create([
        'email' => 'boran@test.com',
        'password' => 'password',
    ]);

    $response = $this->post('/login', [
        'email' => 'boran@test.com',
        'password' => 'password',
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($user);
});

test('un mauvais mot de passe est refuse', function () {
    User::factory()->create([
        'email' => 'boran@test.com',
        'password' => 'password',
    ]);

    $response = $this->post('/login', [
        'email' => 'boran@test.com',
        'password' => 'mauvais-mot-de-passe',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('un email inexistant est refuse', function () {
    $response = $this->post('/login', [
        'email' => 'inconnu@test.com',
        'password' => 'password',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('le formulaire exige un email et un mot de passe', function () {
    $response = $this->post('/login', [
        'email' => '',
        'password' => '',
    ]);

    $response->assertSessionHasErrors(['email', 'password']);
    $this->assertGuest();
});

test('le login est bloque apres 5 tentatives echouees', function () {
    User::factory()->create([
        'email' => 'cible@test.com',
        'password' => 'password',
    ]);

    // 5 tentatives ratees pour atteindre la limite
    for ($i = 0; $i < 5; $i++) {
        $this->post('/login', [
            'email' => 'cible@test.com',
            'password' => 'faux',
        ]);
    }

    // La 6e doit etre bloquee par le rate limiter
    $response = $this->post('/login', [
        'email' => 'cible@test.com',
        'password' => 'password', // meme avec le bon mot de passe
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();

    RateLimiter::clear('cible@test.com|127.0.0.1');
});
