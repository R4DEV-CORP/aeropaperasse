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
        <div
            class="flex min-h-full items-center justify-center bg-slate-50 bg-cover bg-center bg-no-repeat px-4 py-12 sm:px-6 lg:px-8"
            style="background-image: url('{{ asset('images/coulds-background.png') }}');"
        >
            <main class="w-full max-w-3xl">
                <x-ui.card padding="none" class="overflow-hidden">
                    <div class="grid sm:grid-cols-2">
                        <div class="hidden border-r border-border bg-slate-50 p-10 sm:flex sm:items-center sm:justify-center">
                            <a href="{{ route('auth.login') }}" class="inline-flex">
                                <img
                                    src="{{ asset('images/aeropaperasse-logo.png') }}"
                                    alt="{{ config('app.name') }}"
                                    class="h-auto w-full max-w-[240px]"
                                >
                            </a>
                        </div>

                        <div class="p-8 sm:p-10">
                            <div class="mb-6 flex justify-center sm:hidden">
                                <img
                                    src="{{ asset('images/aeropaperasse-logo.png') }}"
                                    alt="{{ config('app.name') }}"
                                    class="h-10 w-auto"
                                >
                            </div>

                            {{ $slot }}
                        </div>
                    </div>
                </x-ui.card>

                <p class="mt-6 text-center text-xs text-foreground-subtle">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés.
                </p>
            </main>
        </div>

        @livewireScripts
    </body>
</html>
