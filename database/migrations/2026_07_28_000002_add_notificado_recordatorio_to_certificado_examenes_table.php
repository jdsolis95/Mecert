<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificado_examenes', function (Blueprint $table) {
            $table->timestamp('notificado_recordatorio_en')->nullable()->after('decidido_at');
        });
    }

    public function down(): void
    {
        Schema::table('certificado_examenes', function (Blueprint $table) {
            $table->dropColumn('notificado_recordatorio_en');
        });
    }
};
