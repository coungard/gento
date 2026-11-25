<?php

namespace App\Enums;

enum PartSource: string
{
    case Manual = 'manual';
    case Import = 'import';
    case OneC = '1c';

    public function label(): string
    {
        return match ($this) {
            self::Manual => 'Вручную',
            self::Import => 'Импорт',
            self::OneC => '1С',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
