# Répartition du travail — SAE2 Suivi de Colis IUTV

Équipe de 4. Chacun est responsable d'une **interface** et d'une **fondation transversale**.
L'application reste complète et fonctionnelle sur toutes les branches : ce document sert à
documenter qui possède quoi, pas à découper le code.

## Membres et responsabilités

| Membre | Rôle | Fondation | Interface |
|--------|------|-----------|-----------|
| **Amir TABELLOUT** | Chef de projet | Architecture technique & front-end | Interface Agent |
| **Boran CAV** | Développeur | Authentification | Interface Service Financier |
| **Anas LACENE-NECER** | Développeur | Base de données | Interface Directeur |
| **Amira BENHADDI** | Développeuse | Système de permissions | Interface CRIT |

## Branches

### Branches permanentes
- `main` — version stable (rendue / présentée)
- `develop` — branche d'intégration

### Branches de fondation (à fusionner en premier dans `develop`)
| Branche | Propriétaire | Fichiers principaux |
|---------|--------------|---------------------|
| `feature/architecture` | Amir | `BaseController`, `DashboardController`, `base.blade.php`, `nav`, `alert`, `AppServiceProvider`, layout & routes |
| `feature/authentification` | Boran | `LoginController`, `DevLoginController`, `ProfileController`, `login.blade.php`, `dev-login`, `profile` |
| `feature/base-de-donnees` | Anas | `database/` (migrations, factories, seeders), modèles `Order`, `Package`, `Supplier`, `Comment`, `Log`, `Article` |
| `feature/permissions` | Amira | modèles `Permission`, `Role`, `UserController`, vues `users/`, gestion des droits |

### Branches d'interface
| Branche | Propriétaire | Fichiers principaux |
|---------|--------------|---------------------|
| `feature/interface-agent` | Amir | `dashboard/agent`, `orders/create-step1/2/3`, `historique-agent`, `orderCreationModal` |
| `feature/interface-service-financier` | Boran | `dashboard/service-financier`, `orders/validation`, `suivi-sf`, `SupplierController`, modales SF, `emails/` |
| `feature/interface-directeur` | Anas | `dashboard/directeur`, `orders/signature`, `signatureModal`, `pdf/signed-bc` |
| `feature/interface-crit` | Amira | `dashboard/crit`, `orders/distribution`, `reception`, `viewOrderModalCrit`, `addDeliveryNoteModal` |

### Fichiers transversaux (partagés)
`OrderController`, modèle `Order`, `StatsController`, `LogController` sont utilisés par
plusieurs rôles — à modifier avec coordination.

## Flux de travail

```
feature/<fondation>  ─┐
                       ├─► develop ─► main
feature/interface-*  ─┘
```

1. On travaille sur sa branche `feature/...`.
2. On fusionne d'abord les **fondations** dans `develop`.
3. On fusionne ensuite les **interfaces** dans `develop`.
4. Quand `develop` est stable, on fusionne dans `main`.

## Convention de nommage
- `feature/` : nouvelle fonctionnalité
- `fix/` : correction de bug
- `docs/` : documentation
