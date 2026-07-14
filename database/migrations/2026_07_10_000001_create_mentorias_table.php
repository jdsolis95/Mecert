<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mentorias', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 150);
            $table->text('desor_icripcion');
            $table->foreignId('autd')->constrained('users');
            $table->enum('multimedia_tipo', ['imagen', 'documento', 'video'])->nullable();
            $table->string('multimedia_path')->nullable();
            $table->string('multimedia_nombre_original')->nullable();
            $table->string('multimedia_url', 2048)->nullable();
            $table->foreignId('eliminado_por_id')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index('autor_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentorias');
    }
};
