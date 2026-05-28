<?php

namespace Database\Seeders;

// Permissions
// - CONSULTER_TOUTES_COMMANDES          => Pouvoir voir et rechercher toutes les commandes dans le système.
// - CONSULTER_COMMANDES_DEPARTMENT      => Pouvoir voir uniquement les commandes appartenant au même département/service.
// - Créer des commandes                 => Pouvoir créer des commandes à l'état de devis.
// - Modifier commandes département      => Pouvoir modifier les informations principales d'une commande appartenant au même département.
// - Ajouter un bon de livraison         => Pouvoir marquer les colis respectifs comme livrés et ajouter un bon de livraison.
// - Notes et commentaires               => Pouvoir ajouter des commentaires et modifier la note pour les commandes et les fournisseurs.
// - Demander l'ajout d'un fournisseur   => Pouvoir demander l'ajout d'un fournisseur au service financier.
// - Consulter la liste des fournisseurs => Pouvoir consulter la liste des fournisseurs valides.
// - Gérer les fournisseurs              => Pouvoir ajouter, modifier et valider ou invalider les fournisseurs.
// - Gérer les bons de commande          => Pouvoir ajouter, refuser, modifier et supprimer des bons de commande.
// - Payer les fournisseurs              => Pouvoir marquer les commandes comme payées.
// - MODIFIER_COMMANDES_DEPARTEMENT      => Pouvoir modifier toutes les commandes auxquels l'utilisateur a accès
// - Admin                               => Avoir tous les accès et pouvoir gérer la base de données.

//  /---------------------------------------------------------------------------------------------------------------------\
// | ATTENTION : Si vous souhaitez ajouter ou modifier des permissions, vous devrez exécuter à nouveau le seeder           |
// | avec la commande "php artisan db:seed" (ATTENTION, en local cette commande va générer à nouveau les données de test)  |
//  \---------------------------------------------------------------------------------------------------------------------/
enum PermissionValue: int
{
    case CONSULTER_TOUTES_COMMANDES = 2;
    case CONSULTER_COMMANDES_DEPARTMENT = 3;
    case CREER_COMMANDES = 4;
    case MODIFIER_COMMANDES_DEPARTEMENT = 5;
    case AJOUTER_BON_DE_LIVRAISON = 6;
    case NOTES_ET_COMMENTAIRES = 7;
    case DEMANDER_AJOUT_FOURNISSEUR = 8;
    case CONSULTER_LISTE_FOURNISSEURS = 9;
    case GERER_FOURNISSEURS = 10;
    case GERER_BONS_DE_COMMANDES = 11;
    case GERER_PAIEMENT_FOURNISSEURS = 12;
    case GERER_DEVIS = 13;
    case SIGNER_BONS_DE_COMMANDES = 14;
    case GERER_COLIS_LIVRES = 15;
    case MODIFIER_TOUTES_COMMANDES = 16;
    case ADMIN = 1;

    public static function getDict(): array
    {
        $permissions = [];
        foreach (PermissionValue::cases() as $permission) {
            $permissions[$permission->value] = false;
        }

        return $permissions;
    }
}
