<?php

namespace App\Support;

/**
 * Результат импорта каталога.
 */
class ImportResult
{
    public int $created = 0;

    public int $updated = 0;

    public int $skipped = 0;

    /** @var list<string> */
    public array $errors = [];

    /** @var list<string> */
    public array $warnings = [];

    public function total(): int
    {
        return $this->created + $this->updated + $this->skipped;
    }

    public function hasErrors(): bool
    {
        return $this->errors !== [];
    }

    public function summary(): string
    {
        $parts = [
            "создано: {$this->created}",
            "обновлено: {$this->updated}",
            "пропущено: {$this->skipped}",
        ];

        if ($this->errors !== []) {
            $parts[] = 'ошибок: '.count($this->errors);
        }

        return implode(', ', $parts);
    }
}
