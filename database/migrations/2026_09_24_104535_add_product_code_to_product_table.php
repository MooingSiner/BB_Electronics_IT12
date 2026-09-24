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
        Schema::table('product', function (Blueprint $table) {
            $table->string('product_code', 20)->nullable()->unique()->after('product_id');
        });

        $this->backfillCodes();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product', function (Blueprint $table) {
            $table->dropColumn('product_code');
        });
    }

    private function backfillCodes(): void
    {
        $products = DB::table('product')
            ->join('category', 'category.category_id', '=', 'product.category_id')
            ->orderBy('product.product_id')
            ->select('product.product_id', 'category.category_name')
            ->get();

        $sequences = [];

        foreach ($products as $product) {
            $prefix = $this->prefixFor($product->category_name);
            $sequences[$prefix] = ($sequences[$prefix] ?? 0) + 1;

            DB::table('product')
                ->where('product_id', $product->product_id)
                ->update(['product_code' => sprintf('%s-%04d', $prefix, $sequences[$prefix])]);
        }
    }

    private function prefixFor(string $categoryName): string
    {
        $letters = strtoupper(preg_replace('/[^A-Za-z]/', '', $categoryName));

        return substr($letters, 0, 3) ?: 'GEN';
    }
};
