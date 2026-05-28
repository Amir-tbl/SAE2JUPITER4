<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Supplier extends Model
{
    use HasFactory;

    // protected $fillable = [
    //     'company_name',
    //     'siret',
    //     'email',
    //     'phone_number',
    //     'contact_name',
    //     'is_valid',
    // ];

    // Pas remplissable
    protected $guarded = [

    ];

    /**
     * Retourne l'identifiant du fournisseur
     *
     * @return string // identifiant du fournisseur
     */
    public function getId(): string
    {
        return $this->attributes['id'];
    }

    /**
     * Retourne le nom de l'entreprise du fournisseur.
     *
     * @return string // Nom d'entreprise du fournisseur
     */
    public function getCompanyName(): string
    {
        return $this->attributes['company_name'];
    }

    /**
     * Retourne le SIRET de l'entreprise du fournisseur.
     *
     * @return string // SIRET d'entreprise du fournisseur
     */
    public function getSiret(): string
    {
        return $this->attributes['siret'];
    }

    /**
     * Retourne l'email de contact du fournisseur.
     *
     * @return ?string // email de contact du fournisseur
     */
    public function getEmail(): ?string
    {
        return $this->attributes['email'];
    }

    /**
     * Retourne le numéro de téléphone de contact du fournisseur.
     *
     * @return ?string // numéro de téléphone de contact du fournisseur
     */
    public function getPhoneNumber(): ?string
    {
        return $this->attributes['phone_number'];
    }

    /**
     * Retourne le nom du contact dans l'entreprise fournisseur.
     *
     * @return ?string // nom du contact dans l'entreprise fournisseur
     */
    public function getContactName(): ?string
    {
        return $this->attributes['contact_name'];
    }

    /**
     * Retourne l'adresse du fournisseur.
     *
     * @return ?string
     */
    public function getAddress(): ?string
    {
        return $this->attributes['address'] ?? null;
    }

    /**
     * Retourne l'IBAN du fournisseur.
     *
     * @return ?string
     */
    public function getIban(): ?string
    {
        return $this->attributes['iban'] ?? null;
    }

    /**
     * Retourne le BIC du fournisseur.
     *
     * @return ?string
     */
    public function getBic(): ?string
    {
        return $this->attributes['bic'] ?? null;
    }

    /**
     * Retourne la description des spécialités de l'entreprise fournisseur.
     *
     * @return ?string // description des spécialités de l'entreprise fournisseur
     */
    public function getSpeciality(): ?string
    {
        return $this->attributes['speciality'];
    }

    /**
     * Retourne les notes sur le fournisseur.
     *
     * @return ?string // notes sur le fournisseur.
     */
    public function getNote(): ?string
    {
        return $this->attributes['note'];
    }

    /**
     * Retourne true si le fournisseur est considéré comme valide, false sinon.
     * Un fournisseur valide est un fournisseur auprès duquel il est possible de commander.
     *
     * @return bool // Si le fournisseur est valide
     */
    public function isValid(): bool
    {
        return $this->attributes['is_valid'];
    }

    /**
     * Définit le nom de l'entreprise du fournisseur
     *
     * @param  string  $companyName  nom de l'entreprise fournisseur
     * @param  bool  $save  si la donnée doit directement être sauvegardée en base de données
     */
    public function setCompanyName(string $companyName, bool $save = true): void
    {
        if ($save) {
            $this->setAttribute('company_name', $companyName);
        } else {
            $this->attributes['company_name'] = $companyName;
        }
    }

    /**
     * Définit le SIRET de l'entreprise du fournisseur
     *
     * @param  string  $siret  SIRET de l'entreprise fournisseur
     * @param  bool  $save  si la donnée doit directement être sauvegardée en base de données
     */
    public function setSiret(string $siret, bool $save = true): void
    {
        if ($save) {
            $this->setAttribute('siret', $siret);
        } else {
            $this->attributes['siret'] = $siret;
        }
    }

    /**
     * Définit l'email de contact du fournisseur.
     *
     * @param  string  $email  email de contact du fournisseur
     * @param  bool  $save  si la donnée doit directement être sauvegardée en base de données
     */
    public function setEmail(?string $email, bool $save = true): void
    {
        if ($save) {
            $this->setAttribute('email', $email);
        } else {
            $this->attributes['email'] = $email;
        }
    }

    /**
     * Définit le numéro de téléphone de contact du fournisseur.
     *
     * @param  string  $phone_number  numéro de téléphone de contact du fournisseur
     * @param  bool  $save  si la donnée doit directement être sauvegardée en base de données
     */
    public function setPhoneNumber(?string $phone_number, bool $save = true): void
    {
        if ($save) {
            $this->setAttribute('phone_number', $phone_number);
        } else {
            $this->attributes['phone_number'] = $phone_number;
        }
    }

    /**
     * Définit le nom du contact dans l'entreprise fournisseur.
     *
     * @param  string  $contact_name  nom du contact dans l'entreprise fournisseur
     * @param  bool  $save  si la donnée doit directement être sauvegardée en base de données
     */
    public function setContactName(string $contact_name, bool $save = true): void
    {
        if ($save) {
            $this->setAttribute('contact_name', $contact_name);
        } else {
            $this->attributes['contact_name'] = $contact_name;
        }
    }

    /**
     * Définit l'adresse du fournisseur.
     */
    public function setAddress(?string $address, bool $save = true): void
    {
        if ($save) {
            $this->setAttribute('address', $address);
        } else {
            $this->attributes['address'] = $address;
        }
    }

    /**
     * Définit l'IBAN du fournisseur.
     */
    public function setIban(?string $iban, bool $save = true): void
    {
        if ($save) {
            $this->setAttribute('iban', $iban);
        } else {
            $this->attributes['iban'] = $iban;
        }
    }

    /**
     * Définit le BIC du fournisseur.
     */
    public function setBic(?string $bic, bool $save = true): void
    {
        if ($save) {
            $this->setAttribute('bic', $bic);
        } else {
            $this->attributes['bic'] = $bic;
        }
    }

    /**
     * Définit la description des spécialités de l'entreprise fournisseur.
     *
     * @param  string  $speciality  description des spécialités de l'entreprise fournisseur.
     * @param  bool  $save  si la donnée doit directement être sauvegardée en base de données
     */
    public function setSpeciality(string $speciality, bool $save = true): void
    {
        if ($save) {
            $this->setAttribute('speciality', $speciality);
        } else {
            $this->attributes['speciality'] = $speciality;
        }
    }

    /**
     * Définit le texte des notes sur le fournisseur.
     *
     * @param  string  $note  texte des notes sur le fournisseur.
     * @param  bool  $save  si la donnée doit directement être sauvegardée en base de données
     */
    public function setNote(?string $note, bool $save = true): void
    {
        if ($save) {
            $this->setAttribute('note', $note);
        } else {
            $this->attributes['note'] = $note;
        }
    }

    /**
     * Définit si le fournisseur est considéré comme valide, false sinon.
     * Un fournisseur valide est un fournisseur auprès duquel il est possible de commander.
     *
     * @param  bool  $is_valid  true si le fournisseur est considéré comme valide, false sinon.
     * @param  bool  $save  si la donnée doit directement être sauvegardée en base de données
     */
    public function setValidity(bool $is_valid, bool $save = true): void
    {
        if ($save) {
            $this->setAttribute('is_valid', $is_valid);
        } else {
            $this->attributes['is_valid'] = $is_valid;
        }
    }

    /**
     * Retourne la date de la dernière modification du fournisseur
     *
     * @return ?string // date
     */
    public function getLastUpdateDate(): ?string
    {
        return $this->attributes[$this->getUpdatedAtColumn()];
    }

    /**
     * Retourne la date de création du fournisseur
     *
     * @return string // date
     */
    public function getCreationDate(): string
    {
        return $this->attributes[$this->getCreatedAtColumn()];
    }

    /**
     * Retourne la liste des commandes du fournisseur
     *
     * @return Collection // Collection (liste) des commandes du fournisseur
     */
    public function getOrders(): Collection
    {
        return $this->getAttribute('orders');
    }

    /**
     * Retourne la liste des commandes du fournisseur
     *
     * @return HasMany // Liste des commandes du fournisseur
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
