<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DevLoginController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SupplierController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Route;

// Login routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

// Dev login (local only)
if (app()->isLocal()) {
    Route::get('/dev-login', [DevLoginController::class, 'index'])->name('dev-login.index');
    Route::post('/dev-login/{id}', [DevLoginController::class, 'login'])->name('dev-login.login');
}

// dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard', [DashboardController::class, 'index']);

// orders get
Route::get('/orders', [OrderController::class, 'viewOrders'])->name('orders.index');

// orders post
Route::post('/orders', [OrderController::class, 'submitNewOrder'])->name('orders.submitNewOrder');

// wizard création commande
Route::get('/orders/create/step1', [OrderController::class, 'createStep1'])->name('orders.create.step1');
Route::post('/orders/create/step1', [OrderController::class, 'storeStep1'])->name('orders.store.step1');
Route::get('/orders/create/step2', [OrderController::class, 'createStep2'])->name('orders.create.step2');
Route::post('/orders/create/step2', [OrderController::class, 'storeStep2'])->name('orders.store.step2');
Route::get('/orders/create/step3', [OrderController::class, 'createStep3'])->name('orders.create.step3');
Route::post('/orders/create/step3', [OrderController::class, 'storeStep3'])->name('orders.store.step3');

// suppliers
Route::get('suppliers', [SupplierController::class, 'viewSuppliers']);
Route::post('suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
Route::put('suppliers/{id}', [SupplierController::class, 'update'])->name('suppliers.update');



// orders modals get
Route::get('/order/{id}/uploadPurchaseOrder', [OrderController::class, 'modalUploadPurchaseOrder'])
    ->name('orders.modal.uploadPurchaseOrder');
Route::get('/order/{id}/refuse', [OrderController::class, 'modalRefuse'])
    ->name('orders.modal.refuse');
Route::get('/order/{id}/paid', [OrderController::class, 'modalPaid'])
    ->name('orders.modal.paid');
Route::get('/order/{id}/uploadDeliveryNote', [OrderController::class, 'modalUploadDeliveryNote'])
    ->name('orders.modal.uploadDeliveryNote');
Route::get('/order/{id}/sentToSupplier', [OrderController::class, 'modalSentToSupplier'])
    ->name('orders.modal.sentToSupplier');
Route::get('/order/{id}/deliveredPackages', [OrderController::class, 'modalDeliveredPackages'])
    ->name('orders.modal.deliveredPackages');
Route::get('/order/{id}/deliveredAll', [OrderController::class, 'modalDeliveredAll'])
    ->name('orders.modal.deliveredAll');
Route::get('/order/{id}/viewDetails', [OrderController::class, 'modalViewDetails'])
    ->name('orders.modal.viewDetails');
Route::get('/order/{id}/viewDetailsCrit', [OrderController::class, 'modalViewDetailsCrit'])
    ->name('orders.modal.viewDetailsCrit');

// orders post modals post

Route::post('/order/{id}/uploadPurchaseOrder', [OrderController::class, 'actionUploadPurchaseOrder'])
    ->name('orders.uploadPurchaseOrder');
Route::post('/order/{id}/refuse', [OrderController::class, 'actionRefuse'])->name('orders.action.refuse');
Route::post('/order/{id}/paid', [OrderController::class, 'actionPaid'])->name('orders.action.paid');
Route::post('/order/{id}/sentToSupplier', [OrderController::class, 'actionSentToSupplier'])->name('orders.action.sentToSupplier');
Route::post('/order/{id}/uploadDeliveryNote', [OrderController::class, 'actionUploadDeliveryNote'])->name('orders.action.uploadDeliveryNote');
Route::post('/order/{id}/deliveredPackages', [OrderController::class, 'actionDeliveredPackages'])->name('orders.action.deliveredPackages');
Route::post('/order/{id}/deliveredAll', [OrderController::class, 'actionDeliveredAll'])->name('orders.action.deliveredAll');
Route::post('/orders/{id}/viewDetails', [OrderController::class, 'modalViewDetails'])
    ->name('orders.modal.viewDetails');
Route::post('/orders/create', [OrderController::class, 'submitNewOrder'])
    ->name('orders.create');

// Ajoutez cette route dans votre groupe de routes authentifiées
Route::get('/order/{id}/document/{type}', [OrderController::class, 'downloadDocument'])
    ->name('orders.download');


// suppliers modals get
Route::get('/supplier/{id}/viewDetails', [SupplierController::class, 'modalViewDetails'])
    ->name('suppliers.modal.viewDetails');



// suppliers modals post
Route::post('/supplier/{id}/viewDetails', [SupplierController::class, 'modalViewDetails'])
    ->name('suppliers.modal.viewDetails');

// suppliers actions
Route::patch('/suppliers/{id}/toggle-valid', [SupplierController::class, 'toggleValid'])->name('suppliers.toggleValid');
Route::delete('/suppliers/{id}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');


// Seulement pour les tests sur le serveur de l'IUT
Route::get('/cookies', function (Request $request) {
    dd($request->cookie());
});


// Agent - Historique
Route::get('/orders/historique-agent', [OrderController::class, 'historiqueAgent'])->name('orders.historique-agent');
Route::get('/orders/historique-agent/export', [OrderController::class, 'exportHistoriqueAgent'])->name('orders.historique-agent.export');

// CRIT - Réception et Distribution
Route::get('/orders/reception', [OrderController::class, 'reception'])->name('orders.reception');
Route::post('/order/{id}/receive', [OrderController::class, 'receiveOrder'])->name('order.receive');
Route::get('/orders/distribution', [OrderController::class, 'distribution'])->name('orders.distribution');
Route::post('/order/{id}/deliver', [OrderController::class, 'deliverOrder'])->name('order.deliver');
Route::get('/orders/historique-crit', [OrderController::class, 'historiqueCrit'])->name('orders.historique-crit');
Route::get('/orders/historique-crit/export', [OrderController::class, 'exportHistoriqueCrit'])->name('orders.historique-crit.export');

// Service Financier - Validation
Route::get('/orders/validation', [OrderController::class, 'validationList'])->name('orders.validation');
Route::get('/order/{id}/validation-sf', [OrderController::class, 'modalValidationSF'])->name('order.modal.validationSF');
Route::post('/order/{id}/validation-sf', [OrderController::class, 'actionValidationSF'])->name('order.action.validationSF');

// Service Financier - Suivi
Route::get('/orders/suivi', [OrderController::class, 'suiviSF'])->name('orders.suivi');
Route::get('/orders/suivi/export', [OrderController::class, 'exportSuiviSF'])->name('orders.suivi.export');
Route::get('/order/{id}/suivi-sf-details', [OrderController::class, 'modalSuiviDetailsSF'])->name('order.modal.suiviDetailsSF');
Route::get('/order/{id}/modal-envoi-bc', [OrderController::class, 'modalEnvoiBC'])->name('order.modal.envoiBC');
Route::post('/order/{id}/send-bc-email', [OrderController::class, 'sendBCEmail'])->name('order.action.sendBC');
Route::get('/order/{id}/modal-paiement', [OrderController::class, 'modalPaiement'])->name('order.modal.paiement');
Route::post('/order/{id}/mark-paid-sf', [OrderController::class, 'markPaidSF'])->name('order.action.markPaid');
Route::get('/order/{id}/modal-relance', [OrderController::class, 'modalRelance'])->name('order.modal.relance');
Route::post('/order/{id}/relance-fournisseur', [OrderController::class, 'relanceFournisseur'])->name('order.action.relance');

// Directeur - Signature
Route::get('/orders/signature', [OrderController::class, 'signatureList'])->name('orders.signature');
Route::get('/order/{id}/signatureModal', [OrderController::class, 'modalSignature'])->name('orders.modal.signature');
Route::post('/order/{id}/signature', [OrderController::class, 'actionSignature'])->name('orders.action.signature');
Route::get('/orders/historique-signatures', [OrderController::class, 'historiqueSignatures'])->name('orders.historique-signatures');
Route::get('/orders/historique-signatures/export', [OrderController::class, 'exportHistoriqueSignatures'])->name('orders.historique-signatures.export');
Route::get('/order/{id}/historiqueSignatureDetails', [OrderController::class, 'modalHistoriqueSignatureDetails'])->name('orders.modal.historiqueSignatureDetails');
Route::post('/user/save-signature', [OrderController::class, 'saveUserSignature'])->name('user.save-signature');

// SuperAdmin pages
Route::get('/users', [App\Http\Controllers\UserController::class, 'index'])->name('users.index');
Route::post('/users', [App\Http\Controllers\UserController::class, 'store'])->name('users.store');
Route::put('/users/{id}', [App\Http\Controllers\UserController::class, 'update'])->name('users.update');
Route::delete('/users/{id}', [App\Http\Controllers\UserController::class, 'destroy'])->name('users.destroy');
Route::get('/users/{id}/edit', [App\Http\Controllers\UserController::class, 'modalEdit'])->name('users.modal.edit');
Route::get('/logs', [App\Http\Controllers\LogController::class, 'index'])->name('logs.index');
Route::get('/stats', [App\Http\Controllers\StatsController::class, 'index'])->name('stats.index');

// Affiche la page de profil et permet de modifier les informations de l'utilisateur
Route::get('/account/profile', [ProfileController::class, 'show'])->name('profile.show');
Route::post('/account/profile', [ProfileController::class, 'update'])->name('profile.update');

// Page "À propos"
Route::get('/about', [AboutController::class, 'about']);


