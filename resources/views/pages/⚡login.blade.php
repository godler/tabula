<?php

use App\Contracts\DatabaseServiceInterface;
use App\Exceptions\DatabaseConnectionException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Layout('components.layouts.app')] #[Title('Login — Tabula')] class extends Component {
    #[Validate('required')]
    public string $host = '127.0.0.1';

    #[Validate('required|integer|min:1|max:65535')]
    public int $port = 3306;

    #[Validate('required')]
    public string $username = '';

    #[Validate('required')]
    public string $password = '';

    private DatabaseServiceInterface $databaseService;

    public function boot(DatabaseServiceInterface $databaseService): void
    {
        $this->databaseService = $databaseService;
    }

    public function connect(): void
    {
        $this->validate();

        try {
            $this->databaseService->testConnection($this->host, $this->port, $this->username, $this->password);
        } catch (DatabaseConnectionException $e) {
            $this->addError('username', 'Nie można połączyć z serwerem: ' . $e->getMessage());

            return;
        }

        session([
            'db_host' => $this->host,
            'db_port' => $this->port,
            'db_username' => $this->username,
            'db_password' => encrypt($this->password),
        ]);

        $this->redirect(route('databases'), navigate: true);
    }
};
?>

<div class="flex min-h-screen items-center justify-center bg-zinc-50 dark:bg-zinc-900">
    <div class="w-full max-w-sm px-4">
        <div class="mb-8 text-center">
            <flux:heading size="xl" class="font-bold tracking-tight">Tabula</flux:heading>
            <flux:text class="mt-1 text-zinc-500">Połącz się z serwerem MySQL</flux:text>
        </div>

        <flux:card class="space-y-6">
            <form wire:submit="connect" class="space-y-5">
                <div class="flex gap-3">
                    <flux:field class="flex-1">
                        <flux:label>Host</flux:label>
                        <flux:input
                            wire:model="host"
                            placeholder="127.0.0.1"
                            autocomplete="off"
                        />
                        <flux:error name="host" />
                    </flux:field>

                    <flux:field class="w-24">
                        <flux:label>Port</flux:label>
                        <flux:input
                            type="number"
                            wire:model="port"
                            placeholder="3306"
                            min="1"
                            max="65535"
                        />
                        <flux:error name="port" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Użytkownik</flux:label>
                    <flux:input
                        wire:model="username"
                        placeholder="root"
                        autocomplete="username"
                    />
                    <flux:error name="username" />
                </flux:field>

                <flux:field>
                    <flux:label>Hasło</flux:label>
                    <flux:input
                        type="password"
                        wire:model="password"
                        placeholder="••••••••"
                        autocomplete="current-password"
                    />
                    <flux:error name="password" />
                </flux:field>

                <flux:button type="submit" variant="primary" class="w-full">
                    Połącz
                </flux:button>
            </form>
        </flux:card>
    </div>
</div>
