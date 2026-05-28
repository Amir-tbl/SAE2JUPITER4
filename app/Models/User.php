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
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
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

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
    ];
 
    /**
     * Permissions de l'utilisateur à l'issu de ses rôles.
     *
     * @var array
     */
    private $permissions = null;

    /**
     * Retourne l'identifiant de l'utilisateur
     *
     * @return string // identifiant de l'utilisateur
     */
    public function getId(): string
    {
        return $this->attributes['id'];
    }

    /**
     * Retourne le prénom de l'utilisateur
     *
     * @return string prénom de l'utilisateur
     */
    public function getFirstName(): string
    {
        return $this->attributes['first_name'];
    }

    /**
     * Retourne le nom de famille de l'utilisateur
     *
     * @return string nom de famille de l'utilisateur
     */
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

    /**
     * Retourne le nom complet de l'utilisateur
     * en concaténant le prénom et le nom séparés par un espace.
     *
     * @return ?string le nom complet de l'utilisateur
     */
    public function getFullName(): ?string
    {
        return $this->getFirstName().' '.$this->getLastName();
    }

    /**
     * Retourne l'adresse email de l'utilisateur
     *
     * @return ?string l'adresse email de l'utilisateur
     */
    public function getEmail(): ?string
    {
        return $this->attributes['email'];
    }

    /**
     * Retourne le numéro de téléphone de l'utilisateur
     *
     * @return ?string numéro de téléphone de l'utilisateur
     */
    public function getPhoneNumber(): ?string
    {
        return $this->attributes['phone_number'];
    }

    /**
     * Retourne la liste des rôles de l'utilisateur
     *
     * @return Collection // Collection (liste) des rôles de l'utilisateur
     */
    public function getRoles(): Collection
    {
        return $this->getAttribute('roles');
    }

    /**
     * Retourne true si l'utilisateur a un rôle en particulier, false sinon.
     *
     * @param  Role  $role  le rôle à vérifier
     * @return bool // Si l'utilisateur a le rôle $role
     */
    public function hasRole(Role $role): bool
    {
        return $this->getRoles()->contains($role);
    }

    /**
     * Retourne la liste des départements auxquels appartient l'utilisateur
     *
     * @return Collection // Collection (liste) des départements de l'utilisateur
     */
    public function getDepartments(): Collection
    {
        return $this->getRoles()->filter(fn (Role $role) => $role->isDepartment());
    }

    /**
     * Retourne true si l'utilisateur a un rôle en particulier, false sinon.
     *
     * @param  bool  $forceLoad  Si la fonction force la récupération des informations depuis la base de données plutôt que du cache du modèle
     * @return array // Si l'utilisateur a le rôle $role
     */
    public function getPermissions(bool $forceLoad = false): array
    {
        if ($forceLoad || ! $this->permissions) {
            $this->permissions = Role::getPermissionsAsDict($this->getRoles());
        }

        return $this->permissions;
    }

    /**
     * Vérifie si un utilisateur a la permission "$permission"
     *
     * @param  PermissionValue|string  $permission  Permission à vérifier
     * @param  bool  $strict  Si ne retourne pas true avec la permission administrateur (à false par défaut)
     * @param  bool  $forceLoad  Si la fonction force la récupération des informations depuis la base de données plutôt que du cache du modèle
     * @return bool // true si l'utilisateur a un rôle avec la permission "$permission", false sinon
     */
    public function hasPermission(PermissionValue|string $permission, bool $strict = false, bool $forceLoad = false): bool
    {
        $permissions = $this->getPermissions($forceLoad);

        return (! $strict && $permissions[PermissionValue::ADMIN->value]) || $permissions[is_string($permission) ? $permission : $permission->value];
    }

    /**
     * Retourne la liste actions de l'utilisateur
     *
     * @return Collection // Liste des actions de l'utilisateur
     */
    public function getLogs(): Collection
    {
        return $this->getAttribute('logs');
    }

    /**
     * Retourne la liste des commentaires écrits par l'utilisateur
     *
     * @return Collection // commentaires écrits par l'utilisateur
     */
    public function getComments(): Collection
    {
        return $this->getAttribute('comments');
    }

    /**
     * Retourne un dictionnaire des permissions de l'utilisateur
     *
     * @return array // Dictionnaire des permissions de l'utilisateur
     */
    public function getPermissionsAsDict(): array
    {
        return Role::getPermissionsAsDict($this->getRoles());
    }

    /**
     * Retourne la date de la dernière modification de l'utilisateur
     *
     * @return ?string // date
     */
    public function getLastUpdateDate(): ?string
    {
        return $this->attributes[$this->getUpdatedAtColumn()];
    }

    /**
     * Retourne la date de création de l'utilisateur
     *
     * @return string // date
     */
    public function getCreationDate(): string
    {
        return $this->attributes[$this->getCreatedAtColumn()];
    }

    /**
     * Définit le prénom de l'utilisateur
     * Le prénom est d'abord mis en minuscule
     * puis la première lettre est mise en majuscule.
     *
     * @param  string  $firstName  prénom de l'utilisateur
     * @param  bool  $save  si la donnée doit directement être sauvegardée en base de données
     */
    public function setFirstName(string $firstName, bool $save = true): void
    {
        $firstName = ucfirst(strtolower($firstName, 'UTF-8'));
        if ($save) {
            $this->setAttribute('first_name', $firstName);
        } else {
            $this->attributes['first_name'] = $firstName;
        }
    }

    /**
     * Définit le nom de l'utilisateur
     * Le nom est d'abord mis en majuscule
     *
     * @param  string  $lastName  nom de l'utilisateur
     * @param  bool  $save  si la donnée doit directement être sauvegardée en base de données
     */
    public function setLastName(string $lastName, bool $save = true): void
    {
        $lastName = strtoupper($lastName, 'UTF-8');
        if ($save) {
            $this->setAttribute('last_name', $lastName);
        } else {
            $this->attributes['last_name'] = $lastName;
        }
    }

    /**
     * Définit le numéro de téléphone de l'utilisateur
     *
     * @param  string  $phoneNumber  numéro de téléphone de l'utilisateur
     * @param  bool  $save  si la donnée doit directement être sauvegardée en base de données
     */
    public function setPhoneNumber(string $phoneNumber, bool $save = true): void
    {
        if ($save) {
            $this->setAttribute('phone_number', $phoneNumber);
        } else {
            $this->attributes['phone_number'] = $phoneNumber;
        }
    }

    /**
     * Définit l'adresse email de l'utilisateur
     *
     * @param  string  $emailAdress  adresse email de l'utilisateur
     * @param  bool  $save  si la donnée doit directement être sauvegardée en base de données
     */
    public function setEmail(string $emailAdress, bool $save = true): void
    {
        if ($save) {
            $this->setAttribute('email', $emailAdress);
        } else {
            $this->attributes['email'] = $emailAdress;
        }
    }

    /**
     * Retourne la liste des rôles de l'utilisateur (table association)
     *
     * @return BelongsToMany // Liste des rôles de l'utilisateur
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    /**
     * Retourne la liste des commentaires de l'utilisateur
     *
     * @return HasMany // Liste des commentaires de l'utilisateur
     */
    public function comments(): HasMany
    {
        return $this->HasMany(Comment::class);
    }

    /**
     * Retourne la liste des actions de l'utilisateur
     *
     * @return HasMany // Liste des actions de l'utilisateur
     */
    public function logs(): HasMany
    {
        return $this->HasMany(Log::class);
    }

    // public function hasRole(): bool {}
}
