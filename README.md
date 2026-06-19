# SAE2 JUPITER4 -- Suivi de Colis IUT Villetaneuse

> 📂 **Documents a rendre** — Les documents de **choix des groupes / roles** et de **base de donnees** se trouvent dans le dossier [`DOC_A_RENDRE/`](./DOC_A_RENDRE).

Application web de suivi de colis pour l'IUT de Villetaneuse (Sorbonne Paris Nord).

Chaque commande suit un parcours precis : creation par un agent de departement, validation par le service financier, signature par le directeur, puis reception et distribution par le service postal (CRIT). L'application permet a chaque acteur de suivre l'avancement en temps reel et d'intervenir a son niveau.

## Stack technique

- **Backend** : Laravel 12, PHP 8.2+
- **Frontend** : Blade + Bootstrap 5.3
- **Base de donnees** : MariaDB
- **Auth** : CAS universitaire (prod) / login local (dev)
- **Infra** : Docker (Ubuntu 24.04, Apache2)

## Prerequis

- [Docker](https://www.docker.com/) et Docker Compose
- Git

C'est tout. Docker embarque PHP, Apache, MariaDB et Composer.

## Installation

```bash
# 1. Cloner le depot
git clone https://github.com/Amir-tbl/SAE2JUPITER4.git
cd SAE2JUPITER4

# 2. Copier le fichier d'environnement
cp .env.example .env

# 3. Lancer les conteneurs
docker compose up --build

# 4. L'application tourne sur http://localhost:8080
```

Le conteneur Docker s'occupe de :
- Installer les dependances PHP (Composer)
- Generer la cle de chiffrement Laravel
- Lancer MariaDB, executer les migrations et les seeders
- Demarrer Apache

### Installation sans Docker (dev local)

Si vous preferez travailler sans Docker :

```bash
# Prerequis : PHP 8.2+, Composer, MariaDB

git clone https://github.com/VOTRE-USERNAME/SAE2JUPITER4.git
cd SAE2JUPITER4

composer install
cp .env.example .env
php artisan key:generate

# Creer la base de donnees et l'utilisateur MariaDB
# (voir db_setup.sql pour le script SQL)

# Lancer les migrations avec les donnees de test
php artisan migrate --seed

# Demarrer le serveur
php artisan serve
```

## Comptes de test

Mot de passe : `password` pour tous les comptes.

| Role | Nom | Login | Email |
|------|-----|-------|-------|
| Agent (Dept Info) | Amir TABELLOUT | `tabellout` | amir@test.com |
| Service Financier | Boran CAV | `cav` | boran@test.com |
| Directeur IUT | Anas LACENE-NECER | `lacene` | anas@test.com |
| Responsable colis (CRIT) | Amira BENHADDI | `benhaddi` | amira@test.com |
| SuperAdmin | Super ADMIN | `superadmin` | superadmin@test.com |

Trois fournisseurs sont pre-charges : Amazon, LDLC, Dell.

## Roles et permissions

| Role | Ce qu'il peut faire |
|------|-------------------|
| **Agent / Departement** | Creer des commandes, consulter celles de son departement, ajouter des bons de livraison, commenter |
| **Service Financier** | Voir toutes les commandes, gerer les devis et bons de commande, gerer les fournisseurs, valider les paiements |
| **Directeur IUT** | Voir toutes les commandes, signer les bons de commande |
| **Responsable colis (CRIT)** | Voir toutes les commandes, gerer les colis livres (reception, distribution) |
| **SuperAdmin** | Acces total, gestion des utilisateurs et des roles |

Il existe aussi plusieurs departements (Info, GEA, CJ, GEII, RT, SD) qui partagent les memes permissions d'agent.

## Structure du projet

```
app/              Code applicatif Laravel (Models, Controllers, etc.)
config/           Configuration Laravel
configDocker/     Dockerfile, vhost Apache, entrypoint
database/         Migrations et seeders
public/           Point d'entree web (index.php)
resources/        Vues Blade, CSS, JS
routes/           Definitions des routes
storage/          Logs, cache, sessions
tests/            Tests PestPHP
```

## Licence

MIT
