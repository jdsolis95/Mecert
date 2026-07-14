<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditorias', function (Blueprint $table) {
            $table->id();
            $table->morphs('auditable');
            $table->enum('accion', ['creado', 'modificado', 'eliminado']);
            $table->foreignId('usuario_id')->constrained('users');
            $table->json('datos_anteriores')->nullable();
            $table->json('datos_nuevos')->nullable();
            $table->timestamps();

            $table->index('usuario_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditorias');
    }
};
