<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('return_record', function (Blueprint $table) {
            $table->increments('return_id');
            $table->unsignedInteger('sale_id')->nullable();
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('supplier_id')->nullable();
            $table->dateTime('return_date')->useCurrent();
            $table->integer('quantity');
            $table->string('reason', 255);
            $table->enum('condition', ['defective', 'damaged', 'wrong_item', 'customer_changed_mind', 'other']);
            $table->enum('resolution', ['replacement', 'refund', 'repair', 'supplier_exchange', 'pending'])->default('pending');
            $table->enum('status', ['open', 'resolved'])->default('open');
            $table->foreign('sale_id', 'fk_return_sale')->references('sale_id')->on('sale')->cascadeOnUpdate()->nullOnDelete();
            $table->foreign('product_id', 'fk_return_product')->references('product_id')->on('product')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('supplier_id', 'fk_return_supplier')->references('supplier_id')->on('supplier')->cascadeOnUpdate()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_record_table');
    }
};
