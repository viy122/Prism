<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>PRISM | Batangas State University</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#F8FAFC] font-sans text-[#111827] antialiased">
        <header class="sticky top-0 z-50 border-b border-[#E5E7EB] bg-white/95 backdrop-blur">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 lg:px-8">
                <a href="{{ route('prism.home') }}" class="flex min-w-0 items-center gap-3">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full border border-[#E5E7EB] bg-white p-1.5 shadow-sm">
                        <img src="{{ asset('images/bsu-seal.png') }}" alt="Batangas State University seal" class="h-full w-full object-contain">
                    </span>
                    <span class="min-w-0">
                        <span class="block text-xl font-extrabold text-[#7A0C12]">PRISM</span>
                        <span class="hidden max-w-xs text-xs font-semibold leading-5 text-slate-500 sm:block">Procurement Records, Intelligence, Scoping, and Monitoring System</span>
                    </span>
                </a>

                <nav class="hidden items-center gap-6 text-sm font-bold text-slate-600 lg:flex" aria-label="Landing page navigation">
                    <a class="hover:text-[#7A0C12]" href="#about">About</a>
                    <a class="hover:text-[#7A0C12]" href="#features">Features</a>
                    <a class="hover:text-[#7A0C12]" href="#users">Users</a>
                    <a class="hover:text-[#7A0C12]" href="#workflow">Workflow</a>
                    <a class="hover:text-[#7A0C12]" href="#contact">Contact</a>
                </nav>

                <a href="{{ route('login') }}" class="inline-flex h-10 shrink-0 items-center justify-center rounded-md bg-[#7A0C12] px-4 text-sm font-extrabold text-white shadow-sm transition hover:bg-[#4A060A]">
                    Login
                </a>
            </div>
        </header>

        <main>
            <section id="about" class="mx-auto grid min-h-[calc(100vh-81px)] max-w-7xl items-center gap-10 px-4 py-12 lg:grid-cols-[minmax(0,0.9fr)_minmax(420px,1.1fr)] lg:px-8">
                <div>
                    <p class="text-sm font-extrabold uppercase text-[#D6A84F]">Batangas State University TNEU ARASOF-Nasugbu Campus</p>
                    <h1 class="mt-4 text-5xl font-extrabold leading-none text-[#7A0C12] sm:text-6xl lg:text-7xl">PRISM</h1>
                    <p class="mt-5 max-w-2xl text-2xl font-extrabold leading-9 text-[#111827]">Procurement Records, Intelligence, Scoping, and Monitoring System</p>
                    <p class="mt-5 max-w-2xl text-base leading-8 text-slate-600">An AI-assisted campus procurement platform for budget deliberation, market scoping, Annual Procurement Plan consolidation, Purchase Request tracking, and budget utilization monitoring.</p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ route('login') }}" class="inline-flex h-11 items-center justify-center rounded-md bg-[#7A0C12] px-5 text-sm font-extrabold text-white shadow-sm transition hover:bg-[#4A060A]">
                            Login to PRISM
                        </a>
                        <a href="#features" class="inline-flex h-11 items-center justify-center rounded-md border border-[#7A0C12]/30 bg-white px-5 text-sm font-extrabold text-[#7A0C12] shadow-sm transition hover:border-[#7A0C12] hover:bg-[#7A0C12]/5">
                            View System Overview
                        </a>
                    </div>
                </div>

                <div class="rounded-lg border border-[#E5E7EB] bg-white p-4 shadow-xl shadow-slate-200/70">
                    <div class="overflow-hidden rounded-lg border border-slate-200 bg-slate-50">
                        <div class="grid min-h-[420px] grid-cols-[88px_minmax(0,1fr)]">
                            <aside class="bg-[#7A0C12] p-3">
                                <div class="mx-auto mb-6 h-10 w-10 rounded-full bg-white p-1.5">
                                    <img src="{{ asset('images/bsu-seal.png') }}" alt="" class="h-full w-full object-contain">
                                </div>
                                <div class="grid gap-2">
                                    <span class="h-8 rounded-md bg-white/20"></span>
                                    <span class="h-8 rounded-md bg-white/10"></span>
                                    <span class="h-8 rounded-md bg-white/10"></span>
                                    <span class="h-8 rounded-md bg-white/10"></span>
                                </div>
                            </aside>
                            <section class="p-5">
                                <div class="mb-5 flex items-center justify-between gap-4">
                                    <div>
                                        <p class="text-xs font-extrabold uppercase text-[#7A0C12]">Campus dashboard</p>
                                        <h2 class="mt-1 text-xl font-extrabold text-slate-950">Procurement Monitoring</h2>
                                    </div>
                                    <span class="rounded-md bg-[#D6A84F]/20 px-3 py-1 text-xs font-extrabold text-[#7A0C12]">FY 2027</span>
                                </div>

                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div class="rounded-lg border border-slate-200 bg-white p-4">
                                        <span class="text-xs font-bold uppercase text-slate-500">Budget Utilization</span>
                                        <strong class="mt-2 block text-2xl font-extrabold text-[#7A0C12]">68.45%</strong>
                                    </div>
                                    <div class="rounded-lg border border-slate-200 bg-white p-4">
                                        <span class="text-xs font-bold uppercase text-slate-500">Pending Requests</span>
                                        <strong class="mt-2 block text-2xl font-extrabold text-[#7A0C12]">24</strong>
                                    </div>
                                    <div class="rounded-lg border border-slate-200 bg-white p-4">
                                        <span class="text-xs font-bold uppercase text-slate-500">Market References</span>
                                        <strong class="mt-2 block text-2xl font-extrabold text-[#7A0C12]">156</strong>
                                    </div>
                                    <div class="rounded-lg border border-slate-200 bg-white p-4">
                                        <span class="text-xs font-bold uppercase text-slate-500">Delayed Items</span>
                                        <strong class="mt-2 block text-2xl font-extrabold text-[#7A0C12]">12</strong>
                                    </div>
                                </div>

                                <div class="mt-4 rounded-lg border border-slate-200 bg-white p-4">
                                    <div class="mb-4 flex items-center justify-between">
                                        <h3 class="text-sm font-extrabold text-slate-950">Budget Utilization Trend</h3>
                                        <span class="text-xs font-bold text-slate-500">Q1-Q4</span>
                                    </div>
                                    <div class="flex h-32 items-end gap-3">
                                        <span class="h-[42%] flex-1 rounded-t-md bg-[#D6A84F]/60"></span>
                                        <span class="h-[54%] flex-1 rounded-t-md bg-[#D6A84F]/70"></span>
                                        <span class="h-[68%] flex-1 rounded-t-md bg-[#7A0C12]/80"></span>
                                        <span class="h-[74%] flex-1 rounded-t-md bg-[#7A0C12]"></span>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </section>

            <section id="features" class="border-y border-[#E5E7EB] bg-white py-14">
                <div class="mx-auto max-w-7xl px-4 lg:px-8">
                    <div class="mb-8">
                        <p class="text-sm font-extrabold uppercase text-[#7A0C12]">Core features</p>
                        <h2 class="mt-2 text-3xl font-extrabold text-slate-950">Campus procurement support modules</h2>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ([
                            ['icon' => 'file-text', 'title' => 'Budget Deliberation', 'text' => 'Organize proposed items, justifications, and office budget requirements.'],
                            ['icon' => 'search', 'title' => 'AI-Assisted Market Scoping', 'text' => 'Gather supplier price references for budget validation and documentation.'],
                            ['icon' => 'clipboard-check', 'title' => 'Budget Proposal Approval', 'text' => 'Route proposals through Finance and Chancellor review.'],
                            ['icon' => 'file-stack', 'title' => 'APP Consolidation', 'text' => 'Prepare consolidated Annual Procurement Plan records from approved proposals.'],
                            ['icon' => 'list-checks', 'title' => 'Procurement Mode Recommendation', 'text' => 'Support procurement mode review using item category and estimated cost.'],
                            ['icon' => 'receipt-text', 'title' => 'Purchase Request Tracking', 'text' => 'Monitor PR submission, status movement, and procurement remarks.'],
                        ] as $feature)
                            <article class="rounded-lg border border-[#E5E7EB] bg-[#F8FAFC] p-5">
                                <span class="flex h-10 w-10 items-center justify-center rounded-md bg-[#7A0C12]/10 text-[#7A0C12]">
                                    <i class="h-5 w-5" data-lucide="{{ $feature['icon'] }}" aria-hidden="true"></i>
                                </span>
                                <h3 class="mt-4 text-lg font-extrabold text-slate-950">{{ $feature['title'] }}</h3>
                                <p class="mt-2 text-sm leading-6 text-slate-600">{{ $feature['text'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="users" class="py-14">
                <div class="mx-auto max-w-7xl px-4 lg:px-8">
                    <div class="mb-8">
                        <p class="text-sm font-extrabold uppercase text-[#7A0C12]">User roles</p>
                        <h2 class="mt-2 text-3xl font-extrabold text-slate-950">Designed for campus procurement coordination</h2>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                        @foreach ([
                            ['title' => 'Office Head / Dean', 'text' => 'Encodes budget proposals, market references, and purchase requests.'],
                            ['title' => 'Finance Office', 'text' => 'Reviews budget availability and consolidates approved procurement plans.'],
                            ['title' => 'Procurement Office', 'text' => 'Tracks purchase requests, status updates, and procurement progress.'],
                            ['title' => 'Chancellor', 'text' => 'Reviews campus-level approvals, reports, and monitoring dashboards.'],
                            ['title' => 'Vice Chancellor', 'text' => 'Monitors division performance and office procurement status.'],
                        ] as $role)
                            <article class="rounded-lg border border-[#E5E7EB] bg-white p-5 shadow-sm">
                                <h3 class="text-base font-extrabold text-[#7A0C12]">{{ $role['title'] }}</h3>
                                <p class="mt-3 text-sm leading-6 text-slate-600">{{ $role['text'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="workflow" class="border-y border-[#E5E7EB] bg-white py-14">
                <div class="mx-auto max-w-7xl px-4 lg:px-8">
                    <div class="mb-8">
                        <p class="text-sm font-extrabold uppercase text-[#7A0C12]">Procurement workflow</p>
                        <h2 class="mt-2 text-3xl font-extrabold text-slate-950">From proposal preparation to analytics</h2>
                    </div>
                    <ol class="grid gap-3 lg:grid-cols-7">
                        @foreach (['Budget Proposal', 'Market Scoping', 'Approval Workflow', 'APP Consolidation', 'Purchase Request', 'Procurement Tracking', 'Dashboard & Analytics'] as $index => $step)
                            <li class="rounded-lg border border-[#E5E7EB] bg-[#F8FAFC] p-4">
                                <span class="flex h-8 w-8 items-center justify-center rounded-md bg-[#7A0C12] text-sm font-extrabold text-white">{{ $index + 1 }}</span>
                                <strong class="mt-3 block text-sm font-extrabold leading-6 text-slate-950">{{ $step }}</strong>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </section>
        </main>

        <footer id="contact" class="bg-[#4A060A] px-4 py-8 text-white lg:px-8">
            <div class="mx-auto flex max-w-7xl flex-col gap-2 text-sm font-semibold leading-6 text-white/80">
                <p class="font-extrabold text-white">Batangas State University TNEU ARASOF-Nasugbu Campus</p>
                <p>College of Informatics and Computing Sciences</p>
                <p>PRISM Prototype</p>
                <p>&copy; 2025 All rights reserved.</p>
            </div>
        </footer>
    </body>
</html>
