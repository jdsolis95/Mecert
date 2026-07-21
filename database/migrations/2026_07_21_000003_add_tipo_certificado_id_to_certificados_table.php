<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificados', function (Blueprint $table) {
            $table->foreignId('tipo_certificado_id')->nullable()->after('tipo_certificado')
                ->constrained('tipos_certificacion');
        });

        // 1. Crear catalogo a partir de los valores distintos ya existentes en certificados.
        $tipos = DB::table('certificados')->select('tipo_certificado')->distinct()->pluck('tipo_certificado');

        foreach ($tipos as $nombre) {
            DB::table('tipos_certificacion')->insertOrIgnore([
                'nombre' => $nombre,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Apuntar cada certificado existente a su tipo correspondiente en el catalogo nuevo.
        DB::table('tipos_certificacion')->select('id', 'nombre')->orderBy('id')->get()->each(function ($tipo) {
            DB::table('certificados')->where('tipo_certificado', $tipo->nombre)
                ->update(['tipo_certificado_id' => $tipo->id]);
        });

        // 3. El backfill anterior cubre todas las filas por construccion (catalogo generado desde
        // un SELECT DISTINCT de esa misma columna), asi que ya es seguro exigir NOT NULL.
        Schema::table('certificados', function (Blueprint $table) {
            $table->foreignId('tipo_certificado_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('certificados', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tipo_certificado_id');
        });
    }
};
