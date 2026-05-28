<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="h-full font-sans antialiased text-foreground">
        <div class="min-h-full bg-slate-50">
            <header class="border-b border-border bg-white">
                <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                    <img
                        src="{{ asset('images/aeropaperasse-logo.png') }}"
                        alt="{{ config('app.name') }}"
                        class="h-9 w-auto"
                    >

                    <a href="{{ route('auth.logout') }}" class="text-sm font-medium text-foreground-muted hover:text-foreground">
                        Se déconnecter
                    </a>
                </div>
            </header>

            <main class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
                {{ $slot }}
            </main>
        </div>

        @livewireScripts
    </body>
</html>
