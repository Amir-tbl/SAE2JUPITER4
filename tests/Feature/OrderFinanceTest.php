<?php

use App\Models\Article;
use App\Models\Order;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Cree une commande valide avec son auteur et son departement,
 * car la table orders exige author_id et department_id (non nullables).
 */
function makeOrder(array $attrs = []): Order
{
    // Role n'a pas de $fillable -> on force l'ecriture des attributs
    $department = Role::forceCreate([
        'name' => 'Departement '.uniqid(),
        'description' => 'Departement de test',
        'is_department' => true,
    ]);

    $author = User::factory()->create();

    return Order::create(array_merge([
        'order_num' => 'CMD'.uniqid(),
        'title' => 'Commande de test',
        'status' => Status::DEVIS->value,
        'quote_num' => 'DEV'.uniqid(),
        'author_id' => $author->id,
        'department_id' => $department->id,
    ], $attrs));
}

test('recalculateTotals calcule le HT, la TVA et le TTC depuis les articles', function () {
    $order = makeOrder();

    // 2 x 100,00 a 20% -> HT 200, TVA 40
    Article::create([
        'order_id' => $order->id,
        'designation' => 'Ecran',
        'quantity' => 2,
        'unit_price' => 100.00,
        'vat_rate' => 20,
        'total_ttc' => 240.00,
    ]);

    // 3 x 50,00 a 10% -> HT 150, TVA 15
    Article::create([
        'order_id' => $order->id,
        'designation' => 'Cable',
        'quantity' => 3,
        'unit_price' => 50.00,
        'vat_rate' => 10,
        'total_ttc' => 165.00,
    ]);

    $order->recalculateTotals();
    $order->refresh();

    expect((float) $order->total_ht)->toBe(350.00);
    expect((float) $order->total_vat)->toBe(55.00);
    expect((float) $order->total_ttc)->toBe(405.00);
});

test('recalculateTotals met les totaux a zero sans article', function () {
    $order = makeOrder();

    $order->recalculateTotals();
    $order->refresh();

    expect((float) $order->total_ht)->toBe(0.00);
    expect((float) $order->total_vat)->toBe(0.00);
    expect((float) $order->total_ttc)->toBe(0.00);
});

test('Article::calculateTotalTtc applique la TVA sur la ligne', function () {
    $article = new Article([
        'quantity' => 4,
        'unit_price' => 25.00,
        'vat_rate' => 20,
    ]);

    // 4 x 25 = 100 HT, +20% -> 120 TTC
    expect($article->calculateTotalTtc())->toBe(120.00);
});

test('getCostFormatted formate le cout en euros', function () {
    $order = new Order(['cost' => 1234.5]);

    expect($order->getCostFormatted())->toBe('1 234,50 €');
});

test('getCostFormatted indique non precise quand le cout est nul', function () {
    $order = new Order(['cost' => null]);

    expect($order->getCostFormatted())->toBe('Non précisé');
});
