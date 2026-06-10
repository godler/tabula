<?php

use App\Contracts\DatabaseServiceInterface;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('components.layouts.app')] #[Title('Baza danych — Tabula')] class extends Component {
    public string $database = '';

    /** @var array{charset: string, collation: string, total_size: int, table_count: int} */
    public array $stats = [];

    /** @var array<int, string> */
    public array $tables = [];

    private DatabaseServiceInterface $databaseService;

    public function boot(DatabaseServiceInterface $databaseService): void
    {
        $this->databaseService = $databaseService;
    }

    public function mount(string $database): void
    {
        if (! session()->has('db_host')) {
            $this->redirect(route('login'), navigate: true);

            return;
        }

        $this->database = $database;

        [$host, $port, $username, $password] = $this->credentials();

        $this->stats = $this->databaseService->getDatabaseStats($host, $port, $username, $password, $database);
        $this->tables = $this->databaseService->getTables($host, $port, $username, $password, $database);

        $this->dispatch('database-page-loaded', database: $database);
    }

    public function formatBytes(int $bytes): string
    {
        if ($bytes === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $exp = min((int) floor(log($bytes, 1024)), count($units) - 1);

        return round($bytes / 1024 ** $exp, 1) . ' ' . $units[$exp];
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

    <div class="space-y-8">
        <div>
            <flux:heading size="xl">{{ $database }}</flux:heading>
            <flux:text class="mt-1 text-zinc-500">Baza danych MySQL</flux:text>
        </div>

        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <flux:card>
                <flux:text class="text-sm text-zinc-500">Tabele</flux:text>
                <flux:heading size="xl" class="mt-1">{{ $stats['table_count'] }}</flux:heading>
            </flux:card>

            <flux:card>
                <flux:text class="text-sm text-zinc-500">Rozmiar</flux:text>
                <flux:heading size="xl" class="mt-1">{{ $this->formatBytes($stats['total_size']) }}</flux:heading>
            </flux:card>

            <flux:card>
                <flux:text class="text-sm text-zinc-500">Kodowanie</flux:text>
                <flux:heading size="xl" class="mt-1">{{ $stats['charset'] }}</flux:heading>
            </flux:card>

            <flux:card>
                <flux:text class="text-sm text-zinc-500">Porównanie</flux:text>
                <flux:heading size="sm" class="mt-1 truncate" title="{{ $stats['collation'] }}">
                    {{ $stats['collation'] }}
                </flux:heading>
            </flux:card>
        </div>

        <div>
            <flux:heading size="lg" class="mb-4">Tabele</flux:heading>

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Nazwa</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($tables as $table)
                        <flux:table.row :key="$table">
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <flux:icon name="table-cells" class="size-4 text-zinc-400" />
                                    <span class="font-medium">{{ $table }}</span>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell>
                                <flux:text class="text-zinc-400">Brak tabel</flux:text>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </div>
