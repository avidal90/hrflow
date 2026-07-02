<?php

namespace App\Enums;

enum LeaveRequestType: string
{
    case Vacation = 'vacation';
    case PaidLeave = 'paid_leave';

    public function label(): string
    {
        return match ($this) {
            self::Vacation => 'Vacaciones',
            self::PaidLeave => 'Permiso retribuido',
        };
    }

    public function isVacation(): bool
    {
        return $this === self::Vacation;
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
