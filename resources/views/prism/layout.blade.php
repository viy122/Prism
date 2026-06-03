<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $pageTitle ?? 'PRISM' }} | PRISM</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body data-prism-app class="min-h-screen bg-bsu-bg font-sans text-slate-800 antialiased">
        @php
            $currentRole = collect($roleNavigation ?? [])->firstWhere('slug', $activeRole);
            $currentModule = collect($moduleNavigation ?? [])->firstWhere('slug', $activeModulePage ?? null);
        @endphp

        <div class="min-h-screen lg:grid lg:grid-cols-[292px_minmax(0,1fr)]">
            <aside class="sticky top-0 z-50 hidden h-screen flex-col overflow-y-auto bg-[#681012] px-4 py-5 text-white lg:flex" aria-label="{{ $moduleNavLabel ?? 'PRISM navigation' }}">
                <div class="flex items-center justify-between gap-3 px-1">
                    <a class="flex min-w-0 items-center gap-3 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-white/60" href="{{ $brandHref ?? route('office-head.dashboard') }}">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-white p-1.5 shadow-sm">
                            <img class="h-full w-full object-contain" src="{{ asset('images/bsu-seal.png') }}" alt="Batangas State University seal">
                        </span>
                        <span class="min-w-0">
                            <span class="block text-lg font-extrabold leading-6">PRISM</span>
                            <span class="block truncate text-xs font-semibold text-white/65">UI prototype workspace</span>
                        </span>
                    </a>
                    <span class="inline-flex shrink-0 items-center rounded-full border border-white/15 bg-white/10 px-2.5 py-1 text-[0.68rem] font-bold uppercase text-white/70">
                        Demo
                    </span>
                </div>

                @if (! empty($moduleNavigation ?? []))
                    <div class="mt-9 px-1">
                        <p class="mb-2 text-xs font-bold uppercase text-white/45">Workspace</p>
                        <p class="mb-4 truncate text-sm font-extrabold text-white">{{ $currentRole['label'] ?? 'PRISM User' }}</p>
                        <nav class="grid gap-2" aria-label="{{ $moduleNavLabel ?? 'PRISM module pages' }}">
                            @foreach ($moduleNavigation as $item)
                                <a class="group relative flex min-h-12 items-center gap-3 rounded-lg px-3.5 py-3 text-[15px] font-semibold leading-5 transition focus:outline-none focus:ring-2 focus:ring-white/60 {{ ($activeModulePage ?? null) === $item['slug'] ? 'bg-white text-[#681012] shadow-sm' : 'text-white/70 hover:bg-white/10 hover:text-white' }}" href="{{ $item['href'] }}">
                                    <i class="h-5 w-5 shrink-0 {{ ($activeModulePage ?? null) === $item['slug'] ? 'text-[#681012]' : 'text-white/45 group-hover:text-white' }}" data-lucide="{{ $item['icon'] }}" aria-hidden="true"></i>
                                    <span>{{ $item['label'] }}</span>
                                </a>
                            @endforeach
                        </nav>
                    </div>
                @else
                    <div class="mt-10 rounded-lg border border-white/10 bg-white/10 p-4 text-sm leading-6 text-white/70">
                        {{ $sidebarNote ?? 'Select a user section to view its PRISM workspace.' }}
                    </div>
                @endif

                <div class="mt-auto grid gap-4">
                    <div class="rounded-lg border border-white/10 bg-white/10 p-4">
                        <div>
                            <span class="block text-xs font-bold uppercase text-white/45">Current page</span>
                            <span class="mt-1 block text-sm font-extrabold text-white">{{ $currentModule['label'] ?? 'Dashboard' }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 px-1 text-white/75">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-white/10 bg-white/10">
                            <i class="h-5 w-5" data-lucide="mouse-pointer-click" aria-hidden="true"></i>
                        </span>
                        <div class="min-w-0">
                            <span class="block truncate text-sm font-extrabold text-white">Prototype mode</span>
                            <span class="block truncate text-xs font-semibold text-white/55">{{ $currentRole['label'] ?? 'PRISM User' }}</span>
                        </div>
                    </div>
                    <a class="flex min-h-11 items-center justify-center gap-2 rounded-lg border border-white/15 bg-white/10 px-3 text-sm font-semibold text-white transition hover:bg-white hover:text-[#681012] focus:outline-none focus:ring-2 focus:ring-white/60" href="{{ route('login') }}">
                        <i class="h-4 w-4" data-lucide="log-out" aria-hidden="true"></i>
                        Logout
                    </a>
                </div>
            </aside>

            <div class="min-w-0">
                <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur">
                    <div class="flex min-h-[80px] flex-col gap-3 px-4 py-3.5 lg:px-6 xl:flex-row xl:items-center xl:justify-between xl:px-8">
                        <div class="flex min-w-0 items-center gap-3 xl:hidden">
                            <a class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-white p-1.5 shadow-sm lg:hidden" href="{{ $brandHref ?? route('office-head.dashboard') }}">
                                <img class="h-full w-full object-contain" src="{{ asset('images/bsu-seal.png') }}" alt="Batangas State University seal">
                            </a>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-extrabold text-slate-950">PRISM Prototype</p>
                                <p class="truncate text-[15px] font-semibold text-slate-600">{{ $currentModule['label'] ?? 'Dashboard' }}</p>
                            </div>
                        </div>

                        <div class="flex min-w-0 flex-1 flex-col gap-2 xl:flex-row xl:items-center">
                            <div class="flex shrink-0 items-center gap-2">
                                <span class="text-[12px] font-bold uppercase text-slate-500">Viewing as</span>
                                <span class="hidden text-[15px] font-bold text-slate-950 sm:inline">{{ $currentRole['label'] ?? 'PRISM User' }}</span>
                            </div>
                            <nav class="flex min-w-0 items-center gap-1 overflow-x-auto rounded-lg border border-slate-200 bg-slate-100 p-1" aria-label="PRISM user sections">
                                @foreach ($roleNavigation as $role)
                                    <a class="inline-flex h-10 shrink-0 items-center justify-center rounded-md px-3.5 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-bsu-gold/70 {{ $activeRole === $role['slug'] ? 'bg-white text-bsu-maroon shadow-sm ring-1 ring-inset ring-slate-200' : 'text-slate-600 hover:bg-white hover:text-bsu-maroon' }}" href="{{ $role['href'] }}">
                                        {{ $role['label'] }}
                                    </a>
                                @endforeach
                            </nav>
                        </div>

                        <div class="flex shrink-0 items-center gap-2">
                            <span class="hidden items-center text-[26px] font-bold leading-8 text-slate-950 xl:inline-flex">{{ $currentModule['label'] ?? 'Dashboard' }}</span>
                            <span class="inline-flex h-10 items-center rounded-md bg-bsu-maroon/5 px-3 text-sm font-semibold text-bsu-maroon xl:hidden">{{ $currentModule['label'] ?? 'Dashboard' }}</span>
                            <a class="inline-flex h-10 items-center gap-2 rounded-md border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-600 transition hover:border-bsu-maroon hover:bg-bsu-maroon/5 hover:text-bsu-maroon" href="{{ route('login') }}">
                                <i class="h-4 w-4" data-lucide="log-out" aria-hidden="true"></i>
                                <span class="hidden sm:inline">Logout</span>
                            </a>
                        </div>
                    </div>

                    @if (! empty($moduleNavigation ?? []))
                        <nav class="flex gap-2 overflow-x-auto border-t border-slate-100 px-4 py-2 lg:hidden" aria-label="{{ $moduleNavLabel ?? 'PRISM module pages' }}">
                            @foreach ($moduleNavigation as $item)
                                <a class="inline-flex h-10 shrink-0 items-center gap-2 rounded-md px-3.5 text-sm font-bold {{ ($activeModulePage ?? null) === $item['slug'] ? 'bg-bsu-maroon text-white' : 'bg-slate-100 text-slate-600' }}" href="{{ $item['href'] }}">
                                    <i class="h-4 w-4" data-lucide="{{ $item['icon'] }}" aria-hidden="true"></i>
                                    {{ $item['label'] }}
                                </a>
                            @endforeach
                        </nav>
                    @endif
                </header>

                <main class="min-w-0 px-4 py-6 lg:px-6 xl:px-8">
                    <div class="w-full [&>*+*]:mt-5">
                        @yield('content')
                    </div>
                </main>
            </div>
        </div>
    </body>
</html>
