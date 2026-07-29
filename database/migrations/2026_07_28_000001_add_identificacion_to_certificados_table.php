<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificados', function (Blueprint $table) {
            $table->string('nombre_certificado', 150)->nullable()->after('tipo_certificado_id');
            $table->string('emisor', 150)->nullable()->after('nombre_certificado');
            $table->string('codigo_certificado', 100)->nullable()->after('emisor');
        });
    }

    public function down(): void
    {
        Schema::table('certificados', function (Blueprint $table) {
            $table->dropColumn(['nombre_certificado', 'emisor', 'codigo_certificado']);
        });
    }
};
