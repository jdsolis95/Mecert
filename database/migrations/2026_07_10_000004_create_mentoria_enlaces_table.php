<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mentoria_enlaces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentoria_id')->constrained('mentorias')->cascadeOnDelete();
            $table->string('url', 2048);
            $table->string('texto', 150)->nullable();
            $table->timestamps();

            $table->index('mentoria_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentoria_enlaces');
    }
};
