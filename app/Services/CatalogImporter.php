<?php

namespace App\Services;

use App\Enums\PartSource;
use App\Models\Brand;
use App\Models\Category;
use App\Models\GeneratorModel;
use App\Models\Part;
use App\Models\PartCrossNumber;
use App\Models\PartPriceHistory;
use App\Support\ImportResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Импорт каталога запчастей из CSV (разделитель «;» или «,», кодировка UTF-8 или Windows-1251).
 *
 * Инварианты импорта:
 *  - запчасть ищется по external_id (если задан) или по sku, повторный импорт не создаёт дублей;
 *  - категории, бренды и модели генераторов создаются автоматически;
 *  - при изменении цены пишется запись в part_price_histories (цена «до» и «после»);
 *  - непригодная строка не рвёт импорт: ошибка копится в ImportResult::$errors.
 */
class CatalogImporter
{
    /** Синонимы заголовков колонок (в нижнем регистре). */
    public const ALIASES = [
        'sku' => ['sku', 'артикул', 'код', 'code', 'article', 'part_number', 'номер'],
        'external_id' => ['external_id', 'guid', 'uuid', 'ид', 'ид 1с'],
        'name' => ['name', 'наименование', 'название', 'товар', 'title'],
        'category' => ['category', 'категория', 'раздел', 'группа', 'вид'],
        'brand' => ['brand', 'бренд', 'производитель', 'vendor', 'марка'],
        'price' => ['price', 'цена', 'цена, руб', 'цена руб', 'стоимость'],
        'old_price' => ['old_price', 'старая цена', 'цена до скидки'],
        'currency' => ['currency', 'валюта'],
        'unit' => ['unit', 'ед', 'ед.', 'единица', 'ед. изм', 'ед. изм.'],
        'stock' => ['stock', 'остаток', 'количество', 'кол-во', 'кол-во, шт'],
        'description' => ['description', 'описание'],
        'specs' => ['specs', 'характеристики', 'параметры'],
        'cross_numbers' => ['cross_numbers', 'кросс', 'аналоги', 'кросс-номера', 'аналог', 'номера аналогов'],
        'oem_numbers' => ['oem', 'oem_numbers', 'оригинальный номер', 'oem-номер'],
        'generator_brand' => ['generator_brand', 'бренд генератора', 'генератор бренд'],
        'generator_model' => ['generator_model', 'модель генератора', 'генератор', 'модель'],
        'is_active' => ['is_active', 'активен', 'опубликовано', 'в наличии на сайте'],
    ];

    /** Предел количества кросс-номеров на одну запчасть (защита от мусорных файлов). */
    private const MAX_CROSS_NUMBERS = 50;

    public function import(string $path, bool $updatePrices = true, bool $deactivateMissing = false): ImportResult
    {
        if (! is_file($path) || ! is_readable($path)) {
            throw new RuntimeException("Файл импорта не найден: {$path}");
        }

        $result = new ImportResult();
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new RuntimeException("Не удалось открыть файл: {$path}");
        }

        try {
            $headerLine = fgets($handle);
            if ($headerLine === false) {
                throw new RuntimeException('Файл пустой: нет строки заголовков.');
            }

            $delimiter = $this->detectDelimiter($headerLine);
            $headers = $this->mapHeaders($this->parseLine($headerLine, $delimiter));

            if (! in_array('sku', $headers, true)) {
                throw new RuntimeException('В заголовках нет колонки с артикулом (sku / Артикул).');
            }

            $lineNumber = 1;

            while (($line = fgets($handle)) !== false) {
                $lineNumber++;

                if (trim($line) === '') {
                    continue;
                }

                $cells = $this->parseLine($line, $delimiter);
                /** @var array<string, string> $row */
                $row = [];

                foreach ($headers as $index => $key) {
                    if ($key === null) {
                        continue;
                    }

                    $row[$key] = trim($cells[$index] ?? '');
                }

                if (($row['sku'] ?? '') === '' && ($row['name'] ?? '') === '') {
                    $result->skipped++;

                    continue;
                }

                try {
                    $created = $this->importRow($row, $updatePrices);
                    $created ? $result->created++ : $result->updated++;
                } catch (\Throwable $e) {
                    $result->errors[] = "Строка {$lineNumber}: {$e->getMessage()}";
                }
            }

            if ($deactivateMissing) {
                $this->deactivateMissingParts($result);
            }
        } finally {
            fclose($handle);
        }

