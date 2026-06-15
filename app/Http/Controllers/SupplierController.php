<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\PermissionValue;
use Database\Seeders\Status;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SupplierController extends BaseController
{
    public function viewSuppliers(): View|Response|RedirectResponse|Redirector
    {
        $request = request();

        // @var User $user
        $user = Auth::user();
        $userRoles = $user->getRoles();
        $userPermissions = Role::getPermissionsAsDict($userRoles);

        // 1. Initialisation de la Query
        $query = Supplier::query();

        $isFinancier = $user->hasPermission(PermissionValue::GERER_FOURNISSEURS);

        if ($isFinancier) {
            $query->orderBy('is_valid', 'asc');
        } else {
            $query->orderBy('is_valid', 'desc');
        }

        $sqlActivitySort = '
    GREATEST(
        suppliers.updated_at,
        COALESCE(
            (SELECT updated_at FROM orders WHERE supplier_id = suppliers.id ORDER BY updated_at DESC LIMIT 1),
            suppliers.updated_at
        )
    ) DESC
';

        $search = $request->input('search');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'LIKE', "%{$search}%")
                    ->orWhere('contact_name', 'LIKE', "%{$search}%")
                    ->orWhere('siret', 'LIKE', "%{$search}%");
            });
        }

        $query->orderByRaw($sqlActivitySort);

        $suppliers = $query->paginate(10)->withQueryString();

        // 4 KPIs
        $kpiTotal = Supplier::count();
        $kpiActifs = Supplier::where('is_valid', true)->count();
        $now = now();
        $kpiCommandesMois = Order::whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count();
        $delivered = Order::whereIn('status', [Status::SERVICE_FAIT->value, Status::LIVRE_ET_PAYE->value])->get();
        $delais = $delivered->map(fn ($o) => $o->created_at->diffInDays($o->updated_at))->filter();
        $kpiDelaiMoyen = $delais->isNotEmpty() ? round($delais->avg(), 1) . 'j' : '—';

        return view('suppliers', [
            'user' => $user,
            'suppliers' => $suppliers,
            'search' => $search,
            'kpiTotal' => $kpiTotal,
            'kpiActifs' => $kpiActifs,
            'kpiCommandesMois' => $kpiCommandesMois,
            'kpiDelaiMoyen' => $kpiDelaiMoyen,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (!$user->hasPermission(PermissionValue::GERER_FOURNISSEURS) && !$user->hasPermission(PermissionValue::DEMANDER_AJOUT_FOURNISSEUR)) {
            return redirect('/suppliers')->with('error', "Vous n'avez pas la permission d'ajouter un fournisseur.");
        }

        $validated = $request->validate([
            'company_name' => 'required|string|max:255|unique:suppliers,company_name',
            'siret' => 'nullable|string|max:14|unique:suppliers,siret',
            'email' => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:30',
            'contact_name' => 'nullable|string|max:255',
            'speciality' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'iban' => 'nullable|string|max:34',
            'bic' => 'nullable|string|max:11',
            'note' => 'nullable|string',
        ], [
            'company_name.required' => 'Le nom de l\'entreprise est obligatoire.',
            'company_name.unique' => 'Ce fournisseur existe déjà.',
            'siret.unique' => 'Ce SIRET est déjà utilisé.',
            'email.email' => 'L\'adresse email n\'est pas valide.',
        ]);

        $validated['is_valid'] = $user->hasPermission(PermissionValue::GERER_FOURNISSEURS);

        Supplier::create($validated);

        return redirect('/suppliers')->with('success', 'Fournisseur ajouté avec succès.');
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $user = Auth::user();

        if (!$user->hasPermission(PermissionValue::GERER_FOURNISSEURS)) {
            return redirect('/suppliers')->with('error', "Vous n'avez pas la permission de modifier un fournisseur.");
        }

        $supplier = Supplier::findOrFail($id);

        $validated = $request->validate([
            'company_name' => 'required|string|max:255|unique:suppliers,company_name,' . $supplier->id,
            'siret' => 'required|string|size:14|unique:suppliers,siret,' . $supplier->id,
            'email' => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:30',
            'contact_name' => 'nullable|string|max:255',
            'speciality' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'iban' => 'nullable|string|max:34',
            'bic' => 'nullable|string|max:11',
            'note' => 'nullable|string',
            'is_valid' => 'nullable',
        ]);

        $validated['is_valid'] = (bool) ($validated['is_valid'] ?? false);

        $supplier->update($validated);

        return redirect('/suppliers')->with('success', 'Fournisseur mis à jour.');
    }

    public function modalViewDetails(string $id)
    {
        $user = Auth::user();
        $request = request();

        // @var Supplier $supplier
        $supplier = Supplier::where('id', $id)->first();
        $edit = $request['edit'];

        if ($request->method() === 'POST') {
            if ($user->hasPermission(PermissionValue::NOTES_ET_COMMENTAIRES)) {
                $note = $request['note'] ?? '';
                $supplier->setNote($note, false);
                session()->flash('supplierSuccess', 'Note du fournisseur mise à jour !');
            }

            if ($edit && $user->hasPermission(PermissionValue::GERER_FOURNISSEURS)) {
                $companyName = $request['companyName'];
                $email = $request['email'];
                $phoneNumber = $request['phoneNumber'];
                $siret = $request['siret'];
                $isValid = $request['isValid'];

                if (isset($companyName)) {
                    $supplier->setCompanyName($companyName, false);
                }

                $supplier->setEmail($email, false);
                $supplier->setPhoneNumber($phoneNumber, false);

                if (isset($siret)) {
                    $siretLength = strlen($siret);
                    if ($siretLength != 14) {
                        session()->flash('supplierError-'.$supplier->getId(), 'Le siret doit faire exactement 14 chiffres et non '.$siretLength.' chiffres');
                    } else {
                        $supplier->setSiret($siret, false);
                    }
                }

                $supplier->setAddress($request['address'], false);
                $supplier->setIban($request['iban'], false);
                $supplier->setBic($request['bic'], false);

                $supplier->setValidity((bool) $isValid, false);

                session()->flash('supplierSuccess', 'Fournisseur mis à jour !');
            } else {
                $edit = false;
            }

            if ($user->hasPermission(PermissionValue::GERER_FOURNISSEURS) || $user->hasPermission(PermissionValue::NOTES_ET_COMMENTAIRES)) {
                $supplier->save();
            } else {
                session()->flash('supplierError-'.$supplier->getId(), "Vous n'avez pas la permission de modifier la moindre information concernant les fournisseurs.");
            }
        }

        // Stats for this supplier
        $supplierStats = [
            'total_commandes' => Order::where('supplier_id', $id)->count(),
            'en_cours' => Order::where('supplier_id', $id)->whereNotIn('status', [Status::LIVRE_ET_PAYE->value, Status::ANNULE->value])->count(),
            'montant_total' => number_format(Order::where('supplier_id', $id)->sum('total_ttc'), 2, ',', ' ') . ' €',
        ];

        $supplierOrders = Order::where('supplier_id', $id)->with('department')->latest()->limit(5)->get();

        return view('components.viewSupplierModal', [
            'user' => $user,
            'supplier' => $supplier,
            'supplierId' => $supplier->getId(),
            'edit' => $edit,
            'supplierStats' => $supplierStats,
            'supplierOrders' => $supplierOrders,
        ]);
    }

    // Suspendre ou réactiver un fournisseur
    public function toggleValid(string $id): RedirectResponse
    {
        $user = Auth::user();

        if (!$user->hasPermission(PermissionValue::GERER_FOURNISSEURS)) {
            return redirect('/suppliers')->with('error', "Permission refusée.");
        }

        $supplier = Supplier::findOrFail($id);
        $supplier->update(['is_valid' => !$supplier->isValid()]);

        $label = $supplier->isValid() ? 'réactivé' : 'suspendu';
        return redirect('/suppliers')->with('success', "Fournisseur {$label}.");
    }

    // Supprimer un fournisseur
    public function destroy(string $id): RedirectResponse
    {
        $user = Auth::user();

        if (!$user->hasPermission(PermissionValue::GERER_FOURNISSEURS)) {
            return redirect('/suppliers')->with('error', "Permission refusée.");
        }

        $supplier = Supplier::findOrFail($id);

        if ($supplier->orders()->exists()) {
            return redirect('/suppliers')->with('error', "Impossible de supprimer un fournisseur lié à des commandes.");
        }

        $supplier->delete();
        return redirect('/suppliers')->with('success', "Fournisseur supprimé.");
    }
}
