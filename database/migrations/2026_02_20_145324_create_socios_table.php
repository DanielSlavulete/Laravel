<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('socios', function (Blueprint $table) {
        $table->id();

        $table->foreignId('solicitud_id')
              ->nullable()
              ->constrained('solicitudes')
              ->nullOnDelete();

        $table->string('nombre', 100);
        $table->string('apellidos', 150);
        $table->date('fecha_nacimiento');
        $table->string('telefono', 30);

        $table->string('tipo_documento', 20);
        $table->string('numero_documento', 20)->unique();

        $table->string('direccion', 200);
        $table->string('ciudad', 100);
        $table->string('provincia', 100);
        $table->string('codigo_postal', 15);
        $table->string('pais', 80);

        $table->boolean('tiene_hijos');
        $table->unsignedSmallInteger('numero_hijos')->nullable();

        $table->boolean('hijo_down');
        $table->date('fecha_nacimiento_hijo_down')->nullable();

        $table->string('tipo_socio', 20);

        $table->string('estado', 20)->default('activo');
        $table->timestamp('fecha_alta')->useCurrent();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('socios');
    }
};
