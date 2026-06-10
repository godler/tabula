<?php

use App\Contracts\DatabaseServiceInterface;
use App\Enums\PerPage;
use App\Table\ColumnFactory;
use App\Table\Columns\Column;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.app')] #[Title('Tabela — Tabula')] class extends Component {
    use WithPagination;

    public string $database = '';

    public string $table = '';

    #[Url]
    public PerPage $perPage = PerPage::TwentyFive;

    private DatabaseServiceInterface $databaseService;

    public function boot(DatabaseServiceInterface $databaseService): void
    {
        $this->databaseService = $databaseService;
    }

    public function mount(string $database, string $table): void
    {
        if (! session()->has('db_host')) {
            $this->redirect(route('login'), navigate: true);

            return;
        }

        $this->database = $database;
        $this->table = $table;
    }

    /** @return array<int, Column> */
    #[Computed]
    public function columns(): array
    {
        [$host, $port, $username, $password] = $this->credentials();

        return array_map(
            fn(array $col) => ColumnFactory::make($col),
            $this->databaseService->getTableColumns($host, $port, $username, $password, $this->database, $this->table),
        );
    }

    /** @return array{rows: array<int, array<string, mixed>>, total: int} */
    #[Computed]
    public function tableData(): array
    {
        [$host, $port, $username, $password] = $this->credentials();

        return $this->databaseService->getTableData(
            $host, $port, $username, $password,
            $this->database, $this->table,
            $this->getPage(), $this->perPage->value,
        );
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
        unset($this->tableData);
    }

    public function totalPages(): int
    {
        return (int) ceil($this->tableData['total'] / $this->perPage->value);
    }

    /** @return array{string, int, string, string} */
    private function credentials(): array
    {
        return [
            session('db_host'),
            (int) session('db_port', 3306),
            session('db_username'),
            decrypt(session('db_password')),
        ];
    }
};
?>

<div class="space-y-6">
    <div>
        <div class="flex items-center gap-2 text-sm text-zinc-500 mb-1">
            <a href="{{ route('database', $database) }}" wire:navigate class="hover:text-zinc-800 dark:hover:text-zinc-200">
                {{ $database }}
            </a>
            <flux:icon name="chevron-right" class="size-3" />
            <span>{{ $table }}</span>
        </div>
        <flux:heading size="xl">{{ $table }}</flux:heading>
        <flux:text class="mt-1 text-zinc-500">
            {{ number_format($this->tableData['total'], 0, '.', ' ') }} wierszy
        </flux:text>
    </div>

    <flux:table container:class="overflow-x-auto">
        <flux:table.columns sticky class="bg-white dark:bg-zinc-900">
            @foreach ($this->columns as $column)
                <flux:table.column
                    :sticky="$column->isPrimaryKey()"
                    :class="$column->isPrimaryKey() ? 'bg-white dark:bg-zinc-900' : ''"
                >
                    <div class="flex items-center gap-1.5">
                        @if ($column->isPrimaryKey())
                            <flux:icon name="key" class="size-3 text-amber-500 shrink-0" />
                        @endif
                        <span class="font-medium">{{ $column->name }}</span>
                        <flux:badge :color="$column->badgeColor()" size="sm" class="font-mono text-[10px]">
                            {{ $column->typeLabel() }}
                        </flux:badge>
                        @if ($column->nullable)
                            <flux:badge color="zinc" size="sm" class="text-[10px]">null</flux:badge>
                        @endif
                    </div>
                </flux:table.column>
            @endforeach
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->tableData['rows'] as $index => $row)
                <flux:table.row :key="$index">
                    @foreach ($this->columns as $column)
                        <flux:table.cell
                            :sticky="$column->isPrimaryKey()"
                            :class="$column->isPrimaryKey() ? 'bg-white dark:bg-zinc-900 font-medium p-1 text-right' : ''"
                        >
                            @php $value = $row[$column->name] ?? null; @endphp
                            @if ($value === null)
                                <span class="text-zinc-400 italic text-xs">NULL</span>
                            @else
                                <span class="font-mono text-sm">{{ $column->format($value) }}</span>
                            @endif
                        </flux:table.cell>
                    @endforeach
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell :colspan="count($this->columns)">
                        <flux:text class="text-zinc-400 text-center py-4">Brak danych</flux:text>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <flux:text class="text-sm text-zinc-500">Wierszy na stronę</flux:text>
            <flux:select wire:model.change.live="perPage" size="sm" class="w-20">
                @foreach (PerPage::cases() as $case)
                    <flux:select.option :value="$case->value">{{ $case->label() }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        @if ($this->totalPages() > 1)
            <div class="flex items-center gap-3">
                <flux:text class="text-sm text-zinc-500">
                    Strona {{ $this->getPage() }} z {{ $this->totalPages() }}
                </flux:text>

                <div class="flex items-center gap-1">
                    <flux:button
                        size="sm"
                        variant="ghost"
                        :disabled="$this->getPage() <= 1"
                        wire:click="previousPage"
                        icon="chevron-left"
                    />

                    <flux:button
                        size="sm"
                        variant="ghost"
                        :disabled="$this->getPage() >= $this->totalPages()"
                        wire:click="nextPage"
                        icon="chevron-right"
                    />
                </div>
            </div>
        @endif
    </div>
</div>
