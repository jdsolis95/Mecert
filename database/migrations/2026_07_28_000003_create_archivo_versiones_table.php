<?php

use App\Models\Certificado;
use App\Models\Mentoria;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('archivo_versiones', function (Blueprint $table) {
            $table->id();
            $table->morphs('versionable');
            $table->string('campo', 50);
            $table->string('path');
            $table->string('nombre_original')->nullable();
            $table->foreignId('subido_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['versionable_type', 'versionable_id', 'campo']);
        });

        $this->backfillVersionActual();
    }

    // Los certificados/mentorías que ya tienen un archivo cargado no tienen historial de versiones
    // (las versiones anteriores a este cambio se sobrescribían físicamente y no son recuperables).
    // Se registra al menos la versión vigente para que el historial no arranque vacío.
    private function backfillVersionActual(): void
    {
        $ahora = now();

        DB::table('certificados')->whereNotNull('documento_path')->orderBy('id')->get()
            ->each(function ($certificado) use ($ahora) {
                DB::table('archivo_versiones')->insert([
                    'versionable_type' => Certificado::class,
                    'versionable_id' => $certificado->id,
                    'campo' => 'documento',
                    'path' => $certificado->documento_path,
                    'nombre_original' => $certificado->documento_nombre_original,
                    'subido_por_id' => null,
                    'created_at' => $certificado->updated_at ?? $ahora,
                    'updated_at' => $ahora,
                ]);
            });

        DB::table('mentorias')->whereNotNull('multimedia_path')->orderBy('id')->get()
            ->each(function ($mentoria) use ($ahora) {
                DB::table('archivo_versiones')->insert([
                    'versionable_type' => Mentoria::class,
                    'versionable_id' => $mentoria->id,
                    'campo' => 'multimedia',
                    'path' => $mentoria->multimedia_path,
                    'nombre_original' => $mentoria->multimedia_nombre_original,
                    'subido_por_id' => null,
                    'created_at' => $mentoria->updated_at ?? $ahora,
                    'updated_at' => $ahora,
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('archivo_versiones');
    }
};