        return $result;
    }

    /**
     * @param  array<string, string>  $row
     * @return bool true — запчасть создана, false — обновлена
     */
    private function importRow(array $row, bool $updatePrices): bool
    {
        $sku = $row['sku'] ?? '';
        $name = $row['name'] ?? '';

        if ($sku === '') {
            throw new RuntimeException('не указан артикул');
        }

        if ($name === '') {
            throw new RuntimeException("не указано наименование для артикула {$sku}");
        }

        return DB::transaction(function () use ($row, $sku, $name, $updatePrices): bool {
            $externalId = $row['external_id'] ?? null;

            $part = Part::withTrashed()
                ->when(filled($externalId), fn ($q) => $q->where('external_id', $externalId))
                ->when(blank($externalId), fn ($q) => $q->where('sku', $sku))
                ->first();

            $created = $part === null;
            $price = $this->parseMoney($row['price'] ?? null);

            if ($created) {
                $part = new Part();
                $part->source = PartSource::Import;
            }

            $previousPrice = $part->exists ? $part->price : null;

            $part->fill([
                'sku' => $sku,
                'external_id' => filled($externalId) ? $externalId : $part->external_id,
                'name' => $name,
                'slug' => $part->exists ? $part->slug : $this->uniqueSlug($name, $sku),
                'brand' => filled($row['brand'] ?? null) ? $row['brand'] : $part->brand,
                'description' => filled($row['description'] ?? null) ? $row['description'] : $part->description,
                'specs' => $this->parseSpecs($row['specs'] ?? null) ?: $part->specs,
                'price' => $price ?? $part->price ?? 0,
                'old_price' => $this->parseMoney($row['old_price'] ?? null) ?? $part->old_price,
                'currency' => filled($row['currency'] ?? null) ? mb_strtoupper($row['currency']) : ($part->currency ?: 'RUB'),
                'unit' => filled($row['unit'] ?? null) ? $row['unit'] : ($part->unit ?: 'шт'),
                'stock' => $this->parseInt($row['stock'] ?? null) ?? $part->stock ?? 0,
                'is_active' => $this->parseBool($row['is_active'] ?? null) ?? $part->is_active ?? true,
                'category_id' => $this->resolveCategory($row['category'] ?? null) ?? $part->category_id,
            ]);

            if ($part->trashed()) {
                $part->restore();
            }

            $part->save();

            if ($created || ($updatePrices && $previousPrice !== null && (float) $previousPrice !== (float) $part->price)) {
                PartPriceHistory::query()->create([
                    'part_id' => $part->id,
                    'price' => $part->price,
                    'old_price' => $previousPrice,
                    'source' => PartSource::Import->value,
                    'recorded_at' => now(),
                ]);
            }

            $this->syncCrossNumbers($part, $row);
            $this->syncGeneratorModels($part, $row);

            return $created;
        });
    }

    private function resolveCategory(?string $path): ?int
    {
        if (blank($path)) {
            return null;
        }

        $parentId = null;
        $segments = array_values(array_filter(array_map('trim', preg_split('#[/>|]+#', $path) ?: [])));

        foreach ($segments as $segment) {
            $slug = Str::slug($segment) ?: Str::random(8);

            $category = Category::query()
                ->where('name', $segment)
                ->orWhere('slug', $slug)
                ->first();

            if ($category === null) {
                $category = Category::query()->create([
                    'name' => $segment,
                    'slug' => $this->uniqueCategorySlug($slug),
                    'parent_id' => $parentId,
                    'is_active' => true,
                ]);
            }

            $parentId = $category->id;
        }

        return $parentId;
    }

    /**
     * @param  array<string, string>  $row
     */
    private function syncCrossNumbers(Part $part, array $row): void
    {
        $oem = $this->splitList($row['oem_numbers'] ?? null);
        $analog = $this->splitList($row['cross_numbers'] ?? null);

        if ($oem === [] && $analog === []) {
            return;
        }

        $numbers = [
            ...array_map(fn (string $n) => ['number' => $n, 'kind' => 'oem'], $oem),
            ...array_map(fn (string $n) => ['number' => $n, 'kind' => 'analog'], $analog),
        ];

        foreach (array_slice($numbers, 0, self::MAX_CROSS_NUMBERS) as $item) {
            PartCrossNumber::query()->firstOrCreate(
                ['part_id' => $part->id, 'number' => $item['number']],
                ['kind' => $item['kind'], 'brand' => $part->brand],
            );
        }
    }

    /**
     * @param  array<string, string>  $row
     */
    private function syncGeneratorModels(Part $part, array $row): void
    {
        $brandName = trim($row['generator_brand'] ?? '');
        $modelName = trim($row['generator_model'] ?? '');

        if ($brandName === '' || $modelName === '') {
            return;
        }

        $brand = Brand::query()->firstOrCreate(
            ['slug' => Str::slug($brandName) ?: Str::lower($brandName)],
            ['name' => $brandName],
        );

        $model = GeneratorModel::query()->firstOrCreate(
            ['brand_id' => $brand->id, 'slug' => Str::slug($modelName) ?: Str::lower($modelName)],
            ['name' => $modelName],
        );

        $part->generatorModels()->syncWithoutDetaching([$model->id]);
    }

    private function deactivateMissingParts(ImportResult $result): void
    {
        $result->warnings[] = 'Снятие с публикации отсутствующих позиций не поддержано в этой версии импортёра.';
    }

    /**
     * @param  list<string>  $rawHeaders
     * @return array<int, string|null> индекс колонки → ключ
     */
    private function mapHeaders(array $rawHeaders): array
    {
        $lookup = [];

        foreach (self::ALIASES as $key => $aliases) {
            foreach ($aliases as $alias) {
                $lookup[$this->normalizeHeader($alias)] = $key;
            }
        }

        $map = [];

        foreach ($rawHeaders as $index => $header) {
            $map[$index] = $lookup[$this->normalizeHeader($header)] ?? null;
        }

        return $map;
    }

    private function normalizeHeader(string $header): string
    {
        $header = $this->toUtf8($header);
        $header = preg_replace('/^\x{FEFF}/u', '', $header) ?? $header;

        return Str::lower(trim($header));
    }

    /**
     * @return list<string>
     */
    private function parseLine(string $line, string $delimiter): array
    {
        $line = $this->toUtf8($line);
        $cells = str_getcsv($line, $delimiter, '"', '\\');

        return array_map(fn ($cell) => trim((string) $cell), $cells);
    }

    private function detectDelimiter(string $line): string
    {
        return substr_count($line, ';') >= substr_count($line, ',') ? ';' : ',';
    }

    private function toUtf8(string $value): string
    {
        if (mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }

        $converted = @mb_convert_encoding($value, 'UTF-8', 'Windows-1251');

        return $converted === false ? $value : $converted;
    }

    /**
     * @return list<string>
     */
    private function splitList(?string $value): array
    {
        if (blank($value)) {
            return [];
        }

        $items = preg_split('#[;,\n|]+#', $value) ?: [];

        return array_values(array_unique(array_filter(array_map('trim', $items))));
    }

    /**
     * @return array<string, string>|null
     */
    private function parseSpecs(?string $value): ?array
    {
        if (blank($value)) {
            return null;
        }

        $specs = [];

        foreach (preg_split('#[;\n]+#', $value) ?: [] as $pair) {
            if (! str_contains($pair, '=') && ! str_contains($pair, ':')) {
                continue;
            }

            [$key, $val] = preg_split('#[=:]#', $pair, 2) ?: [];
            $key = trim((string) $key);

            if ($key === '') {
                continue;
            }

            $specs[$key] = trim((string) $val);
        }

        return $specs === [] ? null : $specs;
    }

    private function parseMoney(?string $value): ?float
    {
        if (blank($value)) {
            return null;
        }

        $clean = str_replace(["\u{00A0}", ' ', '₽', 'руб.', 'руб', 'р.'], '', $value);
        $clean = str_replace(',', '.', $clean);

        return is_numeric($clean) ? (float) $clean : null;
    }

    private function parseInt(?string $value): ?int
    {
        if (blank($value)) {
            return null;
        }

        $clean = preg_replace('/[^\d-]/', '', $value);

        return $clean === null || $clean === '' ? null : (int) $clean;
    }

    private function parseBool(?string $value): ?bool
    {
        if (blank($value)) {
            return null;
        }

        return in_array(Str::lower(trim($value)), ['1', 'да', 'yes', 'true', 'y', '+', 'есть'], true);
    }

    private function uniqueSlug(string $name, string $sku): string
    {
        $base = Str::slug($name);

        if ($base === '') {
            $base = Str::slug($sku) ?: 'part';
        }

        $slug = $base;
        $i = 1;

        while (Part::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    private function uniqueCategorySlug(string $slug): string
    {
        $unique = $slug;
        $i = 1;

        while (Category::query()->where('slug', $unique)->exists()) {
            $unique = $slug.'-'.$i++;
        }

        return $unique;
    }
}
