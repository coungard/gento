<?php

namespace Tests\Feature;

use App\Enums\PartSource;
use App\Models\Category;
use App\Models\Part;
use App\Services\CatalogImporter;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class CatalogImporterTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function fixture(string $name): string
    {
        return base_path('tests/Fixtures/'.$name);
    }

    private function importer(): CatalogImporter
    {
        return app(CatalogImporter::class);
    }

    public function test_import_creates_parts_with_category_brand_and_price(): void
    {
        $result = $this->importer()->import($this->fixture('parts-utf8.csv'));

        $this->assertSame(2, $result->created);
        $this->assertSame(0, $result->updated);
        $this->assertFalse($result->hasErrors(), implode(' | ', $result->errors));

        $part = Part::query()->where('sku', 'FLT-OIL-777')->sole();

        $this->assertSame('Фильтр масляный Cummins', $part->name);
        $this->assertSame('Cummins', $part->brand);
        $this->assertEqualsWithDelta(1850.50, (float) $part->price, 0.001);
        $this->assertSame(12, $part->stock);
        $this->assertSame(PartSource::Import, $part->source);
        $this->assertSame(1, $part->priceHistories()->count());
    }

    public function test_import_builds_nested_category_path(): void
    {
        $this->importer()->import($this->fixture('parts-utf8.csv'));

        $child = Category::query()->where('name', 'Масляные фильтры')->sole();

        $this->assertSame('maslianye-filtry', $child->slug);
        $this->assertNotNull($child->parent);
        $this->assertSame('Фильтры', $child->parent->name);
    }

    public function test_import_links_cross_numbers_and_oem_numbers(): void
    {
        $this->importer()->import($this->fixture('parts-utf8.csv'));

        $part = Part::query()->where('sku', 'FLT-OIL-777')->sole();

        $this->assertEqualsCanonicalizing(
            ['3401544', 'LF-3345'],
            $part->crossNumbers()->pluck('number')->all(),
        );

        $this->assertSame('oem', $part->crossNumbers()->where('number', '3401544')->value('kind'));
        $this->assertSame('analog', $part->crossNumbers()->where('number', 'LF-3345')->value('kind'));
    }

    public function test_import_attaches_generator_model_with_brand(): void
    {
        $this->importer()->import($this->fixture('parts-utf8.csv'));

        $part = Part::query()->where('sku', 'FLT-OIL-777')->sole();
        $model = $part->generatorModels()->with('brand')->sole();

        $this->assertSame('Cummins QSB 6.7', $model->name);
        $this->assertSame('Cummins', $model->brand->name);
    }

    public function test_repeated_import_updates_parts_without_duplicates(): void
    {
        $this->importer()->import($this->fixture('parts-utf8.csv'));
        $second = $this->importer()->import($this->fixture('parts-utf8.csv'));

        $this->assertSame(0, $second->created);
        $this->assertSame(2, $second->updated);
        $this->assertSame(2, Part::query()->count());
        $this->assertSame(3, Category::query()->count());
        $this->assertSame(2, Part::query()->where('sku', 'FLT-OIL-777')->sole()->crossNumbers()->count());
        $this->assertSame(1, Part::query()->where('sku', 'FLT-OIL-777')->sole()->priceHistories()->count());
    }

    public function test_price_change_is_stored_with_history(): void
    {
        $importer = $this->importer();
        $importer->import($this->fixture('parts-utf8.csv'));
        $importer->import($this->fixture('parts-utf8-price-changed.csv'));

        $part = Part::query()->where('sku', 'FLT-OIL-777')->sole();

        $this->assertEqualsWithDelta(2100.0, (float) $part->price, 0.001);
        $this->assertSame(2, $part->priceHistories()->count());

        $latest = $part->priceHistories()->orderByDesc('id')->firstOrFail();

        $this->assertEqualsWithDelta(2100.0, (float) $latest->price, 0.001);
        $this->assertEqualsWithDelta(1850.50, (float) $latest->old_price, 0.001);
    }

    public function test_import_reads_windows_1251_encoded_file(): void
    {
        $result = $this->importer()->import($this->fixture('parts-cp1251.csv'));

        $this->assertSame(1, $result->created);

        $part = Part::query()->where('sku', 'CP-001')->sole();

        $this->assertSame('Фильтр топливный', $part->name);
        $this->assertEqualsWithDelta(3300.0, (float) $part->price, 0.001);
    }

    public function test_import_without_sku_column_fails_with_explanatory_message(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('нет колонки с артикулом');

        $this->importer()->import($this->fixture('parts-no-sku-header.csv'));
    }

    public function test_import_reports_invalid_row_and_keeps_valid_ones(): void
    {
        $result = $this->importer()->import($this->fixture('parts-with-invalid-row.csv'));

        $this->assertSame(1, $result->created);
        $this->assertTrue($result->hasErrors());
        $this->assertCount(1, $result->errors);
        $this->assertStringContainsString('Строка 3', $result->errors[0]);
        $this->assertSame(1, Part::query()->count());
        $this->assertSame('OK-001', Part::query()->sole()->sku);
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function moneyProvider(): array
    {
        return [
            'пробелы и запятая' => ['1 850,50', '1850.50'],
            'неразрывный пробел' => ["2\u{00A0}490", '2490.00'],
            'знак рубля' => ['3 300 ₽', '3300.00'],
            'обозначение рублей' => ['2 490 руб.', '2490.00'],
        ];
    }

    #[DataProvider('moneyProvider')]
    public function test_import_parses_russian_price_formats(string $raw, string $expected): void
    {
        $path = $this->csvFile("Артикул;Наименование;Цена\nPRICE-01;Позиция;{$raw}\n");

        $this->importer()->import($path);

        $this->assertSame($expected, Part::query()->where('sku', 'PRICE-01')->sole()->price);
    }

    /**
     * Создаёт временный CSV внутри рабочей директории приложения.
     */
    private function csvFile(string $contents): string
    {
        $directory = storage_path('framework/testing');

        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $path = $directory.'/import-'.uniqid().'.csv';
        file_put_contents($path, $contents);

        return $path;
    }
}
