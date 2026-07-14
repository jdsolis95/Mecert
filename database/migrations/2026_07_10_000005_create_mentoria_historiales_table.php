<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mentoria_historiales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentoria_id')->constrained('mentorias')->cascadeOnDelete();
            $table->foreignId('editado_por_id')->constrained('users');
            $table->json('datos_anteriores');
            $table->timestamps();

            $table->index('mentoria_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentoria_historiales');
    }
};
