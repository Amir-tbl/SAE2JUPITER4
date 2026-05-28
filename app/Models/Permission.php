<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{

    /**
     * Retourne l'identifiant de la permission
     *
     * @return string // identifiant de la permission
     */
    public function getId(): string
    {
        return $this->attributes['id'];
    }

    /**
     * Retourne le nom de la permission
     *
     * @return string // nom de la permission
     */
    public function getName(): string
    {
        return $this->attributes['name'];
    }

    /**
     * Retourne la date de la dernière modification de la permission
     *
     * @return ?string // date
     */
    public function getLastUpdateDate(): ?string
    {
        return $this->attributes[$this->getUpdatedAtColumn()];
    }

    /**
     * Retourne la date de création de la permission
     *
     * @return string // date
     */
    public function getCreationDate(): string
    {
        return $this->attributes[$this->getCreatedAtColumn()];
    }

    /**
     * Retourne la liste des rôles avec cette permission (table association)
     *
     * @return BelongsToMany // Liste de rôles avec la permission
     */
    public function roles(): BelongsToMany
    {
        return $this->BelongsToMany(Role::class);
    }
}
