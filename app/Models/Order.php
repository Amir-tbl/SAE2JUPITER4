<?php

namespace App\Models;

use Database\Seeders\Status;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class Order extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'title',
        'description',
        'cost',
        'total_ht',
        'total_vat',
        'total_ttc',
        'delivery_location',
        'desired_delivery_date',
        'quote_num',
        'order_num',
        'path_quote',
        'path_purchase_order',
        'path_delivery_note',
        'status',
        'department_id',
        'supplier_id',
        'author_id',
        'path_signed_purchase_order',
        'signed_by_user_id',
        'signed_at',
        'receiver_name',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    // Retourne l'identifiant de la commande
    public function getId(): string
    {
        return $this->attributes['id'];
    }

    // Retourne le titre de la commande
    public function getTitle(): string
    {
        return $this->attributes['title'];
    }

    // Retourne le numero de la commande
    public function getOrderNumber(): string
    {
        return $this->attributes['order_num'];
    }

    // Retourne la description de la commande
    public function getDescription(): ?string
    {
        return $this->attributes['description'];
    }

    // Retourne le statut de la commande
    public function getStatus(bool $noEnum = false): Status|string
    {
        $status = $this->attributes['status'];

        return $noEnum ? $status : Status::from($status);
    }

    // Retourne le cout total de la commande
    public function getCost(): ?string
    {
        return $this->attributes['cost'];
    }

    // Retourne le cout formate en euros
    public function getCostFormatted(): string
    {
        if (is_null($this->getCost())) {
            return 'Non précisé';
        }

        return number_format($this->getCost(), 2, ',', ' ').' €';
    }

    // Retourne le numero du devis
    public function getQuoteNumber(): ?string
    {
        return $this->attributes['quote_num'];
    }

    // Retourne l'URL du devis
    public function getUrlQuote(): ?string
    {
        $path_quote = $this->getAttributeValue('path_quote');
        if (is_null($path_quote)) {
            return null;
        }

        return Storage::url($path_quote);
    }

    // Retourne l'URL du bon de commande
    public function getUrlPurchaseOrder(): ?string
    {
        $path_purchase_order = $this->getAttributeValue('path_purchase_order');
        if (is_null($path_purchase_order)) {
            return null;
        }

        return Storage::url($path_purchase_order);
    }

    public function getUrlSignedPurchaseOrder(): ?string
    {
        $path = $this->getAttributeValue('path_signed_purchase_order');
        if (is_null($path)) {
            return null;
        }
        return Storage::url($path);
    }

    // Retourne l'URL du bon de livraison
    public function getUrlDeliveryNote(): ?string
    {
        $path_delivery_note = $this->getAttributeValue('path_delivery_note');
        if (is_null($path_delivery_note)) {
            return null;
        }

        return Storage::url($path_delivery_note);
    }

    // Retourne la date de derniere modification
    public function getLastUpdateDate(): ?string
    {
        return $this->attributes[$this->getUpdatedAtColumn()];
    }

    // Retourne la date de creation de la commande
    public function getCreationDate(): string
    {
        return $this->attributes[$this->getCreatedAtColumn()];
    }

    // Retourne les colis de la commande
    public function getPackages(bool $foreRefresh = false): Collection
    {
        return $foreRefresh ? $this->packages()->getResults() : $this->getAttribute('packages');
    }

    // Retourne le fournisseur de la commande
    public function getSupplier(): Supplier
    {
        return $this->getAttribute('supplier');
    }

    // Retourne le departement de la commande
    public function getDepartment(): Role
    {
        return $this->getAttribute('department');
    }

    // Retourne les logs de la commande
    public function getLogs(): Collection
    {
        return $this->logs()->getResults();
    }

    // Retourne le premier log de la commande
    public function getFirstLog(): Log
    {
        // TODO Peut-être faire un cache ?
        return $this->getLogs()->first();
    }

    // Retourne l'auteur de la commande
    public function getAuthor(): User
    {
        // TODO Peut-être faire un cache ?
        return $this->author()->getResults();
    }

    // Definit le titre de la commande
    public function setTitle(string $title, bool $save = true): void
    {
        $title = ucfirst($title);
        if ($save) {
            $this->setAttribute('title', $title);
        } else {
            $this->attributes['title'] = $title;
        }
    }

    // Definit le numero de la commande
    public function setOrderNumber(string $order_num, bool $save = true): void
    {
        if ($save) {
            $this->setAttribute('order_num', $order_num);
        } else {
            $this->attributes['order_num'] = $order_num;
        }
    }

    // Definit la description de la commande
    public function setDescription(string $description, bool $save = true): void
    {
        if ($save) {
            $this->setAttribute('description', $description);
        } else {
            $this->attributes['description'] = $description;
        }
    }

    // Definit le statut de la commande
    public function setStatus(Status|string $status, bool $save = true): void
    {
        $status = is_string($status) ? $status : $status->value;
        if ($save) {
            $this->setAttribute('status', $status);
        } else {
            $this->attributes['status'] = $status;
        }

    }

    // Definit le cout total de la commande
    public function setCost(float $cost, bool $save = true): void
    {
        if ($save) {
            $this->setAttribute('cost', $cost);
        } else {
            $this->attributes['cost'] = $cost;
        }
    }

    // Definit le numero du devis
    public function setQuoteNumber(string $quote_num, bool $save = true): void
    {
        if ($save) {
            $this->setAttribute('quote_num', $quote_num);
        } else {
            $this->attributes['quote_num'] = $quote_num;
        }

    }

    // TODO : Autre moyen de récupérer l'url d'un fichier (à tester)
    public function getUrlQuoteAlt(): ?string
    {
        if (is_null($this->path_quote)) {
            return null;
        }

        return asset('storage/'.$this->path_quote);
    }

    public function getUrlPurchaseOrderAlt(): ?string
    {
        if (is_null($this->path_purchase_order)) {
            return null;
        }

        return asset('storage/'.$this->path_purchase_order);
    }

    public function getUrlDeliveryNoteAlt(): ?string
    {
        if (is_null($this->path_delivery_note)) {
            return null;
        }

        return asset('storage/'.$this->path_delivery_note);
    }

    // TODO peut-être un peut factoriser l'upload des fichiers mais... plus tard

    // Enregistre le fichier du devis
    public function uploadQuote(Request $request, bool $save = true): bool
    {
        $request->validate([
            'quote' => 'required|mimes:pdf,doc,docx|max:10240', // Max 10MB
        ]);

        $file = $request->file('quote');
        if ($file) {

            try {

                $fileName = $file->getClientOriginalName();

                if (! stripos($fileName, 'devis')) {
                    $fileName = 'Devis'.$fileName;
                }

                $path_quote = $file->storeAs('uploads/orders/'.$this->getOrderNumber(), $fileName, 'public'); // public -> le dossier

                if ($path_quote) {
                    if ($save) {
                        $this->setAttribute('path_quote', $path_quote);
                    } else {
                        $this->attributes['path_quote'] = $path_quote;
                    }

                    return true;
                }

            } catch (\Throwable $th) {
                error_log("Une erreur est survenue lors de l'enregistrement d'un devis : \n".$th->getMessage());
                report($th);

                return false;
            }

        }

        return false;
    }

    // Enregistre le fichier du bon de commande
    public function uploadPurchaseOrder(Request $request, ?bool $is_signed = false, bool $save = true): bool
    {
        $request->validate([
            'purchase_order' => 'required|mimes:pdf,doc,docx|max:10240', // Max 10MB
        ]);

        $file = $request->file('purchase_order');
        if ($file) {

            try {

                $fileName = $file->getClientOriginalName();

                if (! stripos($fileName, 'BonDeCommande')) {
                    $fileName = 'BonDeCommande'.$fileName;
                }

                if ($is_signed) {
                    $ext = $file->getExtension();
                    $fileName = str_replace('.'.$ext, '(signe).'.$ext, $fileName);
                }

                $purchase_order = $file->storeAs('uploads/orders/'.$this->getOrderNumber(), $fileName, 'public'); // public -> le dossier

                if ($purchase_order) {
                    if ($save) {
                        $this->setAttribute('path_purchase_order', $purchase_order);
                    } else {
                        $this->attributes['path_purchase_order'] = $purchase_order;
                    }

                    return true;
                }

            } catch (\Throwable $th) {
                error_log("Une erreur est survenue lors de l'enregistrement d'un bon de commande : \n".$th->getMessage());
                report($th);

                return false;
            }

        }

        return false;
    }

    // Enregistre le fichier du bon de livraison
    public function uploadDeliveryNote(Request $request, bool $save = true): bool
    {
        $request->validate([
            'delivery_note' => 'required|mimes:pdf,doc,docx|max:10240', // Max 10MB
        ]);

        $file = $request->file('delivery_note');
        if ($file) {

            try {

                $fileName = $file->getClientOriginalName();

                if (! stripos($fileName, 'BonDeLivraison')) {
                    $fileName = 'BonDeLivraison'.$fileName;
                }

                $path_delivery_note = $file->storeAs('uploads/orders/'.$this->getOrderNumber(), $fileName, 'public'); // public -> le dossier

                if ($path_delivery_note) {
                    if ($save) {
                        $this->setAttribute('path_delivery_note', $path_delivery_note);
                    } else {
                        $this->attributes['path_delivery_note'] = $path_delivery_note;
                    }

                    return true;
                }

            } catch (\Throwable $th) {
                error_log("Une erreur est survenue lors de l'enregistrement d'un bon de livraison : \n".$th->getMessage());
                report($th);

                return false;
            }

        }

        return false;
    }


    // Scope : commandes en attente de signature directeur
    public function scopeEnAttenteSignature($query)
    {
        return $query->where('status', \Database\Seeders\Status::BON_DE_COMMANDE_NON_SIGNE->value);
    }

    // Scope : commandes urgentes (en attente de signature depuis plus de X jours)
    public function scopeUrgent($query, int $jours = 7)
    {
        return $query->enAttenteSignature()
            ->where('created_at', '<', now()->subDays($jours));
    }

    // Relation articles de la commande
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    // Recalcule les totaux HT/TVA/TTC depuis les articles
    public function recalculateTotals(): void
    {
        $totalHt = 0;
        $totalVat = 0;

        foreach ($this->articles as $article) {
            $ht = $article->quantity * $article->unit_price;
            $vat = $ht * ($article->vat_rate / 100);
            $totalHt += $ht;
            $totalVat += $vat;
        }

        $this->attributes['total_ht'] = round($totalHt, 2);
        $this->attributes['total_vat'] = round($totalVat, 2);
        $this->attributes['total_ttc'] = round($totalHt + $totalVat, 2);
        $this->save();
    }

    // Relation vers les colis de la commande
    public function packages(): HasMany
    {
        return $this->hasMany(Package::class);
    }

    // Relation vers le fournisseur de la commande
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    // Relation vers les commentaires de la commande
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    // Relation vers les logs de la commande
    public function logs(): HasMany
    {
        return $this->hasMany(Log::class);
    }

    // Relation vers le departement de la commande
    public function department(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'department_id');
    }

    // Relation vers l'auteur de la commande
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function signedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signed_by_user_id');
    }

    // Pas prioritaire - TODO
    // j'ai mis pleins d'options de recherche mais pas obliger de toutes les coder si on manque de temps
    //
    // /**
    //  * Récupérer le log d'une commande à partir d'un indice
    //  *
    //  * @param  int  $indexToSearch  Indice dans la liste des logs de la commande
    //  * @return array // ligne de log
    //  */
    // public function getLog(int $indexToSearch): string {}
    //
    // /**
    //  * Récupérer les logs d'une commande
    //  *
    //  * @return array // tableau de lignes de logs
    //  */
    // public function getLogs(): array {}
    //
    // // /**
    // //  * Récupérer les logs d'une commande contenant un certain texte
    // //  *
    // //  * @param  string  $valueToSearch  récupérer tous les logs contenant une chaîne de caractère en particulier
    // //  * @return array // tableau de lignes de logs
    // //  */
    // // public function getLogsWithText(string $valueToSearch) {}
    //
    // /**
    //  * Ajouter un log
    //  *
    //  * @param  User  $author  Auteur de l'action à l'origine du log
    //  * @param  string  $text  Contenu du log
    //  * @return void
    //  */
    // public function addLog(User $author, string $text) {}
    //
    // /**
    //  * Retirer un log
    //  *
    //  * @param  int  $index  Indice du log
    //  * @return void
    //  */
    // public function removeLog(int $index) {}
}
