<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{

    // Retourne l'identifiant de la permission
    public function getId(): string
    {
        return $this->attributes['id'];
    }

    // Retourne le nom de la permission
    public function getName(): string
    {
        return $this->attributes['name'];
    }

    // Retourne la date de derniere modification
    public function getLastUpdateDate(): ?string
    {
        return $this->attributes[$this->getUpdatedAtColumn()];
    }

    // Retourne la date de creation de la permission
    public function getCreationDate(): string
    {
        return $this->attributes[$this->getCreatedAtColumn()];
    }

    // Relation vers les roles ayant cette permission
    public function roles(): BelongsToMany
    {
        return $this->BelongsToMany(Role::class);
    }
}
