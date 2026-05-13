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
        @php
            $user = auth()->user();
            $isClient = $user?->isClient() ?? false;
            $canSeeFormations = ! $isClient || ($user?->can_access_formation ?? false);

            $navItems = collect([
                [
                    'label' => 'Sociétés',
                    'href' => '/companies',
                    'icon' => 'building-office',
                    'visible' => true,
                ],
                [
                    'label' => "Demande d'activité",
                    'href' => '/activity-requests',
                    'icon' => 'clipboard-document-list',
                    'visible' => ! $isClient,
                ],
                [
                    'label' => 'Collaborateurs & utilisateurs',
                    'href' => '/coworkers',
                    'icon' => 'users',
                    'visible' => true,
                ],
                [
                    'label' => 'Demande de badge',
                    'href' => '/badge-requests',
                    'icon' => 'identification',
                    'visible' => true,
                ],
                [
                    'label' => 'Suivi des badges',
                    'href' => '/badge-management',
                    'icon' => 'rectangle-stack',
                    'visible' => true,
                ],
                [
                    'label' => 'Formations',
                    'href' => '/trainings',
                    'icon' => 'academic-cap',
                    'visible' => $canSeeFormations,
                ],
                [
                    'label' => 'Laissez-passer',
                    'href' => '/vehicle-pass',
                    'icon' => 'ticket',
                    'visible' => ! $isClient,
                ],
            ])->filter(fn ($item) => $item['visible']);
        @endphp

        <div
            x-data="{
                mobileOpen: false,
                desktopOpen: $persist(true).as('sidebarDesktopOpen'),
                isDesktop() {
                    return window.matchMedia('(min-width: 1024px)').matches;
                },
                isSidebarVisible() {
                    return this.isDesktop() ? this.desktopOpen : this.mobileOpen;
                },
                sidebarLabel() {
                    return this.isSidebarVisible() ? 'Masquer la barre latérale' : 'Afficher la barre latérale';
                },
                toggleSidebar() {
                    if (this.isDesktop()) {
                        this.desktopOpen = ! this.desktopOpen;
                    } else {
                        this.mobileOpen = ! this.mobileOpen;
                    }
                },
            }"
            class="flex h-full"
        >
            {{-- Mobile overlay --}}
            <div
                x-show="mobileOpen"
                x-transition.opacity
                @click="mobileOpen = false"
                class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden"
                style="display: none;"
            ></div>

            {{-- Sidebar --}}
            <aside
                :class="[
                    mobileOpen ? 'translate-x-0' : '-translate-x-full',
                    desktopOpen ? '' : 'lg:hidden',
                ]"
                class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col bg-slate-900 text-slate-100 transition-transform duration-200 lg:static lg:translate-x-0"
            >
                {{-- Brand --}}
                <div class="flex items-center justify-center border-b border-slate-800 px-5 py-5">
                    <a href="/dashboard" wire:navigate class="inline-flex">
                        <img
                            src="{{ asset('images/aeropaperasse-logo-white-trimmed.png') }}"
                            alt="{{ config('app.name') }}"
                            class="h-14 w-auto"
                        >
                    </a>
                </div>

                {{-- Nav --}}
                <nav class="flex flex-1 flex-col gap-2 px-3 pt-5 pb-4">
                    @foreach ($navItems as $item)
                        @php
                            $active = request()->is(ltrim($item['href'], '/').'*');
                            $linkClasses = $active
                                ? 'bg-slate-800 text-white'
                                : 'text-slate-400 hover:bg-slate-800/60 hover:text-white';
                        @endphp

                        <a
                            href="{{ $item['href'] }}"
                            wire:navigate
                            class="group flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition {{ $linkClasses }}"
                        >
                            <span class="shrink-0 text-slate-400 group-hover:text-white {{ $active ? 'text-white' : '' }}">
                                @switch($item['icon'])
                                    @case('building-office')
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" /></svg>
                                        @break
                                    @case('clipboard-document-list')
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" /></svg>
                                        @break
                                    @case('users')
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                                        @break
                                    @case('identification')
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6-10.125a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0Zm1.294 6.336a6.721 6.721 0 0 1-3.17.789 6.721 6.721 0 0 1-3.168-.789 3.376 3.376 0 0 1 6.338 0Z" /></svg>
                                        @break
                                    @case('rectangle-stack')
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 6.878V6a2.25 2.25 0 0 1 2.25-2.25h7.5A2.25 2.25 0 0 1 18 6v.878m-12 0c.235-.083.487-.128.75-.128h10.5c.263 0 .515.045.75.128m-12 0A2.25 2.25 0 0 0 4.5 9v.878m13.5-3A2.25 2.25 0 0 1 19.5 9v.878m0 0a2.246 2.246 0 0 0-.75-.128H5.25c-.263 0-.515.045-.75.128m15 0A2.25 2.25 0 0 1 21 12v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6c0-.98.626-1.813 1.5-2.122" /></svg>
                                        @break
                                    @case('academic-cap')
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" /></svg>
                                        @break
                                    @case('ticket')
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" /></svg>
                                        @break
                                @endswitch
                            </span>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </nav>

                {{-- Logout --}}
                <div class="border-t border-slate-800 px-3 py-3">
                    <a
                        href="{{ route('auth.logout') }}"
                        class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium text-slate-400 transition hover:bg-slate-800/60 hover:text-white"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 text-slate-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                        </svg>
                        <span>Déconnexion</span>
                    </a>
                </div>
            </aside>

            {{-- Main column --}}
            <div class="flex min-w-0 flex-1 flex-col">
                {{-- Topbar --}}
                <header class="flex h-14 shrink-0 items-center gap-3 border-b border-border bg-white px-6">
                    <x-ui.tooltip placement="bottom" align="start">
                        <button
                            type="button"
                            @click="toggleSidebar()"
                            :aria-expanded="isSidebarVisible().toString()"
                            :aria-label="sidebarLabel()"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-md text-foreground-muted transition hover:bg-slate-100 hover:text-foreground focus:outline-none focus-visible:ring-2 focus-visible:ring-accent"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.5h16.5a.75.75 0 0 1 .75.75v13.5a.75.75 0 0 1-.75.75H3.75a.75.75 0 0 1-.75-.75V5.25a.75.75 0 0 1 .75-.75ZM9 4.5v15" />
                            </svg>
                        </button>

                        <x-slot:content>
                            <span x-text="sidebarLabel()"></span>
                        </x-slot:content>
                    </x-ui.tooltip>

                    @php
                        $isClientSide = $user && ($user->isClient() || $user->isSClient());

                        if ($isClientSide && $user->client) {
                            $breadcrumbRoot = [
                                'label' => $user->client->company_name,
                                'href' => route('companies.show', ['companyId' => $user->client_id]),
                            ];
                        } else {
                            $breadcrumbRoot = ['label' => 'Administration'];
                        }
                    @endphp

                    @isset($breadcrumb)
                        <x-ui.breadcrumb :items="array_merge([$breadcrumbRoot], $breadcrumb)" />
                    @else
                        <span class="text-sm font-medium text-foreground-muted">{{ $title ?? 'Tableau de bord' }}</span>
                    @endisset
                </header>

                {{-- Page content --}}
                <main class="flex-1 overflow-y-auto bg-slate-50">
                    {{ $slot }}

                    <footer class="border-t border-border bg-white px-6 py-4 text-xs text-foreground-muted">
                        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                            <p>&copy; {{ date('Y') }} Aeropaperasse &mdash; tous droits réservés</p>

                            <nav class="flex flex-wrap items-center gap-x-4 gap-y-1">
                                <a href="#" class="hover:text-foreground hover:underline">Mentions légales</a>
                                <a href="#" class="hover:text-foreground hover:underline">Politique de confidentialité</a>
                                <a href="#" class="hover:text-foreground hover:underline">CGU</a>
                                <a href="#" class="hover:text-foreground hover:underline">CGV</a>
                            </nav>

                            <p>
                                Une réalisation
                                <a href="https://r4dev.fr" target="_blank" rel="noopener noreferrer" class="font-medium text-foreground hover:underline">R4DEV</a>
                            </p>
                        </div>
                    </footer>
                </main>
            </div>
        </div>

        <livewire:notifications.toast-stack />

        @livewireScripts
    </body>
</html>
