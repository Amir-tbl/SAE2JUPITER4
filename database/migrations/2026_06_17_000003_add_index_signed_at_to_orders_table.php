<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajoute un index sur signed_at dans la table orders
     * pour accelerer les requetes de l historique des signatures
     * (filtrage par date de signature dans historiqueSignatures()).
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index('signed_at', 'orders_signed_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_signed_at_index');
        });
    }
};
