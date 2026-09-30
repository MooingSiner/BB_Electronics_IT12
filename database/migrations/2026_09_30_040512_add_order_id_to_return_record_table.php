<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('return_record', function (Blueprint $table) {
            $table->unsignedInteger('order_id')->nullable()->after('supplier_id');
            $table->foreign('order_id', 'fk_return_order')->references('order_id')->on('purchase_order')->cascadeOnUpdate()->nullOnDelete();
        });

        $this->backfillOrderIds();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('return_record', function (Blueprint $table) {
            $table->dropForeign('fk_return_order');
            $table->dropColumn('order_id');
        });
    }

    /**
     * Best-effort attribution of existing supplier damage reports to the
     * most recent matching purchase order, since none was recorded before.
     */
    private function backfillOrderIds(): void
    {
        $reports = DB::table('return_record')
            ->whereNotNull('supplier_id')
            ->whereNull('order_id')
            ->get();

        foreach ($reports as $report) {
            $order = DB::table('purchase_order')
                ->join('order_item', 'order_item.order_id', '=', 'purchase_order.order_id')
                ->where('purchase_order.supplier_id', $report->supplier_id)
                ->where('order_item.product_id', $report->product_id)
                ->orderByDesc('purchase_order.order_date')
                ->value('purchase_order.order_id');

            if ($order) {
                DB::table('return_record')->where('return_id', $report->return_id)->update(['order_id' => $order]);
            }
        }
    }
};
