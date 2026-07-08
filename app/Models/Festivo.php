<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Policies\FestivoPolicy;
use Database\Factories\FestivoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property Carbon $date
 */
#[Fillable(['tenant_id', 'date'])]
#[UsePolicy(FestivoPolicy::class)]
class Festivo extends Model
{
    /** @use HasFactory<FestivoFactory> */
    use BelongsToTenant, HasFactory;

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    /** @return BelongsTo<Tenant, $this> */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
