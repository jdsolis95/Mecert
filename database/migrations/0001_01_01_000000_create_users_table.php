<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    //Ejecuta la migración para crear la tabla de usuarios
    public function up(): void
    {
        Schema::create('users', function (Blueprint $tabla) {
        $tabla->id();                              // id (estándar Laravel, auto_increment)
        $tabla->string('cedula', 20)->unique();    // cédula física costarricense, única
        $tabla->string('name', 100);               // nombre
        $tabla->string('primer_apellido', 100);    // primer apellido
        $tabla->string('segundo_apellido', 100)->nullable(); // segundo apellido (opcional)
        $tabla ->string('email')->unique();          // correo corporativo
        $tabla->timestamp('email_verified_at')->nullable();
        $tabla->string('password');
        $tabla->boolean('esta_activo')->default(true); // activo por defecto
        $tabla->rememberToken();
        $tabla->boolean('must_change_password')->default(true);
        $tabla->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $tabla) {
            $tabla->string('email')->primary();
            $tabla->string('token');
            $tabla->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $tabla) {
            $tabla->string('id')->primary();
            $tabla->foreignId('user_id')->nullable()->index();
            $tabla->string('ip_address', 45)->nullable();
            $tabla->text('user_agent')->nullable();
            $tabla->longText('payload');
            $tabla->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
