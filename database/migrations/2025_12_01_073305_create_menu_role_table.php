<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_role', function (Blueprint $table) {
    $table->id();
    $table->integer('role_id');
    $table->integer('menu_id');
    $table->boolean('is_active')->default(true);
    $table->boolean('can_view')->default(true);
    $table->boolean('can_add')->default(false);
    $table->boolean('can_edit')->default(false);
    $table->boolean('can_delete')->default(false);
    $table->boolean('can_cancel')->default(false);
    $table->boolean('can_change_password')->default(false);
    $table->boolean('can_restore_data')->default(false);
    $table->boolean('show_delete_backup')->default(false);
    $table->boolean('show_logout')->default(false);
    $table->boolean('can_access_settings')->default(false);
    $table->timestamps();
});
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_role');
    }
};
