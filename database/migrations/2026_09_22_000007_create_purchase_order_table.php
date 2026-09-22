<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order', function (Blueprint $table) {
            $table->increments('order_id');
            $table->unsignedInteger('supplier_id');
            $table->unsignedInteger('user_id');
            $table->dateTime('order_date')->useCurrent();
            $table->enum('status', ['pending', 'partially_received', 'received', 'cancelled'])->default('pending');
            $table->dateTime('date_received')->nullable();
            $table->foreign('supplier_id', 'fk_po_supplier')->references('supplier_id')->on('supplier')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('user_id', 'fk_po_user')->references('user_id')->on('user')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_table');
    }
};
