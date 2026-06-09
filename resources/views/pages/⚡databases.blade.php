<?php

use App\Contracts\DatabaseServiceInterface;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('components.layouts.app')] #[Title('Bazy danych — Tabula')] class extends Component {
    /** @var array<int, string> */
    public array $databases = [];

    public string $host = '';

    public string $username = '';

    private DatabaseServiceInterface $databaseService;

    public function boot(DatabaseServiceInterface $databaseService): void
    {
        $this->databaseService = $databaseService;
    }

    public function mount(): void
    {
        if (! session()->has('db_host')) {
            $this->redirect(route('login'), navigate: true);

            return;
        }

        $this->host = session('db_host');
        $this->username = session('db_username');

        $this->loadDatabases();
    }

    private function loadDatabases(): void
    {
        $this->databases = $this->databaseService->getDatabases(
            session('db_host'),
            session('db_port', 3306),
            session('db_username'),
            decrypt(session('db_password')),
        );
    }

    public function disconnect(): void
    {
        session()->forget(['db_host', 'db_port', 'db_username', 'db_password']);

        $this->redirect(route('login'), navigate: true);
    }
};
?>

<div class="min-h-screen bg-zinc-50 dark:bg-zinc-900">
    <header class="border-b border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
            <div class="flex items-center gap-3">
                <flux:heading size="lg" class="font-bold">Tabula</flux:heading>
                <flux:badge color="zinc" size="sm">{{ $host }}</flux:badge>
            </div>

            <div class="flex items-center gap-3">
                <flux:text class="text-sm text-zinc-500">{{ $username }}</flux:text>
                <flux:button wire:click="disconnect" variant="ghost" size="sm" icon="arrow-right-start-on-rectangle">
                    Rozłącz
                </flux:button>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-6 py-8">
        <div class="mb-6">
            <flux:heading size="lg">Bazy danych</flux:heading>
            <flux:text class="mt-1 text-zinc-500">{{ count($databases) }} {{ count($databases) === 1 ? 'baza' : (count($databases) < 5 ? 'bazy' : 'baz') }}</flux:text>
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>Nazwa</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($databases as $database)
                    <flux:table.row :key="$database">
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <flux:icon name="circle-stack" class="size-4 text-zinc-400" />
                                <span class="font-medium">{{ $database }}</span>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell>
                            <flux:text class="text-zinc-400">Brak baz danych</flux:text>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </main>
</div>
