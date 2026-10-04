<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale', function (Blueprint $table) {
            $table->string('void_reason', 255)->nullable();
            $table->dateTime('voided_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('sale', function (Blueprint $table) {
            $table->dropColumn(['void_reason', 'voided_at']);
        });
    }
};
