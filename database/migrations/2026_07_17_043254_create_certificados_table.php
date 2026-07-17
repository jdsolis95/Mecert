<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('colaborador_id')->constrained('users');
            $table->string('tipo_certificado', 150);
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento');
            $table->string('documento_path')->nullable();
            $table->string('documento_nombre_original')->nullable();
            $table->foreignId('eliminado_por_id')->nullable()->constrained('users');
            $table->timestamp('notificado_amarillo_en')->nullable();
            $table->timestamp('notificado_rojo_en')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('colaborador_id');
            $table->index('fecha_vencimiento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificados');
    }
};
