<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Convertir la colonne ENUM en VARCHAR pour éviter les problèmes
        // quand de nouveaux statuts sont ajoutés sans migration.
        // Syntaxe spécifique à MySQL/MariaDB ; sous SQLite (tests) la colonne
        // est déjà stockée comme du texte, donc rien à faire.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY COLUMN status VARCHAR(255) NOT NULL DEFAULT 'DEVIS'");
        }
    }

    public function down(): void
    {
        // Pas de rollback — l'ENUM original est trop restrictif
    }
};
