<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale', function (Blueprint $table) {
            $table->increments('sale_id');
            $table->unsignedInteger('user_id');
            $table->dateTime('sale_date')->useCurrent();
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('change_amount', 10, 2)->default(0);
            $table->enum('status', ['completed', 'voided'])->default('completed');
            $table->index('sale_date', 'idx_sale_date');
            $table->foreign('user_id', 'fk_sale_user')->references('user_id')->on('user')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_table');
    }
};
