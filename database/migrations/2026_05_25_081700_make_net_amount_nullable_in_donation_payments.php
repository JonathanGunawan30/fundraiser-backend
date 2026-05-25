<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donation_payments', function (Blueprint $table) {
            $table->unsignedInteger('net_amount')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('donation_payments', function (Blueprint $table) {
            $table->unsignedInteger('net_amount')->nullable(false)->change();
        });
    }
};
