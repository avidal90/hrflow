<?php

namespace Database\Seeders;

use App\Models\Festivo;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class FestivosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $exampleFestivos = [
            '2026-01-01' => 'Año Nuevo',
            '2026-01-06' => 'Epifanía del Señor',
            '2026-04-03' => 'Viernes Santo',
            '2026-05-01' => 'Día del Trabajo',
            '2026-08-15' => 'Asunción de la Virgen',
            '2026-10-12' => 'Día del Pilar',
            '2026-12-08' => 'Inmaculada Concepción',
            '2026-12-25' => 'Natividad del Señor',
        ];

        $festivoDates = array_keys($exampleFestivos);

        Tenant::query()
            ->whereKeyNot(Tenant::principalTenantId())
            ->get()
            ->each(function (Tenant $tenant) use ($festivoDates): void {
                $alreadySeeded = Festivo::query()
                    ->where('tenant_id', $tenant->getKey())
                    ->whereIn('date', $festivoDates)
                    ->count() === count($festivoDates);

                if ($alreadySeeded) {
                    return;
                }

                $rows = [];

                foreach ($festivoDates as $date) {
                    $rows[] = [
                        'tenant_id' => $tenant->getKey(),
                        'date' => $date,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                Festivo::query()->insertOrIgnore($rows);
            });
    }
}
