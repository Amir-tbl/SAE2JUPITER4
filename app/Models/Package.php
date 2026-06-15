<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Package extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'label',
        'cost',
        'date_expected_delivery',
        'shipping_date',
    ];

    // Retourne l'identifiant du colis
    public function getId(): string
    {
        return $this->attributes['id'];
    }

    // Retourne l'identifiant de la commande du colis
    public function getOrderId(): string
    {
        return $this->attributes['order_id'];
    }

    // Retourne le nom du colis
    public function getName(): string
    {
        return $this->attributes['name'];
    }

    // Retourne le cout unitaire du colis
    public function getCout(): ?int
    {
        return $this->attributes['cost'];
    }

    // Retourne le delai prevu de livraison
    public function getExpectedDeliveryTime(): ?string
    {
        return $this->attributes['expected_delivery_time'];
    }

    // Retourne la date de livraison du colis
    public function getShippingDate(): ?string
    {
        return $this->attributes['shipping_date'];
    }

    // Definit le nom du colis
    public function setName(string $name, bool $save = true): void
    {
        if ($save) {
            $this->setAttribute('name', $name);
        } else {
            $this->attributes['name'] = $name;
        }
    }

    // Definit le cout unitaire du colis
    public function setCout(int $cost, bool $save = true): void
    {
        if ($save) {
            $this->setAttribute('cost', $cost);
        } else {
            $this->attributes['cost'] = $cost;
        }
    }

    // Definit le delai prevu de livraison
    public function setExpectedDeliveryTime(string $expected_delivery_time, bool $save = true): void
    {
        if ($save) {
            $this->setAttribute('expected_delivery_time', $expected_delivery_time);
        } else {
            $this->attributes['expected_delivery_time'] = $expected_delivery_time;
        }
    }

    // Definit la date de livraison du colis
    public function setShippingDate(string $shipping_date, bool $save = true): void
    {
        if ($save) {
            $this->setAttribute('shipping_date', $shipping_date);
        } else {
            $this->attributes['shipping_date'] = $shipping_date;
        }
    }

    // Retourne la date de derniere modification du colis
    public function getLastUpdateDate(): ?string
    {
        return $this->attributes[$this->getUpdatedAtColumn()];
    }

    // Retourne la date de creation du colis
    public function getCreationDate(): string
    {
        return $this->attributes[$this->getCreatedAtColumn()];
    }

    // Relation vers la commande du colis
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
