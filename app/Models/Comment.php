<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'content',
        'author_id',
        'order_id',
    ];

    /**
     * Retourne l'identifiant du commentaire
     *
     * @return string // identifiant du commentaire
     */
    public function getId(): string
    {
        return $this->attributes['id'];
    }

    /**
     * Retourne l'identifiant de la commande du commentaire
     *
     * @return string // identifiant de la commande du commentaire
     */
    public function getOrderId(): string
    {
        return $this->attributes['order_id'];
    }

    /**
     * Retourne le contenu du commentaire
     *
     * @return string // contenu du commentaire
     */
    public function getContent(): string
    {
        return $this->attributes['content'];
    }

    /**
     * Retourne la date de la dernière modification du commentaire
     *
     * @return ?string // date
     */
    public function getLastUpdateDate(): ?string
    {
        return $this->attributes[$this->getUpdatedAtColumn()];
    }

    /**
     * Retourne la date de création du commentaire
     *
     * @return string // date
     */
    public function getCreationDate(): string
    {
        return $this->attributes[$this->getCreatedAtColumn()];
    }
    /**
     * Retourne l'auteur de l'action, l'utilisateur associé au commentaire
     *
     * @return User // Utilisateur auteur de l'action / associé au commentaire
     */
    public function getAuthor(): User
    {
        return $this->getAttribute('author');
    }

    /**
     * Retourne la commande dont appartient le commentaire
     *
     * @return Order // Commande du commentaire
     */
    public function getOrder(): Order
    {
        return $this->getAttribute('order');
    }

    /**
     * Définit le contenu du commentaire
     *
     * @param  string  $content  contenu du commentaire.
     * @param  bool  $save  : si la fonction sauvegarde en base de données
     */
    public function setContent(string $content, bool $save = true): void
    {
        if ($save) {
            $this->setAttribute('content', $content);
        } else {
            $this->attributes['content'] = $content;
        }
    }

    /**
     * Retourne l'utilisateur, auteur de la commande
     *
     * @return BelongsTo // Utilisateur, auteur de la commande
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Retourne la commande concernée par le commentaire
     *
     * @return BelongsTo // Commande associée au commentaire
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
