<?php

use App\Contracts\DatabaseServiceInterface;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    /** @var array<int, string> */
    public array $databases = [];

    /** @var array<int, string> */
    public array $tables = [];

    public string $host = '';

    public string $username = '';

    public string $search = '';

    public ?string $selectedDatabase = null;

    public ?string $selectedTable = null;

    private DatabaseServiceInterface $databaseService;

    public function boot(DatabaseServiceInterface $databaseService): void
    {
        $this->databaseService = $databaseService;
    }

    public function mount(): void
    {
        if (! session()->has('db_host')) {
            return;
        }

        $this->host = session('db_host', '');
        $this->username = session('db_username', '');
        $this->selectedDatabase = request()->route('database');
        $this->selectedTable = request()->route('table');

        $this->loadDatabases();

        if ($this->selectedDatabase !== null) {
            $this->loadTables($this->selectedDatabase);
        }
    }

    /** @return array<int, string> */
    #[Computed]
    public function filteredDatabases(): array
    {
        if ($this->search === '') {
            return $this->databases;
        }

        return array_values(
            array_filter(
                $this->databases,
                fn(string $database) => str_contains(strtolower($database), strtolower($this->search)),
            ),
        );
    }

    #[On('database-page-loaded')]
    public function onDatabasePageLoaded(?string $database): void
    {
        $this->selectedDatabase = $database;
        $this->tables = $database !== null ? $this->loadTables($database) : [];
    }

    private function loadDatabases(): void
    {
        $this->databases = $this->databaseService->getDatabases(
            session('db_host'),
            (int) session('db_port', 3306),
            session('db_username'),
            decrypt(session('db_password')),
        );
    }

    private function loadTables(string $database): array
    {
        return $this->tables = $this->databaseService->getTables(
            session('db_host'),
            (int) session('db_port', 3306),
            session('db_username'),
            decrypt(session('db_password')),
            $database,
        );
    }

    public function disconnect(): void
    {
        session()->forget(['db_host', 'db_port', 'db_username', 'db_password']);

        $this->redirect(route('login'), navigate: true);
    }
};
?>
<div class="flex h-full flex-col">
    <flux:sidebar.header>
        <div class="flex items-center gap-2">
            <flux:heading class="font-bold">Tabula</flux:heading>
            <flux:badge color="zinc" size="sm">{{ $host }}</flux:badge>
        </div>
        <flux:sidebar.collapse class="lg:hidden" />
    </flux:sidebar.header>

    <flux:sidebar.search placeholder="Szukaj bazy..." wire:model.live="search" />

    <flux:sidebar.nav>
        @forelse ($this->filteredDatabases as $database)
            @if ($database === $selectedDatabase && count($tables) > 0)
                <flux:sidebar.group
                    expandable
                    icon="circle-stack"
                    :heading="$database"
                    class="grid"
                >
                    @foreach ($tables as $table)
                        <flux:sidebar.item
                            icon="table-cells"
                            href="{{ route('table', [$selectedDatabase, $table]) }}"
                            :current="$selectedTable === $table"
                            wire:navigate
                            wire:key="{{ $selectedDatabase }}-{{ $table }}"
                        >
                            {{ $table }}
                        </flux:sidebar.item>
                    @endforeach
                </flux:sidebar.group>
            @else
                <flux:sidebar.item
                    icon="circle-stack"
                    href="{{ route('database', $database) }}"
                    :current="$selectedDatabase === $database"
                    wire:navigate
                    wire:key="{{ $database }}"
                >
                    {{ $database }}
                </flux:sidebar.item>
            @endif
        @empty
            <flux:text class="px-2 py-1 text-sm text-zinc-400">Brak wyników</flux:text>
        @endforelse
    </flux:sidebar.nav>

    <flux:sidebar.spacer />

    <flux:sidebar.nav>
        <flux:sidebar.item icon="arrow-right-start-on-rectangle" wire:click="disconnect">
            Rozłącz ({{ $username }})
        </flux:sidebar.item>
    </flux:sidebar.nav>

</div>
