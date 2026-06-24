<?php

use App\Models\Permission;
use App\Models\Role;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('le seeder permissions est idempotent lors d executions multiples', function () {
    $this->seed(DatabaseSeeder::class);
    $countApresUn = Permission::count();

    // Deuxieme execution : ne doit pas creer de doublons
    $this->seed(DatabaseSeeder::class);
    $countApresDeux = Permission::count();

    expect($countApresDeux)->toBe($countApresUn);
});

test('le seeder roles est idempotent lors d executions multiples', function () {
    $this->seed(DatabaseSeeder::class);
    $countApresUn = Role::count();

    $this->seed(DatabaseSeeder::class);
    $countApresDeux = Role::count();

    expect($countApresDeux)->toBe($countApresUn);
});

test('le seeder cree bien les 16 permissions de l enum PermissionValue', function () {
    $this->seed(DatabaseSeeder::class);

    expect(Permission::count())->toBe(16);
});

test('le role Directeur IUT possede la permission SIGNER_BONS_DE_COMMANDES apres seeding', function () {
    $this->seed(DatabaseSeeder::class);

    $directeur = Role::where('name', 'Directeur IUT')->first();

    expect($directeur)->not->toBeNull();

    $hasPerm = $directeur->permissions()
        ->where('name', 'SIGNER_BONS_DE_COMMANDES')
        ->exists();

    expect($hasPerm)->toBeTrue();
});
