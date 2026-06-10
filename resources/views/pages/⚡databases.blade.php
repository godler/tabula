<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('components.layouts.app')] #[Title('Bazy danych — Tabula')] class extends Component {
    public function mount(): void
    {
        if (! session()->has('db_host')) {
            $this->redirect(route('login'), navigate: true);

            return;
        }

        $this->dispatch('database-page-loaded', database: null);
    }
};
?>

    <div class="flex h-96 items-center justify-center">
        <div class="text-center">
            <flux:icon name="circle-stack" class="mx-auto mb-3 size-10 text-zinc-300 dark:text-zinc-600" />
            <flux:heading>Wybierz bazę danych</flux:heading>
            <flux:text class="mt-1 text-zinc-500">Kliknij na bazę danych po lewej stronie</flux:text>
        </div>
    </div>

