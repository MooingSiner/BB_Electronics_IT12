<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warranty', function (Blueprint $table) {
            $table->increments('warranty_id');
            $table->unsignedInteger('sale_item_id');
            $table->string('customer_name', 100);
            $table->string('contact_number', 30)->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('claim_status', ['none', 'claimed', 'in_progress', 'resolved'])->default('none');
            $table->date('claim_date')->nullable();
            $table->enum('outcome', ['replacement', 'refund', 'repair', 'supplier_exchange', 'denied', 'n_a'])->default('n_a');
            $table->foreign('sale_item_id', 'fk_warranty_saleitem')->references('sale_item_id')->on('sale_item')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warranty_table');
    }
};
