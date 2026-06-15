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

    // Retourne l'identifiant du fournisseur
    public function getId(): string
    {
        return $this->attributes['id'];
    }

    // Retourne le nom de l'entreprise fournisseur
    public function getCompanyName(): string
    {
        return $this->attributes['company_name'];
    }

    // Retourne le SIRET du fournisseur
    public function getSiret(): string
    {
        return $this->attributes['siret'];
    }

    // Retourne l'email de contact du fournisseur
    public function getEmail(): ?string
    {
        return $this->attributes['email'];
    }

    // Retourne le telephone du fournisseur
    public function getPhoneNumber(): ?string
    {
        return $this->attributes['phone_number'];
    }

    // Retourne le nom du contact fournisseur
    public function getContactName(): ?string
    {
        return $this->attributes['contact_name'];
    }

    // Retourne l'adresse du fournisseur
    public function getAddress(): ?string
    {
        return $this->attributes['address'] ?? null;
    }

    // Retourne l'IBAN du fournisseur
    public function getIban(): ?string
    {
        return $this->attributes['iban'] ?? null;
    }

    // Retourne le BIC du fournisseur
    public function getBic(): ?string
    {
        return $this->attributes['bic'] ?? null;
    }

    // Retourne les specialites du fournisseur
    public function getSpeciality(): ?string
    {
        return $this->attributes['speciality'];
    }

    // Retourne les notes sur le fournisseur
    public function getNote(): ?string
    {
        return $this->attributes['note'];
    }

    // Verifie si le fournisseur est valide
    public function isValid(): bool
    {
        return $this->attributes['is_valid'];
    }

    // Definit le nom de l'entreprise fournisseur
    public function setCompanyName(string $companyName, bool $save = true): void
    {
        if ($save) {
            $this->setAttribute('company_name', $companyName);
        } else {
            $this->attributes['company_name'] = $companyName;
        }
    }

    // Definit le SIRET du fournisseur
    public function setSiret(string $siret, bool $save = true): void
    {
        if ($save) {
            $this->setAttribute('siret', $siret);
        } else {
            $this->attributes['siret'] = $siret;
        }
    }

    // Definit l'email de contact du fournisseur
    public function setEmail(?string $email, bool $save = true): void
    {
        if ($save) {
            $this->setAttribute('email', $email);
        } else {
            $this->attributes['email'] = $email;
        }
    }

    // Definit le telephone du fournisseur
    public function setPhoneNumber(?string $phone_number, bool $save = true): void
    {
        if ($save) {
            $this->setAttribute('phone_number', $phone_number);
        } else {
            $this->attributes['phone_number'] = $phone_number;
        }
    }

    // Definit le nom du contact fournisseur
    public function setContactName(string $contact_name, bool $save = true): void
    {
        if ($save) {
            $this->setAttribute('contact_name', $contact_name);
        } else {
            $this->attributes['contact_name'] = $contact_name;
        }
    }

    // Definit l'adresse du fournisseur
    public function setAddress(?string $address, bool $save = true): void
    {
        if ($save) {
            $this->setAttribute('address', $address);
        } else {
            $this->attributes['address'] = $address;
        }
    }

    // Definit l'IBAN du fournisseur
    public function setIban(?string $iban, bool $save = true): void
    {
        if ($save) {
            $this->setAttribute('iban', $iban);
        } else {
            $this->attributes['iban'] = $iban;
        }
    }

    // Definit le BIC du fournisseur
    public function setBic(?string $bic, bool $save = true): void
    {
        if ($save) {
            $this->setAttribute('bic', $bic);
        } else {
            $this->attributes['bic'] = $bic;
        }
    }

    // Definit les specialites du fournisseur
    public function setSpeciality(string $speciality, bool $save = true): void
    {
        if ($save) {
            $this->setAttribute('speciality', $speciality);
        } else {
            $this->attributes['speciality'] = $speciality;
        }
    }

    // Definit les notes sur le fournisseur
    public function setNote(?string $note, bool $save = true): void
    {
        if ($save) {
            $this->setAttribute('note', $note);
        } else {
            $this->attributes['note'] = $note;
        }
    }

    // Definit la validite du fournisseur
    public function setValidity(bool $is_valid, bool $save = true): void
    {
        if ($save) {
            $this->setAttribute('is_valid', $is_valid);
        } else {
            $this->attributes['is_valid'] = $is_valid;
        }
    }

    // Retourne la date de derniere modification
    public function getLastUpdateDate(): ?string
    {
        return $this->attributes[$this->getUpdatedAtColumn()];
    }

    // Retourne la date de creation du fournisseur
    public function getCreationDate(): string
    {
        return $this->attributes[$this->getCreatedAtColumn()];
    }

    // Retourne les commandes du fournisseur
    public function getOrders(): Collection
    {
        return $this->getAttribute('orders');
    }

    // Relation vers les commandes du fournisseur
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
