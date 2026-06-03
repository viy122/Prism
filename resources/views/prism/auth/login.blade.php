<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Login | PRISM</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#F8FAFC] font-sans text-[#111827] antialiased">
        <main class="grid min-h-screen lg:grid-cols-[minmax(0,0.95fr)_minmax(480px,1.05fr)]">
            <section class="relative overflow-hidden bg-[#4A060A] px-6 py-10 text-white lg:flex lg:flex-col lg:justify-between lg:px-12">
                <div class="absolute inset-0 bg-[linear-gradient(135deg,rgba(122,12,18,0.95),rgba(74,6,10,0.96))]"></div>
                <div class="absolute inset-x-0 bottom-0 h-72 bg-[linear-gradient(135deg,transparent,rgba(214,168,79,0.18))]"></div>
                <div class="absolute left-12 top-32 h-40 w-64 rounded-lg border border-white/10 bg-white/5"></div>
                <div class="absolute bottom-24 right-8 h-48 w-80 rounded-lg border border-white/10 bg-white/5"></div>

                <div class="relative">
                    <a href="{{ route('prism.home') }}" class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-white p-2 shadow-lg">
                        <img src="{{ asset('images/bsu-seal.png') }}" alt="Batangas State University seal" class="h-full w-full object-contain">
                    </a>

                    <div class="mt-10 max-w-2xl">
                        <h1 class="text-5xl font-extrabold leading-none sm:text-6xl">PRISM</h1>
                        <p class="mt-5 text-2xl font-extrabold leading-9 text-white">Procurement Records, Intelligence, Scoping, and Monitoring System for Campus Budget Deliberation and Procurement Compliance</p>
                        <p class="mt-5 max-w-xl text-base leading-8 text-white/75">Secure campus procurement planning, monitoring, and compliance support platform.</p>
                    </div>

                    <div class="mt-10 grid gap-4 text-sm font-bold text-white/90">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-md bg-[#D6A84F]/20 text-[#D6A84F]">
                                <i class="h-5 w-5" data-lucide="sparkles" aria-hidden="true"></i>
                            </span>
                            <span>AI-assisted market scoping</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-md bg-[#D6A84F]/20 text-[#D6A84F]">
                                <i class="h-5 w-5" data-lucide="list-checks" aria-hidden="true"></i>
                            </span>
                            <span>Real-time procurement monitoring</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-md bg-[#D6A84F]/20 text-[#D6A84F]">
                                <i class="h-5 w-5" data-lucide="chart-no-axes-combined" aria-hidden="true"></i>
                            </span>
                            <span>Budget utilization analytics</span>
                        </div>
                    </div>
                </div>

                <p class="relative mt-12 text-sm font-bold text-white/75">Batangas State University TNEU ARASOF-Nasugbu Campus</p>
            </section>

            <section class="flex items-center justify-center px-4 py-10 sm:px-6 lg:px-10">
                <div class="w-full max-w-lg">
                    <div class="rounded-lg border border-[#E5E7EB] bg-white p-6 shadow-xl shadow-slate-200/70 sm:p-8">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-[#7A0C12]/10 text-[#7A0C12]">
                            <i class="h-7 w-7" data-lucide="shield-check" aria-hidden="true"></i>
                        </div>
                        <div class="mt-5 text-center">
                            <h2 class="text-2xl font-extrabold text-slate-950">Sign in to PRISM</h2>
                            <p class="mt-2 text-sm leading-6 text-slate-500">Use your assigned institutional account to continue.</p>
                        </div>

                        <form class="mt-7 grid gap-4" method="POST" action="#">
                            @csrf
                            <label class="grid gap-1.5">
                                <span class="text-sm font-bold text-slate-700">Username or Email</span>
                                <input class="h-11 rounded-md border border-[#E5E7EB] bg-white px-3 text-sm font-medium text-slate-800 outline-none transition focus:border-[#7A0C12] focus:ring-2 focus:ring-[#D6A84F]/35" type="text" name="email" autocomplete="username">
                            </label>
                            <label class="grid gap-1.5">
                                <span class="text-sm font-bold text-slate-700">Password</span>
                                <span class="relative">
                                    <input class="h-11 w-full rounded-md border border-[#E5E7EB] bg-white px-3 pr-16 text-sm font-medium text-slate-800 outline-none transition focus:border-[#7A0C12] focus:ring-2 focus:ring-[#D6A84F]/35" type="password" name="password" autocomplete="current-password">
                                    <button class="absolute right-2 top-1/2 h-8 -translate-y-1/2 rounded-md px-2 text-xs font-extrabold text-slate-500 hover:bg-slate-100 hover:text-[#7A0C12]" type="button" aria-label="Password visibility placeholder">Show</button>
                                </span>
                            </label>

                            <div class="flex items-center justify-between gap-3 text-sm">
                                <label class="inline-flex items-center gap-2 font-semibold text-slate-600">
                                    <input class="h-4 w-4 rounded border-[#E5E7EB] text-[#7A0C12] focus:ring-[#D6A84F]" type="checkbox" name="remember">
                                    Remember me
                                </label>
                                <a class="font-extrabold text-[#7A0C12] hover:underline" href="#">Forgot password?</a>
                            </div>

                            <button class="mt-2 inline-flex h-11 items-center justify-center rounded-md bg-[#7A0C12] px-4 text-sm font-extrabold text-white shadow-sm transition hover:bg-[#4A060A]" type="submit">
                                Login
                            </button>
                        </form>

                        <p class="mt-5 rounded-md border border-[#E5E7EB] bg-[#F8FAFC] px-3 py-3 text-center text-sm font-semibold leading-6 text-slate-600">Account access is managed by the system administrator.</p>
                    </div>

                    <section class="mt-5 rounded-lg border border-[#E5E7EB] bg-white p-5 shadow-sm">
                        <h3 class="text-xs font-extrabold uppercase text-[#7A0C12]">Prototype Demo Access</h3>
                        <div class="mt-4 grid gap-2 sm:grid-cols-2">
                            <a class="inline-flex h-10 items-center justify-center rounded-md border border-[#E5E7EB] bg-[#F8FAFC] px-3 text-sm font-extrabold text-slate-700 transition hover:border-[#7A0C12] hover:bg-[#7A0C12]/5 hover:text-[#7A0C12]" href="{{ route('office-head.dashboard') }}">Office Head / Dean</a>
                            <a class="inline-flex h-10 items-center justify-center rounded-md border border-[#E5E7EB] bg-[#F8FAFC] px-3 text-sm font-extrabold text-slate-700 transition hover:border-[#7A0C12] hover:bg-[#7A0C12]/5 hover:text-[#7A0C12]" href="{{ route('finance-office.dashboard') }}">Finance Office</a>
                            <a class="inline-flex h-10 items-center justify-center rounded-md border border-[#E5E7EB] bg-[#F8FAFC] px-3 text-sm font-extrabold text-slate-700 transition hover:border-[#7A0C12] hover:bg-[#7A0C12]/5 hover:text-[#7A0C12]" href="{{ route('procurement-office.dashboard') }}">Procurement Office</a>
                            <a class="inline-flex h-10 items-center justify-center rounded-md border border-[#E5E7EB] bg-[#F8FAFC] px-3 text-sm font-extrabold text-slate-700 transition hover:border-[#7A0C12] hover:bg-[#7A0C12]/5 hover:text-[#7A0C12]" href="{{ route('chancellor.dashboard') }}">Chancellor</a>
                            <a class="inline-flex h-10 items-center justify-center rounded-md border border-[#E5E7EB] bg-[#F8FAFC] px-3 text-sm font-extrabold text-slate-700 transition hover:border-[#7A0C12] hover:bg-[#7A0C12]/5 hover:text-[#7A0C12] sm:col-span-2" href="{{ route('vice-chancellor.dashboard') }}">Vice Chancellor</a>
                        </div>
                    </section>
                </div>
            </section>
        </main>
    </body>
</html>
