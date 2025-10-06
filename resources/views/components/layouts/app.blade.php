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
    <body class="min-h-screen bg-gray-50">
        <flux:sidebar sticky collapsible class="bg-zinc-700 text-white border-r border-zinc-200">
            <flux:sidebar.header>
                <flux:sidebar.brand
                    href="/dashboard/badge-requests"
                    logo="{{ asset('storage/aeropaperasse-logo-white.png') }}"
                />
                <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
            </flux:sidebar.header>
            <flux:sidebar.nav>
                <flux:sidebar.item icon="document-text" href="/activity-requests">Demande d'activité</flux:sidebar.item>
                <flux:sidebar.item icon="identification" href="/badge-requests">Demande de badge</flux:sidebar.item>
                <flux:sidebar.item icon="document-check" href="#">Suivi des badges</flux:sidebar.item>
                <flux:sidebar.item icon="hand-raised" href="#">Laissez-passer</flux:sidebar.item>
                <flux:sidebar.item icon="academic-cap" href="#">Formations</flux:sidebar.item>
                <flux:sidebar.item icon="building-office" href="#">Sociétés</flux:sidebar.item>
                <flux:sidebar.item icon="users" href="#">Collaborateurs</flux:sidebar.item>
                <flux:sidebar.item icon="chat-bubble-left-right" href="#">Messagerie</flux:sidebar.item>
            </flux:sidebar.nav>
            <flux:sidebar.spacer />
            <flux:sidebar.nav>
                <flux:sidebar.item icon="arrow-left-start-on-rectangle" href="#">Deconnexion</flux:sidebar.item>
            </flux:sidebar.nav>
        </flux:sidebar>
        <flux:main>
            {{ $slot }}
        </flux:main>
        @fluxScripts
    </body>
</html>