<?php

use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        (new DatabaseSeeder())->run();
    }

    public function down(): void
    {
        // No-op: no se revierte la siembra de roles/admin al hacer rollback.
    }
};
