<?php

namespace App\Enums;

enum DocumentFolder: string
{
    case Payrolls = 'nominas';
    case Contracts = 'contratos';
    case Policies = 'normativas';
    case Other = 'otros';

    public function label(): string
    {
        return match ($this) {
            self::Payrolls => 'Nominas',
            self::Contracts => 'Contratos',
            self::Policies => 'Normativas',
            self::Other => 'Otros',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $folder) {
            $options[$folder->value] = $folder->label();
        }

        return $options;
    }
}
