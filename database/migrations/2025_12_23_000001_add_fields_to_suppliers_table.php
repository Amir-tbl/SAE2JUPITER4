<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->text('address')->nullable()->after('phone_number');
            $table->string('iban', 34)->nullable()->after('address');
            $table->string('bic', 11)->nullable()->after('iban');
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn(['address', 'iban', 'bic']);
        });
    }
};
