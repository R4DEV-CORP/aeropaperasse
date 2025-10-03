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

        @fluxAppearance
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky stashable class="bg-zinc-50 dark:bg-zinc-900 border-r rtl:border-r-0 rtl:border-l border-zinc-200 dark:border-zinc-700">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />
            <flux:brand href="/" name="Diane" class="px-2 dark:hidden" />
            <flux:brand href="/" name="Diane" class="px-2 hidden dark:flex" />
            <!--<flux:input as="button" variant="filled" placeholder="Search..." icon="magnifying-glass" />-->
            <flux:navlist variant="outline">
                <flux:navlist.item icon="home" href="/dashboard">Dashboard</flux:navlist.item>
                <flux:navlist.group expandable heading="Fiches de révisions" class="grid">
                    <flux:navlist.item badge="{{ App\Models\Flashcard::count() }}" badge-color="teal" icon="magnifying-glass" href="/flashcards">Parcourir</flux:navlist.item>
                    <flux:navlist.item icon="folder-open" href="/flashcards/collections">Collections</flux:navlist.item>
                </flux:navlist.group>
                <flux:navlist.group expandable heading="QCMs" class="grid">
                    <flux:navlist.item badge="{{ App\Models\Qcm::count() }}" badge-color="teal"  icon="magnifying-glass" href="/qcms">Parcourir</flux:navlist.item>
                    <flux:navlist.item icon="folder-open" href="/qcms/collections">Collections</flux:navlist.item>
                </flux:navlist.group>
                <flux:navlist.item icon="calendar" href="/planning">Planning</flux:navlist.item>
            </flux:navlist>
            <flux:spacer />
            <!--<flux:navlist variant="outline">
                <flux:navlist.item icon="cog-6-tooth" href="#">Paramètres</flux:navlist.item>
                <flux:navlist.item icon="information-circle" href="#">Aide</flux:navlist.item>
            </flux:navlist>-->
                    <flux:dropdown position="top" align="start" class="max-lg:hidden">
            <flux:profile avatar:color="auto" name="{{ auth()->user()->name }}" />
            <flux:menu>
                <flux:menu.item icon="user" href="{{ route('profile') }}">Profil</flux:menu.item>
                <flux:menu.item icon="arrow-right-start-on-rectangle" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Deconnexion</flux:menu.item>
            </flux:menu>
        </flux:dropdown>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
            @csrf
        </form>
        </flux:sidebar>
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
            <flux:spacer />
                    <flux:dropdown position="top" alignt="start">
            <flux:profile avatar:color="auto" name="{{ auth()->user()->name }}"/>
            <flux:menu>
                <flux:menu.item icon="user" href="{{ route('profile') }}">Profil</flux:menu.item>
                <flux:menu.item icon="arrow-right-start-on-rectangle" href="#" onclick="event.preventDefault(); document.getElementById('logout-form-mobile').submit();">Deconnexion</flux:menu.item>
            </flux:menu>
        </flux:dropdown>
        <form id="logout-form-mobile" action="{{ route('logout') }}" method="POST" class="hidden">
            @csrf
        </form>
        </flux:header>
        <flux:main>
            {{ $slot }}
        </flux:main>
        <flux:toast position="top right" />
        @fluxScripts
    </body>
</html>
