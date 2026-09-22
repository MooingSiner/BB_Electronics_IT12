<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier', function (Blueprint $table) {
            $table->increments('supplier_id');
            $table->string('supplier_name', 150);
            $table->string('contact_person', 100)->nullable();
            $table->string('contact_number', 30)->nullable();
            $table->string('address', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_table');
    }
};
