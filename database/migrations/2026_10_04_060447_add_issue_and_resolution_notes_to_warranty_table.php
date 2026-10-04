<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warranty', function (Blueprint $table) {
            $table->text('issue')->nullable()->after('claim_date');
            $table->text('resolution_notes')->nullable()->after('outcome');
        });
    }

    public function down(): void
    {
        Schema::table('warranty', function (Blueprint $table) {
            $table->dropColumn(['issue', 'resolution_notes']);
        });
    }
};
