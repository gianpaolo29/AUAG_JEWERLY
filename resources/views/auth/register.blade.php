<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AUAG Fine Jewelry Registration</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@700&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        /* General & Layout */
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background-color: #000;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            position: relative;
            color: #f0f0f0;
        }
        .background-overlay {
            position: absolute;
            inset: 0;
            background-image: linear-gradient(rgba(0,0,0,0.8), rgba(0,0,0,0.8)), url("{{ asset('storage/BG.jpg') }}");
            background-size: cover;
            background-position: center;
            filter: blur(5px);
            z-index: -1;
        }
        .register-container {
            width: 100%;
            max-width: 440px;
            padding: 20px;
        }
        .register-card {
            background: rgba(43, 36, 30, 0.8);
            border-radius: 12px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 4px 30px rgba(0,0,0,0.5), 0 0 20px rgba(255,200,0,0.1);
            border: 1px solid rgba(255,255,255,0.1);
            backdrop-filter: blur(5px);
        }

        /* Branding & Headings */
        .logo-text {
            font-family: 'Cormorant Garamond', serif;
            font-size: 30px;
            color: #ffc72c;
            letter-spacing: 5px;
        }
        .logo-subtext {
            font-size: 10px;
            color: #c9c9c9;
            letter-spacing: 3px;
        }
        .heading {
            margin-top: 10px;
            margin-bottom: 0;
            font-size: 24px;
        }
        .subheading {
            color:#a8a8a8; 
            margin-top:5px;
            margin-bottom: 30px; 
            font-size: 15px;
        }

        /* Form elements */
        label { 
            display: block; 
            text-align: left; 
            margin-top: 20px; 
            margin-bottom: 5px;
            color: #c9c9c9; 
            font-weight: 500;
            font-size: 14px;
        }
        .input-group { 
            position: relative; 
            margin-bottom: 5px;
        }
        .input-group input, .input-group select {
            width: 100%;
            padding: 12px 40px 12px 15px;
            background: #ffffff;
            border-radius: 6px;
            border: none;
            color: #302418;
            font-size: 15px;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }
        .input-group input:focus, .input-group select:focus {
            outline: none;
            box-shadow: 0 0 0 2px #ffc72c;
            background-color: #fffdf5;
        }
        .input-group input.valid {
            box-shadow: 0 0 0 1px #4ade80;
        }
        .input-group input.invalid {
            box-shadow: 0 0 0 1px #f87171;
        }
        .icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #ffc72c;
        }
        .password-toggle {
            background: none;
            border: none;
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #ffc72c;
            cursor: pointer;
            z-index: 2;
        }
        .password-strength {
            margin-top: 8px;
            height: 4px;
            border-radius: 2px;
            background: #333;
            overflow: hidden;
        }
        .password-strength-bar {
            height: 100%;
            width: 0;
            transition: width 0.3s ease, background-color 0.3s ease;
        }
        .password-strength-text {
            font-size: 12px;
            margin-top: 4px;
            text-align: left;
            color: #a8a8a8;
        }
        .password-strength.weak .password-strength-bar { 
            width: 33%; 
            background-color: #ef4444; 
        }
        .password-strength.medium .password-strength-bar { 
            width: 66%; 
            background-color: #f59e0b; 
        }
        .password-strength.strong .password-strength-bar { 
            width: 100%; 
            background-color: #10b981; 
        }
        .error-text { 
            color: #ff9c9c; 
            font-size: 13px; 
            margin-top: 5px; 
            text-align: left; 
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .error-text i {
            font-size: 12px;
        }
        .success-text {
            color: #4ade80;
            font-size: 13px;
            margin-top: 5px;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .hint-text {
            color: #a8a8a8;
            font-size: 12px;
            margin-top: 5px;
            text-align: left;
        }
        .register-button {
            width: 100%;
            /* Adjusted margin-top since terms checkbox is removed */
            margin-top: 25px; 
            padding: 15px;
            background: linear-gradient(180deg, #ffc72c, #e7a800);
            border-radius: 8px;
            font-size: 17px;
            font-weight: bold;
            border: none;
            cursor: pointer;
            color: #302418;
            transition: all 0.3s ease;
        }
        .register-button:hover { 
            opacity: 0.9; 
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 199, 44, 0.3);
        }
        .register-button:active {
            transform: translateY(0);
        }
        .register-button:disabled {
            background: #6b7280;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        .login-link {
            margin-top: 20px;
            color: #c9c9c9;
            font-size: 14px;
        }
        .login-link a { 
            color: #ffc72c; 
            font-weight: bold; 
            text-decoration: none; 
            transition: color 0.3s ease;
        }
        .login-link a:hover { 
            color: #ffe6a4; 
        }
        /* Removed terms-checkbox styles as the element is gone */
    </style>
</head>

<body>
    <div class="background-overlay"></div>

    <div class="register-container">
        <div class="register-card">

            <h1 class="logo-text">AUAG</h1>
            <p class="logo-subtext">FINE JEWELRY</p>

            <h2 class="heading" style="margin-top: 20px;">Create Account</h2>
            <p class="subheading" style="color:#a8a8a8; margin-top:5px;">Join our exclusive jewelry community</p>

            <form id="registerForm" method="POST" action="{{ route('register') }}">
                @csrf

                {{-- Name --}}
                <label for="name">Full Name</label>
                <div class="input-group">
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required 
                           autocomplete="name" placeholder="Enter your full name" minlength="2" maxlength="50">
                    <i class="icon fa-solid fa-user"></i>
                </div>
                @error('name')
                    <p class="error-text"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
                @enderror

                {{-- Email --}}
                <label for="email">Email Address</label>
                <div class="input-group">
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required 
                           autocomplete="email" placeholder="your.email@example.com">
                    <i class="icon fa-regular fa-envelope"></i>
                </div>
                @error('email')
                    <p class="error-text"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
                @enderror

                {{-- Password --}}
                <label for="password">Password</label>
                <div class="input-group">
                    <input type="password" id="password" name="password" required 
                           autocomplete="new-password" placeholder="At least 8 characters" minlength="8">
                    <button type="button" class="password-toggle" onclick="togglePassword('password', 'eye1', 'eyeSlash1')">
                        <i id="eyeSlash1" class="fa-solid fa-eye-slash"></i>
                        <i id="eye1" class="fa-solid fa-eye" style="display:none;"></i>
                    </button>
                </div>
                <div class="password-strength" id="passwordStrength">
                    <div class="password-strength-bar"></div>
                </div>
                <p class="password-strength-text" id="passwordStrengthText">Password strength</p>
                @error('password')
                    <p class="error-text"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
                @enderror

                {{-- Confirm Password --}}
                <label for="password_confirmation">Confirm Password</label>
                <div class="input-group">
                    <input type="password" id="password_confirmation" name="password_confirmation" required 
                           autocomplete="new-password" placeholder="Re-enter your password">
                    <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation', 'eye2', 'eyeSlash2')">
                        <i id="eyeSlash2" class="fa-solid fa-eye-slash"></i>
                        <i id="eye2" class="fa-solid fa-eye" style="display:none;"></i>
                    </button>
                </div>
                <p id="passwordMatchText" class="hint-text"></p>
                @error('password_confirmation')
                    <p class="error-text"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
                @enderror


                <button type="submit" class="register-button" id="submitButton">Create Account</button>
            </form>

            <p class="login-link">
                Already have an account?
                <a href="{{ route('login') }}">Sign in</a>
            </p>
        </div>
    </div>

    <script>
        function togglePassword(fieldId, eyeId, eyeSlashId) {
            const input = document.getElementById(fieldId);
            const eye = document.getElementById(eyeId);
            const eyeSlash = document.getElementById(eyeSlashId);

            const type = input.type === 'password' ? 'text' : 'password';
            input.type = type;

            eye.style.display = (type === 'text') ? 'inline' : 'none';
            eyeSlash.style.display = (type === 'text') ? 'none' : 'inline';
        }

        // --- Password strength and Match validation logic ---
        const passwordInput = document.getElementById('password');
        const passwordStrength = document.getElementById('passwordStrength');
        const passwordStrengthText = document.getElementById('passwordStrengthText');
        const passwordMatchText = document.getElementById('passwordMatchText');
        const confirmPasswordInput = document.getElementById('password_confirmation');
        const submitButton = document.getElementById('submitButton');
        const registerForm = document.getElementById('registerForm');

        passwordInput.addEventListener('input', updatePasswordStatus);
        confirmPasswordInput.addEventListener('input', updatePasswordStatus);
        
        function checkPasswordStrength(password) {
            let score = 0;
            if (password.length >= 8) score++; // Length
            if (/[a-z]/.test(password)) score++; // Lowercase
            if (/[A-Z]/.test(password)) score++; // Uppercase
            if (/[0-9]/.test(password)) score++; // Numbers
            if (/[^A-Za-z0-9]/.test(password)) score++; // Special characters
            return score;
        }

        function updatePasswordStatus() {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            const score = checkPasswordStrength(password);
            
            // 1. Update Strength Indicator
            passwordStrength.className = 'password-strength';
            if (password.length > 0) {
                if (score <= 2) {
                    passwordStrength.classList.add('weak');
                    passwordStrengthText.textContent = 'Weak password';
                    passwordStrengthText.style.color = '#ef4444';
                } else if (score <= 4) {
                    passwordStrength.classList.add('medium');
                    passwordStrengthText.textContent = 'Medium strength password';
                    passwordStrengthText.style.color = '#f59e0b';
                } else {
                    passwordStrength.classList.add('strong');
                    passwordStrengthText.textContent = 'Strong password';
                    passwordStrengthText.style.color = '#10b981';
                }
            } else {
                passwordStrengthText.textContent = 'Password strength';
                passwordStrengthText.style.color = '#a8a8a8';
            }

            // 2. Check Password Match
            if (confirmPassword.length > 0) {
                if (password === confirmPassword) {
                    passwordMatchText.textContent = 'Passwords match';
                    passwordMatchText.className = 'success-text';
                } else {
                    passwordMatchText.textContent = 'Passwords do not match';
                    passwordMatchText.className = 'error-text';
                }
            } else {
                passwordMatchText.textContent = '';
                passwordMatchText.className = 'hint-text';
            }
        }
        
        // --- Real-time input validation styling ---
        const inputs = document.querySelectorAll('#registerForm input');
        
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                if (this.value.length > 0) {
                    if (this.checkValidity()) {
                        this.classList.add('valid');
                        this.classList.remove('invalid');
                    } else {
                        this.classList.add('invalid');
                        this.classList.remove('valid');
                    }
                } else {
                    this.classList.remove('valid', 'invalid');
                }
            });
        });

        // --- Final Form Submission Validation (Simplified) ---
        registerForm.addEventListener('submit', function(e) {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                passwordMatchText.textContent = 'Passwords do not match';
                passwordMatchText.className = 'error-text';
                confirmPasswordInput.focus();
                return;
            }
            
            // Disable button on successful submit attempt
            if (registerForm.checkValidity()) {
                submitButton.disabled = true;
                submitButton.textContent = 'Creating Account...';
            }
        });

    </script>
</body>
</html>