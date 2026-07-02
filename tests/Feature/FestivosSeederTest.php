<?php

namespace Tests\Feature;

use App\Models\Festivo;
use App\Models\Tenant;
use Database\Seeders\FestivosSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class FestivosSeederTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * @return array<int, string>
     */
    private function expectedDates(): array
    {
        return [
            '2026-01-01',
            '2026-01-06',
            '2026-04-03',
            '2026-05-01',
            '2026-08-15',
            '2026-10-12',
            '2026-12-08',
            '2026-12-25',
        ];
    }

    public function test_festivos_seeder_creates_example_holidays_for_each_non_principal_tenant(): void
    {
        $principalTenant = Tenant::ensurePrincipalTenant();
        $northwind = Tenant::factory()->create(['id' => 'northwind-seeder']);
        $acme = Tenant::factory()->create(['id' => 'acme-seeder']);

        $this->seed(FestivosSeeder::class);

        $this->assertSame(16, Festivo::query()->count());
        $this->assertSame(0, Festivo::query()->where('tenant_id', $principalTenant->getKey())->count());

        $this->assertSame(
            $this->expectedDates(),
            Festivo::query()->where('tenant_id', $northwind->getKey())->orderBy('date')->pluck('date')->map(fn ($date): string => substr((string) $date, 0, 10))->all(),
        );

        $this->assertSame(
            $this->expectedDates(),
            Festivo::query()->where('tenant_id', $acme->getKey())->orderBy('date')->pluck('date')->map(fn ($date): string => substr((string) $date, 0, 10))->all(),
        );
    }

    public function test_festivos_seeder_is_idempotent(): void
    {
        Tenant::ensurePrincipalTenant();
        Tenant::factory()->create(['id' => 'northwind-seeder']);

        $this->seed(FestivosSeeder::class);
        $this->seed(FestivosSeeder::class);

        $this->assertSame(8, Festivo::query()->count());
    }
}
