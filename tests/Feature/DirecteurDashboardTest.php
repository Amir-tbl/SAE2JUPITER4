<?php

use App\Models\Order;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Cree une commande avec auteur et departement requis.
 */
function makeOrderDir(array $attrs = []): Order
{
    $department = Role::forceCreate([
        'name' => 'Departement '.uniqid(),
        'description' => 'Departement de test',
        'is_department' => true,
    ]);

    $author = User::factory()->create();

    return Order::create(array_merge([
        'order_num' => 'CMD'.uniqid(),
        'title' => 'Commande test directeur',
        'status' => Status::DEVIS->value,
        'quote_num' => 'DEV'.uniqid(),
        'author_id' => $author->id,
        'department_id' => $department->id,
    ], $attrs));
}

test('scopeEnAttenteSignature retourne uniquement les BC non signes', function () {
    makeOrderDir(['status' => Status::BON_DE_COMMANDE_NON_SIGNE->value]);
    makeOrderDir(['status' => Status::BON_DE_COMMANDE_NON_SIGNE->value]);
    makeOrderDir(['status' => Status::DEVIS->value]);
    makeOrderDir(['status' => Status::BON_DE_COMMANDE_SIGNE->value]);

    $count = Order::enAttenteSignature()->count();

    expect($count)->toBe(2);
});

test('scopeUrgent retourne les BC en attente depuis plus de 7 jours', function () {
    // Urgent : cree il y a 10 jours
    makeOrderDir([
        'status' => Status::BON_DE_COMMANDE_NON_SIGNE->value,
        'created_at' => now()->subDays(10),
    ]);

    // Pas urgent : cree il y a 3 jours
    makeOrderDir([
        'status' => Status::BON_DE_COMMANDE_NON_SIGNE->value,
        'created_at' => now()->subDays(3),
    ]);

    // Autre statut : ne doit pas apparaitre
    makeOrderDir([
        'status' => Status::DEVIS->value,
        'created_at' => now()->subDays(10),
    ]);

    $count = Order::urgent(7)->count();

    expect($count)->toBe(1);
});

test('scopeUrgent accepte un seuil de jours personnalise', function () {
    makeOrderDir([
        'status' => Status::BON_DE_COMMANDE_NON_SIGNE->value,
        'created_at' => now()->subDays(15),
    ]);
    makeOrderDir([
        'status' => Status::BON_DE_COMMANDE_NON_SIGNE->value,
        'created_at' => now()->subDays(5),
    ]);

    expect(Order::urgent(10)->count())->toBe(1);
    expect(Order::urgent(3)->count())->toBe(2);
});

test('le dashboard directeur est accessible a un utilisateur avec la permission signer', function () {
    // Creer le role directeur avec la permission SIGNER_BONS_DE_COMMANDES
    $role = Role::forceCreate([
        'name' => 'Directeur IUT',
        'description' => 'Directeur',
        'is_department' => false,
    ]);

    $permission = \App\Models\Permission::create(['name' => 'SIGNER_BONS_DE_COMMANDES']);
    $role->permissions()->attach($permission->id);

    $directeur = User::factory()->create();
    $directeur->roles()->attach($role->id);

    $response = $this->actingAs($directeur)->get('/dashboard');

    $response->assertStatus(200);
});

test('getJoursAttente retourne le nombre de jours depuis la creation', function () {
    $order = makeOrderDir(['created_at' => now()->subDays(5)]);

    expect($order->getJoursAttente())->toBe(5);
});

test('isUrgent retourne vrai apres 7 jours par defaut', function () {
    $urgent = makeOrderDir(['created_at' => now()->subDays(8)]);
    $recent = makeOrderDir(['created_at' => now()->subDays(3)]);

    expect($urgent->isUrgent())->toBeTrue();
    expect($recent->isUrgent())->toBeFalse();
});

test('isUrgent accepte un seuil personnalise', function () {
    $order = makeOrderDir(['created_at' => now()->subDays(5)]);

    expect($order->isUrgent(3))->toBeTrue();
    expect($order->isUrgent(10))->toBeFalse();
});

test('scopeSigneesOuRefusees retourne uniquement les BC signes et refuses', function () {
    makeOrderDir(['status' => Status::BON_DE_COMMANDE_SIGNE->value]);
    makeOrderDir(['status' => Status::BON_DE_COMMANDE_REFUSE->value]);
    makeOrderDir(['status' => Status::BON_DE_COMMANDE_NON_SIGNE->value]);
    makeOrderDir(['status' => Status::DEVIS->value]);

    $count = Order::signeesOuRefusees()->count();

    expect($count)->toBe(2);
});

test('scopeSigneesOuRefusees exclut les commandes encore en attente ou en cours', function () {
    makeOrderDir(['status' => Status::BROUILLON->value]);
    makeOrderDir(['status' => Status::DEVIS->value]);
    makeOrderDir(['status' => Status::BON_DE_COMMANDE_NON_SIGNE->value]);

    expect(Order::signeesOuRefusees()->count())->toBe(0);
});

test('scopeSigneesOuRefusees compte correspond au kpiTotalTraites du dashboard', function () {
    makeOrderDir(['status' => Status::BON_DE_COMMANDE_SIGNE->value]);
    makeOrderDir(['status' => Status::BON_DE_COMMANDE_SIGNE->value]);
    makeOrderDir(['status' => Status::BON_DE_COMMANDE_REFUSE->value]);
    makeOrderDir(['status' => Status::BON_DE_COMMANDE_NON_SIGNE->value]);

    // Le dashboard directeur calcule kpiTotalTraites = signes + refuses
    $kpiTotalTraites = Order::signeesOuRefusees()->count();

    expect($kpiTotalTraites)->toBe(3);
});
