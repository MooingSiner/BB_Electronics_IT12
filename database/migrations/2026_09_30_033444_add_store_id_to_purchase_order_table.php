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
        Schema::table('purchase_order', function (Blueprint $table) {
            $table->unsignedInteger('supplier_id')->nullable()->change();
            $table->unsignedInteger('store_id')->nullable()->after('supplier_id');
            $table->foreign('store_id', 'fk_po_store')->references('store_id')->on('store')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_order', function (Blueprint $table) {
            $table->dropForeign('fk_po_store');
            $table->dropColumn('store_id');
            $table->unsignedInteger('supplier_id')->nullable(false)->change();
        });
    }
};
