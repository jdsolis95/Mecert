<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificado_historiales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('certificado_id')->constrained('certificados')->cascadeOnDelete();
            $table->foreignId('editado_por_id')->constrained('users');
            $table->json('datos_anteriores');
            $table->timestamps();

            $table->index('certificado_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificado_historiales');
    }
};
