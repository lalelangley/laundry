<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kasir', function (Blueprint $table) {
            $table->string('email')->nullable()->unique()->after('nama_kasir');
        });
    }

    public function down(): void
    {
        Schema::table('kasir', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->dropColumn('email');
        });
    }
};
