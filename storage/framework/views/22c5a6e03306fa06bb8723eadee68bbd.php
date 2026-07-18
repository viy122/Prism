<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | PRISM</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .login-page {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: 
            hidden;
            padding: 24px;
        }

        .page-bg {
            position: absolute;
            inset: 0;
            background-image: url('<?php echo e(asset("images/login1.png")); ?>');
            background-size: cover;
            background-position: center 80%;
            background-attachment: fixed;
            z-index: 0;
            transform-origin: center;
            animation: subtlePan 30s ease-in-out infinite alternate;
        }

        @keyframes subtlePan {
            0% { transform: scale(1.04) translate(0px, 0px); }
            50% { transform: scale(1.04) translate(-8px, -4px); }
            100% { transform: scale(1.04) translate(6px, 4px); }
        }

        .page-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom,
                    rgba(30, 6, 7, 0.55) 0%,
                    rgba(20, 4, 5, 0.65) 100%);
            z-index: 1;
        }

        .page-vignette {
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse at center, transparent 55%, rgba(0, 0, 0, 0.28) 100%);
            z-index: 1;
            pointer-events: none;
        }

        .page-bar {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(to right, #c9a84c, #f0d080, #c9a84c);
            z-index: 10;
            animation: barGlow 3s ease-in-out infinite;
        }

        @keyframes barGlow {
            0%, 100% { opacity: .85; }
            50% { opacity: 1; }
        }

        .particles {
            position: absolute;
            inset: 0;
            z-index: 1;
            pointer-events: none;
            overflow: hidden;
        }

        .particle {
            position: absolute;
            border-radius: 50%;
            background: rgba(240, 208, 128, 0.18);
            animation: floatUp linear infinite;
        }

        @keyframes floatUp {
            0% { transform: translateY(100vh) scale(0); opacity: 0; }
            10% { opacity: .7; }
            90% { opacity: .15; }
            100% { transform: translateY(-120px) scale(1.5); opacity: 0; }
        }

        /* ── UNIFIED CARD ── */
        .auth-card {
            position: relative;
            z-index: 2;
            display: flex;
            width: 100%;
            max-width: 860px;
            min-height: 520px;
            border-radius: 24px;
            overflow: hidden;
            box-shadow:
                0 24px 60px rgba(0, 0, 0, 0.35),
                0 6px 20px rgba(0, 0, 0, 0.18);
            opacity: 0;
            transform: translateY(20px);
            animation: cardIn .7s ease .15s forwards;
        }

        @keyframes cardIn {
            to { opacity: 1; transform: translateY(0); }
        }

        /* ── LEFT PANEL (info) ── */
        .auth-left {
            flex: 0 0 42%;
            background: linear-gradient(160deg, #6f1214 0%, #4a0c0e 100%);
            color: #fff;
            padding: 44px 38px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
        }

        .auth-left::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            width: 1px;
            background: rgba(255, 255, 255, 0.12);
        }

        .left-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .left-logo-img {
            width: 52px;
            height: 52px;
            background: #fff;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.2);
        }

        .left-logo-img img {
            width: 82%;
            height: 82%;
            object-fit: contain;
        }

        .left-logo-name {
            font-size: 18px;
            font-weight: 900;
            letter-spacing: .4px;
        }

        .left-logo-sub {
            font-size: 9.5px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.55);
            letter-spacing: .06em;
        }

        .left-body {
            margin-top: 30px;
        }

        .left-eyebrow {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 11px;
            font-weight: 700;
            color: #f0d080;
            letter-spacing: .12em;
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        .left-eyebrow::before {
            content: '';
            width: 20px;
            height: 2px;
            background: #f0d080;
            border-radius: 2px;
        }

        .left-title {
            font-size: 30px;
            font-weight: 800;
            line-height: 1.2;
            letter-spacing: -.5px;
            margin-bottom: 14px;
        }

        .left-title .dot {
            color: #f0d080;
        }

        .left-desc {
            font-size: 12.5px;
            line-height: 1.75;
            color: rgba(255, 255, 255, 0.7);
            max-width: 300px;
        }

        .left-desc strong {
            color: #fff;
            font-weight: 700;
        }

        .left-footer {
            font-size: 10px;
            color: rgba(255, 255, 255, 0.4);
            font-weight: 500;
        }

        /* ── RIGHT PANEL (form) ── */
        .auth-right {
            flex: 1;
            background: #ffffff;
            padding: 44px 42px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .card-h1 {
            font-size: 26px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -.5px;
        }

        .card-h1 span { color: #681012; }

        .card-sub {
            font-size: 12px;
            color: #94a3b8;
            font-weight: 500;
            margin-top: 4px;
            margin-bottom: 22px;
        }

        .field { margin-bottom: 13px; }
        .input-wrap { position: relative; }

        .input-icon {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            width: 32px;
            height: 32px;
            background: #f0e8e8;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
            transition: background .2s;
        }

        .input-icon svg {
            width: 14px;
            height: 14px;
            stroke: #681012;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .field-input {
            width: 100%;
            height: 48px;
            border-radius: 13px;
            border: 1.5px solid #e8e0e0;
            background: #faf6f6;
            padding: 0 44px 0 57px;
            font-size: 13.5px;
            font-weight: 500;
            color: #0f172a;
            font-family: 'Poppins', sans-serif;
            outline: none;
            transition: border-color .2s, box-shadow .2s, background .2s;
        }

        .field-input::placeholder { color: #b8a8a8; font-weight: 400; }
        .field-input:hover { border-color: #d0c4c4; background: #fff; }
        .field-input:focus {
            border-color: #681012;
            background: #fff;
            box-shadow: 0 0 0 3.5px rgba(104, 16, 18, 0.1);
        }
        .field-input:focus ~ .input-icon { background: #f8e0e0; }

        .pw-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #94a3b8;
            padding: 5px;
            border-radius: 7px;
            transition: color .15s, background .15s;
        }

        .pw-toggle:hover { color: #681012; background: #f1f5f9; }
        .pw-toggle svg {
            width: 16px; height: 16px;
            stroke: currentColor; fill: none;
            stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;
            display: block;
        }

        .field-input.error {
            border-color: #e24b4b;
            box-shadow: 0 0 0 3px rgba(226, 75, 75, 0.1);
        }

        .field-error {
            font-size: 11px;
            color: #a32d2d;
            font-weight: 600;
            margin-top: 4px;
            display: none;
        }
        .field-error.show { display: block; }

        .extras-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 6px 0 18px;
        }

        .remember-label {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: 12px;
            font-weight: 500;
            color: #475569;
            cursor: pointer;
        }

        .remember-label input {
            width: 14px; height: 14px;
            accent-color: #681012;
            cursor: pointer;
        }

        .forgot-link {
            font-size: 12px;
            font-weight: 700;
            color: #681012;
            text-decoration: none;
            transition: opacity .15s;
        }
        .forgot-link:hover { opacity: .75; text-decoration: underline; }

        .btn-login {
            width: 100%;
            height: 50px;
            border-radius: 13px;
            background: #681012;
            color: #fff;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 700;
            font-family: 'Poppins', sans-serif;
            display: flex;
            align-items: center;
            padding: 0 10px 0 20px;
            gap: 10px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 5px 18px rgba(104, 16, 18, 0.35);
            transition: opacity .2s, transform .15s, box-shadow .2s;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to right, transparent 0%, rgba(255, 255, 255, 0.1) 50%, transparent 100%);
            transform: translateX(-100%);
            transition: transform .4s;
        }
        .btn-login:hover::before { transform: translateX(100%); }
        .btn-login:hover {
            opacity: .92;
            box-shadow: 0 8px 26px rgba(104, 16, 18, 0.45);
        }
        .btn-login:active { transform: scale(.98); }

        .btn-login-text { flex: 1; text-align: left; }

        .btn-arrow {
            width: 34px; height: 34px;
            flex-shrink: 0;
            background: rgba(255, 255, 255, 0.18);
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background .2s;
        }
        .btn-login:hover .btn-arrow { background: rgba(255, 255, 255, 0.28); }
        .btn-arrow svg {
            width: 14px; height: 14px;
            stroke: #fff; fill: none;
            stroke-width: 2.5; stroke-linecap: round; stroke-linejoin: round;
            transition: transform .2s;
        }
        .btn-login:hover .btn-arrow svg { transform: translateX(2px); }
        .btn-login.loading { pointer-events: none; }
        .btn-login.loading .btn-login-text,
        .btn-login.loading .btn-arrow { opacity: 0; }

        .spinner {
            position: absolute;
            width: 20px; height: 20px;
            border: 2.5px solid rgba(255, 255, 255, 0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .7s linear infinite;
            opacity: 0;
            transition: opacity .2s;
        }
        .btn-login.loading .spinner { opacity: 1; }
        @keyframes spin { to { transform: rotate(360deg); } }

        .demo-box {
            margin-top: 16px;
            border: 1.5px solid #ede4e4;
            border-radius: 15px;
            padding: 14px 16px;
        }

        .demo-eyebrow {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .14em;
            color: #681012;
            margin-bottom: 10px;
        }

        .demo-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }

        .demo-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            height: 36px;
            border-radius: 9px;
            border: 1.5px solid #ede4e4;
            background: #faf7f7;
            font-size: 11px;
            font-weight: 700;
            color: #334155;
            font-family: 'Poppins', sans-serif;
            text-decoration: none;
            transition: border-color .15s, background .15s, color .15s, transform .15s;
        }
        .demo-btn:hover {
            border-color: #681012;
            background: rgba(104, 16, 18, 0.04);
            color: #681012;
            transform: translateY(-1px);
        }
        .demo-btn.full { grid-column: 1 / -1; }
        .demo-btn svg {
            width: 11px; height: 11px;
            stroke: currentColor; fill: none;
            stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 760px) {
            .auth-card {
                flex-direction: column;
                max-width: 420px;
            }
            .auth-left {
                flex: none;
                padding: 32px 30px;
            }
            .auth-left::after {
                width: auto;
                height: 1px;
                right: 0; left: 0; bottom: 0; top: auto;
            }
            .left-body { margin-top: 20px; }
            .left-footer { display: none; }
            .auth-right { padding: 32px 28px; }
        }
    </style>
</head>

<body>

    <div class="login-page">
        <div class="page-bg"></div>
        <div class="page-overlay"></div>
        <div class="page-vignette"></div>
        <div class="page-bar"></div>
        <div class="particles" id="particles"></div>

        <div class="auth-card">

            
            <div class="auth-left">
                <a class="left-logo" href="<?php echo e(route('prism.home')); ?>">
                    <div class="left-logo-img">
                        <img src="<?php echo e(asset('images/prism3.png')); ?>" alt="PRISM">
                    </div>
                    <div>
                        <p class="left-logo-name">PRISM</p>
                        <p class="left-logo-sub">Batangas State University</p>
                    </div>
                </a>

                <div class="left-body">
                    <p class="left-eyebrow">Welcome Back</p>
                    <h1 class="left-title">Your procurement<br>command center<span class="dot">.</span></h1>
                    <p class="left-desc">
                        <strong>PRISM</strong> centralizes procurement workflows, budget monitoring,
                        and compliance tracking for the university.
                    </p>
                </div>

                <p class="left-footer">&copy; <?php echo e(date('Y')); ?> Batangas State University</p>
            </div>

            
            <div class="auth-right">
                <h1 class="card-h1">Log <span>in</span></h1>
                <p class="card-sub">Enter your credentials to continue</p>

                <form id="loginForm" method="POST" action="<?php echo e(route('login')); ?>">
                    <?php echo csrf_field(); ?>

                    <div class="field">
                        <div class="input-wrap">
                            <div class="input-icon">
                                <svg viewBox="0 0 24 24">
                                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                                    <circle cx="12" cy="7" r="4" />
                                </svg>
                            </div>
                            <input class="field-input" type="text" id="emailInput" name="email"
                                autocomplete="username" placeholder="Username"
                                value="<?php echo e(old('email')); ?>">
                        </div>
                        <p class="field-error" id="emailError">Please enter your username or email.</p>
                    </div>

                    <div class="field">
                        <div class="input-wrap">
                            <div class="input-icon">
                                <svg viewBox="0 0 24 24">
                                    <rect x="3" y="11" width="18" height="11" rx="2" />
                                    <path d="M7 11V7a5 5 0 0110 0v4" />
                                </svg>
                            </div>
                            <input class="field-input" type="password" id="passwordInput" name="password"
                                autocomplete="current-password" placeholder="Password">
                            <button class="pw-toggle" type="button" id="pwToggle" aria-label="Toggle password">
                                <svg id="eyeIcon" viewBox="0 0 24 24">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                            </button>
                        </div>
                        <p class="field-error" id="passwordError">Please enter your password.</p>
                    </div>

                    <div class="extras-row">
                        <label class="remember-label">
                            <input type="checkbox" name="remember"> Remember Me
                        </label>
                        <a class="forgot-link" href="#">Forgot Password?</a>
                    </div>

                    <button class="btn-login" type="submit" id="loginBtn">
                        <div class="spinner"></div>
                        <span class="btn-login-text">Log In</span>
                        <span class="btn-arrow">
                            <svg viewBox="0 0 24 24">
                                <line x1="5" y1="12" x2="19" y2="12" />
                                <polyline points="12 5 19 12 12 19" />
                            </svg>
                        </span>
                    </button>
                </form>

                <div class="demo-box">
                    <p class="demo-eyebrow">Prototype Demo Access</p>
                    <div class="demo-grid">
                        <a class="demo-btn" href="<?php echo e(route('demo.login', 'office-head')); ?>">
                            <svg viewBox="0 0 24 24">
                                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                                <path d="M23 21v-2a4 4 0 00-3-3.87" />
                                <path d="M16 3.13a4 4 0 010 7.75" />
                            </svg>
                            Office Head
                        </a>
                        <a class="demo-btn" href="<?php echo e(route('demo.login', 'finance-office')); ?>">
                            <svg viewBox="0 0 24 24">
                                <line x1="12" y1="1" x2="12" y2="23" />
                                <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" />
                            </svg>
                            Finance
                        </a>
                        <a class="demo-btn" href="<?php echo e(route('demo.login', 'procurement-office')); ?>">
                            <svg viewBox="0 0 24 24">
                                <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" />
                                <rect x="9" y="3" width="6" height="4" rx="1" />
                            </svg>
                            Procurement
                        </a>
                        <a class="demo-btn" href="<?php echo e(route('demo.login', 'chancellor')); ?>">
                            <svg viewBox="0 0 24 24">
                                <circle cx="12" cy="8" r="6" />
                                <path d="M15.477 12.89L17 22l-5-3-5 3 1.523-9.11" />
                            </svg>
                            Chancellor
                        </a>
                        <a class="demo-btn full" href="<?php echo e(route('demo.login', 'vice-chancellor')); ?>">
                            <svg viewBox="0 0 24 24">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                            </svg>
                            Vice Chancellor
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        (function() {
            const container = document.getElementById('particles');
            for (let i = 0; i < 14; i++) {
                const p = document.createElement('div');
                p.className = 'particle';
                const size = Math.random() * 10 + 4;
                p.style.cssText = `
            width: ${size}px;
            height: ${size}px;
            left: ${Math.random() * 65}%;
            bottom: -20px;
            animation-duration: ${Math.random() * 10 + 7}s;
            animation-delay: ${Math.random() * 8}s;
        `;
                container.appendChild(p);
            }

            const pwInput = document.getElementById('passwordInput');
            const pwToggle = document.getElementById('pwToggle');
            const eyeIcon = document.getElementById('eyeIcon');

            pwToggle.addEventListener('click', () => {
                const shown = pwInput.type === 'text';
                pwInput.type = shown ? 'password' : 'text';
                eyeIcon.innerHTML = shown ?
                    '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>' :
                    '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
            });

            const emailInput = document.getElementById('emailInput');
            const emailError = document.getElementById('emailError');
            const passwordError = document.getElementById('passwordError');
            const loginBtn = document.getElementById('loginBtn');
            const form = document.getElementById('loginForm');

            emailInput.addEventListener('input', () => {
                emailInput.classList.remove('error');
                emailError.classList.remove('show');
            });
            pwInput.addEventListener('input', () => {
                pwInput.classList.remove('error');
                passwordError.classList.remove('show');
            });

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
        })();
    </script>

</body>

</html><?php /**PATH C:\xampp\htdocs\Prism\resources\views/prism/auth/login.blade.php ENDPATH**/ ?>