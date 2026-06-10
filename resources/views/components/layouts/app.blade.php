<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? 'Tabula' }}</title>

        @fluxAppearance
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen  dark:bg-zinc-800 flex">
        @if (session()->has('db_host'))
            <flux:sidebar sticky collapsible="mobile" class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700">

                 <livewire:database-sidebar />
            </flux:sidebar>

            <flux:header class="lg:hidden">
                <flux:sidebar.toggle icon="bars-2" inset="left" />
                <flux:spacer />
            </flux:header>
        @endif
        <flux:main>
         {{ $slot }}
        </flux:main>

        @livewireScripts
        @fluxScripts
    </body>
</html>
