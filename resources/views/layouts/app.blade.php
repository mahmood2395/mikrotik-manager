<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'MikroTik Manager') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@300;400;500;600&family=Sora:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-base:    #0d1117;
            --bg-sidebar: #111827;
            --bg-card:    #1a2332;
            --bg-card-hover: #1e2a3d;
            --accent:     #00d4aa;
            --accent-dim: #00d4aa22;
            --danger:     #ff4757;
            --warning:    #ffa502;
            --text:       #e2e8f0;
            --text-muted: #64748b;
            --border:     #1e293b;
        }

        * { box-sizing: border-box; }

        body {
            background: var(--bg-base);
            color: var(--text);
            font-family: 'Sora', sans-serif;
            margin: 0;
            min-height: 100vh;
        }

        .font-mono { font-family: 'JetBrains Mono', monospace !important; }

        /* Sidebar */
        .sidebar {
            width: 240px;
            background: var(--bg-sidebar);
            border-right: 1px solid var(--border);
            position: fixed;
            top: 0; left: 0; bottom: 0;
            display: flex;
            flex-direction: column;
            z-index: 50;
        }

        .sidebar-logo {
            padding: 24px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-logo .logo-icon {
            width: 32px; height: 32px;
            background: var(--accent);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        .sidebar-logo .logo-text {
            font-family: 'Sora', sans-serif;
            font-weight: 700;
            font-size: 14px;
            color: var(--text);
            line-height: 1.2;
        }

        .sidebar-logo .logo-sub {
            font-size: 10px;
            color: var(--text-muted);
            font-family: 'JetBrains Mono', monospace;
        }

        .sidebar-nav {
            flex: 1;
            padding: 16px 12px;
            overflow-y: auto;
        }

        .nav-section {
            margin-bottom: 24px;
        }

        .nav-section-title {
            font-size: 10px;
            font-family: 'JetBrains Mono', monospace;
            color: var(--text-muted);
            letter-spacing: 0.1em;
            text-transform: uppercase;
            padding: 0 8px;
            margin-bottom: 6px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            border-radius: 8px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.15s ease;
            margin-bottom: 2px;
        }

        .nav-item:hover {
            background: var(--accent-dim);
            color: var(--accent);
        }

        .nav-item.active {
            background: var(--accent-dim);
            color: var(--accent);
            border-left: 2px solid var(--accent);
        }

        .nav-item .nav-icon {
            width: 16px;
            text-align: center;
            font-size: 14px;
        }

        /* Compare form in sidebar */
        .sidebar-compare {
            padding: 12px;
            border-top: 1px solid var(--border);
        }

        .sidebar-compare input {
            width: 100%;
            background: var(--bg-base);
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 7px 10px;
            font-size: 11px;
            font-family: 'JetBrains Mono', monospace;
            color: var(--text);
            margin-bottom: 6px;
            outline: none;
        }

        .sidebar-compare input:focus {
            border-color: var(--accent);
        }

        .sidebar-compare button {
            width: 100%;
            background: var(--accent-dim);
            border: 1px solid var(--accent);
            color: var(--accent);
            border-radius: 6px;
            padding: 6px;
            font-size: 11px;
            font-family: 'JetBrains Mono', monospace;
            cursor: pointer;
            transition: all 0.15s;
        }

        .sidebar-compare button:hover {
            background: var(--accent);
            color: var(--bg-base);
        }

        /* User section */
        .sidebar-user {
            padding: 16px;
            border-top: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sidebar-user .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-user .avatar {
            width: 30px; height: 30px;
            background: var(--accent-dim);
            border: 1px solid var(--accent);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: var(--accent);
            font-family: 'JetBrains Mono', monospace;
        }

        .sidebar-user .username {
            font-size: 12px;
            color: var(--text);
            font-weight: 500;
        }

        .sidebar-user .logout-btn {
            font-size: 11px;
            color: var(--text-muted);
            background: none;
            border: none;
            cursor: pointer;
            font-family: 'JetBrains Mono', monospace;
            transition: color 0.15s;
        }

        .sidebar-user .logout-btn:hover {
            color: var(--danger);
        }

        /* Main content */
        .main-content {
            margin-left: 240px;
            min-height: 100vh;
            padding: 32px;
        }

        /* Cards */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
        }

        .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-title {
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
            letter-spacing: 0.02em;
        }

        /* Tables */
        table { width: 100%; border-collapse: collapse; }

        thead tr {
            background: #0d1117;
        }

        thead th {
            padding: 10px 16px;
            text-align: left;
            font-size: 10px;
            font-family: 'JetBrains Mono', monospace;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            border-bottom: 1px solid var(--border);
        }

        tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background 0.1s;
        }

        tbody tr:hover { background: var(--bg-card-hover); }
        tbody tr:last-child { border-bottom: none; }

        tbody td {
            padding: 12px 16px;
            font-size: 13px;
            color: var(--text);
        }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 500;
        }

        .badge-success { background: #00d4aa22; color: #00d4aa; border: 1px solid #00d4aa44; }
        .badge-danger  { background: #ff475722; color: #ff4757; border: 1px solid #ff475744; }
        .badge-warning { background: #ffa50222; color: #ffa502; border: 1px solid #ffa50244; }
        .badge-muted   { background: #64748b22; color: #64748b; border: 1px solid #64748b44; }
        .badge-blue    { background: #3b82f622; color: #60a5fa; border: 1px solid #3b82f644; }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s;
            border: none;
            text-decoration: none;
            font-family: 'Sora', sans-serif;
        }

        .btn-primary {
            background: var(--accent);
            color: var(--bg-base);
        }
        .btn-primary:hover { opacity: 0.9; transform: translateY(-1px); }

        .btn-ghost {
            background: transparent;
            color: var(--text-muted);
            border: 1px solid var(--border);
        }
        .btn-ghost:hover { border-color: var(--accent); color: var(--accent); }

        .btn-danger {
            background: #ff475722;
            color: var(--danger);
            border: 1px solid #ff475744;
        }
        .btn-danger:hover { background: var(--danger); color: white; }

        /* Inputs */
        .input {
            background: var(--bg-base);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 9px 12px;
            font-size: 13px;
            color: var(--text);
            width: 100%;
            outline: none;
            transition: border-color 0.15s;
            font-family: 'Sora', sans-serif;
        }

        .input:focus { border-color: var(--accent); }

        .input-mono {
            font-family: 'JetBrains Mono', monospace !important;
            font-size: 12px;
        }

        /* Label */
        .label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 6px;
        }

        /* Page header */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
        }

        .page-title {
            font-size: 22px;
            font-weight: 700;
            color: var(--text);
            font-family: 'Sora', sans-serif;
        }

        .page-subtitle {
            font-size: 12px;
            color: var(--text-muted);
            font-family: 'JetBrains Mono', monospace;
            margin-top: 3px;
        }

        /* Flash messages */
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .alert-success {
            background: #00d4aa11;
            border: 1px solid #00d4aa44;
            color: #00d4aa;
        }

        .alert-error {
            background: #ff475711;
            border: 1px solid #ff475744;
            color: #ff4757;
        }

        /* Progress bars */
        .progress {
            background: var(--border);
            border-radius: 999px;
            height: 4px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            border-radius: 999px;
            transition: width 0.5s ease;
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: var(--bg-base); }
        ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 2px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--text-muted); }

        /* Scan line effect on sidebar */
        .sidebar::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: repeating-linear-gradient(
                0deg,
                transparent,
                transparent 2px,
                rgba(0,212,170,0.01) 2px,
                rgba(0,212,170,0.01) 4px
            );
            pointer-events: none;
        }

        /* Accent dot animation */
        @keyframes pulse-dot {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }

        .live-dot {
            width: 6px; height: 6px;
            background: var(--accent);
            border-radius: 50%;
            display: inline-block;
            animation: pulse-dot 2s infinite;
        }

        /* Page fade in */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .main-content { animation: fadeIn 0.25s ease; }
    </style>
