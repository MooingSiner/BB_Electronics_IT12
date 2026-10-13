<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A setting such as the VAT rate is a short value, so 50 characters are enough.
     */
    public function up(): void
    {
        Schema::table('setting', function (Blueprint $table) {
            $table->string('setting_value', 50)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('setting', function (Blueprint $table) {
            $table->string('setting_value', 255)->change();
        });
    }
};
