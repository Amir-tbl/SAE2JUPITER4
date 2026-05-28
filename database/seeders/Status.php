<?php

namespace Database\Seeders;

// /---------------------------------------------------------------------------------------------------------------\
// | ATTENTION ! si vous souhaitez ajouter ou modifiez des status :                                                 |
// | Dû à la colonne "status" de type ENUM de la table "orders", ajouter un nouveau status nécessite de changer le  |
// | type de la colonne "status" par les migrations.                                                                |
// | Pour cela il vous faudra créer une nouvelle migration de la table orders visant à renommer la colonne "status" |
// | de la table "orders" SANS PERTE DE DONNÉES ! (SE RENSEIGNER sur les migrations laravel)                        |
// | Ensuite vous pourrez exécuter "php artisan migrate --seed"                                                     |
// | OU si vous êtes en local et que les pertes de données engendrées par la recréation totale de la base de données|
// | ne sont d'aucun souci, alors vous pouvez totalement la base de données à partir des migrations et exécuter à   |
// | avec la commande "php artisan migrate:fresh --seed"                                                            |
// \---------------------------------------------------------------------------------------------------------------/
enum Status: string
{ // états possibles de la commande (triés dans l'ordre) :

    case BROUILLON = 'BROUILLON';
    case DEVIS = 'DEVIS'; // (première étape)
    case DEVIS_REFUSE = 'DEVIS_REFUSE';
    case BON_DE_COMMANDE_NON_SIGNE = 'BON_DE_COMMANDE_NON_SIGNE';
    case BON_DE_COMMANDE_REFUSE = 'BON_DE_COMMANDE_REFUSE';
    case BON_DE_COMMANDE_SIGNE = 'BON_DE_COMMANDE_SIGNE';
    case COMMANDE = 'COMMANDE';
    case COMMANDE_REFUSEE = 'COMMANDE_REFUSEE';
    case COMMANDE_AVEC_REPONSE = 'COMMANDE_AVEC_REPONSE';
    case PARTIELLEMENT_LIVRE = 'PARTIELLEMENT_LIVRE';
    case SERVICE_FAIT = 'SERVICE_FAIT';
    case LIVRE_ET_PAYE = 'LIVRE_ET_PAYE'; // (dernière étape)
    case ANNULE = 'ANNULE';

    public static function getDefault(): Status
    {
        return Status::DEVIS;
    }

    public static function getDescriptions(): string
    {
        return "
        - BROUILLON : Commande enregistrée à l'état de brouillon. Mis en avant seulement pour l'auteur de la commande.
        - DEVIS : Commande à l'état de devis. En attente d'un bon de commande (première étape).
        - DEVIS_REFUSE : À l'état de devis. Le service financier a refusé de faire un bon de commande.
        - BON_DE_COMMANDE_NON_SIGNE : À l'état de bon de commande non signé. Le bon de commande réalisé par le service financier doit être signé par le directeur.
        - BON_DE_COMMANDE_REFUSE : À l'état de bon de commande non signé. Le directeur de l'IUT a refusé de signer de le bon de commande.
        - BON_DE_COMMANDE_SIGNE : À l'état de bon de commande signé. Le directeur a signé le bon de commande et la commande peut être passée auprès du fournisseur.
        - COMMANDE : À l'état de bon de commande signé. Le bon de commande a été envoyé au fournisseur. Sans répone pour le moment.
        - COMMANDE_REFUSEE : À l'état de bon de commande signé. Le fournisseur a refusé le bon de commande.
        - COMMANDE_AVEC_REPONSE : À l'état de commande officiellement en cours. Le fournisseur a répondu favorablement à la commande avec ou sans communication d'un délai de livraison. En attente de livraison.
        - PARTIELLEMENT_LIVRE : À l'état de commande officiellement en cours. Seulement certains colis ont été marqués comme livrés par des membre du département de la commande. Les autres colis sont en attente de livraison.
        - SERVICE_FAIT : À l'état de bon de livraison. Commande marquée comme totalement livrée par des membres du département qui ont aussi transmis le bon de livraison au service financier. En attente du paiement par le service financier.
        - LIVRE_ET_PAYE : À l'état de bon de livraison. Commande totalement livrée et payée par le service financier.
        ";
    }

