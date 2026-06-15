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

    // Retourne l'identifiant du commentaire
    public function getId(): string
    {
        return $this->attributes['id'];
    }

    // Retourne l'identifiant de la commande
    public function getOrderId(): string
    {
        return $this->attributes['order_id'];
    }

    // Retourne le contenu du commentaire
    public function getContent(): string
    {
        return $this->attributes['content'];
    }

    // Retourne la date de derniere modification
    public function getLastUpdateDate(): ?string
    {
        return $this->attributes[$this->getUpdatedAtColumn()];
    }

    // Retourne la date de creation du commentaire
    public function getCreationDate(): string
    {
        return $this->attributes[$this->getCreatedAtColumn()];
    }
    // Retourne l'auteur du commentaire
    public function getAuthor(): User
    {
        return $this->getAttribute('author');
    }

    // Retourne la commande associee au commentaire
    public function getOrder(): Order
    {
        return $this->getAttribute('order');
    }

    // Definit le contenu du commentaire
    public function setContent(string $content, bool $save = true): void
    {
        if ($save) {
            $this->setAttribute('content', $content);
        } else {
            $this->attributes['content'] = $content;
        }
    }

    // Relation vers l'auteur du commentaire
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    // Relation vers la commande du commentaire
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
