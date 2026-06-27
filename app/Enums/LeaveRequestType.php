<?php

namespace App\Enums;

enum LeaveRequestType: string
{
    case Vacation = 'vacation';

    public function label(): string
    {
        return match ($this) {
            self::Vacation => 'Vacaciones',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type): array => [$type->value => $type->label()])
            ->all();
    }
}
