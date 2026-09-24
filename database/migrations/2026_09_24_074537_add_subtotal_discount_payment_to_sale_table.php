<?php

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
        Schema::table('sale', function (Blueprint $table) {
            $table->decimal('subtotal', 10, 2)->default(0)->after('user_id');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('subtotal');
            $table->string('payment_method', 20)->default('cash')->after('total_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale', function (Blueprint $table) {
            $table->dropColumn(['subtotal', 'discount_amount', 'payment_method']);
        });
    }
};
