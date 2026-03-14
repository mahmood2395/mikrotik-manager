<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — MikroTik Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@300;400;500&family=Sora:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: #0d1117;
            color: #e2e8f0;
            font-family: 'Sora', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Grid background */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(0,212,170,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0,212,170,0.03) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
        }

        .login-box {
            width: 100%;
            max-width: 400px;
            padding: 20px;
            position: relative;
            z-index: 1;
        }

        .login-logo {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-logo .icon {
            width: 56px; height: 56px;
            background: #00d4aa22;
            border: 1px solid #00d4aa44;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 16px;
        }

        .login-logo h1 {
            font-size: 20px;
            font-weight: 700;
            color: #e2e8f0;
        }

        .login-logo p {
            font-size: 11px;
            font-family: 'JetBrains Mono', monospace;
            color: #64748b;
            margin-top: 4px;
        }

        .login-card {
            background: #111827;
            border: 1px solid #1e293b;
            border-radius: 16px;
            padding: 32px;
        }

        .form-group { margin-bottom: 20px; }

        label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 8px;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            background: #0d1117;
            border: 1px solid #1e293b;
            border-radius: 8px;
            padding: 11px 14px;
            font-size: 13px;
            color: #e2e8f0;
            font-family: 'JetBrains Mono', monospace;
            outline: none;
            transition: border-color 0.15s;
        }

        input:focus { border-color: #00d4aa; }

        .error {
            color: #ff4757;
            font-size: 11px;
            font-family: 'JetBrains Mono', monospace;
            margin-top: 6px;
        }

        button[type="submit"] {
            width: 100%;
            background: #00d4aa;
            color: #0d1117;
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-size: 13px;
            font-weight: 700;
            font-family: 'Sora', sans-serif;
            cursor: pointer;
            margin-top: 8px;
            transition: opacity 0.15s;
        }

        button[type="submit"]:hover { opacity: 0.9; }

        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: #64748b;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="login-box">
    <div class="login-logo">
        <div class="icon">🔧</div>
        <h1>MikroTik Manager</h1>
        <p>fleet.control.system</p>
    </div>

    <div class="login-card">
        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus>
                @error('email') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
                @error('password') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div class="remember">
                <input type="checkbox" name="remember" id="remember">
                <label for="remember" style="text-transform: none; letter-spacing: 0; font-size: 12px; margin: 0;">
                    Remember me
                </label>
            </div>

            <button type="submit">→ Sign In</button>
        </form>
    </div>
</div>
</body>
</html>