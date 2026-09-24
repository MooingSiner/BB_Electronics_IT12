<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_log', function (Blueprint $table) {
            $table->increments('audit_id');
            $table->unsignedInteger('user_id');
            $table->string('action', 50);
            $table->string('description', 255);
            $table->dateTime('created_at')->useCurrent();
            $table->foreign('user_id', 'fk_audit_user')->references('user_id')->on('user')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_log');
    }
};
