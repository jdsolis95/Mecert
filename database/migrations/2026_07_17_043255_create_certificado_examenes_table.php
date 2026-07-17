<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificado_examenes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('certificado_id')->constrained('certificados')->cascadeOnDelete();
            $table->date('fecha_propuesta');
            $table->string('lugar_propuesto')->nullable();
            $table->foreignId('propuesto_por_id')->constrained('users');
            $table->enum('estado', ['pendiente', 'aprobado', 'rechazado'])->default('pendiente');
            $table->date('fecha_aprobada')->nullable();
            $table->string('lugar_aprobado')->nullable();
            $table->text('comentario')->nullable();
            $table->foreignId('decidido_por_id')->nullable()->constrained('users');
            $table->timestamp('decidido_at')->nullable();
            $table->timestamps();

            $table->index('certificado_id');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificado_examenes');
    }
};
