<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Log extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'content',
        'type',
        'author_id',
        'order_id',
    ];

    /**
     * Retourne l'identifiant du log
     *
     * @return string // identifiant du log
     */
    public function getId(): string
    {
        return $this->attributes['id'];
    }

    /**
     * Retourne l'identifiant de la commande du log
     *
     * @return string // identifiant de la commande du log
     */
    public function getOrderId(): string
    {
        return $this->attributes['order_id'];
    }

    /**
     * Retourne le contenu du log
     *
     * @return string // contenu du log
     */
    public function getContent(): string
    {
        return $this->attributes['content'];
    }

    /**
     * Retourne l'auteur de l'action, l'utilisateur associé au log
     *
     * @return User // Utilisateur auteur de l'action / associé au log
     */
    public function getAuthor(): User
    {
        return $this->getAttribute('author');
    }

    /**
     * Retourne la commande dont appartient le log
     *
     * @return Order // Commande du log
     */
    public function getOrder(): Order
    {
        return $this->getAttribute('order');
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
     * Définit le contenu du log
     *
     * @param  string  $content  contenu du log.
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
     * Retourne l'auteur de l'action
     *
     * @return BelongsTo // Auteur de l'action
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Retourne la commande dont appartient le log
     *
     * @return BelongsTo // Commande du log
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