</head>
<body>

@auth
<aside class="sidebar">
    <!-- Logo -->
    <div class="sidebar-logo">
        <div class="logo-icon">🔧</div>
        <div>
            <div class="logo-text">MikroTik</div>
            <div class="logo-sub">Fleet Manager</div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
        <div class="nav-section">
            <div class="nav-section-title">Network</div>
            <a href="{{ route('routers.index') }}"
               class="nav-item {{ request()->routeIs('routers.*') ? 'active' : '' }}">
                <span class="nav-icon">⬡</span> Routers
            </a>
            <a href="{{ route('monitoring.overview') }}"
               class="nav-item {{ request()->routeIs('monitoring.*') ? 'active' : '' }}">
                <span class="nav-icon">◎</span> Monitor
                <span class="live-dot ml-auto"></span>
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">Operations</div>
            <a href="{{ route('commands.bulk') }}"
               class="nav-item {{ request()->routeIs('commands.*') ? 'active' : '' }}">
                <span class="nav-icon">❯</span> Bulk Commands
            </a>
            <a href="{{ route('scripts.index') }}"
               class="nav-item {{ request()->routeIs('scripts.*') ? 'active' : '' }}">
                <span class="nav-icon">⌗</span> Scripts
            </a>
        </div>
    </nav>

    <!-- Compare scripts -->
    <div class="sidebar-compare">
        <div class="nav-section-title" style="padding: 0; margin-bottom: 8px;">Compare Scripts</div>
        <form action="{{ route('router-scripts.compare') }}" method="GET">
            <input type="text" name="name"
                   class="input-mono"
                   placeholder="script-name...">
            <button type="submit">⇄ Compare Across Routers</button>
        </form>
    </div>

    <!-- User -->
    @auth
    <div class="sidebar-user">
        <div class="user-info">
            <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <div class="username">{{ auth()->user()->name }}</div>
        </div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="logout-btn">⏻</button>
        </form>
    </div>
    @endauth
</aside>
@endauth

<!-- Main content -->
<main class="{{ auth()->check() ? 'main-content' : '' }}">
    @if(session('success'))
        <div class="alert alert-success">✓ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error">✕ {{ session('error') }}</div>
    @endif

    @yield('content')
</main>

</body>
</html>