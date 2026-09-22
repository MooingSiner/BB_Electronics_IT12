<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustment', function (Blueprint $table) {
            $table->increments('adjustment_id');
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('user_id');
            $table->dateTime('adjustment_date')->useCurrent();
            $table->integer('quantity_change');
            $table->string('reason', 255);
            $table->foreign('product_id', 'fk_adjustment_product')->references('product_id')->on('product')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('user_id', 'fk_adjustment_user')->references('user_id')->on('user')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustment_table');
    }
};
