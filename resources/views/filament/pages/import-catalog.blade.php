<x-filament-panels::page>
    <form wire:submit="import" class="space-y-6">
        {{ $this->form }}

        <div class="flex items-center gap-3">
            <x-filament::button type="submit" icon="heroicon-o-arrow-up-tray">
                Импортировать
            </x-filament::button>

            <x-filament::loading-indicator wire:loading class="h-5 w-5" />
        </div>
    </form>

    @if ($summary)
        <x-filament::section heading="Результат последнего импорта" class="mt-6">
            <p class="text-sm text-gray-950 dark:text-white">{{ $summary }}</p>

            @if ($importErrors !== [])
                <div class="mt-4">
                    <p class="text-sm font-medium text-danger-600 dark:text-danger-400">
                        Ошибки ({{ count($importErrors) }}):
                    </p>
                    <ul class="mt-2 max-h-64 overflow-auto text-sm text-gray-600 dark:text-gray-400">
                        @foreach (array_slice($importErrors, 0, 100) as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </x-filament::section>
    @endif
</x-filament-panels::page>
