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
        <flux:sidebar sticky collapsible class="bg-zinc-700 text-white border-r border-zinc-200 @container/sidebar">
            <flux:sidebar.header>
                <flux:sidebar.brand
                    href="/dashboard/badge-requests"
                    logo="{{ asset('images/aeropaperasse-logo-white.png') }}"
                />
                <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
            </flux:sidebar.header>
            <flux:sidebar.nav>
                @if(!auth()->user()->isClient())
                <flux:sidebar.item icon="document-text" href="/activity-requests" wire:navigate>Demande d'activité</flux:sidebar.item>
                @endif
                <flux:sidebar.item icon="identification" href="/badge-requests" wire:navigate>Demande de badge</flux:sidebar.item>
                <flux:sidebar.item icon="rectangle-stack" href="/badge-management" wire:navigate>Suivi des badges</flux:sidebar.item>
                <flux:sidebar.item icon="hand-raised" href="/vehicle-pass" wire:navigate>Laissez-passer</flux:sidebar.item>
                <flux:sidebar.item icon="academic-cap" href="/trainings" wire:navigate>Formations</flux:sidebar.item>
                <flux:sidebar.item icon="building-office" href="/clients" wire:navigate>Sociétés</flux:sidebar.item>
                <flux:sidebar.item icon="users" href="/coworkers" wire:navigate>Collaborateurs & utilisateurs</flux:sidebar.item>
                <flux:sidebar.item icon="chat-bubble-left-right" href="#" wire:navigate>Messagerie</flux:sidebar.item>
            </flux:sidebar.nav>
            <flux:sidebar.spacer />
                <div class="@min-[64px]/sidebar:block hidden bg-linear-to-t from-[#0d0b0a] to-[#272322] p-4 m-4 rounded-lg">
                    <div class="flex gap-1 items-center justify-center font-semibold">
                        <flux:icon.information-circle class="size-6" />
                        <p class="text-center text-lg">Support client</p>
                    </div>
                    <p class="mt-2 text-center">Pour tout incident technique, veuillez ouvrir un ticket.</p>
                    <div class="flex justify-center p-4">
                        <a href="https://r4web.zendesk.com" target="_blank" class="py-2 px-4 text-center rounded-lg bg-white text-black font-semibold hover:bg-slate-100 hover:text-slate-900">Ouvrir un ticket</a>
                    </div>
                    <div class="flex justify-center">
                        <img src="{{ asset('images/logo-r4client.png') }}" alt="R4DEV" class="w-24">
                    </div>
                    <p class="mt-2 text-center">www.r4dev.fr</p>
                </div>
                
            <flux:sidebar.nav>
                <flux:sidebar.item icon="arrow-left-start-on-rectangle" href="{{ route('auth.logout') }}">Deconnexion</flux:sidebar.item>
            </flux:sidebar.nav>
        </flux:sidebar>
        <flux:main>
            {{ $slot }}
        </flux:main>
        @fluxScripts
    </body>
</html>