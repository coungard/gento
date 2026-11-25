<?php

namespace Database\Seeders;

use App\Enums\LeadStatus;
use App\Enums\PartSource;
use App\Models\Brand;
use App\Models\Category;
use App\Models\GeneratorModel;
use App\Models\Page;
use App\Models\Part;
use App\Models\PartCrossNumber;
use App\Models\PartPriceHistory;
use App\Models\QuoteRequest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = $this->seedCategories();
        $brands = $this->seedBrands();
        $models = $this->seedGeneratorModels($brands);

        $this->seedParts($categories, $models);
        $this->seedPages();
        $this->seedQuoteRequests();
    }

    /**
     * @return array<string, Category>
     */
    private function seedCategories(): array
    {
        $tree = [
            'filters' => ['Фильтры', null, ['Масляные фильтры', 'Топливные фильтры', 'Воздушные фильтры']],
            'kits' => ['Ремкомплекты', null, ['Прокладки и сальники', 'Ремкомплекты двигателя']],
            'electric' => ['Электрика', null, ['Щётки и регуляторы', 'Датчики и реле']],
            'fuel' => ['Топливная система', null, ['Форсунки', 'Топливные насосы']],
        ];

        $result = [];

        foreach ($tree as $slug => [$name, $parent, $children]) {
            $category = Category::query()->updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'parent_id' => null, 'is_active' => true, 'sort' => count($result)],
            );
            $result[$slug] = $category;

            foreach ($children as $index => $childName) {
                $childSlug = Str::slug($childName) ?: $slug.'-'.$index;
                $result[$childSlug] = Category::query()->updateOrCreate(
                    ['slug' => $childSlug],
                    [
                        'name' => $childName,
                        'parent_id' => $category->id,
                        'is_active' => true,
                        'sort' => $index,
                    ],
                );
            }
        }

        return $result;
    }

    /**
     * @return array<string, Brand>
     */
    private function seedBrands(): array
    {
        $brands = [
            'cummins' => ['Cummins', 'США'],
            'perkins' => ['Perkins', 'Великобритания'],
            'yanmar' => ['Yanmar', 'Япония'],
            'sdmo' => ['SDMO', 'Франция'],
        ];

        $result = [];

        foreach ($brands as $slug => [$name, $country]) {
            $result[$slug] = Brand::query()->updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'country' => $country],
            );
        }

        return $result;
    }

    /**
     * @param  array<string, Brand>  $brands
     * @return array<string, GeneratorModel>
     */
    private function seedGeneratorModels(array $brands): array
    {
        $models = [
            'cummins-c150d5' => ['cummins', 'C150D5', 150, 'Cummins 6CTA8.3-G2'],
            'cummins-c275d5' => ['cummins', 'C275D5', 275, 'Cummins NTA855-G1'],
            'perkins-1104a-44tg2' => ['perkins', '1104A-44TG2', 80, 'Perkins 1104A-44TG2'],
            'yanmar-yeg-25' => ['yanmar', 'YEG-25', 25, 'Yanmar 4TNV88'],
            'sdmo-j110k' => ['sdmo', 'J110K', 110, 'John Deere 4045TFM50'],
        ];

        $result = [];

        foreach ($models as $slug => [$brand, $name, $power, $engine]) {
            $result[$slug] = GeneratorModel::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'brand_id' => $brands[$brand]->id,
                    'name' => $name,
                    'power_kva' => $power,
                    'engine' => $engine,
                ],
            );
        }

        return $result;
    }

    /**
     * @param  array<string, Category>  $categories
     * @param  array<string, GeneratorModel>  $models
     */
    private function seedParts(array $categories, array $models): void
    {
        $parts = [
            ['FLT-OIL-001', 'Фильтр масляный Cummins', 'масляные-фильтры', 1850, 12, ['cummins-c150d5', 'cummins-c275d5'], ['Cummins 3401544']],
            ['FLT-OIL-002', 'Фильтр масляный Perkins', 'масляные-фильтры', 1420, 8, ['perkins-1104a-44tg2'], ['Perkins 2654403']],
            ['FLT-FUEL-001', 'Фильтр топливный Cummins', 'топливные-фильтры', 2100, 6, ['cummins-c150d5'], ['Cummins 3931063']],
            ['FLT-FUEL-002', 'Фильтр топливный Yanmar', 'топливные-фильтры', 990, 20, ['yanmar-yeg-25'], ['Yanmar 129A00-55600']],
            ['FLT-AIR-001', 'Фильтр воздушный Cummins', 'воздушные-фильтры', 3400, 4, ['cummins-c150d5', 'cummins-c275d5'], []],
            ['FLT-AIR-002', 'Фильтр воздушный SDMO', 'воздушные-фильтры', 2750, 0, ['sdmo-j110k'], []],
            ['KIT-GSK-001', 'Прокладка ГБЦ Cummins', 'прокладки-и-сальники', 5600, 3, ['cummins-c150d5'], []],
            ['KIT-GSK-002', 'Сальник коленвала Perkins', 'прокладки-и-сальники', 1850, 10, ['perkins-1104a-44tg2'], []],
            ['KIT-ENG-001', 'Ремкомплект двигателя Yanmar', 'ремкомплекты-двигателя', 24500, 1, ['yanmar-yeg-25'], []],
            ['EL-BR-001', 'Щётки генератора SDMO', 'щётки-и-регуляторы', 3200, 15, ['sdmo-j110k'], []],
            ['EL-AVR-001', 'Регулятор напряжения AVR', 'щётки-и-регуляторы', 8700, 5, ['cummins-c150d5', 'sdmo-j110k'], []],
            ['EL-SNS-001', 'Датчик давления масла', 'датчики-и-реле', 2400, 9, [], []],
            ['EL-RLY-001', 'Реле стартера 24В', 'датчики-и-реле', 1500, 18, [], []],
            ['FUEL-INJ-001', 'Форсунка Cummins', 'форсунки', 18900, 2, ['cummins-c275d5'], ['Cummins 3077715']],
            ['FUEL-INJ-002', 'Форсунка Perkins', 'форсунки', 12500, 4, ['perkins-1104a-44tg2'], []],
            ['FUEL-PMP-001', 'ТНВД Cummins', 'топливные-насосы', 96500, 0, ['cummins-c275d5'], []],
        ];

        foreach ($parts as $index => [$sku, $name, $categorySlug, $price, $stock, $modelSlugs, $oem]) {
            $category = $categories[$categorySlug] ?? null;

            $part = Part::query()->updateOrCreate(
                ['sku' => $sku],
                [
                    'category_id' => $category?->id,
                    'name' => $name,
                    'slug' => Str::slug($name).'-'.Str::lower($sku),
                    'source' => PartSource::Manual,
                    'brand' => 'OEM',
                    'description' => $name.' для генераторных установок. Оригинальная или совместимая деталь.',
                    'specs' => ['Производитель' => 'OEM', 'Гарантия' => '12 мес.'],
                    'price' => $price,
                    'old_price' => $index % 4 === 0 ? round($price * 1.2, 2) : null,
                    'currency' => 'RUB',
                    'unit' => 'шт',
                    'stock' => $stock,
                    'is_active' => true,
                    'is_featured' => $index < 4,
                ],
            );

            PartPriceHistory::query()->create([
                'part_id' => $part->id,
                'price' => $part->price,
                'old_price' => $part->old_price,
                'source' => 'seed',
                'recorded_at' => now(),
            ]);

            foreach ($oem as $number) {
                PartCrossNumber::query()->firstOrCreate(
                    ['part_id' => $part->id, 'number' => $number],
                    ['brand' => 'OEM', 'kind' => 'oem'],
                );
            }

            foreach ($modelSlugs as $modelSlug) {
                $part->generatorModels()->syncWithoutDetaching([$models[$modelSlug]->id]);
            }
        }
    }

    private function seedPages(): void
    {
        $pages = [
            ['about', 'О компании', 'Продажа и сервис генераторов, поставка запчастей.'],
            ['delivery', 'Доставка и оплата', 'Доставляем по всей России, работаем с юридическими и физическими лицами.'],
            ['warranty', 'Гарантия', 'Гарантия на оборудование и запчасти, сервисное обслуживание.'],
            ['contacts', 'Контакты', 'Свяжитесь с нами удобным способом.'],
        ];

        foreach ($pages as $index => [$slug, $title, $excerpt]) {
            Page::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $title,
                    'excerpt' => $excerpt,
                    'body' => '<p>'.$excerpt.'</p><p>Раздел находится в разработке.</p>',
                    'is_active' => true,
                    'sort' => $index,
                    'published_at' => now(),
                ],
            );
        }
    }

    private function seedQuoteRequests(): void
    {
        if (QuoteRequest::query()->exists()) {
            return;
        }

        $part = Part::query()->first();

        if ($part === null) {
            return;
        }

        $request = QuoteRequest::query()->create([
            'name' => 'Иван Петров',
            'phone' => '+79990001122',
            'email' => 'ivan@example.com',
            'company' => 'ООО «Энергосервис»',
            'comment' => 'Нужны фильтры на ТО, 2 комплекта.',
            'status' => LeadStatus::New,
            'source' => 'site',
        ]);

        $request->items()->create([
            'part_id' => $part->id,
            'sku' => $part->sku,
            'name' => $part->name,
            'price' => $part->price,
            'qty' => 2,
        ]);

        $request->recalculateTotal();

        QuoteRequest::factory()->inProgress()->withItems()->create();
    }
}
