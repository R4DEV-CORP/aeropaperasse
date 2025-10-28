<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div class="flex justify-center items-center min-h-screen bg-cover bg-center bg-no-repeat" style="background-image: url('{{ asset('images/coulds-background.png') }}')">
            <div class="flex justify-center items-center bg-gradient-to-b from-sky-200 to-white backdrop-blur-sm p-8 rounded-lg shadow-md w-96">
                <div class="w-80 max-w-80 space-y-6">
                    <img src="{{ asset('images/aeropaperasse-logo.png') }}" alt="Logo Aéropaperasse" class="w-64 mx-auto">
                    <flux:heading class="text-center font-semibold" size="xl" accent="true">Vérification 2FA</flux:heading>
                    <flux:text class="text-center text-gray-800">Un code de vérification a été envoyé à votre adresse email.</flux:text>
                    <livewire:auth.verify2-f-a />
                    <flux:button
                        href="/login"
                        icon="arrow-left"
                        variant="ghost"
                        class="w-full"
                    >
                        Retour à la connexion
                    </flux:button>
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>