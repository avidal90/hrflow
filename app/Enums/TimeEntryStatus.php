<?php

namespace App\Enums;

enum TimeEntryStatus: string
{
    case Complete = 'complete';
    case Incomplete = 'incomplete';

    public function label(): string
    {
        return match ($this) {
            self::Complete => 'Completo',
            self::Incomplete => 'Incompleto',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status): array => [$status->value => $status->label()])
            ->all();
    }
}
