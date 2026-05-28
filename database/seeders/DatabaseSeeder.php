<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Roles
        $roles = collect([
            [
                'name' => 'Administrateur BD',
                'id' => 1,
                'description' => 'Acces total a la base de donnees.',
                'permissions' => [
                    PermissionValue::ADMIN,
                ],
                'is_department' => false,
            ],
            [
                'name' => 'Responsable colis',
                'id' => 2,
                'description' => 'Livraison des colis aux departements.',
                'permissions' => [
                    PermissionValue::CONSULTER_TOUTES_COMMANDES,
                    PermissionValue::NOTES_ET_COMMENTAIRES,
                    PermissionValue::CONSULTER_LISTE_FOURNISSEURS,
                    PermissionValue::GERER_COLIS_LIVRES,
                ],
                'is_department' => false,
            ],
            [
                'name' => 'Service financier',
                'id' => 3,
                'description' => 'Gestion fournisseurs, bons de commandes, paiements.',
                'permissions' => [
                    PermissionValue::CONSULTER_TOUTES_COMMANDES,
                    PermissionValue::NOTES_ET_COMMENTAIRES,
                    PermissionValue::GERER_DEVIS,
                    PermissionValue::GERER_BONS_DE_COMMANDES,
                    PermissionValue::GERER_PAIEMENT_FOURNISSEURS,
                    PermissionValue::GERER_FOURNISSEURS,
                    PermissionValue::CONSULTER_LISTE_FOURNISSEURS,
                    PermissionValue::DEMANDER_AJOUT_FOURNISSEUR,
                    PermissionValue::MODIFIER_TOUTES_COMMANDES,
                ],
                'is_department' => false,
            ],
            [
                'name' => 'Département Info',
                'id' => 4,
                'description' => 'Membre du departement informatique.',
                'permissions' => [
                    PermissionValue::CONSULTER_COMMANDES_DEPARTMENT,
                    PermissionValue::MODIFIER_COMMANDES_DEPARTEMENT,
                    PermissionValue::AJOUTER_BON_DE_LIVRAISON,
                    PermissionValue::DEMANDER_AJOUT_FOURNISSEUR,
                    PermissionValue::NOTES_ET_COMMENTAIRES,
                    PermissionValue::CREER_COMMANDES,
                ],
                'is_department' => true,
            ],
            [
                'name' => 'Département GEA',
                'id' => 5,
                'description' => 'Membre du departement GEA.',
                'permissions' => [
                    PermissionValue::CONSULTER_COMMANDES_DEPARTMENT,
                    PermissionValue::MODIFIER_COMMANDES_DEPARTEMENT,
                    PermissionValue::AJOUTER_BON_DE_LIVRAISON,
                    PermissionValue::DEMANDER_AJOUT_FOURNISSEUR,
                    PermissionValue::NOTES_ET_COMMENTAIRES,
                    PermissionValue::CREER_COMMANDES,
                ],
                'is_department' => true,
            ],
            [
                'name' => 'Département CJ',
                'id' => 6,
                'description' => 'Membre du departement CJ.',
                'permissions' => [
                    PermissionValue::CONSULTER_COMMANDES_DEPARTMENT,
                    PermissionValue::MODIFIER_COMMANDES_DEPARTEMENT,
                    PermissionValue::AJOUTER_BON_DE_LIVRAISON,
                    PermissionValue::DEMANDER_AJOUT_FOURNISSEUR,
                    PermissionValue::NOTES_ET_COMMENTAIRES,
                    PermissionValue::CREER_COMMANDES,
                ],
                'is_department' => true,
            ],
            [
                'name' => 'Département GEII',
                'id' => 7,
                'description' => 'Membre du departement GEII.',
                'permissions' => [
                    PermissionValue::CONSULTER_COMMANDES_DEPARTMENT,
                    PermissionValue::MODIFIER_COMMANDES_DEPARTEMENT,
                    PermissionValue::AJOUTER_BON_DE_LIVRAISON,
                    PermissionValue::DEMANDER_AJOUT_FOURNISSEUR,
                    PermissionValue::NOTES_ET_COMMENTAIRES,
                    PermissionValue::CREER_COMMANDES,
                ],
                'is_department' => true,
            ],
            [
                'name' => 'Département RT',
                'id' => 8,
                'description' => 'Membre du departement reseaux et telecommunications.',
                'permissions' => [
                    PermissionValue::CONSULTER_COMMANDES_DEPARTMENT,
                    PermissionValue::MODIFIER_COMMANDES_DEPARTEMENT,
                    PermissionValue::AJOUTER_BON_DE_LIVRAISON,
                    PermissionValue::DEMANDER_AJOUT_FOURNISSEUR,
                    PermissionValue::NOTES_ET_COMMENTAIRES,
                    PermissionValue::CREER_COMMANDES,
                ],
                'is_department' => true,
            ],
            [
                'name' => 'Département SD',
                'id' => 9,
                'description' => 'Membre du departement sciences des donnees.',
                'permissions' => [
                    PermissionValue::CONSULTER_COMMANDES_DEPARTMENT,
                    PermissionValue::MODIFIER_COMMANDES_DEPARTEMENT,
                    PermissionValue::AJOUTER_BON_DE_LIVRAISON,
                    PermissionValue::DEMANDER_AJOUT_FOURNISSEUR,
                    PermissionValue::NOTES_ET_COMMENTAIRES,
                    PermissionValue::CREER_COMMANDES,
                ],
                'is_department' => true,
            ],
            [
                'name' => 'Directeur IUT',
                'id' => 10,
                'description' => "Directeur de l'IUT, signe les bons de commandes.",
                'permissions' => [
                    PermissionValue::CONSULTER_TOUTES_COMMANDES,
                    PermissionValue::MODIFIER_COMMANDES_DEPARTEMENT,
                    PermissionValue::AJOUTER_BON_DE_LIVRAISON,
                    PermissionValue::DEMANDER_AJOUT_FOURNISSEUR,
                    PermissionValue::NOTES_ET_COMMENTAIRES,
                    PermissionValue::CREER_COMMANDES,
                    PermissionValue::SIGNER_BONS_DE_COMMANDES,
                ],
                'is_department' => false,
            ],
        ]);

        $roles = $roles->sort(fn ($a, $b) => $a['id'] - $b['id']);
        Role::upsert($roles->map(fn ($r) => [
            'name' => $r['name'], 'description' => $r['description'], 'is_department' => $r['is_department'],
        ])->toArray(), uniqueBy: ['id'], update: ['description', 'is_department']);

        // Permissions
        $permissions = PermissionValue::cases();
        sort($permissions);
        $permissionElements = [];
        foreach ($permissions as $permission) {
            $permissionElements[] = ['name' => $permission->name, 'created_at' => now()];
        }
        DB::table('permissions')->upsert($permissionElements, uniqueBy: ['id']);

        // Permission-role pivot
        $pivot = [];
        $i = 1;
        foreach ($roles as $role) {
            foreach ($role['permissions'] as $permission) {
                $pivot[] = ['permission_id' => $permission, 'role_id' => $i];
            }
            $i++;
        }
        if (!empty($pivot)) {
            DB::table('permission_role')->upsert($pivot, uniqueBy: ['permission_id', 'role_id']);
        }

        // 5 comptes de test
        $password = Hash::make('password');

        $amir = User::updateOrCreate(['login' => 'tabellout'], [
            'first_name' => 'Amir',
            'last_name' => 'TABELLOUT',
            'email' => 'amir@test.com',
            'password' => $password,
        ]);
        $amir->roles()->sync([4]); // Departement Info

        $boran = User::updateOrCreate(['login' => 'cav'], [
            'first_name' => 'Boran',
            'last_name' => 'CAV',
            'email' => 'boran@test.com',
            'password' => $password,
        ]);
        $boran->roles()->sync([3]); // Service financier

        $anas = User::updateOrCreate(['login' => 'lacene'], [
            'first_name' => 'Anas',
            'last_name' => 'LACENE-NECER',
            'email' => 'anas@test.com',
            'password' => $password,
        ]);
        $anas->roles()->sync([10]); // Directeur IUT

        $amira = User::updateOrCreate(['login' => 'benhaddi'], [
            'first_name' => 'Amira',
            'last_name' => 'BENHADDI',
            'email' => 'amira@test.com',
            'password' => $password,
        ]);
        $amira->roles()->sync([2]); // Responsable colis (CRIT)

        // Rename old admin → superadmin, or create if fresh DB
        $admin = User::where('login', 'admin')->first();
        if ($admin) {
            $admin->update(['first_name' => 'Super', 'last_name' => 'ADMIN', 'login' => 'superadmin', 'email' => 'superadmin@test.com', 'password' => $password]);
        } else {
            $admin = User::updateOrCreate(['login' => 'superadmin'], [
                'first_name' => 'Super', 'last_name' => 'ADMIN', 'email' => 'superadmin@test.com', 'password' => $password,
            ]);
        }
        $admin->roles()->sync([1]); // Administrateur BD

        // 3 fournisseurs de base (match par siret pour eviter les doublons)
        Supplier::updateOrCreate(['siret' => '48795700012345'], [
            'company_name' => 'Amazon',
            'is_valid' => true,
        ]);
        Supplier::updateOrCreate(['siret' => '40312425700027'], [
            'company_name' => 'LDLC',
            'is_valid' => true,
        ]);
        Supplier::updateOrCreate(['siret' => '35063842100021'], [
            'company_name' => 'Dell',
            'is_valid' => true,
        ]);
    }
}
