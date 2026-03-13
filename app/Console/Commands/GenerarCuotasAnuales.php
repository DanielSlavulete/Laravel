<?php

namespace App\Console\Commands;

use App\Models\Cuota;
use App\Models\Socio;
use Illuminate\Console\Command;

class GenerarCuotasAnuales extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Puedes pasar un año manualmente:
     * php artisan cuotas:generar-anuales 2027
     */
    protected $signature = 'cuotas:generar-anuales {anio?}';

    /**
     * The console command description.
     */
    protected $description = 'Genera las cuotas anuales para todos los socios activos';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $anio = $this->argument('anio') ?? now()->year;

        $this->info("Generando cuotas del año {$anio}...");

        $socios = Socio::where('estado', 'activo')->get();

        if ($socios->isEmpty()) {
            $this->warn('No hay socios activos.');
            return self::SUCCESS;
        }

        $creadas = 0;
        $existentes = 0;

        foreach ($socios as $socio) {
            $yaExiste = Cuota::where('socio_id', $socio->id)
                ->where('anio', $anio)
                ->exists();

            if ($yaExiste) {
                $existentes++;
                continue;
            }

            Cuota::create([
                'socio_id' => $socio->id,
                'anio' => (int) $anio,
                'pagado' => 'false',
                'fecha_pago' => null,
            ]);

            $creadas++;
        }

        $this->info("Cuotas creadas: {$creadas}");
        $this->line("Cuotas ya existentes: {$existentes}");

        return self::SUCCESS;
    }
}