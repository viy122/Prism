@extends('prism.layout')

@section('content')
    <section class="mb-5 flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_22px_rgba(15,23,42,0.045)] lg:flex-row lg:items-start lg:justify-between [&_h1]:mt-1.5 [&_h1]:text-2xl [&_h1]:font-extrabold [&_h1]:tracking-tight [&_h1]:text-slate-950 lg:[&_h1]:text-[1.8rem]">
        <div>
            <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-bsu-maroon">User section</p>
            <h1>{{ $sectionTitle }}</h1>
            <p class="mt-2 max-w-3xl text-base leading-7 text-slate-600">This destination is connected for role switching. The Office Head / Dean section is the active build for this PRISM slice.</p>
        </div>
        <a class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-bsu-maroon px-4 text-sm font-bold text-white shadow-sm shadow-bsu-maroon/15 transition hover:bg-bsu-maroon-900 focus:outline-none focus:ring-2 focus:ring-bsu-gold/70" href="{{ route('office-head.dashboard') }}">
            <i data-lucide="arrow-left" aria-hidden="true"></i>
            Office Head / Dean
        </a>
    </section>

    <section class="flex min-h-80 flex-col items-center justify-center gap-4 rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center shadow-[0_8px_22px_rgba(15,23,42,0.05)] [&_svg]:h-12 [&_svg]:w-12 [&_svg]:text-bsu-maroon [&_h2]:text-xl [&_h2]:font-extrabold [&_h2]:text-slate-950 [&_p]:max-w-md [&_p]:text-base [&_p]:leading-7 [&_p]:text-slate-600">
        <i data-lucide="construction" aria-hidden="true"></i>
        <h2>{{ $sectionTitle }} workspace</h2>
        <p>Navigation is ready. This section can be expanded with its own dashboard and approval workflows when needed.</p>
    </section>
@endsection

