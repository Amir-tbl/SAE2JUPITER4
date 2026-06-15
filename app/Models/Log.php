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

    // Retourne l'identifiant du log
    public function getId(): string
    {
        return $this->attributes['id'];
    }

    // Retourne l'identifiant de la commande du log
    public function getOrderId(): string
    {
        return $this->attributes['order_id'];
    }

    // Retourne le contenu du log
    public function getContent(): string
    {
        return $this->attributes['content'];
    }

    // Retourne l'auteur du log
    public function getAuthor(): User
    {
        return $this->getAttribute('author');
    }

    // Retourne la commande associee au log
    public function getOrder(): Order
    {
        return $this->getAttribute('order');
    }

    // Retourne la date de creation du log
    public function getCreationDate(): string
    {
        return $this->attributes[$this->getCreatedAtColumn()];
    }

    // Definit le contenu du log
    public function setContent(string $content, bool $save = true): void
    {
        if ($save) {
            $this->setAttribute('content', $content);
        } else {
            $this->attributes['content'] = $content;
        }
    }

    // Relation vers l'auteur du log
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    // Relation vers la commande du log
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
