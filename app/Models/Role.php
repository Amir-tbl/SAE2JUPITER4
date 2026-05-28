<?php

namespace App\Models;

use Database\Seeders\PermissionValue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Role extends Model
{

    /**
     * Retourne l'identifiant rôle
     *
     * @return string // identifiant du rôle
     */
    public function getId(): string
    {
        return $this->attributes['id'];
    }

    /**
     * Retourne le nom du rôle
     *
     * @return string // nom du rôle
     */
    public function getName(): string
    {
        return $this->attributes['name'];
    }

    /**
     * Retourne true si le rôle correspond à un département
     *
     * @return bool // Si le rôle correspond à un départment
     */
    public function isDepartment(): bool
    {
        return $this->attributes['is_department'];
    }

    /**
     * Retourne un dictionnaire des permissions de l'association de plusieurs rôles déjà chargés
     *
     * @param  Collection  $roles  Collection de rôles (modèle Role)
     * @return array // Dictionnaire des permissions
     */
    public static function getPermissionsAsDict(Collection $roles): array
    {
        $permissions = PermissionValue::getDict();

        /* @var Role $role */
        foreach ($roles as $role) {
            foreach ($role->getPermissionsAsIds() as $permission) {
                $permissions[$permission] = true;
            }
        }

        return $permissions;
    }

    /**
     * Retourne la liste des permissions en tant que modèles, du rôle
     *
     * @return Collection // permissions en modèles du rôle
     */
    public function getPermissionsAsModels(bool $fromDatabase = true): Collection
    {
        return $fromDatabase ? $this->permissions()->getResults() : $this->getAttribute('permissions');
    }

    /**
     * Retourne la liste des noms des permissions en tant que string, du rôle
     *
     * @return Collection // Liste des noms des permissions en string
     */
    public function getPermissionsAsNames(): Collection
    {
        return $this->permissions()->pluck('name');
    }

    /**
     * Retourne la liste des identifiants des permissions en tant que string, du rôle
     *
     * @return Collection // Liste des identifiants des permissions en string
     */
    public function getPermissionsAsIds(): Collection
    {
        return $this->permissions()->pluck('id');
    }

    /**
     * Vérifie si un rôle a la permission "$permission"
     *
     * @param  PermissionValue  $permission  Permission à vérifier
     * @param  bool  $strict  Si ne retourne pas true avec la permission administrateur (à false par défaut)
     * @return bool // true si le rôle à la permission "$permission", false sinon
     */
    // TODO à tester
    public function hasPermission(PermissionValue $permission, bool $strict = false): bool
    {
        if (! $strict && $this->permissions()->where('id', PermissionValue::ADMIN)->exists()) {
            return true;
        }

        return (bool) $this->permissions()->where('id', $permission)->exists();
    }

    /**
     * Retourne la date de la dernière modification du rôle
     *
     * @return ?string // date
     */
    public function getLastUpdateDate(): ?string
    {
        return $this->attributes[$this->getUpdatedAtColumn()];
    }

    /**
     * Retourne la date de création du rôle
     *
     * @return string // date
     */
    public function getCreationDate(): string
    {
        return $this->attributes[$this->getCreatedAtColumn()];
    }

    /**
     * Retourne la liste des utilisateurs qui possèdent le rôle
     *
     * @return Collection // Collection (liste) des utilisateurs ayant le rôle
     */
    public function getUsers(): Collection
    {
        return $this->getAttribute('users');
    }

    /**
     * Retourne la liste des permissions du rôle
     *
     * @return Collection // Collection (liste) permissions du rôle
     */
    public function getPermissions(): Collection
    {
        return $this->getAttribute('permissions');
    }

    /**
     * Retourne la liste des permissions du rôle
     *
     * @return Collection // Collection (liste) permissions du rôle
     */
    public function getOrders(): Collection
    {
        return $this->getAttribute('permissions');
    }

    /**
     * Retourne la liste des utilisateurs associés au rôle (table association)
     *
     * @return BelongsToMany // Liste des utilisateurs du rôle
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Retourne la liste des permissions du rôle (table association)
     *
     * @return BelongsToMany // Liste des permissions du rôle
     */
    public function permissions(): BelongsToMany
    {
        return $this->BelongsToMany(Permission::class);
    }

    /**
     * Retourne la liste des commandes associée au rôle (pour les départements)
     *
     * @return HasMany // Commandes du rôle (département)
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'department_id');
    }
}
