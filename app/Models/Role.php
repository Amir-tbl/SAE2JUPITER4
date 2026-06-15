<?php

namespace App\Models;

use Database\Seeders\PermissionValue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Role extends Model
{

    // Retourne l'identifiant du role
    public function getId(): string
    {
        return $this->attributes['id'];
    }

    // Retourne le nom du role
    public function getName(): string
    {
        return $this->attributes['name'];
    }

    // Verifie si le role est un departement
    public function isDepartment(): bool
    {
        return $this->attributes['is_department'];
    }

    // Retourne un dictionnaire des permissions de plusieurs roles
    public static function getPermissionsAsDict(Collection $roles): array
    {
        $permissions = PermissionValue::getDict();

        foreach ($roles as $role) {
            foreach ($role->getPermissionsAsIds() as $permission) {
                $permissions[$permission] = true;
            }
        }

        return $permissions;
    }

    // Retourne les permissions en tant que modeles
    public function getPermissionsAsModels(bool $fromDatabase = true): Collection
    {
        return $fromDatabase ? $this->permissions()->getResults() : $this->getAttribute('permissions');
    }

    // Retourne les noms des permissions du role
    public function getPermissionsAsNames(): Collection
    {
        return $this->permissions()->pluck('name');
    }

    // Retourne les identifiants des permissions du role
    public function getPermissionsAsIds(): Collection
    {
        return $this->permissions()->pluck('id');
    }

    // Verifie si le role a une permission donnee
    // TODO a tester
    public function hasPermission(PermissionValue $permission, bool $strict = false): bool
    {
        if (! $strict && $this->permissions()->where('id', PermissionValue::ADMIN)->exists()) {
            return true;
        }

        return (bool) $this->permissions()->where('id', $permission)->exists();
    }

    // Retourne la date de derniere modification du role
    public function getLastUpdateDate(): ?string
    {
        return $this->attributes[$this->getUpdatedAtColumn()];
    }

    // Retourne la date de creation du role
    public function getCreationDate(): string
    {
        return $this->attributes[$this->getCreatedAtColumn()];
    }

    // Retourne les utilisateurs ayant ce role
    public function getUsers(): Collection
    {
        return $this->getAttribute('users');
    }

    // Retourne les permissions du role
    public function getPermissions(): Collection
    {
        return $this->getAttribute('permissions');
    }

    // Retourne les commandes du role
    public function getOrders(): Collection
    {
        return $this->getAttribute('permissions');
    }

    // Relation vers les utilisateurs du role
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    // Relation vers les permissions du role
    public function permissions(): BelongsToMany
    {
        return $this->BelongsToMany(Permission::class);
    }

    // Relation vers les commandes du departement
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'department_id');
    }
}
