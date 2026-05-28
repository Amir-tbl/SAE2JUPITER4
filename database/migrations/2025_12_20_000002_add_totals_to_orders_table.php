<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('total_ht', 12, 2)->nullable()->after('cost');
            $table->decimal('total_vat', 12, 2)->nullable()->after('total_ht');
            $table->decimal('total_ttc', 12, 2)->nullable()->after('total_vat');
            $table->string('delivery_location')->nullable()->after('description');
            $table->date('desired_delivery_date')->nullable()->after('delivery_location');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['total_ht', 'total_vat', 'total_ttc', 'delivery_location', 'desired_delivery_date']);
        });
    }
};
