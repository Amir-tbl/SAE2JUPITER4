<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\Status;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StatsController extends BaseController
{
    public function index(): View
    {
        $now = now();

        // Par statut
        $byStatus = Order::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')->get()
            ->mapWithKeys(fn($row) => [$row->status => $row->count]);

        // Par departement
        $byDepartment = Role::where('is_department', true)
            ->withCount('orders')
            ->withSum('orders', 'total_ttc')
            ->get();

        // Evolution mensuelle (12 derniers mois)
        $monthly = Order::select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"), DB::raw('COUNT(*) as count'), DB::raw('SUM(total_ttc) as montant'))
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        // Top fournisseurs
        $topSuppliers = Supplier::withCount('orders')
            ->withSum('orders', 'total_ttc')
            ->orderByDesc('orders_count')
            ->limit(10)
            ->get();

        // KPIs globaux
        $totalOrders = Order::count();
        $totalMontant = Order::sum('total_ttc');
        $totalUsers = User::count();
        $totalSuppliers = Supplier::where('is_valid', true)->count();

        // Delai moyen global (creation → derniere mise a jour, pour commandes terminees)
        $terminated = Order::whereIn('status', [Status::SERVICE_FAIT->value, Status::LIVRE_ET_PAYE->value])->get();
        $delais = $terminated->map(fn($o) => $o->created_at->diffInDays($o->updated_at))->filter();
        $delaiMoyen = $delais->isNotEmpty() ? round($delais->avg(), 1) . ' j' : '—';

        // Taux de validation global
        $refusees = Order::whereIn('status', [Status::DEVIS_REFUSE->value, Status::BON_DE_COMMANDE_REFUSE->value, Status::COMMANDE_REFUSEE->value])->count();
        $validees = Order::whereNotIn('status', [Status::BROUILLON->value, Status::DEVIS->value, Status::DEVIS_REFUSE->value, Status::BON_DE_COMMANDE_REFUSE->value, Status::COMMANDE_REFUSEE->value, Status::ANNULE->value])->count();
        $totalTraites = $validees + $refusees;
        $tauxValidation = $totalTraites > 0 ? round(($validees / $totalTraites) * 100) . '%' : '—';

        return view('stats.index', compact(
            'byStatus', 'byDepartment', 'monthly', 'topSuppliers',
            'totalOrders', 'totalMontant', 'totalUsers', 'totalSuppliers',
            'delaiMoyen', 'tauxValidation'
        ));
    }
}
