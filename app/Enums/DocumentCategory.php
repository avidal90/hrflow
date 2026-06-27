<?php

namespace App\Enums;

enum DocumentCategory: string
{
    case Contract = 'contract';
    case Payslip = 'payslip';
    case Certificate = 'certificate';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Contract => 'Contrato',
            self::Payslip => 'Nomina',
            self::Certificate => 'Certificado',
            self::Other => 'Otro',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $category): array => [$category->value => $category->label()])
            ->all();
    }
}
