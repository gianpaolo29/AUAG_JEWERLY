<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lumière Fine Jewelry Login</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@700&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        /* Basic Reset and Global Styles */
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            color: #f0f0f0;
            background-color: #000;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            overflow: hidden;
            position: relative;
        }

        /* --- Background Styling (UPDATED TO USE LARAVEL ASSET) --- */
        .background-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            
            /* 🥇 MODIFIED: Background image loaded via Laravel's asset() helper, 
               with a dark linear gradient on top to ensure text contrast */
            background-image: linear-gradient(rgba(0,0,0,0.8), rgba(0,0,0,0.8)), url("{{ asset('storage/BG.jpg') }}");
            background-repeat: no-repeat;
            background-position: center;
            background-size: cover;
            filter: blur(5px);
            z-index: -2;
        }

        /* --- Card and Layout Styling --- */
        .login-container {
            padding: 20px;
            z-index: 10;
            width: 100%;
            max-width: 440px;
        }

        .login-card {
            background: rgba(43, 36, 30, 0.8); /* Dark semi-transparent background */
            border-radius: 12px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.5), 0 0 20px rgba(255, 187, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(5px);
        }

        /* --- Logo and Header --- */
        .logo-text {
            font-family: 'Cormorant Garamond', serif;
            font-size: 30px;
            font-weight: 700;
            color: #ffc72c; /* Gold color */
            margin: 0;
            letter-spacing: 5px;
        }

        .logo-subtext {
            font-size: 10px;
            color: #c9c9c9;
            margin-top: 5px;
            letter-spacing: 3px;
        }

        .welcome-text {
            font-size: 24px;
            margin-top: 30px;
            margin-bottom: 5px;
            color: #ffffff;
        }

        .signin-info {
            font-size: 14px;
            color: #a8a8a8;
            margin-bottom: 30px;
        }

        /* --- Session + Error Messages --- */
        .session-status {
            background-color: #214021;
            border: 1px solid #3fa43f;
            color: #d4f4d4;
            padding: 10px 14px;
            border-radius: 6px;
            font-size: 14px;
            margin-bottom: 15px;
            text-align: left;
        }

        .error-text {
            color: #ffb3b3;
            font-size: 13px;
            margin-top: 4px;
        }

        /* --- Form Elements --- */
        .login-form {
            text-align: left;
        }

        label {
            display: block;
            font-size: 14px;
            color: #c9c9c9;
            margin-bottom: 8px;
            margin-top: 15px;
        }

        .input-group {
            position: relative;
            margin-bottom: 10px;
        }

        /* Input field styles (kept the light background from the previous edit) */
        .login-form .input-group input {
            width: 100%;
            padding: 12px 40px 12px 15px;
            border: none;
            background-color: #ffffff; 
            border-radius: 6px;
            color: #302418; 
            font-size: 16px;
            box-sizing: border-box;
            transition: background-color 0.3s, box-shadow 0.3s;
        }


        input::placeholder {
            color: #666; 
        }

        input:focus {
            outline: none;
            background-color: #f0f0f0; 
            box-shadow: 0 0 0 2px #ffc72c;
        }

        .icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #ffc72c;
            font-size: 16px;
            cursor: pointer;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #ffc72c;
            cursor: pointer;
            padding: 0;
            font-size: 18px;
        }

        .forgot-password {
            display: block;
            text-align: right;
            font-size: 13px;
            color: #ffc72c;
            text-decoration: none;
            margin-bottom: 10px;
            transition: color 0.2s;
        }

        .forgot-password:hover {
            color: #ffe6a4;
        }

        .remember-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 10px 0 25px;
            font-size: 13px;
        }

        .remember-label {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #c9c9c9;
        }

        .remember-label input[type="checkbox"] {
            width: 14px;
            height: 14px;
            accent-color: #ffc72c;
        }

        /* --- Sign In Button --- */
        .signin-button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(180deg, #ffc72c, #e7a800);
            border: none;
            border-radius: 8px;
            color: #302418;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(255, 199, 44, 0.4);
            transition: transform 0.2s, box-shadow 0.2s;
            margin-bottom: 20px;
        }

        .signin-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 12px rgba(255, 199, 44, 0.5);
        }

        /* --- Divider --- */
        .divider-or {
            display: flex;
            align-items: center;
            margin: 10px 0;
            color: #6a6a6a;
        }

        .line {
            flex-grow: 1;
            height: 1px;
            background-color: #3b3127;
        }

        .text {
            margin: 0 15px;
            font-size: 12px;
        }

        /* --- Social Login Buttons --- */
        .social-login {
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: center;
        }
        .social-button {
            flex: 1;
            padding: 12px;
            background-color: transparent;
            border: none;
            color: #f0f0f0;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s;
        }

        .social-button:hover {
            background-color: #3b3127;
            border-radius: 6px;
        }

        .social-icon {
            font-size: 18px;
            margin-right: 8px;
            line-height: 1;
        }

        /* --- Create Account Link --- */
        .create-account {
            font-size: 14px;
            color: #c9c9c9;
        }

        .create-account a {
            color: #ffc72c;
            text-decoration: none;
            font-weight: bold;
        }

        .create-account a:hover {
            color: #ffe6a4;
        }
    </style>
</head>
<body>
    <div class="background-overlay"></div>
    <div class="login-container">
        <div class="login-card">
            <header class="logo">
                <h1 class="logo-text">AUAG</h1>
                <p class="logo-subtext">FINE JEWELRY</p>
            </header>

            <h2 class="welcome-text">Welcome Back</h2>
            <p class="signin-info">Sign in to your account</p>

            {{-- Session Status --}}
            @if (session('status'))
                <div class="session-status">
                    {{ session('status') }}
                </div>
            @endif

            <form class="login-form" method="POST" action="{{ route('login') }}">
                @csrf

                {{-- Email --}}
                <label for="email">Email Address</label>
                <div class="input-group">
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="your@email.com"
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
                        placeholder="Enter your password"
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
                            Forgot password?
                        </a>
                    @endif
                </div>

                <button type="submit" class="signin-button">
                    Log In
                </button>
            </form>

            <div class="divider-or">
                <span class="line"></span>
                <span class="text">OR</span>
                <span class="line"></span>
            </div>

            <div class="social-login">
                <a href="{{ route('google.login') }}" class="social-button google">
                    <span class="social-icon"><i class="fa-brands fa-google"></i></span>
                    Google
                </a>
            </div>

            <p class="create-account">
                Don't have an account?
                @if (Route::has('register'))
                    <a href="{{ route('register') }}">Create account</a>
                @endif
            </p>
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