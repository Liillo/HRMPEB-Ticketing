<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            --sidebar-width: 250px;
            --sidebar-collapsed: 70px;
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
                radial-gradient(circle at 9% 8%, rgba(212, 165, 116, 0.16), transparent 32%),
                radial-gradient(circle at 90% 20%, rgba(124, 106, 70, 0.11), transparent 28%),
                linear-gradient(170deg, #fffdf9 0%, #f7efe3 55%, #fdfaf5 100%);
            background-attachment: fixed;
            color: var(--text-primary);
            line-height: 1.6;
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: var(--sidebar-width);
            background: var(--color-primary);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: transform 0.3s ease, width 0.3s ease;
            z-index: 1000;
            display: flex;
            flex-direction: column;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.mobile-open {
                transform: translateX(0);
            }
        }
        
        .sidebar.collapsed {
            width: var(--sidebar-collapsed);
        }
        
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }

        .sidebar-logo {
            display: block;
            max-width: 170px;
            width: 100%;
            height: auto;
            margin: 0 auto 12px;
        }
        
        .sidebar-header h2 {
            font-size: 20px;
            transition: opacity 0.3s;
        }
        
        .sidebar.collapsed .sidebar-header h2 {
            opacity: 0;
            position: absolute;
        }

        .sidebar.collapsed .sidebar-logo {
            display: none;
        }
        
        .sidebar-toggle {
            background: rgba(255,255,255,0.1);
            border: none;
            color: white;
            padding: 8px 12px;
            cursor: pointer;
            border-radius: 4px;
            margin-top: 10px;
            width: 100%;
        }
        
        .mobile-toggle {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1001;
            background: var(--color-primary);
            color: white;
            border: none;
            padding: 12px 16px;
            border-radius: 8px;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        
        @media (max-width: 768px) {
            .mobile-toggle {
                display: block;
            }
            
            .sidebar-toggle {
                display: none;
            }
        }
        
        .sidebar-nav {
            list-style: none;
            padding: 20px 0;
            flex: 1;
        }
        
        .sidebar-nav li {
            margin-bottom: 4px;
        }
        
        .sidebar-nav a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
            transition: background 0.3s;
        }
        
        .sidebar-nav a:hover {
            background: rgba(255,255,255,0.1);
        }
        
        .sidebar-nav a.active {
            background: rgba(255,255,255,0.2);
        }
        
        .sidebar-nav a i {
            width: 24px;
            margin-right: 12px;
            text-align: center;
        }
        
        .sidebar.collapsed .sidebar-nav a span {
            opacity: 0;
            position: absolute;
        }
        
        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid rgba(255,255,255,0.2);
            margin-top: auto;
        }
        
        .logout-btn {
            background: var(--color-error);
            color: white;
            border: none;
            padding: 10px;
            width: 100%;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .sidebar.collapsed .logout-btn span {
            display: none;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            transition: margin-left 0.3s ease;
            width: 100%;
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }
        }
        
        .sidebar.collapsed ~ .main-content {
            margin-left: var(--sidebar-collapsed);
        }
        
        .topbar {
            background: white;
            padding: 16px 24px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 16px;
        }
        
        @media (max-width: 768px) {
            .topbar {
                padding: 12px 60px 12px 16px;
            }
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        @media (max-width: 480px) {
            .user-info .user-name {
                display: none;
            }
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--color-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        @media (max-width: 768px) {
            .btn {
                padding: 8px 12px;
                font-size: 13px;
            }
        }
        
        .btn-primary {
            background-color: var(--color-primary);
            color: white;
        }
        
        .btn-secondary {
            background-color: var(--color-secondary);
            color: var(--text-primary);
        }
        
        .btn-danger {
            background-color: var(--color-error);
            color: white;
        }
        
        .btn-success {
            background-color: var(--color-success);
            color: white;
        }
        
        .card {
            background: var(--bg-secondary);
            border-radius: 10px;
            padding: 18px;
            box-shadow: 0 1px 6px rgba(124, 106, 70, 0.08);
        }
        
        @media (max-width: 768px) {
            .card {
                padding: 14px;
                border-radius: 8px;
                overflow: hidden;
            }
        }

        .page-content {
            padding: 20px;
        }

        @media (max-width: 768px) {
            .page-content {
                padding: 14px;
            }
        }

        @media (max-width: 480px) {
            .page-content {
                padding: 12px;
            }
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }
        
        table th {
            background: var(--color-muted);
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            table th, table td {
                padding: 8px;
                font-size: 13px;
            }
        }
        
        table td {
            padding: 12px;
            border-bottom: 1px solid var(--color-border);
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="search"],
        input[type="date"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--color-border);
            border-radius: 8px;
            font-size: 14px;
            background-color: #faf6f0;
        }
        
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: var(--color-primary);
        }
        
        .scan-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .scan-badge.scanned {
            background: #d4edda;
            color: #155724;
        }
        
        .scan-badge.not-scanned {
            background: #fff3cd;
            color: #856404;
        }
        
        .scan-badge.partial {
            background: #cce5ff;
            color: #004085;
        }
    </style>
    @stack('styles')
</head>
<body>
    <button class="mobile-toggle" onclick="toggleMobileSidebar()">
        <i class="fas fa-bars"></i>
    </button>
    
    <div class="admin-container">
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <img
                    src="{{ asset('images/hrmpeb-logo.png') }}"
                    alt="HRMPEB Logo"
                    class="sidebar-logo"
                >
                <h2>Administrator</h2>
                <button class="sidebar-toggle" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            <ul class="sidebar-nav">
                <li><a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-chart-line"></i><span>Dashboard</span>
                </a></li>
                <li><a href="{{ route('admin.events.index') }}" class="{{ request()->routeIs('admin.events.*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-alt"></i><span>Events</span>
                </a></li>
                <li><a href="{{ route('admin.tickets') }}" class="{{ request()->routeIs('admin.tickets') || request()->routeIs('admin.ticket.detail') ? 'active' : '' }}">
                    <i class="fas fa-ticket-alt"></i><span>All Tickets</span>
                </a></li>
                <li><a href="{{ route('admin.validation') }}" class="{{ request()->routeIs('admin.validation') ? 'active' : '' }}">
                    <i class="fas fa-qrcode"></i><span>Scan Tickets</span>
                </a></li>
            </ul>
            <div class="sidebar-footer">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i><span>Logout</span>
                    </button>
                </form>
            </div>
        </div>
        
        <div class="main-content">
            <div class="topbar">
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-name">{{ auth()->user()->name }}</div>
                </div>
            </div>
            
            <div class="page-content">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-error">{{ session('error') }}</div>
                @endif
                
                @yield('content')
            </div>
        </div>
    </div>
    
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('collapsed');
        }
        
        function toggleMobileSidebar() {
            document.getElementById('sidebar').classList.toggle('mobile-open');
        }
        
        // Close mobile sidebar when clicking outside
        document.addEventListener('click', function(event) {
            if (window.innerWidth <= 768) {
                const sidebar = document.getElementById('sidebar');
                const toggle = document.querySelector('.mobile-toggle');
                
                if (!sidebar.contains(event.target) && !toggle.contains(event.target)) {
                    sidebar.classList.remove('mobile-open');
                }
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
