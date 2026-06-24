<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Log;
use App\Models\Order;
use App\Models\Package;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use Exception;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class LocalTestSeeder extends Seeder
{
    use WithoutModelEvents;

    // Remplit la base avec des données de test
    public function run(): void
    {
        // S assurer que les roles, permissions et comptes de base existent
        $this->call(DatabaseSeeder::class);

        $nbSuppliers = 10;
        $nbUsers = 20;
        $nbOrders = 100;

        // Récupérer les rôles par défaut (crées par DatabaseSeeder ci-dessus)
        $roles = Role::all();

        // Génération de fournisseurs aléatoires
        $suppliers = Supplier::factory($nbSuppliers)->create();

        // Génération d'utilisateurs aléatoires
        $users = User::factory($nbUsers)->create([
            'password' => bcrypt('password'),
        ]);
        $users->each(function (User $user) use ($roles) {
            // Chaque utilisateur doit avoir au moins un rôle aléatoire
            $user_roles = [
                $roles->random(),
            ];

            // et dans 10% des cas un utilisateur a UN AUTRE, aussi aléatoire
            $role2 = $roles->random();
            $hasSecondRole = rand(0, 100) <= 10 && $user_roles[0] !== $role2;
            if ($hasSecondRole) {
                $user_roles[] = $role2;
            }

            // Sauvegarder les associations rôles-utilisateur en base de données pour l'utilisateur actuel (dans la table association)
            $user->roles()->sync($user_roles);
        });

        $departments = $roles->filter(fn (Role $role) => $role->isDepartment());

        $users_can_access_all_orders = $users->filter(
            fn (User $user) => $user->hasPermission(PermissionValue::CONSULTER_TOUTES_COMMANDES)
        );

        // Génération de commandes aléatoires
        $orders = Order::factory($nbOrders)
            ->make()
            ->each(function (Order $order) use ($departments, $suppliers) {
                // Pour chaque commande associer un fournisseur
                $department = $departments->random();

                // Le département ne doit pas avoir aucun membre
                if ($department->users()->count() == 0) {
                    foreach ($departments as $other_department) {
                        if (! $other_department->users()->count() == 0) {
                            $department = $other_department;
                            break;
                        }
                    }
                }
                if ($department->users()->count() == 0) {
                    throw new Exception("Une erreur est survenue lors de la création de données de tests (orders/commandes) : Aucun département ne possède de membre. Il n'est donc pas possible de créer des données");
                }

                $author = $department->getUsers()->random();

                $order->department()->associate($department);
                $order->author()->associate($author);
                $order->supplier()->associate(($suppliers->random()));

                $order->save();
            })
            ->each(function (Order $order) use ($users_can_access_all_orders) {
                Package::factory(rand(1, 5))
                    ->make()
                    ->each(function (Package $package) use ($order) {
                        // TODO on peut rendre cohérente la date de livraison en accord avec le statut du colis
                        // TODO on peut rendre cohérent le délai de livraison en accord avec le statut
                        $package->order()->associate($order);
                        $package->save();
                    });

                $department = $order->getDepartment();
                $users_same_department = $department->getUsers();

                // Les utilisateurs qui peuvent accéder à toutes les commandes font partis des utilisateurs qui ont accès à la commande (mais pas seulement, voir la suite)
                $users_can_access = new Collection([...$users_can_access_all_orders]);

                // Si les utilisateurs du département ont la permission de voir les commandes du département
                // les utilisateurs qui sont du même départment font aussi partis des utilisateurs qui ont accès à la commande
                if ($department->hasPermission(PermissionValue::CONSULTER_COMMANDES_DEPARTMENT)) {
                    $users_same_department->each(function (User $user) use ($users_can_access) {

                        // Pour chaque utilisateur du même département vérifier s'il est déjà la liste des utilisateurs qui ont accès à toutes les commandes (éviter le doublons)
                        // (vérifier si l'utilisateur a la permission de consulter toutes les commandes à place de vérifier s'il est dans la liste est moins long du au probable faible nombre de rôles et à la possibilité d'un grand nombre d'utilisateurs)
                        if (! $user->hasPermission(PermissionValue::CONSULTER_TOUTES_COMMANDES)) {

                            // Ajouter l'utilisateur à la liste
                            $users_can_access->push($user);
                        }
                    });
                }

                // Pour chaque commande créer entre 1 et 5 log (action)
                Log::factory(rand(1, 5))
                    ->make()
                    ->each(function (Log $log, int $index) use ($order, $users_can_access) {
                        $log->order()->associate($order);

                        // S'il s'agit du premier log, l'auteur doit être l'auteur de la commande
                        // Sinon, il peut être du même département ou avoir un rôle avec la permission de voir toutes les commandes
                        if ($index === 0) {
                            $log->author()->associate($order->getAuthor());
                        } else {
                            $log->author()->associate($users_can_access->random());
                        }

                        // Sauvegarder le log en base de données
                        $log->save();
                    });

                // Pour chaque commande, faire écrire des commentaires à tous les utilisateurs qui ont accès à la commande
                $users_can_access->each(function (User $user) use ($order) {
                    Comment::factory(rand(0, 3))
                        ->make()
                        ->each(function (Comment $comment) use ($order, $user) {
                            $comment->order()->associate($order);
                            $comment->author()->associate($user);
                            $comment->save();
                        });
                });
            });
    }
}
