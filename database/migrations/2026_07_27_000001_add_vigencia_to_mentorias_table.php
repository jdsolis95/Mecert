<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mentorias', function (Blueprint $table) {
            $table->date('fecha_vencimiento')->nullable()->after('descripcion');
            $table->timestamp('notificado_amarillo_en')->nullable();
            $table->timestamp('notificado_rojo_en')->nullable();
            $table->index('fecha_vencimiento');
        });
    }

    public function down(): void
    {
        Schema::table('mentorias', function (Blueprint $table) {
            $table->dropIndex(['fecha_vencimiento']);
            $table->dropColumn(['fecha_vencimiento', 'notificado_amarillo_en', 'notificado_rojo_en']);
        });
    }
};
