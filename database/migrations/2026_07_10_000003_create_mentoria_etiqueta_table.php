<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mentoria_etiqueta', function (Blueprint $table) {
            $table->foreignId('mentoria_id')->constrained('mentorias')->cascadeOnDelete();
            $table->foreignId('etiqueta_id')->constrained('etiquetas')->cascadeOnDelete();

            $table->primary(['mentoria_id', 'etiqueta_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentoria_etiqueta');
    }
};
