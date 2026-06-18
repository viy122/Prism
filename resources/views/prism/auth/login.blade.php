<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | PRISM</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html,
        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
            background: #8B1A1C;
        }

        /* ═══════════════════════════════════════
           LAYOUT
        ═══════════════════════════════════════ */
        .login-wrap {
            display: grid;
            min-height: 100vh;
            grid-template-columns: 1fr;
        }

        @media (min-width: 1024px) {
            .login-wrap {
                grid-template-columns: 1fr 520px;
            }
        }

        /* ═══════════════════════════════════════
           LEFT PANEL
        ═══════════════════════════════════════ */
        .left-panel {
            position: relative;
            background: #8B1A1C;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 40px 52px 44px;
            overflow: hidden;
            color: #fff;
        }

        /* Animated background orbs */
        .orb {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
        }

        .orb-1 {
            width: 520px;
            height: 520px;
            background: radial-gradient(circle, rgba(201, 168, 76, .18) 0%, transparent 70%);
            top: -120px;
            right: -80px;
            animation: orbFloat 8s ease-in-out infinite;
        }

        .orb-2 {
            width: 380px;
            height: 380px;
            background: radial-gradient(circle, rgba(255, 255, 255, .06) 0%, transparent 70%);
            bottom: 40px;
            left: -60px;
            animation: orbFloat 11s ease-in-out infinite reverse;
        }

        .orb-3 {
            width: 220px;
            height: 220px;
            background: radial-gradient(circle, rgba(201, 168, 76, .12) 0%, transparent 70%);
            top: 45%;
            left: 50%;
            animation: orbFloat 7s ease-in-out infinite 2s;
        }

        @keyframes orbFloat {

            0%,
            100% {
                transform: translateY(0) scale(1);
            }

            50% {
                transform: translateY(-28px) scale(1.04);
            }
        }

        /* Grid pattern overlay */
        .grid-overlay {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255, 255, 255, .04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, .04) 1px, transparent 1px);
            background-size: 44px 44px;
            pointer-events: none;
        }

        /* Diagonal accent stripe */
        .stripe {
            position: absolute;
            width: 2px;
            height: 200px;
            background: linear-gradient(to bottom, transparent, rgba(201, 168, 76, .5), transparent);
            animation: stripeSlide 4s ease-in-out infinite;
        }

        .stripe-1 {
            top: 10%;
            right: 25%;
            animation-delay: 0s;
        }

        .stripe-2 {
            top: 30%;
            right: 40%;
            animation-delay: 1.5s;
            height: 130px;
        }

        .stripe-3 {
            top: 60%;
            right: 15%;
            animation-delay: 0.8s;
            height: 90px;
        }

        @keyframes stripeSlide {

            0%,
            100% {
                opacity: .3;
                transform: scaleY(.8);
            }

            50% {
                opacity: 1;
                transform: scaleY(1);
            }
        }

        /* Logo */
        .left-logo {
            position: relative;
            display: flex;
            align-items: center;
            gap: 14px;
            animation: fadeSlideUp .7s ease both;
        }

        .left-logo-img {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: #fff;
            padding: 7px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, .25);
            flex-shrink: 0;
            transition: transform .3s;
        }

        .left-logo-img:hover {
            transform: scale(1.06);
        }

        .left-logo-img img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .left-logo-text {
            font-size: 22px;
            font-weight: 900;
            color: #fff;
            letter-spacing: .5px;
        }

        .left-logo-sub {
            font-size: 11px;
            font-weight: 600;
            color: rgba(255, 255, 255, .5);
            letter-spacing: .08em;
            margin-top: 1px;
        }

        /* Hero text */
        .left-hero {
            position: relative;
            animation: fadeSlideUp .7s ease .1s both;
        }

        .hero-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .14em;
            color: #c9a84c;
            margin-bottom: 20px;
        }

        .hero-eyebrow span {
            display: inline-block;
            width: 28px;
            height: 2px;
            background: #c9a84c;
            border-radius: 2px;
        }

        .hero-title {
            font-size: clamp(2.8rem, 5vw, 4.2rem);
            font-weight: 900;
            line-height: 1;
            color: #fff;
            letter-spacing: -2px;
            margin-bottom: 18px;
        }

        .hero-title em {
            font-style: normal;
            color: #c9a84c;
        }

        .hero-desc {
            font-size: 14px;
            font-weight: 500;
            line-height: 1.75;
            color: rgba(255, 255, 255, .65);
            max-width: 460px;
        }

        /* Feature bullets */
        .left-features {
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 14px;
            animation: fadeSlideUp .7s ease .2s both;
        }

        .feat-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 18px;
            border-radius: 14px;
            background: rgba(255, 255, 255, .06);
            border: 1px solid rgba(255, 255, 255, .08);
            transition: background .2s, border-color .2s, transform .2s;
            cursor: default;
        }

        .feat-item:hover {
            background: rgba(201, 168, 76, .1);
            border-color: rgba(201, 168, 76, .25);
            transform: translateX(4px);
        }

        .feat-icon {
            width: 38px;
            height: 38px;
            flex-shrink: 0;
            border-radius: 10px;
            background: rgba(201, 168, 76, .15);
            border: 1px solid rgba(201, 168, 76, .2);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .feat-icon svg {
            width: 18px;
            height: 18px;
            stroke: #c9a84c;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .feat-label {
            font-size: 13px;
            font-weight: 600;
            color: rgba(255, 255, 255, .85);
        }

        /* Footer */
        .left-foot {
            position: relative;
            font-size: 12px;
            font-weight: 500;
            color: rgba(255, 255, 255, .35);
            animation: fadeSlideUp .7s ease .3s both;
        }

        /* ═══════════════════════════════════════
           RIGHT PANEL
        ═══════════════════════════════════════ */
        .right-panel {
            background: #f4eded;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 28px;
            position: relative;
        }

        .right-panel::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(160deg, #f8f4f4 0%, #f0e9e9 100%);
        }

        .form-box {
            position: relative;
            width: 100%;
            max-width: 420px;
            animation: fadeSlideUp .65s ease .15s both;
        }

        /* Header */
        .form-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .shield-wrap {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            border-radius: 18px;
            background: #fff;
            border: 1.5px solid rgba(139, 26, 28,.12);
            box-shadow: 0 4px 18px rgba(139, 26, 28,.1);
            margin-bottom: 18px;
            transition: transform .3s, box-shadow .3s;
        }

        .shield-wrap:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 28px rgba(139, 26, 28,.15);
        }

        .shield-wrap svg {
            width: 26px;
            height: 26px;
            stroke: #8B1A1C;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .form-title {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -.4px;
        }

        .form-sub {
            font-size: 13px;
            color: #64748b;
            margin-top: 6px;
            line-height: 1.5;
        }

        /* Card */
        .form-card {
            background: #fff;
            border: 1px solid rgba(139, 26, 28,.1);
            border-radius: 20px;
            padding: 32px 28px 28px;
            box-shadow: 0 8px 40px rgba(139, 26, 28,.08), 0 2px 8px rgba(139, 26, 28,.04);
        }

        /* Form fields */
        .field {
            margin-bottom: 18px;
        }

        .field label {
            display: block;
            font-size: 11.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #475569;
            margin-bottom: 8px;
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .input-icon svg {
            width: 16px;
            height: 16px;
            stroke: #94a3b8;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
            transition: stroke .2s;
        }

        .field-input {
            width: 100%;
            height: 46px;
            border-radius: 12px;
            border: 1.5px solid #e2e8f0;
            background: #f8fafc;
            padding: 0 40px 0 42px;
            font-size: 14px;
            font-weight: 500;
            color: #0f172a;
            font-family: 'Poppins', sans-serif;
            outline: none;
            transition: border-color .2s, box-shadow .2s, background .2s;
        }

        .field-input::placeholder {
            color: #cbd5e1;
            font-weight: 400;
        }

        .field-input:hover {
            border-color: #cbd5e1;
            background: #fff;
        }

        .field-input:focus {
            border-color: #8B1A1C;
            background: #fff;
            box-shadow: 0 0 0 3.5px rgba(139, 26, 28,.1);
        }

        .field-input:focus~.input-icon svg,
        .input-wrap:focus-within .input-icon svg {
            stroke: #8B1A1C;
        }

        /* Show/hide button */
        .pw-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 6px 8px;
            border-radius: 8px;
            color: #94a3b8;
            font-size: 11px;
            font-weight: 700;
            font-family: 'Poppins', sans-serif;
            letter-spacing: .04em;
            transition: background .15s, color .15s;
        }

        .pw-toggle:hover {
            background: #f1f5f9;
            color: #8B1A1C;
        }

        /* Extras row */
        .extras-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 22px;
        }

        .remember-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            cursor: pointer;
        }

        .remember-label input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #8B1A1C;
            cursor: pointer;
        }

        .forgot-link {
            font-size: 12px;
            font-weight: 700;
            color: #8B1A1C;
            text-decoration: none;
            transition: opacity .15s;
        }

        .forgot-link:hover {
            opacity: .75;
            text-decoration: underline;
        }

        /* Login button */
        .btn-login {
            width: 100%;
            height: 48px;
            border-radius: 12px;
            background: #8B1A1C;
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            font-family: 'Poppins', sans-serif;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            position: relative;
            overflow: hidden;
            transition: opacity .2s, transform .15s, box-shadow .2s;
            box-shadow: 0 4px 16px rgba(139, 26, 28,.3);
        }

        .btn-login::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0);
            transition: background .2s;
        }

        .btn-login:hover {
            opacity: .9;
            box-shadow: 0 6px 22px rgba(139, 26, 28,.38);
        }

        .btn-login:active {
            transform: scale(.98);
        }

        .btn-login svg {
            width: 16px;
            height: 16px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2.5;
            stroke-linecap: round;
            stroke-linejoin: round;
            transition: transform .2s;
        }

        .btn-login:hover svg {
            transform: translateX(3px);
        }

        /* Loading state */
        .btn-login.loading {
            pointer-events: none;
        }

        .btn-login.loading .btn-text {
            opacity: 0;
        }

        .spinner {
            position: absolute;
            width: 20px;
            height: 20px;
            border: 2.5px solid rgba(255, 255, 255, .3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .7s linear infinite;
            opacity: 0;
            transition: opacity .2s;
        }

        .btn-login.loading .spinner {
            opacity: 1;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Info note */
        .info-note {
            margin-top: 16px;
            padding: 12px 14px;
            border-radius: 10px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            font-size: 12px;
            font-weight: 500;
            color: #64748b;
            text-align: center;
            line-height: 1.55;
        }

        /* Demo access */
        .demo-box {
            margin-top: 20px;
            background: #fff;
            border: 1px solid rgba(139, 26, 28,.1);
            border-radius: 16px;
            padding: 20px 22px;
            box-shadow: 0 2px 12px rgba(139, 26, 28,.05);
        }

        .demo-eyebrow {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .14em;
            color: #8B1A1C;
            margin-bottom: 14px;
        }

        .demo-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .demo-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            height: 40px;
            border-radius: 10px;
            border: 1.5px solid #e2e8f0;
            background: #f8fafc;
            font-size: 12px;
            font-weight: 700;
            color: #334155;
            font-family: 'Poppins', sans-serif;
            text-decoration: none;
            transition: border-color .15s, background .15s, color .15s, transform .15s;
        }

        .demo-btn:hover {
            border-color: #8B1A1C;
            background: rgba(139, 26, 28,.04);
            color: #8B1A1C;
            transform: translateY(-1px);
        }

        .demo-btn.full {
            grid-column: 1 / -1;
        }

        .demo-btn svg {
            width: 13px;
            height: 13px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        /* ═══ Animations ═══ */
        @keyframes fadeSlideUp {
            from {
                opacity: 0;
                transform: translateY(22px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Input icon position fix */
        .input-wrap {
            position: relative;
        }

        .input-wrap .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            z-index: 1;
        }

        /* Error state */
        .field-input.error {
            border-color: #e24b4b;
            box-shadow: 0 0 0 3px rgba(226, 75, 75, .1);
        }

        .field-error {
            font-size: 11px;
            color: #a32d2d;
            font-weight: 600;
            margin-top: 5px;
            display: none;
        }

        .field-error.show {
            display: block;
        }

        /* Focus visible ring for accessibility */
        .btn-login:focus-visible,
        .demo-btn:focus-visible {
            outline: 2.5px solid #c9a84c;
            outline-offset: 2px;
        }

        @media (max-width: 1023px) {
            .left-panel {
                display: none;
            }

            .right-panel {
                background: linear-gradient(160deg, #f8f4f4 0%, #f0e9e9 100%);
                min-height: 100vh;
            }
        }
    </style>
</head>

<body>

    <div class="login-wrap">

        {{-- ═══ LEFT PANEL ═══ --}}
        <section class="left-panel">
            <div class="grid-overlay"></div>
            <div class="orb orb-1"></div>
            <div class="orb orb-2"></div>
            <div class="orb orb-3"></div>
            <div class="stripe stripe-1"></div>
            <div class="stripe stripe-2"></div>
            <div class="stripe stripe-3"></div>

            <a class="left-logo" href="{{ route('prism.home') }}">
                <div class="left-logo-img">
                    <img src="{{ asset('images/bsu-seal.png') }}" alt="BSU seal"
                        onerror="this.parentElement.innerHTML='🎓'">
                </div>
                <div>
                    <p class="left-logo-text">PRISM</p>
                    <p class="left-logo-sub">Batangas State University</p>
                </div>
            </a>

            <div class="left-hero">
                <p class="hero-eyebrow"><span></span>Procurement Management</p>
                <h1 class="hero-title">Smart<br>Procurement,<br><em>Simplified.</em></h1>
                <p class="hero-desc">Secure campus procurement planning, monitoring, and compliance support — all in one intelligent platform.</p>
            </div>

            <div class="left-features">
                <div class="feat-item">
                    <div class="feat-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                        </svg>
                    </div>
                    <span class="feat-label">AI-assisted market scoping</span>
                </div>
                <div class="feat-item">
                    <div class="feat-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M9 11l3 3L22 4" />
                            <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11" />
                        </svg>
                    </div>
                    <span class="feat-label">Real-time procurement monitoring</span>
                </div>
                <div class="feat-item">
                    <div class="feat-icon">
                        <svg viewBox="0 0 24 24">
                            <line x1="18" y1="20" x2="18" y2="10" />
                            <line x1="12" y1="20" x2="12" y2="4" />
                            <line x1="6" y1="20" x2="6" y2="14" />
                        </svg>
                    </div>
                    <span class="feat-label">Budget utilization analytics</span>
                </div>
            </div>

            <p class="left-foot">© {{ date('Y') }} Batangas State University TNEU ARASOF-Nasugbu Campus</p>
        </section>

        {{-- ═══ RIGHT PANEL ═══ --}}
        <section class="right-panel">
            <div class="form-box">

                <div class="form-header">
                    <div class="shield-wrap">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                            <polyline points="9 12 11 14 15 10" />
                        </svg>
                    </div>
                    <h2 class="form-title">Sign in to PRISM</h2>
                    <p class="form-sub">Use your assigned institutional account to continue.</p>
                </div>

                <div class="form-card">
                    <form id="loginForm" method="POST" action="{{ route('login.post') }}">
                        @csrf

                        <div class="field">
                            <label for="emailInput">Username or Email</label>
                            <div class="input-wrap">
                                <span class="input-icon">
                                    <svg viewBox="0 0 24 24">
                                        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                                        <circle cx="12" cy="7" r="4" />
                                    </svg>
                                </span>
                                <input class="field-input" type="text" id="emailInput" name="email" autocomplete="username" placeholder="Enter your username or email" value="{{ old('email') }}">
                            </div>
                            @error('email')
                            <p class="field-error" style="display: block;">{{ $message }}</p>
                            @else
                            <p class="field-error" id="emailError">Please enter your username or email.</p>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="passwordInput">Password</label>
                            <div class="input-wrap">
                                <span class="input-icon">
                                    <svg viewBox="0 0 24 24">
                                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                        <path d="M7 11V7a5 5 0 0110 0v4" />
                                    </svg>
                                </span>
                                <input class="field-input" type="password" id="passwordInput" name="password" autocomplete="current-password" placeholder="Enter your password">
                                <button class="pw-toggle" type="button" id="pwToggle" aria-label="Toggle password visibility">Show</button>
                            </div>
                            @error('password')
                            <p class="field-error" style="display: block;">{{ $message }}</p>
                            @else
                            <p class="field-error" id="passwordError">Please enter your password.</p>
                            @enderror
                        </div>

                        <div class="extras-row">
                            <label class="remember-label">
                                <input type="checkbox" name="remember">
                                Remember me
                            </label>
                            <a class="forgot-link" href="#">Forgot password?</a>
                        </div>

                        <button class="btn-login" type="submit" id="loginBtn">
                            <div class="spinner"></div>
                            <span class="btn-text" style="display:flex;align-items:center;gap:8px;">
                                Login
                                <svg viewBox="0 0 24 24">
                                    <line x1="5" y1="12" x2="19" y2="12" />
                                    <polyline points="12 5 19 12 12 19" />
                                </svg>
                            </span>
                        </button>
                    </form>

                    <p class="info-note">Account access is managed by the system administrator.</p>
                </div>

                <div class="demo-box">
                    <p class="demo-eyebrow">Prototype Demo Access</p>
                    <div class="demo-grid">
                        <button class="demo-btn" type="button" onclick="demoLogin('office_head')">
                            <svg viewBox="0 0 24 24">
                                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                                <path d="M23 21v-2a4 4 0 00-3-3.87" />
                                <path d="M16 3.13a4 4 0 010 7.75" />
                            </svg>
                            Office Head / Dean
                        </button>
                        <button class="demo-btn" type="button" onclick="demoLogin('finance')">
                            <svg viewBox="0 0 24 24">
                                <line x1="12" y1="1" x2="12" y2="23" />
                                <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" />
                            </svg>
                            Finance Office
                        </button>
                        <button class="demo-btn" type="button" onclick="demoLogin('procurement')">
                            <svg viewBox="0 0 24 24">
                                <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" />
                                <rect x="9" y="3" width="6" height="4" rx="1" />
                            </svg>
                            Procurement Office
                        </button>
                        <button class="demo-btn" type="button" onclick="demoLogin('chancellor')">
                            <svg viewBox="0 0 24 24">
                                <circle cx="12" cy="8" r="6" />
                                <path d="M15.477 12.89L17 22l-5-3-5 3 1.523-9.11" />
                            </svg>
                            Chancellor
                        </button>
                        <button class="demo-btn full" type="button" onclick="demoLogin('vice_chancellor')">
                            <svg viewBox="0 0 24 24">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                            </svg>
                            Vice Chancellor
                        </button>
                    </div>
                </div>

            </div>
        </section>

    </div>

    <script>
        (function() {
            const pwInput = document.getElementById('passwordInput');
            const pwToggle = document.getElementById('pwToggle');
            const form = document.getElementById('loginForm');
            const loginBtn = document.getElementById('loginBtn');
            const emailInput = document.getElementById('emailInput');
            const emailError = document.getElementById('emailError');
            const passwordError = document.getElementById('passwordError');

            /* Show / hide password */
            pwToggle.addEventListener('click', () => {
                const shown = pwInput.type === 'text';
                pwInput.type = shown ? 'password' : 'text';
                pwToggle.textContent = shown ? 'Show' : 'Hide';
            });

            /* Clear errors on input */
            emailInput.addEventListener('input', () => {
                emailInput.classList.remove('error');
                emailError.classList.remove('show');
            });
            pwInput.addEventListener('input', () => {
                pwInput.classList.remove('error');
                passwordError.classList.remove('show');
            });

            /* Form submit — loading state demo */
            form.addEventListener('submit', function(e) {
                let valid = true;

                if (!emailInput.value.trim()) {
                    emailInput.classList.add('error');
                    emailError.classList.add('show');
                    valid = false;
                }
                if (!pwInput.value.trim()) {
                    pwInput.classList.add('error');
                    passwordError.classList.add('show');
                    valid = false;
                }

                if (!valid) {
                    e.preventDefault();
                    return;
                }

                loginBtn.classList.add('loading');
            });

            /* Demo login auto-fill */
            window.demoLogin = function(username) {
                emailInput.value = username;
                pwInput.value = 'password';
                emailInput.classList.remove('error');
                pwInput.classList.remove('error');
                loginBtn.classList.add('loading');
                form.submit();
            };

            /* Staggered feature item entrance */
            const featItems = document.querySelectorAll('.feat-item');
            featItems.forEach((el, i) => {
                el.style.opacity = '0';
                el.style.transform = 'translateX(-12px)';
                el.style.transition = 'opacity .5s ease, transform .5s ease';
                setTimeout(() => {
                    el.style.opacity = '1';
                    el.style.transform = 'translateX(0)';
                }, 400 + i * 120);
            });
        })();
    </script>

</body>

</html>