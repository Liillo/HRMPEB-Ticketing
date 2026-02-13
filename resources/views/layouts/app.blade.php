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
            color: var(--text-primary);
            line-height: 1.6;
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
    @yield('content')
    @stack('scripts')
</body>
</html>