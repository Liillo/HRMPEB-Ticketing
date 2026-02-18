<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Ticketing System')</title>
    <style>
        :root {
            --bg-primary: #fdfaf5;
            --bg-secondary: #ffffff;
            --text-primary: #2d2416;
            --text-secondary: #6b5d48;
            --color-primary: #7c6a46;
            --color-secondary: #e8dcc8;
            --color-accent: #d4a574;
            --color-muted: #f5f0e7;
            --color-border: rgba(124, 106, 70, 0.15);
            --color-error: #c44536;
            --color-success: #4a7c59;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: var(--bg-primary);
            background-image:
                radial-gradient(circle at 12% 10%, rgba(212, 165, 116, 0.18), transparent 34%),
                radial-gradient(circle at 86% 16%, rgba(124, 106, 70, 0.12), transparent 30%),
                linear-gradient(165deg, #fffdf9 0%, #f8f1e6 52%, #fdfaf5 100%);
            background-attachment: fixed;
            color: var(--text-primary);
            line-height: 1.6;
            position: relative;
            overflow-x: hidden;
        }

        body > * {
            position: relative;
            z-index: 1;
        }

        html::before,
        html::after,
        body::before,
        body::after {
            content: "";
            position: fixed;
            pointer-events: none;
            z-index: 0;
            transform-style: preserve-3d;
        }

        html::before {
            width: 360px;
            height: 200px;
            left: -70px;
            top: 36px;
            border-radius: 24px;
            border: 1px solid rgba(124, 106, 70, 0.34);
            background:
                linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(212, 165, 116, 0.34)),
                repeating-linear-gradient(120deg, rgba(124, 106, 70, 0.16) 0 1px, transparent 1px 24px);
            box-shadow: 0 24px 40px rgba(124, 106, 70, 0.2);
            transform: perspective(1200px) rotateX(56deg) rotateZ(-28deg);
            opacity: 0.9;
        }

        html::after {
            width: 240px;
            height: 240px;
            right: -52px;
            top: 88px;
            border-radius: 28px;
            border: 1px solid rgba(124, 106, 70, 0.3);
            background:
                radial-gradient(circle at 30% 26%, rgba(255, 255, 255, 0.92), rgba(255, 255, 255, 0.1) 56%),
                linear-gradient(145deg, rgba(212, 165, 116, 0.44), rgba(124, 106, 70, 0.16));
            box-shadow: 0 22px 36px rgba(124, 106, 70, 0.18);
            transform: perspective(1000px) rotateX(18deg) rotateY(-26deg) rotateZ(10deg);
            opacity: 0.82;
        }

        body::before {
            width: 580px;
            height: 220px;
            right: -110px;
            bottom: -58px;
            border-radius: 22px;
            border: 1px solid rgba(124, 106, 70, 0.3);
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.18), rgba(232, 220, 200, 0.5)),
                repeating-linear-gradient(90deg, rgba(124, 106, 70, 0.16) 0 1px, transparent 1px 28px);
            box-shadow: 0 26px 40px rgba(124, 106, 70, 0.2);
            transform: perspective(1200px) rotateX(75deg) rotateZ(-8deg);
            opacity: 0.78;
        }

        body::after {
            width: 320px;
            height: 320px;
            left: -110px;
            bottom: -132px;
            border-radius: 50%;
            background:
                radial-gradient(circle, rgba(212, 165, 116, 0.34) 0 46%, rgba(212, 165, 116, 0) 68%),
                radial-gradient(circle, rgba(124, 106, 70, 0.24), rgba(124, 106, 70, 0) 72%);
            transform: perspective(980px) rotateX(64deg) rotateY(18deg);
            opacity: 0.62;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            font-size: 16px;
            font-weight: 500;
            text-decoration: none;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }
        
        @media (max-width: 768px) {
            .btn {
                padding: 10px 16px;
                font-size: 14px;
            }
        }
        
        .btn-primary {
            background-color: var(--color-primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #6b5d48;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background-color: var(--color-secondary);
            color: var(--text-primary);
        }
        
        .btn-secondary:hover {
            background-color: var(--color-accent);
        }
        
        .card {
            background: var(--bg-secondary);
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(124, 106, 70, 0.1);
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .card {
                padding: 16px;
                border-radius: 8px;
            }
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-primary);
        }
        
        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="password"],
        input[type="number"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--color-border);
            border-radius: 8px;
            font-size: 16px;
            background-color: #faf6f0;
            transition: border-color 0.3s;
        }
        
        @media (max-width: 768px) {
            input[type="text"],
            input[type="email"],
            input[type="tel"],
            input[type="password"],
            input[type="number"],
            input[type="date"],
            select,
            textarea {
                padding: 10px 12px;
                font-size: 16px; /* Prevent zoom on iOS */
            }
        }
        
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--color-primary);
        }
        
        .error {
            color: var(--color-error);
            font-size: 14px;
            margin-top: 4px;
            display: block;
        }
        
        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .text-center {
            text-align: center;
        }
        
        h1 {
            font-size: 32px;
            margin-bottom: 16px;
        }
        
        h2 {
            font-size: 24px;
            margin-bottom: 12px;
        }
        
        @media (max-width: 768px) {
            h1 {
                font-size: 24px;
            }
            
            h2 {
                font-size: 20px;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="container" style="padding-bottom: 0;">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif
    </div>

    @yield('content')
    @stack('scripts')
</body>
</html>

