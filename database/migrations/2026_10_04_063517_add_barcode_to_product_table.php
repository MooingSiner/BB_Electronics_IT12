<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product', function (Blueprint $table) {
            $table->string('barcode', 50)->nullable()->unique()->after('product_code');
        });

        DB::table('product')->whereNull('barcode')->update(['barcode' => DB::raw('product_code')]);
    }

    public function down(): void
    {
        Schema::table('product', function (Blueprint $table) {
            $table->dropColumn('barcode');
        });
    }
};
