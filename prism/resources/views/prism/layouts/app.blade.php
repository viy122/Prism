<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PRISM') | PRISM</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head-extras')
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --crimson:        #8B1A1C;
            --crimson-dark:   #5C1011;
            --crimson-light:  #C0393B;
            --crimson-pale:   #FDF0F0;
            --crimson-mid:    rgba(139,26,28,.09);
            --crimson-border: rgba(139,26,28,.15);
            --bg:       #F5EEEE;
            --bg2:      #EDE5E5;
            --white:    #FFFFFF;
            --border:   rgba(139,26,28,.10);
            --border2:  rgba(0,0,0,.06);
            --txt:      #1C1010;
            --txt2:     #6B4F50;
            --txt3:     #A88B8C;
            --green:    #166534; --green-bg: #DCFCE7;
            --amber:    #92400E; --amber-bg: #FEF3C7;
            --blue:     #1E40AF; --blue-bg:  #DBEAFE;
            --red:      #991B1B; --red-bg:   #FEE2E2;
            --purple:   #4C1D95; --purple-bg:#EDE9FE;
            --r-sm: 10px; --r: 16px; --r-lg: 22px;
            --sh:  0 1px 4px rgba(139,26,28,.07), 0 1px 2px rgba(0,0,0,.04);
            --sh2: 0 6px 24px rgba(139,26,28,.10), 0 2px 8px rgba(0,0,0,.05);
            --sb-w: 260px;
            --sb-rail: 64px;
            --s200: #e2e8f0; --s400: #94a3b8;
        }

        html { scroll-behavior: smooth; }
        html, body { height: 100%; }
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg);
            color: var(--txt);
            font-size: 13px;
            height: 100vh;
            display: flex;
            overflow: hidden;
        }

        /* ══ SIDEBAR ══ */
        .sb {
            width: var(--sb-w);
            background: var(--white);
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow-y: auto;
            overflow-x: hidden;
            flex-shrink: 0;
            z-index: 60;
            border-right: 1px solid var(--border2);
            box-shadow: 2px 0 16px rgba(139,26,28,.06);
            transition: width .25s cubic-bezier(.4,0,.2,1);
        }

        /* ── Sidebar header row (logo + hamburger) ── */
        .sb-header {
            display: flex; align-items: flex-start; justify-content: flex-end;
            padding: 10px 10px 0; flex-shrink: 0;
        }
        .sb-toggle {
            width: 34px; height: 34px; border: none; background: transparent;
            cursor: pointer; color: var(--txt2); border-radius: var(--r-sm);
            display: flex; align-items: center; justify-content: center;
            transition: color .18s, background .18s; flex-shrink: 0;
        }
        .sb-toggle:hover { color: var(--crimson); background: var(--crimson-mid); }
        .sb-toggle i { font-size: 20px; pointer-events: none; }

        .sb-brand {
            display: flex; flex-direction: column; align-items: center;
            padding: 8px 16px 18px; text-decoration: none; gap: 10px;
        }
        .sb-logo { width: 110px; height: 110px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: width .25s, height .25s; }
        .sb-logo img { width: 100%; height: 100%; object-fit: contain; }
        .sb-divider { height: 1px; background: var(--border2); margin: 0 16px; flex-shrink: 0; }
        .sb-nav { padding: 14px 10px; display: flex; flex-direction: column; gap: 2px; flex: 1; }
        .sb-nav a {
            display: flex; align-items: center; gap: 11px;
            padding: 11px 14px; border-radius: var(--r-sm);
            font-size: 13px; font-weight: 600; color: var(--txt2);
            text-decoration: none; transition: all .18s; position: relative;
            white-space: nowrap;
        }
        .sb-nav a i { font-size: 18px; flex-shrink: 0; color: var(--txt3); transition: color .18s; }
        .sb-nav a:hover { background: var(--crimson-mid); color: var(--crimson); }
        .sb-nav a:hover i { color: var(--crimson); }
        .sb-nav a.active {
            background: var(--crimson); color: #fff; font-weight: 700;
            box-shadow: 0 4px 14px rgba(139,26,28,.30);
        }
        .sb-nav a.active i { color: #fff; }
        .sb-nav a.active::before {
            content: ''; position: absolute; right: -10px; top: 50%;
            transform: translateY(-50%); width: 4px; height: 26px;
            background: var(--crimson); border-radius: 4px 0 0 4px;
        }
        .sb-bottom { padding: 10px 10px 20px; display: flex; flex-direction: column; gap: 8px; flex-shrink: 0; }
        .sb-user {
            display: flex; align-items: center; gap: 10px; padding: 10px 12px;
            background: var(--crimson-mid); border: 1px solid var(--crimson-border); border-radius: var(--r-sm);
            width: 100%; text-align: left; cursor: pointer; font-family: 'Poppins', sans-serif; transition: background .15s;
        }
        .sb-user:hover { background: var(--crimson-border); }
        .sb-avatar {
            width: 34px; height: 34px; border-radius: 50%; background: var(--crimson);
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 800; color: #fff; flex-shrink: 0; overflow: hidden;
        }
        .sb-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .sb-user-info { min-width: 0; overflow: hidden; }
        .sb-user-label { font-size: 9px; font-weight: 700; letter-spacing: .13em; text-transform: uppercase; color: var(--txt3); line-height: 1.3; margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .sb-user-name { font-size: 12px; font-weight: 700; color: var(--crimson); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .sb-logout {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            padding: 10px; border-radius: var(--r-sm);
            border: 1.5px solid var(--crimson-border); background: transparent;
            font-size: 12px; font-weight: 700; color: var(--crimson);
            text-decoration: none; transition: all .2s; font-family: 'Poppins', sans-serif;
            white-space: nowrap; width: 100%; cursor: pointer;
        }
        .sb-logout:hover { background: var(--crimson); color: #fff; border-color: var(--crimson); }
        .sb-logout i { font-size: 16px; flex-shrink: 0; }

        /* ── Collapsed icon-rail state ── */
        body.sb-collapsed .sb { width: var(--sb-rail); }
        body.sb-collapsed .sb-header { justify-content: center; padding: 10px 0 0; }
        body.sb-collapsed .sb-brand { padding: 6px 0 12px; }
        body.sb-collapsed .sb-logo { width: 38px; height: 38px; }
        body.sb-collapsed .sb-divider { margin: 0 8px; }
        body.sb-collapsed .sb-label { display: none; }
        body.sb-collapsed .sb-nav { padding: 14px 6px; }
        body.sb-collapsed .sb-nav a { justify-content: center; padding: 11px 0; gap: 0; }
        body.sb-collapsed .sb-nav a i { font-size: 20px; color: var(--txt3); }
        body.sb-collapsed .sb-nav a.active i { color: #fff; }
        body.sb-collapsed .sb-nav a.active::before { display: none; }
        body.sb-collapsed .sb-bottom { padding: 8px 6px 16px; }
        body.sb-collapsed .sb-user { justify-content: center; padding: 8px 0; background: transparent; border-color: transparent; }
        body.sb-collapsed .sb-user-info { display: none; }
        body.sb-collapsed .sb-logout { padding: 10px 0; gap: 0; border-color: transparent; }
        body.sb-collapsed .sb-logout:hover { border-color: var(--crimson); }

        /* ══ MAIN ══ */
        .main { flex: 1; min-width: 0; display: flex; flex-direction: column; height: 100vh; overflow-y: auto; overflow-x: hidden; }

        /* ── Banner ── */
        .univ-header { position: relative; flex-shrink: 0; line-height: 0; }
        .univ-header img.hdr-bg { width: 100%; height: auto; display: block; object-fit: cover; }
        .univ-header-overlay {
            position: absolute; inset: 0;
            background: linear-gradient(90deg, rgba(139,26,28,.45) 0%, rgba(139,26,28,.15) 60%, rgba(10,30,80,.55) 100%);
        }
        .univ-header-right { position: absolute; right: 22px; top: 50%; transform: translateY(-50%); display: flex; align-items: center; gap: 14px; }
        .univ-date { font-size: 13px; font-weight: 800; color: #fff; letter-spacing: .5px; text-shadow: 0 1px 6px rgba(0,0,0,.4); }
        .univ-time { font-size: 22px; font-weight: 900; color: #fff; letter-spacing: -1px; text-shadow: 0 2px 8px rgba(0,0,0,.4); line-height: 1.1; margin-top: 8px; }

        /* ══ PAGE SHELL ══ */
        .page-shell { flex: 1; display: flex; flex-direction: column; padding: 22px 24px 48px; gap: 16px; }

        /* ── Mobile ── */
        @media (max-width: 720px) {
            html, body { height: auto; overflow: visible; overflow-x: hidden; }
            .sb { display: none; }
            body { display: block; }
            .main { display: block; height: auto; overflow-y: visible; }
            .page-shell { padding: 16px 16px 48px; }
        }
        @media print {
            html, body { height: auto; overflow: visible; }
            .main { height: auto; overflow: visible; }
        }
    </style>
    @stack('page-css')
</head>
<body>

{{-- ══ SIDEBAR ══ --}}
<aside class="sb">
    <div class="sb-header">
        <button class="sb-toggle" id="sbToggleBtn" aria-label="Toggle sidebar">
            <i class="ti ti-menu-2"></i>
        </button>
    </div>
    <a class="sb-brand" href="{{ $brandHref ?? '#' }}">
        <div class="sb-logo">
            <img src="{{ asset('images/prism.png') }}" alt="PRISM logo"
                 onerror="this.style.display='none';this.parentElement.innerHTML='<span style=\'font-size:38px\'>🎓</span>'">
        </div>
    </a>
    <div class="sb-divider"></div>
    <nav class="sb-nav">
        @foreach($moduleNavigation ?? [] as $item)
        <a href="{{ $item['href'] }}" title="{{ $item['label'] }}"
           @if(($activeModulePage ?? '') === $item['slug']) class="active" @endif>
            <i class="ti ti-{{ $item['icon'] }}"></i>
            <span class="sb-label">{{ $item['label'] }}</span>
        </a>
        @endforeach
    </nav>
    <div class="sb-bottom">
        @php
            $__me = auth()->user();
            $__meInitials = $__me
                ? collect(preg_split('/\s+/', trim($__me->name)))->map(fn ($w) => strtoupper(substr($w, 0, 1)))->take(2)->implode('')
                : ($roleInitials ?? '??');
            $__meAvatarUrl = $__me?->avatar_path ? \Illuminate\Support\Facades\Storage::url($__me->avatar_path) : null;
            // Always the real roles held in the DB, not the single hardcoded
            // label each role controller passes — a dual-hat user (e.g. a
            // Vice Chancellor who is also Dean of their home college) holds
            // more than one row in roles(), and every one of them should show
            // here regardless of which role's pages they're currently on.
            $__meRoles = $__me?->roles->pluck('name')->implode(', ') ?: ($roleLabel ?? 'User');
        @endphp
        <button type="button" class="sb-user" id="sbUserBtn" title="Edit your info">
            <div class="sb-avatar">
                @if($__meAvatarUrl)
                    <img src="{{ $__meAvatarUrl }}" alt="" id="sbAvatarImg">
                    <span id="sbAvatarInitials" style="display:none;">{{ $__meInitials }}</span>
                @else
                    <img src="" alt="" id="sbAvatarImg" style="display:none;">
                    <span id="sbAvatarInitials">{{ $__meInitials }}</span>
                @endif
            </div>
            <div class="sb-user-info">
                <div class="sb-user-label" title="{{ $__meRoles }}">{{ $__meRoles }}</div>
                <div class="sb-user-name" id="sbUserName">{{ $__me?->name ?? 'User' }}</div>
            </div>
        </button>
        <form method="POST" action="{{ route('logout') }}" style="margin:0">
            @csrf
            <button type="submit" class="sb-logout" title="Logout">
                <i class="ti ti-logout"></i><span class="sb-label">Logout</span>
            </button>
        </form>
    </div>
</aside>

<x-prism.profile-modal />

{{-- ══ MAIN ══ --}}
<div class="main">

    {{-- University banner with clock --}}
    <div class="univ-header">
        <img class="hdr-bg" src="{{ asset('images/headers2.png') }}" alt="Batangas State University"
             onerror="this.parentElement.style.background='linear-gradient(135deg,#8B1A1C,#1E3A8A)';this.style.display='none'">
        <div class="univ-header-overlay"></div>
        <div class="univ-header-right">
            @include('prism.partials.notification-bell')
            <div>
                <div class="univ-date" id="hdr-date"></div>
                <div class="univ-time" id="hdr-time"></div>
            </div>
        </div>
    </div>

    @yield('content')

</div>{{-- /main --}}

<script>
(function () {
    function updateClock() {
        const now = new Date();
        const dEl = document.getElementById('hdr-date');
        const tEl = document.getElementById('hdr-time');
        if (dEl) dEl.textContent = now.toLocaleDateString('en-PH', { timeZone:'Asia/Manila', weekday:'long', year:'numeric', month:'long', day:'numeric' });
        if (tEl) tEl.textContent = now.toLocaleTimeString('en-PH', { timeZone:'Asia/Manila', hour:'2-digit', minute:'2-digit', second:'2-digit', hour12:true });
    }
    updateClock();
    setInterval(updateClock, 1000);

    const sbBtn = document.getElementById('sbToggleBtn');
    if (sbBtn) {
        if (localStorage.getItem('sb-collapsed') === '1') document.body.classList.add('sb-collapsed');
        sbBtn.addEventListener('click', function () {
            document.body.classList.toggle('sb-collapsed');
            localStorage.setItem('sb-collapsed', document.body.classList.contains('sb-collapsed') ? '1' : '0');
        });
    }

    const sbUserBtn = document.getElementById('sbUserBtn');
    if (sbUserBtn) {
        sbUserBtn.addEventListener('click', function () {
            if (window.prismOpenProfile) window.prismOpenProfile();
        });
    }
})();
</script>

<x-prism.confirm-modal />
<div class="pr-toast" id="globalToast"></div>

@stack('scripts')

</body>
</html>
