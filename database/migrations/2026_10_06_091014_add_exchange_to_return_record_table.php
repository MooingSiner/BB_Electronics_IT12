<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('return_record', function (Blueprint $table) {
            $table->unsignedInteger('replacement_product_id')->nullable()->after('order_id');
            $table->decimal('price_difference', 10, 2)->nullable()->after('replacement_product_id');

            $table->foreign('replacement_product_id', 'fk_return_replacement_product')
                ->references('product_id')->on('product')->nullOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('return_record', function (Blueprint $table) {
            $table->dropForeign('fk_return_replacement_product');
            $table->dropColumn(['replacement_product_id', 'price_difference']);
        });
    }
};
