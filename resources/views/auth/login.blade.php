<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AUAG Jewelry - Login</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- Ensure this path is correct for your Laravel setup --}}
    <link rel="icon" type="image/png" href="{{ asset('Auag.jpg') }}">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&family=Cormorant+Garamond:wght@600;700&family=Montserrat:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            /* Luxury Gold Palette */
            --gold-light: #fcf6ba;
            --gold-base: #bf953f;
            --gold-dark: #aa771c;
            --gold-gradient: linear-gradient(to right, #bf953f, #fcf6ba, #b38728, #fbf5b7, #aa771c);
            --dark-bg: #0a0a0a;
            --glass-bg: rgba(20, 20, 20, 0.75);
        }

        /* Basic Reset */
        body {
            margin: 0;
            font-family: 'Montserrat', sans-serif;
            color: #e0e0e0;
            background-color: var(--dark-bg);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            overflow: hidden;
            position: relative;
        }

        /* --- Background Styling --- */
        .background-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            /* Ensure this path is correct for your Laravel setup */
            background-image: radial-gradient(circle at center, rgba(0,0,0,0.4), #000000), url("{{ asset('BG.jpg') }}");
            background-repeat: no-repeat;
            background-position: center;
            background-size: cover;
            filter: blur(4px);
            transform: scale(1.05);
            z-index: -2;
        }

        /* --- FLOATING BACKGROUND PARTICLES (ENHANCED) --- */
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1; /* Behind the card */
            pointer-events: none;
        }

        .particle {
            position: absolute;
            bottom: -50px; /* Start further down */
            background: radial-gradient(circle, rgba(255, 246, 186, 0.7) 0%, rgba(191, 149, 63, 0.1) 60%, transparent 100%);
            border-radius: 50%;
            opacity: 0;
            /* Changed timing function for a more graceful drift */
            animation: rise ease-in-out infinite; 
            mix-blend-mode: screen; /* Makes them glow better against dark bg */
        }

        /* UPDATED ANIMATION: More sway, higher rise */
        @keyframes rise {
            0% {
                bottom: -50px;
                transform: translateX(0);
                opacity: 0;
            }
            15% {
                opacity: 0.5;
                transform: translateX(-30px); /* Drift left */
            }
            50% {
                opacity: 0.3;
                transform: translateX(60px); /* Big drift right */
            }
            80% {
                opacity: 0.2;
                transform: translateX(-20px); /* Drift back left */
            }
            100% {
                bottom: 120%; /* Go way above screen */
                transform: translateX(10px);
                opacity: 0;
            }
        }

        /* Randomizing more particles with slower, more varied timing */
        .particle:nth-child(1)  { left: 5%; width: 15px; height: 15px; animation-duration: 25s; animation-delay: 0s; }
        .particle:nth-child(2)  { left: 15%; width: 35px; height: 35px; animation-duration: 35s; animation-delay: 5s; }
        .particle:nth-child(3)  { left: 25%; width: 10px; height: 10px; animation-duration: 18s; animation-delay: 12s; opacity: 0.4; }
        .particle:nth-child(4)  { left: 35%; width: 50px; height: 50px; animation-duration: 45s; animation-delay: 2s; }
        .particle:nth-child(5)  { left: 45%; width: 20px; height: 20px; animation-duration: 28s; animation-delay: 9s; }
        .particle:nth-child(6)  { left: 55%; width: 40px; height: 40px; animation-duration: 38s; animation-delay: 1s; }
        .particle:nth-child(7)  { left: 65%; width: 12px; height: 12px; animation-duration: 22s; animation-delay: 15s; }
        .particle:nth-child(8)  { left: 75%; width: 60px; height: 60px; animation-duration: 50s; animation-delay: 7s; opacity: 0.3; }
        .particle:nth-child(9)  { left: 85%; width: 25px; height: 25px; animation-duration: 30s; animation-delay: 3s; }
        .particle:nth-child(10) { left: 95%; width: 18px; height: 18px; animation-duration: 24s; animation-delay: 11s; }
        /* New particles for density */
        .particle:nth-child(11) { left: 10%; width: 22px; height: 22px; animation-duration: 33s; animation-delay: 18s; }
        .particle:nth-child(12) { left: 30%; width: 8px; height: 8px;   animation-duration: 15s; animation-delay: 6s; }
        .particle:nth-child(13) { left: 50%; width: 45px; height: 45px; animation-duration: 42s; animation-delay: 22s; }
        .particle:nth-child(14) { left: 70%; width: 14px; height: 14px; animation-duration: 26s; animation-delay: 1s; }
        .particle:nth-child(15) { left: 90%; width: 30px; height: 30px; animation-duration: 36s; animation-delay: 14s; }
        .particle:nth-child(16) { left: 40%; width: 10px; height: 10px; animation-duration: 20s; animation-delay: 25s; }


        /* --- Card Floating Animation --- */
        @keyframes floatCard {
            0% { transform: translateY(0px); box-shadow: 0 5px 15px 0px rgba(0,0,0,0.6); }
            50% { transform: translateY(-10px); box-shadow: 0 25px 15px 0px rgba(0,0,0,0.4); }
            100% { transform: translateY(0px); box-shadow: 0 5px 15px 0px rgba(0,0,0,0.6); }
        }

        /* --- Layout --- */
        .login-container {
            padding: 20px;
            z-index: 10;
            width: 100%;
            max-width: 420px;
        }

        /* --- Glassmorphism Card --- */
        .login-card {
            background: var(--glass-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 16px;
            padding: 45px 35px;
            text-align: center;
            border: 1px solid rgba(191, 149, 63, 0.3);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.5);
            /* Renamed animation to avoid conflict with particles */
            animation: floatCard 6s ease-in-out infinite; 
            position: relative;
            overflow: hidden;
        }

        /* Shine effect */
        .login-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 246, 186, 0.1), transparent);
            transition: 0.5s;
            pointer-events: none;
        }
        .login-card:hover::before {
            left: 100%;
            transition: 0.8s;
        }

        /* --- Typography --- */
        .logo-text {
            font-family: 'Cinzel', serif;
            font-size: 42px;
            font-weight: 700;
            margin: 0;
            letter-spacing: 6px;
            background: var(--gold-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0px 2px 2px rgba(0,0,0,0.3);
            position: relative;
            display: inline-block;
        }

        .logo-subtext {
            font-family: 'Montserrat', sans-serif;
            font-size: 11px;
            color: #c9c9c9;
            margin-top: 0px;
            letter-spacing: 5px;
            text-transform: uppercase;
            border-bottom: 1px solid #bf953f;
            display: inline-block;
            padding-bottom: 5px;
            margin-bottom: 25px;
        }

        .welcome-text {
            font-family: 'Cormorant Garamond', serif;
            font-size: 28px;
            font-weight: 600;
            margin-top: 10px;
            margin-bottom: 5px;
            color: #ffffff;
        }

        .signin-info {
            font-size: 13px;
            color: #888;
            margin-bottom: 30px;
            font-weight: 300;
        }

        /* --- Alerts --- */
        .session-status {
            background: rgba(33, 64, 33, 0.6);
            border-left: 3px solid #3fa43f;
            color: #d4f4d4;
            padding: 12px;
            font-size: 13px;
            margin-bottom: 20px;
            text-align: left;
        }

        .error-text {
            color: #ff8a8a;
            font-size: 12px;
            margin-top: 4px;
            text-align: left;
            padding-left: 5px;
        }

        /* --- Form --- */
        .login-form {
            text-align: left;
        }

        label {
            font-size: 12px;
            color: var(--gold-base);
            margin-bottom: 8px;
            margin-top: 15px;
            display: block;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        .input-group {
            position: relative;
            margin-bottom: 5px;
        }

        .login-form .input-group input {
            width: 100%;
            padding: 14px 45px 14px 15px;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            color: #fff;
            font-size: 15px;
            font-family: 'Montserrat', sans-serif;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }

        .login-form .input-group input:focus {
            outline: none;
            background: rgba(0, 0, 0, 0.4);
            border-color: var(--gold-base);
            box-shadow: 0 0 10px rgba(191, 149, 63, 0.2);
        }

        .login-form .input-group input::placeholder {
            color: #555;
            font-weight: 300;
        }

        .icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gold-base);
            font-size: 16px;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #777;
            cursor: pointer;
            transition: color 0.3s;
        }
        .password-toggle:hover {
            color: var(--gold-light);
        }

        /* --- Links & Checks --- */
        .remember-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 15px 0 30px;
            font-size: 12px;
        }

        .remember-label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #aaa;
            text-transform: none;
            font-weight: 400;
            margin: 0;
            cursor: pointer;
        }

        .remember-label input[type="checkbox"] {
            appearance: none;
            -webkit-appearance: none;
            width: 16px;
            height: 16px;
            border: 1px solid var(--gold-base);
            border-radius: 2px;
            display: grid;
            place-content: center;
            cursor: pointer;
        }

        .remember-label input[type="checkbox"]::before {
            content: "";
            width: 10px;
            height: 10px;
            transform: scale(0);
            transition: 120ms transform ease-in-out;
            box-shadow: inset 1em 1em var(--gold-base);
        }

        .remember-label input[type="checkbox"]:checked::before {
            transform: scale(1);
        }

        .forgot-password {
            color: #aaa;
            text-decoration: none;
            transition: color 0.3s;
        }

        .forgot-password:hover {
            color: var(--gold-light);
            text-decoration: underline;
        }

        /* --- Metallic Button --- */
        .signin-button {
            width: 100%;
            padding: 16px;
            background: linear-gradient(45deg, #bf953f, #fcf6ba, #b38728, #fbf5b7);
            background-size: 200% auto;
            border: none;
            border-radius: 4px;
            color: #2b241e;
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(191, 149, 63, 0.4);
            transition: all 0.4s ease;
        }

        .signin-button:hover {
            background-position: right center;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(191, 149, 63, 0.6);
        }

        /* --- Social & Footer --- */
        .divider-or {
            display: flex;
            align-items: center;
            margin: 25px 0;
            color: #555;
            font-size: 11px;
            letter-spacing: 1px;
        }

        .line {
            flex-grow: 1;
            height: 1px;
            background: linear-gradient(90deg, transparent, #333, transparent);
        }

        .social-button {
            width: 100%;
            padding: 12px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid #333;
            color: #ccc;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.3s;
            border-radius: 4px;
            text-decoration: none;
            box-sizing: border-box;
        }

        .social-button:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: #555;
            color: #fff;
        }

        .create-account {
            margin-top: 25px;
            font-size: 13px;
            color: #888;
        }

        .create-account a {
            color: var(--gold-base);
            text-decoration: none;
            font-weight: 600;
            margin-left: 5px;
            transition: 0.3s;
        }

        .create-account a:hover {
            color: var(--gold-light);
            text-shadow: 0 0 5px rgba(255, 215, 0, 0.5);
        }

    </style>
</head>
<body>
    <div class="background-overlay"></div>
    
    <div class="particles">
        <span class="particle"></span>
        <span class="particle"></span>
        <span class="particle"></span>
        <span class="particle"></span>
        <span class="particle"></span>
        <span class="particle"></span>
        <span class="particle"></span>
        <span class="particle"></span>
        <span class="particle"></span>
        <span class="particle"></span>
        <span class="particle"></span>
        <span class="particle"></span>
        <span class="particle"></span>
        <span class="particle"></span>
        <span class="particle"></span>
        <span class="particle"></span>
    </div>
    
    <div class="login-container">
        <div class="login-card">
            
            <header class="logo">
                <h1 class="logo-text">AUAG</h1>
                <div style="display:block;"></div>
                <p class="logo-subtext">Fine Jewelry</p>
            </header>

            <h2 class="welcome-text">Welcome Back</h2>
            <p class="signin-info">Please enter your details to sign in.</p>

            {{-- Session Status --}}
            @if (session('status'))
                <div class="session-status">
                    {{ session('status') }}
                </div>
            @endif

            <form class="login-form" method="POST" action="{{ route('login') }}">
                @csrf

                {{-- Email --}}
                <label for="email">Email</label>
                <div class="input-group">
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="example@auag.com"
                    >
                    <i class="icon fa-regular fa-envelope"></i>
                </div>
                @error('email')
                    <p class="error-text">{{ $message }}</p>
                @enderror

                {{-- Password --}}
                <label for="password">Password</label>
                <div class="input-group">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                    >
                    <button type="button" class="password-toggle" id="togglePassword">
                        <i id="eyeSlashIcon" class="fa-solid fa-eye-slash"></i>
                        <i id="eyeIcon" class="fa-solid fa-eye" style="display: none;"></i>
                    </button>
                </div>
                @error('password')
                    <p class="error-text">{{ $message }}</p>
                @enderror

                <div class="remember-row">
                    <label class="remember-label">
                        <input id="remember_me" type="checkbox" name="remember">
                        <span>Remember me</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a class="forgot-password" href="{{ route('password.request') }}">
                            Lost Password?
                        </a>
                    @endif
                </div>

                <button type="submit" class="signin-button">
                    Sign In
                </button>
            </form>

            <div class="divider-or">
                <span class="line"></span>
                <span class="text">OR CONTINUE WITH</span>
                <span class="line"></span>
            </div>

            <div class="social-login">
                <a href="{{ route('google.login') }}" class="social-button google">
                    <span class="social-icon"><i class="fa-brands fa-google"></i></span> &nbsp; Google
                </a>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            const eyeSlashIcon = document.getElementById('eyeSlashIcon');

            if (togglePassword) {
                togglePassword.addEventListener('click', function () {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);

                    eyeIcon.style.display = (type === 'text' ? 'inline' : 'none');
                    eyeSlashIcon.style.display = (type === 'text' ? 'none' : 'inline');
                });
            }
        });
    </script>
</body>
</html>