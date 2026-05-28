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

    /**
     * Retourne l'identifiant du colis
     *
     * @return string // identifiant du colis
     */
    public function getId(): string
    {
        return $this->attributes['id'];
    }

    /**
     * Retourne l'identifiant de la commande du colis
     *
     * @return string // identifiant de la commande du colis
     */
    public function getOrderId(): string
    {
        return $this->attributes['order_id'];
    }

    /**
     * Retourne le nom du colis
     *
     * @return string // nom du colis
     */
    public function getName(): string
    {
        return $this->attributes['name'];
    }

    /**
     * Retourne le coût unitaire du colis
     *
     * @return ?int // coût unitaire du colis
     */
    public function getCout(): ?int
    {
        return $this->attributes['cost'];
    }

    /**
     * Retourne le délai prévu de livraison si cela a été communiqué par le fournisseur après l'envoi du bon de commande
     *
     * @return ?string // délai prévu de livraison
     */
    public function getExpectedDeliveryTime(): ?string
    {
        return $this->attributes['expected_delivery_time'];
    }

    /**
     * Retourne la date prévue de livraison si le colis est livré
     *
     * @return ?string // Date de livraison si le colis est livré, null sinon
     */
    public function getShippingDate(): ?string
    {
        return $this->attributes['shipping_date'];
    }

    /**
     * Définit le nom du colis
     *
     * @param  string  $name  nom du colis.
     * @param  bool  $save  si la donnée doit directement être sauvegardée en base de données.
     */
    public function setName(string $name, bool $save = true): void
    {
        if ($save) {
            $this->setAttribute('name', $name);
        } else {
            $this->attributes['name'] = $name;
        }
    }

    /**
     * Définit le coût unitaire du colis
     *
     * @param  int  $cost  coût unitaire du colis.
     * @param  bool  $save  si la donnée doit directement être sauvegardée en base de données.
     */
    public function setCout(int $cost, bool $save = true): void
    {
        if ($save) {
            $this->setAttribute('cost', $cost);
        } else {
            $this->attributes['cost'] = $cost;
        }
    }

    /**
     * Définit le délai prévu de livraison
     * Généralement lorsque cela a été communiqué par le fournisseur après l'envoi du bon de commande
     *
     * @param  string  $expected_delivery_time  délai prévu livraison
     * @param  bool  $save  si la donnée doit directement être sauvegardée en base de données.
     */
    public function setExpectedDeliveryTime(string $expected_delivery_time, bool $save = true): void
    {
        if ($save) {
            $this->setAttribute('expected_delivery_time', $expected_delivery_time);
        } else {
            $this->attributes['expected_delivery_time'] = $expected_delivery_time;
        }
    }

    /**
     * Définit la date prévue de livraison
     * Généralement lorsque le colis est livré
     *
     * @param  string  $shipping_date  date prévue de livraison
     * @param  bool  $save  si la donnée doit directement être sauvegardée en base de données.
     */
    public function setShippingDate(string $shipping_date, bool $save = true): void
    {
        if ($save) {
            $this->setAttribute('shipping_date', $shipping_date);
        } else {
            $this->attributes['shipping_date'] = $shipping_date;
        }
    }

    /**
     * Retourne la date de la dernière modification du colis
     *
     * @return ?string // date
     */
    public function getLastUpdateDate(): ?string
    {
        return $this->attributes[$this->getUpdatedAtColumn()];
    }

    /**
     * Retourne la date de création du colis
     *
     * @return string // date
     */
    public function getCreationDate(): string
    {
        return $this->attributes[$this->getCreatedAtColumn()];
    }

    /**
     * Retourne la commande dont appartient le colis
     *
     * @return BelongsTo // Commande associée au colis
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
