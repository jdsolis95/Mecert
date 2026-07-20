<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accesos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users');
            $table->dateTime('fecha_ingreso');
            $table->dateTime('fecha_salida')->nullable();
            $table->string('ip_ingreso', 45)->nullable();
            $table->timestamps();

            $table->index(['usuario_id', 'fecha_salida']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accesos');
    }
};
