<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Crée la table logs
    public function up(): void
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders', 'id')->cascadeOnDelete();
            $table->text('content');
            $table->foreignId('author_id')->nullable()->constrained('users', 'id')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    // Supprime la table logs
    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
