<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Give every column a size that fits what it stores instead of the framework defaults
     * (varchar 255, int 10 or 11, decimal 10,2): a stock quantity is at most 5 digits and a price at most 6 digits and 2 decimals.
     */
    public function up(): void
    {
        DB::table('audit_log')->whereRaw('CHAR_LENGTH(description) > 200')
            ->update(['description' => DB::raw("CONCAT(LEFT(description, 197), '...')")]);

        Schema::table('user', function (Blueprint $table) {
            $table->string('password_hash', 100)->change();
            $table->string('profile_photo', 100)->nullable()->change();
        });

        Schema::table('product', function (Blueprint $table) {
            $table->string('image_url', 200)->nullable()->change();
            $table->decimal('unit_price', 8, 2)->change();
            $table->decimal('cost_price', 8, 2)->change();
            $table->smallInteger('quantity_on_hand')->default(0)->change();
            $table->smallInteger('reorder_level')->default(0)->change();
            $table->smallInteger('warranty_period_days')->nullable()->default(0)->change();
        });

        Schema::table('supplier', function (Blueprint $table) {
            $table->string('address', 150)->nullable()->change();
        });

        Schema::table('order_item', function (Blueprint $table) {
            $table->smallInteger('quantity_ordered')->change();
            $table->smallInteger('quantity_received')->default(0)->change();
            $table->decimal('unit_cost', 8, 2)->change();
        });

        Schema::table('sale', function (Blueprint $table) {
            $table->decimal('subtotal', 9, 2)->default(0)->change();
            $table->decimal('discount_amount', 9, 2)->default(0)->change();
            $table->decimal('vat_amount', 9, 2)->default(0)->change();
            $table->decimal('total_amount', 9, 2)->default(0)->change();
            $table->decimal('amount_paid', 9, 2)->default(0)->change();
            $table->decimal('change_amount', 9, 2)->default(0)->change();
            $table->string('void_reason', 120)->nullable()->change();
        });

        Schema::table('sale_item', function (Blueprint $table) {
            $table->smallInteger('quantity')->change();
            $table->decimal('unit_price', 8, 2)->change();
            $table->decimal('subtotal', 9, 2)->change();
        });

        Schema::table('stock_adjustment', function (Blueprint $table) {
            $table->smallInteger('quantity_change')->change();
            $table->string('reason', 150)->change();
        });

        Schema::table('return_record', function (Blueprint $table) {
            $table->smallInteger('quantity')->change();
            $table->string('reason', 120)->change();
            $table->decimal('price_difference', 8, 2)->nullable()->change();
        });

        Schema::table('audit_log', function (Blueprint $table) {
            $table->string('description', 200)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_log', function (Blueprint $table) {
            $table->string('description', 255)->change();
        });

        Schema::table('return_record', function (Blueprint $table) {
            $table->integer('quantity')->change();
            $table->string('reason', 255)->change();
            $table->decimal('price_difference', 10, 2)->nullable()->change();
        });

        Schema::table('stock_adjustment', function (Blueprint $table) {
            $table->integer('quantity_change')->change();
            $table->string('reason', 255)->change();
        });

        Schema::table('sale_item', function (Blueprint $table) {
            $table->integer('quantity')->change();
            $table->decimal('unit_price', 10, 2)->change();
            $table->decimal('subtotal', 10, 2)->change();
        });

        Schema::table('sale', function (Blueprint $table) {
            foreach (['subtotal', 'discount_amount', 'vat_amount', 'total_amount', 'amount_paid', 'change_amount'] as $column) {
                $table->decimal($column, 10, 2)->default(0)->change();
            }
            $table->string('void_reason', 255)->nullable()->change();
        });

        Schema::table('order_item', function (Blueprint $table) {
            $table->integer('quantity_ordered')->change();
            $table->integer('quantity_received')->default(0)->change();
            $table->decimal('unit_cost', 10, 2)->change();
        });

        Schema::table('supplier', function (Blueprint $table) {
            $table->string('address', 255)->nullable()->change();
        });

        Schema::table('product', function (Blueprint $table) {
            $table->string('image_url', 500)->nullable()->change();
            $table->decimal('unit_price', 10, 2)->change();
            $table->decimal('cost_price', 10, 2)->change();
            $table->integer('quantity_on_hand')->default(0)->change();
            $table->integer('reorder_level')->default(0)->change();
            $table->integer('warranty_period_days')->nullable()->default(0)->change();
        });

        Schema::table('user', function (Blueprint $table) {
            $table->string('password_hash', 255)->change();
            $table->string('profile_photo', 255)->nullable()->change();
        });
    }
};
