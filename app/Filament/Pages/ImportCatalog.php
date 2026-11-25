<?php

namespace App\Filament\Pages;

use App\Services\CatalogImporter;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ImportCatalog extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    protected static string|\UnitEnum|null $navigationGroup = 'Каталог';

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationLabel = 'Импорт каталога';

    protected static ?string $title = 'Импорт каталога из CSV';

    protected string $view = 'filament.pages.import-catalog';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public ?string $summary = null;

    /** @var list<string> */
    public array $importErrors = [];

    public function mount(): void
    {
        $this->form->fill([
            'update_prices' => true,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Файл')
                    ->description('CSV с разделителем «;» или «,», кодировка UTF-8 или Windows-1251. Обязательна колонка «Артикул» (sku).')
                    ->schema([
                        FileUpload::make('file')
                            ->label('CSV-файл')
                            ->disk('local')
                            ->directory('imports')
                            ->visibility('private')
                            ->acceptedFileTypes(['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'])
                            ->maxSize(20 * 1024)
                            ->required(),
                        Toggle::make('update_prices')
                            ->label('Записывать историю цен при изменении')
                            ->default(true),
                    ]),
                Section::make('Поддерживаемые колонки')
                    ->collapsed()
                    ->schema([
                        Text::make(
                            'обязательные: Артикул, Наименование. Необязательные: Категория (путь через «/»), Бренд, Цена, '
                            .'Старая цена, Ед., Остаток, Описание, Характеристики (Ключ=Значение через «;»), Аналоги, '
                            .'OEM, Бренд генератора, Модель генератора, Активен, GUID.'
                        ),
                    ]),
            ])
            ->statePath('data');
    }

    public function import(CatalogImporter $importer): void
    {
        $state = $this->form->getState();
        $file = $state['file'] ?? null;

        if (blank($file)) {
            Notification::make()->title('Выберите файл')->danger()->send();

            return;
        }

        $path = Storage::disk('local')->path(is_array($file) ? reset($file) : $file);

        try {
            $result = $importer->import($path, (bool) ($state['update_prices'] ?? true));
        } catch (Throwable $e) {
            Notification::make()
                ->title('Импорт не выполнен')
                ->body($e->getMessage())
                ->danger()
                ->send();

            return;
        }

        $this->summary = $result->summary();
        $this->importErrors = $result->errors;
        $this->data['file'] = null;
        $this->form->fill(['update_prices' => $state['update_prices'] ?? true]);

        Notification::make()
            ->title('Импорт завершён')
            ->body($result->summary())
            ->success()
            ->send();
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'summary' => $this->summary,
            'importErrors' => $this->importErrors,
        ];
    }
}
