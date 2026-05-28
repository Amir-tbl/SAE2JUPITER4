<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Order;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\PermissionValue;
use Database\Seeders\Status;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends BaseController
{
    public function index(): View
    {
        $user = Auth::user();

        // Priorité : SuperAdmin > Service Financier > Directeur > CRIT > Département
        if ($user->hasPermission(PermissionValue::ADMIN)) {
            return $this->superadmin($user);
        }
        if ($user->hasPermission(PermissionValue::GERER_PAIEMENT_FOURNISSEURS)) {
            return $this->serviceFinancier($user);
        }
        if ($user->hasPermission(PermissionValue::SIGNER_BONS_DE_COMMANDES)) {
            return $this->directeur($user);
        }
        if ($user->hasPermission(PermissionValue::GERER_COLIS_LIVRES)) {
            return $this->crit($user);
        }

        return $this->agent($user);
    }

    private function agent(User $user): View
    {
        $departments = $user->getDepartments();
        $deptIds = $departments->pluck('id');

        $activeStatuses = [
            Status::BROUILLON->value, Status::DEVIS->value,
            Status::BON_DE_COMMANDE_NON_SIGNE->value, Status::BON_DE_COMMANDE_SIGNE->value,
            Status::COMMANDE->value, Status::COMMANDE_AVEC_REPONSE->value,
            Status::PARTIELLEMENT_LIVRE->value, Status::SERVICE_FAIT->value,
        ];

        $kpiEnCours = Order::whereIn('department_id', $deptIds)->whereIn('status', $activeStatuses)->count();

        $kpiLivrees = Order::whereIn('department_id', $deptIds)
            ->whereIn('status', [Status::SERVICE_FAIT->value, Status::LIVRE_ET_PAYE->value])
            ->count();

        $kpiEnRetard = Order::whereIn('department_id', $deptIds)
            ->whereIn('status', $activeStatuses)
            ->where('updated_at', '<', now()->subDays(14))
            ->count();

        $kpiAttenteValidation = Order::whereIn('department_id', $deptIds)
            ->where('status', Status::DEVIS->value)
            ->count();

        $kpiCeMois = Order::whereIn('department_id', $deptIds)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $kpiMontantTotal = number_format(
            Order::whereIn('department_id', $deptIds)->whereIn('status', $activeStatuses)->sum('total_ttc'),
            2, ',', ' '
        ) . ' €';

        // Recent logs (5)
        $recentLogs = Log::with('author')
            ->whereHas('order', function ($q) use ($deptIds) {
                $q->whereIn('department_id', $deptIds);
            })
            ->latest()
            ->limit(5)
            ->get();

        // Recent orders (10)
        $recentOrders = Order::with('supplier', 'department')
            ->whereIn('department_id', $deptIds)
            ->latest()
            ->limit(10)
            ->get();

        return view('dashboard.agent', compact(
            'user', 'kpiEnCours', 'kpiLivrees', 'kpiEnRetard',
            'kpiAttenteValidation', 'kpiCeMois', 'kpiMontantTotal',
            'recentLogs', 'recentOrders'
        ));
    }

    private function serviceFinancier(User $user): View
    {
        $now = now();

        // 6 KPIs
        $kpiAValider = Order::where('status', Status::DEVIS->value)->count();
        $kpiAttenteSignature = Order::where('status', Status::BON_DE_COMMANDE_NON_SIGNE->value)->count();
        $kpiBcEnvoyes = Order::where('status', Status::COMMANDE->value)->count();
        $kpiLivreesMois = Order::whereIn('status', [Status::SERVICE_FAIT->value, Status::LIVRE_ET_PAYE->value])
            ->whereMonth('updated_at', $now->month)->whereYear('updated_at', $now->year)->count();
        $kpiRetards = Order::where('status', Status::COMMANDE->value)
            ->where('created_at', '<', $now->copy()->subDays(14))->count();
        $kpiAPayer = Order::where('status', Status::SERVICE_FAIT->value)->count();

        // Urgentes: DEVIS > 5 jours
        $urgentes = Order::with(['supplier', 'department', 'author'])
            ->where('status', Status::DEVIS->value)
            ->where('created_at', '<', $now->copy()->subDays(5))
            ->orderBy('created_at', 'asc')
            ->limit(10)
            ->get();

        // Commandes à valider (DEVIS)
        $ordersAValider = Order::with(['supplier', 'department', 'author'])
            ->where('status', Status::DEVIS->value)
            ->orderBy('created_at', 'asc')
            ->limit(10)
            ->get();

        // Stats mensuelles — compte les commandes ayant depasse le stade DEVIS ce mois
        $statutsAvances = [
            Status::BON_DE_COMMANDE_NON_SIGNE->value,
            Status::BON_DE_COMMANDE_SIGNE->value,
            Status::COMMANDE->value,
            Status::COMMANDE_AVEC_REPONSE->value,
            Status::PARTIELLEMENT_LIVRE->value,
            Status::SERVICE_FAIT->value,
            Status::LIVRE_ET_PAYE->value,
        ];
        $statsValidesMois = Order::whereIn('status', $statutsAvances)
            ->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count();
        $statsMontantMois = number_format(
            Order::whereIn('status', $statutsAvances)
                ->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)
                ->sum('total_ttc'), 2, ',', ' '
        ) . ' €';
        // Delai moyen (creation → derniere mise a jour)
        $validees = Order::whereIn('status', $statutsAvances)
            ->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)
            ->get();
        $delais = $validees->map(fn ($o) => $o->created_at->diffInDays($o->updated_at))->filter();
        $statsDelaiMoyen = $delais->isNotEmpty() ? round($delais->avg(), 1) . 'j' : '—';

        // Taux de validation
        $refusees = Order::whereIn('status', [Status::DEVIS_REFUSE->value, Status::BON_DE_COMMANDE_REFUSE->value, Status::COMMANDE_REFUSEE->value])
            ->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count();
        $totalTraites = $statsValidesMois + $refusees;
        $statsTauxValidation = $totalTraites > 0 ? round(($statsValidesMois / $totalTraites) * 100) . '%' : '—';

        return view('dashboard.service-financier', compact(
            'user', 'kpiAValider', 'kpiAttenteSignature', 'kpiBcEnvoyes',
            'kpiLivreesMois', 'kpiRetards', 'kpiAPayer',
            'urgentes', 'ordersAValider',
            'statsValidesMois', 'statsMontantMois', 'statsDelaiMoyen', 'statsTauxValidation'
        ));
    }

    private function directeur(User $user): View
    {
        $now = now();

        // 6 KPIs
        $kpiEnAttente = Order::where('status', Status::BON_DE_COMMANDE_NON_SIGNE->value)->count();
        $kpiSignesAujourdhui = Order::where('status', Status::BON_DE_COMMANDE_SIGNE->value)
            ->whereDate('updated_at', today())->count();
        $kpiSignesMois = Order::where('status', Status::BON_DE_COMMANDE_SIGNE->value)
            ->whereMonth('updated_at', $now->month)->whereYear('updated_at', $now->year)->count();
        $kpiMontantMois = number_format(
            Order::where('status', Status::BON_DE_COMMANDE_SIGNE->value)
                ->whereMonth('updated_at', $now->month)->whereYear('updated_at', $now->year)
                ->sum('total_ttc'), 2, ',', ' '
        ) . ' €';
        $kpiUrgents = Order::where('status', Status::BON_DE_COMMANDE_NON_SIGNE->value)
            ->where('created_at', '<', $now->copy()->subDays(7))->count();
        $kpiTotalTraites = Order::whereIn('status', [
            Status::BON_DE_COMMANDE_SIGNE->value,
            Status::BON_DE_COMMANDE_REFUSE->value,
        ])->count();

        // 5 BC en attente (les plus anciens d'abord)
        $bcEnAttente = Order::with(['supplier', 'department', 'author'])
            ->where('status', Status::BON_DE_COMMANDE_NON_SIGNE->value)
            ->orderBy('created_at', 'asc')
            ->limit(5)
            ->get();

        // Stats mensuelles
        $statsSignesMois = $kpiSignesMois;
        $statsMontantSigne = $kpiMontantMois;
        $statsDelaiMoyen = round(
            Order::where('status', Status::BON_DE_COMMANDE_SIGNE->value)
                ->whereMonth('updated_at', $now->month)->whereYear('updated_at', $now->year)
                ->get()
                ->avg(fn ($o) => $o->created_at->diffInDays($o->updated_at)),
            1
        );
        // Departement le plus actif (BC signes ce mois)
        $topDeptRow = Order::where('status', Status::BON_DE_COMMANDE_SIGNE->value)
            ->whereMonth('updated_at', $now->month)->whereYear('updated_at', $now->year)
            ->select('department_id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('department_id')
            ->orderByDesc('cnt')
            ->with('department')
            ->first();
        $topDepartment = $topDeptRow?->department?->getName() ?? '—';

        return view('dashboard.directeur', compact(
            'user', 'kpiEnAttente', 'kpiSignesAujourdhui', 'kpiSignesMois',
            'kpiMontantMois', 'kpiUrgents', 'kpiTotalTraites',
            'bcEnAttente',
            'statsSignesMois', 'statsMontantSigne', 'statsDelaiMoyen', 'topDepartment'
        ));
    }

    private function crit(User $user): View
    {
        $now = now();

        // 6 KPIs
        $kpiEnAttente = Order::whereIn('status', [Status::COMMANDE->value, Status::COMMANDE_AVEC_REPONSE->value])->count();

        $kpiRecusAujourdhui = Log::where('type', 'delivery')
            ->where('content', 'like', '%réceptionné%')
            ->whereDate('created_at', today())->count();

        $kpiADistribuer = Order::where('status', Status::PARTIELLEMENT_LIVRE->value)->count();

        $kpiDistribuesAujourdhui = Log::where('type', 'delivery')
            ->where('content', 'like', '%distribué%')
            ->whereDate('created_at', today())->count();

        $kpiAnomalies = Log::where('type', 'delivery')
            ->where('content', 'like', '%anomalie%')->count();

        $kpiTotalMois = Order::whereIn('status', [Status::SERVICE_FAIT->value, Status::LIVRE_ET_PAYE->value])
            ->whereMonth('updated_at', $now->month)->whereYear('updated_at', $now->year)->count();

        // Tables (5 max)
        $enAttente = Order::with(['supplier', 'department', 'author'])
            ->whereIn('status', [Status::COMMANDE->value, Status::COMMANDE_AVEC_REPONSE->value])
            ->orderByDesc('created_at')
            ->limit(5)->get();

        $aDistribuer = Order::with(['supplier', 'department', 'author', 'logs'])
            ->where('status', Status::PARTIELLEMENT_LIVRE->value)
            ->orderByDesc('updated_at')
            ->limit(5)->get();

        // Stats mensuelles
        $statsReceptionnesMois = Log::where('type', 'delivery')
            ->where('content', 'like', '%réceptionné%')
            ->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count();

        $statsDistribuesMois = Log::where('type', 'delivery')
            ->where('content', 'like', '%distribué%')
            ->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count();

        // Délai moyen distribution (jours entre réception et distribution ce mois)
        $deliveredThisMonth = Order::with('logs')
            ->whereIn('status', [Status::SERVICE_FAIT->value, Status::LIVRE_ET_PAYE->value])
            ->whereMonth('updated_at', $now->month)->whereYear('updated_at', $now->year)
            ->get();
        $delais = $deliveredThisMonth->map(function ($o) {
            $rec = $o->logs->where('type', 'delivery')->filter(fn($l) => str_contains($l->content, 'réceptionné'))->last();
            $dist = $o->logs->where('type', 'delivery')->filter(fn($l) => str_contains($l->content, 'distribué'))->last();
            return ($rec && $dist) ? (int) $rec->created_at->diffInDays($dist->created_at) : null;
        })->filter();
        $statsDelaiMoyen = $delais->isNotEmpty() ? round($delais->avg(), 1) : 0;

        // Département le plus actif
        $topDeptRow = Order::whereIn('status', [Status::SERVICE_FAIT->value, Status::LIVRE_ET_PAYE->value])
            ->whereMonth('updated_at', $now->month)->whereYear('updated_at', $now->year)
            ->select('department_id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('department_id')
            ->orderByDesc('cnt')
            ->with('department')
            ->first();
        $topDepartment = $topDeptRow?->department?->getName() ?? '—';

        return view('dashboard.crit', compact(
            'user', 'kpiEnAttente', 'kpiRecusAujourdhui', 'kpiADistribuer',
            'kpiDistribuesAujourdhui', 'kpiAnomalies', 'kpiTotalMois',
            'enAttente', 'aDistribuer',
            'statsReceptionnesMois', 'statsDistribuesMois', 'statsDelaiMoyen', 'topDepartment'
        ));
    }

    private function superadmin(User $user): View
    {
        $now = now();
        $activeStatuses = [
            Status::BROUILLON->value, Status::DEVIS->value,
            Status::BON_DE_COMMANDE_NON_SIGNE->value, Status::BON_DE_COMMANDE_SIGNE->value,
            Status::COMMANDE->value, Status::COMMANDE_AVEC_REPONSE->value,
            Status::PARTIELLEMENT_LIVRE->value, Status::SERVICE_FAIT->value,
        ];

        $kpis = [
            ['value' => Order::count(), 'label' => 'Total commandes', 'icon' => 'list-task'],
            ['value' => User::has('roles')->count(), 'label' => 'Utilisateurs actifs', 'icon' => 'people'],
            ['value' => number_format(Order::sum('total_ttc'), 2, ',', ' ') . ' €', 'label' => 'Montant total', 'icon' => 'bar-chart'],
            ['value' => Order::whereIn('status', $activeStatuses)->count(), 'label' => 'Commandes en cours', 'icon' => 'clock-history'],
            ['value' => Supplier::where('is_valid', true)->count(), 'label' => 'Fournisseurs actifs', 'icon' => 'building'],
            ['value' => Order::where('updated_at', '<', $now->copy()->subDays(14))->whereIn('status', $activeStatuses)->count(), 'label' => 'Alertes', 'icon' => 'bell'],
        ];

        // Stats par role
        $roleStats = [
            ['label' => 'AGENTS', 'gradient' => 'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)',
             'users' => User::whereHas('roles', fn($q) => $q->where('is_department', true))->count(),
             'detail' => Order::whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count() . ' commandes ce mois'],
            ['label' => 'SERVICE FINANCIER', 'gradient' => 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
             'users' => User::whereHas('roles', fn($q) => $q->whereHas('permissions', fn($p) => $p->where('id', PermissionValue::GERER_PAIEMENT_FOURNISSEURS->value)))->count(),
             'detail' => Order::whereIn('status', [Status::BON_DE_COMMANDE_NON_SIGNE->value, Status::BON_DE_COMMANDE_SIGNE->value, Status::COMMANDE->value, Status::COMMANDE_AVEC_REPONSE->value, Status::PARTIELLEMENT_LIVRE->value, Status::SERVICE_FAIT->value, Status::LIVRE_ET_PAYE->value])->count() . ' validations'],
            ['label' => 'DIRECTEUR', 'gradient' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
             'users' => User::whereHas('roles', fn($q) => $q->whereHas('permissions', fn($p) => $p->where('id', PermissionValue::SIGNER_BONS_DE_COMMANDES->value)))->count(),
             'detail' => Order::whereIn('status', [Status::BON_DE_COMMANDE_SIGNE->value, Status::COMMANDE->value, Status::COMMANDE_AVEC_REPONSE->value, Status::PARTIELLEMENT_LIVRE->value, Status::SERVICE_FAIT->value, Status::LIVRE_ET_PAYE->value])->count() . ' signatures'],
            ['label' => 'SERVICE POSTAL', 'gradient' => 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)',
             'users' => User::whereHas('roles', fn($q) => $q->whereHas('permissions', fn($p) => $p->where('id', PermissionValue::GERER_COLIS_LIVRES->value)))->count(),
             'detail' => Order::whereIn('status', [Status::SERVICE_FAIT->value, Status::LIVRE_ET_PAYE->value])->count() . ' distributions'],
        ];

        // Stats par departement
        $terminalStatuses = [Status::LIVRE_ET_PAYE->value, Status::ANNULE->value];
        $departments = Role::where('is_department', true)
            ->withCount(['orders', 'users'])
            ->withSum('orders', 'total_ttc')
            ->get()
            ->map(function ($dept) use ($terminalStatuses) {
                return [
                    'name' => $dept->getName(),
                    'users' => $dept->users_count,
                    'total' => $dept->orders_count,
                    'montant' => number_format($dept->orders_sum_total_ttc ?? 0, 2, ',', ' ') . ' €',
                    'en_cours' => Order::where('department_id', $dept->id)->whereNotIn('status', $terminalStatuses)->count(),
                ];
            });

        // 10 dernieres commandes
        $recentOrders = Order::with('supplier', 'department', 'author')
            ->latest()
            ->limit(10)
            ->get();

        // Stats systeme du mois
        $creeesMois = Order::whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count();
        $valideesMois = Order::whereIn('status', [Status::BON_DE_COMMANDE_NON_SIGNE->value, Status::BON_DE_COMMANDE_SIGNE->value, Status::COMMANDE->value, Status::COMMANDE_AVEC_REPONSE->value, Status::PARTIELLEMENT_LIVRE->value, Status::SERVICE_FAIT->value, Status::LIVRE_ET_PAYE->value])
            ->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count();
        $refuseesMois = Order::whereIn('status', [Status::DEVIS_REFUSE->value, Status::BON_DE_COMMANDE_REFUSE->value, Status::COMMANDE_REFUSEE->value])
            ->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count();
        $totalTraitesMois = $valideesMois + $refuseesMois;
        $tauxValidation = $totalTraitesMois > 0 ? round(($valideesMois / $totalTraitesMois) * 100) . '%' : '—';
        $ordersForDelay = Order::whereIn('status', [Status::BON_DE_COMMANDE_NON_SIGNE->value, Status::BON_DE_COMMANDE_SIGNE->value, Status::COMMANDE->value, Status::COMMANDE_AVEC_REPONSE->value, Status::PARTIELLEMENT_LIVRE->value, Status::SERVICE_FAIT->value, Status::LIVRE_ET_PAYE->value])
            ->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->get();
        $delais = $ordersForDelay->map(fn ($o) => $o->created_at->diffInDays($o->updated_at))->filter();
        $delaiMoyen = $delais->isNotEmpty() ? round($delais->avg(), 1) . ' j' : '—';

        $systemStats = [
            ['label' => 'COMMANDES CREEES', 'value' => $creeesMois],
            ['label' => 'COMMANDES VALIDEES', 'value' => $valideesMois],
            ['label' => 'TAUX DE VALIDATION', 'value' => $tauxValidation],
            ['label' => 'DELAI MOYEN TRAITEMENT', 'value' => $delaiMoyen],
        ];

        // 5 derniers logs
        $recentLogs = Log::with('author')
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard.superadmin', compact(
            'user', 'kpis', 'roleStats', 'departments', 'recentOrders',
            'systemStats', 'recentLogs'
        ));
    }
}
