<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Database\Seeders\PermissionValue;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Champs remplissables en masse
    protected $fillable = [
        'login',
        'last_name',
        'first_name',
        'email',
        'phone_number',
        'campus',
        'password',
        'signature_path',
    ];

    // Champs masques lors de la serialisation
    protected $hidden = [
        'password',
    ];

    // Casts des attributs
    protected $casts = [
        'password' => 'hashed',
    ];
 
    // Permissions de l'utilisateur via ses roles
    private $permissions = null;

    // Retourne l'identifiant de l'utilisateur
    public function getId(): string
    {
        return $this->attributes['id'];
    }

    // Retourne le prenom de l'utilisateur
    public function getFirstName(): string
    {
        return $this->attributes['first_name'];
    }

    // Retourne le nom de famille de l'utilisateur
    public function getLastName(): string
    {
        return $this->attributes['last_name'];
    }

    public function getSignatureUrl(): ?string
    {
        $path = $this->attributes['signature_path'] ?? null;
        return $path ? \Illuminate\Support\Facades\Storage::url($path) : null;
    }

    public function hasSignature(): bool
    {
        return !empty($this->attributes['signature_path']);
    }

    // Retourne le nom complet de l'utilisateur
    public function getFullName(): ?string
    {
        return $this->getFirstName().' '.$this->getLastName();
    }

    // Retourne l'email de l'utilisateur
    public function getEmail(): ?string
    {
        return $this->attributes['email'];
    }

    // Retourne le telephone de l'utilisateur
    public function getPhoneNumber(): ?string
    {
        return $this->attributes['phone_number'];
    }

    // Retourne les roles de l'utilisateur
    public function getRoles(): Collection
    {
        return $this->getAttribute('roles');
    }

    // Verifie si l'utilisateur a un role donne
    public function hasRole(Role $role): bool
    {
        return $this->getRoles()->contains($role);
    }

    // Retourne les departements de l'utilisateur
    public function getDepartments(): Collection
    {
        return $this->getRoles()->filter(fn (Role $role) => $role->isDepartment());
    }

    // Retourne les permissions de l'utilisateur
    public function getPermissions(bool $forceLoad = false): array
    {
        if ($forceLoad || ! $this->permissions) {
            $this->permissions = Role::getPermissionsAsDict($this->getRoles());
        }

        return $this->permissions;
    }

    // Verifie si l'utilisateur a une permission donnee
    public function hasPermission(PermissionValue|string $permission, bool $strict = false, bool $forceLoad = false): bool
    {
        $permissions = $this->getPermissions($forceLoad);

        return (! $strict && $permissions[PermissionValue::ADMIN->value]) || $permissions[is_string($permission) ? $permission : $permission->value];
    }

    // Retourne les logs de l'utilisateur
    public function getLogs(): Collection
    {
        return $this->getAttribute('logs');
    }

    // Retourne les commentaires de l'utilisateur
    public function getComments(): Collection
    {
        return $this->getAttribute('comments');
    }

    // Retourne les permissions sous forme de dictionnaire
    public function getPermissionsAsDict(): array
    {
        return Role::getPermissionsAsDict($this->getRoles());
    }

    // Retourne la date de derniere modification
    public function getLastUpdateDate(): ?string
    {
        return $this->attributes[$this->getUpdatedAtColumn()];
    }

    // Retourne la date de creation de l'utilisateur
    public function getCreationDate(): string
    {
        return $this->attributes[$this->getCreatedAtColumn()];
    }

    // Definit le prenom de l'utilisateur
    public function setFirstName(string $firstName, bool $save = true): void
    {
        $firstName = ucfirst(strtolower($firstName, 'UTF-8'));
        if ($save) {
            $this->setAttribute('first_name', $firstName);
        } else {
            $this->attributes['first_name'] = $firstName;
        }
    }

    // Definit le nom de famille de l'utilisateur
    public function setLastName(string $lastName, bool $save = true): void
    {
        $lastName = strtoupper($lastName, 'UTF-8');
        if ($save) {
            $this->setAttribute('last_name', $lastName);
        } else {
            $this->attributes['last_name'] = $lastName;
        }
    }

    // Definit le telephone de l'utilisateur
    public function setPhoneNumber(string $phoneNumber, bool $save = true): void
    {
        if ($save) {
            $this->setAttribute('phone_number', $phoneNumber);
        } else {
            $this->attributes['phone_number'] = $phoneNumber;
        }
    }

    // Definit l'email de l'utilisateur
    public function setEmail(string $emailAdress, bool $save = true): void
    {
        if ($save) {
            $this->setAttribute('email', $emailAdress);
        } else {
            $this->attributes['email'] = $emailAdress;
        }
    }

    // Relation vers les roles de l'utilisateur
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    // Relation vers les commentaires de l'utilisateur
    public function comments(): HasMany
    {
        return $this->HasMany(Comment::class);
    }

    // Relation vers les logs de l'utilisateur
    public function logs(): HasMany
    {
        return $this->HasMany(Log::class);
    }

    // public function hasRole(): bool {}
}