    public static function getDescriptionsDict(): array
    {
        return [
            Status::BROUILLON->value => "Commande enregistrée à l'état de brouillon. Mis en avant seulement pour l'auteur de la commande.",
            Status::DEVIS->value => "Commande à l'état de devis. En attente d'un bon de commande (première étape).",
            Status::DEVIS_REFUSE->value => "À l'état de devis. Le service financier a refusé de faire un bon de commande.",
            Status::BON_DE_COMMANDE_NON_SIGNE->value => "À l'état de bon de commande non signé. Le bon de commande réalisé par le service financier doit être signé par le directeur.",
            Status::BON_DE_COMMANDE_REFUSE->value => "À l'état de bon de commande non signé. Le directeur de l'IUT a refusé de signer de le bon de commande.",
            Status::BON_DE_COMMANDE_SIGNE->value => "À l'état de bon de commande signé. Le directeur a signé le bon de commande et la commande peut être passée auprès du fournisseur.",
            Status::COMMANDE->value => "À l'état de bon de commande signé. Le bon de commande a été envoyé au fournisseur. Sans répone pour le moment.",
            Status::COMMANDE_REFUSEE->value => "À l'état de bon de commande signé. Le fournisseur a refusé le bon de commande.",
            Status::COMMANDE_AVEC_REPONSE->value => "À l'état de commande officiellement en cours. Le fournisseur a répondu favorablement à la commande avec ou sans communication d'un délai de livraison. En attente de livraison",
            Status::PARTIELLEMENT_LIVRE->value => "À l'état de commande officiellement en cours. Seulement certains colis ont été marqués comme livrés par des membre du département de la commande. Les autres colis sont en attente de livraison",
            Status::SERVICE_FAIT->value => "À l'état de bon de livraison. Commande marquée comme totalement livrée par des membres du département qui ont aussi transmis le bon de livraison au service financier. En attente du paiement par le service financier",
            Status::LIVRE_ET_PAYE->value => "À l'état de bon de livraison. Commande totalement livrée et payée par le service financier",
            Status::ANNULE->value => 'Commande annulée',
        ];
    }

    public function getDescription(): string
    {
        return Status::getDescriptionsDict()[$this->value];
    }

    public function getCssClass(): string
    {
        return match($this) {
            self::BROUILLON => 'status-brouillon',
            self::DEVIS => 'status-devis',
            self::DEVIS_REFUSE, self::BON_DE_COMMANDE_REFUSE, self::COMMANDE_REFUSEE => 'status-refuse',
            self::BON_DE_COMMANDE_NON_SIGNE => 'status-bc-non-signe',
            self::BON_DE_COMMANDE_SIGNE => 'status-bc-signe',
            self::COMMANDE => 'status-commande',
            self::COMMANDE_AVEC_REPONSE => 'status-commande-reponse',
            self::PARTIELLEMENT_LIVRE => 'status-partiel',
            self::SERVICE_FAIT => 'status-service-fait',
            self::LIVRE_ET_PAYE => 'status-livre-paye',
            self::ANNULE => 'status-annule',
        };
    }

    public function getLabel(): string
    {
        return match($this) {
            self::BROUILLON => 'Brouillon',
            self::DEVIS => 'Devis',
            self::DEVIS_REFUSE => 'Devis refusé',
            self::BON_DE_COMMANDE_NON_SIGNE => 'En attente de signature',
            self::BON_DE_COMMANDE_REFUSE => 'BC refusé',
            self::BON_DE_COMMANDE_SIGNE => 'BC signé',
            self::COMMANDE => 'BC envoyé',
            self::COMMANDE_REFUSEE => 'Commande refusée',
            self::COMMANDE_AVEC_REPONSE => 'Réponse fournisseur',
            self::PARTIELLEMENT_LIVRE => 'Partiellement livré',
            self::SERVICE_FAIT => 'Distribué',
            self::LIVRE_ET_PAYE => 'Livré et payé',
            self::ANNULE => 'Annulé',
        };
    }

    public function getBadgeClass(): string
    {
        return match($this) {
            self::BON_DE_COMMANDE_SIGNE, self::COMMANDE => 'b-blue',
            self::SERVICE_FAIT, self::LIVRE_ET_PAYE => 'b-green',
            self::DEVIS_REFUSE, self::BON_DE_COMMANDE_REFUSE, self::COMMANDE_REFUSEE, self::ANNULE => 'b-red',
            default => 'b-grey',
        };
    }
}
