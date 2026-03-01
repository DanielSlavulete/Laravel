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
    Schema::create('cuotas', function (Blueprint $table) {
        $table->id();

        $table->foreignId('socio_id')
              ->constrained('socios')
              ->cascadeOnDelete();

        $table->unsignedSmallInteger('anio');

        $table->boolean('pagado')->default(false);
        $table->timestamp('fecha_pago')->nullable();

        $table->timestamps();

        // Evita duplicados: un socio no puede tener 2 cuotas del mismo año
        $table->unique(['socio_id', 'anio']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuotas');
    }
};
