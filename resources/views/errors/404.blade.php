<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Page introuvable &mdash; {{ config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css'])
    </head>
    <body class="h-full font-sans antialiased text-foreground">
        @php
            $homeHref = auth()->check() ? '/' : route('auth.login');
        @endphp

        <div
            class="flex min-h-full items-center justify-center bg-slate-50 bg-cover bg-center bg-no-repeat px-4 py-12 sm:px-6 lg:px-8"
            style="background-image: url('{{ asset('images/coulds-background.png') }}');"
        >
            <main class="w-full max-w-xl">
                <x-ui.card padding="lg">
                    <div class="flex flex-col items-center gap-6 text-center">
                        <a href="{{ $homeHref }}" class="inline-flex">
                            <img
                                src="{{ asset('images/aeropaperasse-logo.png') }}"
                                alt="{{ config('app.name') }}"
                                class="h-10 w-auto"
                            >
                        </a>

                        <div class="space-y-2">
                            <p class="text-sm font-semibold tracking-wider text-accent uppercase">Erreur 404</p>
                            <h1 class="text-2xl font-semibold text-foreground sm:text-3xl">Page introuvable</h1>
                            <p class="text-sm text-foreground-muted">
                                La page que vous demandez n'existe pas ou n'est pas accessible depuis cet espace.
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center justify-center gap-3">
                            <x-ui.button :href="$homeHref" variant="primary">
                                Retour à l'accueil
                            </x-ui.button>
                            <x-ui.button variant="secondary" onclick="window.history.back(); return false;">
                                Page précédente
                            </x-ui.button>
                        </div>
                    </div>
                </x-ui.card>

                <p class="mt-6 text-center text-xs text-foreground-subtle">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés.
                </p>
            </main>
        </div>
    </body>
</html>
