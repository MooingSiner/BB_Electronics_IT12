<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_item', function (Blueprint $table) {
            $table->increments('sale_item_id');
            $table->unsignedInteger('sale_id');
            $table->unsignedInteger('product_id');
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->index('product_id', 'idx_saleitem_product');
            $table->foreign('sale_id', 'fk_saleitem_sale')->references('sale_id')->on('sale')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('product_id', 'fk_saleitem_product')->references('product_id')->on('product')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_item_table');
    }
};
