<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Order;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\PermissionValue;
use Database\Seeders\Status;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class OrderController extends BaseController
{
    // TODO Annotation pour utiliser la fonction auth() de AuthController pour chaque page
    public function viewOrders(?string $alertMsg = null): View|Response|RedirectResponse|Redirector
    {
        $request = request();

        // TODO réduire le nombre de requêtes et voir à propos du cache (je pense qu'on ne fera pas de cache mais on opti les requêtes)
        // TODO factoriser avec un déctorateur le code pour l'utilisateur et si possible factoriser l'envoi des variables courantes (ex: $suerPermissions)
        // @var User $user
        $user = Auth::user();
        $userRoles = $user->getRoles(); // Récupération des rôles en base de données
        $userPermissions = $user->getPermissions(); // Récupération d'un dictionnaire des permissions pour simplifier la vérification de permissions
        $userDepartments = $userRoles->filter(fn (Role $role) => $role->isDepartment());

        $search = $request->input('search');

        // Initialisation de la requête
        $query = Order::query();

        $userId = $user->getId();

        // Récupération uniquement des commandes dont l'utilisateur a accès
        if (! $user->hasPermission(PermissionValue::CONSULTER_TOUTES_COMMANDES)) {
            $query->where(function (Builder $q) use ($userDepartments, $userPermissions) {
                $userDepartments->each(function (Role $department) use ($q, $userPermissions) {
                    if ($userPermissions[PermissionValue::CONSULTER_COMMANDES_DEPARTMENT->value]) {
                        $q->orWhere('department_id', $department->getId());
                    }
                });
            });
        }

        // --- 2. TRI INTELLIGENT AVEC ENUMS ---

        // Définition des rôles
        $isDirecteur = $user->hasPermission(PermissionValue::SIGNER_BONS_DE_COMMANDES);
        $isFinancier = $user->hasPermission(PermissionValue::GERER_PAIEMENT_FOURNISSEURS);
        $isResponsableColis = $user->hasPermission(PermissionValue::GERER_COLIS_LIVRES);
        $isDepartment = $userDepartments->isNotEmpty();

        if ($isDirecteur) {
            // TRI DIRECTEUR
            $bcNonSigne = Status::BON_DE_COMMANDE_NON_SIGNE->value;
            $devis = Status::DEVIS->value;

            $query->orderByRaw("CASE
            WHEN status = '$bcNonSigne' THEN 1
            WHEN status = '$devis' THEN 2
            ELSE 3
        END");

        } elseif ($isFinancier) {
            // TRI FINANCIER
            $p1 = Status::SERVICE_FAIT->value;
            $p2 = Status::DEVIS->value;
            $p3 = Status::BON_DE_COMMANDE_NON_SIGNE->value;
            $p4 = Status::BON_DE_COMMANDE_REFUSE->value;
            $p5 = Status::LIVRE_ET_PAYE->value;

            $query->orderByRaw("CASE
            WHEN status = '$p1' THEN 1
            WHEN status = '$p2' THEN 2
            WHEN status = '$p3' THEN 3
            WHEN status = '$p4' THEN 4
            WHEN status = '$p5' THEN 5
            ELSE 6
        END");

        } elseif ($isResponsableColis) {
            // TRI RESPONSABLE COLIS
            // 1. En attente de livraison (Réponse reçue ou Partiel)
            // 2. Commande envoyée (Potentiellement en attente)
            // 3. Le reste

            $p1_Colis = implode("','", [
                Status::COMMANDE_AVEC_REPONSE->value,
                Status::PARTIELLEMENT_LIVRE->value,
            ]);

            $p2_Colis = Status::COMMANDE->value;

            $query->orderByRaw("CASE
            WHEN status IN ('$p1_Colis') THEN 1
            WHEN status = '$p2_Colis' THEN 2
            ELSE 3
        END");

        } elseif ($isDepartment) {
            // TRI DEPARTEMENTS
            $brouillon = Status::BROUILLON->value;

            $refusals = implode("','", [
                Status::DEVIS_REFUSE->value,
                Status::BON_DE_COMMANDE_REFUSE->value,
                Status::COMMANDE_REFUSEE->value,
            ]);

            $actionsRequises = implode("','", [
                Status::BON_DE_COMMANDE_SIGNE->value,
                Status::COMMANDE->value,
                Status::COMMANDE_AVEC_REPONSE->value,
                Status::PARTIELLEMENT_LIVRE->value,
            ]);

            $sqlSort = "CASE
            WHEN status = '$brouillon' AND author_id = ? THEN 1
            WHEN status IN ('$refusals') THEN 2
            WHEN status IN ('$actionsRequises') THEN 3
            ELSE 4
        END";

            $query->orderByRaw($sqlSort, [$user->getId()]);
            $query->orderByRaw($sqlSort, [$user->getId()]);
        }

        // 2.1 Filtre de recherche (si rempli)
        if ($search) {
            $query->where(function (Builder $q) use ($search) {
                $q->where('order_num', 'LIKE', "%{$search}%")
                    ->orWhere('title', 'LIKE', "%{$search}%")
                    ->orWhere('status', 'LIKE', "%{$search}%")
                    ->orWhere('quote_num', 'LIKE', "%{$search}%");
            });
        }

        // Filtre par statut
        $statusFilter = $request->input('status');
        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        // --- 3. TRI SECONDAIRE (Date) ---
        // Les plus anciennes (date lointaine) en premier
        $query->orderBy('updated_at', 'asc');

        // --- 4. EXECUTION ---
        $query->with(['supplier', 'department', 'author']);
        $orders = $query->paginate(20)->withQueryString();

        $suppliers = Supplier::all(['id', 'company_name', 'is_valid']); // Récupération uniquement des informations utiles à propos des fournisseurs

        // TODO flash messages: redirect('urls.create')->with('success', 'URL has been added');
        return view('orders', [
            'user' => $user,
            'orders' => $orders,
            'validSupplierNames' => $suppliers->where('is_valid', true)->map(fn (Supplier $supplier) => $supplier->getCompanyName())->values()->toArray(),
            'userDepartments' => $userDepartments,
            'search' => $search,
            'statusFilter' => $statusFilter,
        ]);
    }

    public function submitNewOrder(): RedirectResponse|Redirector
    {

        $request = request();
        $user = Auth::user();
        $orderNum = $request['order_num'];

        try {
            // 1) VALIDATION

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'supplier_name' => 'required|exists:suppliers,company_name',
                'order_num' => 'required|string|max:255',
                'quote_num' => 'required|string|max:255',
                'department_name' => 'nullable|exists:roles,name',
                'description' => 'nullable|string',
                'status' => 'required|string',
                'cost' => 'nullable|numeric',
                'quote' => 'nullable|file|mimes:pdf|max:20480',
                'purchase_order' => 'nullable|file|mimes:pdf|max:20480',
            ]);

            $userDepartment = $user->getDepartments()->first();
            $departmentName = $request['department_name'];

            $description = $request['description'];
            $quote_num = $request['quote_num'];
            $status = $request['status'];
            $cost = $request['cost'];
            $isSigned = $request['signed'];

            $department = Role::where('name', $departmentName ? $departmentName : $userDepartment->getName())->firstOrFail();
            $supplier = Supplier::where('company_name', $request['supplier_name'])->firstOrFail();

            // 2 CRÉATION DE LA COMMANDE
            $order = new Order([
                'title' => $validated['title'],
                'order_num' => $validated['order_num'],
                'status' => $validated['status'],
                'author_id' => $user->getId(),
                'department_id' => $department->getId(),
                'supplier_id' => $supplier->getId(),
            ]);

            // 3 ATTRIBUTION DES CHAMPS
            if (isset($quote_num)) {
                $order->setQuoteNumber($quote_num, false);
            }

            if (isset($description)) {
                $order->setDescription($description, false);
            }

            $order->setStatus($status, false);

            if (isset($cost)) {
                $order->setCost($cost, false);
            }

            // 4 UPLOAD DU DEVIS
            if ($request->hasFile('quote')) {
                $order->uploadQuote($request, false);
            }

            // 5 UPLOAD DU DEVIS
            if ($request->hasFile('purchase_order')) {
                $order->uploadPurchaseOrder($request, $isSigned, false);
            }

            // 6 SAUVEGARDE
            $order->save();

            $order->logs()->create(['content' => 'Commande créée.', 'type' => 'created', 'author_id' => Auth::id()]);

            session()->flash('success', 'La commande N°'.$order->getOrderNumber().' a été créée avec succès.');

        } catch (\Throwable $t) {
            session()->flash('error', 'Une erreur est survenue lors de la création de la commande commande N°'.$orderNum.'.');
        }

        return redirect('orders');
    }

    // --- WIZARD CRÉATION COMMANDE ---

    public function createStep1()
    {
        $user = Auth::user();
        if (! $user->hasPermission(PermissionValue::CREER_COMMANDES)) {
            abort(403, "Vous n'avez pas la permission de créer des commandes.");
        }

        $departments = $user->getRoles()->filter(fn (Role $role) => $role->isDepartment());

        return view('orders.create-step1', [
            'user' => $user,
            'departments' => $departments,
            'step1' => session('order_step1', []),
            'currentStep' => 1,
        ]);
    }

    public function storeStep1()
    {
        $user = Auth::user();
        if (! $user->hasPermission(PermissionValue::CREER_COMMANDES)) {
            abort(403, "Vous n'avez pas la permission de créer des commandes.");
        }

        $validated = request()->validate([
            'department_id' => 'required|exists:roles,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'desired_delivery_date' => 'required|date|after_or_equal:today',
            'delivery_location' => 'required|string|max:255',
        ]);

        session()->put('order_step1', $validated);

        return redirect()->route('orders.create.step2');
    }

    public function createStep2()
    {
        $user = Auth::user();
        if (! $user->hasPermission(PermissionValue::CREER_COMMANDES)) {
            abort(403, "Vous n'avez pas la permission de créer des commandes.");
        }

        if (! session()->has('order_step1')) {
            return redirect()->route('orders.create.step1');
        }

        $suppliers = Supplier::where('is_valid', true)->get();

        return view('orders.create-step2', [
            'user' => $user,
            'suppliers' => $suppliers,
            'step2' => session('order_step2', []),
            'currentStep' => 2,
        ]);
    }

    public function storeStep2()
    {
        $user = Auth::user();
        if (! $user->hasPermission(PermissionValue::CREER_COMMANDES)) {
            abort(403, "Vous n'avez pas la permission de créer des commandes.");
        }

        $request = request();

        $rules = [
            'supplier_id' => ($request->input('supplier_id') === 'new') ? 'required|string' : 'required|exists:suppliers,id',
            'quote_num' => 'required|string|max:255',
            'articles' => 'required|array|min:1',
            'articles.*.designation' => 'required|string|max:255',
            'articles.*.quantity' => 'required|integer|min:1',
            'articles.*.unit_price' => 'required|numeric|min:0',
            'articles.*.vat_rate' => 'required|numeric|min:0',
        ];

        // Validation nouveau fournisseur
        if ($request->input('supplier_id') === 'new') {
            $rules['new_supplier_name'] = 'required|string|max:255';
            $rules['new_supplier_email'] = 'required|email|max:255';
            $rules['new_supplier_siret'] = 'required|string|size:14';
        }

        $validated = $request->validate($rules);

        // Fichier devis stocké en session via chemin temp uniquement si présent
        if ($request->hasFile('quote')) {
            $validated['quote_path_temp'] = $request->file('quote')->store('temp/quotes', 'public');
        }

        session()->put('order_step2', $validated);

        return redirect()->route('orders.create.step3');
    }

    public function createStep3()
    {
        $user = Auth::user();
        if (! $user->hasPermission(PermissionValue::CREER_COMMANDES)) {
            abort(403, "Vous n'avez pas la permission de créer des commandes.");
        }

        if (! session()->has('order_step1') || ! session()->has('order_step2')) {
            return redirect()->route('orders.create.step1');
        }

        $step1 = session('order_step1');
        $step2 = session('order_step2');
        $department = Role::find($step1['department_id']);

        // Résolution fournisseur
        if ($step2['supplier_id'] === 'new') {
            $supplierName = $step2['new_supplier_name'];
        } else {
            $supplier = Supplier::find($step2['supplier_id']);
            $supplierName = $supplier ? $supplier->getCompanyName() : 'Inconnu';
        }

        // Calculs totaux
        $totalHt = 0;
        $totalVat = 0;
        foreach ($step2['articles'] as $article) {
            $ht = $article['quantity'] * $article['unit_price'];
            $vat = $ht * ($article['vat_rate'] / 100);
            $totalHt += $ht;
            $totalVat += $vat;
        }

        return view('orders.create-step3', [
            'user' => Auth::user(),
            'step1' => $step1,
            'step2' => $step2,
            'department' => $department,
            'supplierName' => $supplierName,
            'totalHt' => round($totalHt, 2),
            'totalVat' => round($totalVat, 2),
            'totalTtc' => round($totalHt + $totalVat, 2),
            'currentStep' => 3,
        ]);
    }

    public function storeStep3()
    {
        $user = Auth::user();
        if (! $user->hasPermission(PermissionValue::CREER_COMMANDES)) {
            abort(403, "Vous n'avez pas la permission de créer des commandes.");
        }

        $step1 = session('order_step1');
        $step2 = session('order_step2');

        if (! $step1 || ! $step2) {
            return redirect()->route('orders.create.step1');
        }

        // Création fournisseur si nouveau
        if ($step2['supplier_id'] === 'new') {
            $supplier = Supplier::create([
                'company_name' => $step2['new_supplier_name'],
                'email' => $step2['new_supplier_email'],
                'siret' => $step2['new_supplier_siret'],
                'is_valid' => false,
            ]);
            $supplierId = $supplier->id;
        } else {
            $supplierId = $step2['supplier_id'];
        }

        // Generation order_num auto
        $year = now()->year;
        $lastOrder = Order::where('order_num', 'like', "CMD-{$year}-%")->orderByDesc('id')->first();
        $nextNum = $lastOrder ? ((int) substr($lastOrder->order_num, -4)) + 1 : 1;
        $orderNum = sprintf('CMD-%d-%04d', $year, $nextNum);

        // Creation de la commande
        $order = new Order([
            'title' => $step1['title'],
            'description' => $step1['description'] ?? null,
            'delivery_location' => $step1['delivery_location'],
            'desired_delivery_date' => $step1['desired_delivery_date'],
            'order_num' => $orderNum,
            'quote_num' => $step2['quote_num'],
            'status' => Status::DEVIS->value,
            'author_id' => $user->getId(),
            'department_id' => $step1['department_id'],
            'supplier_id' => $supplierId,
        ]);
        $order->save();

        // Création des articles
        foreach ($step2['articles'] as $articleData) {
            $ttc = round($articleData['quantity'] * $articleData['unit_price'] * (1 + $articleData['vat_rate'] / 100), 2);
            $order->articles()->create([
                'designation' => $articleData['designation'],
                'quantity' => $articleData['quantity'],
                'unit_price' => $articleData['unit_price'],
                'vat_rate' => $articleData['vat_rate'],
                'total_ttc' => $ttc,
            ]);
        }

        // Upload devis si stocké en temp
        if (! empty($step2['quote_path_temp'])) {
            $tempPath = $step2['quote_path_temp'];
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($tempPath)) {
                $finalPath = str_replace('temp/quotes', 'uploads/orders/'.$order->getOrderNumber(), $tempPath);
                \Illuminate\Support\Facades\Storage::disk('public')->move($tempPath, $finalPath);
                $order->path_quote = $finalPath;
                $order->save();
            }
        }

        // Recalcul totaux
        $order->load('articles');
        $order->recalculateTotals();

        // Log
        $order->logs()->create(['content' => 'Commande créée via wizard.', 'type' => 'created', 'author_id' => Auth::id()]);

        // Nettoyage session
        session()->forget(['order_step1', 'order_step2']);

        session()->flash('success', 'La commande N°'.$order->getOrderNumber().' a été créée avec succès.');

        return redirect('/dashboard');
    }

    public function actionUploadPurchaseOrder($page = 1)
    {
        $request = request();
        $id = $request['id'];
        // @var Order $order
        $order = Order::findOrFail($id);

        // Vérification de permissions
        $user = Auth::user();
        if (! ($user->hasPermission(PermissionValue::GERER_BONS_DE_COMMANDES) || $user->hasPermission(PermissionValue::SIGNER_BONS_DE_COMMANDES) || $user->hasRole($order->getDepartment()))) {
            session()->flash('purchaseOrderError-'.$id, "Vous n'avez pas la permission d'ajouter un bon de commande");

            return $this->modalUploadPurchaseOrder($id);
        }
        $nextStep = $request['nextStep'];
        $isSigned = $request['signed'];

        // Stockage du fichier dans storage/app/public/quotes
        // TODO ce serait bien que upload purchase order retourne le validator pour avoir l'erreur personnalisée
        $success = $order->uploadPurchaseOrder($request, $isSigned, false);
        if (! $success) {
            session()->flash('purchaseOrderError-'.$id, "Une erreur est survenue à l'enregistrement du bon de commande");

            return $this->modalUploadPurchaseOrder($id);
        }

        if ($nextStep) {
            $order->setStatus($isSigned ? Status::BON_DE_COMMANDE_SIGNE : Status::BON_DE_COMMANDE_NON_SIGNE, false);
        }

        $successToSave = $order->save();
        if (! $successToSave) {
            session()->flash('purchaseOrderError-'.$id, 'Une erreur est survenue à la sauvegarde de la commande !');

            return $this->modalUploadPurchaseOrder($id);
        }

        $logContent = $isSigned ? 'Bon de commande signé et ajouté.' : 'Bon de commande ajouté.';
        $order->logs()->create(['content' => $logContent, 'type' => 'document', 'author_id' => Auth::id()]);

        return BaseController::getSuccessModal('Le bon de commande a été ajouté avec succès à la commande N°'.$order->getOrderNumber().'.');
    }

    public function modalUploadPurchaseOrder($id)
    {

        // @var Order $order
        $sign = request()['sign'];
        $order = Order::where('id', $id)->first();

        $user = Auth::user();

        // On retourne une vue partielle (sans header, footer, etc.)
        // render() est important si vous voulez manipuler le string,
        // mais return view() suffit souvent car Laravel le convertit en string.
        return view('components.addPurchaseOrderModal', [
            'user' => Auth::user(),
            'order' => $order,
            'orderId' => $order->getId(),
            'canSign' => $sign || $user->hasPermission(PermissionValue::SIGNER_BONS_DE_COMMANDES),
        ]);
    }

    public function modalRefuse($id)
    {
        $order = Order::where('id', $id)->first();
        $about = request()['about'];

        return view('components.refuseOrderModal', [
            'user' => Auth::user(),
            'order' => $order,
            'orderId' => $order->getId(),
            'about' => $about,
        ]);
    }

    public function modalPaid($id)
    {
        $order = Order::where('id', $id)->first();

        return view('components.paidOrderModal', [
            'user' => Auth::user(),
            'order' => $order,
            'orderId' => $order->getId(),
        ]);
    }

    public function modalUploadDeliveryNote($id)
    {
        $order = Order::where('id', $id)->first();

        return view('components.addDeliveryNoteModal', [
            'user' => Auth::user(),
            'order' => $order,
            'orderId' => $order->getId(),
        ]);
    }

    public function modalSentToSupplier($id)
    {
        $order = Order::where('id', $id)->first();

        return view('components.sentToSupplier', [
            'user' => Auth::user(),
            'order' => $order,
            'orderId' => $order->getId(),
            'supplierEmail' => $order->getSupplier()->getEmail(),
        ]);
    }

    public function modalDeliveredPackages($id)
    {
        $order = Order::where('id', $id)->first();
        $packages = $order->getPackages();

        return view('components.deliveredPackagesModal', [
            'user' => Auth::user(),
            'order' => $order,
            'orderId' => $order->getId(),
            'packages' => $packages,
        ]);
    }

    public function modalDeliveredAll(string $id)
    {
        $order = Order::where('id', $id)->first();

        return view('components.deliveredAllModal', [
            'user' => Auth::user(),
            'order' => $order,
            'orderId' => $order->getId(),
        ]);
    }

    public function modalViewDetails(string $id)
    {
        $user = Auth::user();
        $request = request();

        // @var Order $order
        $order = Order::with(['logs.author', 'articles', 'comments.author'])->where('id', $id)->first();
        $orderId = $order->getId();
        $edit = $request['edit'];

        if ($request->method() === 'POST') {
            // TODO corriger le fait que le message erreur ou succès il apparaît seulement au bout de 2 actualisations, pas une.

            if ($edit && (($user->hasPermission(PermissionValue::MODIFIER_COMMANDES_DEPARTEMENT) && $user->hasRole($order->getDepartment())) || $user->hasPermission(PermissionValue::MODIFIER_TOUTES_COMMANDES))) {
                $title = $request['title'];
                $orderNum = $request['order_num'];
                $quoteNum = $request['quote_num'];
                $description = $request['description'];
                $cost = $request['cost'];
                $status = $request['status'];
                $quote = $request['quote'];
                $purchaseOrder = $request['purchase_order'];
                $deliveryNote = $request['delivery_note'];

                if (isset($title)) {
                    $order->setTitle($title, false);
                }
                if (isset($orderNum)) {
                    $order->setOrderNumber($orderNum, false);
                }
                if (isset($quoteNum)) {
                    $order->setQuoteNumber($quoteNum, false);
                }

                if (isset($description)) {
                    $order->setDescription($description, false);
                }

                if (isset($cost)) {
                    $order->setCost($cost, false);
                }

                if ($request->hasFile('quote')) {
                    $order->uploadQuote($request, false);
                }

                if ($request->hasFile('purchase_order')) {
                    $order->uploadPurchaseOrder($request, false);
                }

                if ($request->hasFile('delivery_note')) {
                    $order->uploadDeliveryNote($request, false);
                }

                $order->setStatus($status, false);

                $order->save();

                $order->logs()->create(['content' => 'Commande modifiée.', 'type' => 'edit', 'author_id' => Auth::id()]);

                session()->flash('orderSuccess', 'La commande a été mise à jour !');
            } else {
                session()->flash('orderError-'.$orderId, "Vous n'avez pas la permission de modifier cette commande");
                $edit = false;
            }
        }

        return view('components.viewOrderModal', [
            'user' => $user,
            'order' => $order,
            'orderId' => $orderId,
            'edit' => $edit,
            'userDepartments' => $user->getDepartments(),
            'logs' => $order->logs()->with('author')->orderBy('created_at', 'desc')->get(),
        ]);

    }

    public function actionRefuse($id)
    {
        $request = request();
        $order = Order::findOrFail($id);
        $user = Auth::user();
        $about = $request->input('about');
        $reason = $request->input('reason');

        // Déterminer le statut cible selon le contexte du refus
        $statusMap = [
            'purchaseOrder' => ['permission' => PermissionValue::GERER_BONS_DE_COMMANDES, 'status' => Status::DEVIS_REFUSE],
            'purchaseOrderSignature' => ['permission' => PermissionValue::SIGNER_BONS_DE_COMMANDES, 'status' => Status::BON_DE_COMMANDE_REFUSE],
            'supplier' => ['permission' => null, 'status' => Status::COMMANDE_REFUSEE],
        ];

        if (!isset($statusMap[$about])) {
            session()->flash('refuseError-'.$id, 'Contexte de refus invalide.');
            return $this->modalRefuse($id);
        }

        $config = $statusMap[$about];

        // Vérification permission
        if ($config['permission'] !== null && !$user->hasPermission($config['permission'])) {
            session()->flash('refuseError-'.$id, "Vous n'avez pas la permission de refuser cette commande.");
            return $this->modalRefuse($id);
        }
        if ($about === 'supplier' && !$user->hasRole($order->getDepartment())) {
            session()->flash('refuseError-'.$id, "Vous n'avez pas la permission de refuser cette commande.");
            return $this->modalRefuse($id);
        }

        $order->setStatus($config['status'], false);
        $order->save();

        $order->logs()->create([
            'content' => 'Commande refusée ('.$about.') : '.$reason,
            'type' => 'status_change',
            'author_id' => Auth::id(),
        ]);

        return BaseController::getSuccessModal('La commande N°'.$order->getOrderNumber().' a été refusée.');
    }

    public function actionPaid($id)
    {
        $order = Order::findOrFail($id);
        $user = Auth::user();

        if (!$user->hasPermission(PermissionValue::GERER_BONS_DE_COMMANDES)) {
            session()->flash('paidError-'.$id, "Vous n'avez pas la permission de marquer cette commande comme payée.");
            return $this->modalPaid($id);
        }

        $order->setStatus(Status::LIVRE_ET_PAYE, false);
        $order->save();

        $order->logs()->create([
            'content' => 'Commande marquée comme payée.',
            'type' => 'status_change',
            'author_id' => Auth::id(),
        ]);

        return BaseController::getSuccessModal('La commande N°'.$order->getOrderNumber().' a été marquée comme payée.');
    }

    public function actionSentToSupplier($id)
    {
        $request = request();
        $order = Order::findOrFail($id);
        $user = Auth::user();

        if (!$user->hasRole($order->getDepartment())) {
            session()->flash('sentToSupplierError-'.$id, "Vous n'êtes pas membre du département de cette commande.");
            return $this->modalSentToSupplier($id);
        }

        $order->setStatus(Status::COMMANDE, false);
        $order->save();

        $order->logs()->create([
            'content' => 'Bon de commande envoyé au fournisseur.',
            'type' => 'status_change',
            'author_id' => Auth::id(),
        ]);

        // Envoi mail si demand��
        if ($request->input('sendMail')) {
            $this->sendAutoMail($request);
        }

        return BaseController::getSuccessModal('La commande N°'.$order->getOrderNumber().' a été envoyée au fournisseur.');
    }

    public function actionUploadDeliveryNote($id)
    {
        $request = request();
        $order = Order::findOrFail($id);
        $user = Auth::user();

        if (!$user->hasRole($order->getDepartment())) {
            session()->flash('deliveryNoteError-'.$id, "Vous n'êtes pas membre du département de cette commande.");
            return $this->modalUploadDeliveryNote($id);
        }

        $success = $order->uploadDeliveryNote($request, false);
        if (!$success) {
            session()->flash('deliveryNoteError-'.$id, "Une erreur est survenue lors de l'enregistrement du bon de livraison.");
            return $this->modalUploadDeliveryNote($id);
        }

        $order->setStatus(Status::COMMANDE_AVEC_REPONSE, false);
        $order->save();

        $order->logs()->create([
            'content' => 'Bon de livraison ajouté.',
            'type' => 'document',
            'author_id' => Auth::id(),
        ]);

        return BaseController::getSuccessModal('Le bon de livraison a été ajouté à la commande N°'.$order->getOrderNumber().'.');
    }

    public function actionDeliveredPackages($id)
    {
        $request = request();
        $order = Order::findOrFail($id);
        $user = Auth::user();

        if (!$user->hasRole($order->getDepartment())) {
            session()->flash('deliveredPackagesError-'.$id, "Vous n'êtes pas membre du département de cette commande.");
            return $this->modalDeliveredPackages($id);
        }

        $packageIds = $request->input('packages', []);
        if (empty($packageIds)) {
            session()->flash('deliveredPackagesError-'.$id, 'Veuillez sélectionner au moins un colis.');
            return $this->modalDeliveredPackages($id);
        }

        // Mise à jour des colis sélectionnés
        $now = now()->toDateString();
        foreach ($order->getPackages() as $package) {
            if (in_array($package->getId(), $packageIds)) {
                $package->setShippingDate($now, false);
                $package->save();
            }
        }

        $order->setStatus(Status::PARTIELLEMENT_LIVRE, false);
        $order->save();

        $order->logs()->create([
            'content' => count($packageIds).' colis marqué(s) comme livré(s).',
            'type' => 'delivery',
            'author_id' => Auth::id(),
        ]);

        return BaseController::getSuccessModal('Les colis sélectionnés ont été marqués comme livrés.');
    }

    public function actionDeliveredAll($id)
    {
        $request = request();
        $order = Order::findOrFail($id);
        $user = Auth::user();

        if (!$user->hasRole($order->getDepartment())) {
            session()->flash('deliveredAllError-'.$id, "Vous n'êtes pas membre du département de cette commande.");
            return $this->modalDeliveredAll($id);
        }

        // Upload optionnel du bon de livraison
        if ($request->hasFile('delivery_note')) {
            $order->uploadDeliveryNote($request, false);
        }

        // Marquer tous les colis comme livrés
        $now = now()->toDateString();
        foreach ($order->getPackages() as $package) {
            if (!$package->getShippingDate()) {
                $package->setShippingDate($now, false);
                $package->save();
            }
        }

        $order->setStatus(Status::SERVICE_FAIT, false);
        $order->save();

        $order->logs()->create([
            'content' => 'Tous les colis livrés. Service fait.',
            'type' => 'delivery',
            'author_id' => Auth::id(),
        ]);

        return BaseController::getSuccessModal('La commande N°'.$order->getOrderNumber().' a été marquée comme totalement livrée.');
    }

    public function sendAutoMail(Request $request)
    {
        $to = $request->input('email');
        $content = $request->input('mail_content', '');
        $subject = $request->input('subject', 'Commande - IUT Villetaneuse');

        if (!$to) {
            return;
        }

        Mail::raw($content, function ($message) use ($to, $subject) {
            $message->to($to)->subject($subject);
        });
    }

    // Agent - Historique
    public function historiqueAgent(): View
    {
        $user = Auth::user();
        $deptIds = $user->getDepartments()->pluck('id');

        $query = Order::with(['supplier', 'department', 'author'])
            ->whereIn('department_id', $deptIds);

        if (request('date_from')) {
            $query->whereDate('created_at', '>=', request('date_from'));
        }
        if (request('date_to')) {
            $query->whereDate('created_at', '<=', request('date_to'));
        }
        if (request('status')) {
            $query->where('status', request('status'));
        }

        $orders = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        $statuses = Status::cases();

        return view('orders.historique-agent', compact('orders', 'statuses'));
    }

    public function exportHistoriqueAgent()
    {
        $user = Auth::user();
        $deptIds = $user->getDepartments()->pluck('id');

        $query = Order::with(['supplier', 'department', 'author'])
            ->whereIn('department_id', $deptIds);

        if (request('date_from')) {
            $query->whereDate('created_at', '>=', request('date_from'));
        }
        if (request('date_to')) {
            $query->whereDate('created_at', '<=', request('date_to'));
        }
        if (request('status')) {
            $query->where('status', request('status'));
        }

        $orders = $query->orderByDesc('created_at')->get();

        $filename = 'historique_agent_' . now()->format('Y-m-d') . '.csv';
        $headers = ['Content-Type' => 'text/csv; charset=UTF-8', 'Content-Disposition' => "attachment; filename=\"$filename\""];

        $callback = function () use ($orders) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM UTF-8
            fputcsv($out, ['N° Commande', 'Demandeur', 'Fournisseur', 'Montant TTC', 'Date', 'Statut'], ';');
            foreach ($orders as $order) {
                fputcsv($out, [
                    $order->getOrderNumber(),
                    $order->author ? $order->author->getFirstName() . ' ' . $order->author->getLastName() : '',
                    $order->supplier?->getCompanyName() ?? '',
                    number_format($order->total_ttc ?? 0, 2, ',', ' ') . ' €',
                    $order->created_at->format('d/m/Y'),
                    $order->getStatus()->getLabel(),
                ], ';');
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    // CRIT - Réception
    // CRIT - Réception (filtres serveur)
    public function reception(): View
    {
        $query = Order::with(['supplier', 'department', 'author'])
            ->whereIn('status', [Status::COMMANDE->value, Status::COMMANDE_AVEC_REPONSE->value]);

        if (request('department')) {
            $query->where('department_id', request('department'));
        }
        if (request('supplier')) {
            $query->where('supplier_id', request('supplier'));
        }

        $orders = $query->orderByDesc('created_at')->get();

        $departments = Role::where('is_department', true)->orderBy('name')->get();
        $suppliers = Supplier::where('is_valid', true)->orderBy('company_name')->get();

        $kpiTotal = $orders->count();
        $kpiRecusAujourdhui = Log::where('type', 'delivery')
            ->where('content', 'like', '%réceptionné%')
            ->whereDate('created_at', today())->count();

        return view('orders.reception', compact('orders', 'departments', 'suppliers', 'kpiTotal', 'kpiRecusAujourdhui'));
    }

    public function receiveOrder(Request $request, $id): RedirectResponse
    {
        $order = Order::findOrFail($id);
        $order->setStatus(Status::PARTIELLEMENT_LIVRE, false);
        $order->save();

        $order->logs()->create([
            'content' => 'Colis réceptionné à l\'IUT.',
            'type' => 'delivery',
            'author_id' => Auth::id(),
        ]);

        if ($request->filled('comment')) {
            $order->comments()->create([
                'content' => $request->input('comment'),
                'author_id' => Auth::id(),
            ]);
        }

        if ($request->boolean('has_anomaly')) {
            $anomalyText = 'Anomalie : ' . $request->input('anomaly_type', 'non précisé');
            if ($request->filled('anomaly_description')) {
                $anomalyText .= ' - ' . $request->input('anomaly_description');
            }
            $order->comments()->create([
                'content' => $anomalyText,
                'author_id' => Auth::id(),
            ]);
            $order->logs()->create([
                'content' => 'Anomalie signalée lors de la réception.',
                'type' => 'delivery',
                'author_id' => Auth::id(),
            ]);
        }

        return redirect()->back()->with('success', 'Colis réceptionné.');
    }

    // CRIT - Distribution (filtres serveur)
    public function distribution(): View
    {
        $query = Order::with(['supplier', 'department', 'author', 'logs'])
            ->where('status', Status::PARTIELLEMENT_LIVRE->value);

        if (request('department')) {
            $query->where('department_id', request('department'));
        }

        $orders = $query->orderByDesc('updated_at')->get();

        // Calcul jours attente + filtre priorité
        $orders->each(function ($order) {
            $receptionLog = $order->logs->where('type', 'delivery')
                ->filter(fn($l) => str_contains($l->content, 'réceptionné'))->last();
            $order->reception_date = $receptionLog?->created_at;
            $order->jours_attente = $receptionLog ? (int) $receptionLog->created_at->diffInDays(now()) : 0;
        });

        if (request('priority') === 'retard') {
            $orders = $orders->filter(fn($o) => $o->jours_attente > 2)->values();
        } elseif (request('priority') === 'normal') {
            $orders = $orders->filter(fn($o) => $o->jours_attente <= 2)->values();
        }

        $departments = Role::where('is_department', true)->orderBy('name')->get();

        $kpiTotal = $orders->count();
        $kpiRetard = $orders->filter(fn($o) => $o->jours_attente > 2)->count();
        $kpiDistribuesAujourdhui = Log::where('type', 'delivery')
            ->where('content', 'like', '%distribué%')
            ->whereDate('created_at', today())->count();

        return view('orders.distribution', compact('orders', 'departments', 'kpiTotal', 'kpiRetard', 'kpiDistribuesAujourdhui'));
    }

    public function deliverOrder(Request $request, $id): RedirectResponse
    {
        $request->validate(['receiver_name' => 'nullable|string|max:255']);

        $order = Order::findOrFail($id);
        $order->setStatus(Status::SERVICE_FAIT, false);

        $receiverName = $request->input('receiver_name');
        if ($receiverName) {
            $order->receiver_name = $receiverName;
        }

        $order->save();

        $logContent = 'Colis distribue.';
        if ($receiverName) {
            $logContent = "Colis distribue. Receptionne par : $receiverName.";
        }

        $order->logs()->create([
            'content' => $logContent,
            'type' => 'delivery',
            'author_id' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Colis distribue.');
    }

    // CRIT - Historique
    public function historiqueCrit(): View
    {
        $query = Order::with(['supplier', 'department', 'author', 'logs'])
            ->whereIn('status', [
                Status::PARTIELLEMENT_LIVRE->value,
                Status::SERVICE_FAIT->value,
                Status::LIVRE_ET_PAYE->value,
            ]);

        if (request('department')) {
            $query->where('department_id', request('department'));
        }

        // Filtre période
        match (request('period')) {
            'today' => $query->whereDate('updated_at', today()),
            'week' => $query->where('updated_at', '>=', now()->startOfWeek()),
            'month' => $query->whereMonth('updated_at', now()->month)->whereYear('updated_at', now()->year),
            'year' => $query->whereYear('updated_at', now()->year),
            default => null,
        };

        $orders = $query->orderByDesc('updated_at')->get();

        // Enrichir avec dates réception/distribution
        $orders->each(function ($order) {
            $recLog = $order->logs->where('type', 'delivery')
                ->filter(fn($l) => str_contains($l->content, 'réceptionné'))->last();
            $distLog = $order->logs->where('type', 'delivery')
                ->filter(fn($l) => str_contains($l->content, 'distribué'))->last();
            $order->reception_date = $recLog?->created_at;
            $order->distribution_date = $distLog?->created_at;
            $order->delai_jours = ($recLog && $distLog) ? (int) $recLog->created_at->diffInDays($distLog->created_at) : null;
            $order->has_anomaly = $order->logs->contains(fn($l) => str_contains(strtolower($l->content), 'anomalie'));
        });

        // Filtre type
        if (request('type') === 'receptionnes') {
            $orders = $orders->filter(fn($o) => $o->reception_date && !$o->distribution_date)->values();
        } elseif (request('type') === 'distribues') {
            $orders = $orders->filter(fn($o) => $o->distribution_date)->values();
        } elseif (request('type') === 'anomalie') {
            $orders = $orders->filter(fn($o) => $o->has_anomaly)->values();
        }

        $departments = Role::where('is_department', true)->orderBy('name')->get();

        // KPIs
        $kpiTotal = $orders->count();
        $kpiMois = $orders->filter(fn($o) => $o->updated_at->isCurrentMonth())->count();
        $delais = $orders->filter(fn($o) => $o->delai_jours !== null)->pluck('delai_jours');
        $kpiDelaiMoyen = $delais->isNotEmpty() ? round($delais->avg(), 1) : '—';
        $kpiAnomalies = $orders->filter(fn($o) => $o->has_anomaly)->count();

        return view('orders.historique-crit', compact('orders', 'departments', 'kpiTotal', 'kpiMois', 'kpiDelaiMoyen', 'kpiAnomalies'));
    }

    // CRIT - Export CSV historique
    public function exportHistoriqueCrit()
    {
        $query = Order::with(['supplier', 'department', 'author', 'logs'])
            ->whereIn('status', [
                Status::PARTIELLEMENT_LIVRE->value,
                Status::SERVICE_FAIT->value,
                Status::LIVRE_ET_PAYE->value,
            ]);

        if (request('department')) $query->where('department_id', request('department'));

        match (request('period')) {
            'today' => $query->whereDate('updated_at', today()),
            'week' => $query->where('updated_at', '>=', now()->startOfWeek()),
            'month' => $query->whereMonth('updated_at', now()->month)->whereYear('updated_at', now()->year),
            'year' => $query->whereYear('updated_at', now()->year),
            default => null,
        };

        $orders = $query->orderByDesc('updated_at')->get();

        $orders->each(function ($order) {
            $recLog = $order->logs->where('type', 'delivery')->filter(fn($l) => str_contains($l->content, 'réceptionné'))->last();
            $distLog = $order->logs->where('type', 'delivery')->filter(fn($l) => str_contains($l->content, 'distribué'))->last();
            $order->reception_date = $recLog?->created_at;
            $order->distribution_date = $distLog?->created_at;
            $order->delai_jours = ($recLog && $distLog) ? (int) $recLog->created_at->diffInDays($distLog->created_at) : null;
        });

        $filename = 'historique_crit_' . now()->format('Y-m-d') . '.csv';
        $headers = ['Content-Type' => 'text/csv; charset=UTF-8', 'Content-Disposition' => "attachment; filename=\"$filename\""];

        $callback = function () use ($orders) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM UTF-8
            fputcsv($out, ['N° Commande', 'Destinataire', 'Département', 'Fournisseur', 'Date réception', 'Date distribution', 'Délai (j)'], ';');
            foreach ($orders as $order) {
                fputcsv($out, [
                    $order->getOrderNumber(),
                    $order->author ? $order->author->getFirstName() . ' ' . $order->author->getLastName() : '',
                    $order->department?->getName() ?? '',
                    $order->supplier?->company_name ?? '',
                    $order->reception_date?->format('d/m/Y H:i') ?? '',
                    $order->distribution_date?->format('d/m/Y H:i') ?? '',
                    $order->delai_jours ?? '',
                ], ';');
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    // Service Financier - Validation
    public function validationList(): View
    {
        $query = Order::with(['supplier', 'department', 'author'])
            ->where('status', Status::DEVIS->value);

        // Filtres serveur
        if (request('department')) {
            $query->where('department_id', request('department'));
        }
        if (request('supplier')) {
            $query->where('supplier_id', request('supplier'));
        }
        if (request('montant')) {
            match (request('montant')) {
                '0-500' => $query->where('total_ttc', '<=', 500),
                '500-1000' => $query->whereBetween('total_ttc', [500, 1000]),
                '1000-2500' => $query->whereBetween('total_ttc', [1000, 2500]),
                '2500+' => $query->where('total_ttc', '>', 2500),
                default => null,
            };
        }

        $orders = $query->orderByDesc('created_at')->get();

        // Enrichir avec jours d'attente
        $orders->each(function ($order) {
            $order->jours_attente = (int) $order->created_at->diffInDays(now());
        });

        // 3 KPIs
        $kpiTotal = Order::where('status', Status::DEVIS->value)->count();
        $kpiMontantTotal = number_format(
            Order::where('status', Status::DEVIS->value)->sum('total_ttc'), 2, ',', ' '
        ) . ' €';
        $kpiUrgentes = Order::where('status', Status::DEVIS->value)
            ->where('created_at', '<', now()->subDays(5))->count();

        $departments = Role::where('is_department', true)->orderBy('name')->get();
        $suppliers = Supplier::where('is_valid', true)->orderBy('company_name')->get();

        return view('orders.validation', compact(
            'orders', 'kpiTotal', 'kpiMontantTotal', 'kpiUrgentes',
            'departments', 'suppliers'
        ));
    }

    // Service Financier - Suivi
    public function suiviSF(): View
    {
        $query = Order::with(['supplier', 'department', 'author'])
            ->whereNotIn('status', [Status::BROUILLON->value, Status::DEVIS->value, Status::DEVIS_REFUSE->value, Status::ANNULE->value]);

        // Filtres serveur
        if (request('department')) {
            $query->where('department_id', request('department'));
        }
        if (request('supplier')) {
            $query->where('supplier_id', request('supplier'));
        }
        if (request('status')) {
            $query->where('status', request('status'));
        }
        if (request('date_from')) {
            $query->whereDate('created_at', '>=', request('date_from'));
        }
        if (request('date_to')) {
            $query->whereDate('created_at', '<=', request('date_to'));
        }

        $orders = $query->orderByDesc('updated_at')->paginate(20)->withQueryString();

        // 5 KPIs (sur toutes les commandes, sans filtres)
        $kpiBcNonSignes = Order::where('status', Status::BON_DE_COMMANDE_NON_SIGNE->value)->count();
        $kpiBcSignes = Order::where('status', Status::BON_DE_COMMANDE_SIGNE->value)->count();
        $kpiCommandes = Order::where('status', Status::COMMANDE->value)->count();
        $kpiServiceFait = Order::where('status', Status::SERVICE_FAIT->value)->count();
        $kpiPayees = Order::where('status', Status::LIVRE_ET_PAYE->value)->count();

        $departments = Role::where('is_department', true)->orderBy('name')->get();
        $suppliers = Supplier::where('is_valid', true)->orderBy('company_name')->get();
        $statuses = Status::cases();

        return view('orders.suivi-sf', compact(
            'orders', 'kpiBcNonSignes', 'kpiBcSignes', 'kpiCommandes',
            'kpiServiceFait', 'kpiPayees', 'departments', 'suppliers', 'statuses'
        ));
    }

    public function exportSuiviSF()
    {
        $query = Order::with(['supplier', 'department', 'author'])
            ->whereNotIn('status', [Status::BROUILLON->value, Status::DEVIS->value, Status::DEVIS_REFUSE->value, Status::ANNULE->value]);

        if (request('department')) $query->where('department_id', request('department'));
        if (request('supplier')) $query->where('supplier_id', request('supplier'));
        if (request('status')) $query->where('status', request('status'));
        if (request('date_from')) $query->whereDate('created_at', '>=', request('date_from'));
        if (request('date_to')) $query->whereDate('created_at', '<=', request('date_to'));

        $orders = $query->orderByDesc('updated_at')->get();

        $filename = 'suivi_sf_' . now()->format('Y-m-d') . '.csv';
        $headers = ['Content-Type' => 'text/csv; charset=UTF-8', 'Content-Disposition' => "attachment; filename=\"$filename\""];

        $callback = function () use ($orders) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM UTF-8
            fputcsv($out, ['N° Commande', 'Departement', 'Fournisseur', 'Montant TTC', 'Statut', 'Date creation', 'Derniere MAJ'], ';');
            foreach ($orders as $order) {
                fputcsv($out, [
                    $order->getOrderNumber(),
                    $order->department?->getName() ?? '',
                    $order->supplier?->getCompanyName() ?? '',
                    number_format($order->total_ttc ?? 0, 2, ',', ' ') . ' €',
                    $order->getStatus()->getLabel(),
                    $order->created_at->format('d/m/Y'),
                    $order->updated_at->format('d/m/Y'),
                ], ';');
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    // Directeur - Signature
    public function signatureList(Request $request): View
    {
        $query = Order::with(['supplier', 'department', 'author'])
            ->where('status', Status::BON_DE_COMMANDE_NON_SIGNE->value);

        // Filtres GET
        if ($request->filled('department')) {
            $query->where('department_id', $request->input('department'));
        }
        if ($request->filled('montant')) {
            match ($request->input('montant')) {
                '0-1000' => $query->where('total_ttc', '<=', 1000),
                '1000-5000' => $query->whereBetween('total_ttc', [1000, 5000]),
                '5000-10000' => $query->whereBetween('total_ttc', [5000, 10000]),
                '10000+' => $query->where('total_ttc', '>', 10000),
                default => null,
            };
        }
        if ($request->input('priorite') === 'urgent') {
            $query->where('created_at', '<', now()->subDays(7));
        } elseif ($request->input('priorite') === 'normal') {
            $query->where('created_at', '>=', now()->subDays(7));
        }

        $orders = $query->orderBy('created_at', 'asc')->get();

        // Enrichir avec jours d'attente
        $orders->each(function ($order) {
            $order->jours_attente = (int) $order->created_at->diffInDays(now());
        });

        // 3 KPIs
        $kpiTotal = Order::where('status', Status::BON_DE_COMMANDE_NON_SIGNE->value)->count();
        $kpiUrgents = Order::where('status', Status::BON_DE_COMMANDE_NON_SIGNE->value)
            ->where('created_at', '<', now()->subDays(7))->count();
        $kpiMontantTotal = number_format(
            Order::where('status', Status::BON_DE_COMMANDE_NON_SIGNE->value)->sum('total_ttc'),
            2, ',', ' '
        ) . ' €';

        $departments = Role::where('is_department', true)->orderBy('name')->get();

        return view('orders.signature', compact(
            'orders', 'kpiTotal', 'kpiUrgents', 'kpiMontantTotal', 'departments'
        ));
    }

    // Directeur - Modal signature
    public function modalSignature(string $id): View
    {
        $order = Order::with(['supplier', 'department', 'author', 'articles', 'comments.author'])->findOrFail($id);
        $user = Auth::user();
        return view('components.signatureModal', compact('order', 'user'));
    }

    // Directeur - Action signer BC
    public function actionSignature(Request $request, string $id)
    {
        $order = Order::findOrFail($id);
        $user = Auth::user();

        if (!$user->hasPermission(PermissionValue::SIGNER_BONS_DE_COMMANDES)) {
            return BaseController::getErrorModal('Permission refusée.');
        }
        if ($order->getStatus() !== Status::BON_DE_COMMANDE_NON_SIGNE) {
            return BaseController::getErrorModal('Ce bon de commande ne peut pas être signé dans son état actuel.');
        }

        $action = $request->input('action');

        if ($action === 'sign') {
            $signedAt = now();

            $order->setStatus(Status::BON_DE_COMMANDE_SIGNE, false);
            $order->signed_by_user_id = $user->getId();
            $order->signed_at = $signedAt;
            $order->save();

            // Générer le PDF du BC signé
            $this->generateSignedBcPdf($order, $user, $signedAt);

            Log::create([
                'type' => 'status_change',
                'content' => 'BC signé par ' . $user->getFirstName() . ' ' . $user->getLastName(),
                'order_id' => $order->getId(),
                'author_id' => $user->getId(),
            ]);

            if ($request->filled('comment')) {
                $order->comments()->create([
                    'content' => $request->input('comment'),
                    'author_id' => $user->getId(),
                ]);
            }

            return BaseController::getSuccessModal('Le bon de commande N°' . $order->getOrderNumber() . ' a été signé.');

        } elseif ($action === 'refuse') {
            $request->validate(['comment' => 'required|string|min:3']);

            $order->setStatus(Status::BON_DE_COMMANDE_REFUSE, false);
            $order->save();

            Log::create([
                'type' => 'status_change',
                'content' => 'BC refusé par ' . $user->getFirstName() . ' ' . $user->getLastName(),
                'order_id' => $order->getId(),
                'author_id' => $user->getId(),
            ]);

            $order->comments()->create([
                'content' => $request->input('comment'),
                'author_id' => $user->getId(),
            ]);

            return BaseController::getSuccessModal('Le bon de commande N°' . $order->getOrderNumber() . ' a été refusé.');
        }

        return redirect()->back();
    }

    // Enregistre la signature manuscrite du directeur
    public function saveUserSignature(Request $request)
    {
        $user = Auth::user();

        if (!$request->filled('signature_data')) {
            return response()->json(['status' => 'error', 'message' => 'Aucune signature fournie.'], 422);
        }

        $signatureData = $request->input('signature_data');
        $signatureData = str_replace('data:image/png;base64,', '', $signatureData);
        $signatureData = str_replace(' ', '+', $signatureData);
        $fileName = 'signature_user_' . $user->getId() . '_' . time() . '.png';
        $path = 'uploads/signatures/' . $fileName;

        \Illuminate\Support\Facades\Storage::disk('public')->put($path, base64_decode($signatureData));

        // Supprimer l'ancienne si elle existe
        if ($user->getAttribute('signature_path')) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($user->getAttribute('signature_path'));
        }

        $user->signature_path = $path;
        $user->save();

        return response()->json(['status' => 'success', 'message' => 'Signature enregistrée.']);
    }

    // Genere le PDF du BC signe
    private function generateSignedBcPdf(Order $order, $signer, $signedAt): void
    {
        $order->load(['author', 'department', 'supplier', 'articles']);

        // Récupérer l'image de signature en base64
        $signatureImageBase64 = null;
        if ($signer->hasSignature()) {
            $sigPath = $signer->getAttribute('signature_path');
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($sigPath)) {
                $signatureImageBase64 = base64_encode(
                    \Illuminate\Support\Facades\Storage::disk('public')->get($sigPath)
                );
            }
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.signed-bc', [
            'order' => $order,
            'signer' => $signer,
            'signedAt' => $signedAt,
            'signatureImageBase64' => $signatureImageBase64,
        ]);

        $fileName = 'BC-' . $order->getOrderNumber() . '-SIGNE.pdf';
        $storagePath = 'uploads/orders/' . $order->getOrderNumber() . '/' . $fileName;

        \Illuminate\Support\Facades\Storage::disk('public')->put($storagePath, $pdf->output());

        $order->path_signed_purchase_order = $storagePath;
        $order->save();
    }

    public function historiqueSignatures(Request $request): View
    {
        $query = Order::with(['supplier', 'department', 'author'])
            ->whereIn('status', [
                Status::BON_DE_COMMANDE_SIGNE->value,
                Status::BON_DE_COMMANDE_REFUSE->value,
                Status::COMMANDE->value,
                Status::COMMANDE_AVEC_REPONSE->value,
                Status::PARTIELLEMENT_LIVRE->value,
                Status::SERVICE_FAIT->value,
                Status::LIVRE_ET_PAYE->value,
            ]);

        // Filtres GET
        if ($request->filled('department')) {
            $query->where('department_id', $request->input('department'));
        }
        if ($request->filled('statut')) {
            $query->where('status', $request->input('statut'));
        }
        if ($request->filled('periode')) {
            $now = now();
            match ($request->input('periode')) {
                'today' => $query->whereDate('updated_at', $now->toDateString()),
                'week' => $query->where('updated_at', '>=', $now->startOfWeek()),
                'month' => $query->whereMonth('updated_at', $now->month)->whereYear('updated_at', $now->year),
                'year' => $query->whereYear('updated_at', $now->year),
                default => null,
            };
        }

        $orders = $query->orderByDesc('updated_at')->get();

        // Enrichir avec delai de signature
        $orders->each(function ($order) {
            $order->delai_signature = (int) $order->created_at->diffInDays($order->updated_at);
        });

        // 4 KPIs (calculs globaux, pas filtrés)
        $now = now();
        $kpiTotalSignes = Order::where('status', Status::BON_DE_COMMANDE_SIGNE->value)->count()
            + Order::whereIn('status', [
                Status::COMMANDE->value, Status::COMMANDE_AVEC_REPONSE->value,
                Status::PARTIELLEMENT_LIVRE->value, Status::SERVICE_FAIT->value,
                Status::LIVRE_ET_PAYE->value,
            ])->count();
        $kpiSignesCeMois = Order::where('status', Status::BON_DE_COMMANDE_SIGNE->value)
            ->whereMonth('updated_at', $now->month)->whereYear('updated_at', $now->year)->count();
        $kpiMontantTotal = number_format(
            Order::whereIn('status', [
                Status::BON_DE_COMMANDE_SIGNE->value, Status::COMMANDE->value,
                Status::SERVICE_FAIT->value, Status::LIVRE_ET_PAYE->value,
            ])->sum('total_ttc'), 2, ',', ' '
        ) . ' €';
        $kpiDelaiMoyen = round(
            Order::where('status', Status::BON_DE_COMMANDE_SIGNE->value)
                ->whereMonth('updated_at', $now->month)->whereYear('updated_at', $now->year)
                ->get()
                ->avg(fn ($o) => $o->created_at->diffInDays($o->updated_at)),
            1
        );

        $departments = Role::where('is_department', true)->orderBy('name')->get();

        return view('orders.historique-signatures', compact(
            'orders', 'kpiTotalSignes', 'kpiSignesCeMois', 'kpiMontantTotal', 'kpiDelaiMoyen', 'departments'
        ));
    }

    // Directeur - Export historique signatures CSV
    public function exportHistoriqueSignatures(Request $request)
    {
        $query = Order::with(['supplier', 'department', 'author'])
            ->whereIn('status', [
                Status::BON_DE_COMMANDE_SIGNE->value,
                Status::BON_DE_COMMANDE_REFUSE->value,
                Status::COMMANDE->value,
                Status::SERVICE_FAIT->value,
                Status::LIVRE_ET_PAYE->value,
            ]);

        if ($request->filled('department')) {
            $query->where('department_id', $request->input('department'));
        }
        if ($request->filled('statut')) {
            $query->where('status', $request->input('statut'));
        }
        if ($request->filled('periode')) {
            $now = now();
            match ($request->input('periode')) {
                'today' => $query->whereDate('updated_at', $now->toDateString()),
                'week' => $query->where('updated_at', '>=', $now->startOfWeek()),
                'month' => $query->whereMonth('updated_at', $now->month)->whereYear('updated_at', $now->year),
                'year' => $query->whereYear('updated_at', $now->year),
                default => null,
            };
        }

        $orders = $query->orderByDesc('updated_at')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="historique-signatures-' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($orders) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['N° BC', 'Demandeur', 'Departement', 'Fournisseur', 'Montant TTC', 'Date signature', 'Delai (j)', 'Statut actuel'], ';');
            foreach ($orders as $order) {
                $demandeur = $order->author ? $order->author->getFirstName() . ' ' . $order->author->getLastName() : '';
                fputcsv($out, [
                    $order->getOrderNumber(),
                    $demandeur,
                    $order->department?->getName() ?? '',
                    $order->supplier?->getCompanyName() ?? '',
                    number_format($order->total_ttc ?? 0, 2, ',', ' ') . ' €',
                    $order->updated_at->format('d/m/Y'),
                    (int) $order->created_at->diffInDays($order->updated_at),
                    $order->getStatus()->getLabel(),
                ], ';');
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    // Directeur - Modal historique details (lecture seule)
    public function modalHistoriqueSignatureDetails(string $id): View
    {
        $order = Order::with(['supplier', 'department', 'author', 'signedBy', 'articles', 'comments.author', 'logs' => function ($q) {
            $q->with('author')->orderByDesc('created_at');
        }])->findOrFail($id);
        return view('components.historiqueSignatureModal', compact('order'));
    }

    // SF - Modal validation complet
    public function modalValidationSF(string $id): View
    {
        $order = Order::with(['supplier', 'department', 'author', 'articles', 'comments.author'])->findOrFail($id);
        return view('components.modalValidationSF', compact('order'));
    }

    // SF - Action validation/refus
    public function actionValidationSF(Request $request, string $id)
    {
        $order = Order::findOrFail($id);
        $user = Auth::user();

        if (!$user->hasPermission(PermissionValue::GERER_BONS_DE_COMMANDES)) {
            return BaseController::getErrorModal('Permission refusée.');
        }
        if ($order->getStatus() !== Status::DEVIS) {
            return BaseController::getErrorModal('Cette commande ne peut pas être traitée dans son état actuel.');
        }

        $action = $request->input('action');

        if ($action === 'validate') {
            // BC obligatoire
            $request->validate(['purchase_order' => 'required|file|mimes:pdf,doc,docx|max:10240']);

            $order->uploadPurchaseOrder($request, false, false);
            $order->setStatus(Status::BON_DE_COMMANDE_NON_SIGNE, false);
            $order->save();

            $order->logs()->create(['content' => 'Devis validé par le Service Financier. BC généré.', 'type' => 'status_change', 'author_id' => Auth::id()]);
            $order->logs()->create(['content' => 'Bon de commande ajouté.', 'type' => 'document', 'author_id' => Auth::id()]);

            if ($request->filled('comment')) {
                $order->comments()->create(['content' => $request->input('comment'), 'author_id' => Auth::id()]);
            }

            return BaseController::getSuccessModal('La commande N°' . $order->getOrderNumber() . ' a été validée.');

        } elseif ($action === 'refuse') {
            $request->validate(['comment' => 'required|string|min:3']);

            $order->setStatus(Status::DEVIS_REFUSE, false);
            $order->save();

            $order->logs()->create(['content' => 'Devis refusé par le Service Financier.', 'type' => 'status_change', 'author_id' => Auth::id()]);
            $order->comments()->create(['content' => $request->input('comment'), 'author_id' => Auth::id()]);

            return BaseController::getSuccessModal('La commande N°' . $order->getOrderNumber() . ' a été refusée.');
        }

        return redirect()->back();
    }

    // SF - Modal suivi details
    public function modalSuiviDetailsSF(string $id): View
    {
        $order = Order::with(['supplier', 'department', 'author', 'articles', 'comments.author', 'logs.author'])->findOrFail($id);
        return view('components.modalSuiviDetailsSF', compact('order'));
    }

    // CRIT - Modal details commande (vue simplifiee)
    public function modalViewDetailsCrit(string $id): View
    {
        $order = Order::with(['supplier', 'department', 'author'])->findOrFail($id);
        $orderId = $order->getId();
        return view('components.viewOrderModalCrit', compact('order', 'orderId'));
    }

    // SF - Modal envoi BC
    public function modalEnvoiBC(string $id): View
    {
        $order = Order::with(['supplier'])->findOrFail($id);
        return view('components.modalEnvoiBC', compact('order'));
    }

    // SF - Action envoi BC par email
    public function sendBCEmail(Request $request, string $id)
    {
        $order = Order::with(['supplier'])->findOrFail($id);
        $user = Auth::user();

        if (!$user->hasPermission(PermissionValue::GERER_BONS_DE_COMMANDES)) {
            return BaseController::getErrorModal('Permission refusée.');
        }
        if ($order->getStatus() !== Status::BON_DE_COMMANDE_SIGNE) {
            return BaseController::getErrorModal('Ce bon de commande ne peut pas être envoyé dans son état actuel.');
        }

        $supplierEmail = $request->input('supplier_email');
        $emailSubject = $request->input('email_subject', 'Bon de commande signé - ' . $order->getOrderNumber());
        $emailBody = $request->input('email_body', '');

        if (!$supplierEmail) {
            return BaseController::getErrorModal('L\'adresse email du fournisseur est obligatoire.');
        }

        // Envoyer l'email avec le BC signé en PJ
        try {
            \Illuminate\Support\Facades\Mail::to($supplierEmail)
                ->send(new \App\Mail\BonDeCommandeMail($order, $emailSubject, $emailBody));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erreur envoi email BC: ' . $e->getMessage());
            return BaseController::getErrorModal('Erreur lors de l\'envoi de l\'email : ' . $e->getMessage());
        }

        $order->setStatus(Status::COMMANDE, false);
        $order->save();

        $fournisseurNom = $order->supplier?->getCompanyName() ?? $supplierEmail;
        $order->logs()->create([
            'content' => 'BC envoyé par email à ' . $fournisseurNom . ' (' . $supplierEmail . ')',
            'type' => 'status_change',
            'author_id' => Auth::id(),
        ]);

        return BaseController::getSuccessModal('Le bon de commande N°' . $order->getOrderNumber() . ' a été envoyé à ' . $fournisseurNom . '.');
    }

    // SF - Modal paiement
    public function modalPaiement(string $id): View
    {
        $order = Order::with(['supplier'])->findOrFail($id);
        return view('components.modalPaiement', compact('order'));
    }

    // SF - Action paiement (simule)
    public function markPaidSF(Request $request, string $id)
    {
        $order = Order::findOrFail($id);
        $user = Auth::user();

        if (!$user->hasPermission(PermissionValue::GERER_PAIEMENT_FOURNISSEURS)) {
            return BaseController::getErrorModal('Permission refusée.');
        }
        if ($order->getStatus() !== Status::SERVICE_FAIT) {
            return BaseController::getErrorModal('Cette commande ne peut pas être marquée comme payée dans son état actuel.');
        }

        $order->setStatus(Status::LIVRE_ET_PAYE, false);
        $order->save();

        $order->logs()->create(['content' => 'Paiement effectue. Commande cloturee.', 'type' => 'status_change', 'author_id' => Auth::id()]);

        return BaseController::getSuccessModal('La commande N°' . $order->getOrderNumber() . ' a ete marquee comme payee.');
    }

    // SF - Modal relance
    public function modalRelance(string $id): View
    {
        $order = Order::with(['supplier'])->findOrFail($id);
        return view('components.modalRelance', compact('order'));
    }

    // SF - Action relance fournisseur (email reel)
    public function relanceFournisseur(Request $request, string $id)
    {
        $order = Order::with(['supplier'])->findOrFail($id);

        $supplierEmail = $request->input('supplier_email');
        $emailSubject = $request->input('email_subject', 'Relance commande ' . $order->getOrderNumber());
        $message = $request->input('message', '');

        if (!$supplierEmail) {
            return BaseController::getErrorModal('L\'adresse email du fournisseur est obligatoire.');
        }

        try {
            \Illuminate\Support\Facades\Mail::to($supplierEmail)
                ->send(new \App\Mail\RelanceFournisseurMail($order, $emailSubject, $message));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erreur envoi relance: ' . $e->getMessage());
            return BaseController::getErrorModal('Erreur lors de l\'envoi de la relance : ' . $e->getMessage());
        }

        $fournisseurNom = $order->supplier?->getCompanyName() ?? $supplierEmail;
        $order->logs()->create([
            'content' => 'Relance envoyee par email a ' . $fournisseurNom . ' (' . $supplierEmail . ')',
            'type' => 'status_change',
            'author_id' => Auth::id(),
        ]);

        return BaseController::getSuccessModal('La relance a ete envoyee a ' . $fournisseurNom . '.');
    }

    public function downloadDocument(string $id, string $type)
    {
        // @var Order $order
        $order = Order::findOrFail($id);
        $user = Auth::user();

        // 1. VÉRIFICATION DES PERMISSIONS (Sécurité)
        // Adaptez selon vos permissions existantes.
        // Ici, je vérifie juste si l'user peut consulter la commande.

        // Exemple basique basé sur votre logique actuelle :
        $canView = $user->hasPermission(PermissionValue::CONSULTER_TOUTES_COMMANDES);

        if (! $canView) {
            // Vérification si membre du département
            $userDepartments = $user->getRoles()->filter(fn (Role $role) => $role->isDepartment());
            if ($userDepartments->contains($order->getDepartment())) {
                $canView = $user->hasPermission(PermissionValue::CONSULTER_COMMANDES_DEPARTMENT);
            }
        }

        if (! $canView) {
            abort(403, "Vous n'avez pas accès à ce document.");
        }

        // 2. RÉCUPÉRATION DU CHEMIN DU FICHIER
        $path = match ($type) {
            'quote' => $order->getAttribute('path_quote'), // On accède à l'attribut brut en BDD
            'purchase_order' => $order->getAttribute('path_purchase_order'),
            'delivery_note' => $order->getAttribute('path_delivery_note'),
            default => null,
        };

        if (! $path || ! Storage::disk('public')->exists($path)) {
            abort(404, "Le fichier n'existe pas ou a été déplacé.");
        }

        // 3. TÉLÉCHARGEMENT
        // Storage::download(chemin_disque, nom_fichier_pour_l_utilisateur)
        // Note: Comme vos fichiers sont dans storage/app/public, on utilise le disk 'public'
        return Storage::disk('public')->download($path);

        // Si vous préférez afficher le PDF dans le navigateur au lieu de forcer le téléchargement :
        // return Storage::disk('public')->response($path);
    }
}
