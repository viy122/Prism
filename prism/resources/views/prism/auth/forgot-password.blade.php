<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password | PRISM</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { font-family: 'Poppins', sans-serif; min-height: 100vh; overflow-x: hidden; }
        .auth-page { position: relative; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; overflow: hidden; }

        .page-bg {
            position: absolute;
            inset: 0;
            background-image: url('{{ asset("images/prism3.png") }}');
            background-size: cover;
            background-position: center 80%;
            background-attachment: fixed;
            z-index: 0;
            transform: scale(1.04);
        }

        .page-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom, rgba(30, 6, 7, 0.55) 0%, rgba(20, 4, 5, 0.65) 100%);
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
            bottom: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(to right, #c9a84c, #f0d080, #c9a84c);
            z-index: 10;
        }

        .auth-card { position: relative; z-index: 2; width: 100%; max-width: 420px; background: #fff; border-radius: 20px; padding: 40px 36px; box-shadow: 0 24px 60px rgba(0,0,0,0.35); }
        .card-h1 { font-size: 22px; font-weight: 800; color: #0f172a; text-align: center; }
        .card-sub { font-size: 12.5px; color: #94a3b8; font-weight: 500; margin-top: 6px; margin-bottom: 24px; text-align: center; }
        .field { margin-bottom: 16px; }
        .field label { display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px; }
        .field-input { width: 100%; height: 46px; border-radius: 12px; border: 1.5px solid #e8e0e0; background: #faf6f6; padding: 0 16px; font-size: 13.5px; font-weight: 500; color: #0f172a; font-family: 'Poppins', sans-serif; outline: none; }
        .field-input:focus { border-color: #681012; background: #fff; box-shadow: 0 0 0 3.5px rgba(104,16,18,0.1); }
        .status-msg { background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; font-size: 12.5px; font-weight: 600; border-radius: 8px; padding: 10px 12px; margin-bottom: 18px; }
        .server-error { background: #fdf1f1; border: 1px solid #f2c4c4; color: #a32d2d; font-size: 12px; font-weight: 600; border-radius: 8px; padding: 10px 12px; margin-bottom: 16px; }
        .btn-primary { width: 100%; height: 48px; border-radius: 12px; background: #681012; color: #fff; border: none; cursor: pointer; font-size: 14px; font-weight: 700; font-family: 'Poppins', sans-serif; position: relative; display: flex; align-items: center; justify-content: center; }
        .btn-primary:hover { opacity: .92; }
        .btn-primary.loading { pointer-events: none; }
        .btn-primary.loading .btn-text { opacity: 0; }
        .btn-primary .spinner {
            position: absolute;
            width: 20px; height: 20px;
            border: 2.5px solid rgba(255, 255, 255, 0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .7s linear infinite;
            opacity: 0;
            transition: opacity .2s;
        }
        .btn-primary.loading .spinner { opacity: 1; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .back-link { display: block; text-align: center; margin-top: 18px; font-size: 12.5px; font-weight: 600; color: #681012; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>

<body>
    <div class="auth-page">
        <div class="page-bg"></div>
        <div class="page-overlay"></div>
        <div class="page-vignette"></div>
        <div class="page-bar"></div>
        <div class="auth-card">
            <h1 class="card-h1">Forgot Password</h1>
            <p class="card-sub">Enter your email and we'll send you a reset code.</p>

            @if (session('status'))
                <div class="status-msg">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="server-error">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" id="forgotPasswordForm">
                @csrf
                <div class="field">
                    <label for="email">Email Address</label>
                    <input class="field-input" type="email" id="email" name="email" placeholder="you@example.com" value="{{ old('email') }}" required autofocus>
                </div>
                <button class="btn-primary" type="submit" id="submitBtn">
                    <div class="spinner"></div>
                    <span class="btn-text">Send Reset Code</span>
                </button>
            </form>

            <a class="back-link" href="{{ route('login') }}">&larr; Back to Sign In</a>
        </div>
    </div>

    <script>
        document.getElementById('forgotPasswordForm').addEventListener('submit', function () {
            document.getElementById('submitBtn').classList.add('loading');
        });
    </script>
</body>

</html>
