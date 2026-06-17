<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajoute des index sur department_id et author_id dans orders
     * pour accelerer les requetes de filtrage par departement et par auteur.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index('department_id', 'orders_department_id_index');
            $table->index('author_id', 'orders_author_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_department_id_index');
            $table->dropIndex('orders_author_id_index');
        });
    }
};
