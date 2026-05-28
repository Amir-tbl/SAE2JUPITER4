<?php

use Database\Seeders\Status;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get values of ENUM column (ex: etats) with app:
        // SELECT COLUMN_TYPE
        // FROM information_schema.COLUMNS
        // WHERE TABLE_SCHEMA = 'nom_de_votre_base'
        //   AND TABLE_NAME = 'nom_de_votre_table'
        //   AND COLUMN_NAME = 'nom_de_votre_colonne';
        //
        // Get values of ENUM column (ex: etats) with terminal:
        // SELECT
        //     SUBSTRING(COLUMN_TYPE, 6, LENGTH(COLUMN_TYPE) - 6) AS valeurs_enum
        // FROM information_schema.COLUMNS
        // WHERE TABLE_SCHEMA = 'nom_de_votre_base'
        //     AND TABLE_NAME = 'nom_de_votre_table'
        //     AND COLUMN_NAME = 'nom_de_votre_colonne';
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_num')
                ->unique()
                ->comment('Numéro associé à la commande notamment fournit par le logiciel Chorus');
            $table->string('title')
                ->comment('Désignation de la commande');
            $table->text('description')
                ->nullable()
                ->comment('Description du contenu de la commande et informations complémentaires');
            $table->foreignId('supplier_id')->nullable()
                ->constrained('suppliers', 'id')
                ->nullOnDelete();
            $table->decimal('cost', 12, 2)
                ->nullable()
                ->comment('Coût total de la commande présent sur le devis et sur le bon de commande');
            $table->string('quote_num')
                ->unique()
                ->comment('Numéro du devis associé à la commande');
            $table->string('path_quote')
                ->nullable()
                ->comment('Chemin vers le fichier du devis de la commande');
            $table->string('path_purchase_order')
                ->nullable()
                ->comment('Chemin vers le fichier du bon de commande signé ou non signé');
            $table->string('path_delivery_note')
                ->nullable()
                ->comment('Chemin vers le fichier du bon de livraison associé à la commande');
            $table->enum('status', Status::cases())
                ->default(Status::BROUILLON)
                ->comment('Statut de la commande');
            $table->timestamps();

            $table->foreignId('author_id')->constrained('users', 'id')->cascadeOnUpdate();
            $table->foreignId('department_id')->constrained('roles', 'id')->cascadeOnUpdate();
        });

        Schema::create('packages', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement();
            $table->foreignId('order_id')->constrained('orders', 'id')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('name');
            $table->decimal('cost', 12, 2)
                ->nullable()
                ->comment('Coût unitaire du colis');
            $table->string('expected_delivery_time')
                ->nullable()
                ->comment('Délai prévu de livraison (en jours, semaines, mois ou années) ');
            $table->dateTime('shipping_date')
                ->nullable()
                ->comment('Date de livraison si le colis à été reçu');
            $table->timestamps();

            $table->primary(['id', 'order_id']);

        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package');
        Schema::dropIfExists('orders');
    }
};
