<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Database\Seeders\PermissionValue;
use Database\Seeders\Status;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ProfileController extends BaseController
{
    public function show(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();
        $deptIds = $user->getDepartments()->pluck('id');
        $isDirecteur = $user->hasPermission(PermissionValue::SIGNER_BONS_DE_COMMANDES);

        $activeStatuses = [
            Status::BROUILLON->value, Status::DEVIS->value,
            Status::BON_DE_COMMANDE_NON_SIGNE->value, Status::BON_DE_COMMANDE_SIGNE->value,
            Status::COMMANDE->value, Status::COMMANDE_AVEC_REPONSE->value,
            Status::PARTIELLEMENT_LIVRE->value, Status::SERVICE_FAIT->value,
        ];

        if ($isDirecteur) {
            // Stats specifiques directeur
            $now = now();
            $stats = [
                'bc_signes' => Order::where('status', Status::BON_DE_COMMANDE_SIGNE->value)->count()
                    + Order::whereIn('status', [
                        Status::COMMANDE->value, Status::SERVICE_FAIT->value, Status::LIVRE_ET_PAYE->value,
                    ])->count(),
                'ce_mois' => Order::where('status', Status::BON_DE_COMMANDE_SIGNE->value)
                    ->whereMonth('updated_at', $now->month)->whereYear('updated_at', $now->year)->count(),
                'delai_moyen' => round(
                    Order::where('status', Status::BON_DE_COMMANDE_SIGNE->value)
                        ->whereMonth('updated_at', $now->month)->whereYear('updated_at', $now->year)
                        ->get()
                        ->avg(fn ($o) => $o->created_at->diffInDays($o->updated_at)),
                    1
                ) . 'j',
                'montant_total' => number_format(
                    Order::whereIn('status', [
                        Status::BON_DE_COMMANDE_SIGNE->value, Status::COMMANDE->value,
                        Status::SERVICE_FAIT->value, Status::LIVRE_ET_PAYE->value,
                    ])->sum('total_ttc'), 2, ',', ' '
                ) . ' €',
            ];
        } else {
            $stats = [
                'total' => Order::whereIn('department_id', $deptIds)->count(),
                'en_cours' => Order::whereIn('department_id', $deptIds)->whereIn('status', $activeStatuses)->count(),
                'ce_mois' => Order::whereIn('department_id', $deptIds)
                    ->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
                'montant_total' => number_format(
                    Order::whereIn('department_id', $deptIds)->whereIn('status', $activeStatuses)->sum('total_ttc'),
                    2, ',', ' '
                ) . ' €',
            ];
        }

        return view('profile', [
            'user' => $user,
            'roles' => $user->getRoles(),
            'departments' => $user->getDepartments(),
            'stats' => $stats,
            'isDirecteur' => $isDirecteur,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $mailSent = false;
        $validated = $request->validate([
            'email' => ['nullable', 'email', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:30'],
        ]);

        // On garde l'ancien état pour comparer
        $before = [
            'email' => $user->email,
            'phone_number' => $user->phone_number,
        ];

        $user->fill($validated);

        // Rien n'a changé => pas de save, pas de mail
        if (! $user->isDirty()) {
            return redirect()
                ->route('profile.show')
                ->with('success', 'Aucune modification détectée.');
        }

        $user->save();

        // Envoi d'un mail uniquement si on a une adresse email
        if (!empty($user->email)) {
            $changes = [];
            foreach (['email', 'phone_number'] as $field) {
                if (($before[$field] ?? null) !== ($user->$field ?? null)) {
                    $changes[$field] = ['before' => $before[$field] ?? '', 'after' => $user->$field ?? ''];
                }
            }

            // Mail simple (sans Mailable pour rester léger)
            Mail::raw($this->formatChangesMail($user, $changes), function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Modification de votre profil - Suivi des colis IUTV');
            });

            $mailSent = true;
        }

        return redirect()
            ->route('profile.show')
            ->with('success', 'Profil mis à jour.')
            ->with('mailSent', $mailSent);
    }

    private function formatChangesMail(User $user, array $changes): string
    {
        $lines = [];
        $lines[] = "Bonjour {$user->getFullName()},";
        $lines[] = "";
        $lines[] = "Une modification a été effectuée sur votre profil (Suivi des colis IUTV).";
        $lines[] = "";
        $lines[] = "Changements :";
        foreach ($changes as $field => $diff) {
            $label = match ($field) {
                'email' => 'Email',
                'phone_number' => 'Téléphone',
                default => $field,
            };
            $lines[] = "- {$label} : \"{$diff['before']}\" → \"{$diff['after']}\"";
        }
        $lines[] = "";
        $lines[] = "Si vous n’êtes pas à l’origine de cette modification, contactez un responsable.";
        $lines[] = "";
        $lines[] = "Cordialement,";
        $lines[] = "Suivi des colis IUTV";

        return implode("\n", $lines);
    }
}
